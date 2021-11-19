/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./includes/gutenberg/blocks/components/filter-panel-body.js":
/*!*******************************************************************!*\
  !*** ./includes/gutenberg/blocks/components/filter-panel-body.js ***!
  \*******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);


/**
 * FilterPanelBody component
 *
 * @since 2.0
 */

/**
 * Internal block libraries
 */




class FilterPanelBody extends _wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const {
      context,
      course_grid_id,
      search,
      taxonomies,
      price,
      price_min,
      price_max,
      setAttributes
    } = this.props;
    let search_key = 'search';
    let taxonomies_key = 'taxonomies';
    let price_key = 'price';
    let price_min_key = 'price_min';
    let price_max_key = 'price_max';

    if (context == 'page') {
      search_key = 'filter_search';
      taxonomies_key = 'filter_taxonomies';
      price_key = 'filter_price';
      price_min_key = 'filter_price_min';
      price_max_key = 'filter_price_max';
    }

    const taxonomies_options = LearnDash_Course_Grid_Block_Editor.taxonomies;
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Filter', 'learndash-course-grid'),
      initialOpen: context == 'page' ? false : true
    }, context == 'widget' && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Course Grid ID'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Course grid ID the filter is for.'),
      value: course_grid_id || '',
      type: 'text',
      onChange: course_grid_id => setAttributes({
        course_grid_id
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Search', 'learndash-course-grid'),
      checked: search,
      onChange: search => {
        const search_obj = {
          [search_key]: search
        };
        setAttributes(search_obj);
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.BaseControl, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.SelectControl, {
      multiple: true,
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Taxonomies', 'learndash-course-grid'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Hold ctrl on Windows or cmd on Mac to select multiple values.', 'learndash-course-grid'),
      options: taxonomies_options,
      value: taxonomies || [],
      onChange: taxonomies => {
        const taxonomies_obj = {
          [taxonomies_key]: taxonomies
        };
        setAttributes(taxonomies_obj);
      }
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Price', 'learndash-course-grid'),
      checked: price,
      onChange: price => {
        const price_obj = {
          [price_key]: price
        };
        setAttributes(price_obj);
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.BaseControl, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Price Min'),
      className: 'left',
      value: price_min || 0,
      type: 'number',
      onChange: price_min => {
        const price_min_obj = {
          [price_min_key]: price_min
        };
        setAttributes(price_min_obj);
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Price Max'),
      className: 'right',
      value: price_max || 0,
      type: 'number',
      onChange: price_max => {
        const price_max_obj = {
          [price_max_key]: price_max
        };
        setAttributes(price_max_obj);
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      style: {
        clear: 'both'
      }
    })));
  }

}

/* harmony default export */ __webpack_exports__["default"] = (FilterPanelBody);

/***/ }),

/***/ "./includes/gutenberg/blocks/learndash-course-grid-filter/index.js":
/*!*************************************************************************!*\
  !*** ./includes/gutenberg/blocks/learndash-course-grid-filter/index.js ***!
  \*************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/server-side-render */ "@wordpress/server-side-render");
/* harmony import */ var _wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _components_filter_panel_body_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../components/filter-panel-body.js */ "./includes/gutenberg/blocks/components/filter-panel-body.js");


/**
 * LearnDash Block ld-course-grid-filter
 *
 * @since 2.0
 */

/**
 * Internal block libraries
 */







