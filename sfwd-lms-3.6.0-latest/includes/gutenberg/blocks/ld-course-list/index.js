/**
 * LearnDash Block ld-course-list
 *
 * @since 2.5.9
 * @package LearnDash
 */

/**
 * LearnDash block functions
 */
import {
	ldlms_get_custom_label,
	ldlms_get_per_page,
} from '../ldlms.js';

/**
 * Internal block libraries
 */
import { __, _x, sprintf} from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

registerBlockType(
	'learndash/ld-course-list',
	{
		// translators: placeholder: Course.
		title: sprintf(_x('LearnDash %s List', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course')),
		// translators: placeholder: Courses.
		description: sprintf(_x('This block shows a list of %s.', 'placeholders: Courses', 'learndash'), ldlms_get_custom_label('courses') ),
		icon: 'list-view',
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
			orderby: {
				type: 'string',
				default: 'ID'
			},
			order: {
				type: 'string',
				default: 'DESC'
			},
			per_page: {
				type: 'string',
				default: '',
			},
			mycourses: {
				type: 'string',
				default: ''
			},
			status: {
				type: 'array',
				default: ['not_started', 'in_progress', 'completed'],
			},
			show_content: {
				type: 'boolean',
				default: true
			},
			show_thumbnail: {
				type: 'boolean',
				default: true
			},
			course_category_name: {
				type: 'string',
				default: ''
			},
			course_cat: {
				type: 'string',
				default: ''
			},
			course_categoryselector: {
				type: 'boolean',
				default: false
			},
			course_tag: {
				type: 'string',
				default: ''
			},
			course_tag_id: {
				type: 'string',
				default: ''
			},
			category_name: {
				type: 'string',
				default: ''
			},
			cat: {
				type: 'string',
				default: ''
			},
			categoryselector: {
				type: 'boolean',
				default: false
			},
			tag: {
				type: 'string',
				default: ''
			},
			tag_id: {
				type: 'string',
				default: ''
			},
			course_grid: {
				type: 'boolean',
			},
			progress_bar: {
				type: 'boolean',
				default: false
			},
			col: {
				type: 'integer',
				default: (ldlms_settings['plugins']['learndash-course-grid']['col_default'] || 3),
			},
			preview_show: {
				type: 'boolean',
				default: true
			},
			example_show: {
				type: 'boolean',
				default: 0
			},
			price_type: {
				type: 'array',
				default: ['open', 'free', 'paynow', 'subscribe', 'closed'],
			},
		},
		edit: function( props ) {
			const { attributes: { orderby, order, per_page, mycourses, status, show_content, show_thumbnail, course_category_name, course_cat, course_categoryselector, course_tag, course_tag_id, category_name, cat, categoryselector, tag, tag_id, course_grid, progress_bar, col, preview_user_id, preview_show, example_show, price_type },
				setAttributes } = props;

			let field_show_content = '';
			let field_show_thumbnail = '';
			let panel_course_grid_section = '';

			let course_grid_default = true;
			if (ldlms_settings['plugins']['learndash-course-grid']['enabled'] === true) {
				if ((typeof course_grid !== 'undefined') && ((course_grid == true) || (course_grid == false)) ) {
					course_grid_default = course_grid;
				}

				let course_grid_section_open = false;
				if ( course_grid_default == true ) {
					course_grid_section_open = true;
				}
				panel_course_grid_section = (
					<PanelBody
						title={__('Grid Settings', 'learndash')}
						initialOpen={course_grid_section_open}
					>
						<ToggleControl
							label={__('Show Grid', 'learndash')}
							checked={!!course_grid_default}
							onChange={course_grid => setAttributes({ course_grid })}
						/>
						<ToggleControl
							label={__('Show Progress Bar', 'learndash')}
							checked={!!progress_bar}
							onChange={progress_bar => setAttributes({ progress_bar })}
						/>
						<RangeControl
							label={__('Columns', 'learndash')}
							value={col || ldlms_settings['plugins']['learndash-course-grid']['col_default']}
							min={1}
							max={ldlms_settings['plugins']['learndash-course-grid']['col_max']}
							step={1}
							onChange={col => setAttributes({ col })}
						/>
					</PanelBody>
				);
			}

			//if (course_grid !== true) {
				field_show_content = (
					<ToggleControl
						label={__('Show Content', 'learndash')}
						checked={!!show_content}
						onChange={show_content => setAttributes({ show_content })}
					/>
				);

				field_show_thumbnail = (
					<ToggleControl
						label={__('Show Thumbnail', 'learndash')}
						checked={!!show_thumbnail}
						onChange={show_thumbnail => setAttributes({ show_thumbnail })}
					/>
				);
			//}

			const panelbody_header = (
				<PanelBody
					className="learndash-block-controls-panel learndash-block-controls-panel-ld-course-list"
					title={__('Settings', 'learndash')}
				>
					<SelectControl
						key="orderby"
						label={__('Order by', 'learndash')}
						value={orderby}
						options={[
							{
								label: __('ID - Order by post id. (default)', 'learndash'),
								value: 'ID',
							},
							{
								label: __('Title - Order by post title', 'learndash'),
								value: 'title',
							},
							{
								label: __('Date - Order by post date', 'learndash'),
								value: 'date',
							},
							{
								label: __('Menu - Order by Page Order Value', 'learndash'),
								value: 'menu_order',
							}
						]}
						onChange={ orderby => setAttributes({ orderby })}
					/>
					<SelectControl
						key="order"
						label={__('Order', 'learndash')}
						value={order}
						options={[
							{
								label: __('DESC - highest to lowest values (default)', 'learndash'),
								value: 'DESC',
							},
							{
								label: __('ASC - lowest to highest values', 'learndash'),
								value: 'ASC',
							},
						]}
						onChange={order => setAttributes({ order })}
					/>
					<TextControl
						// translators: placeholder: Courses.
						label={sprintf(_x('%s per page', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
						// translators: placeholder: default per page.
						help={sprintf(_x('Leave empty for default (%d) or 0 to show all items.', 'placeholder: default per page', 'learndash'), ldlms_get_per_page('per_page' ) ) }
						value={per_page || ''}
						type={'number'}
						onChange={per_page => setAttributes({ per_page })}
					/>

					<SelectControl
						multiple
						key="price_type"
						// translators: placeholder: Course Access Mode(s)
						label={sprintf(_x('%s Access Mode(s)', 'placeholder" Course Access Mode(s)', 'learndash'), ldlms_get_custom_label('course'))}
						help={__( 'Ctrl+click to deselect selected items.', 'placeholder: default per page', 'learndash' )}
						value={price_type}
						options={[
							{
								label: __('Open', 'learndash'),
								value: 'open',
							},
							{
								label: __('Free', 'learndash'),
								value: 'free',
							},
							{
								label: __('Buy Now', 'learndash'),
								value: 'paynow',
							},
							{
								label: __('Recurring', 'learndash'),
								value: 'subscribe',
							},
							{
								label: __('Closed', 'learndash'),
								value: 'closed',
							},
						]}
						onChange={price_type => setAttributes({ price_type })}
						/>

					<SelectControl
						key="mycourses"
						// translators: placeholder: Courses.
						label={sprintf(_x('My %s', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
						value={mycourses}
						options={[
							{
								// translators: placeholder: Courses.
								label: sprintf(_x('Show All %s (default)', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses') ),
								value: '',
							},
							{
								// translators: placeholder: Courses.
								label: sprintf(_x('Show Enrolled %s only', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses')),
								value: 'enrolled',
							},
							{
								// translators: placeholder: Courses.
								label: sprintf(_x('Show not-Enrolled %s only', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses')),
								value: 'not-enrolled',
							},
						]}
						onChange={mycourses => setAttributes({ mycourses })}
					/>
					{'enrolled' === mycourses && (
					<SelectControl
						multiple
						key="status"
						// translators: placeholder: Courses.
						label={sprintf(_x('Enrolled %s Status', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
						help={__( 'Ctrl+click to deselect selected items.', 'placeholder: default per page', 'learndash' )}
						value={status}
						options={[
							{
								label: __('Not Started', 'learndash'),
								value: 'not_started',
							},
							{
								label: __('In Progress', 'learndash'),
								value: 'in_progress',
							},
							{
								label: __('Completed', 'learndash'),
								value: 'completed',
							},
						]}
						onChange={status => setAttributes({ status })}
					/>
					)}
					{field_show_content}
					{field_show_thumbnail}
				</PanelBody>
			);

			let panel_course_category_section = '';
			if ( ldlms_settings['settings']['courses_taxonomies']['ld_course_category'] === 'yes' ) {
				let panel_course_category_section_open = false;
				if ((course_category_name != '') || (course_cat != '')) {
					panel_course_category_section_open = true;
				}
				panel_course_category_section = (
					<PanelBody
						// translators: placeholder: Course.
						title={sprintf(_x('%s Category Settings', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course') ) }
						initialOpen={panel_course_category_section_open}
					>
						<TextControl
							// translators: placeholder: Course.
							label={sprintf(_x('%s Category Slug', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course') )}
							// translators: placeholder: Courses.
							help={sprintf(_x('shows %s with mentioned category slug.', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
							value={course_category_name || ''}
							onChange={course_category_name => setAttributes({ course_category_name })}
						/>

						<TextControl
							// translators: placeholder: Course.
							label={sprintf(_x('%s Category ID', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course'))}
							// translators: placeholder: Courses.
							help={sprintf(_x('shows %s with mentioned category ID.', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
							value={course_cat || ''}
							onChange={course_cat => setAttributes({ course_cat })}
						/>
						<ToggleControl
							// translators: placeholder: Course.
							label={sprintf(_x('%s Category Selector', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course'))}
							// translators: placeholder: Courses.
							help={sprintf(_x('shows a %s category dropdown.', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
							checked={!!course_categoryselector}
							onChange={course_categoryselector => setAttributes({ course_categoryselector })}
						/>
					</PanelBody>
				);
			}

			let panel_course_tag_section = '';
			if (ldlms_settings['settings']['courses_taxonomies']['ld_course_tag'] === 'yes') {
				let panel_course_tag_section_open = false;
				if ((course_tag != '') || (course_tag_id != '')) {
					panel_course_tag_section_open = true;
				}
				panel_course_tag_section = (
					<PanelBody
						// translators: placeholder: Course.
						title={sprintf(_x('%s Tag Settings', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course'))}
						initialOpen={panel_course_tag_section_open}
					>
						<TextControl
							// translators: placeholder: Course.
							label={sprintf(_x('%s Tag Slug', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course'))}
							// translators: placeholder: Courses.
							help={sprintf(_x('shows %s with mentioned tag slug.', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
							value={course_tag || ''}
							onChange={course_tag => setAttributes({ course_tag })}
						/>

						<TextControl
							// translators: placeholder: Course.
							label={sprintf(_x('%s Tag ID', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course'))}
							// translators: placeholder: Courses.
							help={sprintf(_x('shows %s with mentioned tag ID.', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
							value={course_tag_id || ''}
							onChange={course_tag_id => setAttributes({ course_tag_id })}
						/>
					</PanelBody>
				);
			}

			let panel_wp_category_section = '';
			if (ldlms_settings['settings']['courses_taxonomies']['wp_post_category'] === 'yes') {
				let panel_wp_category_section_open = false;
				if ((category_name != '') || (cat != '')) {
					panel_wp_category_section_open = true;
				}
				panel_wp_category_section = (
					<PanelBody
						title={__('WP Category Settings', 'learndash')}
						initialOpen={panel_wp_category_section_open}
					>
						<TextControl
							label={__('WP Category Slug', 'learndash') }
							// translators: placeholder: Courses.
							help={sprintf(_x('shows %s with mentioned WP category slug.', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
							value={category_name || ''}
							onChange={category_name => setAttributes({ category_name })}
						/>

						<TextControl
							// translators: placeholder: Course.
							label={sprintf(_x('%s Category ID', 'placeholder: Course', 'learndash'), ldlms_get_custom_label('course'))}
							// translators: placeholder: Courses.
							help={sprintf(_x('shows %s with mentioned category ID.', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
							value={cat || ''}
							onChange={cat => setAttributes({ cat })}
						/>
						<ToggleControl
							label={__('WP Category Selector', 'learndash') }
							help={__('shows a WP category dropdown.', 'learndash')}
							checked={!!categoryselector}
							onChange={categoryselector => setAttributes({ categoryselector })}
						/>
					</PanelBody>
				);
			}

			let panel_wp_tag_section = '';
			if (ldlms_settings['settings']['courses_taxonomies']['wp_post_tag'] === 'yes') {
				let panel_wp_tag_section_open = false;
				if ((tag != '') || (tag_id != '')) {
					panel_wp_tag_section_open = true;
				}
				panel_wp_tag_section = (
					<PanelBody
						title={__('WP Tag Settings', 'learndash')}
						initialOpen={panel_wp_tag_section_open}
					>
						<TextControl
							label={__('WP Tag Slug', 'learndash')}
							// translators: placeholder: Courses.
							help={sprintf(_x('shows %s with mentioned WP tag slug.', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
							value={tag || ''}
							onChange={tag => setAttributes({ tag })}
						/>

						<TextControl
							label={__('WP Tag ID', 'learndash')}
							// translators: placeholder: Courses.
							help={sprintf(_x('shows %s with mentioned WP tag ID.', 'placeholder: Courses', 'learndash'), ldlms_get_custom_label('courses'))}
							value={tag_id || ''}
							onChange={tag_id => setAttributes({ tag_id })}
						/>
					</PanelBody>
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
					{ panelbody_header }
					{ panel_course_grid_section}
					{ panel_course_category_section }
					{ panel_course_tag_section }
					{ panel_wp_category_section }
					{ panel_wp_tag_section }
					{ panel_preview }
				</InspectorControls>
			);

			function do_serverside_render( attributes ) {
				if ( attributes.preview_show == true ) {
					return <ServerSideRender
					block="learndash/ld-course-list"
					attributes={ attributes }
					key="learndash/ld-course-list"
					/>
				} else {
					return __( '[ld_course_list] shortcode output shown here', 'learndash' );
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
