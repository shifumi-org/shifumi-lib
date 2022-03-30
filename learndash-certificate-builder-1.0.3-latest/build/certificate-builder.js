(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["style-certificate-builder"],{

/***/ "./blocks/certificate-builder/style.scss":
/*!***********************************************!*\
  !*** ./blocks/certificate-builder/style.scss ***!
  \***********************************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports, __webpack_require__) {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./blocks/certificate-builder/style.scss?");

/***/ })

}]);

/******/ (function(modules) { // webpackBootstrap
/******/ 	// install a JSONP callback for chunk loading
/******/ 	function webpackJsonpCallback(data) {
/******/ 		var chunkIds = data[0];
/******/ 		var moreModules = data[1];
/******/ 		var executeModules = data[2];
/******/
/******/ 		// add "moreModules" to the modules object,
/******/ 		// then flag all "chunkIds" as loaded and fire callback
/******/ 		var moduleId, chunkId, i = 0, resolves = [];
/******/ 		for(;i < chunkIds.length; i++) {
/******/ 			chunkId = chunkIds[i];
/******/ 			if(Object.prototype.hasOwnProperty.call(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 				resolves.push(installedChunks[chunkId][0]);
/******/ 			}
/******/ 			installedChunks[chunkId] = 0;
/******/ 		}
/******/ 		for(moduleId in moreModules) {
/******/ 			if(Object.prototype.hasOwnProperty.call(moreModules, moduleId)) {
/******/ 				modules[moduleId] = moreModules[moduleId];
/******/ 			}
/******/ 		}
/******/ 		if(parentJsonpFunction) parentJsonpFunction(data);
/******/
/******/ 		while(resolves.length) {
/******/ 			resolves.shift()();
/******/ 		}
/******/
/******/ 		// add entry modules from loaded chunk to deferred list
/******/ 		deferredModules.push.apply(deferredModules, executeModules || []);
/******/
/******/ 		// run deferred modules when all chunks ready
/******/ 		return checkDeferredModules();
/******/ 	};
/******/ 	function checkDeferredModules() {
/******/ 		var result;
/******/ 		for(var i = 0; i < deferredModules.length; i++) {
/******/ 			var deferredModule = deferredModules[i];
/******/ 			var fulfilled = true;
/******/ 			for(var j = 1; j < deferredModule.length; j++) {
/******/ 				var depId = deferredModule[j];
/******/ 				if(installedChunks[depId] !== 0) fulfilled = false;
/******/ 			}
/******/ 			if(fulfilled) {
/******/ 				deferredModules.splice(i--, 1);
/******/ 				result = __webpack_require__(__webpack_require__.s = deferredModule[0]);
/******/ 			}
/******/ 		}
/******/
/******/ 		return result;
/******/ 	}
/******/
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// object to store loaded and loading chunks
/******/ 	// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 	// Promise = chunk loading, 0 = chunk loaded
/******/ 	var installedChunks = {
/******/ 		"certificate-builder": 0
/******/ 	};
/******/
/******/ 	var deferredModules = [];
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	var jsonpArray = window["webpackJsonp"] = window["webpackJsonp"] || [];
/******/ 	var oldJsonpFunction = jsonpArray.push.bind(jsonpArray);
/******/ 	jsonpArray.push = webpackJsonpCallback;
/******/ 	jsonpArray = jsonpArray.slice();
/******/ 	for(var i = 0; i < jsonpArray.length; i++) webpackJsonpCallback(jsonpArray[i]);
/******/ 	var parentJsonpFunction = oldJsonpFunction;
/******/
/******/
/******/ 	// add entry module to deferred list
/******/ 	deferredModules.push(["./blocks/index.js","style-certificate-builder"]);
/******/ 	// run deferred modules when ready
/******/ 	return checkDeferredModules();
/******/ })
/************************************************************************/
/******/ ({

/***/ "./blocks/certificate-builder/editor.scss":
/*!************************************************!*\
  !*** ./blocks/certificate-builder/editor.scss ***!
  \************************************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports, __webpack_require__) {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./blocks/certificate-builder/editor.scss?");

/***/ }),

/***/ "./blocks/certificate-builder/index.js":
/*!*********************************************************!*\
  !*** ./blocks/certificate-builder/index.js + 2 modules ***!
  \*********************************************************/
/*! no exports provided */
/*! ModuleConcatenation bailout: Cannot concat with ./blocks/helper/font.js */
/*! ModuleConcatenation bailout: Cannot concat with external ["wp","blockEditor"] (<- Module is not an ECMAScript module) */
/*! ModuleConcatenation bailout: Cannot concat with external ["wp","blocks"] (<- Module is not an ECMAScript module) */
/*! ModuleConcatenation bailout: Cannot concat with external ["wp","components"] (<- Module is not an ECMAScript module) */
/*! ModuleConcatenation bailout: Cannot concat with external ["wp","element"] (<- Module is not an ECMAScript module) */
/*! ModuleConcatenation bailout: Cannot concat with external ["wp","i18n"] (<- Module is not an ECMAScript module) */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("// ESM COMPAT FLAG\n__webpack_require__.r(__webpack_exports__);\n\n// EXTERNAL MODULE: external [\"wp\",\"blocks\"]\nvar external_wp_blocks_ = __webpack_require__(\"@wordpress/blocks\");\n\n// EXTERNAL MODULE: external [\"wp\",\"i18n\"]\nvar external_wp_i18n_ = __webpack_require__(\"@wordpress/i18n\");\n\n// EXTERNAL MODULE: ./blocks/certificate-builder/style.scss\nvar style = __webpack_require__(\"./blocks/certificate-builder/style.scss\");\n\n// EXTERNAL MODULE: external [\"wp\",\"element\"]\nvar external_wp_element_ = __webpack_require__(\"@wordpress/element\");\n\n// EXTERNAL MODULE: external [\"wp\",\"blockEditor\"]\nvar external_wp_blockEditor_ = __webpack_require__(\"@wordpress/block-editor\");\n\n// EXTERNAL MODULE: external [\"wp\",\"components\"]\nvar external_wp_components_ = __webpack_require__(\"@wordpress/components\");\n\n// EXTERNAL MODULE: ./blocks/helper/font.js\nvar helper_font = __webpack_require__(\"./blocks/helper/font.js\");\n\n// EXTERNAL MODULE: ./blocks/certificate-builder/editor.scss\nvar editor = __webpack_require__(\"./blocks/certificate-builder/editor.scss\");\n\n// CONCATENATED MODULE: ./blocks/certificate-builder/edit.js\n\n\n/**\r\n * Retrieves the translation of text.\r\n *\r\n * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/\r\n */\n\n/**\r\n * React hook that is used to mark the block wrapper element.\r\n * It provides all the necessary props like the class name.\r\n *\r\n * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps\r\n */\n\n\n\n\n/**\r\n * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.\r\n * Those files can contain any CSS code that gets applied to the editor.\r\n *\r\n * @see https://www.npmjs.com/package/@wordpress/scripts#using-css\r\n */\n\n\n/**\r\n * The edit function describes the structure of your block in the context of the\r\n * editor. This represents what the editor will render when the block is used.\r\n *\r\n * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit\r\n *\r\n * @return {JSX.Element} Element to render.\r\n */\n\nfunction Edit(props) {\n  var attributes = props.attributes,\n      setAttributes = props.setAttributes,\n      className = props.className;\n  var _attributes$id = attributes.id,\n      id = _attributes$id === void 0 ? 0 : _attributes$id,\n      _attributes$backgroun = attributes.backgroundImage,\n      backgroundImage = _attributes$backgroun === void 0 ? '' : _attributes$backgroun,\n      _attributes$pageSize = attributes.pageSize,\n      pageSize = _attributes$pageSize === void 0 ? 'LETTER' : _attributes$pageSize,\n      _attributes$pageOrien = attributes.pageOrientation,\n      pageOrientation = _attributes$pageOrien === void 0 ? 'L' : _attributes$pageOrien,\n      _attributes$font = attributes.font,\n      font = _attributes$font === void 0 ? '' : _attributes$font,\n      _attributes$useFont = attributes.useFont,\n      useFont = _attributes$useFont === void 0 ? false : _attributes$useFont,\n      _attributes$pageHeigh = attributes.pageHeight,\n      pageHeight = _attributes$pageHeigh === void 0 ? 0 : _attributes$pageHeigh,\n      _attributes$pageWidth = attributes.pageWidth,\n      pageWidth = _attributes$pageWidth === void 0 ? 0 : _attributes$pageWidth,\n      _attributes$container = attributes.containerWidth,\n      containerWidth = _attributes$container === void 0 ? 70 : _attributes$container,\n      _attributes$spacing = attributes.spacing,\n      spacing = _attributes$spacing === void 0 ? 1 : _attributes$spacing,\n      _attributes$rtl = attributes.rtl,\n      rtl = _attributes$rtl === void 0 ? false : _attributes$rtl,\n      _attributes$viewPort = attributes.viewPort,\n      viewPort = _attributes$viewPort === void 0 ? true : _attributes$viewPort;\n  var hasBackground = id !== 0; //update post id\n\n  var onSelectMedia = function onSelectMedia(imageObject) {\n    if (imageObject.type === \"image\") {\n      setAttributes({\n        id: imageObject.id,\n        backgroundImage: imageObject.url\n      });\n      setTimeout(function () {\n        var height = jQuery('#certificate-builder-blocks').height();\n        var width = jQuery('#certificate-builder-blocks').width();\n        setAttributes({\n          pageHeight: height,\n          pageWidth: width\n        });\n      });\n    }\n  };\n\n  var ALLOWED_MEDIA_TYPES = ['image'];\n  var PAGE_SIZES = [{\n    label: Object(external_wp_i18n_[\"__\"])('Letter/USLetter (default)', 'learndash-certificate-builder'),\n    value: 'LETTER'\n  }, {\n    label: Object(external_wp_i18n_[\"__\"])('A4', 'learndash-certificate-builder'),\n    value: 'A4'\n  }];\n  var PAGE_ORIENTATION = [{\n    label: Object(external_wp_i18n_[\"__\"])('Landscape (default)', 'learndash-certificate-builder'),\n    value: 'L'\n  }, {\n    label: Object(external_wp_i18n_[\"__\"])('Portrait', 'learndash-certificate-builder'),\n    value: 'P'\n  }];\n  var styles = {};\n\n  if ('' !== backgroundImage) {\n    styles.position = 'relative';\n  }\n\n  var useCoreFont = function useCoreFont() {\n    return \"#certificate-builder-inner-blocks, #certificate-builder-inner-blocks * {\\n\\t\\tfont-family: \\\"freeserif\\\", serif;\\n\\t}\";\n  };\n\n  var info = helper_font[\"getActiveFont\"](Object.entries(certificate_builder.fonts), font);\n\n  if (font === '' || font === null) {\n    setAttributes({\n      font: info.currFont.key\n    });\n  }\n\n  var fontDefine = helper_font[\"buildFontStyle\"](certificate_builder.font_url, certificate_builder.fonts);\n  var fontStyle = '';\n\n  if (useFont === false) {\n    fontStyle = useCoreFont();\n  } else {\n    fontStyle = \"\\n\\t#certificate-builder-inner-blocks, #certificate-builder-inner-blocks * {\\n\\t\\tfont-family: \\\"\".concat(info.currFont.key, \"\\\", \\\"freeserif\\\", serif;\\n\\t}}\\n\\t\");\n  }\n\n  var MyToggleControl = Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"ToggleControl\"], {\n    label: Object(external_wp_i18n_[\"__\"])(\"Use custom font\", 'learndash-certificate-builder'),\n    checked: useFont,\n    onChange: function onChange(value) {\n      setAttributes({\n        useFont: value\n      });\n    }\n  });\n  var controls = Object(external_wp_element_[\"createElement\"])(external_wp_element_[\"Fragment\"], null, Object(external_wp_element_[\"createElement\"])(external_wp_blockEditor_[\"BlockControls\"], null, hasBackground && Object(external_wp_element_[\"createElement\"])(external_wp_element_[\"Fragment\"], null, Object(external_wp_element_[\"createElement\"])(external_wp_blockEditor_[\"MediaReplaceFlow\"], {\n    mediaId: id,\n    mediaURL: backgroundImage,\n    allowedTypes: ALLOWED_MEDIA_TYPES,\n    accept: \"image/jpeg\",\n    onSelect: onSelectMedia\n  }))), Object(external_wp_element_[\"createElement\"])(external_wp_blockEditor_[\"InspectorControls\"], null, hasBackground && Object(external_wp_element_[\"createElement\"])(external_wp_element_[\"Fragment\"], null, Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"PanelBody\"], {\n    title: Object(external_wp_i18n_[\"__\"])('Spacing', 'learndash-certificate-builder')\n  }, Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"RangeControl\"], {\n    label: Object(external_wp_i18n_[\"__\"])(\"Container size (%)\", 'learndash-certificate-builder'),\n    value: containerWidth,\n    min: 50,\n    max: 100,\n    onChange: function onChange(newSize) {\n      setAttributes({\n        containerWidth: newSize\n      });\n    }\n  }), Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"RangeControl\"], {\n    label: Object(external_wp_i18n_[\"__\"])(\"Margin bottom (rem)\", 'learndash-certificate-builder'),\n    value: spacing,\n    min: 0,\n    max: 10,\n    step: 0.1,\n    onChange: function onChange(value) {\n      setAttributes({\n        spacing: value\n      });\n    }\n  })), Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"PanelBody\"], {\n    title: Object(external_wp_i18n_[\"__\"])('Options', 'learndash-certificate-builder')\n  }, Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"SelectControl\"], {\n    label: Object(external_wp_i18n_[\"__\"])(\"PDF Page Size\", 'learndash-certificate-builder'),\n    value: pageSize,\n    options: PAGE_SIZES,\n    onChange: function onChange(size) {\n      setAttributes({\n        pageSize: size\n      });\n    }\n  }), Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"SelectControl\"], {\n    label: Object(external_wp_i18n_[\"__\"])(\"PDF Page Orientation\", 'learndash-certificate-builder'),\n    value: pageOrientation,\n    options: PAGE_ORIENTATION,\n    onChange: function onChange(o) {\n      setAttributes({\n        pageOrientation: o\n      });\n    }\n  }), Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"ToggleControl\"], {\n    label: Object(external_wp_i18n_[\"__\"])(\"Fixed Viewport\", 'learndash-certificate-builder'),\n    checked: viewPort,\n    onChange: function onChange(value) {\n      setAttributes({\n        viewPort: value\n      });\n    }\n  }), Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"ToggleControl\"], {\n    label: Object(external_wp_i18n_[\"__\"])(\"RTL\", 'learndash-certificate-builder'),\n    checked: rtl,\n    onChange: function onChange(value) {\n      setAttributes({\n        rtl: value\n      });\n    }\n  })), Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"PanelBody\"], {\n    title: Object(external_wp_i18n_[\"__\"])('Fonts', 'learndash-certificate-builder')\n  }, MyToggleControl, useFont && Object(external_wp_element_[\"createElement\"])(external_wp_components_[\"SelectControl\"], {\n    label: Object(external_wp_i18n_[\"__\"])(\"Font family\", 'learndash-certificate-builder'),\n    value: font,\n    options: info.list,\n    onChange: function onChange(o) {\n      setAttributes({\n        font: o\n      });\n    }\n  })))));\n\n  if (!hasBackground) {\n    return Object(external_wp_element_[\"createElement\"])(\"div\", null, controls, Object(external_wp_element_[\"createElement\"])(\"div\", null, Object(external_wp_element_[\"createElement\"])(external_wp_blockEditor_[\"MediaPlaceholder\"], {\n      allowedTypes: ALLOWED_MEDIA_TYPES,\n      multiple: false,\n      labels: {\n        title: Object(external_wp_i18n_[\"__\"])('Certificate Background', 'learndash-certificate-builder')\n      },\n      onSelect: onSelectMedia,\n      accept: \"image/jpeg\"\n    })));\n  }\n\n  var ALLOWED_BLOCKS = ['core/columns', 'core/paragraph', 'core/heading', 'core/spacer', 'core/shortcode', 'core/image', 'core/quote', 'core/list', 'core/separator', 'learndash/ld-courseinfo', 'learndash/ld-usermeta', 'learndash/ld-groupinfo', 'learndash/ld-quizinfo'];\n  var spaceStyle = \"#certificate-builder-inner-blocks .wp-block:not(.wp-block-column) {\\n      margin-bottom: \".concat(spacing, \"rem !important;\\n    }\\n    #certificate-builder-inner-blocks .wp-block:last-child {\\n        margin-bottom:0 !important;\\n    }\\n    \");\n  var sizeRatio = {\n    'LETTER_L': [1056, 816],\n    'LETTER_P': [816, 1056],\n    'A4_L': [1122, 793],\n    'A4_P': [793, 1122]\n  }; //we use full width and calculate height\n\n  var key = pageSize + '_' + pageOrientation;\n  var size = sizeRatio[key];\n  var width = size[0];\n  var height = size[1];\n  styles.width = width + 'px';\n  styles.height = height + 'px';\n\n  if (viewPort === false) {\n    styles.width = '100%';\n    styles.height = 'auto';\n  }\n\n  var rtlStyle = '';\n\n  if (rtl) {\n    rtlStyle = \"#certificate-builder-inner-blocks{\\n\\t\\t\\tdirection: rtl;\\n\\t\\t}\";\n  }\n\n  return Object(external_wp_element_[\"createElement\"])(\"div\", null, Object(external_wp_element_[\"createElement\"])(\"style\", null, spaceStyle, fontDefine, fontStyle, rtlStyle), controls, Object(external_wp_element_[\"createElement\"])(\"div\", {\n    id: \"certificate-builder-blocks\",\n    className: className\n  }, Object(external_wp_element_[\"createElement\"])(\"div\", {\n    style: styles\n  }, Object(external_wp_element_[\"createElement\"])(\"img\", {\n    src: backgroundImage,\n    style: {\n      opacity: 1,\n      width: '100%',\n      height: 'auto'\n    }\n  }), Object(external_wp_element_[\"createElement\"])(\"div\", {\n    id: 'certificate-builder-inner-blocks',\n    style: {\n      position: 'absolute',\n      top: 0,\n      width: '100%',\n      height: 'auto'\n    }\n  }, Object(external_wp_element_[\"createElement\"])(\"div\", {\n    style: {\n      width: containerWidth + '%',\n      margin: 'auto'\n    }\n  }, Object(external_wp_element_[\"createElement\"])(external_wp_blockEditor_[\"InnerBlocks\"], {\n    templateLock: false,\n    allowedBlocks: ALLOWED_BLOCKS\n  }))))));\n}\n// CONCATENATED MODULE: ./blocks/certificate-builder/save.js\n\n\n/**\n * Retrieves the translation of text.\n *\n * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/\n */\n\n/**\n * The save function defines the way in which the different attributes should\n * be combined into the final markup, which is then serialized by the block\n * editor into `post_content`.\n *\n * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save\n *\n * @return {WPElement} Element to render.\n */\n\nfunction save(_ref) {\n  var attributes = _ref.attributes,\n      setAttributes = _ref.setAttributes,\n      className = _ref.className;\n  return Object(external_wp_element_[\"createElement\"])(\"div\", null, Object(external_wp_element_[\"createElement\"])(external_wp_blockEditor_[\"InnerBlocks\"].Content, null));\n}\n// CONCATENATED MODULE: ./blocks/certificate-builder/index.js\n/**\r\n * Registers a new block provided a unique name and an object defining its behavior.\r\n *\r\n * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block\r\n */\n\n/**\r\n * Retrieves the translation of text.\r\n *\r\n * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/\r\n */\n\n\n/**\r\n * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.\r\n * All files containing `style` keyword are bundled together. The code used\r\n * gets applied both to the front of your site and to the editor.\r\n *\r\n * @see https://www.npmjs.com/package/@wordpress/scripts#using-css\r\n */\n\n\n/**\r\n * Internal dependencies\r\n */\n\n\n\n/**\r\n * Every block starts by registering a new block type definition.\r\n *\r\n * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block\r\n */\n\nObject(external_wp_blocks_[\"registerBlockType\"])('learndash/ld-certificate-builder', {\n  /**\r\n   * This is the display title for your block, which can be translated with `i18n` functions.\r\n   * The block inserter will show this name.\r\n   */\n  title: Object(external_wp_i18n_[\"__\"])('LearnDash Certificate Builder', 'learndash-certificate-builder'),\n\n  /**\r\n   * This is a short description for your block, can be translated with `i18n` functions.\r\n   * It will be shown in the Block Tab in the Settings Sidebar.\r\n   */\n  description: Object(external_wp_i18n_[\"__\"])('LearnDash certificate builder', 'learnDash-certificate-builder'),\n\n  /**\r\n   * Blocks are grouped into categories to help users browse and discover them.\r\n   * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.\r\n   */\n  category: 'learndash-blocks',\n\n  /**\r\n   * An icon property should be specified to make it easier to identify a block.\r\n   * These can be any of WordPress’ Dashicons, or a custom svg element.\r\n   */\n  icon: 'welcome-learn-more',\n\n  /**\r\n   * Optional block extended support features.\r\n   */\n  supports: {\n    // Removes support for an HTML mode.\n    html: false,\n    align: ['full']\n  },\n  multiple: false,\n  attributes: {\n    id: {\n      type: 'int',\n      default: 0\n    },\n    post_id: {\n      type: 'int',\n      default: 0\n    },\n    backgroundImage: {\n      type: 'string',\n      default: ''\n    },\n    font: {\n      type: 'string',\n      default: ''\n    },\n    useFont: {\n      type: 'boolean',\n      default: false\n    },\n    pageSize: {\n      type: 'string',\n      default: 'LETTER'\n    },\n    pageOrientation: {\n      type: 'string',\n      default: 'L'\n    },\n    align: {\n      type: 'string',\n      default: 'full'\n    },\n    pageHeight: {\n      type: 'int',\n      default: 0\n    },\n    pageWidth: {\n      type: 'int',\n      default: 0\n    },\n    containerWidth: {\n      type: 'int',\n      default: 70\n    },\n    spacing: {\n      type: 'number',\n      default: 1\n    },\n    rtl: {\n      type: 'boolean',\n      default: false\n    },\n    viewPort: {\n      type: 'boolean',\n      default: true\n    }\n  },\n\n  /**\r\n   * @see ./edit.js\r\n   */\n  edit: Edit,\n\n  /**\r\n   * @see ./save.js\r\n   */\n  save: save\n});\n\n//# sourceURL=webpack:///./blocks/certificate-builder/index.js_+_2_modules?");

/***/ }),

