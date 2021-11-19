<?php

use LearnDash\Course_Grid\Utilities;

function learndash_course_grid_prepare_template_post_attributes( $post, $atts = [] )
{
    $user_id = get_current_user_id();

    $students = false;
    $duration = false;
    $trial_price = false;
    $trial_duration = false;
    $subscription_duration = false;
    $reviews = false;
    $categories = false;
    $tags = false;
    $author = false;
    $total_steps = false;
    $courses = false;
    $lessons = false;
    $topics = false;
    $quizzes = false;
    $forums = false;
    $sticky_course = false;

    $course_options = null;
    if ( $post->post_type == 'sfwd-courses' ) {
        $course_options = get_post_meta( $post->ID, '_sfwd-courses', true );
    }

    $ribbon_text = get_post_meta( $post->ID, '_learndash_course_grid_custom_ribbon_text', true );
    $ribbon_text = ! empty( $ribbon_text ) ? $ribbon_text : '';

    $description = get_post_meta( $post->ID, '_learndash_course_grid_short_description', true );

    $description = wpautop( do_shortcode( htmlspecialchars_decode( $description ) ) );
    $description = strlen( $description ) > $atts['description_char_max'] ? mb_strimwidth( $description, 0, $atts['description_char_max'] ) . '...' : $description;

    $video = get_post_meta( $post->ID, '_learndash_course_grid_enable_video_preview', true );

    $embed_code = get_post_meta( $post->ID, '_learndash_course_grid_video_embed_code', true );

    // Retrive oembed HTML if URL provided
    if ( preg_match( '/^http/', $embed_code ) ) {
        $embed_code = wp_oembed_get( $embed_code, array( 'height' => 600, 'width' => 400 ) );
    }
    
    $button_link = get_permalink( $post->ID );
    $button_link = apply_filters( 'learndash_course_grid_custom_button_link', $button_link, $post->ID );

    $duration = Utilities::get_duration( $post->ID, 'output' );

    switch ( $post->post_type ) {
        case 'sfwd-courses':
            $cat_taxonomies = [ 'category', 'ld_course_category' ];
            $tag_taxonomies = [ 'post_tag', 'ld_course_tag' ];
            break;

        case 'sfwd-lessons':
            $cat_taxonomies = [ 'category', 'ld_lesson_category' ];
            $tag_taxonomies = [ 'post_tag', 'ld_lesson_tag' ];
            break;

        case 'sfwd-topic':
            $cat_taxonomies = [ 'category', 'ld_topic_category' ];
            $tag_taxonomies = [ 'post_tag', 'ld_topic_tag' ];
            break;

        case 'sfwd-quiz':
            $cat_taxonomies = [ 'category', 'ld_quiz_category' ];
            $tag_taxonomies = [ 'post_tag', 'ld_quiz_tag' ];
            break;

        case 'sfwd-question':
            $cat_taxonomies = [ 'category', 'ld_question_category' ];
            $tag_taxonomies = [ 'post_tag', 'ld_question_tag' ];
            break;

        case 'groups':
            $cat_taxonomies = [ 'category', 'ld_group_category' ];
            $tag_taxonomies = [ 'post_tag', 'ld_group_tag' ];
            break;
        
        default:
            $cat_taxonomies = [ 'category' ];
            $tag_taxonomies = [ 'post_tag' ];
            break;
    }

    if ( isset( $cat_taxonomies ) ) {
        $categories = get_terms( [
            'taxonomy' => $cat_taxonomies,
            'object_ids' => $post->ID,
            'orderby' => 'name',
            'fields' => 'names',
        ] );

        $categories = implode( ', ', $categories );
    }

    if ( isset( $tag_taxonomies ) ) {
        $tags = get_terms( [
            'taxonomy' => $tag_taxonomies,
            'object_ids' => $post->ID,
            'orderby' => 'name',
            'fields' => 'names',
        ] );

        $tags = implode( ', ', $tags );
    }
    
    if ( defined( 'LEARNDASH_VERSION' ) ) {
        if ( $post->post_type == 'sfwd-courses' ) {
            $total_steps = learndash_get_course_steps_count( $post->ID );
            $students_data = learndash_get_users_for_course( $post->ID );
            $lessons_data = learndash_course_get_steps_by_type( $post->ID, 'sfwd-lessons' );
            $topics_data = learndash_course_get_steps_by_type( $post->ID, 'sfwd-topic' );
            $quizzes_data = learndash_course_get_steps_by_type( $post->ID, 'sfwd-quiz' );
        } elseif ( $post->post_type == 'groups' ) {
            $students_data = learndash_get_groups_users( $post->ID );
            $courses  = learndash_group_enrolled_courses( $post->ID );
        } elseif ( $post->post_type == 'sfwd-lessons' ) {
            $course_id = learndash_get_course_id( $post->ID );
            $students_data = learndash_get_users_for_course( $course_id );
            $topics_data = learndash_course_get_children_of_step( $course_id, $post->ID, 'sfwd-topic' );
            $quizzes_data = learndash_course_get_children_of_step( $course_id, $post->ID, 'sfwd-quiz' );
        } elseif ( $post->post_type == 'sfwd-topic' ) {
            $course_id = learndash_get_course_id( $post->ID );
            $students_data = learndash_get_users_for_course( $course_id );
            $quizzes_data = learndash_course_get_children_of_step( $course_id, $post->ID, 'sfwd-quiz' );
        }

        if ( isset( $students_data ) && is_a( $students_data, 'WP_User_Query' ) ) {
            $students_data = $students_data->get_results();
            $students = [
                'count' => count( $students_data ),
                'list' => $students_data,
            ];
        } elseif ( isset( $students_data ) && is_array( $students_data ) ) {
            $students = [
                'count' => count( $students_data ),
                'list' => $students_data,
            ];
        }

        if ( isset( $lessons_data ) && is_array( $lessons_data ) ) {
            $lessons = [
                'count' => count( $lessons_data ),
                'list' => $lessons_data,
            ];
        }

        if ( isset( $topics_data ) && is_array( $topics_data ) ) {
            $topics = [
                'count' => count( $topics_data ),
                'list' => $topics_data,
            ];
        }

        if ( isset( $quizzes_data ) && is_array( $quizzes_data ) ) {
            $quizzes = [
                'count' => count( $quizzes_data ),
                'list' => $quizzes_data ,
            ];
        }
    }

    if ( function_exists( 'bbpress' ) && defined( 'LEARNDASH_BBPRESS_VERSION' ) ) {
        global $wpdb;

        $meta_key = false;
        if ( $post->post_type == 'sfwd-courses' ) {
            $meta_key = '_ld_associated_courses';
        } elseif ( $post->post_type == 'groups' ) {
            $meta_key = '_ld_associated_groups';
        }

        $forums_data = false;
        if ( $meta_key ) {
            $forums_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = %s AND meta_value LIKE '%%:%d;%%'", $meta_key, $post->ID ) );   
        }

        if ( is_array( $forums_data ) && ! empty( $forums_data ) ) {
            $forums = [
                'count' => count( $forums_data ),
                'list'  => [],
            ];

            foreach ( $forums_data as $forum ) {
                $forums['list'][] = $forum->post_id;
            }
        }
    }

    $currency = class_exists( 'LearnDash_Settings_Section' ) ? LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Section_PayPal', 'paypal_currency' ) : null;

    if ( class_exists( 'NumberFormatter' ) && $currency ) {
        $locale = get_locale();
        $number_format = new NumberFormatter( $locale . '@currency=' . $currency, NumberFormatter::CURRENCY );
        $currency = $number_format->getSymbol( NumberFormatter::CURRENCY_SYMBOL );
    }

    /**
     * Currency symbol filter hook
     * 
     * @param string $currency Currency symbol
     * @param int    $post->ID
     */
    $currency = apply_filters( 'learndash_course_grid_currency', $currency, $post->ID );

    $price = '';
    $price_type = '';
    $price_text = '';
    if ( function_exists( 'learndash_get_course_price' ) && function_exists( 'learndash_get_group_price' ) ) {
        if ( $post->post_type == 'sfwd-courses' ) {
            $price_args = learndash_get_course_price( $post->ID );
        } elseif ( $post->post_type == 'groups' ) {
            $price_args = learndash_get_group_price( $post->ID );
        }

        if ( ! empty( $price_args ) ) {
            $price      = $price_args['price'];
            $price_type = $price_args['type'];

            if ( is_numeric( $price ) && ! empty( $price ) ) {
                $price_format = apply_filters( 'learndash_course_grid_price_text_format', '{currency}{price}' );

                $price_text = str_replace( [ '{currency}', '{price}' ], [ $currency, $price ], $price_format );
            } elseif ( is_string( $price ) && ! empty( $price ) ) {
                $price_text = $price;
            } elseif ( empty( $price ) ) {
                $price_text = __( 'Free', 'learndash-course-grid' );
            }

            if ( $price_type == 'subscribe' ) {
                $trial_price = $price_args['trial_price'] ?? false;
                
                $trial_duration = isset( $price_args['trial_interval'] ) && isset( $price_args['trial_frequency'] ) ? $price_args['trial_interval'] . ' ' . $price_args['trial_frequency'] : false;

                if ( isset( $price_args['interval'] ) && isset( $price_args['frequency'] ) ) {
                    $subscription_duration =  $price_args['interval'] > 1 ? $price_args['interval'] . ' ' . $price_args['frequency'] : $price_args['frequency'];

                    $price_text = sprintf( '%s%s', $price_text, $subscription_duration ? '/' . $subscription_duration : '' );
                }
            }
        }
    }

    if ( empty( $price ) ) {
        $price = __( 'Free', 'learndash-course-grid' );
    }

    $price = apply_filters( 'learndash_course_grid_price', $price, $post->ID );

    $reviews = apply_filters( 'learndash_course_grid_reviews', $reviews, $post->ID );

    $user_object = get_user_by( 'ID', $post->post_author );
    $author = apply_filters( 'learndash_course_grid_author', [
        'name' => $user_object->display_name,
        'avatar' => get_avatar_url( $post->post_author ),
    ], $post->ID, $post->post_author );

    $is_completed = false;
    $ribbon_class = 'ribbon';

    $has_access = false;
    if ( defined( 'LEARNDASH_VERSION' ) ) {
        if ( $post->post_type == 'sfwd-courses' ) {
            $has_access   = sfwd_lms_has_access( $post->ID, $user_id );
            $is_completed = learndash_course_completed( $user_id, $post->ID );
        } elseif ( $post->post_type == 'groups' ) {
            $has_access = learndash_is_user_in_group( $user_id, $post->ID );
            $is_completed = learndash_get_user_group_completed_timestamp( $post->ID, $user_id );
        } elseif ( $post->post_type == 'sfwd-lessons' ) {
            $parent_course_id = learndash_get_course_id( $post->ID );
            $has_access   = sfwd_lms_has_access( $parent_course_id, $user_id );
            $is_completed = learndash_is_lesson_complete( $user_id, $post->ID, $parent_course_id );
        } elseif ( $post->post_type == 'sfwd-topic' ) {
            $parent_course_id = learndash_get_course_id( $post->ID );
            $has_access   = sfwd_lms_has_access( $parent_course_id, $user_id );
            $is_completed = learndash_is_topic_complete( $user_id, $post->ID, $parent_course_id );
        }

        if ( in_array( $post->post_type, [ 'sfwd-courses', 'groups' ] ) ) {
            if ( $price_type != 'open' && empty( $ribbon_text ) ) {
                if ( $has_access && ! $is_completed ) {
                    $ribbon_class .= ' enrolled';
                    $ribbon_text = __( 'Enrolled', 'learndash-course-grid' );
                } elseif ( $has_access && $is_completed ) {
                    $ribbon_class .= ' completed';
                    $ribbon_text = __( 'Completed', 'learndash-course-grid' );
                } elseif ( is_numeric( $price ) ) {
                    $ribbon_text = $price_text;
                } elseif ( $price_type == 'free' ) {
                    $ribbon_class .= ' free';
                    $ribbon_text = __( 'Free', 'learndash-course-grid' );
                } else {
                    $ribbon_class .= ' available';
                    $ribbon_text = __( 'Available', 'learndash-course-grid' );
                }
            } elseif ( $price_type == 'open' && empty( $ribbon_text ) ) {
                if ( is_user_logged_in() && ! $is_completed ) {
                    $ribbon_class .= ' enrolled';
                    $ribbon_text = __( 'Enrolled', 'learndash-course-grid' );
                } elseif ( is_user_logged_in() && $is_completed ) {
                    $ribbon_class .= ' completed';
                    $ribbon_text = __( 'Completed', 'learndash-course-grid' );
                } else {
                    $ribbon_class .= ' free';
                    $ribbon_text = __( 'Free', 'learndash-course-grid' );
                }
            } 
        } elseif ( in_array( $post->post_type, ['sfwd-lessons', 'sfwd-topic'] ) ) {
            if ( $has_access && $is_completed ) {
                $ribbon_class .= ' completed';
                $ribbon_text = __( 'Completed', 'learndash-course-grid' );
            } elseif ( $has_access && ! $is_completed ) {
                $ribbon_class .= ' enrolled';
                $ribbon_text = __( 'In progress', 'learndash-course-grid' );
            } else {
                $ribbon_class .= ' not-available';
                $ribbon_text = __( 'Not Available', 'learndash-course-grid' );
            }
        }
    }

    $button_text = get_post_meta( $post->ID, '_learndash_course_grid_custom_button_text', true );

    if ( empty( $button_text ) ) {
        if ( in_array( $post->post_type, [ 'sfwd-courses', 'groups' ] ) && ! $has_access  ) {
            $button_text = __( 'Enroll Now', 'learndash-course-grid' );
        } elseif ( in_array( $post->post_type, [ 'sfwd-courses', 'groups' ] ) && $has_access  ) {
            $button_text = __( 'Continue Study', 'learndash-course-grid' );
        } else {
            $button_text =  __( 'See More', 'learndash-course-grid' );
        }
    }

    /**
     * Filter: individual course ribbon text
     *
     * @param string $ribbon_text Returned ribbon text
     * @param int    $post->ID   Course ID
     * @param string $price_type  Course price type
     */
    $ribbon_text = apply_filters( 'learndash_course_grid_ribbon_text', $ribbon_text, $post->ID, $price_type );

    /**
     * Filter: individual course ribbon class names
     *
     * @param string $ribbon_class     	 Returned class names
     * @param int    $post->ID 	 Course ID
     * @param array  $course_options Course's options
     * @var string
     */
    $ribbon_class = apply_filters( 'learndash_course_grid_ribbon_class', $ribbon_class, $post->ID, $course_options );

    $post_atts = [
        'user_id' => $user_id,
        'post_type' => $post->post_type,
        'title' => $post->post_title,
        'description' => $description,
        'price' => $price,
        'currency' => $currency,
        'price_text' => $price_text,
        'video' => $video,
        'video_embed_code' => $embed_code,
        'button_link' => $button_link,
        'button_text' => $button_text,
        'ribbon_text' => $ribbon_text,
        'ribbon_class' => $ribbon_class,
        'students' => $students,
        'duration' => $duration,
        'trial_price' => $trial_price,
        'trial_duration' => $trial_duration,
        'subscription_duration' => $subscription_duration,
        'reviews' => $reviews,
        'categories' => $categories,
        'tags' => $tags,
        'author' => $author,
        'total_steps' => $total_steps,
        'courses' => $courses,
        'lessons' => $lessons,
        'topics' => $topics,
        'quizzes' => $quizzes,
        'forums' => $forums,
        'sticky_course' => $sticky_course,
    ];

    return apply_filters( 'learndash_course_grid_template_post_attributes', $post_atts, $post );
}

function learndash_course_grid_load_card_template( $atts, $post )
{
    $template = Utilities::get_card_layout( $atts['card'] );

    if ( $template ) {
        $post_atts = learndash_course_grid_prepare_template_post_attributes( $post, $atts );
        extract( $post_atts );

        include $template;
    }
}

function learndash_course_grid_load_resources()
{
    LearnDash\course_grid()->skins->enqueue_skin_assets();

    // Check and load legacy v1 skin assets
	$skin  = 'legacy-v1';
	$style_file = LearnDash\Course_Grid\Utilities::get_skin_style( $skin );

	if ( $style_file ) {
		wp_enqueue_style( 'learndash-course-grid-skin-' . $skin, $style_file, [], LEARNDASH_COURSE_GRID_VERSION );
	}
}