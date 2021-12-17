/**
 * LearnDash Block ld-payment-buttons
 *
 * @since 2.5.9
 * @package LearnDash
 */

/**
 * LearnDash block functions
 */
import {
	ldlms_get_post_edit_meta,
	ldlms_get_custom_label,
	ldlms_get_integer_value,
} from '../ldlms.js';

/**
 * Internal block libraries
 */
import { __, _x, sprintf } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

registerBlockType(
	'learndash/ld-payment-buttons',
	{
		title: __( 'LearnDash Payment Buttons', 'learndash' ),
		// translators: placeholder: Course.
		description: sprintf(_x('This block displays the %s payment buttons', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course') ),
		icon: 'cart',
		category: 'learndash-blocks',
		supports: {
			customClassName: false,
		},
		attributes: {
			course_id: {
				type: 'string',
			},
			preview_show: {
				type: 'boolean',
				default: 1
			},
			preview_course_id: {
				type: 'string',
				default: '',
			},
			meta: {
				type: 'object',
			}
		},
		edit: props => {
			const { attributes: { course_id, preview_show, preview_course_id },
				className, setAttributes } = props;

			const inspectorControls = (
				<InspectorControls key="controls">
					<PanelBody
						title={ __( 'Settings', 'learndash' ) }
					>
						<TextControl
							// translators: placeholder: Course.
							label={sprintf(_x('%s ID', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course') ) }
							// translators: placeholders: Course, Course.
							help={sprintf(_x('Enter a single %1$s ID. Leave blank if used within a %2$s.', 'placeholders: Course, Course', 'learndash'), ldlms_get_custom_label('course'), ldlms_get_custom_label('course') ) }
							value={ course_id || '' }
							onChange={ course_id => setAttributes( { course_id } ) }
						/>
					</PanelBody>
					<PanelBody
						title={__('Preview', 'learndash')}
						initialOpen={false}
					>
						<ToggleControl
							label={__('Show Preview', 'learndash')}
							checked={!!preview_show}
							onChange={preview_show => setAttributes({ preview_show })}
						/>
						<TextControl
							// translators: placeholder: Course.
							label={sprintf(_x('%s ID', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course'))}
							// translators: placeholder: Course.
							help={sprintf(_x('Enter a %s ID to test preview', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course'))}
							value={preview_course_id || ''}
							type={'number'}
							onChange={preview_course_id => setAttributes({ preview_course_id })}
						/>
					</PanelBody>
				</InspectorControls>
			);

			function do_serverside_render(attributes) {
				if (attributes.preview_show == true) {
					let ld_block_error_message = '';
					let preview_course_id = ldlms_get_integer_value(course_id);

					if (preview_course_id === 0) {
						preview_course_id = ldlms_get_post_edit_meta('course_id');
						preview_course_id = ldlms_get_integer_value(preview_course_id);

						if (preview_course_id == 0) {
							// translators: placeholders: Course, Course.
							ld_block_error_message = sprintf(_x('%1$s ID is required when not used within a %2$s.', 'placeholders: Course, Course', 'learndash'), ldlms_get_custom_label('course'), ldlms_get_custom_label('course'));
						}
					}

					if (ld_block_error_message.length) {
						ld_block_error_message = (<span className="learndash-block-error-message">{ld_block_error_message}</span>);

						const outputBlock = (
							<div className={className} key='learndash/ld-payment-buttons'>
								<div className="learndash-block-inner">
									{ld_block_error_message}
								</div>
							</div>
						);
						return outputBlock;
					} else {
						// We add the meta so the server knowns what is being edited.
						attributes.meta = ldlms_get_post_edit_meta();

						return <ServerSideRender
							block="learndash/ld-payment-buttons"
							attributes={attributes}
							key="learndash/ld-payment-buttons"
						/>
					}
				} else {
					return __('[learndash_payment_buttons] shortcode output shown here', 'learndash');
				}
			}

			return [
				inspectorControls,
				do_serverside_render(props.attributes)
			];
		},

		save: props => {
			// Delete meta from props to prevent it being saved.
			delete (props.attributes.meta);
		}
	},
);
