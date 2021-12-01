/**
 * LearnDash Block ld-course-content
 *
 * @since 2.5.9
 * @package LearnDash
 */

/**
 * LearnDash block functions
 */
import {
	ldlms_get_custom_label,
	ldlms_get_post_edit_meta,
	ldlms_get_per_page,
} from '../ldlms.js';

/**
 * Internal block libraries
 */
import { __, _x, sprintf} from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

registerBlockType(
	'learndash/ld-course-content',
	{
		// translators: placeholder: Course.
		title: sprintf(_x('LearnDash %s Content', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course')),
		// translators: placeholder: Course.
		description: sprintf(_x('This block displays the %s Content table.', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course') ),
		icon: 'format-aside',
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
			course_id: {
				type: 'string',
				default: '',
			},
			per_page: {
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
			example_show: {
				type: 'boolean',
				default: 0
			},
			meta: {
				type: 'object',
			}
		},
		edit: props => {
			const { attributes: { course_id, per_page, preview_show, preview_course_id, example_show },
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
							help={sprintf(_x('Enter single %1$s ID. Leave blank if used within a %2$s.', 'placeholders: Course, Course', 'learndash'), ldlms_get_custom_label('course'), ldlms_get_custom_label('course') ) }
							value={ course_id || '' }
							onChange={ course_id => setAttributes( { course_id } ) }
						/>
						<TextControl
							// translators: placeholder: Lessons.
							label={sprintf(_x('%s per page', 'placeholder: Lessons', 'learndash'), ldlms_get_custom_label('lessons') ) }
							// translators: placeholder: default per page.
							help={sprintf(_x('Leave empty for default (%d) or 0 to show all items.', 'placeholder: default per page', 'learndash'), ldlms_get_per_page( 'per_page' ) ) }
							value={ per_page || '' }
							type={ 'number' }
							onChange={ per_page => setAttributes( { per_page } ) }
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
							label={sprintf(_x('%s ID', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course') ) }
							// translators: placeholder: Course.
							help={sprintf(_x('Enter a %s ID to test preview', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course') ) }
							value={preview_course_id || ''}
							type={'number'}
							onChange={preview_course_id => setAttributes({ preview_course_id })}
						/>
					</PanelBody>
				</InspectorControls>
			);

			function do_serverside_render( attributes ) {
				if ( attributes.preview_show == true ) {
					// We add the meta so the server knowns what is being edited.
					attributes.meta = ldlms_get_post_edit_meta();

					return <ServerSideRender
						block="learndash/ld-course-content"
						attributes={attributes}
						key="learndash/ld-course-content"
					/>

				} else {
					return __('[course_content] shortcode output shown here', 'learndash');
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
		},
	},
);
