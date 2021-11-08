/**
 * LearnDash Block ld-course-inprogress
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
	ldlms_get_integer_value
} from '../ldlms.js';

/**
 * Internal block libraries
 */
import { __, _x, sprintf} from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

const block_title = sprintf(
	// translators: placeholder: Course.
	_x('LearnDash %s In Progress', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course')
);

registerBlockType(
	'learndash/ld-course-inprogress',
	{
		title: block_title,
		// translators: placeholder: Course.
		description: sprintf(_x('This block shows the content if the user is enrolled into the %s but not yet completed.', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course') ),
		icon: 'star-half',
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
			autop: {
				type: 'boolean',
				default: true
			},
		},
		edit: props => {
			const { attributes: { course_id, user_id, autop }, className, setAttributes } = props;

			const inspectorControls = (
				<InspectorControls key="controls">
					<PanelBody
						title={__('Settings', 'learndash')}
					>
						<TextControl
							// translators: placeholder: Course.
							label={sprintf(_x('%s ID', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course' ) ) }
							// translators: placeholders: Course, Course.
							help={sprintf(_x('Enter single %1$s ID. Leave blank if used within a %2$s.', 'placeholders: Course, Course', 'learndash'), ldlms_get_custom_label('course'), ldlms_get_custom_label('course') ) }
							value={course_id || ''}
							onChange={course_id => setAttributes({ course_id })}
						/>
						<TextControl
							label={__('User ID', 'learndash')}
							help={__('Enter specific User ID. Leave blank for current User.', 'learndash')}
							value={user_id || ''}
							onChange={user_id => setAttributes({ user_id })}
						/>
						<ToggleControl
							label={__('Auto Paragraph', 'learndash')}
							checked={!!autop}
							onChange={autop => setAttributes({ autop })}
						/>
					</PanelBody>
				</InspectorControls>
			);

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
			}

			const outputBlock = (
				<div className={className} key="learndash/ld-course-inprogress">
					<span className="learndash-inner-header">{block_title}</span>
					<div className="learndash-block-inner">
						{ld_block_error_message}
						<InnerBlocks />
					</div>
				</div>
			);


			return [
				inspectorControls,
				outputBlock
			];
		},

		save: props => {
			return (
				<InnerBlocks.Content />
			);
		}
	},
);
