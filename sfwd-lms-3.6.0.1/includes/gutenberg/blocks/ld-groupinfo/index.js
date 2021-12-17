/**
 * LearnDash Block ld-groupinfo
 *
 * @since 3.2.0
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
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

registerBlockType(
	'learndash/ld-groupinfo',
	{
		// translators: placeholder: Group.
		title: sprintf( _x( 'LearnDash %s Info [groupinfo]', 'placeholder: Group', 'learndash' ), ldlms_get_custom_label( 'group' ) ),
		// translators: placeholder: Group.
		description: sprintf(_x('This block displays %s related information', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group') ),
		icon: 'analytics',
		category: 'learndash-blocks',
		supports: {
			customClassName: false,
		},
		attributes: {
			show: {
				type: 'string',
			},
			group_id: {
				type: 'string',
				default: '',
			},
			user_id: {
				type: 'string',
				default: '',
			},
			format: {
				type: 'string',
			},
			decimals: {
				type: 'string',
			},
			preview_show: {
				type: 'boolean',
				default: 1
			},
			preview_group_id: {
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
			const { attributes: { group_id, show, user_id, format, decimals, preview_show, preview_user_id },
				setAttributes } = props;

			const field_show = (
				<SelectControl
					key="show"
					value={show}
					label={__('Show', 'learndash')}
					options={[
						{
							// translators: placeholder: Group.
							label: sprintf(_x('%s Title', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group')),
							value: 'group_title',
						},
						{
							// translators: placeholder: Group.
							label: sprintf(_x('%s URL', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group')),
							value: 'group_url',
						},
						{
							// translators: placeholder: Group.
							label: sprintf(_x('%s Price', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group')),
							value: 'group_price',
						},
						{
							// translators: placeholder: Group.
							label: sprintf(_x('%s Price Type', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group')),
							value: 'group_price_type',
						},
						{
							// translators: placeholder: Group.
							label: sprintf(_x('%s Enrolled Users Count', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group')),
							value: 'group_users_count',
						},
						{
							// translators: placeholder: Group, Courses.
							label: sprintf(_x('%1$s %2$s Count', 'placeholder: Group, Courses', 'learndash'), ldlms_get_custom_label('group'), ldlms_get_custom_label('courses')),
							value: 'group_courses_count',
						},
						{
							// translators: placeholder: Group.
							label: sprintf(_x('User %s Status', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group')),
							value: 'user_group_status',
						},

						{
							// translators: placeholder: Group.
							label: sprintf(_x('%s Completed On (date)', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group')),
							value: 'completed_on',
						},
						{
							// translators: placeholder: Group.
							label: sprintf(_x('%s Enrolled On (date)', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group')),
							value: 'enrolled_on',
						},
						{
							// translators: placeholder: Group.
							label: sprintf(_x('%s Completed Percentage', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group')),
							value: 'percent_completed',
						},
					]}
					onChange={show => setAttributes({ show })}
				/>
			);

			const field_group_id = (
				<TextControl
					// translators: placeholder: Group.
					label={sprintf(_x('%s ID', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group'))}
					// translators: placeholders: Group, Group.
					help={sprintf(_x('Enter single %1$s ID. Leave blank if used within a %2$s.', 'placeholders: Group, Group', 'learndash'), ldlms_get_custom_label('group'), ldlms_get_custom_label('group'))}
					value={group_id || ''}
					onChange={group_id => setAttributes({ group_id })}
				/>
			);
			let field_user_id = '';

			if (['user_group_status', 'completed_on', 'enrolled_on', 'percent_completed' ].includes(show)) {
				field_user_id = (
					<TextControl
						label={__('User ID', 'learndash')}
						help={__('Enter specific User ID. Leave blank for current User.', 'learndash')}
						value={user_id || ''}
						onChange={user_id => setAttributes({ user_id })}
					/>
				);
			}

			let field_format = '';
			if (['completed_on', 'enrolled_on'].includes(show)) {
				field_format = (
					<TextControl
						label={__('Format', 'learndash')}
						help={__('This can be used to change the date format. Default: "F j, Y, g:i a.', 'learndash')}
						value={format || ''}
						onChange={format => setAttributes({ format })}
					/>
				);
			}

			let field_decimals = '';
			if (['percent_completed'].includes(show)) {
				field_decimals = (
					<TextControl
						label={__('Decimals', 'learndash')}
						help={__('Number of decimal places to show. Default is 2.', 'learndash')}
						value={decimals || ''}
						onChange={decimals => setAttributes({ decimals })}
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
						{ field_group_id }
						{ field_user_id }
						{ field_show }
						{ field_format }
						{ field_decimals }
					</PanelBody>
					{ panel_preview }
				</InspectorControls>
			);

			function do_serverside_render(attributes) {
				if (attributes.preview_show == true) {
					// We add the meta so the server knowns what is being edited.
					attributes.meta = ldlms_get_post_edit_meta();

					return <ServerSideRender
						block="learndash/ld-groupinfo"
						attributes={attributes}
						key="learndash/ld-groupinfo"
					/>
				} else {
					return __('[groupinfo] shortcode output shown here', 'learndash');
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
		}
	},
);
