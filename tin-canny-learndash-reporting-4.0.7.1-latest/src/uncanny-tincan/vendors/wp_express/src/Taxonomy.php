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

class Taxonomy extends Extensions\Abs {
	use \WE_TINCANNY\Extensions\HtmlHelper;
	use \WE_TINCANNY\Extensions\StoredInfoSet;

	private $arguments;
	private $additional_args = [];

	private $post_type = 'post';

	private $column_after = [];
	private $column_before = [];

	public function __construct() {
		$name = ( !func_num_args() ) ? false : func_get_arg(0);
		parent::__construct( $name );

		$this->initOptionSetting( 'WP_TermMeta_Settings-' . $this->key );

		$this->arguments = array(
			'name' => _x( $name, 'taxonomy general name' ),
			'singular_name' => _x( $name, 'taxonomy singular name' ),
			'search_items' =>  __( 'Search ' . $name ),
			'all_items' => __( 'All ' . $name ),
			'edit_item' => __( 'Edit ' . $name ),
			'update_item' => __( 'Update ' . $name ),
			'add_new_item' => __( 'Add New ' . $name ),
			'new_item_name' => __( 'New ' . $name . ' Name' )
		);

		$this->arguments = array(
			'labels' => $this->arguments,
			'hierarchical' => true,
			'public' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => 'rewrite'
		);

		add_action( 'init', array( $this, 'registerTaxonomy' ), 100 );
	}

	public function __get( $name ) {
		return $this->getOptionSetting( $name );
	}

	public function __set( $name, $value ) {
		if ( parent::__set( $name, $value ) ) return;
		if ( $this->setOptionSetting( $name, $value ) ) return;

		switch( $name ) {
			case 'public' :
			case 'show_in_menu' :
			case 'show_in_nav_menus' :
			case 'show_tagcloud' :
			case 'show_in_quick_edit' :
			case 'meta_box_cb' :
			case 'show_admin_column' :
			case 'description' :
			case 'update_count_callback' :
			case 'capabilities' :
			case 'sort' :
			case '_builtin' :
			case 'hierarchical' :
			case 'show_ui' :
			case 'query_var' :
			case 'rewrite' :
				$this->additional_args[ $name ] = $value;
				return;
			break;

			case 'post_type' :
			case 'posttype' :
			case 'post' :
				$this->post_type = sanitize_title( $value );
				return;
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

	public function registerTaxonomy() {
		if ( $this->post_type && array_key_exists( $this->post_type, get_post_types() ) ) {
			$taxonomy_objects = get_object_taxonomies( $this->post_type, 'objects' );

			if ( $this->post_type == 'post' && $this->key == 'tags' ) $this->key = 'post_tag';
			if ( $this->post_type == 'post' && $this->key == 'categories' ) $this->key = 'category';

			if ( array_key_exists( $this->key, $taxonomy_objects ) ) {
				// Modify an Exist Post Type
				$arguments = array_merge( (array) $taxonomy_objects[ $this->key ], $this->additional_args );
				register_taxonomy( $this->key, array( $this->post_type ), $this->arguments );
			} else {
				// Add New Post Type
				$this->arguments = array_merge( $this->arguments, $this->additional_args );
				if ( $this->arguments[ 'rewrite' ] == 'rewrite' )
					$this->arguments[ 'rewrite' ] = array( 'slug' => $this->key );

				register_taxonomy( $this->key, array( $this->post_type ), $this->arguments );
			}

			// Meta Edit & Save
			add_action( "{$this->key}_edit_form", array( $this, 'printTermMeta' ), 10 );
			add_action( "edited_{$this->key}", array( $this, 'saveMetas' ), 10 );


			add_filter( "manage_edit-{$this->key}_columns", array( $this, 'ManageColumns' ) );
			add_action( "manage_{$this->key}_custom_column", array( $this, 'ManageCustiomColumns' ), 10, 3 );
		}
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

	public function ManageCustiomColumns( $tem, $column, $term_id ) {
		if ( $this->column_after && isset( $this->column_after[ $column ] ) ) {
			$this->PrintColumn( $column, $this->column_after[ $column ], $term_id );
		}

		if ( $this->column_before && isset( $this->column_before[ $column ] ) ) {
			$this->PrintColumn( $column, $this->column_before[ $column ], $term_id );
		}
	}

	private function PrintColumn( $key, $column, $term_id ) {
		// Term Meta
		if ( array_key_exists( $key, $this->options ) ) {
			$meta = get_term_meta( $term_id, '_WE-meta_', true );

			if ( isset( $meta[ $key ] ) ) {
				$this->options[ $key ]->PrintColumnVaue( $meta[ $key ] );
			}

			return;
		}
	}

	private function isSupported() {
		global $wp_version;
		return ( version_compare( $wp_version, '4.4', '<' ) ) ? false : true;
	}

	private function readFromDb( $term_id ) {
		if ( $this->value = get_term_meta( $term_id, '_WE-meta_', true ) ) {
			foreach( $this->options as $key => $option ) {
				if ( array_key_exists( $key, $this->values ) )
					$option->value = $this->values[ $key ];
			}
		}
	}

	public function printTermMeta( $term ) {
		if ( !$this->isSupported() ) {
			$this->showMessage( 'In order to use term meta function, you must update your Wordpress to at least up to 4.4 version.', 'error' );
			return;
		}

		if ( !$this->options ) return false;

		$this->readFromDb( $term->term_id );

		if ( $this->version === '0.0.0' ) {
			printf( '<div class="description">The setting will be stored in term meta - _WE-meta_ value. This message will be disappeared when you set <code>version</code> value. ( ig. 1.0.0 )</div>' );
		}

		printf( '<table class="form-table" id="term-%s"><tbody>', $this->key );

		foreach( $this->sections as $fields ) {
			foreach( $fields[ 'fields' ] as $field ) {
				echo '<tr>';
						if ( is_array( $field ) ) {	// Set Item
							printf( '<th scope="row" valign="top"><label for="%s">%s</label></th>', $this->options[ $field[0] ]->key, $this->options[ $field[0] ]->name );
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
		}

		echo '</tbody></table>';
	}

	public function saveMetas( $term_id ) {
		if ( !$this->options ) return;

		$metas = [];
		foreach( $this->options as $option ) {
			if ( $option->type === 'set' ) continue;

			$metas[ $option->key ] = $_POST[ $option->key ];
		}

		update_term_meta( $term_id, '_WE-meta_', $metas );
	}
}