(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__.registerBlockType)('learndash/ld-course-grid-filter', {
  title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('LearnDash Course Grid Filter', 'learndash-course-grid'),
  description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('LearnDash course grid filter widget.', 'learndash-course-grid'),
  icon: 'filter',
  category: 'learndash-blocks',
  supports: {
    customClassName: false
  },
  attributes: {
    course_grid_id: {
      type: 'string',
      default: ''
    },
    search: {
      type: 'boolean',
      default: 1
    },
    taxonomies: {
      type: 'array',
      default: ['category', 'post_tag']
    },
    price: {
      type: 'boolean',
      default: 1
    },
    price_min: {
      type: 'string',
      default: 0
    },
    price_max: {
      type: 'string',
      default: 1000
    },
    preview_show: {
      type: 'boolean',
      default: 1
    }
  },
  edit: props => {
    const {
      attributes: {
        course_grid_id,
        search,
        taxonomies,
        price,
        price_min,
        price_max,
        preview_show
      },
      setAttributes
    } = props;
    const taxonomies_options = LearnDash_Course_Grid_Block_Editor.taxonomies;
    const inspectorControls = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: 'learndash-course-grid-filter-settings'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InspectorControls, {
      key: "controls"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Panel, {
      className: 'learndash-course-grid-filter-panel'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_filter_panel_body_js__WEBPACK_IMPORTED_MODULE_6__["default"], {
      context: 'widget',
      course_grid_id: course_grid_id,
      search: search,
      taxonomies: taxonomies,
      price: price,
      price_min: price_min,
      price_max: price_max,
      setAttributes: setAttributes
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Preview', 'learndash-course-grid'),
      initialOpen: false
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Show Preview', 'learndash-course-grid'),
      checked: !!preview_show,
      onChange: preview_show => setAttributes({
        preview_show
      })
    })))));

    function do_serverside_render(attributes) {
      if (attributes.preview_show == true) {
        // We add the meta so the server knowns what is being edited.
        // attributes.meta = ldlms_get_post_edit_meta()
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)((_wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5___default()), {
          block: "learndash/ld-course-grid-filter",
          attributes: attributes,
          key: "learndash/ld-course-grid-filter"
        });
      } else {
        return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('[learndash_course_grid_filter] shortcode output shown here', 'learndash-course-grid');
      }
    }

    return [inspectorControls, do_serverside_render(props.attributes)];
  },
  save: props => {}
});

/***/ }),

/***/ "./includes/gutenberg/blocks/learndash-course-grid/index.js":
/*!******************************************************************!*\
  !*** ./includes/gutenberg/blocks/learndash-course-grid/index.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/server-side-render */ "@wordpress/server-side-render");
/* harmony import */ var _wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _components_filter_panel_body_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../components/filter-panel-body.js */ "./includes/gutenberg/blocks/components/filter-panel-body.js");


/**
 * LearnDash Block ld-course-grid
 *
 * @since 2.0
 */

/**
 * Internal block libraries
 */








function generate_unique_id() {
  const id = Date.now().toString(36) + Math.random().toString(36).substr(2);
  return 'ld-cg-' + id.substr(0, '10');
}

