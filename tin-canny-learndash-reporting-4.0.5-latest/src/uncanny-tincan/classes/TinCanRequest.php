<?php
/**
 * Processing Request
 *
 * @package    Tin Canny Reporting for LearnDash
 * @subpackage TinCan Module
 * @author     Uncanny Owl
 * @since      1.0.0
 */

namespace UCTINCAN;

if ( ! defined( "UO_ABS_PATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

abstract class TinCanRequest {
	use \UCTINCAN\Modules;

	/**
	 * TinCan Objects
	 *
	 * @access protected
	 * @since  1.0.0
	 */
	protected $TC_Agent, $TC_Actitity, $TC_Context, $TC_Verb, $TC_Result;

	/**
	 * Content ID
	 *
	 * @access protected
	 * @since  1.0.0
	 */
	protected $content_id;

	protected $lesson_id;
	protected $user_id;

	/**
	 * Set TinCan Objects from Requested Array
	 *
	 * @access protected
	 *
	 * @param  array $decoded
	 *
	 * @return void
	 * @since  1.0.0
	 */
	protected function init_tincan_objects( $decoded ) {
		$this->TC_Agent    = ( ! empty( $decoded['actor'] ) ) ? new \TinCan\Agent( $decoded['actor'] ) : new \TinCan\Agent();
		$this->TC_Actitity = ( ! empty( $decoded['object'] ) ) ? new \TinCan\Activity( $decoded['object'] ) : new \TinCan\Activity();
		$this->TC_Context  = ( ! empty( $decoded['context'] ) ) ? new \TinCan\Context( $decoded['context'] ) : new \TinCan\Context();
		$this->TC_Verb     = ( ! empty( $decoded['verb'] ) ) ? new \TinCan\Verb( $decoded['verb'] ) : new \TinCan\Verb();

		if ( isset( $decoded['result'] ) ) {
			$result = $decoded['result'];

			if ( isset( $result['score'] ) && is_array( $result['score'] ) && isset( $result['score']['min'] ) && isset( $result['score']['max'] ) && $result['score']['min'] >= $result['score']['max'] ) {
				unset( $result['score'] );
			}
		}

		$this->TC_Result = ( ! empty( $result ) ) ? new \TinCan\Result( $result ) : new \TinCan\Result();
		$account = $this->TC_Agent->getAccount();

		if( ! empty( $account ) && ! empty( $account->getName() ) && filter_var( $account->getName(), FILTER_VALIDATE_EMAIL ) ) {
			return $account->getName();
		}
		return $this->TC_Agent->getMbox();
	}

	/**
	 * Save Data
	 *
	 * @access protected
	 * @return bool
	 * @since  1.0.0
	 */
	protected function save() {
		/*error_reporting(E_ALL);
		ini_set('display_errors', 1);*/
		global $post;

		if ( ! $this->TC_Agent ) {
			return;
		}

		// Agent
		$userEmail = $this->TC_Agent->getMbox();
		if( $userEmail == null ) {
			$account = $this->TC_Agent->getAccount();

			if( ! empty( $account->getName() ) && filter_var( $account->getName(), FILTER_VALIDATE_EMAIL ) ) {
				$userEmail = $account->getName();
			}
		}

		if ( ! $userEmail ) {
			return;
		}

		$userEmail = str_replace( 'mailto:', '', $userEmail );
		$wpUser    = get_user_by( 'email', $userEmail );

		if ( ! $wpUser->ID ) {
			return;
		}

		$user_id       = $wpUser->ID;
		$this->user_id = $user_id;

		// Lesson, Course, Group
		$grouping = $this->TC_Context->getContextActivities()->getGrouping();
		$grouping = array_pop( $grouping );
		// Group and Parent
		$auth = null;
		parse_str( $_SERVER['HTTP_REFERER'], $referer );
		if ( strstr( $_SERVER['HTTP_REFERER'], '&client=' ) !== false ) {
			if ( ! empty( $referer['auth'] ) ) {
				$auth = $referer['auth'];
			}
		}

		if ( empty( $auth ) ) {
			// Try to read all headers first.
			if ( function_exists( 'getallheaders' ) ) {
				$all_headers = getallheaders();
				if ( isset( $all_headers['Authorization'] ) ) {
					$auth = $all_headers['Authorization'];
				}
			}
		}

		if ( empty( $auth ) ) {
			$contents = file_get_contents( 'php://input' );
			$decoded  = json_decode( $contents, true );
			if ( ! is_array( $decoded ) ) {
				parse_str( $contents, $decoded_2 );
			}
			if ( isset( $decoded_2['Authorization'] ) ) {
				$auth = $decoded_2['Authorization'];
			}
		}

		if ( ! empty( $auth ) ) {
			$lesson_id = substr( $auth, 11 );
		}

		if ( empty( $lesson_id ) ) {
			$lesson_id = get_user_meta( $user_id, 'tincan_last_known_ld_module', true );
		}

		$this->lesson_id = $lesson_id;
		if ( isset( $_GET['course_id'] ) ) {
			$course_id = $_GET['course_id'];
		}
		if ( empty( $course_id ) ) {
			$course_id = get_user_meta( $user_id, 'tincan_last_known_ld_course', true );
		}

		$group_id  = $this->get_learndash_user_enrolled_group_id( $wpUser->ID, $course_id );
		$group_id  = ( $group_id ) ? $group_id : 0;
		$course_id = ( $course_id ) ? $course_id : 0;

		// Verb
		$verb = $this->TC_Verb->getId();

		if ( is_string($verb)  ) {
			$verb = array_filter( explode( '/', $verb ) );
			$verb = array_pop( $verb );
			$verb = strtolower( $verb );
		}

		// GET ACTIVITY Details
		$activity_id   = $this->TC_Actitity->getId();
		$activity      = $this->TC_Actitity->getDefinition();
		if ( ! empty( $activity ) ) {
			$activity_name = $activity->getDescription();
			$activity_name = isset( $activity_name->_map['en-US'] ) ? $activity_name->_map['en-US'] : ( isset( $activity_name->_map['und'] ) ? $activity_name->_map['und'] : '' );
			extract($this->parse_responses());
		}

		// Module and Target
		extract( $this->get_module_and_target() );

		// Result
		$completion   = false;
		$result       = false;
		$maximum      = false;
		$max_score    = false;
		$min_score    = false;
		$raw_score    = false;
		$scaled_score = false;
		$duration     = 0;



		if ( $this->TC_Result->getScore() ) {
			if ( ! is_null( $this->TC_Result->getScore()->getScaled() ) ) {
				$result  = $this->TC_Result->getScore()->getScaled() * 100;
				$maximum = 100;
			}

			if ( $result === false && $this->TC_Result->getScore()->getRaw() ) {
				$result = $this->TC_Result->getScore()->getRaw();
				//$result = ($result > 0 ) ? 100 : 0;
			}

			if ( ! is_null( $this->TC_Result->getScore()->getScaled() ) ) {
				$scaled_score = $this->TC_Result->getScore()->getScaled();
			}

			if ( ! is_null( $this->TC_Result->getScore()->getMax() ) ) {
				$max_score = $this->TC_Result->getScore()->getMax();
			}

			if ( ! is_null( $this->TC_Result->getScore()->getMin() ) ) {
				$min_score = $this->TC_Result->getScore()->getMin();
			}

			if ( ! is_null( $this->TC_Result->getScore()->getRaw() ) ) {
				$raw_score = $this->TC_Result->getScore()->getRaw();
			}

		}


		if ( $this->TC_Result->getSuccess() ) {
			if ( ! is_null( $this->TC_Result->getScore() ) && ! is_null( $this->TC_Result->getScore()->getScaled() ) ) {
				$result  = $this->TC_Result->getScore()->getScaled() * 100;
			}

			if ( $result === false && ! is_null( $this->TC_Result->getScore() ) && $this->TC_Result->getScore()->getRaw() ) {
				$result = $this->TC_Result->getScore()->getRaw();
			}

			if ( $result === false ) {
				//$result = 100;
			}

		}


		if ( ! is_null( $this->TC_Result->getDuration() ) ) {
			$duration = 0;//self::ISO8601ToSeconds( $this->TC_Result->getDuration() );
		}

		if ( ! is_null( $this->TC_Result->getSuccess() ) ) {
			$completion = ( $this->TC_Result->getSuccess() ) ? 1 : 0;
		}

		if ( ! $verb ) {
			return;
		}

		// Save
		$database = new Database();
		$database->set_report( $group_id, $course_id, $lesson_id, $module, $module_name, $target, $target_name, $verb, $result, $maximum, $completion, $user_id );
		if ( ! is_null( $this->TC_Result->getResponse() ) ) {

			if ( $this->TC_Result->getSuccess() ) {
				if ( $result === false && is_null( $this->TC_Result->getScore() )) {
					$result = 1;
				}
			}

			$database->set_quiz_data( $group_id, $course_id, $lesson_id, $module, $module_name, $activity_id, $activity_name, $result, $user_id, $available_responses_string, $correct_response, $user_response, $max_score, $min_score, $raw_score, $scaled_score, $duration );
		}

		return compact( 'group_id', 'course_id', 'lesson_id', 'module', 'module_name', 'target', 'target_name', 'verb', 'result', 'maximum', 'completion', 'user_id' );
	}

	/**
	 * Get Module and Target
	 *
	 * @access abstract protected
	 * @return void
	 * @since  1.0.0
	 */
	abstract protected function get_module_and_target();

	/**
	 * Get Group ID from Course ID
	 *
	 * @access private
	 *
	 * @param  int $user_id
	 * @param  int $course_id
	 *
	 * @return void
	 * @since  1.0.0
	 */
	private function get_learndash_user_enrolled_group_id( $user_id, $course_id ) {
		$group_ids = learndash_get_users_group_ids( $user_id );

		foreach ( $group_ids as $group_id ) {
			if ( learndash_group_has_course( $group_id, $course_id ) ) {
				return $group_id;
			}
		}

		return false;
	}

	private function get_lesson_id( $grouping ) {
		if ( ! $grouping ) {
			return 0;
		}

		if ( ! $grouping->getId() ) {
			return 0;
		}

		if ( url_to_postid( $grouping->getId() ) ) {
			return url_to_postid( $grouping->getId() );
		}

		parse_str( $grouping->getId(), $request_url );
		$queried_post = get_page_by_path( array_pop( $request_url ), OBJECT, array(
			'post',
			'sfwd-quiz',
			'sfwd-lessons',
			'sfwd-topic'
		) );

		if ( $queried_post && $queried_post->ID ) {
			return $queried_post->ID;
		}

		return 0;

	}

	/**
	 * Convert ISO 8601 values like P2DT15M33S to a total value of seconds.
	 *
	 * @param   string $ISO8601 .
	 *
	 * @return  string
	 */
	protected static function ISO8601ToSeconds( $ISO8601 ) {
		$ISO8601 = preg_replace("'/.([0-9]{2})/'", "", $ISO8601);
		$interval = new \DateInterval( $ISO8601 );

		return ( $interval->d * 24 * 60 * 60 ) +
		       ( $interval->h * 60 * 60 ) +
		       ( $interval->i * 60 ) +
		       $interval->s;
	}

	private function parse_responses() {

		$activity_defination = $this->TC_Actitity->getDefinition();
		$interaction_type    = $activity_defination->getInteractionType();
		switch ( $interaction_type ) {
			case 'true-false':
				extract( $this->correct_response_generic() );
				break;
			case 'choice':
				extract( $this->correct_response_choices() );
				break;
			case 'fill-in':
				extract( $this->correct_response_generic() );
				break;
			case 'long-fill-in':
				extract( $this->correct_response_generic() );
				break;
			case 'likert':
				extract( $this->correct_response_likert() );
				break;
			case 'matching':
				extract( $this->correct_response_matching() );
				break;
			case 'performance':
				break;
			case 'sequencing':
				extract( $this->correct_response_choices() );
				break;
			case 'numeric':
				extract( $this->correct_response_generic() );
				break;
			case 'other':
				extract( $this->correct_response_generic() );
				break;
			default:
				extract( $this->correct_response_generic() );
				break;

		}

		return compact( 'correct_response', 'available_responses', 'available_responses_string', 'user_response' );
	}

	private function correct_response_generic() {
		$correct_response           = NULL;
		$available_responses        = NULL;
		$available_responses_string = NULL;
		$user_response              = NULL;
		$activity_defination        = $this->TC_Actitity->getDefinition();

		if ( ! is_null( $activity_defination->getCorrectResponsesPattern() ) ) {
			$correct_response = $activity_defination->getCorrectResponsesPattern();
			if ( is_array( $correct_response ) ) {
				$correct_response = implode( ', ', $correct_response );
			}
		}

		if ( ! is_null( $this->TC_Result->getResponse() ) ) {
			$user_response = $this->TC_Result->getResponse();
		}

		return compact( 'correct_response', 'available_responses', 'available_responses_string', 'user_response' );
	}

	private function correct_response_matching() {
		$correct_response           = NULL;
		$available_responses        = NULL;
		$available_responses_string = NULL;
		$user_response              = NULL;
		$available_sources          = NULL;
		$available_targets          = NULL;
		$activity_defination        = $this->TC_Actitity->getDefinition();

		if ( ! is_null( $activity_defination->getSource() ) ) {
			$sources = $activity_defination->getSource();
			if ( ! empty( $sources ) ) {
				foreach ( $sources as $source ) {
					$available_sources[ $source['id'] ]     = $source['description']['und'];
					$available_responses_string['source'][] = $source['description']['und'];
				}
			}
		}

		if ( ! is_null( $activity_defination->getTarget() ) ) {
			$targets = $activity_defination->getTarget();

			if ( ! empty( $targets ) ) {
				foreach ( $targets as $target ) {
					$available_sources[ $target['id'] ]     = $target['description']['und'];
					$available_responses_string['target'][] = $target['description']['und'];
				}
			}
		}
		if ( ! empty( $available_responses_string ) ) {
			$available_responses_string = json_encode( $available_responses_string );
		}
		if ( ! is_null( $activity_defination->getCorrectResponsesPattern() ) ) {
			$correct_response_string = '';
			$correct_response        = $activity_defination->getCorrectResponsesPattern();

			if ( is_array( $correct_response ) ) {
				$correct_response = $correct_response[0];
			}
			$matches = explode( '[,]', $correct_response );
			if ( ! empty( $matches ) ) {
				foreach ( $matches as $match ) {
					$pair = explode( '[.]', $match );
					if ( ! empty( $available_sources ) ) {
						$correct_response_string .= ( $correct_response_string != '' ? ';' : '' ) . $available_sources[ $pair[0] ] . '=>' . $available_sources[ $pair[1] ];
					} else {
						$correct_response_string .= ( $correct_response_string != '' ? ';' : '' ) . str_replace( 'urn:scormdriver:', '', $pair[0] ) . '=>' . str_replace( 'urn:scormdriver:', '', $pair[1] );
					}

				}
			}

			$correct_response = $correct_response_string;
		}

		if ( ! is_null( $this->TC_Result->getResponse() ) ) {
			$user_response        = $this->TC_Result->getResponse();
			$user_response_string = '';
			$matches              = explode( '[,]', $user_response );
			if ( ! empty( $matches ) ) {
				foreach ( $matches as $match ) {
					$pair = explode( '[.]', $match );
					if ( ! empty( $available_sources ) ) {
						$user_response_string .= ( $user_response_string != '' ? ';' : '' ) . $available_sources[ $pair[0] ] . '=>' . $available_sources[ $pair[1] ];
					} else {
						$user_response_string .= ( $user_response_string != '' ? ';' : '' ) . str_replace( 'urn:scormdriver:', '', $pair[0] ) . '=>' . str_replace( 'urn:scormdriver:', '', $pair[1] );
					}
				}
			}

			$user_response = $user_response_string;
		}

		return compact( 'correct_response', 'available_responses', 'available_responses_string', 'user_response' );
	}

	private function correct_response_choices() {
		$correct_response           = NULL;
		$available_responses        = NULL;
		$available_responses_string = NULL;
		$user_response              = NULL;
		$available_sources          = NULL;
		$available_targets          = NULL;
		$activity_defination        = $this->TC_Actitity->getDefinition();

		if ( ! is_null( $activity_defination->getChoices() ) ) {
			$available_responses = $activity_defination->getChoices();
			if ( ! empty( $available_responses ) ) {
				foreach ( $available_responses as $respons ) {
					$available_responses_string[] = implode( ',', $respons['description'] );
				}
			}
			if ( ! empty( $available_responses_string ) ) {
				$available_responses_string = implode( ', ', $available_responses_string );
			}
		}

		if ( ! is_null( $activity_defination->getCorrectResponsesPattern() ) ) {
			$correct_responses = $activity_defination->getCorrectResponsesPattern();
			if ( ! empty( $correct_responses ) ) {
				if ( is_array( $correct_responses ) && count( $correct_responses ) == 1 ) {
					$correct_responses = explode( '[,]', $correct_responses[0] );
				}
				foreach ( $correct_responses as $crp ) {
					foreach ( $available_responses as $respons ) {
						if ( $respons['id'] === $crp ) {
							$correct_response[] = implode( ',', $respons['description'] );
						}
					}
				}
			}
			if ( ! empty( $correct_response ) ) {
				$correct_response = implode( ', ', $correct_response );
			} else {
				$correct_response = str_replace( 'urn:scormdriver:', '', implode( ', ', $correct_responses ) );
			}
		}

		if ( ! is_null( $this->TC_Result->getResponse() ) ) {
			$user_responses = $this->TC_Result->getResponse();
			$matches        = explode( '[,]', $user_responses );
			if ( ! empty( $available_responses ) && ! empty( $matches ) ) {
				foreach ( $matches as $match ) {
					foreach ( $available_responses as $respons ) {
						if ( $respons['id'] === $match ) {
							$user_response[] = implode( ',', $respons['description'] );
						}
					}
				}
			}

			if ( ! empty( $user_response ) ) {
				$user_response = implode( ', ', $user_response );
			} else {
				$user_response = str_replace( 'urn:scormdriver:', '', $user_responses );
			}
		}

		return compact( 'correct_response', 'available_responses', 'available_responses_string', 'user_response' );
	}

	private function correct_response_likert() {
		$correct_response           = NULL;
		$available_responses        = NULL;
		$available_responses_string = NULL;
		$user_response              = NULL;
		$available_sources          = NULL;
		$available_targets          = NULL;
		$activity_defination        = $this->TC_Actitity->getDefinition();

		if ( ! is_null( $activity_defination->getScale() ) ) {
			$sources = $activity_defination->getScale();
			if ( ! empty( $sources ) ) {
				foreach ( $sources as $source ) {
					$available_sources[ $source['id'] ] = $source['description']['und'];
					$available_responses_string         .= ( $available_responses_string != '' ? ',' : '' ) . $source['description']['und'];
				}
			}
		}

		if ( ! is_null( $activity_defination->getCorrectResponsesPattern() ) ) {
			$correct_response_string = '';
			$correct_response        = $activity_defination->getCorrectResponsesPattern();

			if ( ! empty( $available_sources ) ) {

				$correct_response_string = $available_sources[ $correct_response ];

			}

			$correct_response = $correct_response_string;
		}

		if ( ! is_null( $this->TC_Result->getResponse() ) ) {
			$user_response        = $this->TC_Result->getResponse();
			$user_response_string = '';
			if ( ! empty( $user_response ) ) {
				$user_response_string = $available_sources[ $user_response ];
			}
			if ( ! empty( $user_response_string ) ) {
				$user_response = $user_response_string;
			} else {
				$user_response = str_replace( 'urn:scormdriver:', '', $user_response );
			}
		}

		return compact( 'correct_response', 'available_responses', 'available_responses_string', 'user_response' );
	}

}
