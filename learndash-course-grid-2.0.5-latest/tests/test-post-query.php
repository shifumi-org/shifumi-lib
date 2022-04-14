<?php
namespace LearnDash\Course_Grid\Tests;

use LearnDash\Course_Grid\Utilities;
use WP_Query;
use WP_UnitTestCase;

class PostQueryTest extends WP_UnitTestCase
{
    /**
     * @test
     */
    public function it_builds_correct_query_args_from_shortcode_attributes()
    {
        $atts = [
            'taxonomies' => 'category:category1, category2; post_tag: tag1, tag2;',
            'per_page' => 10,
            'post_type' => 'post',
            'enrollment_status' => '',
            'orderby' => 'post_title',
            'order' => 'ASC',
        ];

        $query_args = Utilities::build_posts_query_args( $atts );

        $this->assertArraySubset(
            [
                'post_type' => 'post',
                'posts_per_page' => 10,
                'post_status' => 'publish',
                'orderby' => 'post_title',
                'order' => 'ASC',
                'tax_query' => [
                    [
                        'taxonomy' => 'category',
                        'field' => 'slug',
                        'terms' => [ 'category1', 'category2' ],
                    ],
                    [
                        'taxonomy' => 'post_tag',
                        'field' => 'slug',
                        'terms' => [ 'tag1', 'tag2' ],
                    ],
                    'relation' => 'OR',
                ],
                'post__in' => null,
            ],
            $query_args
        );
    }

    /**
     * @test
     */
    public function it_gets_correct_posts_filtered_by_taxonomies()
    {
        $post1 = $this->factory->post->create_and_get();
        $term1 = $this->factory->term->create_and_get([
            'name' => 'cat1',
            'taxonomy' => 'category',
        ]);
        wp_set_post_terms( $post1->ID, [ $term1->term_id ], 'category' ); 
        
        $post2 = $this->factory->post->create_and_get();
        $term2 = $this->factory->term->create_and_get([
            'name' => 'cat2',
            'taxonomy' => 'category',
        ]);
        wp_set_post_terms( $post2->ID, [ $term2->term_id ], 'category' );

        // Query the posts using LD CG
        $atts = [
            'post_type' => 'post',
            'taxonomies' => 'category: cat1, cat2;',
            'orderby' => 'post_title',
            'order' => 'ASC',
            'per_page' => 10,
        ];

        $query_args = Utilities::build_posts_query_args( $atts );
        
        // $query_args = [
        //     'post_type' => 'post',
        //     'tax_query' => [
        //         'relation' => 'OR',
        //         [
        //             'taxonomy' => 'category',
        //             'field' => 'slug',
        //             'terms' => [ 'cat1', 'cat2' ],
        //         ]
        //     ]
        // ];

        // $this->assertCount( 2, $posts );

        // error_log( print_r($query_args,true));
        // error_log(print_r( get_terms( [ 'taxonomy' => 'category'] ), true));

        $query = new WP_Query( $query_args );
        $posts = $query->get_posts();

        // $posts = get_posts( $query_args );

        // Assert the returned posts, it should return 2 posts above
        $this->assertCount( 2, $posts );
    }

    /**
     * @test
     */
    public function it_gets_correct_courses()
    {
        $user = $this->factory->user->create_and_get();

        $courses = $this->factory->post->create_many( 5, [
            'post_type' => 'sfwd-courses',
        ]);
        
        wp_set_current_user( $user->ID );

        // Query the posts using LD CG
        $atts = [
            'post_type' => 'sfwd-courses',
            'orderby' => 'post_title',
            'order' => 'ASC',
            'per_page' => 10,
        ];

        $query_args = Utilities::build_posts_query_args( $atts );
        
        $query = new WP_Query( $query_args );
        $posts = $query->get_posts();

        $this->assertCount( 5, $posts );
    }

    /**
     * @test
     */
    public function it_gets_correct_enrolled_and_not_started_courses_for_non_enrolled_user()
    {
        $user = $this->factory->user->create_and_get();

        $course1 = $this->factory->post->create_and_get([
            'post_type' => 'sfwd-courses',
        ]);
        
        $course2 = $this->factory->post->create_and_get([
            'post_type' => 'sfwd-courses',
        ]);

        wp_set_current_user( $user->ID );

        // Query the posts using LD CG
        $atts = [
            'post_type' => 'sfwd-courses',
            'orderby' => 'post_title',
            'order' => 'ASC',
            'per_page' => 10,
            'enrollment_status' => 'enrolled',
            'progress_status' => 'not_started',
        ];

        $query_args = Utilities::build_posts_query_args( $atts );
        
        $query = new WP_Query( $query_args );
        $posts = $query->get_posts();

        $this->assertCount( 0, $posts );
    }

    /**
     * @test
     */
    public function it_gets_correct_enrolled_and_not_started_courses_for_enrolled_user()
    {
        $user = $this->factory->user->create_and_get();

        $course1 = $this->factory->post->create_and_get([
            'post_type' => 'sfwd-courses',
        ]);
        
        $course2 = $this->factory->post->create_and_get([
            'post_type' => 'sfwd-courses',
        ]);

        wp_set_current_user( $user->ID );

        ld_update_course_access( $user->ID, $course1->ID );

        // Query the posts using LD CG
        $atts = [
            'post_type' => 'sfwd-courses',
            'orderby' => 'post_title',
            'order' => 'ASC',
            'per_page' => 10,
            'enrollment_status' => 'enrolled',
            'progress_status' => 'not_started',
        ];

        $query_args = Utilities::build_posts_query_args( $atts );
        
        $query = new WP_Query( $query_args );
        $posts = $query->get_posts();

        $this->assertCount( 1, $posts );
    }

    /**
     * @test
     */
    public function it_gets_correct_enrolled_and_not_started_courses_for_enrolled_user_that_started_all_courses()
    {
        $user = $this->factory->user->create_and_get();

        $courses = $this->factory->post->create_many( 10, [
            'post_type' => 'sfwd-courses',
        ]);
        
        $course1 = $this->factory->post->create_and_get([
            'post_type' => 'sfwd-courses',
        ]);
        
        $course2 = $this->factory->post->create_and_get([
            'post_type' => 'sfwd-courses',
        ]);

        $course3 = $this->factory->post->create_and_get([
            'post_type' => 'sfwd-courses',
        ]);

        wp_set_current_user( $user->ID );

        ld_update_course_access( $user->ID, $course1->ID );
        ld_update_course_access( $user->ID, $course2->ID );
        ld_update_course_access( $user->ID, $course3->ID );

        learndash_activity_start_course( $user->ID, $course1->ID, time() );
        learndash_activity_start_course( $user->ID, $course2->ID, time() );
        learndash_activity_start_course( $user->ID, $course3->ID, time() );

        // Query the posts using LD CG
        $atts = [
            'post_type' => 'sfwd-courses',
            'orderby' => 'post_title',
            'order' => 'ASC',
            'per_page' => -1,
            'enrollment_status' => 'enrolled',
            'progress_status' => 'not_started',
        ];

        $query_args = Utilities::build_posts_query_args( $atts );
        
        $query = new WP_Query( $query_args );
        $posts = $query->get_posts();

        $this->assertCount( 0, $posts );

        // Different query

        // Query the posts using LD CG
        $atts = [
            'post_type' => 'sfwd-courses',
            'orderby' => 'post_title',
            'order' => 'ASC',
            'per_page' => -1,
        ];

        $query_args = Utilities::build_posts_query_args( $atts );
        
        $query = new WP_Query( $query_args );
        $posts = $query->get_posts();

        $this->assertCount( 13, $posts );
    }
}
