/**
 * LearnDash ProPanel Filters Block
 *
 * @package ProPanel
 * @since 2.1.4
 */

/**
 * ProPanel block functions
 */

/**
 * Internal block libraries
 */
const { __, _x, sprintf } = wp.i18n;
const {
	registerBlockType,
} = wp.blocks;

const {
    InspectorControls,
} = wp.editor;

const {
	ServerSideRender,
	PanelBody,
	SelectControl,
	ToggleControl,
	TextControl
} = wp.components;

registerBlockType(
    'ld-propanel/ld-propanel-filters',
    {
		title: _x('ProPanel Filters', 'ld_propanel'),
		description: __('This shortcode adds the ProPanel Filters widget any page', 'ld_propanel'),
		icon: 'admin-network',
		category: 'ld-propanel-blocks',
		//example: {
		//	attributes: {
		//		example_show: 0,
		//	},
		//},
		supports: {
			customClassName: false,
		},
        attributes: {
			preview_show: {
				type: 'boolean',
				default: false
			},
		},
        edit: function( props ) {
			const { attributes: { preview_show },
            	setAttributes } = props;

			const panel_preview = (
				<PanelBody
					title={__('Preview', 'ld_propanel')}
					initialOpen={false}
				>
					<ToggleControl
						label={__('Show Preview', 'ld_propanel')}
						checked={!!preview_show}
						onChange={preview_show => setAttributes({ preview_show })}
					/>
				</PanelBody>
			);

			const inspectorControls = (
				<InspectorControls>
					{ panel_preview }
				</InspectorControls>
			);

			function do_serverside_render( attributes ) {
				//console.log('attributes[%o]', attributes);
				
				if ( attributes.preview_show == true ) {
					return <ServerSideRender
					block="ld-propanel/ld-propanel-filters"
					attributes={ attributes }
					/>
				} else {
					return __( '[ld_propanel widget="filtering"] shortcode output shown here', 'ld_propanel' );
				}
			}

			return [
				inspectorControls,
				do_serverside_render( props.attributes )
			];
        },
        save: props => {
			// Delete meta from props to prevent it being saved.
			delete (props.attributes.meta);
		}
	},
);
