const { __ } = wp.i18n;

const { assign } = lodash;

const { Fragment } = wp.element;

const { addFilter } = wp.hooks;

const {
    Button,
    FormFileUpload,
    TextControl,
    RadioControl,
    SelectControl,
    PanelBody
} = wp.components;

const { createHigherOrderComponent } = wp.compose;

const {
    MediaUpload,
    MediaUploadCheck
} = wp.editor;

const {
    InspectorControls
} = wp.blockEditor;

const mediaUpload = wp.editor.mediaUpload;

// Define defaults
const tincannyDefaults = {
    insertAs: 'lightbox',
    openWith: 'button',
    iframe: {
        widthValue:  '100',
        widthUnit:   '%',
        heightValue: '400',
        heightUnit:  'px'
    },
    lightbox: {
        title:       '',
        widthValue:  '90',
        widthUnit:   'vw',
        heightValue: '90',
        heightUnit:  'vh',
        effect:      'fade'
    },
    button: {
        text:  __( 'Open' ),
        size:  'normal',
    },
    image: {
        id:    '',
        title: '',
        sizes: {},
        url:   '',
        isLoading: false
    },
    link: {
        text:  __( 'Open' ),
    },
    page: {
        target: '_blank',
    }
}

export const addTinCannyContentSettings = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        // Check if we have to do something
        if ( isTheTincannyContentBlock( props.name ) && props.isSelected && [ 'has-valid-content' ].includes( props.attributes.status ) ){
            // Create global function to set attributes
            // We will use this function in all our components
            let setAttributes = ( key, value ) => {
                // Create object with attributes to change
                let attributes = {}
                attributes[ key ] = value;

                // Set attributes
                props.setAttributes( attributes );
            }

            // Iterate attributes and remove u0022
            Object.entries( props.attributes ).forEach( ( [ key, attribute ] ) => {
                // Check if it's a string
                if ( typeof attribute == 'string' ){
                    // Replace u0022 with a quote mark
                    props.attributes[ key ] = attribute.replace( /u0022/g, '"' );
                }
            });

            // We'll create an array with all the custom
            // components we have to show on the sidebar
            let componentsToShow = [];

            // "Insert As"
            // This one will be shown always
            componentsToShow.push(
                <InsertAs
                    properties={ props.attributes }
                    onChangeDo={ setAttributes }
                />
            );

            // "Iframe Settings"
            // Only if "Insert as" is "iframe"
            if ( [ 'iframe' ].includes( props.attributes.insertAs ) ){
                componentsToShow.push(
                    <IframeSettings
                        properties={ props.attributes }
                        onChangeDo={ setAttributes }
                    />
                );
            }

            // "Lightbox Settings"
            // Only if "Insert as" is "lightbox"
            if ( [ 'lightbox' ].includes( props.attributes.insertAs ) ){
                componentsToShow.push(
                    <LightboxSettings
                        properties={ props.attributes }
                        onChangeDo={ setAttributes }
                    />
                );
            }

            // "Page Settings"
            // Only if "Insert as" is "page"
            //if ( [ 'page' ].includes( props.attributes.insertAs ) ){
            //    componentsToShow.push(
            //        <PageSettings
            //            properties={ props.attributes }
            //            onChangeDo={ setAttributes }
            //        />
            //    );
            //}

            // "Open with"
            // Only if "Insert As" is "lightbox" or "page"
            if ( [ 'lightbox', 'page' ].includes( props.attributes.insertAs ) ){
                componentsToShow.push(
                    <OpenWith
                        properties={ props.attributes }
                        onChangeDo={ setAttributes }
                    />
                );

                // "Button Settings"
                // Only if "Open with" is "button"
                if ( [ 'button' ].includes( props.attributes.openWith ) ){
                    componentsToShow.push(
                        <ButtonSettings
                            properties={ props.attributes }
                            onChangeDo={ setAttributes }
                        />
                    );
                }

                // "Image Settings"
                // Only if "Open with" is "image"
                if ( [ 'image' ].includes( props.attributes.openWith ) ){
                    componentsToShow.push(
                        <ImageSettings
                            properties={ props.attributes }
                            onChangeDo={ setAttributes }
                        />
                    );
                }

                // "Link Settings"
                // Only if "Open with" is "link"
                if ( [ 'link' ].includes( props.attributes.openWith ) ){
                    componentsToShow.push(
                        <LinkSettings
                            properties={ props.attributes }
                            onChangeDo={ setAttributes }
                        />
                    );
                }
            }

            return (
                <Fragment>
                    <BlockEdit { ...props } />
                    <InspectorControls>
                        {componentsToShow}
                    </InspectorControls>
                </Fragment>
            );
        }

        return <BlockEdit { ...props } />;
    };
}, 'addTinCannyContentSettings' );

