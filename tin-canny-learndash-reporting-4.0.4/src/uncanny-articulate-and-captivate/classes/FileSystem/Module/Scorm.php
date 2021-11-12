<?php
/**
 * Storyline Controller
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage Embed Articulate Storyline and Adobe Captivate
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace TINCANNYSNC\FileSystem\Module;

if ( !defined( 'UO_ABS_PATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class Scorm extends \TINCANNYSNC\FileSystem\absModule {
	private static $storyline_files = array( 'player.html', 'index_lms.html', 'story.html', 'engage.html', 'quiz.html', 'presentation.html', 'interaction.html', 'index.html' );
	
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function __construct( $item_id ) {
		parent::__construct( $item_id );
		$this->set_type( 'Scorm' );
	}
	
	// implement
	protected function get_registering_url() {
		//check in tincan.xml exist then get launch file from it.
		$target = $this->get_target_dir();
		$imsmanifestxml_subdir = $this->find_manifestxml_file( $target );
		
		if ( empty( $imsmanifestxml_subdir ) ) {
			$imsmanifest_file = $target . DIRECTORY_SEPARATOR . "imsmanifest.xml";
		} else {
			$imsmanifest_file = $target . DIRECTORY_SEPARATOR . $imsmanifestxml_subdir . DIRECTORY_SEPARATOR . "imsmanifest.xml";
		}
		
		if ( file_exists( $imsmanifest_file ) ) {
			$scorm_version_ar = self::uo_get_scormversion( $imsmanifest_file );
			
			$scorm_version = 'scorm1.2';
			
			if (isset($scorm_version_ar['schemaversion'])) {
				if ( (strpos($scorm_version_ar['schemaversion'], '2004') !== false) ||  (trim($scorm_version_ar['schemaversion']) == 'CAM 1.3') ) {
					$scorm_version = 'scorm2004';
				}
			}

			$SCOdata = self::uo_read_imsmanifestfile( $imsmanifest_file );
			$ORGdata = self::uo_get_org_data( $imsmanifest_file );
			
			$i = 0;
			$page = [];
			$title = [];
			$launch_title = '';
			foreach ( $SCOdata as $identifier => $SCO ) {
				$page[ $i ] = $SCO['href'];
				$title[ $i ] = $SCO['title'];
				$i ++;
			}
			$launch_path = '';
			$is_easygenerator = false;

			foreach ( $ORGdata as $identifier => $ORG ) {
				if( isset( $ORG['org_identifier'] ) && $ORG['org_identifier'] === "easygenerator") {
					$is_easygenerator = true;
				}
				if ( $ORG['identifierref'] != '' ) {
					$key_ref = 0;
					foreach ( $SCOdata as $identifier_temp => $SCO ) {
						if ( $identifier_temp == $identifier ) {
							break;
						} else {
							$key_ref ++;
						}
					}

					if ( $key_ref >= 0 ) {
						$launch_file = $page[ $key_ref ];
						$launch_title = $title[ $key_ref ];

						$file_name   = explode( '?', $launch_file );
						$launch_path = dirname( $imsmanifest_file ) . DIRECTORY_SEPARATOR . $file_name[0];
						$launch_url  = $this->get_target_url() . '/' . $launch_file;
					}
					if ( ! empty( $launch_path ) && file_exists( $launch_path ) ) {
						break;
					}
				}
			}
			if( $is_easygenerator ) {
				$this->easygenerator_support( $launch_path );
			}
			// generate own launch file for wrapping up.
			ob_start();
			?><html><head>
			<?php if( $scorm_version != 'scorm1.2'){ ?>
				<script type="text/javascript">
					var TC_COURSE_ID, TC_COURSE_NAME, TC_COURSE_DESC, TC_RECORD_STORES;
					var getUrl = window.location;
					var baseUrl = getUrl .protocol + "//" + getUrl.host + getUrl.pathname;
					TC_COURSE_ID = baseUrl;
					TC_COURSE_NAME = {
						"en-US": "<?php echo $launch_title;?>"
					};
					TC_COURSE_DESC = {
						"en-US": "Course Description."
					};
					TC_RECORD_STORES = [
					];
				</script>
				<script src="<?php echo plugins_url('/src/assets/scripts/scormdriver.js', UO_REPORTING_FILE)."?sv=2004&v=".UNCANNY_REPORTING_VERSION; ?>" type="text/javascript"></script>
			<?php } else { ?>
				<script type="text/javascript">
					var TC_COURSE_ID, TC_COURSE_NAME, TC_COURSE_DESC, TC_RECORD_STORES;
					var getUrl = window.location;
					var baseUrl = getUrl .protocol + "//" + getUrl.host + getUrl.pathname;
					TC_COURSE_ID = baseUrl;
					TC_COURSE_NAME = {
						"en-US": "<?php echo $launch_title;?>"
					};
					TC_COURSE_DESC = {
						"en-US": "Course Description."
					};
					TC_RECORD_STORES = [
					];

				</script>
				<script src="<?php echo plugins_url('/src/assets/scripts/scormdriver.js', UO_REPORTING_FILE)."?sv=1.2&v=".UNCANNY_REPORTING_VERSION; ?>" type="text/javascript"></script>
			<?php } ?>
		</head>
			<body>
			<?php
			if( $scorm_version != 'scorm1.2' ) {
				//echo '<frameset frameborder="0" framespacing="0" border="0" rows="*" cols="*" onbeforeunload="API_1484_11.Terminate(\'\');" onunload="API_1484_11.Terminate(\'\');">';
				echo '<iframe src="'.utf8_encode($launch_url).'" width="100%" style="border: none; width: 100%; height: 100%" height="100%" name="course" onbeforeunload="API_1484_11.Terminate(\'\');" onunload="API_1484_11.Terminate(\'\');"></iframe>';
				//echo '</frameset>';
			} else {
				//echo '<frameset frameborder="0" framespacing="0" border="0" rows="*" cols="*" onbeforeunload="API.LMSFinish(\'\');" onunload="API.LMSFinish(\'\');">';
				echo '<iframe src="'.utf8_encode($launch_url).'" width="100%" style="border: none; width: 100%; height: 100%" height="100%" name="course" onbeforeunload="API.LMSFinish(\'\');" onunload="API.LMSFinish(\'\');"></iframe>';
				//echo '</frameset>';
			}
			echo "</body></html>";

			$new_launch_file_content = ob_get_clean();
			file_put_contents( $target . '/tc_index.html', $new_launch_file_content );
			return $this->get_target_url() . '/' . 'tc_index.html';
		}
		
		// force index_lms.html before finding other files. On some OS directory list is not sorted
		if ( $return_file = $this->in_array_search( 'index_lms.html', $this->get_dir_contents() ) ) {
			
			return $this->get_target_url() . '/' . $return_file;
		}
		
		if ( $return_file = $this->in_array_search( self::$storyline_files, $this->get_dir_contents() ) ) {
			
			return $this->get_target_url() . '/' . $return_file;
		}
		return false;
	}

	protected function easygenerator_support( $launch_file ){
		$addition = '<script>function uoparseURL(t){var e,r,a,o=String(t).split("?"),i={};if(2===o.length)for(e=o[1].split("&"),a=0;a<e.length;a+=1)2===(r=e[a].split("=")).length&&r[0]&&(i[r[0]]=decodeURIComponent(r[1]));return{path:o[0],params:i}}var uo_params=uoparseURL(window.parent.location.href).params,_uo_reservedQSParams={statementId:!0,voidedStatementId:!0,verb:!0,object:!0,registration:!0,context:!0,actor:!0,since:!0,until:!0,limit:!0,authoritative:!0,sparse:!0,instructor:!0,ascending:!0,continueToken:!0,agent:!0,activityId:!0,stateId:!0,profileId:!0,activity_platform:!0,grouping:!0,"Accept-Language":!0,endpoint:!0};XMLHttpRequest.prototype.origOpen=XMLHttpRequest.prototype.open,XMLHttpRequest.prototype.open=function(){var t=arguments[1],e={},r=uo_params.auth;for(i in uo_params)uo_params.hasOwnProperty(i)&&(_uo_reservedQSParams.hasOwnProperty(i)||((e=e||{})[i]=uo_params[i]));for(g in f=new Array,e)f.push(g+"="+e[g]);0<f.length&&(t+=(-1<t.indexOf("?")?"&":"?")+f.join("&")),arguments[1]=t,this.origOpen.apply(this,arguments),this.setRequestHeader("authorization",r)};</script></head>';
		if ( file_exists( $launch_file ) ) {
			$contents = file_get_contents( $launch_file );
			$contents = str_replace( "</head>", $addition, $contents );
			file_put_contents( $launch_file, $contents );
		}
	}
	protected function add_tincan_support() {
		
		$this->add_nonce_block_code();
		
		$target = $this->get_target_dir();
		
		$file_js = 'assets/scripts/module_supports/tc-config.js';
		$file    = $target . '/tc-config.js';
		//copy( SnC_PLUGIN_DIR . $file_js, $file );
	}
	
	// Replace scormdriver.js to a working version
	public function add_nonce_block_code() {
		$target = $this->get_target_dir();
		$scormdriver = $target . '/scormdriver/scormdriver.js';
	}
	
	public function replace_nonce_block_code() {
		return;
		$target = $this->get_target_dir();
		
		$scormdriver = $target . '/scormdriver/scormdriver.js';
		if ( file_exists( $scormdriver ) ) {
			$contents = file_get_contents( $scormdriver );
			$contents = str_replace( self::NONCE_BLOCK_B212, self::NONCE_BLOCK, $contents );
			file_put_contents( $scormdriver, $contents );
		}
	}
	
	private static function find_manifestxml_file( $dir ) {
		$imsmanifestxml_file = $dir . DIRECTORY_SEPARATOR . "imsmanifest.xml";
		
		if ( file_exists( $imsmanifestxml_file ) ) {
			return "";
		} else {
			$dirlist = scandir( $dir );
			foreach ( $dirlist as $d ) {
				if ( $d != "." && $d != ".." ) {
					$imsmanifestxml_file = $dir . DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR . "imsmanifest.xml";
					if ( file_exists( $imsmanifestxml_file ) ) {
						return $d;
					}
				}
			}
		}
		
		return 0;
	}
	
	private static function uo_resolveIMSManifestDependencies( $identifier ) {
		global $resourceData;
		
		$files = $resourceData[ $identifier ]['files'];
		if ( isset( $resourceData[ $identifier ]['dependencies'] ) ) {
			$dependencies = $resourceData[ $identifier ]['dependencies'];
			if ( is_array( $dependencies ) ) {
				foreach ( $dependencies as $d => $dependencyidentifier ) {
					//var_dump(self::uo_resolveIMSManifestDependencies( $dependencyidentifier ));
					//$files = array_merge( $files, self::uo_resolveIMSManifestDependencies( $dependencyidentifier ) );
					unset( $resourceData[ $identifier ]['dependencies'][ $d ] );
				}
				$files = array_unique( $files );
			}
		}
		
		return $files;
	}
	
	private static function uo_get_org_data( $manifestfile ) {
		
		// load the imsmanifest.xml file
		$dom                     = new \DomDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->load( $manifestfile );
		
		// adlcp namespace
		$manifest = $dom->getElementsByTagName( 'manifest' );
		$adlcp    = $manifest->item( 0 )->getAttribute( 'xmlns:adlcp' );
		
		// READ THE RESOURCES LIST
		
		// get the organizations element
		$organizationsList = $dom->getElementsByTagName( 'organizations' );
		
		// iterate over each of the organizations
		
		foreach ( $organizationsList as $organizationsListRow ) {
			$organizationList = $organizationsListRow->getElementsByTagName( 'organization' );
			foreach ( $organizationList as $organizationListRow ) {
				$org_identifier = $organizationListRow->getAttribute( 'identifier' );
				$itemsList      = $organizationListRow->getElementsByTagName( 'item' );
				foreach ( $itemsList as $itemsListRow ) {
					// decode the attributes
					// e.g. <item identifier="I_A001" identifierref="A001">
					
					$identifier      = $itemsListRow->getAttribute( 'identifier' );
					$identifierref   = $itemsListRow->getAttribute( 'identifierref' );
					$titleTag        = $itemsListRow->getElementsByTagName( 'title' );
					$title           = $titleTag->item( 0 )->nodeValue;
					$masteryscoreTag = $itemsListRow->getElementsByTagNameNS( $adlcp, 'masteryscore' );
					$launchdataTag   = $itemsListRow->getElementsByTagNameNS( $adlcp, 'datafromlms' );
					
					// table row
					$ORGdata[ $identifier ]['org_identifier'] = $org_identifier;
					$ORGdata[ $identifier ]['identifier']     = $identifier;
					$ORGdata[ $identifier ]['identifierref']  = $identifierref;
					$ORGdata[ $identifier ]['name']           = $title;;
				}
			}
		}
		
		return ( $ORGdata );
	}
	
	private static function uo_read_imsmanifestfile( $manifestfile ) {
		// central array for resource data
		global $resourceData;
		
		// load the imsmanifest.xml file
		$xmlfile                     = new \DomDocument;
		$xmlfile->preserveWhiteSpace = FALSE;
		$xmlfile->load( $manifestfile );
		
		// adlcp namespace
		$manifest = $xmlfile->getElementsByTagName( 'manifest' );
		$adlcp    = $manifest->item( 0 )->getAttribute( 'xmlns:adlcp' );
		
		// READ THE RESOURCES LIST
		// array to store the results
		$resourceData = [];
		
		// get the list of resource element
		$resourceList = $xmlfile->getElementsByTagName( 'resource' );
		$r            = 0;
		
		foreach ( $resourceList as $rtemp ) {
			
			// decode the resource attributes
			
			$identifier                          = $resourceList->item( $r )->getAttribute( 'identifier' );
			$resourceData[ $identifier ]['type'] = $resourceList->item( $r )->getAttribute( 'type' );
			$resourceData[ $identifier ]['base'] = $resourceList->item( $r )->getAttribute( 'xml:base' );
			if ( $resourceList->item( $r )->hasAttribute( 'adlcp:scormtype' ) ) {
				$resourceData[ $identifier ]['scormtype'] = $resourceList->item( $r )->getAttribute( 'adlcp:scormtype' );
			}
			if ( $resourceList->item( $r )->hasAttribute( 'adlcp:scormType' ) ) {
				$resourceData[ $identifier ]['scormtype'] = $resourceList->item( $r )->getAttribute( 'adlcp:scormType' );
			}
			$resourceData[ $identifier ]['href'] = $resourceList->item( $r )->getAttribute( 'href' );
			
			// list of files
			$fileList = $resourceList->item( $r )->getElementsByTagName( 'file' );
			
			$f = 0;
			
			foreach ( $fileList as $ftemp ) {
				$resourceData[ $identifier ]['files'][ $f ] = $fileList->item( $f )->getAttribute( 'href' );
				$f ++;
			}
			
			// list of dependencies
			$dependencyList = $resourceList->item( $r )->getElementsByTagName( 'dependency' );
			$d              = 0;
			foreach ( $dependencyList as $dtemp ) {
				$resourceData[ $identifier ]['dependencies'][ $d ] = $dependencyList->item( $d )->getAttribute( 'identifierref' );
				$d ++;
			}
			$r ++;
		}
		
		// resolve resource dependencies to create the file lists for each resource
		foreach ( $resourceData as $identifier => $resource ) {
			//$resourceData[ $identifier ]['files'] = self::uo_resolveIMSManifestDependencies( $identifier );
		}
		
		// READ THE ITEMS LIST
		// array to store the results
		
		$itemData = [];
		
		// get the list of item elements
		$itemList = $xmlfile->getElementsByTagName( 'item' );
		
		$i = 0;
		foreach ( $itemList as $itemp ) {
			
			// decode the item attributes and sub-elements
			
			$identifier                               = $itemList->item( $i )->getAttribute( 'identifier' );
			$itemData[ $identifier ]['identifierref'] = $itemList->item( $i )->getAttribute( 'identifierref' );
			$itemData[ $identifier ]['parameters']    = $itemList->item( $i )->getAttribute( 'parameters' );
			
			$itemData[ $identifier ]['title'] = $itemList->item( $i )->getElementsByTagName( 'title' )->item( 0 )->nodeValue;
			$i ++;
		}
		
		// PROCESS THE ITEMS LIST TO FIND SCOS
		
		// array for the results
		$SCOdata = [];
		
		// loop through the list of items
		
		foreach ( $itemData as $identifier => $item ) {
			
			// find the linked resource
			$identifierref = $item['identifierref'];
			
			// is the linked resource a SCO? if not, skip this item
			if ( isset( $resourceData[ $identifierref ]['scormtype'] ) ) {
				if ( strtolower( $resourceData[ $identifierref ]['scormtype'] ) != 'sco' ) {
					continue;
				}
				
				// save data that we want to the output array
				$SCOdata[ $identifier ]['title'] = $item['title'];
				$SCOdata[ $identifier ]['base'] = isset( $resourceData[ $identifierref ]['base'] ) ? $resourceData[ $identifierref ]['base'] : '' ;
				$SCOdata[ $identifier ]['href'] = $SCOdata[ $identifier ]['base'] . $resourceData[ $identifierref ]['href'];
				if ( isset( $item['parameters'] ) ) {
					$SCOdata[ $identifier ]['href'] = $SCOdata[ $identifier ]['href'] . $item['parameters'];
				}
				$SCOdata[ $identifier ]['files'] = $resourceData[ $identifierref ]['files'];
			}
		}
		
		return $SCOdata;
	}
	
	private static function uo_get_scormversion( $manifestfile ) {
		// load the imsmanifest.xml file
		$dom                     = new \DomDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->load( $manifestfile );
		// // adlcp namespace
		$manifest = $dom->getElementsByTagName( 'manifest' );
		$adlcp    = $manifest->item( 0 )->getAttribute( 'xmlns:adlcp' );
		
		foreach ( $manifest as $manifestEl ) {
			$metadata = $manifestEl->getElementsByTagName( "metadata" );
			if ( $metadata->item( 0 )->nodeValue != '' ) {
				$schema                         = $metadata->item( 0 )->getElementsByTagName( "schema" );
				$schemaversion                  = $metadata->item( 0 )->getElementsByTagName( "schemaversion" );
				$adlcplocation                  = $metadata->item( 0 )->getElementsByTagNameNS( $adlcp, "location" );
				$scorm_version['schema']        = $schema->item( 0 )->nodeValue;
				$scorm_version['schemaversion'] = $schemaversion->item( 0 )->nodeValue;
				$scorm_version['adlcplocation'] = @$adlcplocation->item( 0 )->textContent;
			} else {
				// adlcp namespace
				$adlcp = $manifest->item( 0 )->getAttribute( 'xmlns:adlcp' );
				// get the organizations element
				$organizationsList = $manifestEl->getElementsByTagName( 'organizations' );
				foreach ( $organizationsList as $organizationsListRow ) {
					$organizationList = $organizationsListRow->getElementsByTagName( 'organization' );
					foreach ( $organizationList as $organizationListRow ) {
						$metadata = $organizationListRow->getElementsByTagName( 'metadata' );
						foreach ( $metadata as $metadataEl ) {
							$schema                         = $metadataEl->getElementsByTagName( "schema" );
							$schemaversion                  = $metadataEl->getElementsByTagName( "schemaversion" );
							$adlcplocation                  = $metadataEl->getElementsByTagNameNS( $adlcp, "location" );
							$scorm_version['schema']        = $schema->item( 0 )->textContent;
							$scorm_version['schemaversion'] = $schemaversion->item( 0 )->textContent;
							$scorm_version['adlcplocation'] = $adlcplocation->item( 0 )->textContent;
						}
					}
				}
			}
		}
		
		return $scorm_version;
	}
}
