<?php
/**
 * Main controller for certificate builder, all the hooks start here.
 *
 * @file
 * @package Learndash_Certificate_Builer
 */

namespace LearnDash_Certificate_Builder\Controller;

use LearnDash_Certificate_Builder\Component\PDF;

/**
 * Class Certificate_Builder
 *
 * Init the Certificate Builder block
 *
 * @package LearnDash_Certificate_Builder\Controller
 */
class Certificate_Builder {
	/**
	 * The certificate post type slug
	 *
	 * @var string
	 */
	private $post_type_slug;

	/**
	 * Service class, which handling the PDF generate logic
	 *
	 * @var PDF
	 */
	private $service;

	/**
	 * This construct is start in wp init hook
	 *
	 * Certificate_Builder constructor.
	 */
	public function __construct() {
		$this->post_type_slug = learndash_get_post_type_slug( 'certificate' );
		$this->service        = new PDF();
		add_filter( 'use_block_editor_for_post_type', array( $this, 'remove_disable' ), 11, 2 );
		add_action( 'add_meta_boxes', array( $this, 'metabox_backward_compatibility' ), 99 );
		add_filter( 'allowed_block_types_all', array( $this, 'only_allow_this' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_block' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'localize_script' ) );
		add_action( 'wp', array( $this, 'maybe_hook_into_tcpdf' ), 5 );
		add_action( 'template_redirect', array( $this, 'generate_preview_pdf' ), 1 );
		add_filter( 'register_block_type_args', array( $this, 'custom_ld_block_attributes' ), 10, 2 );
		/**
		 * Because we don't want user click on the view link and see the blocked text because if invalid state,
		 * so we going to hide it, only preview allow.
		 */
		add_filter( 'post_row_actions', array( $this, 'maybe_hide_view_link' ), 10, 2 );
		// LCB-9 Add a column to show which one uses builder.
		add_filter( 'manage_' . $this->post_type_slug . '_posts_columns', array( $this, 'add_type_column' ) );
		add_action( 'manage_' . $this->post_type_slug . '_posts_custom_column', array( $this, 'type_column' ), 10, 2 );
		add_action( 'admin_head', array( $this, 'style_certificate_table_to_fluid' ) );
		$this->block_template();
		// init the switcher class.
		new Switcher( $this->post_type_slug );
	}

	/**
	 * This to make the certificate post type table looks fluid
	 */
	public function style_certificate_table_to_fluid() {
		global $current_screen;
		if ( ! is_object( $current_screen ) ) {
			return;
		}
		if ( 'edit-' . $this->post_type_slug === $current_screen->id ) {
			?>
			<style>
				table.fixed {
					table-layout: auto;
				}
			</style>
			<?php
		}
	}

	/**
	 * Add a type column into certificate post type table
	 *
	 * @param array $columns The columns.
	 *
	 * @return array
	 */
	public function add_type_column( $columns ) {
		$columns['type'] = '<span style="width:10px">Type</span>';

		return $columns;
	}

	/**
	 * Show the type of a certificate in list page.
	 *
	 * @param string $column The column name.
	 * @param int    $post_id The post ID.
	 */
	public function type_column( $column, $post_id ) {
		if ( 'type' === $column ) {
			$is_use_builder = absint( get_post_meta( $post_id, Switcher::KEY, true ) ) === 1;
			if ( $is_use_builder ) {
				esc_html_e( 'Builder', 'learndash-certificate-builder' );
			} else {
				esc_html_e( 'Legacy', 'learndash-certificate-builder' );
			}
		}
	}

	/**
	 * Hide the view link in certificate table
	 *
	 * @param array    $actions The post actions data.
	 * @param \WP_Post $post The current post.
	 *
	 * @return mixed
	 */
	public function maybe_hide_view_link( $actions, $post ) {
		if ( $post->post_type === $this->post_type_slug && 'publish' === $post->post_status ) {
			$parsed = parse_blocks( $post->post_content );
			if ( count( $parsed ) && 'learndash/ld-certificate-builder' === $parsed[0]['blockName'] ) {
				// this is publish, however, the certificate is a special post type that can only be preview.
				$preview_link    = get_preview_post_link( $post );
				$title           = _draft_or_post_title();
				$actions['view'] = sprintf(
					'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
					esc_url( $preview_link ),
					// translators: Post type title.
						esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'learndash-certificate-builder' ), $title ) ),
					__( 'Preview' )
				);
			}
		}