export const addAttribute = ( settings ) => {
    if ( isTheTincannyContentBlock( settings.name ) ){
        settings.attributes = assign( settings.attributes, {
            insertAs: {
                type:    'string',
                default: tincannyDefaults.insertAs
            },
            openWith: {
                type:    'string',
                default: tincannyDefaults.openWith
            },
            iframeSettings: {
                type:    'string',
                default: JSON.stringify( tincannyDefaults.iframe )
            },
            lightboxSettings: {
                type:    'string',
                default: JSON.stringify( tincannyDefaults.lightbox )
            },
            pageSettings: {
                type:    'string',
                default: JSON.stringify( tincannyDefaults.page )
            },
            buttonSettings: {
                type:    'string',
                default: JSON.stringify( tincannyDefaults.button )
            },
            imageSettings: {
                type:    'string',
                default: JSON.stringify( tincannyDefaults.image )
            },
            linkSettings: {
                type:    'string',
                default: JSON.stringify( tincannyDefaults.link )
            }
        });
    }

    return settings;
}

export const addSaveProps = ( extraProps, blockType, attributes ) => {
    if ( isTheTincannyContentBlock( blockType.name ) ){
        extraProps.insertAs         = attributes.insertAs;
        extraProps.openWith         = attributes.openWith;
        extraProps.lightboxSettings = attributes.lightboxSettings;
        extraProps.iframeSettings   = attributes.iframeSettings;
        /*extraProps.pageSettings     = attributes.pageSettings;*/
        extraProps.buttonSettings   = attributes.buttonSettings;
        extraProps.imageSettings    = attributes.imageSettings;
        extraProps.linkSettings     = attributes.linkSettings;
    }

    return extraProps;
}

addFilter( 'editor.BlockEdit', 'tincanny/content', addTinCannyContentSettings );
addFilter( 'blocks.registerBlockType', 'tincanny/content', addAttribute );
addFilter( 'blocks.getSaveContent.extraProps', 'tincanny/content', addSaveProps );

export const isTheTincannyContentBlock = ( name ) => {
    return name == 'tincanny/content';
}

/**
 * Components
 */

export const InsertAs = ({ properties, onChangeDo }) => {
    return (
        <PanelBody title={ __( 'Display Content In' ) }>
            <RadioControl
                selected={ properties.insertAs }
                options={[
                    {
                        value: 'iframe',
                        label: __( 'Iframe' )
                    },
                    {
                        value: 'lightbox',
                        label: __( 'Lightbox' )
                    },
                    {
                        value: 'page',
                        label: __( 'New Tab' )
                    },
                 ]}
                onChange={ ( value ) => { onChangeDo( 'insertAs', value ) } }
            />
        </PanelBody>
    );
}

