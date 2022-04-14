<?php
namespace LearnDash\Course_Grid\Tests;

use WP_UnitTestCase;

class PostAttributesTest extends WP_UnitTestCase
{
    /**
     * @test
     * @dataProvider prices_provider
     */
    public function it_parses_price_to_expected_format( $input, $expected )
    {
        add_filter( 'learndash_course_grid_currency', function() {
            return '$';
        } );

        $user = $this->factory->user->create_and_get();

        if ( $user ) {
            $course = $this->factory->post->create_and_get( [ 'post_type' => 'sfwd-courses', 'post_author' => $user->ID ] );

            if ( $course ) {
                $meta = [
                    'sfwd-courses_course_price' => $input,
                    'sfwd-courses_course_price_type' => 'paynow',
                ];
    
                update_post_meta( $course->ID, '_sfwd-courses', $meta );
    
                $atts = learndash_course_grid_prepare_template_post_attributes( $course->ID, [] );
                
                $this->assertSame( $expected, $atts['ribbon_text'] );
            }
        }
    }
    
    public function prices_provider()
    {
        return [
            [ '1,999', '$1,999' ],
            [ '1999', '$1,999' ], 
            [ '1999.00', '$1,999.00' ],
            [ '1999.95', '$1,999.95' ],
            [ '$1999', '$1,999' ],
            [ '$1999.95', '$1,999.95' ],
            [ '$1999.95', '$1,999.95' ],
            [ 'USD 1999', '$1,999' ],
            [ 'USD 1,999', '$1,999' ],
            [ 'USD 1999.95', '$1,999.95' ],
            [ 'USD 1,999.95', '$1,999.95' ],
            [ 'USD1999', '$1,999' ],
            [ 'USD1,999', '$1,999' ],
            [ 'USD1999.95', '$1,999.95' ],
            [ 'USD1,999.95', '$1,999.95' ],
            [ '1,999.95$', '$1,999.95' ],
            [ '1999.95$', '$1,999.95' ],
            [ '1999$', '$1,999' ],
            [ '1,999.95USD', '$1,999.95' ],
            [ '1,999.95 USD', '$1,999.95' ],
            [ '1999.95USD', '$1,999.95' ],
            [ '1999.95 USD', '$1,999.95' ],
            [ '1999USD', '$1,999' ],
            [ '1999 USD', '$1,999' ],
            [ '1,999,999', '$1,999,999', ],
            [ '1,999,999.95', '$1,999,999.95', ],
            [ '1999999', '$1,999,999', ],
            [ '1999999.95', '$1,999,999.95', ],
            [ '14,95', '$14,95'],
            [ '2.000,95', '$2.000,95'],
            [ '2000,95', '$2.000,95'],
        ];
    }
}
