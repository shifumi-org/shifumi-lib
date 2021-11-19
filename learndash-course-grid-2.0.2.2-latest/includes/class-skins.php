<?php
namespace LearnDash\Course_Grid;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

use LearnDash\Course_Grid\Utilities;

class Skins
{
    private $registered_skins;

    private $registered_cards;

    public function __construct()
    {
        $this->register_skins();
        $this->register_cards();

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_skin_assets' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_skin_assets' ] );
    }

    public function get_default_editor_fields()
    {
        return apply_filters( 'learndash_course_grid_editor_fields', [
            'post_type',
            'per_page',
            'orderby',
            'order',
            'taxonomies',
            'thumbnail',
            'thumbnail_size',
            'ribbon',
            'content',
            'title',
            'title_clickable',
            'description',
            'description_char_max',
            'meta',
            'button',
            'pagination',
            'skin',
            'columns',
            'items_per_row',
            'grid_height_equal',
            'font_family_title',
            'font_family_description',
            'font_size_title',
            'font_size_description',
            'font_color_title',
            'font_color_description',
            'background_color_title',
            'background_color_description',
            // Misc
            'class_name',
            'id',
            // Filter
            'filter_search',
            'filter_taxonomies',
            'filter_price',
            'filter_price_min',
            'filter_price_max',
        ] );
    }

    public function register_skins()
    {
        $this->registered_skins = [
            'grid' => [
                'slug' => 'grid',
                'label' => __( 'Grid', 'learndash-course-grid' ),
                'disable' => [
                    'items_per_row',
                ]
            ],
            'masonry' => [
                'slug' => 'masonry',
                'label' => __( 'Masonry', 'learndash-course-grid' ),
                'disable' => [
                    'items_per_row',
                    'grid_height_equal',
                ],
                'script_dependencies' => [
                    'masonry' => [
                        'url' => LEARNDASH_COURSE_GRID_PLUGIN_ASSET_URL . 'lib/masonry/masonry.pkgd.min.js',
                        'version' => '4.2.2',
                    ]
                ]
            ],
            'list' => [
                'slug' => 'list',
                'label' => __( 'List', 'learndash-course-grid' ),
                'disable' => [
                    'columns',
                    'items_per_row',
                    'grid_height_equal',
                ]
            ],
        ];
    
        return apply_filters( 'learndash_course_grid_skins', $this->registered_skins );
    }

    public function register_cards()
    {
        $this->registered_cards = [
            'grid-1' => [
                'label' => __( 'Grid 1', 'learndash-course-grid' ),
                'skins' => [ 'grid', 'masonry' ],
                'elements' => [
                    'thumbnail',
                    'ribbon',
                    'content',
                    'title',
                    'meta',
                ]
            ],
            'grid-2' => [
                'label' => __( 'Grid 2', 'learndash-course-grid' ),
                'skins' => [ 'grid', 'masonry' ],
                'elements' => [
                    'thumbnail',
                    'ribbon',
                    'content',
                    'title',
                    'description',
                    'meta',
                    'button',
                ]
            ],
            'grid-3' => [
                'label' => __( 'Grid 3', 'learndash-course-grid' ),
                'skins' => [ 'grid', 'masonry' ],
                'elements' => [
                    'thumbnail',
                    'content',
                    'title',
                    'description',
                    'meta',
                    'button',
                ]
            ],
            'list-1' => [
                'label' => __( 'List 1', 'learndash-course-grid' ),
                'skins' => [ 'list' ],
                'elements' => [
                    'thumbnail',
                    'ribbon',
                    'content',
                    'title',
                    'description',
                    'meta',
                    'button',
                ]
            ],
            'list-2' => [
                'label' => __( 'List 2', 'learndash-course-grid' ),
                'skins' => [ 'list' ],
                'elements' => [
                    'thumbnail',
                    'ribbon',
                    'content',
                    'title',
                    'description',
                    'meta',
                ]
            ],
        ];
    
        return apply_filters( 'learndash_course_grid_cards', $this->registered_cards );
    }

    public function get_skins()
    {
        return $this->registered_skins;
    }

    public function get_skin( $skin )
    {
        return $this->registered_skins[ $skin ] ?? false;
    }

    public function get_cards()
    {
        return $this->registered_cards;
    }

    public function get_card( $card )
    {
        return $this->registered_cards[ $card ] ?? false;
    }

    public function enqueue_general_assets( $enqueue_script = true )
    {
        $script = LEARNDASH_COURSE_GRID_PLUGIN_ASSET_URL . 'js/script.js';
        $script_file = LEARNDASH_COURSE_GRID_PLUGIN_ASSET_PATH . 'js/script.js';

        if ( file_exists( $script_file ) && $enqueue_script ) {
            wp_enqueue_script( 'learndash-course-grid', $script, [], LEARNDASH_COURSE_GRID_VERSION, true );

            wp_localize_script( 'learndash-course-grid', 'LearnDash_Course_Grid', [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce' => [
                    'load_posts'    => wp_create_nonce( 'ld_cg_load_posts' ),
                ]
            ] );
        }

        $style = LEARNDASH_COURSE_GRID_PLUGIN_ASSET_URL . 'css/style.css';
        $style_file = LEARNDASH_COURSE_GRID_PLUGIN_ASSET_PATH . 'css/style.css';

        if ( file_exists( $style_file ) ) {
            wp_enqueue_style( 'learndash-course-grid', $style, [], LEARNDASH_COURSE_GRID_VERSION, 'all' );
        }
    }

    public function enqueue_filter_assets( $enqueue_script = true )
    {
        $filter_style  = Utilities::get_template_url( 'filter/style.css' );

        if ( $filter_style ) {
            wp_enqueue_style( 'learndash-course-grid-filter', $filter_style, [ 'dashicons' ], LEARNDASH_COURSE_GRID_VERSION );
        }
    }

    public function enqueue_pagination_assets( $enqueue_script = true )
    {
        $pagination_style  = Utilities::get_pagination_style();

        if ( $pagination_style ) {
            wp_enqueue_style( 'learndash-course-grid-pagination', $pagination_style, [], LEARNDASH_COURSE_GRID_VERSION );
        }
    }

    public function parse_block_tags( $content )
    {
        preg_match( '/<!--.*?(\{.*?\}).*?\/-->/', $content, $matches );

        $block_tags = json_decode( $matches[1], true );

        return $block_tags;
    }

    public function parse_shortcode_tags( $content )
    {
        preg_match_all( '/\s(.*?)=(.*?)(?=\s|\])/', $content, $matches );

        $returned_matches = [];
        foreach ( $matches as $group => $match ) {
            foreach ( $match as $key => $value ) {
                $returned_matches[ $group ][ $key ] = trim( str_replace( [ '\'', '"' ], '', $value ) );
            }
        }

        return $returned_matches;
    }

    public function parse_content_shortcodes( $content, $args )
    {
        extract( $args );

        preg_match_all( '/\[learndash_course_grid.*?\]/', $content, $matches );

        foreach ( $matches[0] as $match ) {
            $sub_matches = $this->parse_shortcode_tags( $match );

            $course_grids[] = $sub_matches;

            if ( in_array( 'skin', $sub_matches[1] ) ) {
                $key = array_search( 'skin', $sub_matches[1] );
                if ( $key !== false ) {
                    $skins[] = $sub_matches[2][ $key ];
                }
            } else {
                $skins[] = 'grid';
            }

            if ( in_array( 'card', $sub_matches[1] ) ) {
                $key = array_search( 'card', $sub_matches[1] );
                if ( $key !== false ) {
                    $cards[] = $sub_matches[2][ $key ];
                }
            } else {
                $cards[] = 'grid-1';
            }
        }

        return compact( 'course_grids', 'skins', 'cards' );
    }

    public function parse_content_blocks( $content, $args )
    {
        extract( $args );

        preg_match_all( '/<\!-- wp:learndash\/ld-course-grid.*?\/-->/', $content, $matches );

        foreach ( $matches[0] as $match ) {
            $block_tags = $this->parse_block_tags( $match );

            $course_grids[] = $block_tags;

            if ( ! empty( $block_tags['skin'] ) ) {
                $skins[] = $block_tags['skin'];
            } else {
                $skins[] = 'grid';
            }

            if ( ! empty( $block_tags['card'] ) ) {
                $cards[] = $block_tags['card'];
            } else {
                $cards[] = 'grid-1';
            }
        }
        
        return compact( 'course_grids', 'skins', 'cards' );
    }

    public function enqueue_skin_assets()
    {
        global $post;
        
        $skins = [];
        $cards = [];
        $course_grids = [];
        $legacy_v1 = false;
        
        // Check widget content to load course grid assets
        $widgets = wp_get_sidebars_widgets();
        
        foreach ( $widgets as $sidebar => $widgets ) {
            if ( $sidebar === 'wp_inactive_widgets' ) {
                continue;
            }
            
            foreach ( $widgets as $widget ) {
                $widget_id = _get_widget_id_base( $widget );

                preg_match( '/-([0-9]+)$/', $widget, $widget_matches );
                $widget_number = $widget_matches[1];

                $widget_options = get_option( 'widget_' . $widget_id );

                if ( ! empty( $widget_options[ $widget_number ]['content'] ) && has_shortcode( $widget_options[ $widget_number ]['content'], 'learndash_course_grid' ) ) {
                    $args = $this->parse_content_shortcodes( $widget_options[ $widget_number ]['content'], compact( 'skins', 'course_grids', 'cards' ) );
                    extract( $args );
                }

                if ( ! empty( $widget_options[ $widget_number ]['content'] ) && strpos( $widget_options[ $widget_number ]['content'], '<!-- wp:learndash/ld-course-grid' ) !== false ) {
                    $args = $this->parse_content_blocks( $widget_options[ $widget_number ]['content'], compact( 'skins', 'course_grids', 'cards' ) );
                    extract( $args );
                }

                if ( ! empty( $widget_options[ $widget_number ]['content'] ) && $this->has_legacy_v1( $widget_options[ $widget_number ]['content'] ) ) {
                    $legacy_v1 = true;
                }
            }
        }

        // Check and load legacy v1 skin assets
        if ( 
            $post && $this->has_legacy_v1( $post->post_content )
        ) {
            $legacy_v1 = true;
        }

        if ( $legacy_v1 ) {
            $skin  = 'legacy-v1';
            $style_file = Utilities::get_skin_style( $skin );

            if ( $style_file ) {
                wp_enqueue_style( 'learndash-course-grid-skin-' . $skin, $style_file, [], LEARNDASH_COURSE_GRID_VERSION );
            }
        }

        if ( $post && has_shortcode( $post->post_content, 'learndash_course_grid' ) ) {
            $args = $this->parse_content_shortcodes( $post->post_content, compact( 'skins', 'course_grids', 'cards' ) );
            extract( $args );
        }

        if ( $post && strpos( $post->post_content, '<!-- wp:learndash/ld-course-grid' ) !== false ) {
            $args = $this->parse_content_blocks( $post->post_content, compact( 'skins', 'course_grids', 'cards' ) );
            extract( $args );
        }

        if ( ! empty( $skins ) && is_array( $skins ) ) {
            $skins = array_unique( $skins );

            foreach ( $skins as $skin ) {
                // Register dependencies
                $skin_args = $this->get_skin( $skin );
                $script_dependencies = $skin_args['script_dependencies'] ?? [];
                $style_dependencies  = $skin_args['style_dependencies'] ?? [];

                $script_keys = array_keys( $script_dependencies );
                $script_keys = array_map( function( $id ) {
                    return 'learndash-course-grid-' . $id;
                }, $script_keys );

                $style_keys  = array_keys( $style_dependencies );
                $style_keys = array_map( function( $id ) {
                    return 'learndash-course-grid-' . $id;
                }, $style_keys );

                foreach ( $script_dependencies as $id => $script ) {
                    wp_register_script( 'learndash-course-grid-' . $id, $script['url'], [], $script['version'], true );
                }

                foreach ( $style_dependencies as $id => $style ) {
                    wp_register_style( 'learndash-course-grid-' . $id, $style['url'], [], $style['version'] );
                }

                $style_file = Utilities::get_skin_style( $skin );

                if ( $style_file ) {
                    wp_enqueue_style( 'learndash-course-grid-skin-' . $skin, $style_file, $style_keys, LEARNDASH_COURSE_GRID_VERSION );
                }
    
                $script_file = Utilities::get_skin_script( $skin );

                if ( $script_file ) {
                    wp_enqueue_script( 'learndash-course-grid-skin-' . $skin, $script_file, $script_keys, LEARNDASH_COURSE_GRID_VERSION, true );
                }
            }

            $this->enqueue_general_assets();
            $this->enqueue_pagination_assets();
            $this->enqueue_filter_assets();
        }

        if ( ! empty( $cards ) && is_array( $cards ) ) {
            $cards = array_unique( $cards );

            foreach ( $cards as $card ) {
                // Register dependencies
                $card_args = $this->get_card( $card );
                $script_dependencies = $card_args['script_dependencies'] ?? [];
                $style_dependencies  = $card_args['style_dependencies'] ?? [];

                $script_keys = array_keys( $script_dependencies );
                $script_keys = array_map( function( $id ) {
                    return 'learndash-course-grid-' . $id;
                }, $script_keys );

                $style_keys  = array_keys( $style_dependencies );
                $style_keys = array_map( function( $id ) {
                    return 'learndash-course-grid-' . $id;
                }, $style_keys );

                foreach ( $script_dependencies as $id => $script ) {
                    wp_register_script( 'learndash-course-grid-' . $id, $script['url'], [], $script['version'], true );
                }

                foreach ( $style_dependencies as $id => $style ) {
                    wp_register_style( 'learndash-course-grid-' . $id, $style['url'], [], $style['version'] );
                }

                $style_file = Utilities::get_card_style( $card );

                if ( $style_file ) {
                    wp_enqueue_style( 'learndash-course-grid-card-' . $card, $style_file, $style_keys, LEARNDASH_COURSE_GRID_VERSION );
                }
    
                $script_file = Utilities::get_card_script( $card );

                if ( $script_file ) {
                    wp_enqueue_script( 'learndash-course-grid-card-' . $card, $script_file, $script_keys, LEARNDASH_COURSE_GRID_VERSION, true );
                }
            }
        }

        if ( ! empty( $course_grids ) && is_array( $course_grids ) ) {
            /**
             * Prints scripts or data in the head tag on the front end.
             */
            add_action( 'wp_head', function() use ( $course_grids ) {
                $this->enqueue_custom_assets( $course_grids );
            } );
        }
    }

    public function enqueue_editor_skin_assets()
    {
        global $post;

        $skins = $this->get_skins();

        foreach ( $skins as $id => $skin ) {
            // Register dependencies
            $skin_args = $this->get_skin( $id );
            $script_dependencies = $skin_args['script_dependencies'] ?? [];
            $style_dependencies  = $skin_args['style_dependencies'] ?? [];

            $script_keys = array_keys( $script_dependencies );
            $script_keys = array_map( function( $id ) {
                return 'learndash-course-grid-' . $id;
            }, $script_keys );

            $style_keys  = array_keys( $style_dependencies );
            $style_keys = array_map( function( $id ) {
                return 'learndash-course-grid-' . $id;
            }, $style_keys );

            foreach ( $script_dependencies as $id => $script ) {
                wp_register_script( 'learndash-course-grid-' . $id, $script['url'], [], $script['version'], false );
            }

            foreach ( $style_dependencies as $id => $style ) {
                wp_register_style( 'learndash-course-grid-' . $id, $style['url'], [], $style['version'] );
            }

            $style_file = Utilities::get_skin_style( $id );

            if ( $style_file ) {
                wp_enqueue_style( 'learndash-course-grid-skin-' . $id, $style_file, $style_keys, LEARNDASH_COURSE_GRID_VERSION );
            }

            $script_file = Utilities::get_skin_script( $id );
            if ( $script_file ) {
                wp_enqueue_script( 'learndash-course-grid-skin-' . $id, $script_file, $script_keys, LEARNDASH_COURSE_GRID_VERSION, true );
            }
        }

        // Check and load legacy v1 asssets
        $skin  = 'legacy-v1';
        $style_file = Utilities::get_skin_style( $skin );

        if ( $style_file ) {
            wp_enqueue_style( 'learndash-course-grid-skin-' . $skin, $style_file, [], LEARNDASH_COURSE_GRID_VERSION );
        }

        $cards = $this->get_cards();
        
        foreach ( $cards as $id => $card ) {
            // Register dependencies
            $skin_args = $this->get_card( $id );
            $script_dependencies = $card_args['script_dependencies'] ?? [];
            $style_dependencies  = $card_args['style_dependencies'] ?? [];

            $script_keys = array_keys( $script_dependencies );
            $script_keys = array_map( function( $id ) {
                return 'learndash-course-grid-' . $id;
            }, $script_keys );

            $style_keys  = array_keys( $style_dependencies );
            $style_keys = array_map( function( $id ) {
                return 'learndash-course-grid-' . $id;
            }, $style_keys );

            foreach ( $script_dependencies as $id => $script ) {
                wp_register_script( 'learndash-course-grid-' . $id, $script['url'], [], $script['version'], false );
            }

            foreach ( $style_dependencies as $id => $style ) {
                wp_register_style( 'learndash-course-grid-' . $id, $style['url'], [], $style['version'] );
            }

            $style_file = Utilities::get_card_style( $id );

            if ( $style_file ) {
                wp_enqueue_style( 'learndash-course-grid-card-' . $id, $style_file, $style_keys, LEARNDASH_COURSE_GRID_VERSION );
            }

            $script_file = Utilities::get_card_script( $id );
            if ( $script_file ) {
                wp_enqueue_script( 'learndash-course-grid-card-' . $id, $script_file, $script_keys, LEARNDASH_COURSE_GRID_VERSION, false );
            }
        }

        // Add custom CSS wrapper
        add_action( 'admin_head', function() {
            ?>
            <style id="learndash-course-grid-custom-css"></style>
            <?php
        }, 100 );

        $this->enqueue_general_assets();
        $this->enqueue_pagination_assets( false );
        $this->enqueue_filter_assets( false );

        wp_enqueue_script( 'learndash-course-grid-block-editor-helper', LEARNDASH_COURSE_GRID_PLUGIN_URL . 'includes/gutenberg/assets/js/editor.js', [], LEARNDASH_COURSE_GRID_VERSION, true );
    }

    public function has_legacy_v1( $content )
    {
        if ( ( 
            preg_match( '/\[ld_.*?_list/', $content ) 
            && ! preg_match( '/\[ld_.*?_list.*?course_grid=(?:"|\')*false(?:"|\')*/', $content ) 
        ) || ( 
            preg_match( '/<!-- wp:learndash\/ld-.*?-list/', $content ) 
            && ! preg_match( '/<!-- wp:learndash\/ld-.*?-list.*?course_grid":false/', $content ) 
        ) ) {
            return true;
        } else {
            return false;
        }
    }

    public function generate_custom_css( $args = [] )
    {
        // Parse args first
        if ( isset( $args[1] ) && $args[2] ) {
            $temp_args = [];
            foreach ( $args[1] as $index => $key ) {
                $temp_args[ $key ] = $args[2][ $index ];
            }
            $args = $temp_args;
        }

        // Bail if the element doesn't have ID
        if ( empty( $args['id'] ) ) {
            return false;
        }

        $default_atts = \LearnDash\course_grid()->shortcodes->learndash_course_grid->get_default_atts();

        $skin = ! empty( $args['skin'] ) ? $args['skin'] : $default_atts['skin'];
        $columns =  ! empty( $args['columns'] ) ? $args['columns'] : $default_atts['columns'];
        $grid_height_equal =  ! empty( $args['grid_height_equal'] ) ? $args['grid_height_equal'] : $default_atts['grid_height_equal'];
        $grid_height_equal = filter_var( $grid_height_equal, FILTER_VALIDATE_BOOLEAN );

        $font_family_title = ! empty( $args['font_family_title'] ) ? $args['font_family_title'] : $default_atts['font_family_title'];
        $font_size_title =  ! empty( $args['font_size_title'] ) ? $args['font_size_title'] : $default_atts['font_size_title'];
        $font_color_title =  ! empty( $args['font_color_title'] ) ? $args['font_color_title'] : $default_atts['font_color_title'];
        $background_color_title =  ! empty( $args['background_color_title'] ) ? $args['background_color_title'] : $default_atts['background_color_title'];

        $font_family_description =  ! empty( $args['font_family_description'] ) ? $args['font_family_description'] : $default_atts['font_family_description'];
        $font_size_description = ! empty( $args['font_size_description'] ) ? $args['font_size_description'] : $default_atts['font_size_description'];
        $font_color_description = ! empty( $args['font_color_description'] ) ? $args['font_color_description'] : $default_atts['font_color_description'];
        $background_color_description = ! empty( $args['background_color_description'] ) ? $args['background_color_description'] : $default_atts['background_color_description'];

        ob_start();
        ?>
        
        <?php // Columns ?>
        <?php if ( in_array( $skin, [ 'grid', 'masonry' ] ) ) : ?>
        <?php echo '#' . esc_html( $args['id'] ) . ' .' . esc_html( $skin ); ?> {
            grid-template-columns: repeat( <?php echo esc_html( $columns ); ?>, minmax( 0, 1fr ) );
        }
        <?php endif; ?>

        <?php // Grid Height Equal ?>
        <?php if ( $grid_height_equal && 'grid' == $skin ) : ?>
            <?php echo '#' . esc_html( $args['id'] ) . ' .grid' . ' > .item > .post'; ?>,
            <?php echo '#' . esc_html( $args['id'] ) . ' .grid' . ' > .item .content'; ?> {
                display: flex;
                flex-direction: column;
                height: 100%;
            }

            
            <?php echo '#' . esc_html( $args['id'] ) . ' .grid' . ' > .item .content > *:last-child'; ?> {
                margin-top: auto;
            }
        <?php endif; ?>

        <?php // Styles ?>
        <?php echo '#' . esc_html( $args['id'] ) . ' .' . esc_html( $skin ) . ' > .item .content .entry-title'; ?>,
        <?php echo '#' . esc_html( $args['id'] ) . ' .' . esc_html( $skin ) . ' > .item .content .entry-title *'; ?> {
            <?php if ( ! empty( $font_family_title ) ) : ?>
                font-family: <?php echo html_entity_decode( $font_family_title ); ?>;
            <?php endif; ?>

            <?php if ( ! empty( $font_size_title ) ) : ?>
                font-size: <?php echo esc_html( $font_size_title ); ?>;
            <?php endif; ?>
        }

        <?php echo '#' . esc_html( $args['id'] ) . ' .' . esc_html( $skin ) . ' > .item .content .entry-title'; ?> {
            <?php if ( ! empty( $background_color_title ) ) : ?>
                padding: 10px;
                border-radius: 5px;
                background-color: <?php echo esc_html( $background_color_title ); ?>;
            <?php endif; ?>

            <?php if ( ! empty( $font_color_title ) ) : ?>
                color: <?php echo esc_html( $font_color_title ); ?>;
            <?php endif; ?>
        }

        <?php echo '#' . esc_html( $args['id'] ) . ' .' . esc_html( $skin ) . ' > .item .content .entry-title *'; ?> {
            <?php if ( ! empty( $font_color_title ) ) : ?>
                color: <?php echo esc_html( $font_color_title ); ?>;
            <?php endif; ?>
        }

        <?php // Description ?>

        <?php echo '#' . esc_html( $args['id'] ) . ' .' . esc_html( $skin ) . ' > .item .content .entry-content'; ?> {
            <?php if ( ! empty( $font_family_description ) ) : ?>
                font-family: <?php echo html_entity_decode( $font_family_description ); ?>;
            <?php endif; ?>

            <?php if ( ! empty( $font_size_description ) ) : ?>
                font-size: <?php echo esc_html( $font_size_description ); ?>;
            <?php endif; ?>

            <?php if ( ! empty( $background_color_description ) ) : ?>
                padding: 10px;
                border-radius: 5px;
                background-color: <?php echo esc_html( $background_color_description ); ?>;
            <?php endif; ?>
        }

        <?php echo '#' . esc_html( $args['id'] ) . ' .' . esc_html( $skin ) . ' > .item .content .entry-content *'; ?> {
            <?php if ( ! empty( $font_color_description ) ) : ?>
                color: <?php echo esc_html( $font_color_description ); ?>;
            <?php endif; ?>
        }

        <?php

        $custom_css = ob_get_clean();

        return preg_replace( '/\s{1,}(?!id|\.|\#|\*|\+|\~|\>|\"|\')/', '', $custom_css );
    }

    public function enqueue_custom_assets( $course_grids )
    {
        ob_start();

        echo '<style id="learndash-course-grid-custom-css">';
        
        foreach ( $course_grids as $args ) {
            echo $this->generate_custom_css( $args );
            do_action( 'learndash_course_grid_custom_css', $args );
        }
        
        echo '</style>';

        $assets = ob_get_clean();
        echo $assets;
    }
}
