<?php
/**
 *
 * WE\PostType Class
 *
 * @author	Sujin 수진 Choi
 * @package	wp-express
 * @version	4.5.0
 * @website	http://sujinc.com
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 */

namespace WE_TINCANNY;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class PostType extends Extensions\Abs {
	use \WE_TINCANNY\Extensions\StoredInfoSet;

	private $arguments;
	private $additional_args = [];

	private $taxonomies = [];
	private $column_after = [];
	private $column_before = [];

	public function __construct() {
		$name = ( !func_num_args() ) ? false : func_get_arg(0);
		parent::__construct( $name );

		$this->initOptionSetting( false );

		$labels = array(
			'name' => _x( $this->name, 'post type general name' ),
			'singular_name' => _x( $name, 'post type singular name' ),
			'add_new' => _x( 'Add New', 'project' ),
			'add_new_item' => __( 'Add New ' . $name ),
			'edit_item' => __( 'Edit ' . $name ),
			'new_item' => __( 'New ' . $name ),
			'view_item' => __( 'View ' . $name ),
			'search_items' => __( 'Search ' . $name ),
			'not_found' =>  __( 'No ' . $name . ' found' ),
			'not_found_in_trash' => __( 'No ' . $name . ' found in Trash' ),
			'parent_item_colon' => '',
			'menu_name' => $name
		);

		$this->arguments = array(
			'labels' => $labels,
			'supports' => array( 'title' ),
			'public' => true,
			'rewrite' => 'rewrite'
		);

		add_action( 'init', array( $this, 'registerPostType' ) );
		add_action( 'admin_head', array( $this, 'registerMetaBoxes' ) );

		add_action( 'parse_query', array( $this, 'parseQuery' ) );
		add_action( 'save_post', array( $this, 'savePost' ), 10, 2 );
	}

	public function __call( $name, $arguments ) {
		if ( strpos( $name, 'PostMetaBox_' ) === 0 ) {
			$this->echoPostMetaBox( substr( $name, 12 ) );
		}
	}

	public function __get( $name ) {
		if ( $return = $this->getOptionSetting( $name ) ) return $return;

		switch( $name ) {
			case 'taxonomy';
				if ( !$this->taxonomies ) return false;

				end( $this->taxonomies );
				$key = key( $this->taxonomies );
				return  $this->taxonomies[ $key ];
			break;
		}
	}

	public function __set( $name, $value ) {
		if ( parent::__set( $name, $value ) ) return;
		if ( $this->setOptionSetting( $name, $value ) ) return;

		switch( $name ) {
			case 'public' :
			case 'exclude_from_search' :
			case 'description' :
			case 'publicly_queryable' :
			case 'show_ui' :
			case 'show_in_menu' :
			case 'show_in_admin_bar' :
			case 'query_var' :
			case 'rewrite' :
			case 'capability_type' :
			case 'capabilities' :
			case 'has_archive' :
			case 'hierarchical' :
			case 'menu_position' :
			case 'menu_icon' :
			case 'map_meta_cap' :
			case 'register_meta_box_cb' :
			case 'taxonomies' :
			case 'permalink_epmask' :
			case 'can_export' :
			case 'show_in_rest' :
			case 'rest_base' :
			case 'rest_controller_class' :
			case '_builtin' :
			case '_edit_link' :
				$this->additional_args[ $name ] = $value;
			break;

			case 'supports' :
				if ( !isset( $this->additional_args[ 'supports' ] ) ) $this->additional_args[ 'supports' ] = [];

				if ( is_array( $value ) ) {
					$this->additional_args[ 'supports' ] = array_merge( $this->additional_args[ 'supports' ], $value );
				} else {
					$this->additional_args[ 'supports' ][] = $value;
				}
			break;

			case 'taxonomy';
				if ( gettype( $value ) === 'object' && get_class( $value ) === 'WE_TINCANNY\\Taxonomy' ) {
					$value->post_type = $this->key;
					$this->taxonomies[ $value->key ] = $value;

				} else if( gettype( $value ) === 'string' ) {
					$key = sanitize_title( $value );
					$this->taxonomies[ $key ] = new Taxonomy( $value );
					$this->taxonomies[ $key ]->post_type = $this->key;
				}
			break;

			case 'column_before' :
				$key = sanitize_title( $value );
				$this->column_before[ $key ] = $value;
			break;

			case 'column' :
			case 'column_after' :
				$key = sanitize_title( $value );
				$this->column_after[ $key ] = $value;
			break;
		}
	}

