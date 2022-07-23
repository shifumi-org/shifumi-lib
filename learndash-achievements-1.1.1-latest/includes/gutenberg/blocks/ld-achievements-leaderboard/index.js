const { createElement } = wp.element;
const { __, _x, sprintf } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
const { PanelBody, ServerSideRender, TextControl, ToggleControl } = wp.components;

registerBlockType( 'learndash/ld-achievements-leaderboard', {
    title: __( 'LearnDash Achievements Leaderboard', 'learndash-achievements' ), 
    description: __( 'Display LearnDash achievements leaderboard.', 'learndash-achievements' ),
    icon: 'editor-ol',
    category: 'learndash-blocks',
    keywords: [
        'LearnDash', 'learndash-achievements', 'achievements', 'leaderboard'
    ],
    attributes: {
        number: {
            type: 'integer',
            default: 10
        },
        preview_show: {
            type: 'boolean',
            default: true
        }
    },

    edit: (props) => {
        const { attributes, className, setAttributes } = props;

        const inspectorControls = (
            <InspectorControls>
                <PanelBody
                    title={ __( 'Settings', 'learndash-achievements' ) }
                >
                    <TextControl
                        label={ __( 'Number', 'learndash-achievements') }
                        help={ __( 'Number of users that will be displayed in leaderboard.', 'learndash-achievements' ) }
                        value={ attributes.number || '' }
                        type={ 'number' }
                        onChange={ number => setAttributes( { number } ) }
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
                    block="learndash/ld-achievements-leaderboard"
                    attributes={ attributes }
                />

            } else {
                return __( '[ld_achievements_leaderboard] shortcode output shown here', 'learndash-achievements' );
            }
        }

        return [
            inspectorControls,
            do_serverside_render( attributes )
        ];
    },

    save: ( props ) => {
        return null;
    }
} );