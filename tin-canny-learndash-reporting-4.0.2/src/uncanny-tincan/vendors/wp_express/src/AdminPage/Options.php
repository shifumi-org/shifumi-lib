<?php
/**
 *
 * WP_Admin_Page Class
 *
 * @author	Sujin 수진 Choi
 * @package	wp-hacks
 * @version	4.5.0
 * @website	http://sujinc.com
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 */

namespace WE_TINCANNY\AdminPage;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class Options extends \WE_TINCANNY\AdminPage {
	use \WE_TINCANNY\Extensions\HtmlHelper;

	protected $isWpSetting = false;

	public function __construct() {
		$name = ( !func_num_args() ) ? false : func_get_arg(0);
		parent::__construct( $name );

		add_action( 'init', array( $this, 'saveSettings' ), 5 );
		add_action( 'init', array( $this, 'readFromDb' ), 50 );
		add_action( 'admin_init', array( $this, 'CheckWPSetting' ) );
	}

	public function CheckWPSetting() {
		switch ( strtolower( $this->position ) ) {
			case 'general' :
				remove_action( 'admin_menu', array( $this, 'setAdminMenu' ) );
				$this->key = 'general';
				$this->initOptionSetting( 'WP_Admin_Page_Settings-' . $this->key );
				$this->setSettingsSection();
				return;
			break;
		}

		$this->initOptionSetting( 'WP_Admin_Page_Settings-' . $this->key );
	}

	public function __get( $name ) {
		if ( $value = parent::__get( $name ) ) return $value;
		return $this->getOptionSetting( $name );
	}

	public function __set( $name, $value ) {
		if ( parent::__set( $name, $value ) ) return;
		if ( $this->setOptionSetting( $name, $value ) ) return;
	}

	private function checkIsSaving() {
		if( !$_POST ) return false;
		if( !isset( $_POST[ '_wpnonce' ] ) ) return false;
		if( function_exists( 'wp_verify_nonce' ) && !wp_verify_nonce( $_POST[ '_wpnonce' ], $this->key . '-options' ) ) return false;

		delete_transient( $this->transientKey );
		return true;
	}

	public function readFromDb() {
 		$this->values = get_option( '_' . $this->key . '_', false );

 		foreach( $this->options as $key => $option ) {
			if ( is_array( $this->values ) && array_key_exists( $key, $this->values ) )
				$option->value = $this->values[ $key ];
 		}
	}

	public function printTemplate( $contents = '' ) {
		$this->setSettingsSection();

		ob_start();
		if ( $this->version === '0.0.0' ) {
			printf( '<div class="description">The setting will be stored in <code>_%s_</code> option value. You can call <code>view</code> member attribute as well. This message will be disappeared when you set <code>version</code> value. ( ig. 1.0.0 )</div>', $this->key );
		}

		if ( $this->template ) {
			$template = array_shift( $this->template );

			if ( $template ) call_user_func( $template );
		}

		?>
		<form id="form-<?php echo $this->key ?>" method="POST" enctype="multipart/form-data">
			<?php settings_fields( $this->key ); ?>
			<?php do_settings_sections( $this->key ); ?>
			<?php submit_button( 'Submit', 'primary' ); ?>
		</form>
		<?php

		$contents = ob_get_clean();

		parent::printTemplate( $contents );
	}

	// ! Register Sections and Fields
	public function setSettingsSection() {
		foreach ( $this->sections as $sectionKey => $section ) {
			add_settings_section( $sectionKey, $section['name'], false, $this->key );

			# Set Fields
			foreach ( $section['fields'] as $fieldKey ) {
				// is Set Item
				if ( is_array( $fieldKey ) ) {
					$setKey = array_shift( $fieldKey );
					$name = $this->options[ $setKey ]->name;

					$setting = [];

					foreach( $fieldKey as $key ) {
						$setting[] = $this->options[ $key ];
					}

					$fieldKey = $setKey;

				} else {
					$setting = $this->options[ $fieldKey ];
					$name = ( $setting->type === 'html' ) ? '' : $setting->name;
				}

				add_settings_field( $fieldKey, $name, array( $this, 'callPrintSettingsField' ), $this->key, $sectionKey, [ $setting ] );
			}
		}
	}

	# Setting API Fields
	public function callPrintSettingsField( $setting ) {
		$setting = $setting[0];

		if ( !is_array( $setting ) ) {
			$setting->printSettingsField();
		} else {
			foreach ( $setting as $setting_ ) {
				$this->callPrintSettingsField( [ $setting_ ] );
			}
		}
	}

	// Save
	public function saveSettings() {
		if( !$this->checkIsSaving() ) return;

		if ( $this->save ) {
			call_user_func( $this->save );

		} else {
			$options = [];
			foreach( $this->options as $key => $value ) {
				if ( isset( $_POST[ $key ] ) )
					$options[ $key ] = $_POST[ $key ];
			}
			update_option( '_' . $this->key . '_', $options );
			$this->showMessage( 'Option Saved!' );
		}
	}
}