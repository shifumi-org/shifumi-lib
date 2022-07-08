<?php
/**
 * Initializing
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 * @todo       activation
 */

namespace TINCANNYSNC;

use TINCANNYSNC\FileSystem\Module\Storyline;
use TINCANNYSNC\FileSystem\Module\Captivate;
use TINCANNYSNC\FileSystem\Module\Captivate2017;
use TINCANNYSNC\FileSystem\Module\iSpring;
use TINCANNYSNC\FileSystem\Module\ArticulateRise;
use TINCANNYSNC\FileSystem\Module\ArticulateRise2017;
/* add Presenter360 tin can format */
use TINCANNYSNC\FileSystem\Module\Presenter360;
/* END Presenter360 */
/* add Lectora tin can format */
use TINCANNYSNC\FileSystem\Module\Lectora;
/* END Lectora */

if ( !defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class FileSystem {
	use FileSystem\traitModule;

	public function upgrade( $version ) {
		if ( ! $this->add_version_key() )
			return false;

		if ( ! $this->upgrade_2_1_2() )
			return false;

		return true;
	}

	private function add_version_key() {
		$modules = Database::get_modules( 'WHERE `version` IS NULL' );
		for( $i = 0; $i < 100; $i++ ) {
			if ( empty( $modules[ $i ] ) )
				break;

			switch( strtolower( $modules[ $i ]->type ) ) {
				case 'storyline' :
					$module = new Storyline( $modules[ $i ]->ID );
				break;

				case 'captivate' :
					$module = new Captivate( $modules[ $i ]->ID );
				break;

				case 'captivate2017' :
					$module = new Captivate2017( $modules[ $i ]->ID );
				break;

				case 'ispring' :
					$module = new iSpring( $modules[ $i ]->ID );
				break;

				case 'articulaterise' :
					$module = new ArticulateRise( $modules[ $i ]->ID );
				break;

				case 'ar2017' :
					$module = new ArticulateRise2017( $modules[ $i ]->ID );
				break;
				
				/* add Presenter360 tin can format */
				case 'presenter360' :
					$module = new Presenter360( $modules[ $i ]->ID );
					break;
				/* END Presenter360 */
				
				/* add Lectora tin can format */
				case 'Lectora' :
					$module = new Lectora( $modules[ $i ]->ID );
					break;
				/* END Lectora */

				default:
					$module = null;
					break;
			}

			if ( ! $module )
				continue;

			$module->add_nonce_block_code();

			Database::add_detail(
				$modules[ $i ]->ID,
				$modules[ $i ]->type,
				$modules[ $i ]->url,
				$modules[ $i ]->subtype,
				UNCANNY_REPORTING_VERSION
			);

			unset( $modules[ $i ] );
		}

		if ( count( $modules ) )
			return false;

		return true;
	}

	private function upgrade_2_1_2() {
		$modules = Database::get_modules( "WHERE `version` < '2.1.2'" );

		for( $i = 0; $i < 100; $i++ ) {
			if ( empty( $modules[ $i ] ) )
				break;

			switch( strtolower( $modules[ $i ]->type ) ) {
				case 'storyline' :
					$module = new Storyline( $modules[ $i ]->ID );
				break;

				case 'captivate' :
					$module = new Captivate( $modules[ $i ]->ID );
				break;

				case 'captivate2017' :
					$module = new Captivate2017( $modules[ $i ]->ID );
				break;

				case 'ispring' :
					$module = new iSpring( $modules[ $i ]->ID );
				break;

				case 'articulaterise' :
					$module = new ArticulateRise( $modules[ $i ]->ID );
				break;

				case 'ar2017' :
					$module = new ArticulateRise2017( $modules[ $i ]->ID );
				break;
				
				/* add Presenter360 tin can format */
				case 'presenter360' :
					$module = new Presenter360( $modules[ $i ]->ID );
					break;
				/* END Presenter360 */
				
				/* add Lectora tin can format */
				case 'Lectora' :
					$module = new Lectora( $modules[ $i ]->ID );
					break;
				/* END Lectora */

				default:
					$module = null;
					break;
			}

			if ( ! $module )
				continue;

			$module->replace_nonce_block_code();

			Database::add_detail(
				$modules[ $i ]->ID,
				$modules[ $i ]->type,
				$modules[ $i ]->url,
				$modules[ $i ]->subtype,
				UNCANNY_REPORTING_VERSION
			);

			unset( $modules[ $i ] );
		}

		if ( count( $modules ) )
			return false;

		return true;
	}
}