export const OpenWith = ({ properties, onChangeDo }) => {
    // Define default values
    properties = Object.assign( {}, {
        openWith: 'button'
    }, properties );

    return (
        <PanelBody title={ __( 'Open with' ) }>
            <RadioControl
                selected={ properties.openWith }
                options={[
                    {
                        value: 'button',
                        label: __( 'Button' )
                    },
                    {
                        value: 'image',
                        label: __( 'Image' )
                    },
                    {
                        value: 'link',
                        label: __( 'Link' )
                    },
                ]}
                onChange={ ( value ) => { onChangeDo( 'openWith', value ) } }
            />
        </PanelBody>
    );
}

export const LightboxSettings = ({ properties, onChangeDo }) => {
    // Clone properties
    properties = JSON.parse( JSON.stringify( properties ) );

    // Define default values
    properties.lightboxSettings = Object.assign( {}, tincannyDefaults.lightbox, JSON.parse( properties.lightboxSettings ));

    // Create function to update the properties object before sending the
    // updated version to the main props object
    let updateProperties = ( key, value ) => {
        // Update property
        properties.lightboxSettings[ key ] = value;

        // Return updated object
        return JSON.stringify( properties.lightboxSettings );
    }

    return (
        <PanelBody title={ __( 'Lightbox Settings' ) }>
            <TextControl
                label={ __( 'Title' ) }
                value={ properties.lightboxSettings.title }
                type="text"
                onChange={ ( value ) => {
                    onChangeDo( 'lightboxSettings', updateProperties( 'title', value ) )
                }}
            />

            <DimensionsFields
                properties={ properties.lightboxSettings }
                onChangeDo={ ( key, value ) => {
                    onChangeDo( 'lightboxSettings', updateProperties( key, value ) )
                }}
            />

            <SelectControl
                label={ __( 'Effect' ) }
                value={ properties.lightboxSettings.effect }
                options={[
                    {
                        value: 'fade',
                        label: __( 'Fade' ) 
                    },
                    {
                        value: 'fadeScale',
                        label: __( 'Fade Scale' ) 
                    },
                    {
                        value: 'slideLeft',
                        label: __( 'Slide Left' ) 
                    },
                    {
                        value: 'slideRight',
                        label: __( 'Slide Right' ) 
                    },
                    {
                        value: 'slideUp',
                        label: __( 'Slide Up' ) 
                    },
                    {
                        value: 'slideDown',
                        label: __( 'Slide Down' ) 
                    },
                    {
                        value: 'fall',
                        label: __( 'Fall' ) 
                    },
                ]}
                onChange={ ( value ) => {
                    onChangeDo( 'lightboxSettings', updateProperties( 'effect', value ) )
                }}
            />
        </PanelBody>
    );
}

export const IframeSettings = ({ properties, onChangeDo }) => {
    // Clone properties
    properties = JSON.parse( JSON.stringify( properties ) );

    // Define default values
    properties.iframeSettings = Object.assign( {}, tincannyDefaults.iframe, JSON.parse( properties.iframeSettings ));

    // Create function to update the properties object before sending the
    // updated version to the main props object
    let updateProperties = ( key, value ) => {
        // Update property
        properties.iframeSettings[ key ] = value;

        // Return updated object
        return JSON.stringify( properties.iframeSettings );
    }

    return (
        <PanelBody title={ __( 'Iframe Settings' ) }>
            <DimensionsFields
                properties={ properties.iframeSettings }
                onChangeDo={ ( key, value ) => {
                    onChangeDo( 'iframeSettings', updateProperties( key, value ) )
                }}
            />
        </PanelBody>
    );
}

