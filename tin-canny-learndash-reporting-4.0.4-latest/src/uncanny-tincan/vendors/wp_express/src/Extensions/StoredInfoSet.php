<?php
/**
 *
 * WE\HtmlTrait Trait
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

namespace WE_TINCANNY\Extensions;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

trait StoredInfoSet {
	protected $transientKey, $transient, $save, $isSaving, $tempSetting, $isSet, $setItem;
	public $version = '0.0.0';
	protected $options = [];
	protected $sections = [];

	protected function initOptionSetting( $transientKey ) {
		$this->tempSetting = new \WE_TINCANNY\Extensions\Setting();

		if ( !$transientKey ) return;

		$this->transientKey = $transientKey;
		$this->transient = get_transient( $transientKey );
		$this->isSaving = $this->checkIsSaving();

		add_action( 'shutdown', array( $this, 'saveTransient' ) );
	}

	protected function getOptionSetting( $name ) {
		switch( $name ) {
			case 'setting' :
			case 'settings' :
			case 'option' :
			case 'options' :
			case 'meta' :
			case 'set' :
				if ( $this->isSet ) return $this->tempSetting;

				if ( empty( $this->options ) ) $this->addOption();

				end( $this->options );
				$optionKey = key( $this->options );

				return $this->options[ $optionKey ];
			break;
		}

		return false;
	}

	protected function setOptionSetting( $name, $value ) {
		$this->isSet = ( $this->version !== '0.0.0' && $this->transient && $this->transient[0] === $this->version && !$this->isSaving );

		if ( $this->isSet && !$this->options ) {
			$this->options = $this->transient[1];
			$this->sections = $this->transient[2];
		}

		switch( $name ) {
			case 'section' :
				if ( $this->isSet ) return true;

				return $this->addSection( $value );
			break;

			case 'save' :
				$this->save = $value;
				return true;
			break;

			case 'setting' :
			case 'settings' :
			case 'option' :
			case 'options' :
			case 'meta' :
				if ( $this->isSet ) return true;

				$this->addOption( $value );
				return true;
			break;

			case 'set' :
				if ( $this->isSet ) return true;

				# Add Option
				end( $this->options );
				$optionKey = key( $this->options );

				//// Create New Set Item
				if ( !$this->setItem ) {
					$this->setItem = $this->options[ $optionKey ];
					$this->options[ $optionKey ]->type = 'set';
				}

				$name = $this->setItem->name . ' ' . $value;

				$optionKey = $this->addOption( $name, $value );

				// Add into Section
				end( $this->sections );
				$sectionKey = key( $this->sections );

				end( $this->sections[ $sectionKey ][ 'fields' ] );
				$sectionFieldKey = key( $this->sections[ $sectionKey ][ 'fields' ] );

				array_pop( $this->sections[ $sectionKey ][ 'fields' ] );
				$sectionFieldKey--;

				if ( !is_array( $this->sections[ $sectionKey ][ 'fields' ][ $sectionFieldKey ] ) ) {
					$this->sections[ $sectionKey ][ 'fields' ][ $sectionFieldKey ] = [ $this->sections[ $sectionKey ][ 'fields' ][ $sectionFieldKey ] ];
				}

				$this->sections[ $sectionKey ][ 'fields' ][ $sectionFieldKey ][] = $optionKey;
				return true;
			break;
		}
	}

	private function checkIsSaving() {
		return false;
	}

	private function addSection( $sectionName = '' ) {
		if ( !$sectionName ) {
			$this->sections[] =  array( 'name' => '', 'fields' => [] );

		} else {
			$sectionKey = sanitize_title( $sectionName );

			if ( empty( $this->sections[ $sectionKey ] ) )
				$this->sections[ $sectionKey ] =  array( 'name' => $sectionName, 'fields' => [] );
		}

		end( $this->sections );
		return key( $this->sections );
	}

	private function addOption( $optionName = 'New Option', $isSet = false ) {
		if ( !$isSet ) $this->setItem = false;

		$optionKey = sanitize_title( $optionName );
		if ( in_array( $optionName, array_keys( $this->options ) ) ) return;

		$this->options[ $optionKey ] = new \WE_TINCANNY\Extensions\Setting( $optionName, $isSet );

		if ( !$this->sections )
			$this->addSection();

		end( $this->sections );
		$sectionKey = key( $this->sections );

		$this->sections[ $sectionKey ][ 'fields' ][] = $optionKey;

		return $optionKey;
	}

	public function saveTransient() {
		if ( $this->version !== '0.0.0' ) {
			$this->transient = get_transient( $this->transientKey );

			if ( !$this->transient || $this->transient[0] !== $this->version ) {
				$this->transient = [ $this->version, $this->options, $this->sections ];
				set_transient( $this->transientKey, $this->transient, HOUR_IN_SECONDS );
			}
		} else {
			$this->transient = false;
			delete_transient( $this->transientKey );
		}
	}
}