	public function registerPostType() {
		if ( post_type_exists( $this->key ) ) {
			// Modify an Exist Post Type
			$arguments = array_merge( (array) get_post_type_object( $this->key ), $this->additional_args );
			register_post_type( $this->key, $arguments );

		} else {
			// New Post Type
			$this->arguments = array_merge( $this->arguments, $this->additional_args );

			if ( $this->arguments[ 'rewrite' ] == 'rewrite' )
				$this->arguments[ 'rewrite' ] = array( 'slug' => $this->key );
			register_post_type( $this->key, $this->arguments );
		}

		//<-- Modify List Column
		switch( $this->key ) {
			case 'post' :
				$filter = 'manage_posts_columns';
				$action = 'manage_posts_custom_column';
				break;
			case 'page' :
				$filter = 'manage_pages_columns';
				$action = 'manage_pages_custom_column';
				break;
			default :
				$filter = "manage_{$this->key}_posts_columns";
				$action = "manage_{$this->key}_posts_custom_column";
				break;
		}

		add_filter( $filter, array( $this, 'ManageColumns' ) );
		add_action( $action, array( $this, 'ManageCustiomColumns' ), 10, 2 );
		// Modify List Column -->
	}

	// Manage Columns
	public function ManageColumns( $columns ) {
		if ( $this->column_after ) {
			foreach( $this->column_after as $key => $column ) {
				$columns[ $key ] = __( $column );
			}
		}

		if ( $this->column_before ) {
			foreach( $this->column_before as $key => $column ) {
				$columns = array( $key => $column ) + $columns;
			}
		}

		return  $columns;
	}

	public function ManageCustiomColumns( $column, $post_id ) {
		if ( $this->column_after && isset( $this->column_after[ $column ] ) ) {
			$this->PrintColumn( $column, $this->column_after[ $column ], $post_id );
		}

		if ( $this->column_before && isset( $this->column_before[ $column ] ) ) {
			$this->PrintColumn( $column, $this->column_before[ $column ], $post_id );
		}
	}

	private function PrintColumn( $key, $column, $post_id ) {
		// Tthumbnail
		if ( $key == 'thumbnail' ) {
			echo get_the_post_thumbnail( $post_id, array( 50, 50 ) );
			return;
		}

		// Taxonomies
		if ( array_key_exists( $key, $this->taxonomies ) ) {
			if ( $terms = wp_get_post_terms( $post_id, $key ) ) {
				$text = [];

				foreach( $terms as $term ) {
					$text[] = sprintf( '<a href="%s">%s</a>', add_query_arg( $key, $term->slug ), $term->name );
				}

				echo implode( ', ', $text );
			}
		}

		// Post Meta
		if ( array_key_exists( $key, $this->options ) ) {
			$meta = get_post_meta( $post_id, '_WE-meta_', true );

			if ( $meta[ $key ] ) {
				$this->options[ $key ]->PrintColumnVaue( $meta[ $key ] );
			}

			return;
		}
	}

	public function registerMetaBoxes() {
		if ( !empty( $this->sections ) ) {
			foreach( $this->sections as $sectionKey => $section ) {
				add_meta_box( $sectionKey, $section[ 'name' ], array( $this, 'PostMetaBox_' . $sectionKey ), $this->key );
			}
		}
	}

	private function readFromDb() {
		global $post;

		if ( gettype( $post ) !== 'object' && get_class( $post ) !== 'WP_Post' ) return;
		if ( !$post ) return;
		if ( $post->post_type !== $this->key ) return;

 		if ( !$this->values && $this->values = get_post_meta( $post->ID, '_WE-meta_', true ) ) {
			foreach( $this->options as $key => $option ) {
				if ( array_key_exists( $key, $this->values ) )
					$option->value = $this->values[ $key ];
			}
 		}
	}