export const DimensionsFields = ({ properties, onChangeDo }) => {
    // Define units
    let units = [
        {
            value: '%',
            label: '%'
        },
        {
            value: 'px',
            label: 'px'
        },
        {
            value: 'vw',
            label: 'vw'
        },
        {
            value: 'vh',
            label: 'vh'
        },
    ];

    return (
        <Fragment>
            <div className="components-base-control">
                <div className="uo-tclr-gutenberg-field-with-unit">
                    <div className="uo-tclr-gutenberg-field-with-unit__number">
                        <TextControl
                            label={ __( 'Width' ) }
                            value={ properties.widthValue }
                            type="number"
                            onChange={ ( value ) => { onChangeDo( 'widthValue', value ) } }
                        />
                    </div>
                    <div className="uo-tclr-gutenberg-field-with-unit__select">
                        <SelectControl
                            value={ properties.widthUnit }
                            options={ units }
                            onChange={ ( value ) => { onChangeDo( 'widthUnit', value ) } }
                        />
                    </div>
                </div>
                <div className="uo-tclr-gutenberg-field-with-unit">
                    <div className="uo-tclr-gutenberg-field-with-unit__number">
                        <TextControl
                            label={ __( 'Height' ) }
                            value={ properties.heightValue }
                            type="number"
                            onChange={ ( value ) => { onChangeDo( 'heightValue', value ) } }
                        />
                    </div>
                    <div className="uo-tclr-gutenberg-field-with-unit__select">
                        <SelectControl
                            value={ properties.heightUnit }
                            options={ units }
                            onChange={ ( value ) => { onChangeDo( 'heightUnit', value ) } }
                        />
                    </div>
                </div>
            </div>
        </Fragment>
    );
}

export const ButtonSettings = ({ properties, onChangeDo }) => {
    // Clone properties
    properties = JSON.parse( JSON.stringify( properties ) );

    // Define default values
    properties.buttonSettings = Object.assign( {}, tincannyDefaults.button, JSON.parse( properties.buttonSettings ));

    // Create function to update the properties object before sending the
    // updated version to the main props object
    let updateProperties = ( key, value ) => {
        // Update property
        properties.buttonSettings[ key ] = value;

        // Return updated object
        return JSON.stringify( properties.buttonSettings );
    }

    return (
        <PanelBody title={ __( 'Button Settings' ) }>
            <TextControl
                label={ __( 'Text' ) }
                value={ properties.buttonSettings.text }
                type="text"
                onChange={ ( value ) => {
                    onChangeDo( 'buttonSettings', updateProperties( 'text', value ) )
                }}
            />

            <RadioControl
                label={ __( 'Size' ) }
                selected={ properties.buttonSettings.size }
                options={[
                    {
                        value: 'small',
                        label: __( 'Small' ) 
                    },
                    {
                        value: 'normal',
                        label: __( 'Normal' ) 
                    },
                    {
                        value: 'big',
                        label: __( 'Big' ) 
                    }
                 ]}
                onChange={ ( value ) => {
                    onChangeDo( 'buttonSettings', updateProperties( 'size', value ) )
                }}
            />
        </PanelBody>
    );
}