/***/ "./blocks/helper/font.js":
/*!*******************************!*\
  !*** ./blocks/helper/font.js ***!
  \*******************************/
/*! exports provided: getActiveFont, buildFontStyle */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"getActiveFont\", function() { return getActiveFont; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"buildFontStyle\", function() { return buildFontStyle; });\n/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ \"./node_modules/@babel/runtime/helpers/slicedToArray.js\");\n/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__);\n\n\n/**\r\n * Build the selector\r\n *\r\n * @param arr\r\n * @param font\r\n * @returns {{list: [], currFont: any}}\r\n */\nfunction getActiveFont(arr, font) {\n  var list = [];\n  var currFont = undefined;\n\n  if (font === '') {\n    font = arr[0][0];\n  }\n\n  arr.forEach(function (v, i) {\n    var key = v[0];\n    var info = v[1];\n    list.push({\n      label: info.name,\n      value: key\n    });\n\n    if (key === font) {\n      currFont = info;\n      currFont.key = key;\n    }\n  }); // the currentfont is empty, this can happen because\n\n  if (currFont === undefined) {\n    currFont = arr[0][0];\n  }\n\n  return {\n    list: list,\n    currFont: currFont\n  };\n}\n/**\r\n * Build the font stack styles\r\n *\r\n * @param baseUrl\r\n * @param fonts\r\n * @returns {string}\r\n */\n\nfunction buildFontStyle(baseUrl, fonts) {\n  var fontStyle = '';\n\n  for (var _i = 0, _Object$entries = Object.entries(fonts); _i < _Object$entries.length; _i++) {\n    var _Object$entries$_i = _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0___default()(_Object$entries[_i], 2),\n        key = _Object$entries$_i[0],\n        v = _Object$entries$_i[1];\n\n    var fontUrl = void 0;\n\n    if (v.custom === true) {\n      fontUrl = v['R'];\n    } else {\n      fontUrl = baseUrl + '/' + v['R'];\n    }\n\n    fontStyle += \"\\n\\t\\t@font-face {\\n\\t\\t\\tfont-family: \\\"\".concat(key, \"\\\";\\n\\t\\t\\tsrc: url(\\\"\").concat(fontUrl, \"\\\") format('truetype');\\n\\t\\t\\tfont-weight: 400;\\n\\t\\t\\tfont-style: normal\\n\\t\\t}\\n\\t\");\n\n    if (v['B'] !== undefined) {\n      var fontUrl_B = baseUrl + '/' + v['B'];\n\n      if (v.custom === true) {\n        fontUrl_B = v['B'];\n      }\n\n      fontStyle += \"\\n\\t\\t@font-face {\\n\\t\\t\\tfont-family: \\\"\".concat(key, \"\\\";\\n\\t\\t\\tsrc: url(\\\"\").concat(fontUrl_B, \"\\\") format('truetype');\\n\\t\\t\\tfont-weight: bold;\\n\\t\\t\\tfont-style: normal\\n\\t\\t}\\n\\t\\t\");\n    }\n\n    if (v['I'] !== undefined) {\n      var fontUrl_I = baseUrl + '/' + v['I'];\n\n      if (v.custom === true) {\n        fontUrl_I = v['I'];\n      }\n\n      fontStyle += \"\\n\\t\\t@font-face {\\n\\t\\t\\tfont-family: \\\"\".concat(key, \"\\\";\\n\\t\\t\\tsrc: url(\\\"\").concat(fontUrl_I, \"\\\") format('truetype');\\n\\t\\t\\tfont-weight: 400;\\n\\t\\t\\tfont-style: italic\\n\\t\\t}\\n\\t\\t\");\n    }\n\n    if (v['BI'] !== undefined) {\n      var fontUrl_BI = baseUrl + '/' + v['BI'];\n\n      if (v.custom === true) {\n        fontUrl_BI = v['BI'];\n      }\n\n      fontStyle += \"\\n\\t\\t@font-face {\\n\\t\\t\\tfont-family: \\\"\".concat(key, \"\\\";\\n\\t\\t\\tsrc: url(\\\"\").concat(fontUrl_BI, \"\\\") format('truetype');\\n\\t\\t\\tfont-weight: bold;\\n\\t\\t\\tfont-style: italic\\n\\t\\t}\\n\\t\\t\");\n    }\n  }\n\n  return fontStyle;\n}\n\n//# sourceURL=webpack:///./blocks/helper/font.js?");