	public function echoPostMetaBox( $sectionKey ) {
		$this->readFromDb();

		if ( $this->version === '0.0.0' ) {
			printf( '<div class="description">The setting will be stored in post meta - _WE-meta_ value. This message will be disappeared when you set <code>version</code> value. ( ig. 1.0.0 )</div>' );
		}

		echo '<table class="form-table">';
		foreach( $this->sections[ $sectionKey ][ 'fields' ] as $field ) {
			echo '<tr>';
					if ( is_array( $field ) ) {	// Set Item
						printf( '<th>%s</th>', $this->options[ $field[0] ]->name );
						array_shift( $field );

						echo '<td>';

						foreach( $field as $setFieldKey ) {
							$this->options[ $setFieldKey ]->printSettingsField();
						}
						echo '</td>';

					} else {
						printf( '<th>%s</th>', $this->options[ $field ]->name );

						echo '<td>';
						$this->options[ $field ]->printSettingsField();
						echo '</td>';
					}
			echo '</tr>';
		}
		echo '</table>';
	}

	private function checkIsSaving() {
		if( !$_POST ) return false;
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST[ 'bulk_edit' ] ) ) return;
		if ( !isset( $_POST[ 'post_type' ] ) || $_POST[ 'post_type' ] != $this->key ) return;

		delete_transient( $this->transientKey );
		return true;
	}

	public function savePost( $post_id, $post ) {
		if( !$this->checkIsSaving() ) return;

		$metas = [];
		foreach( $this->options as $option ) {
			if ( $option->type === 'set' ) continue;

			$metas[ $option->key ] = $_POST[ $option->key ];
		}

		update_post_meta( $post_id, '_WE-meta_', $metas );
	}

	public function parseQuery( $query ) {
		if ( !$query->is_main_query() ) return false;

		if ( is_single() && !empty( $query->query[ 'post_type' ] ) && $query->query[ 'post_type' ] == $this->key ) {
			if ( !$query->is_posttype ) {
				$query->is_posttype = array();
			}
			$query->is_posttype[ $this->key ] = true;
		}

		# add the slug to the body class
		if ( isset( $query->is_posttype[ $this->key ] ) )
			add_filter( 'body_class', array( $this, 'themeBodyClass' ) );
	}

	public function themeBodyClass( $classes ) {
		$classes[] = 'template-' . $this->key;
		return $classes;
	}
}



/*
	// 헬프 탭
// Priority 5 allows the removal of default tabs and insertion of other plugin's tabs
add_filter( 'contextual_help', 'wpse_77308_products_help', 5, 3 );

function wpse_77308_products_help( $old_help, $screen_id, $screen )
{
    // Not our screen, exit earlier
    // Adjust for your correct screen_id, see plugin recommendation bellow
    if( 'edit-magazine' != $screen_id )
        return;

    // Remove default tabs
    $screen->remove_help_tabs();

    // Add one help tab
    // For new ones: duplicate this, change id's and create custom callbacks
    $screen->add_help_tab( array(
        'id'      => 'products-help',
        'title'   => 'Products',
        'content' => '', // left empty on purpose, we use the callback bellow
        'callback' => 'wpse_77308_print_help'
    ));

    // This sets the sidebar, which is common for all tabs of this screen
    get_current_screen()->set_help_sidebar(
        '<p><strong>' . __('For more information:') . '</strong></p>' .
        '<p>' . __('<a href="http://wordpress.stackexchange.com/" title="WordPress StackExchange" target="_blank">WordPress Answers</a>') . '</p>' .
        '<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
    );

    return $old_help;
}


function wpse_77308_print_help()
{
    echo '
        <p>Products show the details of the items that we sell on the website.
        You can see a list of them on this page in reverse chronological order
        - the latest one we added is first.</p>

        <p>You can view/edit the details of each product
        by clicking on its name, or you can perform bulk actions
        using the dropdown menu and selecting multiple items.</p>
    ';
}
*/