export const ImageSettings = ({ properties, onChangeDo }) => {
    // Clone properties
    properties = JSON.parse( JSON.stringify( properties ) );

    // Define default values
    properties.imageSettings = Object.assign( {}, tincannyDefaults.image, JSON.parse( properties.imageSettings ));

    // Create function to update the properties object before sending the
    // updated version to the main props object
    let updateProperties = () => {
        // Return updated object
        return JSON.stringify( properties.imageSettings );
    }

    // Create image preview
    let imagePreview = [];

    if ( properties.imageSettings.url !== '' ){
        imagePreview.push((
            <div className="uo-tclr-gutenberg-image__preview">
                <div className="uo-tclr-gutenberg-image-preview__block">
                    <img src={ properties.imageSettings.url }/>
                </div>
                <div className="uo-tclr-gutenberg-image-preview__title">
                    { properties.imageSettings.title }
                </div>
            </div>
        ));
    }

    return (
        <PanelBody title={ __( 'Image Settings' ) }>
            <div className="components-base-control">
                <MediaUploadCheck>
                    <div className="uo-tclr-gutenberg-image">
                        { imagePreview }
                        <div className="uo-tclr-gutenberg-image__upload">
                            <FormFileUpload
                                isDefault
                                isBusy={ properties.imageSettings.isLoading }
                                accept="image/*"
                                onChange={ ( event ) => {
                                    // Change to "busy"
                                    properties.imageSettings.isLoading = true;
                                    onChangeDo( 'lightboxSettings', updateProperties() );

                                    // Upload
                                    mediaUpload({
                                        allowedTypes: [ 'image' ],
                                        filesList: event.target.files,
                                        onFileChange: ( media ) => {
                                            // Get first file
                                            media = media[0]

                                            // Add media data
                                            properties.imageSettings = Object.assign({}, properties.imageSettings, {
                                                id:    media.id,
                                                title: media.title,
                                                sizes: media.sizes,
                                                url:   media.url,
                                            });

                                            // Delete "isLoading"
                                            delete properties.imageSettings.isLoading;

                                            // Update props
                                            onChangeDo( 'imageSettings', updateProperties() );
                                        } 
                                    });
                                }}
                            >
                                { __( 'Upload' ) }
                            </FormFileUpload>
                        </div>
                        <div className="uo-tclr-gutenberg-image__select">
                            <MediaUpload
                                onSelect={ ( media ) => {
                                    // Add media data
                                    properties.imageSettings = Object.assign({}, properties.imageSettings, {
                                        id:    media.id,
                                        title: media.title,
                                        sizes: media.sizes,
                                        url:   media.url,
                                    });

                                    // Delete "isLoading"
                                    delete properties.imageSettings.isLoading;

                                    // Update props
                                    onChangeDo( 'imageSettings', updateProperties() );
                                }}
                                type="image"
                                className="editor-media-placeholder__button"
                                render={ ( { open } ) => (
                                    <Button isDefault onClick={ open }>
                                        { __( 'Media Library' ) }
                                    </Button>
                                ) }
                            />
                        </div>
                    </div>
                </MediaUploadCheck>
            </div>
        </PanelBody>
    );
}

export const LinkSettings = ({ properties, onChangeDo }) => {
    // Clone properties
    properties = JSON.parse( JSON.stringify( properties ) );

    // Define default values
    properties.linkSettings = Object.assign( {}, tincannyDefaults.link, JSON.parse( properties.linkSettings ));

    // Create function to update the properties object before sending the
    // updated version to the main props object
    let updateProperties = ( key, value ) => {
        // Update property
        properties.linkSettings[ key ] = value;

        // Return updated object
        return JSON.stringify( properties.linkSettings );
    }

    return (
        <PanelBody title={ __( 'Link Settings' ) }>
            <TextControl
                label={ __( 'Text' ) }
                value={ properties.linkSettings.text }
                type="text"
                onChange={ ( value ) => {
                    onChangeDo( 'linkSettings', updateProperties( 'text', value ) )
                }}
            />
        </PanelBody>
    );
}

export const PageSettings = ({ properties, onChangeDo }) => {
    // Clone properties
    properties = JSON.parse( JSON.stringify( properties ) );

    // Define default values
    properties.pageSettings = Object.assign( {}, tincannyDefaults.page, JSON.parse( properties.pageSettings ));

    // Create function to update the properties object before sending the
    // updated version to the main props object
    let updateProperties = ( key, value ) => {
        // Update property
        properties.pageSettings[ key ] = value;

        // Return updated object
        return JSON.stringify( properties.pageSettings );
    }

    return (
        <PanelBody title={ __( 'Page Settings' ) }>
            <RadioControl
                label={ __( 'Open in' ) }
                selected={ properties.pageSettings.target }
                options={[
                    {
                        value: '_self',
                        label: __( 'Same window' ) 
                    },
                    {
                        value: '_blank',
                        label: __( 'New window' ) 
                    },
                 ]}
                onChange={ ( value ) => {
                    onChangeDo( 'pageSettings', updateProperties( 'target', value ) )
                }}
            />
        </PanelBody>
    );
}

/**
 * Helper functions
 */

export const isDefined = ( variable ) => {
    return variable !== undefined && variable !== null;
}