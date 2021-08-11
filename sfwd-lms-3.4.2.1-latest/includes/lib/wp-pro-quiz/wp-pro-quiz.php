<?php
/*
Plugin Name: WP-Pro-Quiz
Plugin URI: http://wordpress.org/extend/plugins/wp-pro-quiz
Description: A powerful and beautiful quiz plugin for WordPress.
Version: 0.28
Author: Julius Fischer
Author URI: http://www.it-gecko.de
Text Domain: wp-pro-quiz
Domain Path: /languages
// phpcs:disable WordPress.NamingConventions.ValidVariableName,WordPress.NamingConventions.ValidFunctionName,WordPress.NamingConventions.ValidHookName
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @ignore
 */
define( 'WPPROQUIZ_VERSION', '0.28' );

/**
 * @ignore
 */
define( 'WPPROQUIZ_PATH', dirname( __FILE__ ) );

/**
 * @ignore
 */
define( 'WPPROQUIZ_URL', plugins_url( '', __FILE__ ) );

/**
 * @ignore
 */
define( 'WPPROQUIZ_FILE', __FILE__ );

$wpproquiz_upload_dir = wp_upload_dir();

/**
 * @ignore
 */
define( 'WPPROQUIZ_CAPTCHA_DIR', $wpproquiz_upload_dir['basedir'] . '/wp_pro_quiz_captcha' );

/**
 * @ignore
 */
define( 'WPPROQUIZ_CAPTCHA_URL', $wpproquiz_upload_dir['baseurl'] . '/wp_pro_quiz_captcha' );

spl_autoload_register( 'wpProQuiz_autoload' );

add_action( 'plugins_loaded', 'wpProQuiz_pluginLoaded' );

if ( is_admin() ) {
	new WpProQuiz_Controller_Admin();
} else {
	new WpProQuiz_Controller_Front();
}

/**
 * Handles the wp pro quiz class autoloading.
 *
 * Callback for `spl_autoload_register` function.
 *
 * @param string $class Class name.
 */
function wpProQuiz_autoload( $class ) {
	$c = explode( '_', $class );

	if ( false === $c || count( $c ) != 3 || 'WpProQuiz' !== $c[0] ) {
		return;
	}

	$dir = '';

	switch ( $c[1] ) {
		case 'View':
			$dir = 'view';
			break;
		case 'Model':
			$dir = 'model';
			break;
		case 'Helper':
			$dir = 'helper';
			break;
		case 'Controller':
			$dir = 'controller';
			break;
		case 'Plugin':
			$dir = 'plugin';
			break;
		default:
			return;
	}

	if ( file_exists( WPPROQUIZ_PATH . '/lib/' . $dir . '/' . $class . '.php' ) ) {
		include_once WPPROQUIZ_PATH . '/lib/' . $dir . '/' . $class . '.php';
	}
}

/**
 * Runs the wp pro quiz upgrade after the plugins are loaded.
 *
 * Fires on `plugins_loaded` hook.
 */
function wpProQuiz_pluginLoaded() {

	if ( get_option( 'wpProQuiz_version' ) !== WPPROQUIZ_VERSION ) {
		WpProQuiz_Helper_Upgrade::upgrade();
	}
}

/**
 * Instansiates the `WpProQuiz_Plugin_BpAchievementsV3` class.
 *
 * Fires on `dpa_ready` hook.
 */
function wpProQuiz_achievementsV3() {
	achievements()->extensions->wp_pro_quiz = new WpProQuiz_Plugin_BpAchievementsV3();

	/**
	 * Fires after instansiating WpProQuiz_Plugin_BpAchievementsV3 class.
	 */
	do_action( 'wpProQuiz_achievementsV3' );
}

add_action( 'dpa_ready', 'wpProQuiz_achievementsV3' );

/**
 * Formats the quiz cloze type answers into an array to be used when comparing responses.
 *
 * The function is copied from `WpProQuiz_View_FrontQuiz` class.
 *
 * @since 2.5.0
 *
 * @param string  $answer_text      Answer text.
 * @param boolean $convert_to_lower Optional. Whether to convert anwser to lowercase. Default true.
 *
 * @return array An array of cloze question data.
 */
