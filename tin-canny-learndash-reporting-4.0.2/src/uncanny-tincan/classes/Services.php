<?php
/**
 * Services : H5P and Storyline
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 * @todo       descriptions
 */

namespace UCTINCAN;

use uncanny_learndash_reporting\Config;

if ( ! defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Services {
	use Modules;

	private static $snc_upload_dir;

	public function __construct() {
		if ( ! is_admin() ) {
			add_action( 'wp', array( $this, 'activate_mark_complete_control' ) );
		}

		# Ajax (Storyline)
		add_action( 'wp_ajax_Check Storyline Completion', array( $this, 'ajax_check_slide_completion' ) );
		add_action( 'wp_ajax_Check Captivate Completion', array( $this, 'ajax_check_slide_completion' ) );
		add_action( 'wp_ajax_Check iSpring Completion', array( $this, 'ajax_check_slide_completion' ) );
		add_action( 'wp_ajax_Check ArticulateRise Completion', array( $this, 'ajax_check_slide_completion' ) );
		add_action( 'wp_ajax_Check ArticulateRise2017 Completion', array( $this, 'ajax_check_slide_completion' ) );

		add_action( 'wp_ajax_Check H5P Completion', array( $this, 'ajax_check_h5p_completion' ) );
		/* add Presenter360 tin can format */
		add_action( 'wp_ajax_Check Presenter360 Completion', array( $this, 'ajax_check_slide_completion' ) );
		/* END Presenter360 */
		/* add Lectora tin can format */
		add_action( 'wp_ajax_Check Lectora Completion', array( $this, 'ajax_check_slide_completion' ) );
		/* END Lectora */
		add_action( 'wp_ajax_uncanny-snc-mark-completed', array( $this, 'mark_content_completed' ) );
	}

	// wp
	public function activate_mark_complete_control() {
		global $post;

		if ( empty( $post ) ) {
			return;
		}

		$post_option   = ( is_object( $post ) ) ? get_post_meta( $post->ID, '_WE-meta_', true ) : array();
		$global_option = get_option( 'disable_mark_complete_for_tincan', 'yes' );


		if ( "" === $post_option ) {
			$post_option = array();
		}
		// Post Option Default : Use Global Setting
		if ( ! is_array( $post_option ) || ! isset( $post_option['restrict-mark-complete'] ) ) {
			$post_option['restrict-mark-complete'] = 'Use Global Setting';
		}

		switch ( $post_option['restrict-mark-complete'] ) {
			case 'Use Global Setting' :
				if ( $global_option == 'no' ) {
					return;
				}
				break;

			case 'No' :
				return;
				break;
		}

		// If "Capture Tin Can and SCORM data" is disabled
		$is_capture_enabled = get_option( 'show_tincan_reporting_tables', 'yes' );
		if( $is_capture_enabled == 'no' ) {
			return;
		}

		// trait: Modules
		if ( ! $this->prepare_modules() ) {
			return;
		}

		// Check if the Tin Canny Gutenberg Block is being used
		// First create variable where we're going to save the result
		$tincanny_gutenberg_block_is_being_used = false;

		// Check if Gutenberg exists (just in case the user
		// is using WP < 5.0 )
		if ( function_exists( 'has_blocks' ) && function_exists( 'parse_blocks' ) ) {
			// Check if the post content has blocks
			if ( has_blocks( $post->post_content ) ) {
				// Get all the blocks
				$blocks = parse_blocks( $post->post_content );
				$tincanny_gutenberg_block_is_being_used = self::get_all_inner_block( $blocks, 'tincanny/content' );
				if ( empty( $tincanny_gutenberg_block_is_being_used ) ) {
					$tincanny_gutenberg_block_is_being_used = false;
				}
			}
		}

		global $post;

		if ( is_a( $post, 'WP_Post' ) &&
			 (
				 has_shortcode( $post->post_content, 'h5p' ) ||
				 has_shortcode( $post->post_content, 'vc_snc' ) ||
				 $tincanny_gutenberg_block_is_being_used
			 )
		) {

			# Extract H5P, Storyline Shortcode
			$this->extract_tincanny_content( $post->post_content );

			# Script
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			# Set js var for completion
			add_action( 'wp_footer', array( $this, 'print_js_completion_vars' ), 100 );

			# Set custom label for mark complete button
			$custom_label  = get_option( 'label_mark_complete_for_tincan', '' );
			if( ! empty( $custom_label ) ){
				add_filter( 'learndash_get_label', function( $label, $key ) {
				    if( 'button_mark_complete' === $key ){
					    $custom_label  = get_option( 'label_mark_complete_for_tincan', '' );
					    return $custom_label;
                    }

					return $label;
                }, 10, 2 );
            }
		}

	}

	public static function get_all_inner_block( $blocks, $block_code ) {
		$block_is_on_page = [];
		foreach ( $blocks as $block ) {
			if ( $block_code === $block['blockName'] ) {
				$block_is_on_page[] = $block;
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				$inner_block_is_on_page = self::get_all_inner_block( $block['innerBlocks'], $block_code );
				if ( ! empty( $inner_block_is_on_page ) ) {
					$block_is_on_page = array_merge( $block_is_on_page, $inner_block_is_on_page );
				}
			}
		}

		return $block_is_on_page;
	}

	private function get_snc_upload_dir() {
		if ( ! self::$snc_upload_dir ) {
			$wp_upload_dir        = wp_upload_dir();
			self::$snc_upload_dir = $wp_upload_dir['basedir'] . '/' . SnC_UPLOAD_DIR_NAME;
		}

		return self::$snc_upload_dir;
	}

	// Extract H5P and Storyline/Articulate Shortcodes
	// the_content
	public function extract_tincanny_content( $content ) {
		// Check if the page has Tin Canny content
		// Create variable where we're going to save the result
		$has_tincanny_content = false;

		// Check for the shortcode
		// Create variable where we're going to save the result
		$tincanny_shortcode_is_being_used = false;
		preg_match_all( self::$PATTERN_SHORTCODE_ID, $content, $match_id );
		$tincanny_shortcode_is_being_used = ! empty( $match_id[4] );

		// Check if the Tin Canny Gutenberg Block is being used
		// Create variable where we're going to save the result
		$tincanny_gutenberg_block_is_being_used = false;

		// Check if Gutenberg exists (just in case the user
		// is using WP < 5.0 )
		$tincanny_blocks = [];

		if ( function_exists( 'has_blocks' ) && function_exists( 'parse_blocks' ) ) {
			// Check if the post content has blocks
			if ( has_blocks( $content ) ) {
				// Get all the blocks
				$blocks = parse_blocks( $content );
                $tincanny_blocks = self::get_all_inner_block( $blocks, 'tincanny/content');
				if ( ! empty( $tincanny_blocks ) ) {
					// Change value of variable
					$tincanny_gutenberg_block_is_being_used = TRUE;

					// Add this block to the list of Tin Canny blocks
					$tincanny_blocks = $tincanny_blocks;
				}
			}
		}

		// Check if it has either the shortcode or the gutenberg block
		// Otherwise do nothing
		$has_tincanny_content = $tincanny_shortcode_is_being_used || $tincanny_gutenberg_block_is_being_used;

		if ( $has_tincanny_content ) {
			// Shortcode matches
			if ( $tincanny_shortcode_is_being_used ) {
				foreach ( $match_id[4] as $key => $content_id ) {
					if ( empty( $content_id ) || ! is_numeric( $content_id ) ) {
						continue;
					}

					switch ( $match_id[1][ $key ] ) {
						case 'h5p' :
							if ( $this->available['H5P'] ) {
								$this->set_h5p_module_info( $content_id );
							}

							break;

						case 'vc_snc' :
							if ( $this->available['SnC'] ) {
								$this->set_slide_module_info( $content_id );
							}

							break;
					}
				}
			}

			// Blocks
			foreach ( $tincanny_blocks as $block ) {
				// Check if it has the content ID parameter
				if ( isset( $block['attrs']['contentId'] ) ) {
					// Get content ID
					$content_id = $block['attrs']['contentId'];

					// Set slide info
					$this->set_slide_module_info( $content_id );
				}
			}

			$this->set_completion();
		}
	}

	private function set_h5p_module_info( $id ) {
		global $wpdb;

		$content_table_name = $wpdb->prefix . self::$TABLE_H5P_CONTENTS;
		$lib_table_name     = $wpdb->prefix . self::$TABLE_H5P_LIBRARY;

		$query = $wpdb->prepare( "
			SELECT library.`name` FROM {$content_table_name} contents
			INNER JOIN {$lib_table_name} library ON contents.`library_id` = library.`id`
			WHERE contents.`id` = %s
			",
			$id
		);

		$library_name = $wpdb->get_var( $query );

		$this->module_info['H5P']['contents'][ $id ] = $id;
	}

	// todo Check Availability into snc classes
	private function set_slide_module_info( $item_id ) {
		$Module = \TINCANNYSNC\Module::get_module( $item_id );

		if ( $Module && $Module->is_available() ) {
			$module_type                                               = $Module->get_type();
			$module_type                                               = $module_type === 'AR2017' ? 'ArticulateRise2017' : $module_type;
			$this->module_info[ $module_type ]['contents'][ $item_id ] = $item_id;
		}
	}

	private function set_completion() {
		global $post;
		$user_id   = get_current_user_id();
		$course_id = false;

		if ( ! $user_id ) {
			return;
		}

		if ( function_exists( 'learndash_get_course_id' ) ) {
			$course_id = learndash_get_course_id( $post->ID );
		}

		$mark_complete_settings = '';
		$global_option = get_option( 'disable_mark_complete_for_tincan', 'yes' );

		$has_complete_setting = 0;
		$post_meta            = ( $post->ID ) ? get_post_meta( $post->ID, '_WE-meta_', true ) : array();

		if ( ! empty( $post_meta['completion-condition'] ) ) {
			$has_complete_setting = 1;
		}

		if ( "" === $post_meta ) {
			$post_meta = array();
		}
		// Post Option Default : Use Global Setting
		if ( ! is_array( $post_meta ) || ! isset( $post_meta['restrict-mark-complete'] ) ) {
			$post_meta['restrict-mark-complete'] = 'Use Global Setting';
		}

		switch ( $post_meta['restrict-mark-complete'] ) {
			case 'Use Global Setting' :
				$mark_complete_settings = $global_option;
				break;
			default:
				$mark_complete_settings = $post_meta['restrict-mark-complete'];
				break;
		}
		$is_already_completed = true;
		if ( $mark_complete_settings == 'remove' || $mark_complete_settings == 'autoadvance' ) {
			if( 'sfwd-lessons' === $post->post_type ) {
				$is_already_completed = learndash_is_lesson_complete( $user_id, $post->ID, $course_id );
			} elseif ( 'sfwd-topic' === $post->post_type ) {
				$is_already_completed = learndash_is_topic_complete( $user_id, $post->ID );
			}
		}
		$database = new Database\Completion();

		// H5P Completion
		foreach ( $this->module_info['H5P']['contents'] as $id ) {
			if ( $database->get_H5P_completion( $id, $course_id, $post->ID, $user_id ) ) {
				if( $is_already_completed ) {
					$this->module_info['H5P']['complete'][ $id ] = TRUE;
				}
			}
		}

		$module_types = array(
			'Storyline',
			'Captivate',
			'Captivate2017',
			'iSpring',
			'ArticulateRise',
			'ArticulateRise2017',
            /* add Presenter360 tin can format */
			'Presenter360',
			/* END Presenter360 */
            /* add Lectora tin can format */
			'Lectora'
			/* END Lectora */
		);

		foreach ( $module_types as $type ) {
			foreach ( $this->module_info[ $type ]['contents'] as $id ) {
				$Module = \TINCANNYSNC\Module::get_module( $id );

				if ( $Module->is_available() ) {
					$module_url = $Module->get_url();

					if ( $database->get_completion_by_URL( $module_url, $course_id, $post->ID ) ) {
						if( $is_already_completed ) {
							$this->module_info[ $type ]['complete'][ $id ] = TRUE;
						}
					}
				}
			}
		}
	}

	# TODO : Course ID
	public function ajax_check_slide_completion() {
		$completion = $this->check_slide_completion( $_POST['URL'] );

		if ( $completion ) {
			echo $completion;
		}

		wp_die();
	}

	public function check_slide_completion( $url ) {
		$database  = new Database\Completion();
		$course_id = false;
		parse_str( $url, $decoded );
		$global_option = get_option( 'disable_mark_complete_for_tincan', 'yes' );

		$url_converted = remove_query_arg( array(
			'endpoint',
			'auth',
			'activity_id',
			'actor',
			'client',
			'tincan',
            'course_id',
            'base_url',
            'nonce',
		), $url );


		if( isset( $decoded['course_id'] ) && $decoded['course_id'] !== 0 ){
		    $course_id = $decoded['course_id'];
			$lesson_id = $this->get_lesson_id_from_url( $url );
        } else {
			if ( $lesson_id = $this->get_lesson_id_from_url( $url ) ) {
				if ( function_exists( 'learndash_get_course_id' ) ) {
					$course_id = learndash_get_course_id( $lesson_id );
				}
			}
        }

		if ( $database->get_completion_by_URL( $url_converted, $course_id, $lesson_id ) ) {
			$matches = $this->get_slide_id_from_url( $url_converted );
			$post = get_post( $lesson_id );
			$post_meta            = ( $post->ID ) ? get_post_meta( $post->ID, '_WE-meta_', true ) : array();
			if ( "" === $post_meta ) {
				$post_meta = array();
			}
			// Post Option Default : Use Global Setting
			if ( ! is_array( $post_meta ) || ! isset( $post_meta['restrict-mark-complete'] ) ) {
				$post_meta['restrict-mark-complete'] = 'Use Global Setting';
			}

			switch ( $post_meta['restrict-mark-complete'] ) {
				case 'Use Global Setting' :
					$mark_complete_settings = $global_option;
					break;
				default:
					$mark_complete_settings = $post_meta['restrict-mark-complete'];
					break;
			}
			$is_already_completed = false;

			// Check if its a single page course from Toolkit Pro.
            // If it is true then replace post with course lesson
			if( class_exists('\uncanny_pro_toolkit\OnePageCourseStep') ) {
				$uncanny_active_classes = get_option( 'uncanny_toolkit_active_classes', array() );
				if ( ! empty( $uncanny_active_classes ) && is_array( $uncanny_active_classes ) && key_exists( 'uncanny_pro_toolkit\OnePageCourseStep', $uncanny_active_classes ) ) {
					$single_page_tutorial = get_post_meta( $post->ID, 'uo_single_page_course', TRUE );
					if ( $single_page_tutorial ) {
						$courseSteps = new \LDLMS_Course_Steps( $post->ID );
						$courseSteps->load_steps();
						if ( $courseSteps->get_steps_count() === 1 ) {
							$lessons     = $courseSteps->get_steps( 't' );
							$lesson_id   = $lessons['sfwd-lessons'][0];
							$course_post = $post;
							$post        = get_post( $lesson_id );
						}
					}
				}
			}
			if ( $mark_complete_settings == 'remove' ) {
				$current_user = wp_get_current_user();
				$user_id      = $current_user->ID;
				if( 'sfwd-lessons' === $post->post_type ) {
					$is_already_completed = learndash_is_lesson_complete( $user_id, $post->ID, $course_id );
				} elseif ( 'sfwd-topic' === $post->post_type ) {
					$is_already_completed = learndash_is_topic_complete( $user_id, $post->ID );
				}
				if ( ! $is_already_completed ) {
					learndash_process_mark_complete( $user_id, $post->ID, FALSE, $course_id );
				}
			}
			if ( 'autoadvance' === $mark_complete_settings ) {
				learndash_process_mark_complete( $user_id, $post->ID, FALSE, $course_id );
				$previous_link = isset( $_POST['lesson_link'] ) ? $_POST['lesson_link'] : '';
				$url           = learndash_next_post_link( $previous_link, TRUE, $post );
				if( $url === $previous_link ){
					if ( 'sfwd-topic' === $post->post_type ) {
						if ( \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' ) == 'yes' ) {
							$course_id = learndash_get_course_id( $post->ID );
							$lesson_id = learndash_course_get_single_parent_step( $course_id, $post->ID );
						} else {
							$lesson_id = learndash_get_setting( $post, 'lesson' );
						}
						$url = get_permalink( $lesson_id );
					} else {
						$course_id = learndash_get_course_id( $post );
						$url      = learndash_next_global_quiz( true, null, $course_id );
					}
				}

				if ( ! empty( $url ) ) {
					return json_encode( [ 'redirect_to' => $url , 'content_id' => $matches, 'completion_matched' => true ] );
				}
			}


			return $matches[1];
		}

		return false;
	}

	public function ajax_check_h5p_completion() {
		echo $this->check_h5p_completion( $_POST['lesson_id'] );
		wp_die();
	}

	public function check_h5p_completion( $lesson_id ) {
		$database = new Database\Completion();

		return $database->get_completion_by_lesson_meta( $lesson_id );
	}

	public function check_h5p_completion_( $snc_id, $lesson_id, $user_id = false ) {
		$database = new Database\Completion();

		return $database->get_H5P_completion( $snc_id, false, $lesson_id, $user_id );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'tincanny-hooks', Config::get_gulp_js( 'hooks' ), array( 'jquery' ), UNCANNY_REPORTING_VERSION );
		wp_enqueue_script( 'tincanny-modules', Config::get_gulp_js( 'modules' ), array(
			'jquery',
			'tincanny-hooks'
		), UNCANNY_REPORTING_VERSION, false );
	}

	public function print_js_completion_vars() {
		global $post;
		$course_id = false;
		$lesson_id = $post->ID;
		$current_link = get_permalink( $post->ID );
		if ( function_exists( 'learndash_get_course_id' ) ) {
			$course_id = learndash_get_course_id( $post->ID );
		}

		$mark_complete_settings = '';
		$global_option = get_option( 'disable_mark_complete_for_tincan', 'yes' );

		$has_complete_setting = 0;
		$post_meta            = ( $post->ID ) ? get_post_meta( $post->ID, '_WE-meta_', true ) : array();

		if ( ! empty( $post_meta['completion-condition'] ) ) {
			$has_complete_setting = 1;
		}

		if ( "" === $post_meta ) {
			$post_meta = array();
		}
		// Post Option Default : Use Global Setting
		if ( ! is_array( $post_meta ) || ! isset( $post_meta['restrict-mark-complete'] ) ) {
			$post_meta['restrict-mark-complete'] = 'Use Global Setting';
		}

		switch ( $post_meta['restrict-mark-complete'] ) {
			case 'Use Global Setting' :
				$mark_complete_settings = $global_option;
				break;
			default:
				$mark_complete_settings = $post_meta['restrict-mark-complete'];
				break;
		}

		$method_mark_complete_for_tincan = get_option( 'method_mark_complete_for_tincan', 'new' );

		?>

		<script type="text/javascript">
            jQuery(document).ready(function () {
				<?php foreach( $this->modules as $module ) : ?>

                moduleController.count['<?php echo $module ?>'] = <?php echo count( $this->module_info[ $module ]['contents'] ) ?>;
                moduleController.completion['<?php echo $module ?>'] = JSON.parse('<?php echo json_encode( $this->module_info[ $module ]['complete'] ) ?>');

				<?php endforeach; ?>
                moduleController.hasCompletionSetting = <?php echo $has_complete_setting; ?>;
                moduleController.markCompleteSettings = '<?php echo $mark_complete_settings; ?>';
                moduleController.markCompleteLesson = '<?php echo $lesson_id;?>';
                moduleController.markCompleteCourse = '<?php echo $course_id;?>';
                moduleController.currentLessonLink = '<?php echo $current_link;?>';
                moduleController.methodMarkCompleteForTincan = '<?php echo $method_mark_complete_for_tincan;?>';
                moduleController.ready();
            });

            var wp_ajax_url = '<?php echo admin_url( 'admin-ajax.php' ) ?>';
		</script>
		<?php
	}

	/**
     * Mark content as completed auto.
     */
	public function mark_content_completed() {
		if ( isset( $_POST['course_id'] ) && isset( $_POST['lesson_id'] ) && isset( $_POST['contentType'] ) ) {
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				$user_id      = $current_user->ID;
			} else {
				return FALSE;
			}

			// If "Capture Tin Can and SCORM data" is disabled
			$is_capture_enabled = get_option( 'show_tincan_reporting_tables', 'yes' );
			if( $is_capture_enabled == 'no' ) {
				return;
			}

			global $post;
			$post_id   = $_POST['lesson_id'];
			$course_id = $_POST['course_id'];
			$post = get_post( $post_id );
			$is_already_completed = false;
			if( 'sfwd-lessons' === $post->post_type ) {
				$is_already_completed = learndash_is_lesson_complete( $user_id, $post_id, $course_id );
            } elseif ( 'sfwd-topic' === $post->post_type ) {
				$is_already_completed = learndash_is_topic_complete( $user_id, $post_id );
            }


			if( ! $is_already_completed ) {
			    learndash_process_mark_complete( $user_id, $post_id, FALSE, $course_id );

				if ( 'autoadvance' === $_POST['setting_option'] ) {

					$previous_link = isset( $_POST['lesson_link'] ) ? $_POST['lesson_link'] : '';
					$url           = learndash_next_post_link( $previous_link, TRUE, $post );

					/*
					 * There is a bug where the learndash_next_post_link() returns the last post type in url of it type
					 * ex. if the current post is the last topic of a lesson then it will return the last topic url
					 * of the lesson
					 * AND
					 * if the current post is the last lesson of the current course then it will return the last lesson url
					 * of the course
					 */
					if( $url === $previous_link ){
						// the bug occured... let redirect to the next course if its a lesson or lesson if its a topic
						if ( 'sfwd-topic' === $post->post_type ) {
							if ( \LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' ) == 'yes' ) {
								$course_id = learndash_get_course_id( $post->ID );
								$lesson_id = learndash_course_get_single_parent_step( $course_id, $post->ID );
							} else {
								$lesson_id = learndash_get_setting( $post, 'lesson' );
							}
							$url = get_permalink( $lesson_id );
						} else {
							$course_id = learndash_get_course_id( $post );
							// This function will actually redirect to the course level quiz if there is one or the course if not
							$url      = learndash_next_global_quiz( true, null, $course_id );
						}
					}

					if ( ! empty( $url ) ) {
						echo wp_json_encode( [ 'redirect_to' => $url ] );
					}
				}
			}
		}
		die();
	}
}