/***/ }),

/***/ "./blocks/index.js":
/*!*************************!*\
  !*** ./blocks/index.js ***!
  \*************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports, __webpack_require__) {

eval("__webpack_require__(/*! ./certificate-builder/index */ \"./blocks/certificate-builder/index.js\");\n\n//# sourceURL=webpack:///./blocks/index.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/arrayLikeToArray.js":
/*!*****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/arrayLikeToArray.js ***!
  \*****************************************************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports) {

eval("function _arrayLikeToArray(arr, len) {\n  if (len == null || len > arr.length) len = arr.length;\n\n  for (var i = 0, arr2 = new Array(len); i < len; i++) {\n    arr2[i] = arr[i];\n  }\n\n  return arr2;\n}\n\nmodule.exports = _arrayLikeToArray;\n\n//# sourceURL=webpack:///./node_modules/@babel/runtime/helpers/arrayLikeToArray.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/arrayWithHoles.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/arrayWithHoles.js ***!
  \***************************************************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports) {

eval("function _arrayWithHoles(arr) {\n  if (Array.isArray(arr)) return arr;\n}\n\nmodule.exports = _arrayWithHoles;\n\n//# sourceURL=webpack:///./node_modules/@babel/runtime/helpers/arrayWithHoles.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/iterableToArrayLimit.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/iterableToArrayLimit.js ***!
  \*********************************************************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports) {

eval("function _iterableToArrayLimit(arr, i) {\n  if (typeof Symbol === \"undefined\" || !(Symbol.iterator in Object(arr))) return;\n  var _arr = [];\n  var _n = true;\n  var _d = false;\n  var _e = undefined;\n\n  try {\n    for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {\n      _arr.push(_s.value);\n\n      if (i && _arr.length === i) break;\n    }\n  } catch (err) {\n    _d = true;\n    _e = err;\n  } finally {\n    try {\n      if (!_n && _i[\"return\"] != null) _i[\"return\"]();\n    } finally {\n      if (_d) throw _e;\n    }\n  }\n\n  return _arr;\n}\n\nmodule.exports = _iterableToArrayLimit;\n\n//# sourceURL=webpack:///./node_modules/@babel/runtime/helpers/iterableToArrayLimit.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/nonIterableRest.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/nonIterableRest.js ***!
  \****************************************************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports) {

eval("function _nonIterableRest() {\n  throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\");\n}\n\nmodule.exports = _nonIterableRest;\n\n//# sourceURL=webpack:///./node_modules/@babel/runtime/helpers/nonIterableRest.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/slicedToArray.js":
/*!**************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/slicedToArray.js ***!
  \**************************************************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports, __webpack_require__) {

eval("var arrayWithHoles = __webpack_require__(/*! ./arrayWithHoles */ \"./node_modules/@babel/runtime/helpers/arrayWithHoles.js\");\n\nvar iterableToArrayLimit = __webpack_require__(/*! ./iterableToArrayLimit */ \"./node_modules/@babel/runtime/helpers/iterableToArrayLimit.js\");\n\nvar unsupportedIterableToArray = __webpack_require__(/*! ./unsupportedIterableToArray */ \"./node_modules/@babel/runtime/helpers/unsupportedIterableToArray.js\");\n\nvar nonIterableRest = __webpack_require__(/*! ./nonIterableRest */ \"./node_modules/@babel/runtime/helpers/nonIterableRest.js\");\n\nfunction _slicedToArray(arr, i) {\n  return arrayWithHoles(arr) || iterableToArrayLimit(arr, i) || unsupportedIterableToArray(arr, i) || nonIterableRest();\n}\n\nmodule.exports = _slicedToArray;\n\n//# sourceURL=webpack:///./node_modules/@babel/runtime/helpers/slicedToArray.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/unsupportedIterableToArray.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/unsupportedIterableToArray.js ***!
  \***************************************************************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports, __webpack_require__) {

eval("var arrayLikeToArray = __webpack_require__(/*! ./arrayLikeToArray */ \"./node_modules/@babel/runtime/helpers/arrayLikeToArray.js\");\n\nfunction _unsupportedIterableToArray(o, minLen) {\n  if (!o) return;\n  if (typeof o === \"string\") return arrayLikeToArray(o, minLen);\n  var n = Object.prototype.toString.call(o).slice(8, -1);\n  if (n === \"Object\" && o.constructor) n = o.constructor.name;\n  if (n === \"Map\" || n === \"Set\") return Array.from(o);\n  if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return arrayLikeToArray(o, minLen);\n}\n\nmodule.exports = _unsupportedIterableToArray;\n\n//# sourceURL=webpack:///./node_modules/@babel/runtime/helpers/unsupportedIterableToArray.js?");

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports) {

eval("(function() { module.exports = window[\"wp\"][\"blockEditor\"]; }());\n\n//# sourceURL=webpack:///external_%5B%22wp%22,%22blockEditor%22%5D?");

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports) {

eval("(function() { module.exports = window[\"wp\"][\"blocks\"]; }());\n\n//# sourceURL=webpack:///external_%5B%22wp%22,%22blocks%22%5D?");

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports) {

eval("(function() { module.exports = window[\"wp\"][\"components\"]; }());\n\n//# sourceURL=webpack:///external_%5B%22wp%22,%22components%22%5D?");

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports) {

eval("(function() { module.exports = window[\"wp\"][\"element\"]; }());\n\n//# sourceURL=webpack:///external_%5B%22wp%22,%22element%22%5D?");

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/*! no static exports found */
/*! ModuleConcatenation bailout: Module is not an ECMAScript module */
/***/ (function(module, exports) {

eval("(function() { module.exports = window[\"wp\"][\"i18n\"]; }());\n\n//# sourceURL=webpack:///external_%5B%22wp%22,%22i18n%22%5D?");

/***/ })

/******/ });