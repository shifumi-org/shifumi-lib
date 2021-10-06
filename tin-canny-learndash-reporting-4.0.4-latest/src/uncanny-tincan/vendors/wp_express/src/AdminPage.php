<?php
/**
 *
 * WE\AdminPage Class
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

class AdminPage extends Extensions\Abs {
	use \WE_TINCANNY\Extensions\StoredInfoSet;

	private $url, $scripts, $styles, $plugin;
	protected $template = [];

	protected $defaultName = 'Admin Page';

	protected $position = 'settings';
	private $icon = 'dashicons-admin-generic';
	private $menu_icon = '';
	private $capability = 'manage_options';

	protected $this_page;

	public function __construct() {
		$name = ( !func_num_args() ) ? false : func_get_arg(0);
		parent::__construct( $name );

		if ( is_network_admin() ) {
			add_action( 'network_admin_menu', array( $this, 'setAdminMenu' ) );
		} else {
			add_action( 'admin_menu', array( $this, 'setAdminMenu' ) );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
		add_action( 'plugin_action_links', array( $this, 'setPluginActionLinks' ), 15, 3 );
	}

	// ! Callbacks
	public function __set( $name, $value ) {
		switch( $name ) {
			case 'key' :
				$this->key = sanitize_title( $value );
				return true;
			break;

			case 'template' :
				$this->template[] = $value;
			break;

			case 'position' :
			case 'version' :
			case 'plugin' :
			case 'capability' :
				$this->{$name} = $value;
				return true;
			break;

			case 'script' :
			case 'scripts' :
			case 'js' :
			case 'javascript' :
				$this->scripts[] = $value;
				return true;
			break;

			case 'styles' :
			case 'style' :
			case 'css' :
				$this->styles[] = $value;
				return true;
			break;
		}

		return false;
	}

	public function setAdminMenu() {
		switch ( $this->position ) {
			case 'option' :
			case 'settings' :
			case 'Settings' :
				$this->this_page = add_options_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
				$this->url = admin_url( 'options-general.php?page=' . $this->key );
			break;

			case 'tools' :
			case 'Tools' :
				$this->this_page = add_management_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
				$this->url = admin_url( 'tools.php?page=' . $this->key );
			break;

			case 'users' :
			case 'Users' :
				$this->this_page = add_users_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
				$this->url = admin_url( 'users.php?page=' . $this->key );
			break;

			case 'plugins' :
			case 'Plugins' :
				$this->this_page = add_plugins_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
				$this->url = admin_url( 'plugins.php?page=' . $this->key );
			break;

			case 'comments' :
			case 'Comments' :
				$this->this_page = add_comments_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
				$this->url = admin_url( 'comments.php?page=' . $this->key );
			break;

			case 'pages' :
			case 'Pages' :
				$this->this_page = add_pages_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
				$this->url = admin_url( 'edit.php?post_type=page&page=' . $this->key );
			break;

			case 'posts' :
			case 'Posts' :
				$this->this_page = add_posts_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
				$this->url = admin_url( 'edit.php?page=' . $this->key );
			break;

			case 'media' :
			case 'Media' :
				$this->this_page = add_media_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
				$this->url = admin_url( 'upload.php?page=' . $this->key );
			break;

			case 'dashboard' :
			case 'Dashboard' :
				$this->this_page = add_dashboard_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
				$this->url = admin_url( 'index.php?page=' . $this->key );
			break;

			case 'appearance' :
			case 'Appearance' :
				$this->this_page = add_theme_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
				$this->url = admin_url( 'themes.php?page=' . $this->key );
			break;

			default :
				global $menu;
				$position_key = $this->key;

				if ( is_numeric( $this->position ) ) {
					if ( isset( $menu[ $this->position ] ) ) {
						$position_key = $menu[ $this->position ][2];
						$this->this_page = add_submenu_page( $position_key, $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
					} else {
						$this->this_page = add_menu_page( $this->name, $this->name, $this->capability, $position_key, array( $this, 'printTemplate' ), $this->icon, $this->position );
					}
				} else {
					$detected = false;

					foreach( $menu as $menu_ ) {
						if ( $this->position == $menu_[0] ) {
							$position_key = $menu_[2];
							$detected = true;
							$this->this_page = add_submenu_page( $position_key, $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ) );
							break;
						}
					}

					if ( !$detected ) {
						$this->this_page = add_menu_page( $this->name, $this->name, $this->capability, $this->key, array( $this, 'printTemplate' ), $this->icon );
					}
				}

				$this->url = admin_url( 'admin.php?page=' . $this->key );
			break;
		}

		add_action( "load-{$this->this_page}", array( $this, "ScreenOptions") );
	}

	public function ScreenOptions() {
		$screen = get_current_screen();

		if( !is_object($screen) || $screen->id != $this->this_page )
			return;

/*
$args = array(
		'label' => __('Members per page', 'pippin'),
		'default' => 10,
		'option' => 'pippin_per_page'
	);
	add_screen_option( 'per_page', $args );
*/
	}

	// Admin Page
	public function printTemplate( $contents = "" ) {
		if ( $this->template ) {
			ob_start();
			foreach( $this->template as $template ) {
				if ( $template ) call_user_func( $template );
			}
			$contents = $contents . ob_get_clean();
		}

		printf( '<div class="wrap" id="admin-%s">', $this->key );
		printf( '<h2 class="page-title">%s</h2>', $this->name );
		echo $contents;
		echo '</div>';
	}

	public function enqueueScripts() {
		if ( strpos( $this->url, $_SERVER[ 'REQUEST_URI' ] ) !== false ) {
			if ( $this->scripts ) {
				foreach( $this->scripts as $key => $script ) {
					wp_enqueue_script( $this->key . '-' . $key, $script , array( 'jquery' ), UNCANNY_REPORTING_VERSION );
				}
			}

			if ( $this->styles ) {
				foreach( $this->styles as $key => $styles ) {
					wp_enqueue_style( $this->key . '-' . $key, $styles , false, UNCANNY_REPORTING_VERSION );
				}
			}
		}
	}

	public function setPluginActionLinks( $actions, $plugin_file, $plugin ) {
		if ( $this->plugin && $this->plugin == $plugin[ 'Name' ] ) {
			$actions[ 'setting' ] = sprintf( '<a href="%s"><span class="dashicons dashicons-admin-settings"></span> Setting</a>', $this->url );
		}

		return $actions;
	}
}