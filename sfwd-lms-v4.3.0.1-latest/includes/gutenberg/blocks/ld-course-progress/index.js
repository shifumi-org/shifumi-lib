/**
 * LearnDash Block ld-course-progress
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
import { __, _x, sprintf} from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, PanelRow } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useMemo } from "@wordpress/element";

const el = wp.element.createElement;

const iconEl = el('svg', { width: 300, height: 300, viewBox: '0 0 50 10' },
  el('path', { d: "M47.1,0h-44c-1.7,0-3,1.3-3,3v4c0,1.7,1.3,3,3,3h44c1.7,0,3-1.3,3-3V3C50.1,1.3,48.7,0,47.1,0z M48.1,7c0,0.6-0.4,1-1,1h-12 V2h12c0.6,0,1,0.4,1,1V7z" } )
);

const block_key   = 'learndash/ld-course-progress';
const block_title = sprintf(
	// translators: placeholder: Course.
	_x('LearnDash %s Progress', 'placeholders: Course', 'learndash'), ldlms_get_custom_label('course')
);

registerBlockType(
	block_key,
	{
		title: block_title,
		description: sprintf(
			// translators: placeholder: Course.
			_x('This block displays users progress bar for the %s.', 'placeholders: Course', 'learndash'), ldlms_get_custom_label('course') ),
		icon: iconEl,
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
				default: ''
			},
			user_id: {
				type: 'string',
				default: ''
			},
			preview_show: {
				type: 'boolean',
				default: 1
			},
			preview_user_id: {
				type: 'string',
			},
			example_show: {
				type: 'boolean',
				default: 0
			},
			editing_post_meta: {
				type: 'object'
			}
		},
		edit: props => {
			let { attributes: { course_id }, className } = props;
			const { attributes: { user_id, preview_show, preview_user_id, example_show },
				setAttributes } = props;

			const inspectorControls = (
				<InspectorControls key="controls">
					<PanelBody title={__("Settings", "learndash")}>
						<TextControl
							label={sprintf(
								// translators: placeholder: Course.
								_x("%s ID", "placeholder: Course", "learndash"),
								ldlms_get_custom_label("course")
							)}
							help={sprintf(
								// translators: placeholders: Course, Course.
								_x(
									"Enter single %1$s ID. Leave blank if used within a %2$s.",
									"placeholders: Course, Course",
									"learndash"
								),
								ldlms_get_custom_label("course"),
								ldlms_get_custom_label("course")
							)}
							value={course_id || ""}
							type={"number"}
							onChange={function (new_course_id) {
								if (new_course_id != "" && new_course_id < 0) {
									setAttributes({ course_id: "0" });
								} else {
									setAttributes({ course_id: new_course_id });
								}
							}}
						/>
						<TextControl
							label={__("User ID", "learndash")}
							help={__(
								"Enter specific User ID. Leave blank for current User.",
								"learndash"
							)}
							value={user_id || ""}
							type={"number"}
							onChange={function (new_user_id) {
								if (new_user_id != "" && new_user_id < 0) {
									setAttributes({ user_id: "0" });
								} else {
									setAttributes({ user_id: new_user_id });
								}
							}}
						/>
					</PanelBody>
					<PanelBody title={__("Preview", "learndash")} initialOpen={false}>
						<ToggleControl
							label={__("Show Preview", "learndash")}
							checked={!!preview_show}
							onChange={(preview_show) => setAttributes({ preview_show })}
						/>

						<PanelRow className="learndash-block-error-message">
							{__("Preview settings are not saved.", "learndash")}
						</PanelRow>

						<TextControl
							label={__("Preview User ID", "learndash")}
							help={__("Enter a User ID to test preview", "learndash")}
							value={preview_user_id || ""}
							type={"number"}
							onChange={function (preview_new_user_id) {
								if (preview_new_user_id != "" && preview_new_user_id < 0) {
									setAttributes({ preview_user_id: "0" });
								} else {
									setAttributes({ preview_user_id: preview_new_user_id });
								}
							}}
						/>
					</PanelBody>
				</InspectorControls>
			);

			function get_default_message() {
				return sprintf(
					// translators: placeholder: block_title.
					_x('%s block output shown here', 'placeholder: block_title', 'learndash'), block_title
				);
			}

			function empty_response_placeholder_function(props) {
				return get_default_message();
			}

			function do_serverside_render(attributes) {
				if ( attributes.preview_show == true ) {
					// We add the meta so the server knowns what is being edited.
					attributes.editing_post_meta = ldlms_get_post_edit_meta();

					return <ServerSideRender
						block={block_key}
						attributes={attributes}
						key={block_key}
						EmptyResponsePlaceholder={ empty_response_placeholder_function }
					/>
				} else {
					return get_default_message();
				}
			}

			return [
				inspectorControls,
				useMemo(() => do_serverside_render(props.attributes), [props.attributes]),
			];
		},

		save: props => {
			delete (props.attributes.example_show);
			delete (props.attributes.editing_post_meta);
		}
	},
);
