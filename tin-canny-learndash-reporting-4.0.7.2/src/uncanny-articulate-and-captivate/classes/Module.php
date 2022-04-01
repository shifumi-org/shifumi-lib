<?php
/**
 * New File Controller
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace TINCANNYSNC;

if ( ! defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class Module {
	public static function get_module( $item_id ) {
		$item   = Database::get_item( $item_id );
		$module = false;
		
		if ( ! $item ) {
			return false;
		}
		
		if ( ! $item['ID'] ) {
			return false;
		}

		$item['type'] = strtolower( str_replace( ' ', '', $item['type'] ) );

		switch ( $item['type'] ) {
			case 'articulaterise':
				$module = new FileSystem\Module\ArticulateRise( $item_id );
				break;
			case 'ar2017':
				$module = new FileSystem\Module\ArticulateRise2017( $item_id );
				break;
			case 'storyline':
				$module = new FileSystem\Module\Storyline( $item_id );
				break;
			case 'ispring':
				$module = new FileSystem\Module\iSpring( $item_id );
				break;
			case 'captivate':
				$module = new FileSystem\Module\Captivate( $item_id );
				break;
			case 'captivate2017':
				$module = new FileSystem\Module\Captivate2017( $item_id );
				break;
			/* add Presenter360 tin can format */
			case 'presenter360':
				$module = new FileSystem\Module\Presenter360( $item_id );
				break;
			/* END Presenter360 */
			
			/* add Lectora tin can format */
			case 'lectora':
				$module = new FileSystem\Module\Lectora( $item_id );
				break;
			/* END Lectora */
			case 'scorm':
				$module = new FileSystem\Module\Scorm( $item_id );
				break;
			case 'tincan':
				$module = new FileSystem\Module\Xapi( $item_id );
				break;
			default:
				$module = new FileSystem\Module\UnknownType( $item_id );
				break;
		}

		$url         = get_site_url() . $item['url'];
		$item['url'] = apply_filters( 'tincanny_module_url', $url, $item, $module );

		if ( $module ) {
			if ( is_ssl() ) {
				$item['url'] = str_replace( 'http://', 'https://', $item['url'] );
			}

			$module->set_url( $item['url'] );
			$module->set_name( $item['file_name'] );

			return $module;
		}

		return false;
	}
}

