/**
 * LearnDash Block ld-registration
 *
 * @since 3.6.0
 * @package LearnDash
 */

/**
 * Internal block libraries
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

registerBlockType(
	'learndash/ld-registration',
	{
		title: __( 'LearnDash Registration', 'learndash' ),
		description: __( 'Shows the registration form', 'learndash' ),
		icon: 'id-alt',
		category: 'learndash-blocks',
		example: {
			attributes: {
				example_show: 1,
			},
		},
		supports: {
			customClassName: false,
		},
		attributes: {
			preview_show: {
				type: 'boolean',
				default: 1
			},
			example_show: {
				type: 'boolean',
				default: 0
			},
			width: {
				type: 'string',
			}
		},
		edit: function( props ) {
			const { attributes: { preview_show, example_show, width },
				setAttributes } = props;

			const inspectorControls = (
				<InspectorControls key="controls">
					<PanelBody
						title={ __( 'Styling', 'learndash' ) }
						initialOpen={ true }
					>
						<TextControl
							label={__('Form Width', 'learndash')}
							help={__('Sets the width of the registration form.', 'learndash')}
							value={width || ''}
							type={'string'}
							onChange={width => setAttributes({ width })}
						/>
					</PanelBody>
					<PanelBody
						title={ __( 'Preview', 'learndash' ) }
						initialOpen={ false }
					>
						<ToggleControl
							label={ __('Show Preview', 'learndash') }
							checked={ !!preview_show }
							onChange={ preview_show => setAttributes( { preview_show } ) }
						/>
					</PanelBody>
				</InspectorControls>
			);

			function do_serverside_render( attributes ) {
				if ( attributes.preview_show == true ) {
					return <ServerSideRender
					block="learndash/ld-registration"
					attributes={ attributes }
					key="learndash/ld-registration"
					/>
				} else {
					return __( '[ld_registration] shortcode output shown here', 'learndash' );
				}
			}

			return [
				inspectorControls,
				do_serverside_render( props.attributes )
			];
		},

		save: props => {

		}
	},
);
