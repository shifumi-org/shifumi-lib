/**
 * LearnDash Block ld-certificate
 *
 * @since 3.2
 * @package LearnDash
 */

/**
 * LearnDash block functions
 */
import { ldlms_get_custom_label } from '../ldlms.js';

/**
 * Internal block libraries
 */
import { __, _x, sprintf} from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

registerBlockType(
	'learndash/ld-course-resume',
	{
		// translators: placeholder: Course.
		title: sprintf(_x('%s Resume', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course') ),
		// translators: placeholder: Course.
		description: sprintf(_x('Return to %s link/button.', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course' ) ),
		icon: 'welcome-learn-more',
		category: 'learndash-blocks',
		supports: {
			customClassName: false,
		},
		attributes: {
			course_id: {
				type: 'string',
				default: '',
			},
			user_id: {
				type: 'string',
				default: '',
			},
			label: {
				type: 'string',
				default: '',
			},
			html_class: {
				type: 'string',
				default: '',
			},
			button: {
				type: 'string',
				default: '',
			},
			preview_show: {
				type: 'boolean',
				default: 1
			},
			preview_course_id: {
				type: 'string',
				default: '',
			},
			preview_user_id: {
				type: 'string',
				default: '',
			},
			example_show: {
				type: 'boolean',
				default: 0
			},
		},
		edit: props => {
			const { attributes: { course_id, user_id, label, html_class, button, preview_show, preview_course_id, preview_user_id, example_show }, className, setAttributes } = props;

			const inspectorControls = (
				<InspectorControls key="controls">
					<PanelBody
						title={__('Settings', 'learndash')}
					>
						<TextControl
							// translators: placeholder: Course.
							label={sprintf(_x('%s ID', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course') )}
							// translators: placeholders: Course, Course.
							help={sprintf(_x('Enter single %1$s ID. Leave blank if used within a %2$s.', 'placeholders: Course, Course', 'learndash'), ldlms_get_custom_label('course'), ldlms_get_custom_label('course' ) ) }
							value={course_id || ''}
							onChange={course_id => setAttributes({ course_id })}
						/>
						<TextControl
							label={__('User ID', 'learndash')}
							help={__('Enter specific User ID. Leave blank for current User.', 'learndash')}
							value={user_id || ''}
							onChange={user_id => setAttributes({ user_id })}
						/>
						<SelectControl
							key="button"
							label={__('Show as button', 'learndash')}
							value={button}
							options={[
								{
									label: __('Yes', 'learndash'),
									value: 'true',
								},
								{
									label: __('No', 'learndash'),
									value: 'false',
								},
							]}
							onChange={button => setAttributes({ button })}
						/>
						<TextControl
							label={__('Label', 'learndash')}
							help={__('Label for link shown to user', 'learndash')}
							value={label || ''}
							onChange={label => setAttributes({ label })}
						/>
						<TextControl
							key="html_class"
							label={__('Class', 'learndash')}
							help={__('HTML class for link element', 'learndash')}
							value={html_class || ''}
							onChange={html_class => setAttributes({ html_class })}
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
						<TextControl
							label={__('User ID', 'learndash')}
							help={__('Enter specific User ID. Leave blank for current User.', 'learndash')}
							value={preview_user_id || ''}
							onChange={preview_user_id => setAttributes({ preview_user_id })}
						/>
					</PanelBody>
				</InspectorControls>
			);

			function do_serverside_render(attributes) {
				if (attributes.preview_show == true) {
					return <ServerSideRender
						block="learndash/ld-course-resume"
						attributes={attributes}
						key="learndash/ld-course-resume"
					/>
				} else {
					return __('[ld_course_resume] shortcode output shown here', 'learndash');
				}
			}

			return [
				inspectorControls,
				do_serverside_render(props.attributes)
			];
		},
	},
);
