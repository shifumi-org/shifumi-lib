<?php
/**
 * @package LearnDash LMS - Course Grid
 * @version 1.7.0
 */
/*
Plugin Name: LearnDash LMS - Course Grid
Plugin URI: http://www.learndash.com
Description: Display LearnDash LMS course list in grid view.
Version: 1.7.0
Author: LearnDash
Author URI: http://www.learndash.com
Text Domain: learndash-course-grid
Doman Path: /languages/
*/

// Plugin version
if ( ! defined( 'LEARNDASH_COURSE_GRID_VERSION' ) ) {
	define( 'LEARNDASH_COURSE_GRID_VERSION', '1.7.0' );
}

// Plugin file
if ( ! defined( 'LEARNDASH_COURSE_GRID_FILE' ) ) {
	define( 'LEARNDASH_COURSE_GRID_FILE', __FILE__ );
}		

// Plugin folder path
if ( ! defined( 'LEARNDASH_COURSE_GRID_PLUGIN_PATH' ) ) {
	define( 'LEARNDASH_COURSE_GRID_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin folder URL
if ( ! defined( 'LEARNDASH_COURSE_GRID_PLUGIN_URL' ) ) {
	define( 'LEARNDASH_COURSE_GRID_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Default values
if ( ! defined( 'LEARNDASH_COURSE_GRID_COLUMNS' ) ) {
	define( 'LEARNDASH_COURSE_GRID_COLUMNS', 3 );
}

if ( ! defined( 'LEARNDASH_COURSE_GRID_MAX_COLUMNS' ) ) {
	define( 'LEARNDASH_COURSE_GRID_MAX_COLUMNS', 12 );
}

/**
 * Check plugin dependencies
 * @return void
 */
function learndash_course_grid_check_dependency()
{
	include LEARNDASH_COURSE_GRID_PLUGIN_PATH . 'includes/class-dependency-check.php';

	LearnDash_Dependency_Check_LD_Course_Grid::get_instance()->set_dependencies(
		array(
			'sfwd-lms/sfwd_lms.php' => array(
				'label'       => '<a href="https://learndash.com">LearnDash LMS</a>',
				'class'       => 'SFWD_LMS',
				'min_version' => '3.0.0',
			),
		)
	);

	LearnDash_Dependency_Check_LD_Course_Grid::get_instance()->set_message(
		__( 'LearnDash LMS - Course Grid Add-on requires the following plugin(s) to be active:', 'learndash-2checkout' )
	);
}

learndash_course_grid_check_dependency();

add_action( 'plugins_loaded', 'learndash_course_grid_localize' );

add_action( 'plugins_loaded', 'learndash_course_grid_init' );

function learndash_course_grid_init()
{
	if ( LearnDash_Dependency_Check_LD_Course_Grid::get_instance()->check_dependency_results() ) {
		include plugin_dir_path( __FILE__ ) . 'includes/conflicts-resolver.php';

		add_action( 'admin_enqueue_scripts', 'learndash_course_grid_admin', 0 );
		add_action( 'add_meta_boxes', 'learndash_course_grid_add_meta_box' );
		add_action( 'save_post', 'learndash_course_grid_save_meta_box', 10, 3 );
		add_filter( 'wp_kses_allowed_html', 'learndash_course_grid_allowed_html', 10, 2 );

		add_action( 'after_setup_theme', 'learndash_course_grid_thumb_size', 10, 3 );
		add_action( 'wp', 'learndash_course_grid_check_shortcode_in_widget' );
		add_action( 'wp_enqueue_scripts', 'learndash_course_grid_css_head', 1 );
		add_filter( 'learndash_template', 'learndash_course_grid_course_list', 999999, 5 );
		add_filter( 'ld_course_list', 'learndash_course_grid_course_list_ending', 1, 1 );
		add_filter( 'ld_course_list_shortcode_attr_defaults', 'learndash_course_grid_shortcode_attr' );
		add_filter( 'learndash_post_args', 'learndash_course_grid_post_args', 10, 1 );
		add_filter( 'the_content', 'learndash_course_grid_css', 1 );
		add_filter( 'widget_text', 'learndash_course_grid_css', 1 );
	}
}


// Setup custom course thumb size
function learndash_course_grid_thumb_size() {
	add_image_size( 'course-thumb', 400, 300, false );
}

function learndash_course_grid_localize() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'learndash-course-grid' );
	load_textdomain( 'learndash-course-grid', WP_LANG_DIR . '/plugins/learndash-course-grid-' . $locale . '.mo' );
	load_plugin_textdomain( 'learndash-course-grid', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );                

	// include translations/update class
	include LEARNDASH_COURSE_GRID_PLUGIN_PATH . 'includes/class-translations-ld-course-grid.php';
}

// Enqueue style and script if text widget has ld_course_list shortcode
function learndash_course_grid_check_shortcode_in_widget() {
	global $ld_course_grid_assets_needed;

	$sidebars_widgets = get_option( 'sidebars_widgets', array() );

	foreach ( $sidebars_widgets as $sidebar => $widgets ) {
		if ( ! is_active_sidebar( $sidebar ) ) {
			continue;
		}

		foreach ( $widgets as $widget_name ) {
			if ( false === strpos( $widget_name, 'text-' ) ) {
				continue;
			}

			preg_match( '/text-(\d+)/', $widget_name, $matches );

			$text_widgets = get_option( 'widget_text' );

			$text_widget = $text_widgets[ $matches[1] ];
			$text_widget = isset( $text_widget['text'] ) ? $text_widget['text'] : '';

			if ( has_shortcode( $text_widget, 'ld_course_list' ) ) {
				$ld_course_grid_assets_needed = true;
			}
		}
	}
}

function learndash_course_grid_css_head() {
	global $post, $ld_course_grid_assets_needed;

	if ( ( is_a( $post, 'WP_Post' ) && ( preg_match( '/(\[ld_\w+_list)/', $post->post_content ) || preg_match( '/wp:learndash\/ld-course-list/', $post->post_content ) ) )
		|| ( isset( $ld_course_grid_assets_needed ) && $ld_course_grid_assets_needed === true )
		|| is_post_type_archive( 'sfwd-courses' )
		|| apply_filters( 'learndash_course_grid_load_resources', false )
	) {
		learndash_course_grid_load_resources();
	}

}

function learndash_course_grid_load_resources() {
	wp_enqueue_style( 'learndash_course_grid_bootstrap', plugins_url( 'assets/css/bootstrap.css', __FILE__ ), array(), LEARNDASH_COURSE_GRID_VERSION );
	wp_enqueue_style( 'learndash_course_grid_css', plugins_url( 'assets/css/style.css', __FILE__ ), array(), LEARNDASH_COURSE_GRID_VERSION );
	wp_enqueue_script( 'learndash_course_grid_js', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery' ), LEARNDASH_COURSE_GRID_VERSION, true  );
}

function learndash_course_grid_admin() {
	global $pagenow, $post;

	if (
		( $pagenow == "post.php" && $post->post_type == "sfwd-courses" )
		|| ( $pagenow == "post-new.php" && isset( $_GET['post_type'] ) && $_GET['post_type'] == "sfwd-courses" )
	) {
		wp_enqueue_script( 'learndash_course_grid_admin_js', plugins_url( 'assets/js/admin-script.js', __FILE__ ), array( 'jquery' ), LEARNDASH_COURSE_GRID_VERSION, true );
	}
}

function learndash_course_grid_css( $content ) {
	// For backward compatibility filter
	$old_content = $content;

	if ( preg_match( '/(.*\[ld_\w+_list.*)/', $content ) ) {
		global $ld_course_grid_assets_needed;
		$ld_course_grid_assets_needed = true;
	}

	return apply_filters( 'learndash_course_grid_the_content', $content, $old_content );
}

// Force col to have default value
function learndash_course_grid_shortcode_attr( $attr ) {
	$attr['col'] = LEARNDASH_COURSE_GRID_COLUMNS;
	$attr['thumb_size'] = 'course-thumb';

	return $attr;
}

// Big priority argument to make the function last function to execute in the hook
function learndash_course_grid_course_list( $filepath, $name, $args, $echo, $return_file_path ) {

	if ( $name == 'course_list_template' && strpos( $filepath, LEARNDASH_LMS_PLUGIN_DIR ) !== false ) {		
		if ( $args['shortcode_atts']['course_grid'] == 'false' || 
			$args['shortcode_atts']['course_grid'] === false || 
			empty( $args['shortcode_atts']['course_grid'] ) ) {
			return $filepath;
		}

		return apply_filters( 'learndash_course_grid_template', dirname( __FILE__ ) . '/course_list_template.php', $filepath, $name, $args, $return_file_path );
	}

	return $filepath;
}

function learndash_course_grid_course_list_ending( $output ) {
	global $ld_course_grid_assets_needed;
	$ld_course_grid_assets_needed = true;

	return $output . "<br style='clear:both'>";
}

function learndash_course_grid_post_args( $post_args ) {
	foreach( $post_args as $key => $post_arg ) {
		if( isset( $post_arg["post_type"] ) && $post_arg["post_type"] == "sfwd-courses" ) {
			$course_short_description = array(
              'name' => sprintf( __( '%s Short Description', 'learndash-course-grid' ), LearnDash_Custom_Label::get_label( 'course' ) ),
              'type' => 'textarea',
              'help_text' => __( 'Include a short description that will be displayed on the Course Grid list output.', 'learndash-course-grid' ),
            );
			$post_args[$key]["fields"] = array("course_short_description" => $course_short_description) + $post_args[$key]["fields"];
		}
	}
	return $post_args;
}

/**
 * Add course grid settings meta box
 */
function learndash_course_grid_add_meta_box()
{	
	add_meta_box( 'learndash-course-grid-meta-box', __( 'Course Grid Settings', 'learndash-course-grid' ), 'learndash_course_grid_output_meta_box', array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'groups' ), 'advanced', 'low', array() );
}

/**
 * Output course grid settings meta box
 * 
 * @param  array $args List or args passed on callback function
 */
function learndash_course_grid_output_meta_box( $args )
{
	$post_id       = get_the_ID();
	$post 		   = get_post( $post_id );

	$legacy_course_options    = get_post_meta( $post_id, '_sfwd-courses', true );
	$legacy_short_description = isset( $legacy_course_options['sfwd-courses_course_short_description'] ) ? $legacy_course_options['sfwd-courses_course_short_description'] : '';

	$short_description  = get_post_meta( $post_id, '_learndash_course_grid_short_description', true );

	if ( empty( $short_description ) && ! empty( $legacy_short_description ) ) {
		$short_description = $legacy_short_description;
	}

	$enable_video  = get_post_meta( $post_id, '_learndash_course_grid_enable_video_preview', true );
	$embed_code    = get_post_meta( $post_id, '_learndash_course_grid_video_embed_code', true );
	$button_text   = get_post_meta( $post_id, '_learndash_course_grid_custom_button_text', true );

	if ( 'sfwd-courses' === $post->post_type ) {
		$ribbon_text = get_post_meta( $post_id, '_learndash_course_grid_custom_ribbon_text', true );
	}

	$video_html = <<<EOD
<video controls>
	<source src="video-file.mp4" type="video/mp4">
</video>
EOD;
	?>

	<?php wp_nonce_field( 'learndash_course_grid_save', 'learndash_course_grid_nonce' ); ?>
	<script type="text/javascript">
		jQuery( document ).ready( function( $ ) {
			$( window ).on( 'load', function() {
				if ( $( 'input[name="learndash_course_grid_enable_video_preview"]' ).is( ':checked' ) ) {
					$( '#learndash_course_grid_video_embed_code_field' ).show();
				}
			} );

			$( 'input[name="learndash_course_grid_enable_video_preview"]' ).change( function( e ) {
				if ( $( this ).prop( 'checked' ) ) {
					$( '#learndash_course_grid_video_embed_code_field' ).show();
				} else {
					$( '#learndash_course_grid_video_embed_code_field' ).hide();
				}
			});
		});
	</script>
	<div class="sfwd sfwd_options">
		<div class="sfwd_input">
			<span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
				<a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('learndash_course_grid_short_description');"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ?>">
				<label class="sfwd_label textinput"><?php _e( 'Short Description', 'learndash-course-grid' ); ?></label></a>
			</span>
			<span class="sfwd_option_input">
				<div class="sfwd_option_div">
					<textarea name="learndash_course_grid_short_description" cols="20" rows="3"><?php echo esc_textarea( $short_description ) ?></textarea>
				</div>
				<div class="sfwd_help_text_div" style="display:none" id="learndash_course_grid_short_description">
					<label class="sfwd_help_text"><?php _e( 'Include a short description that will be displayed on the Course Grid list output.', 'learndash-course-grid' ); ; ?></label>
				</div>
			</span>
			<p style="clear:left"></p>
		</div>
		<div class="sfwd_input">
			<span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
				<a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('learndash_course_grid_enable_video_preview');"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ?>">
				<label class="sfwd_label textinput"><?php _e( 'Enable Video Preview', 'learndash-course-grid' ); ?></label></a>
			</span>
			<span class="sfwd_option_input">
				<div class="sfwd_option_div">
					<input type="hidden" name="learndash_course_grid_enable_video_preview" value="0">
					<input type="checkbox" name="learndash_course_grid_enable_video_preview" value="1" <?php checked( $enable_video, 1, true ); ?>>
				</div>
				<div class="sfwd_help_text_div" style="display:none" id="learndash_course_grid_enable_video_preview">
					<label class="sfwd_help_text"><?php printf( __( 'Select this option to use a featured video for this %s in the course grid. If not selected, the featured image will be used.', 'learndash-course-grid' ), LearnDash_Custom_Label::label_to_lower( 'course' ) ) ; ?></label>
				</div>
			</span>
			<p style="clear:left"></p>
		</div>
		<div class="sfwd_input" style="display: none;" id="learndash_course_grid_video_embed_code_field">
			<span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
				<a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('learndash_course_grid_video_embed_code');"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ?>">
				<label class="sfwd_label textinput"><?php _e( 'Video URL or Embed Code', 'learndash-course-grid' ); ?></label></a>
			</span>
			<span class="sfwd_option_input">
				<div class="sfwd_option_div">
					<textarea name="learndash_course_grid_video_embed_code" rows="2" cols="57"><?php echo esc_textarea( $embed_code ); ?></textarea>
				</div>
				<div class="sfwd_help_text_div" style="display:none" id="learndash_course_grid_video_embed_code">
					<label class="sfwd_help_text"><?php _e( 'Paste the direct video URL (or embed code) of the video you want to use in the course grid. If you have a video file URL, then you can use the video tag to embed your video like this:', 'learndash-course-grid' ); ?> <code><?php echo esc_html( $video_html ); ?></code>
					</label>
				</div>
			</span>
			<p style="clear:left"></p>
		</div>
		<div class="sfwd_input" id="learndash_course_grid_custom_button_text_field">
			<span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
				<a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('learndash_course_grid_custom_button_text');"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ?>">
				<label class="sfwd_label textinput"><?php _e( 'Custom Button Text', 'learndash-course-grid' ); ?></label></a>
			</span>
			<span class="sfwd_option_input">
				<div class="sfwd_option_div">
					<input name="learndash_course_grid_custom_button_text" type="text" value="<?php echo esc_attr( $button_text ); ?>"></textarea>
				</div>
				<div class="sfwd_help_text_div" style="display:none" id="learndash_course_grid_custom_button_text">
					<label class="sfwd_help_text"><?php _e( 'Use this field to change the default "See More..." button text in the course grid.', 'learndash-course-grid' ); ?>
					</label>
				</div>
			</span>
			<p style="clear:left"></p>
		</div>

		<?php if ( 'sfwd-courses' === $post->post_type ) : ?>
		<div class="sfwd_input" id="learndash_course_grid_custom_ribbon_text_field">
			<span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
				<a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('learndash_course_grid_custom_ribbon_text');"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ?>">
				<label class="sfwd_label textinput"><?php _e( 'Custom Ribbon Text', 'learndash-course-grid' ); ?></label></a>
			</span>
			<span class="sfwd_option_input">
				<div class="sfwd_option_div">
					<input name="learndash_course_grid_custom_ribbon_text" type="text" value="<?php echo esc_attr( $ribbon_text ); ?>"></textarea>
				</div>
				<div class="sfwd_help_text_div" style="display:none" id="learndash_course_grid_custom_ribbon_text">
					<label class="sfwd_help_text"><?php _e( 'Use this field to change the default ribbon text in the course grid.', 'learndash-course-grid' ); ?>
					</label>
				</div>
			</span>
			<p style="clear:left"></p>
		</div>
		<?php endif; ?>

	</div>

	<?php
}

/**
 * Save course grid meta box fields
 * 
 * @param  int    $post_id Post ID
 * @param  object $post    WP post object
 * @param  bool   $update  True if post is an update
 */
function learndash_course_grid_save_meta_box( $post_id, $post, $update )
{
	if ( ! in_array( $post->post_type, array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'groups' ) ) ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['learndash_course_grid_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['learndash_course_grid_nonce'], 'learndash_course_grid_save' ) ) {
		wp_die( __( 'Cheatin\' huh?' ) );
	}

	$allowed_html = wp_kses_allowed_html( 'learndash_course_grid_meta_box' );

	update_post_meta( $post_id, '_learndash_course_grid_short_description', wp_kses_post( $_POST['learndash_course_grid_short_description'] ) );
	learndash_update_setting( $post_id, 'course_short_description', wp_kses_post( $_POST['learndash_course_grid_short_description'] ) );
	
	update_post_meta( $post_id, '_learndash_course_grid_enable_video_preview', wp_filter_kses( $_POST['learndash_course_grid_enable_video_preview'] ) );

	update_post_meta( $post_id, '_learndash_course_grid_video_embed_code', wp_kses( $_POST['learndash_course_grid_video_embed_code'], $allowed_html ) );

	update_post_meta( $post_id, '_learndash_course_grid_custom_button_text', sanitize_text_field( trim( $_POST['learndash_course_grid_custom_button_text'] ) ) );
	
	update_post_meta( $post_id, '_learndash_course_grid_custom_ribbon_text', sanitize_text_field( trim( $_POST['learndash_course_grid_custom_ribbon_text'] ) ) );
}

/**
 * Filter to allow HTML tags for course grid meta box settings
 * 
 * @param  array  $tags    List of HTML tags
 * @param  string $context String of context
 * @return array           New allowed HTML tags
 */
function learndash_course_grid_allowed_html( $tags, $context )
{
	if ( 'learndash_course_grid_meta_box' == $context ) {
		$tags['iframe'] = array(
			'allowfullscreen' => true,
			'frameborder' => true,
			'height' => true,
			'src' => true,
			'width' => true,
			'allow' => true,
			'class' => true,
			'data-playerid' => true,
			'allowtransparency' => true,
			'style' => true,
			'name' => true,
			'watch-type' => true,
			'url-params' => true,
			'scrolling' => true,
		);

		$tags['video'] = array(
			'controls' => true,
			'autoplay' => true,
			'height' => true,
			'width' => true,
			'src' => true,
		);

		$tags['script'] = array(
			'src' => true,
		);

		$tags['source'] = array(
			'src' => true,
			'media' => true,
			'sizes' => true,
			'type' => true,
		);

		$tags['track'] = array(
			'default' => true,
			'src' => true,
			'srclang' => true,
			'kind' => true,
			'label' => true,
		);

		$tags = apply_filters( 'learndash_course_grid_meta_box_allowed_html', $tags );
	}

	return $tags;
}