/**
 * FilterPanelBody component
 *
 * @since 2.0
 */

/**
 * Internal block libraries
 */
import { Component } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { PanelBody, TextControl, ToggleControl, SelectControl, BaseControl } from '@wordpress/components'

class FilterPanelBody extends Component {
    constructor( props ) {
        super( props );
    }

    render() {
        const {
            context,
            course_grid_id,
            search,
            taxonomies,
            price,
            price_min,
            price_max,
            setAttributes
        } = this.props;

        let search_key = 'search';
        let taxonomies_key = 'taxonomies';
        let price_key = 'price';
        let price_min_key = 'price_min';
        let price_max_key = 'price_max';

        if ( context == 'page' ) {
            search_key = 'filter_search';
            taxonomies_key = 'filter_taxonomies';
            price_key = 'filter_price';
            price_min_key = 'filter_price_min';
            price_max_key = 'filter_price_max';
        }

        const taxonomies_options = LearnDash_Course_Grid_Block_Editor.taxonomies;

        return (
            <PanelBody
                title={ __( 'Filter', 'learndash-course-grid' ) }
                initialOpen={ context == 'page' ? false : true }
            >
                { context == 'widget' && 
                    <TextControl
                        label={ __( 'Course Grid ID' ) }
                        help={ __( 'Course grid ID the filter is for.' ) }
                        value={ course_grid_id || '' }
                        type={ 'text' }
                        onChange={ ( course_grid_id ) => setAttributes( { course_grid_id } ) } 
                    />
                }
                <ToggleControl 
                    label={ __( 'Search', 'learndash-course-grid' ) }
                    checked={ search }
                    onChange={ ( search ) => {
                        const search_obj = {
                            [ search_key ]: search,
                        }

                        setAttributes( search_obj );
                    }  }
                />
                <BaseControl>
                    <SelectControl
                        multiple
                        label={ __( 'Taxonomies', 'learndash-course-grid' ) }
                        help={ __( 'Hold ctrl on Windows or cmd on Mac to select multiple values.', 'learndash-course-grid' ) }
                        options={ taxonomies_options }
                        value={ taxonomies || [] }
                        onChange={ ( taxonomies ) => {
                            const taxonomies_obj = {
                                [ taxonomies_key ]: taxonomies,
                            }

                            setAttributes( taxonomies_obj );
                        } }
                    />
                </BaseControl>
                <ToggleControl 
                    label={ __( 'Price', 'learndash-course-grid' ) }
                    checked={ price }
                    onChange={ ( price ) => {
                        const price_obj = {
                            [ price_key ]: price,
                        }

                        setAttributes( price_obj );
                    }  }
                />
                <BaseControl>
                    <TextControl
                        label={ __( 'Price Min' ) }
                        className={ 'left' }
                        value={ price_min || 0 }
                        type={ 'number' }
                        onChange={ ( price_min ) => {
                            const price_min_obj = {
                                [ price_min_key ]: price_min,
                            }
    
                            setAttributes( price_min_obj );
                        }  }
                    />
                    <TextControl
                        label={ __( 'Price Max' ) }
                        className={ 'right' }
                        value={ price_max || 0 }
                        type={ 'number' }
                        onChange={ ( price_max ) => {
                            const price_max_obj = {
                                [ price_max_key ]: price_max,
                            }
    
                            setAttributes( price_max_obj );
                        }  }
                    />
                    <div style={ { clear: 'both' } }></div>
                </BaseControl>
            </PanelBody>
        );
    }
}

export default FilterPanelBody;