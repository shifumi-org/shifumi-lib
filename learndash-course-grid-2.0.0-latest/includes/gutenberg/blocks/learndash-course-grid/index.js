/**
 * LearnDash Block ld-course-grid
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

function generate_unique_id() {
    const id = Date.now().toString( 36 ) + Math.random().toString( 36 ).substr( 2 );
    return 'ld-cg-' + id.substr( 0, '10' );
}

registerBlockType( 
    'learndash/ld-course-grid', 
    {
        title: __( 'LearnDash Course Grid', 'learndash-course-grid' ),
        description: __( 'Build LearnDash course grid easily.', 'learndash-course-grid' ),
        icon: 'grid-view',
        category: 'learndash-blocks',
        supports: {
            customClassName: true,
        },
        attributes: {
            // Query
            post_type: {
                type: 'string',
                default: 'sfwd-courses',
            },
            per_page: {
                type: 'string',
                default: 9,
            },
            orderby: {
                type: 'string',
                default: 'ID',
            },
            order: {
                type: 'string',
                default: 'DESC',
            },
            taxonomies: {
                type: 'string',
                default: '',
            },
            // Elements
            thumbnail: {
                type: 'boolean',
                default: 1,
            },
            thumbnail_size: {
                type: 'string',
                default: 'course-thumbnail'
            },
            ribbon: {
                type: 'boolean',
                default: 1,
            },
            content: {
                type: 'boolean',
                default: 1,
            },
            title: {
                type: 'boolean',
                default: 1,
            },
            title_clickable: {
                type: 'boolean',
                default: 1,
            },
            description: {
                type: 'boolean',
                default: 1,
            },
            description_char_max: {
                type: 'string',
                default: 120,
            },
            meta: {
                type: 'boolean',
                default: 1,
            },
            button: {
                type: 'boolean',
                default: 1,
            },
            pagination: {
                type: 'string',
                default: 'button',
            },
            grid_height_equal: {
                type: 'boolean',
                default: 0,
            },
            progress_bar: {
                type: 'boolean',
                default: 0,
            },
            filter: {
                type: 'boolean',
                default: 1,
            },
            // Templates
            skin: {
                type: 'string',
                default: 'grid',
            },
            card: {
                type: 'string',
                default: 'grid-1',
            },
            columns: {
                type: 'string',
                default: 3,
            },
            min_column_width: {
                type: 'string',
                default: 250,
            },
            items_per_row: {
                type: 'string',
                default: 5,
            },
            // Styles
            font_family_title: {
                type: 'string',
            },
            font_family_description: {
                type: 'string',
                default: '',
            },
            font_size_title: {
                type: 'string',
                default: '',
            },
            font_size_description: {
                type: 'string',
                default: '',
            },
            font_color_title: {
                type: 'string',
                default: '',
            },
            font_color_description: {
                type: 'string',
                default: '',
            },
            background_color_title: {
                type: 'string',
                default: '',
            },
            background_color_description: {
                type: 'string',
                default: '',
            },
            // Misc
            id: {
                type: 'string',
                default: '',
            },
            preview_show: {
                type: 'boolean',
                default: 1,
            },
            display_state: {
                type: 'object',
                default: {},
            },
            // Filter
            filter_search: {
                type: 'boolean',
                default: 1,
            },
            filter_taxonomies: {
                type: 'array',
                default: [ 'category', 'post_tag' ],
            },
            filter_price: {
                type: 'boolean',
                default: 1,
            },
            filter_price_min: {
                type: 'string',
                default: 0,
            },
            filter_price_max: {
                type: 'string',
                default: 1000,
            },
        },

        edit: ( props ) => {
            const {
                attributes: {
                    post_type,
                    per_page,
                    orderby,
                    order,
                    taxonomies,
                    thumbnail,
                    thumbnail_size,
                    ribbon,
                    content,
                    title,
                    title_clickable,
                    description,
                    description_char_max,
                    meta,
                    button,
                    pagination,
                    grid_height_equal,
                    progress_bar,
                    filter,
                    skin,
                    card,
                    columns,
                    min_column_width,
                    items_per_row,
                    font_family_title,
                    font_family_description,
                    font_size_title,
                    font_size_description,
                    font_color_title,
                    font_color_description,
                    background_color_title,
                    background_color_description,
                    id,
                    display_state,
                    preview_show,
                    filter_search,
                    filter_taxonomies,
                    filter_price,
                    filter_price_min,
                    filter_price_max,
                },
                className,
                setAttributes,
            } = props;

            if ( id == '' ) {
                const temp_id = generate_unique_id();
                setAttributes( { id: temp_id } );
            }

            const post_type_options = LearnDash_Course_Grid_Block_Editor.post_types;

            const pagination_options = LearnDash_Course_Grid_Block_Editor.paginations;

            const skins = LearnDash_Course_Grid_Block_Editor.skins;
            const cards = LearnDash_Course_Grid_Block_Editor.cards;

            const skin_options = [],
                skin_disabled_fields = {};
            for ( const id in skins ) {
                if ( Object.hasOwnProperty.call( skins, id ) ) {
                    const element = {
                        label: skins[ id ].label,
                        value: skins[ id ].slug
                    };

                    skin_options.push( element );

                    if ( Object.hasOwnProperty.call( skins[ id ], 'disable' ) ) {
                        skin_disabled_fields[ skins[ id ].slug ] = skins[ id ].disable;
                    }
                }
            }

            const card_options = [],
                card_values = [],
                skin_cards = {},
                card_disabled_fields = {};
            for ( const id in cards ) {
                if ( Object.hasOwnProperty.call( cards, id ) ) {
                    if ( Object.hasOwnProperty.call( cards[ id ], 'disable' ) ) {
                        card_disabled_fields[ cards[ id ] ] = cards[ id ].disable;
                    }

                    if ( Object.hasOwnProperty.call( cards[ id ], 'skins' ) ) {
                        cards[ id ].skins.forEach( function( temp_skin ) {
                            skin_cards[ temp_skin ] = skin_cards[ temp_skin ] || [];
    
                            skin_cards[ temp_skin ].push( id );
                        } );
                    }

                    if ( typeof cards[ id ].skins !== 'undefined' && cards[ id ].skins.indexOf( skin ) > -1 ) {
                        const element = {
                            label: cards[ id ].label,
                            value: id
                        };
    
                        card_options.push( element );
                        card_values.push( id );
                    }
                }
            }

            const thumbnail_size_options = LearnDash_Course_Grid_Block_Editor.image_sizes;

            const orderby_options = LearnDash_Course_Grid_Block_Editor.orderby;

            const order_options = [
                { label: __( 'Ascending', 'learndash-course-grid' ), value: 'ASC' },
                { label: __( 'Descending', 'learndash-course-grid' ), value: 'DESC' },
            ];

            selectSkin( props );

            const inspectorControls = (
                <Fragment key={ 'learndash-course-grid-settings' }>
                    <InspectorControls 
                        key="controls"
                    >
                        <Panel
                            className={ 'learndash-course-grid-panel' }
                        >
                            <PanelBody
                                title={ __( 'Template', 'learndash-course-grid' ) }
                                initialOpen={ true }
                            >
                                <BaseControl
                                    className={ typeof display_state.skin !== 'undefined' && ! display_state.skin ? 'hide' : 'show' }
                                >
                                    <SelectControl
                                        label={ __( 'Skin', 'learndash-course-grid' ) }
                                        options={ skin_options }
                                        value={ skin || '' }
                                        onChange={ 
                                            ( skin ) => { 
                                                setAttributes( { skin } );
                                                selectSkin( props );
                                            }
                                        }
                                    />
                                </BaseControl>
                                <BaseControl
                                    className={ typeof display_state.card !== 'undefined' && ! display_state.card ? 'hide' : 'show' }
                                >
                                    <SelectControl
                                        label={ __( 'Card', 'learndash-course-grid' ) }
                                        options={ card_options }
                                        value={ card || '' }
                                        onChange={ 
                                            ( card ) => { 
                                                setAttributes( { card } );
                                            }
                                        }
                                    />
                                </BaseControl>
                                <TextControl
                                    label={ __( 'Columns' ) }
                                    value={ columns || '' }
                                    type={ 'number' }
                                    onChange={ ( columns ) => setAttributes( { columns } ) }
                                    className={ typeof display_state.columns !== 'undefined' && ! display_state.columns ? 'hide' : 'show' }
                                />
                                {
                                    [ 'grid', 'masonry' ].indexOf( skin ) > -1 &&
                                    <TextControl
                                        label={ __( 'Min Column Width (in pixel)', 'learndash-course-grid' ) }
                                        value={ min_column_width }
                                        type={ 'number' }
                                        help={ __( 'If column width reach value lower than this, the grid columns number will automatically be adjusted on display.', 'learndash-course-grid' ) }
                                        onChange={ ( min_column_width ) => setAttributes( { min_column_width } ) }
                                        className={ typeof display_state.min_column_width !== 'undefined' && ! display_state.min_column_width ? 'hide' : 'show' }
                                    />
                                }
                                <TextControl
                                    label={ __( 'Items Per Row' ) }
                                    help={ __( 'Number of items per row. Certain skins use this to customize the design.' ) }
                                    value={ items_per_row || '' }
                                    type={ 'number' }
                                    onChange={ ( items_per_row ) => setAttributes( { items_per_row } ) }
                                    className={ typeof display_state.items_per_row !== 'undefined' && ! display_state.items_per_row ? 'hide' : 'show' }
                                />
                            </PanelBody>
                            <PanelBody 
                                title={ __( 'Query', 'learndash-course-grid' ) }
                                initialOpen={ false }
                            >
                                <BaseControl
                                    className={ typeof display_state.post_type !== 'undefined' && ! display_state.post_type ? 'hide' : 'show' }
                                >
                                    <SelectControl
                                        label={ __( 'Post Type', 'learndash-course-grid' ) }
                                        options={ post_type_options }
                                        value={ post_type || '' }
                                        onChange={ ( post_type ) => setAttributes( { post_type } )}
                                    />
                                </BaseControl>
                                <TextControl
                                    label={ __( 'Posts per page' ) }
                                    help={ __( 'Enter 0 show all items.', 'learndash-course-grid' ) }
                                    value={ per_page || '' }
                                    type={ 'number' }
                                    onChange={ ( per_page ) => setAttributes( { per_page } ) }
                                    className={ typeof display_state.per_page !== 'undefined' && ! display_state.per_page ? 'hide' : 'show' }
                                />
                                <BaseControl
                                    className={ typeof display_state.orderby !== 'undefined' && ! display_state.orderby ? 'hide' : 'show' }
                                >
                                    <SelectControl
                                        label={ __( 'Order By', 'learndash-course-grid' ) }
                                        options={ orderby_options }
                                        value={ orderby || '' }
                                        onChange={ ( orderby ) => setAttributes( { orderby } )}
                                    />
                                </BaseControl>
                                <BaseControl
                                    className={ typeof display_state.order !== 'undefined' && ! display_state.order ? 'hide' : 'show' }
                                >
                                    <SelectControl
                                        label={ __( 'Order', 'learndash-course-grid' ) }
                                        options={ order_options }
                                        value={ order || '' }
                                        onChange={ ( order ) => setAttributes( { order } )}
                                    />
                                </BaseControl>
                                <TextControl
                                    label={ __( 'Taxonomies', 'learndash-course-grid' ) }
                                    help={ __( 'Format:', 'learndash-course-grid' ) + ' "taxonomy1:term1,term2; taxonomy2:term1,term2;"' }
                                    value={ taxonomies || '' }
                                    onChange={ ( taxonomies ) => setAttributes( { taxonomies } ) }
                                    className={ typeof display_state.taxonomies !== 'undefined' && ! display_state.taxonomies ? 'hide' : 'show' + ' taxonomies' }
                                />
                            </PanelBody>
                            <PanelBody
                                title={ __( 'Elements', 'learndash-course-grid' ) }
                                initialOpen={ false }
                            >
                                { 
                                    ( cards[ card ].elements.indexOf( 'thumbnail' ) > -1 ) &&
                                    <ToggleControl 
                                        label={ __( 'Thumbnail', 'learndash-course-grid' ) }
                                        checked={ thumbnail }
                                        onChange={ ( thumbnail ) => setAttributes( { thumbnail } ) }
                                        className={ typeof display_state.thumbnail !== 'undefined' && ! display_state.thumbnail ? 'hide' : 'show' }
                                    />
                                }
                                { ( cards[ card ].elements.indexOf( 'thumbnail' ) > -1 ) && thumbnail && 
                                    <BaseControl
                                        className={ typeof display_state.thumbnail_size !== 'undefined' && ! display_state.thumbnail_size ? 'hide' : 'show' }
                                    >
                                        <SelectControl
                                            label={ __( 'Thumbnail Size', 'learndash-course-grid' ) }
                                            options={ thumbnail_size_options }
                                            value={ thumbnail_size || '' }
                                            onChange={ ( thumbnail_size ) => setAttributes( { thumbnail_size } ) }
                                        />
                                    </BaseControl>
                                }
                                {
                                    ( cards[ card ].elements.indexOf( 'ribbon' ) > -1 ) &&
                                    <ToggleControl 
                                        label={ __( 'Ribbon', 'learndash-course-grid' ) }
                                        checked={ ribbon }
                                        onChange={ ( ribbon ) => setAttributes( { ribbon } ) }
                                        className={ typeof display_state.ribbon !== 'undefined' && ! display_state.ribbon ? 'hide' : 'show' }
                                    />
                                } 
                                { 
                                    ( cards[ card ].elements.indexOf( 'content' ) > -1 ) &&
                                    <ToggleControl 
                                        label={ __( 'Content', 'learndash-course-grid' ) }
                                        help={ __( 'Content includes elements in the area outside of the thumbnail.', 'learndash-course-grid' ) }
                                        checked={ content }
                                        onChange={ ( content ) => setAttributes( { content } ) }
                                        className={ typeof display_state.content !== 'undefined' && ! display_state.content ? 'hide' : 'show' }
                                    />
                                }
                                {
                                    ( cards[ card ].elements.indexOf( 'title' ) > -1 ) && 
                                    <ToggleControl 
                                        label={ __( 'Title', 'learndash-course-grid' ) }
                                        checked={ title }
                                        onChange={ ( title ) => setAttributes( { title } ) }
                                        className={ typeof display_state.title !== 'undefined' && ! display_state.title ? 'hide' : 'show' }
                                    />
                                }
                                { 
                                    ( cards[ card ].elements.indexOf( 'title' ) > -1 ) &&
                                    title && 
                                    <ToggleControl 
                                        label={ __( 'Clickable Title', 'learndash-course-grid' ) }
                                        checked={ title_clickable }
                                        onChange={ ( title_clickable ) => setAttributes( { title_clickable } ) }
                                        className={ typeof display_state.title_clickable !== 'undefined' && ! display_state.title_clickable ? 'hide' : 'show' }
                                    />
                                }
                                {
                                    ( cards[ card ].elements.indexOf( 'description' ) > -1 ) &&
                                    <ToggleControl 
                                        label={ __( 'Description', 'learndash-course-grid' ) }
                                        checked={ description }
                                        onChange={ ( description ) => setAttributes( { description } ) }
                                        className={ typeof display_state.description !== 'undefined' && ! display_state.description ? 'hide' : 'show' }
                                    />
                                }
                                {
                                    ( cards[ card ].elements.indexOf( 'description' ) > -1 ) && 
                                    description &&
                                    <TextControl
                                        label={ __( 'Max Description Character Count' ) }
                                        value={ description_char_max || '' }
                                        type={ 'number' }
                                        onChange={ ( description_char_max ) => {
                                            setAttributes( { description_char_max } );
                                        }  }
                                    />
                                }
                                {
                                    ( cards[ card ].elements.indexOf( 'meta' ) > -1 ) &&
                                    <ToggleControl 
                                        label={ __( 'Meta', 'learndash-course-grid' ) }
                                        checked={ meta }
                                        onChange={ ( meta ) => setAttributes( { meta } ) }
                                        className={ typeof display_state.meta !== 'undefined' && ! display_state.meta ? 'hide' : 'show' }
                                    />
                                }
                                {
                                    ( cards[ card ].elements.indexOf( 'button' ) > -1 ) &&
                                    <ToggleControl 
                                        label={ __( 'Button', 'learndash-course-grid' ) }
                                        checked={ button }
                                        onChange={ ( button ) => setAttributes( { button } ) }
                                        className={ typeof display_state.button !== 'undefined' && ! display_state.button ? 'hide' : 'show' }
                                    />
                                }
                                <ToggleControl 
                                    label={ __( 'Progress Bar', 'learndash-course-grid' ) }
                                    help={ __( 'Available for LearnDash course and group.', 'learndash-course-grid' ) }
                                    checked={ progress_bar }
                                    onChange={ ( progress_bar ) => setAttributes( { progress_bar } ) }
                                    className={ typeof display_state.progress_bar !== 'undefined' && ! display_state.progress_bar ? 'hide' : 'show' }
                                />
                                <BaseControl
                                    className={ typeof display_state.pagination !== 'undefined' && ! display_state.pagination ? 'hide' : 'show' }
                                >
                                    <SelectControl
                                        label={ __( 'Pagination', 'learndash-course-grid' ) }
                                        options={ pagination_options }
                                        value={ pagination || '' }
                                        onChange={ ( pagination ) => setAttributes( { pagination } ) }
                                    />
                                </BaseControl>
                                <ToggleControl 
                                    label={ __( 'Filter', 'learndash-course-grid' ) }
                                    checked={ filter }
                                    onChange={ ( filter ) => {
                                        setAttributes( { filter } )
                                    } }
                                    className={ typeof display_state.filter !== 'undefined' && ! display_state.filter ? 'hide' : 'show' }
                                />
                            </PanelBody>
                            {
                                filter &&
                                <FilterPanelBody
                                    context={ 'page' }
                                    course_grid_id={ id }
                                    search={ filter_search }
                                    taxonomies={ filter_taxonomies }
                                    price={ filter_price }
                                    price_min={ filter_price_min }
                                    price_max={ filter_price_max }
                                    setAttributes={ setAttributes }
                                />
                            }
                            <PanelBody
                                title = { __( 'Styles', 'learndash-course-grid' ) }
                                initialOpen={ false }
                            >
                                { skin == 'grid' && 
                                    <div classNam="grid-style">
                                        <h3>{ __( 'Grid', 'learndash-course-grid' ) }</h3>
                                        <ToggleControl 
                                            label={ __( 'Equal Grid Height', 'learndash-course-grid' ) }
                                            checked={ grid_height_equal }
                                            onChange={ ( grid_height_equal ) => setAttributes( { grid_height_equal } ) }
                                            className={ typeof display_state.grid_height_equal !== 'undefined' && ! display_state.grid_height_equal ? 'hide' : 'show' }
                                        />
                                    </div>
                                }
                                <h3>{ __( 'Heading', 'learndash-course-grid' ) }</h3>
                                <TextControl
                                    label={ __( 'Heading Font Family' ) }
                                    value={ font_family_title || '' }
                                    onChange={ ( font_family_title ) => setAttributes( { font_family_title } ) }
                                    className={ typeof display_state.font_family_title !== 'undefined' && ! display_state.font_family_title ? 'hide' : 'show' }
                                />
                                <TextControl
                                    label={ __( 'Heading Font Size' ) }
                                    help={ __( 'Accepts full format, e.g. 18px, 2rem' ) }
                                    value={ font_size_title || '' }
                                    onChange={ ( font_size_title ) => setAttributes( { font_size_title } ) }
                                    className={ typeof display_state.font_size_title !== 'undefined' && ! display_state.font_size_title ? 'hide' : 'show' }
                                />
                                <BaseControl
                                    className={ typeof display_state.font_color_title !== 'undefined' && ! display_state.font_color_title ? 'hide color-picker' : 'show color-picker' }
                                    label={ __( 'Heading Font Color' ) }
                                >
                                    <div className="color-wrapper">
                                        <ColorIndicator
                                            colorValue={ font_color_title || '' }
                                        />
                                        <ColorPalette
                                            value={ font_color_title || '' }
                                            onChange={ ( font_color_title ) => {
                                                setAttributes( { font_color_title } );
                                            } }
                                            clearable={ true }
                                        />
                                    </div>
                                </BaseControl>
                                <BaseControl
                                    className={ typeof display_state.background_color_title !== 'undefined' && ! display_state.background_color_title ? 'hide color-picker' : 'show color-picker' }
                                    label={ __( 'Heading Background Color' ) }
                                >
                                    <div className="color-wrapper">
                                        <ColorIndicator
                                            colorValue={ background_color_title || '' }
                                        />
                                        <ColorPalette
                                            value={ background_color_title || '' }
                                            onChange={ ( background_color_title ) => {
                                                setAttributes( { background_color_title } );
                                            } }
                                            clearable={ true }
                                        />
                                    </div>
                                </BaseControl>
                                <h3>{ __( 'Description', 'learndash-course-grid' ) }</h3>
                                <TextControl
                                    label={ __( 'Description Font Family' ) }
                                    value={ font_family_description || '' }
                                    onChange={ ( font_family_description ) => setAttributes( { font_family_description } ) }
                                    className={ typeof display_state.font_family_description !== 'undefined' && ! display_state.font_family_description ? 'hide' : 'show' }
                                />
                                <TextControl
                                    label={ __( 'Description Font Size' ) }
                                    help={ __( 'Accepts full format, e.g. 18px, 2rem' ) }
                                    value={ font_size_description || '' }
                                    onChange={ ( font_size_description ) => setAttributes( { font_size_description } ) }
                                    className={ typeof display_state.font_size_description !== 'undefined' && ! display_state.font_size_description ? 'hide' : 'show' }
                                />
                                <BaseControl
                                    className={ typeof display_state.font_color_description !== 'undefined' && ! display_state.font_color_description ? 'hide color-picker' : 'show color-picker' }
                                    label={ __( 'Description Font Color' ) }
                                >
                                    <div className="color-wrapper">
                                        <ColorIndicator
                                            colorValue={ font_color_description || '' }
                                        />
                                        <ColorPalette
                                            value={ font_color_description || '' }
                                            onChange={ ( font_color_description ) => {
                                                setAttributes( { font_color_description } );
                                            } }
                                            clearable={ true }
                                        />
                                    </div>
                                </BaseControl>
                                <BaseControl
                                    className={ typeof display_state.background_color_description !== 'undefined' && ! display_state.background_color_description ? 'hide color-picker' : 'show color-picker' }
                                    label={ __( 'Description Background Color' ) }
                                >
                                    <div className="color-wrapper">
                                        <ColorIndicator
                                            colorValue={ background_color_description || '' }
                                        />
                                        <ColorPalette
                                            value={ background_color_description || '' }
                                            onChange={ ( background_color_description ) => {
                                                setAttributes( { background_color_description } );
                                            } }
                                            clearable={ true }
                                        />
                                    </div>
                                </BaseControl>
                            </PanelBody>
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
                    <InspectorAdvancedControls>
                        <TextControl
                            label={ __( 'ID' ) }
                            help={ __( 'Unique ID for CSS styling purpose.' ) }
                            value={ id || '' }
                            onChange={ ( id ) => setAttributes( { id } ) }
                            className={ typeof display_state.id !== 'undefined' && ! display_state.id ? 'hide' : 'show' }
                        />
                    </InspectorAdvancedControls>
                </Fragment>
            )

            function do_serverside_render( attributes ) {
                if ( attributes.preview_show == true ) {
                    // We add the meta so the server knowns what is being edited.
                    // attributes.meta = ldlms_get_post_edit_meta()

                    return (
                        <ServerSideRender
                            block="learndash/ld-course-grid"
                            attributes={ attributes }
                            key="learndash/ld-course-grid"
                        />
                    )
                } else {
                    return __(
                        '[learndash_course_grid] shortcode output shown here',
                        'learndash-course-grid'
                    )
                }
            }

            function selectSkin( props ) {
                const {
                    attributes = {
                        skin,
                        card,
                        display_state
                    },
                    setAttributes
                } = props;

                let disabled_fields = [];
                if ( typeof skin_disabled_fields[ skin ] !== 'undefined' ) {
                    disabled_fields = skin_disabled_fields[ skin ];
                }

                LearnDash_Course_Grid_Block_Editor.editor_fields.forEach( field  => {
                    let temp_display_state = display_state;
                    temp_display_state[ field ] = true;

                    setAttributes( {
                        display_state: temp_display_state
                    } );
                } );

                disabled_fields.forEach( field => {
                    let temp_display_state = display_state;
                    temp_display_state[ field ] = false;

                    setAttributes( {
                        display_state: temp_display_state
                    } );
                } );
                
                if ( card_values.indexOf( card ) == -1 && typeof skin_cards[ skin ][0] !== 'undefined' ) {
                    let temp_card = card;
                    temp_card = skin_cards[ skin ][0];

                    setAttributes( {
                        card: temp_card
                    } );
                }
            }

            function setDisplayState( key, value ) {
                const {
                    display_state,
                } = props.attributes;

                display_state[ key ] = value;
                
                setAttributes( { 
                    display_state
                } );
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
