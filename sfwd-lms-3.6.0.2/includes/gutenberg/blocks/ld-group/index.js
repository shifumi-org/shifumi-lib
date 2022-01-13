/**
 * LearnDash Block ld-group
 *
 * @since 2.5.9
 * @package LearnDash
 */

/**
 * LearnDash block functions
 */
import {
	ldlms_get_custom_label,
	ldlms_get_integer_value
} from '../ldlms.js';

/**
 * Internal block libraries
 */
import { __, _x, sprintf } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

const block_title = sprintf(
		// translators: placeholder: Group.
	_x('LearnDash %s', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group')
	);

registerBlockType(
	'learndash/ld-group',
	{
		title: block_title,
		// translators: placeholder: Group.
		description: sprintf(_x( 'This block shows the content if the user is enrolled into the %s.', 'learndash'), ldlms_get_custom_label('group')),
		icon: 'groups',
		category: 'learndash-blocks',
		supports: {
			customClassName: false,
		},
		attributes: {
			group_id: {
				type: 'string',
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
			const { attributes: { group_id, user_id, autop }, className, setAttributes } = props;

			const inspectorControls = (
				<InspectorControls key="controls">
					<PanelBody
						title={__('Settings', 'learndash')}
					>
						<TextControl
							// translators: placeholder: Group.
							label={sprintf(_x('%s ID', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group'))}
							// translators: placeholder: Group.
							help={sprintf(_x('%s ID (required)', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group'))}
							value={group_id || ''}
							onChange={group_id => setAttributes({ group_id })}
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
			let preview_group_id = ldlms_get_integer_value(group_id);
			if (preview_group_id == 0) {
				// translators: placeholder: Group.
				ld_block_error_message = sprintf(_x('%s ID is required.', 'placeholder: Group', 'learndash'), ldlms_get_custom_label('group'));
			}

			if (ld_block_error_message.length) {
				ld_block_error_message = (<span className="learndash-block-error-message">{ld_block_error_message}</span>);
			}

			const outputBlock = (
				<div className={className} key='learndash/ld-group'>
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
