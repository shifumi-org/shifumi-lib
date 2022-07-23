const { createElement } = wp.element
const { __, _x, sprintf } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
const { PanelBody, ServerSideRender, TextControl, ToggleControl } = wp.components;

registerBlockType( 'learndash/ld-my-achievements', {
    title: __( 'LearnDash My Achievements', 'learndash-achievements' ), 
    description: __( 'Display a list of current user LearnDash achievements.', 'learndash-achievements' ),
    icon: 'grid-view',
    category: 'learndash-blocks',
    keywords: [
        'LearnDash', 'learndash achievements', 'my achievements', 'achievements'
    ],
    attributes: {
        preview_show: {
            type: 'boolean',
            default: true
        },
        show_title: {
            type: 'boolean',
            default: false
        }
    },

    edit: (props) => {
        const { attributes, className, setAttributes } = props;

        const inspectorControls = (
            <InspectorControls>
                <PanelBody
                    title={ __( 'Settings', 'learndash-achievements' ) }
                    initialOpen={ false }
                >
                     <ToggleControl
                        label={ __( 'Show Title', 'learndash-achievements' ) }
                        checked={ !! attributes.show_title }
                        onChange={ show_title => setAttributes( { show_title } ) }
                    />
                </PanelBody>
                <PanelBody
                    title={ __( 'Preview', 'learndash-achievements' ) }
                    initialOpen={ false }
                >
                    <ToggleControl
                        label={ __( 'Show Preview', 'learndash-achievements' ) }
                        checked={ !! attributes.preview_show }
                        onChange={ preview_show => setAttributes( { preview_show } ) }
                    />
                </PanelBody>
            </InspectorControls>
        );

         function do_serverside_render( attributes ) {
            if ( attributes.preview_show == true ) {
                return <ServerSideRender
                    block="learndash/ld-my-achievements"
                    attributes={ attributes }
                />

            } else {
                return __( '[ld_my_achievements] shortcode output shown here', 'learndash-achievements' );
            }
        }

        return [
            inspectorControls,
            do_serverside_render( attributes )
        ];
    },

    save: (props) => {
        return null;
    }
});