function fetchQuestionCloze( $answer_text, $convert_to_lower = true ) {

	/**
	 * Filters the value of quiz question answer before processing.
	 *
	 * @param string $answer  The quiz question anser text.
	 * @param string $context The context of type of question.
	 */
	$answer_text = apply_filters( 'learndash_quiz_question_answer_preprocess', $answer_text, 'cloze' );

	preg_match_all( '#\{(.*?)(?:\|(\d+))?(?:[\s]+)?\}#im', $answer_text, $matches, PREG_SET_ORDER );

	$data = array();

	foreach ( $matches as $k => $v ) {
		$text    = $v[1];
		$points  = ! empty( $v[2] ) ? (int) $v[2] : 1;
		$rowText = $multiTextData = array();
		$len     = array();

		if ( preg_match_all( '#\[(.*?)\]#im', $text, $multiTextMatches ) ) {
			foreach ( $multiTextMatches[1] as $multiText ) {
				$multiText_clean = trim( html_entity_decode( $multiText, ENT_QUOTES ) );

				/**
				 * Filters whether to convert quiz question cloze to lowercase or not.
				 *
				 * @param boolean $conver_to_lower Whether to convert quiz question cloze to lower case.
				 */
				if ( apply_filters( 'learndash_quiz_question_cloze_answers_to_lowercase', $convert_to_lower ) ) {
					if ( function_exists( 'mb_strtolower' ) ) {
						$x = mb_strtolower( $multiText_clean );
					} else {
						$x = strtolower( $multiText_clean );
					}
				} else {
					$x = $multiText_clean;
				}

				$len[]           = strlen( $x );
				$multiTextData[] = $x;
				$rowText[]       = $multiText;
			}
		} else {
			$text_clean = trim( html_entity_decode( $text, ENT_QUOTES ) );
			/** This filter is documented in includes/lib/wp-pro-quiz/wp-pro-quiz.php */
			if ( apply_filters( 'learndash_quiz_question_cloze_answers_to_lowercase', $convert_to_lower ) ) {
				if ( function_exists( 'mb_strtolower' ) ) {
					$x = mb_strtolower( trim( html_entity_decode( $text_clean, ENT_QUOTES ) ) );
				} else {
					$x = strtolower( trim( html_entity_decode( $text_clean, ENT_QUOTES ) ) );
				}
			} else {
				$x = $text_clean;
			}

			$len[]           = strlen( $x );
			$multiTextData[] = $x;
			$rowText[]       = $text;
		}

		$a  = '<span class="wpProQuiz_cloze"><input autocomplete="off" data-wordlen="' . max( $len ) . '" type="text" value=""> ';
		$a .= '<span class="wpProQuiz_clozeCorrect" style="display: none;"></span></span>';

		$data['correct'][] = $multiTextData;
		$data['points'][]  = $points;
		$data['data'][]    = $a;
	}

	$data['replace'] = preg_replace( '#\{(.*?)(?:\|(\d+))?(?:[\s]+)?\}#im', '@@wpProQuizCloze@@', $answer_text );

	/**
	 * Filters the value of quiz question answer after processing.
	 *
	 * @param string $answer  The quiz question anser text.
	 * @param string $context The context of type of question.
	 */
	$data['replace'] = apply_filters( 'learndash_quiz_question_answer_postprocess', $data['replace'], 'cloze' );

	return $data;
}


/**
 * Casts an instance of PHP stdClass to the type of given class name.
 *
 * This function will take an instance of a PHP stdClass and attempt to cast it to
 * the type of the specified $className parameter.
 * For example, we may pass 'Acme\Model\Product' as the $className.
 *
 * @param object $instance An instance of the stdClass to cast.
 * @param string $className The name of the class type to which we want to convert.
 *
 * @return mixed The instance after casting.
 */
function learndash_cast_WpProQuiz_Model_AnswerTypes( $instance, $className ) {
	return unserialize(
		sprintf(
			'O:%d:"%s"%s',
			\strlen( $className ),
			$className,
			strstr( strstr( serialize( $instance ), '"' ), ':' )
		)
	);
}