(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__.registerBlockType)('learndash/ld-course-grid', {
  title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('LearnDash Course Grid', 'learndash-course-grid'),
  description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Build LearnDash course grid easily.', 'learndash-course-grid'),
  icon: 'grid-view',
  category: 'learndash-blocks',
  supports: {
    customClassName: true
  },
  attributes: {
    // Query
    post_type: {
      type: 'string',
      default: 'sfwd-courses'
    },
    per_page: {
      type: 'string',
      default: 9
    },
    orderby: {
      type: 'string',
      default: 'ID'
    },
    order: {
      type: 'string',
      default: 'DESC'
    },
    taxonomies: {
      type: 'string',
      default: ''
    },
    enrollment_status: {
      type: 'string',
      default: ''
    },
    progress_status: {
      type: 'string',
      default: ''
    },
    // Elements
    thumbnail: {
      type: 'boolean',
      default: 1
    },
    thumbnail_size: {
      type: 'string',
      default: 'course-thumbnail'
    },
    ribbon: {
      type: 'boolean',
      default: 1
    },
    content: {
      type: 'boolean',
      default: 1
    },
    title: {
      type: 'boolean',
      default: 1
    },
    title_clickable: {
      type: 'boolean',
      default: 1
    },
    description: {
      type: 'boolean',
      default: 1
    },
    description_char_max: {
      type: 'string',
      default: 120
    },
    meta: {
      type: 'boolean',
      default: 1
    },
    button: {
      type: 'boolean',
      default: 1
    },
    pagination: {
      type: 'string',
      default: 'button'
    },
    grid_height_equal: {
      type: 'boolean',
      default: 0
    },
    progress_bar: {
      type: 'boolean',
      default: 0
    },
    filter: {
      type: 'boolean',
      default: 1
    },
    // Templates
    skin: {
      type: 'string',
      default: 'grid'
    },
    card: {
      type: 'string',
      default: 'grid-1'
    },
    columns: {
      type: 'string',
      default: 3
    },
    min_column_width: {
      type: 'string',
      default: 250
    },
    items_per_row: {
      type: 'string',
      default: 5
    },
    // Styles
    font_family_title: {
      type: 'string'
    },
    font_family_description: {
      type: 'string',
      default: ''
    },
    font_size_title: {
      type: 'string',
      default: ''
    },
    font_size_description: {
      type: 'string',
      default: ''
    },
    font_color_title: {
      type: 'string',
      default: ''
    },
    font_color_description: {
      type: 'string',
      default: ''
    },
    background_color_title: {
      type: 'string',
      default: ''
    },
    background_color_description: {
      type: 'string',
      default: ''
    },
    // Misc
    id: {
      type: 'string',
      default: ''
    },
    preview_show: {
      type: 'boolean',
      default: 1
    },
    display_state: {
      type: 'object',
      default: {}
    },
    // Filter
    filter_search: {
      type: 'boolean',
      default: 1
    },
    filter_taxonomies: {
      type: 'array',
      default: ['category', 'post_tag']
    },
    filter_price: {
      type: 'boolean',
      default: 1
    },
    filter_price_min: {
      type: 'string',
      default: 0
    },
    filter_price_max: {
      type: 'string',
      default: 1000
    }
  },
  edit: props => {
    const {
      attributes: {
        post_type,
        per_page,
        orderby,
        order,
        taxonomies,
        enrollment_status,
        progress_status,
        thumbnail,
        thumbnail_size,
        ribbon,
        content,
        title,
        title_clickable,
        description,
        description_char_max,
        meta,
        button,
        pagination,
        grid_height_equal,
        progress_bar,
        filter,
        skin,
        card,
        columns,
        min_column_width,
        items_per_row,
        font_family_title,
        font_family_description,
        font_size_title,
        font_size_description,
        font_color_title,
        font_color_description,
        background_color_title,
        background_color_description,
        id,
        display_state,
        preview_show,
        filter_search,
        filter_taxonomies,
        filter_price,
        filter_price_min,
        filter_price_max
      },
      className,
      setAttributes
    } = props;

    if (id == '') {
      const temp_id = generate_unique_id();
      setAttributes({
        id: temp_id
      });
    }

    const post_type_options = LearnDash_Course_Grid_Block_Editor.post_types;
    const pagination_options = LearnDash_Course_Grid_Block_Editor.paginations;
    const skins = LearnDash_Course_Grid_Block_Editor.skins;
    const cards = LearnDash_Course_Grid_Block_Editor.cards;
    const skin_options = [],
          skin_disabled_fields = {};

    for (const id in skins) {
      if (Object.hasOwnProperty.call(skins, id)) {
        const element = {
          label: skins[id].label,
          value: skins[id].slug
        };
        skin_options.push(element);

        if (Object.hasOwnProperty.call(skins[id], 'disable')) {
          skin_disabled_fields[skins[id].slug] = skins[id].disable;
        }
      }
    }

    const card_options = [],
          card_values = [],
          skin_cards = {},
          card_disabled_fields = {};

    for (const id in cards) {
      if (Object.hasOwnProperty.call(cards, id)) {
        if (Object.hasOwnProperty.call(cards[id], 'disable')) {
          card_disabled_fields[cards[id]] = cards[id].disable;
        }

        if (Object.hasOwnProperty.call(cards[id], 'skins')) {
          cards[id].skins.forEach(function (temp_skin) {
            skin_cards[temp_skin] = skin_cards[temp_skin] || [];
            skin_cards[temp_skin].push(id);
          });
        }

        if (typeof cards[id].skins !== 'undefined' && cards[id].skins.indexOf(skin) > -1) {
          const element = {
            label: cards[id].label,
            value: id
          };
          card_options.push(element);
          card_values.push(id);
        }
      }
    }

    const thumbnail_size_options = LearnDash_Course_Grid_Block_Editor.image_sizes;
    const orderby_options = LearnDash_Course_Grid_Block_Editor.orderby;
    const order_options = [{
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Ascending', 'learndash-course-grid'),
      value: 'ASC'
    }, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Descending', 'learndash-course-grid'),
      value: 'DESC'
    }];
    const enrollment_status_options = [{
      value: '',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('All', 'learndash-course-grid')
    }, {
      value: 'enrolled',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enrolled', 'learndash-course-grid')
    }, {
      value: 'not-enrolled',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Not Enrolled', 'learndash-course-grid')
    }];
    const progress_status_options = [{
      value: '',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('All', 'learndash-course-grid')
    }, {
      value: 'completed',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Completed', 'learndash-course-grid')
    }, {
      value: 'in_progress',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('In Progress', 'learndash-course-grid')
    }, {
      value: 'not_started',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Not Started', 'learndash-course-grid')
    }];
    selectSkin(props);
    const inspectorControls = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      key: 'learndash-course-grid-settings'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InspectorControls, {
      key: "controls"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.Panel, {
      className: 'learndash-course-grid-panel'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Template', 'learndash-course-grid'),
      initialOpen: true
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.skin !== 'undefined' && !display_state.skin ? 'hide' : 'show'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Skin', 'learndash-course-grid'),
      options: skin_options,
      value: skin || '',
      onChange: skin => {
        setAttributes({
          skin
        });
        selectSkin(props);
      }
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.card !== 'undefined' && !display_state.card ? 'hide' : 'show'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Card', 'learndash-course-grid'),
      options: card_options,
      value: card || '',
      onChange: card => {
        setAttributes({
          card
        });
      }
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Columns'),
      value: columns || '',
      type: 'number',
      onChange: columns => setAttributes({
        columns
      }),
      className: typeof display_state.columns !== 'undefined' && !display_state.columns ? 'hide' : 'show'
    }), ['grid', 'masonry'].indexOf(skin) > -1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Min Column Width (in pixel)', 'learndash-course-grid'),
      value: min_column_width,
      type: 'number',
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('If column width reach value lower than this, the grid columns number will automatically be adjusted on display.', 'learndash-course-grid'),
      onChange: min_column_width => setAttributes({
        min_column_width
      }),
      className: typeof display_state.min_column_width !== 'undefined' && !display_state.min_column_width ? 'hide' : 'show'
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Items Per Row'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Number of items per row. Certain skins use this to customize the design.'),
      value: items_per_row || '',
      type: 'number',
      onChange: items_per_row => setAttributes({
        items_per_row
      }),
      className: typeof display_state.items_per_row !== 'undefined' && !display_state.items_per_row ? 'hide' : 'show'
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Query', 'learndash-course-grid'),
      initialOpen: false
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.post_type !== 'undefined' && !display_state.post_type ? 'hide' : 'show'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Post Type', 'learndash-course-grid'),
      options: post_type_options,
      value: post_type || '',
      onChange: post_type => setAttributes({
        post_type
      })
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Posts per page'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enter 0 show all items.', 'learndash-course-grid'),
      value: per_page || '',
      type: 'number',
      onChange: per_page => setAttributes({
        per_page
      }),
      className: typeof display_state.per_page !== 'undefined' && !display_state.per_page ? 'hide' : 'show'
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.orderby !== 'undefined' && !display_state.orderby ? 'hide' : 'show'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Order By', 'learndash-course-grid'),
      options: orderby_options,
      value: orderby || '',
      onChange: orderby => setAttributes({
        orderby
      })
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.order !== 'undefined' && !display_state.order ? 'hide' : 'show'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Order', 'learndash-course-grid'),
      options: order_options,
      value: order || '',
      onChange: order => setAttributes({
        order
      })
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Taxonomies', 'learndash-course-grid'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Format:', 'learndash-course-grid') + ' "taxonomy1:term1,term2; taxonomy2:term1,term2;"',
      value: taxonomies || '',
      onChange: taxonomies => setAttributes({
        taxonomies
      }),
      className: typeof display_state.taxonomies !== 'undefined' && !display_state.taxonomies ? 'hide' : 'show' + ' taxonomies'
    }), ['sfwd-courses', 'groups'].indexOf(post_type) > -1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.enrollment_status !== 'undefined' && !display_state.enrollment_status ? 'hide' : 'show'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enrollment Status', 'learndash-course-grid'),
      options: enrollment_status_options,
      value: enrollment_status,
      onChange: enrollment_status => setAttributes({
        enrollment_status
      })
    })), ['sfwd-courses'].indexOf(post_type) > -1 && enrollment_status == 'enrolled' && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.progress_status !== 'undefined' && !display_state.progress_status ? 'hide' : 'show'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Progress Status', 'learndash-course-grid'),
      options: progress_status_options,
      value: progress_status,
      onChange: progress_status => setAttributes({
        progress_status
      })
    }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Elements', 'learndash-course-grid'),
      initialOpen: false
    }, cards[card].elements.indexOf('thumbnail') > -1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Thumbnail', 'learndash-course-grid'),
      checked: thumbnail,
      onChange: thumbnail => setAttributes({
        thumbnail
      }),
      className: typeof display_state.thumbnail !== 'undefined' && !display_state.thumbnail ? 'hide' : 'show'
    }), cards[card].elements.indexOf('thumbnail') > -1 && thumbnail && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.thumbnail_size !== 'undefined' && !display_state.thumbnail_size ? 'hide' : 'show'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Thumbnail Size', 'learndash-course-grid'),
      options: thumbnail_size_options,
      value: thumbnail_size || '',
      onChange: thumbnail_size => setAttributes({
        thumbnail_size
      })
    })), cards[card].elements.indexOf('ribbon') > -1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Ribbon', 'learndash-course-grid'),
      checked: ribbon,
      onChange: ribbon => setAttributes({
        ribbon
      }),
      className: typeof display_state.ribbon !== 'undefined' && !display_state.ribbon ? 'hide' : 'show'
    }), cards[card].elements.indexOf('content') > -1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Content', 'learndash-course-grid'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Content includes elements in the area outside of the thumbnail.', 'learndash-course-grid'),
      checked: content,
      onChange: content => setAttributes({
        content
      }),
      className: typeof display_state.content !== 'undefined' && !display_state.content ? 'hide' : 'show'
    }), cards[card].elements.indexOf('title') > -1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Title', 'learndash-course-grid'),
      checked: title,
      onChange: title => setAttributes({
        title
      }),
      className: typeof display_state.title !== 'undefined' && !display_state.title ? 'hide' : 'show'
    }), cards[card].elements.indexOf('title') > -1 && title && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Clickable Title', 'learndash-course-grid'),
      checked: title_clickable,
      onChange: title_clickable => setAttributes({
        title_clickable
      }),
      className: typeof display_state.title_clickable !== 'undefined' && !display_state.title_clickable ? 'hide' : 'show'
    }), cards[card].elements.indexOf('description') > -1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description', 'learndash-course-grid'),
      checked: description,
      onChange: description => setAttributes({
        description
      }),
      className: typeof display_state.description !== 'undefined' && !display_state.description ? 'hide' : 'show'
    }), cards[card].elements.indexOf('description') > -1 && description && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Max Description Character Count'),
      value: description_char_max || '',
      type: 'number',
      onChange: description_char_max => {
        setAttributes({
          description_char_max
        });
      }
    }), cards[card].elements.indexOf('meta') > -1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Meta', 'learndash-course-grid'),
      checked: meta,
      onChange: meta => setAttributes({
        meta
      }),
      className: typeof display_state.meta !== 'undefined' && !display_state.meta ? 'hide' : 'show'
    }), cards[card].elements.indexOf('button') > -1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Button', 'learndash-course-grid'),
      checked: button,
      onChange: button => setAttributes({
        button
      }),
      className: typeof display_state.button !== 'undefined' && !display_state.button ? 'hide' : 'show'
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Progress Bar', 'learndash-course-grid'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Available for LearnDash course and group.', 'learndash-course-grid'),
      checked: progress_bar,
      onChange: progress_bar => setAttributes({
        progress_bar
      }),
      className: typeof display_state.progress_bar !== 'undefined' && !display_state.progress_bar ? 'hide' : 'show'
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.pagination !== 'undefined' && !display_state.pagination ? 'hide' : 'show'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.SelectControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Pagination', 'learndash-course-grid'),
      options: pagination_options,
      value: pagination || '',
      onChange: pagination => setAttributes({
        pagination
      })
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Filter', 'learndash-course-grid'),
      checked: filter,
      onChange: filter => {
        setAttributes({
          filter
        });
      },
      className: typeof display_state.filter !== 'undefined' && !display_state.filter ? 'hide' : 'show'
    })), filter && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_filter_panel_body_js__WEBPACK_IMPORTED_MODULE_6__["default"], {
      context: 'page',
      course_grid_id: id,
      search: filter_search,
      taxonomies: filter_taxonomies,
      price: filter_price,
      price_min: filter_price_min,
      price_max: filter_price_max,
      setAttributes: setAttributes
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Styles', 'learndash-course-grid'),
      initialOpen: false
    }, skin == 'grid' && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      classNam: "grid-style"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Grid', 'learndash-course-grid')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Equal Grid Height', 'learndash-course-grid'),
      checked: grid_height_equal,
      onChange: grid_height_equal => setAttributes({
        grid_height_equal
      }),
      className: typeof display_state.grid_height_equal !== 'undefined' && !display_state.grid_height_equal ? 'hide' : 'show'
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Heading', 'learndash-course-grid')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Heading Font Family'),
      value: font_family_title || '',
      onChange: font_family_title => setAttributes({
        font_family_title
      }),
      className: typeof display_state.font_family_title !== 'undefined' && !display_state.font_family_title ? 'hide' : 'show'
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Heading Font Size'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Accepts full format, e.g. 18px, 2rem'),
      value: font_size_title || '',
      onChange: font_size_title => setAttributes({
        font_size_title
      }),
      className: typeof display_state.font_size_title !== 'undefined' && !display_state.font_size_title ? 'hide' : 'show'
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.font_color_title !== 'undefined' && !display_state.font_color_title ? 'hide color-picker' : 'show color-picker',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Heading Font Color')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "color-wrapper"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ColorIndicator, {
      colorValue: font_color_title || ''
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ColorPalette, {
      value: font_color_title || '',
      onChange: font_color_title => {
        setAttributes({
          font_color_title
        });
      },
      clearable: true
    }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.background_color_title !== 'undefined' && !display_state.background_color_title ? 'hide color-picker' : 'show color-picker',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Heading Background Color')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "color-wrapper"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ColorIndicator, {
      colorValue: background_color_title || ''
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ColorPalette, {
      value: background_color_title || '',
      onChange: background_color_title => {
        setAttributes({
          background_color_title
        });
      },
      clearable: true
    }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description', 'learndash-course-grid')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description Font Family'),
      value: font_family_description || '',
      onChange: font_family_description => setAttributes({
        font_family_description
      }),
      className: typeof display_state.font_family_description !== 'undefined' && !display_state.font_family_description ? 'hide' : 'show'
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description Font Size'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Accepts full format, e.g. 18px, 2rem'),
      value: font_size_description || '',
      onChange: font_size_description => setAttributes({
        font_size_description
      }),
      className: typeof display_state.font_size_description !== 'undefined' && !display_state.font_size_description ? 'hide' : 'show'
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.font_color_description !== 'undefined' && !display_state.font_color_description ? 'hide color-picker' : 'show color-picker',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description Font Color')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "color-wrapper"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ColorIndicator, {
      colorValue: font_color_description || ''
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ColorPalette, {
      value: font_color_description || '',
      onChange: font_color_description => {
        setAttributes({
          font_color_description
        });
      },
      clearable: true
    }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.BaseControl, {
      className: typeof display_state.background_color_description !== 'undefined' && !display_state.background_color_description ? 'hide color-picker' : 'show color-picker',
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Description Background Color')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "color-wrapper"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ColorIndicator, {
      colorValue: background_color_description || ''
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ColorPalette, {
      value: background_color_description || '',
      onChange: background_color_description => {
        setAttributes({
          background_color_description
        });
      },
      clearable: true
    })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.PanelBody, {
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Preview', 'learndash-course-grid'),
      initialOpen: false
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.ToggleControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Show Preview', 'learndash-course-grid'),
      checked: !!preview_show,
      onChange: preview_show => setAttributes({
        preview_show
      })
    })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InspectorAdvancedControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('ID'),
      help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Unique ID for CSS styling purpose.'),
      value: id || '',
      onChange: id => setAttributes({
        id
      }),
      className: typeof display_state.id !== 'undefined' && !display_state.id ? 'hide' : 'show'
    })));

    function do_serverside_render(attributes) {
      if (attributes.preview_show == true) {
        // We add the meta so the server knowns what is being edited.
        // attributes.meta = ldlms_get_post_edit_meta()
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)((_wordpress_server_side_render__WEBPACK_IMPORTED_MODULE_5___default()), {
          block: "learndash/ld-course-grid",
          attributes: attributes,
          key: "learndash/ld-course-grid"
        });
      } else {
        return (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('[learndash_course_grid] shortcode output shown here', 'learndash-course-grid');
      }
    }

    function selectSkin(props) {
      const {
        attributes = {
          skin,
          card,
          display_state
        },
        setAttributes
      } = props;
      let disabled_fields = [];

      if (typeof skin_disabled_fields[skin] !== 'undefined') {
        disabled_fields = skin_disabled_fields[skin];
      }

      LearnDash_Course_Grid_Block_Editor.editor_fields.forEach(field => {
        let temp_display_state = display_state;
        temp_display_state[field] = true;
        setAttributes({
          display_state: temp_display_state
        });
      });
      disabled_fields.forEach(field => {
        let temp_display_state = display_state;
        temp_display_state[field] = false;
        setAttributes({
          display_state: temp_display_state
        });
      });

      if (card_values.indexOf(card) == -1 && typeof skin_cards[skin][0] !== 'undefined') {
        let temp_card = card;
        temp_card = skin_cards[skin][0];
        setAttributes({
          card: temp_card
        });
      }
    }

    function setDisplayState(key, value) {
      const {
        display_state
      } = props.attributes;
      display_state[key] = value;
      setAttributes({
        display_state
      });
    }

    return [inspectorControls, do_serverside_render(props.attributes)];
  },
  save: props => {}
});

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ (function(module) {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ (function(module) {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ (function(module) {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "@wordpress/server-side-render":
/*!******************************************!*\
  !*** external ["wp","serverSideRender"] ***!
  \******************************************/
/***/ (function(module) {

module.exports = window["wp"]["serverSideRender"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!********************************************!*\
  !*** ./includes/gutenberg/blocks/index.js ***!
  \********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _learndash_course_grid__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./learndash-course-grid */ "./includes/gutenberg/blocks/learndash-course-grid/index.js");
/* harmony import */ var _learndash_course_grid_filter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./learndash-course-grid-filter */ "./includes/gutenberg/blocks/learndash-course-grid-filter/index.js");
/**
 * Import blocks
 */


}();
/******/ })()
;
//# sourceMappingURL=index.js.map