/**
 * LearnDash Block ld-course-grid-filter
 *
 * @since 2.0
 */

/**
 * Internal block libraries
 */
import { __, _x, sprintf } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls, InspectorAdvancedControls } from '@wordpress/block-editor'
import { Fragment } from '@wordpress/element'
import { Panel, PanelBody, TextControl, ToggleControl, SelectControl, ColorPalette, ColorIndicator, BaseControl } from '@wordpress/components'
import ServerSideRender from '@wordpress/server-side-render'
import FilterPanelBody from '../components/filter-panel-body.js'

registerBlockType( 
    'learndash/ld-course-grid-filter', 
    {
        title: __( 'LearnDash Course Grid Filter', 'learndash-course-grid' ),
        description: __( 'LearnDash course grid filter widget.', 'learndash-course-grid' ),
        icon: 'filter',
        category: 'learndash-blocks',
        supports: {
            customClassName: false,
        },
        attributes: {
            course_grid_id: {
                type: 'string',
                default: '',
            },
            search: {
                type: 'boolean',
                default: 1,
            },
            taxonomies: {
                type: 'array',
                default: [ 'category', 'post_tag' ],
            },
            price: {
                type: 'boolean',
                default: 1,
            },
            price_min: {
                type: 'string',
                default: 0,
            },
            price_max: {
                type: 'string',
                default: 1000,
            },
            preview_show: {
                type: 'boolean',
                default: 1,
            }
        },

        edit: ( props ) => {
            const {
                attributes: {
                    course_grid_id,
                    search,
                    taxonomies,
                    price,
                    price_min,
                    price_max,
                    preview_show
                },
                setAttributes,
            } = props;

            const taxonomies_options = LearnDash_Course_Grid_Block_Editor.taxonomies;

            const inspectorControls = (
                <Fragment key={ 'learndash-course-grid-filter-settings' }>
                    <InspectorControls 
                        key="controls"
                    >
                        <Panel
                            className={ 'learndash-course-grid-filter-panel' }
                        >
                            <FilterPanelBody
                                context={ 'widget' }
                                course_grid_id={ course_grid_id }
                                search={ search }
                                taxonomies={ taxonomies }
                                price={ price }
                                price_min={ price_min }
                                price_max={ price_max }
                                setAttributes={ setAttributes }
                            />
                            <PanelBody
                                title = { __( 'Preview', 'learndash-course-grid' ) }
                                initialOpen={ false }
                            >
                                <ToggleControl
                                    label={ __( 'Show Preview', 'learndash-course-grid' ) }
                                    checked={ !! preview_show }
                                    onChange={ ( preview_show ) =>
                                        setAttributes( { preview_show } )
                                    }
                                />
                            </PanelBody>
                        </Panel>
                    </InspectorControls>
                </Fragment>
            )

            function do_serverside_render( attributes ) {
                if ( attributes.preview_show == true ) {
                    // We add the meta so the server knowns what is being edited.
                    // attributes.meta = ldlms_get_post_edit_meta()

                    return (
                        <ServerSideRender
                            block="learndash/ld-course-grid-filter"
                            attributes={ attributes }
                            key="learndash/ld-course-grid-filter"
                        />
                    )
                } else {
                    return __(
                        '[learndash_course_grid_filter] shortcode output shown here',
                        'learndash-course-grid'
                    )
                }
            }
        
            return [ 
                inspectorControls, 
                do_serverside_render( props.attributes ) 
            ];
        },

        save: ( props ) => {
            
        },
    } 
);
