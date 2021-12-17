/**
 * LearnDash Block ld-quizinfo
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
} from '../ldlms.js';

/**
 * Internal block libraries
 */
import { __, _x, sprintf } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

if (typenow === 'sfwd-certificates') {
	registerBlockType(
		'learndash/ld-quizinfo',
		{
			// translators: placeholder: Quiz.
			title: sprintf( _x( 'LearnDash %s Info [quizinfo]', 'placeholder: Quiz', 'learndash' ), ldlms_get_custom_label( 'quiz' ) ),
			// translators: placeholder: Quiz.
			description: sprintf(_x('This block displays %s related information', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz') ),
			icon: 'analytics',
			category: 'learndash-blocks',
			supports: {
				customClassName: false,
			},
			attributes: {
				show: {
					type: 'string',
					default: 'quiz_title',
				},
				quiz_id: {
					type: 'string',
					default: '',
				},
				format: {
					type: 'string',
				},
				field_id: {
					type: 'string',
				},
				preview_show: {
					type: 'boolean',
					default: 1
				},
				preview_quiz_id: {
					type: 'string',
					default: '',
				},
				preview_user_id: {
					type: 'string',
					default: '',
				},
				meta: {
					type: 'object',
				}
			},
			edit: props => {
				const { attributes: { quiz_id, show, format, field_id, preview_show, preview_quiz_id, preview_user_id },
					className, setAttributes } = props;

				const field_show = (
					<SelectControl
						key="show"
						value={show || 'quiz_title'}
						label={__('Show', 'learndash')}
						options={[
							{
								// translators: placeholder: Quiz.
								label: sprintf(_x('%s Title', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz')),
								value: 'quiz_title',
							},
							{
								// translators: placeholder: Quiz.
								label: sprintf(_x('%s Score', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz')),
								value: 'score',
							},
							{
								// translators: placeholder: Quiz.
								label: sprintf(_x('%s Count', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz')),
								value: 'count',
							},
							{
								// translators: placeholder: Quiz.
								label: sprintf(_x('%s Pass', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz')),
								value: 'pass',
							},
							{
								// translators: placeholder: Quiz.
								label: sprintf(_x('%s Timestamp', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz')),
								value: 'timestamp',
							},
							{
								// translators: placeholder: Quiz.
								label: sprintf(_x('%s Points', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz')),
								value: 'points',
							},
							{
								// translators: placeholder: Quiz.
								label: sprintf(_x('%s Total Points', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz')),
								value: 'total_points',
							},
							{
								// translators: placeholder: Quiz.
								label: sprintf(_x('%s Percentage', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz')),
								value: 'percentage',
							},
							{
								// translators: placeholder: Course.
								label: sprintf(_x('%s Title', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course')),
								value: 'course_title',
							},
							{
								// translators: placeholder: Quiz.
								label: sprintf(_x('%s Form Field', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz')),
								value: 'field',
							},
						]}
						onChange={show => setAttributes({ show })}
					/>
				);

				const field_quiz_id = (
					<TextControl
						// translators: placeholder: Quiz.
						label={sprintf(_x('%s ID', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz'))}
						// translators: placeholders: Quiz, Quiz.
						help={sprintf(_x('Enter a single %1$s ID. Leave blank if used within a %2$s or certificate.', 'placeholders: Quiz, Quiz', 'learndash'), ldlms_get_custom_label('quiz'), ldlms_get_custom_label('quiz'))}
						value={quiz_id || ''}
						onChange={quiz_id => setAttributes({ quiz_id })}
					/>
				);

				let field_form_field_id = '';
				if ((show == 'field')) {
					field_form_field_id = (
						<TextControl
							label={__('Form Field ID', 'learndash')}
							// translators: placeholders: Quiz.
							help={sprintf(_x('The Field ID is shown on the %s Custom Fields table.','placeholders: Quiz', 'learndash'), ldlms_get_custom_label('quiz'))}
							value={field_id || ''}
							onChange={field_id => setAttributes({ field_id })}
						/>
					);
				}

				let field_format = '';
				if ( (show == 'timestamp') || (show == 'field') ) {
					field_format = (
						<TextControl
							label={__('Format', 'learndash')}
							help={__('This can be used to change the date format. Default: "F j, Y, g:i a.', 'learndash')}
							value={format || ''}
							onChange={format => setAttributes({ format })}
						/>
					);
				}

				const panel_preview = (
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
							// translators: placeholder: Quiz.
							label={sprintf(_x('%s ID', 'placeholder: Quiz', 'learndash'),ldlms_get_custom_label('quiz'))}
							// translators: placeholder: Quiz.
							help={sprintf(_x('Enter a %s ID to test preview', 'placeholder: Quiz', 'learndash'), ldlms_get_custom_label('quiz'))}
							value={preview_quiz_id || ''}
							type={'number'}
							onChange={preview_quiz_id => setAttributes({ preview_quiz_id })}
						/>
						<TextControl
							label={__('User ID', 'learndash')}
							help={__('Enter a User ID to test preview', 'learndash')}
							value={preview_user_id || ''}
							type={'number'}
							onChange={preview_user_id => setAttributes({ preview_user_id })}
						/>
					</PanelBody>
				);

				const inspectorControls = (
					<InspectorControls key="controls">
						<PanelBody
							title={ __( 'Settings', 'learndash' ) }
						>
							{ field_quiz_id }
							{ field_show }
							{ field_form_field_id }
							{ field_format }
						</PanelBody>
						{ panel_preview }
					</InspectorControls>
				);

				function do_serverside_render(attributes) {
					console.log('do_serverside_render: attributes[%o]', attributes);
					if (attributes.preview_show == true) {
						// We add the meta so the server knowns what is being edited.
						attributes.meta = ldlms_get_post_edit_meta();

						return <ServerSideRender
							block="learndash/ld-quizinfo"
							attributes={attributes}
							key="learndash/ld-quizinfo"
						/>
					} else {
						return __('[quizinfo] shortcode output shown here', 'learndash');
					}
				}

				return [
					inspectorControls,
					do_serverside_render(props.attributes)
				];
			},
			save: function (props) {
				// Delete meta from props to prevent it being saved.
				delete(props.attributes.meta);

				delete (props.attributes.preview_show);
				delete (props.attributes.preview_quiz_id);
				delete (props.attributes.preview_user_id);
			}
		},
	);
}