		return $actions;
	}

	/**
	 * Update attributes so we can use the server_render block
	 *
	 * @param array  $args The block args.
	 * @param string $name The block name.
	 *
	 * @return mixed
	 */
	public function custom_ld_block_attributes( $args, $name ) {
		if ( ! in_array(
			$name,
			array(
				'learndash/ld-courseinfo',
				'learndash/ld-groupinfo',
				'learndash/ld-usermeta',
				'learndash/ld-quizinfo',
			),
			true
		) ) {
			return $args;
		}
		$args['attributes'] = array_merge(
			$args['attributes'],
			array(
				'font'            => array(
					'type'    => 'string',
					'default' => '',
				),
				'useFont'         => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'fontSize'        => array(
					'type'    => 'string',
					'default' => 1.25,
				),
				'textAlign'       => array(
					'type'    => 'string',
					'default' => 'left',
				),
				'fontStyle'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'fontWeight'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'textTransform'   => array(
					'type'    => 'string',
					'default' => '',
				),
				'textColor'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'backgroundColor' => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);

		return $args;
	}

	/**
	 * This is for backward compatibility with old certificate post
	 */
	public function maybe_hook_into_tcpdf() {
		if ( ! is_singular( $this->post_type_slug ) ) {
			return;
		}
		$post = get_post();
		if ( ! is_object( $post ) ) {
			return;
		}
		$blocks = parse_blocks( $post->post_content );
		if ( count( $blocks ) && 'learndash/ld-certificate-builder' === $blocks[0]['blockName'] ) {
			add_action( 'learndash_tcpdf_init', array( $this, 'generate_pdf' ) );
		}
	}

	/**
	 * Serve the pdf
	 *
	 * @param array $cert_args Certificate data.
	 */
	public function generate_pdf( $cert_args ) {
		if ( ! is_singular( $this->post_type_slug ) ) {
			return;
		}

		$cert_args_defaults = array(
			'cert_id'       => 0,     // The certificate Post ID.
			'post_id'       => 0,     // The Course/Quiz Post ID.
			'user_id'       => 0,     // The User ID for the Certificate.
			'lang'          => 'eng', // The default language.
			'filename'      => '',
			'filename_url'  => '',
			'filename_type' => 'title',
			'pdf_title'     => '',

			/*
			I: send the file inline to the browser (default).
			D: send to the browser and force a file download with the name given by name.
			F: save to a local server file with the name given by name.
			S: return the document as a string (name is ignored).
			FI: equivalent to F + I option
			FD: equivalent to F + D option
			E: return the document as base64 mime multi-part email attachment (RFC 2045)
			*/
		);
		$cert_args = shortcode_atts( $cert_args_defaults, $cert_args );

		// Just to ensure we have valid IDs.
		$cert_args['cert_id'] = absint( $cert_args['cert_id'] );
		$cert_args['post_id'] = absint( $cert_args['post_id'] );
		$cert_args['user_id'] = absint( $cert_args['user_id'] );

		$cert_args['cert_post'] = get_post( $cert_args['cert_id'] );
		if ( ( ! $cert_args['cert_post'] ) || ( ! is_a( $cert_args['cert_post'], 'WP_Post' ) ) || ( learndash_get_post_type_slug( 'certificate' ) !== $cert_args['cert_post']->post_type ) ) {
			wp_die( esc_html__( 'Certificate Post does not exist.', 'learndash-certificate-builder' ) );
		}

		$cert_args['post_post'] = get_post( $cert_args['post_id'] );
		if ( ( ! $cert_args['post_post'] ) || ( ! is_a( $cert_args['post_post'], 'WP_Post' ) ) ) {
			wp_die( esc_html__( 'Awarded Post does not exist.', 'learndash-certificate-builder' ) );
		}

		$cert_args['user'] = get_user_by( 'ID', $cert_args['user_id'] );
		if ( ( ! $cert_args['user'] ) || ( ! is_a( $cert_args['user'], 'WP_User' ) ) ) {
			wp_die( esc_html__( 'User does not exist.', 'learndash-certificate-builder' ) );
		}

		$parsed = parse_blocks( $cert_args['cert_post']->post_content );
		$this->service->serve( $parsed, $cert_args['cert_id'], $cert_args['post_id'] );
	}

	/**
	 * Generate the PDF preview
	 */
	public function generate_preview_pdf() {
		if ( ! is_preview() ) {
			return;
		}

		if ( ! is_singular( $this->post_type_slug ) ) {
			return;
		}

		// render the code.
		$post = get_queried_object();
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}
		$parsed = parse_blocks( $post->post_content );
		if ( count( $parsed ) && 'learndash/ld-certificate-builder' === $parsed[0]['blockName'] ) {
			$this->service->serve( $parsed );
		}
		// if not, then leave to the core to block.
	}

	/**
	 * Enqueue certificate builder block
	 */
	public function enqueue_blocks() {
		wp_enqueue_script( 'learndash-certificate-builder' );
		wp_enqueue_style( 'learndash-certificate-builder' );
	}

	/**
	 * Load the block when we are in the editor
	 */
	public function localize_script() {
		global $current_screen;
		if ( $current_screen->id === $this->post_type_slug ) {
			wp_dequeue_script( 'autosave' );
			global $_wp_theme_features;
			wp_localize_script(
				'learndash-certificate-builder',
				'certificate_builder',
				array(
					'fonts'    => $this->service->get_fonts(),
					'font_url' => learndash_certificate_builder_asset_url( '/external/mpdf/mpdf/ttfonts' ),
					'colors'   => isset( $_wp_theme_features['editor-color-palette'] ) ? $_wp_theme_features['editor-color-palette'] : array( $this->service->get_default_pallete_colors() ),
				)
			);
		}
	}

	/**
	 * Create a template for certificate post type, we pre-add the builder block and lock other blocks to be added
	 */
	public function block_template() {
		$post_type_object = get_post_type_object( $this->post_type_slug );
		if ( ! is_object( $post_type_object ) ) {
			return;
		}
		$post_type_object->template      = array(
			array( 'learndash/ld-certificate-builder' ),
		);
		$post_type_object->template_lock = 'all';
	}

	/**
	 * Whitelist blocks on certificate editor
	 *
	 * @param array                    $allowed_block_types The blocks that we allow to add inside builder block.
	 * @param \WP_Block_Editor_Context $context The current post.
	 *
	 * @return string[]
	 */
	public function only_allow_this( $allowed_block_types, $context ) {
		$post = $context->post;
		if ( $post->post_type === $this->post_type_slug ) {
			return array(
				'core/columns',
				'core/paragraph',
				'core/heading',
				'core/spacer',
				'core/shortcode',
				'core/image',
				'core/quote',
				'core/list',
				'core/separator',
				'learndash/ld-courseinfo',
				'learndash/ld-usermeta',
				'learndash/ld-groupinfo',
				'learndash/ld-quizinfo',
			);
		}

		return $allowed_block_types;
	}

	/**
	 * Hide the metabox if gutenberg enable
	 */
	public function metabox_backward_compatibility() {
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes[ $this->post_type_slug ] ) ) {
			// phpcs:ignore
			$wp_meta_boxes[ $this->post_type_slug ]['advanced']['high']['learndash_certificate_options']['args'] = array(
				'__back_compat_meta_box' => true,
			);
		}
	}

	/**
	 * Re-enable gutenberg editor for certificate post type, which is disable in \LearnDash\Admin\Gutenberg\disable_on_cpts
	 *
	 * @param bool   $is_enabled Current status.
	 * @param string $post_type Current post type.
	 *
	 * @return bool|mixed
	 */
	public function remove_disable( $is_enabled, $post_type ) {
		if ( $this->post_type_slug === $post_type ) {
			$is_enabled = false;
			// we also need to check if the current certificate post is legacy.
			//phpcs:ignore
			if ( isset( $_GET['post'] ) ) {
				//phpcs:ignore
				$id   = absint( $_GET['post'] );
				$post = get_post( $id );
				if ( ! is_object( $post ) ) {
					$is_enabled = false;
				}
				$blocks = parse_blocks( $post->post_content );
				if ( count( $blocks ) && 'learndash/ld-certificate-builder' === $blocks[0]['blockName'] ) {
					$is_enabled = true;
				}
			}
		}

		return $is_enabled;
	}

	/**
	 * Register the builder block, this should be call only one time in bootstrap
	 */
	public function register_block() {
		global $current_screen;
		if ( $current_screen->id !== $this->post_type_slug ) {
			return;
		}
		$script_asset = require learndash_certificate_builder_path( 'build/certificate-builder.asset.php' );
		wp_register_script(
			'learndash-certificate-builder',
			learndash_certificate_builder_asset_url( '/build/certificate-builder.js' ),
			$script_asset['dependencies'],
			$script_asset['version'],
			false
		);
		wp_set_script_translations( 'learndash-certificate-builder', 'learndash-certificate-builder', learndash_certificate_builder_path( 'languages' ) );

		wp_register_style(
			'learndash-certificate-builder',
			learndash_certificate_builder_asset_url( '/build/certificate-builder.css' ),
			array(),
			filemtime( learndash_certificate_builder_path( '/build/certificate-builder.css' ) )
		);

		register_block_type(
			'learndash/learndash-certificate-builder',
			array(
				'editor_script' => 'learndash-certificate-builder',
				'style'         => 'learndash-certificate-builder',
			)
		);

		$this->enqueue_blocks();
	}
}
