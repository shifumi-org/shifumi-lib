"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

/* global window, exports, define */
!function () {
  'use strict';

  var re = {
    not_string: /[^s]/,
    not_bool: /[^t]/,
    not_type: /[^T]/,
    not_primitive: /[^v]/,
    number: /[diefg]/,
    numeric_arg: /[bcdiefguxX]/,
    json: /[j]/,
    not_json: /[^j]/,
    text: /^[^\x25]+/,
    modulo: /^\x25{2}/,
    placeholder: /^\x25(?:([1-9]\d*)\$|\(([^)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-gijostTuvxX])/,
    key: /^([a-z_][a-z_\d]*)/i,
    key_access: /^\.([a-z_][a-z_\d]*)/i,
    index_access: /^\[(\d+)\]/,
    sign: /^[+-]/
  };

  function sprintf(key) {
    // `arguments` is not an array, but should be fine for this call
    return sprintf_format(sprintf_parse(key), arguments);
  }

  function vsprintf(fmt, argv) {
    return sprintf.apply(null, [fmt].concat(argv || []));
  }

  function sprintf_format(parse_tree, argv) {
    var cursor = 1,
        tree_length = parse_tree.length,
        arg,
        output = '',
        i,
        k,
        ph,
        pad,
        pad_character,
        pad_length,
        is_positive,
        sign;

    for (i = 0; i < tree_length; i++) {
      if (typeof parse_tree[i] === 'string') {
        output += parse_tree[i];
      } else if (_typeof(parse_tree[i]) === 'object') {
        ph = parse_tree[i]; // convenience purposes only

        if (ph.keys) {
          // keyword argument
          arg = argv[cursor];

          for (k = 0; k < ph.keys.length; k++) {
            if (arg == undefined) {
              throw new Error(sprintf('[sprintf] Cannot access property "%s" of undefined value "%s"', ph.keys[k], ph.keys[k - 1]));
            }

            arg = arg[ph.keys[k]];
          }
        } else if (ph.param_no) {
          // positional argument (explicit)
          arg = argv[ph.param_no];
        } else {
          // positional argument (implicit)
          arg = argv[cursor++];
        }

        if (re.not_type.test(ph.type) && re.not_primitive.test(ph.type) && arg instanceof Function) {
          arg = arg();
        }

        if (re.numeric_arg.test(ph.type) && typeof arg !== 'number' && isNaN(arg)) {
          throw new TypeError(sprintf('[sprintf] expecting number but found %T', arg));
        }

        if (re.number.test(ph.type)) {
          is_positive = arg >= 0;
        }

        switch (ph.type) {
          case 'b':
            arg = parseInt(arg, 10).toString(2);
            break;

          case 'c':
            arg = String.fromCharCode(parseInt(arg, 10));
            break;

          case 'd':
          case 'i':
            arg = parseInt(arg, 10);
            break;

          case 'j':
            arg = JSON.stringify(arg, null, ph.width ? parseInt(ph.width) : 0);
            break;

          case 'e':
            arg = ph.precision ? parseFloat(arg).toExponential(ph.precision) : parseFloat(arg).toExponential();
            break;

          case 'f':
            arg = ph.precision ? parseFloat(arg).toFixed(ph.precision) : parseFloat(arg);
            break;

          case 'g':
            arg = ph.precision ? String(Number(arg.toPrecision(ph.precision))) : parseFloat(arg);
            break;

          case 'o':
            arg = (parseInt(arg, 10) >>> 0).toString(8);
            break;

          case 's':
            arg = String(arg);
            arg = ph.precision ? arg.substring(0, ph.precision) : arg;
            break;

          case 't':
            arg = String(!!arg);
            arg = ph.precision ? arg.substring(0, ph.precision) : arg;
            break;

          case 'T':
            arg = Object.prototype.toString.call(arg).slice(8, -1).toLowerCase();
            arg = ph.precision ? arg.substring(0, ph.precision) : arg;
            break;

          case 'u':
            arg = parseInt(arg, 10) >>> 0;
            break;

          case 'v':
            arg = arg.valueOf();
            arg = ph.precision ? arg.substring(0, ph.precision) : arg;
            break;

          case 'x':
            arg = (parseInt(arg, 10) >>> 0).toString(16);
            break;

          case 'X':
            arg = (parseInt(arg, 10) >>> 0).toString(16).toUpperCase();
            break;
        }

        if (re.json.test(ph.type)) {
          output += arg;
        } else {
          if (re.number.test(ph.type) && (!is_positive || ph.sign)) {
            sign = is_positive ? '+' : '-';
            arg = arg.toString().replace(re.sign, '');
          } else {
            sign = '';
          }

          pad_character = ph.pad_char ? ph.pad_char === '0' ? '0' : ph.pad_char.charAt(1) : ' ';
          pad_length = ph.width - (sign + arg).length;
          pad = ph.width ? pad_length > 0 ? pad_character.repeat(pad_length) : '' : '';
          output += ph.align ? sign + arg + pad : pad_character === '0' ? sign + pad + arg : pad + sign + arg;
        }
      }
    }

    return output;
  }

  var sprintf_cache = Object.create(null);

  function sprintf_parse(fmt) {
    if (sprintf_cache[fmt]) {
      return sprintf_cache[fmt];
    }

    var _fmt = fmt,
        match,
        parse_tree = [],
        arg_names = 0;

    while (_fmt) {
      if ((match = re.text.exec(_fmt)) !== null) {
        parse_tree.push(match[0]);
      } else if ((match = re.modulo.exec(_fmt)) !== null) {
        parse_tree.push('%');
      } else if ((match = re.placeholder.exec(_fmt)) !== null) {
        if (match[2]) {
          arg_names |= 1;
          var field_list = [],
              replacement_field = match[2],
              field_match = [];

          if ((field_match = re.key.exec(replacement_field)) !== null) {
            field_list.push(field_match[1]);

            while ((replacement_field = replacement_field.substring(field_match[0].length)) !== '') {
              if ((field_match = re.key_access.exec(replacement_field)) !== null) {
                field_list.push(field_match[1]);
              } else if ((field_match = re.index_access.exec(replacement_field)) !== null) {
                field_list.push(field_match[1]);
              } else {
                throw new SyntaxError('[sprintf] failed to parse named argument key');
              }
            }
          } else {
            throw new SyntaxError('[sprintf] failed to parse named argument key');
          }

          match[2] = field_list;
        } else {
          arg_names |= 2;
        }

        if (arg_names === 3) {
          throw new Error('[sprintf] mixing positional and named placeholders is not (yet) supported');
        }

        parse_tree.push({
          placeholder: match[0],
          param_no: match[1],
          keys: match[2],
          sign: match[3],
          pad_char: match[4],
          align: match[5],
          width: match[6],
          precision: match[7],
          type: match[8]
        });
      } else {
        throw new SyntaxError('[sprintf] unexpected placeholder');
      }

      _fmt = _fmt.substring(match[0].length);
    }

    return sprintf_cache[fmt] = parse_tree;
  }
  /**
   * export to either browser or node.js
   */

  /* eslint-disable quote-props */


  if (typeof exports !== 'undefined') {
    exports['sprintf'] = sprintf;
    exports['vsprintf'] = vsprintf;
  }

  if (typeof window !== 'undefined') {
    window['sprintf'] = sprintf;
    window['vsprintf'] = vsprintf;

    if (typeof define === 'function' && define['amd']) {
      define(function () {
        return {
          'sprintf': sprintf,
          'vsprintf': vsprintf
        };
      });
    }
  }
  /* eslint-enable quote-props */

}(); // eslint-disable-line
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

/**
 * Find whether a variable is a string
 *
 * @param   {mixed}     variable - The variable being evaluated
 * @return  {boolean}   TRUE if the variable is an string
 */
function isString(string) {
  return typeof string === 'string';
}
/**
 * Find whether a variable is an object
 *
 * @param   {mixed}     variable - The variable being evaluated
 * @return  {boolean}   TRUE if the variable is an object
 */


function isObject(variable) {
  return variable !== null && _typeof(variable) === 'object';
}
/**
 * Remove all spaces from a string
 *
 * @param   {string}    String
 * @return  {string}    String without spaces
 */


function removeSpaces(string) {
  return string.replace(/\s/g, '');
}
/**
 * Check if a value exists in an array
 * If the needle is an array then it will check if all his elements exists in the haystack
 *
 * @param   {mixed}     needle - The searched value. The comparison between values is strict. If the needle is an array then it will check if all his elements exists in the haystack
 * @param   {array}     haystack - An array through which to search.
 *
 * @return  {boolean} TRUE if needle is found in the array or if needle is an array, TRUE if all the elements from the needle are in haystack
 */


function inArray(needle, haystack) {
  var response = false;

  if (Array.isArray(needle)) {
    // Check that all the elements from the array "value" are in the array "array"
    response = arrayDifference(needle, haystack).length == 0;
  } else {
    // Check if one element is in an array
    response = $.inArray(needle, haystack) !== -1;
  }

  return response;
}
/**
 * Determine if a variable is set and is not NULL
 *
 * @param  {mixed}      variable - The variable being evaluated
 * @return {boolean}    TRUE if the variable is defined
 */


function isDefined(variable) {
  // Returns true if the variable is undefined
  return typeof variable !== 'undefined' && variable !== null;
}
/**
 * Determine whether a variable is empty
 *
 * @param   {mixed}     variable - The variable being evaluated
 * @return  {boolean}   TRUE if the variable is empty
 */


function isEmpty(variable) {
  var response = true; // Check if the variable is defined, otherwise is empty

  if (isDefined(variable)) {
    // Check if it's array
    if ($.isArray(variable)) {
      response = variable.length == 0;
    } else if (isObject(variable)) {
      response = $.isEmptyObject(variable);
    } else {
      response = variable == '';
    }
  }

  return response;
}
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */
!function (a) {
  "function" == typeof define && define.amd ? define(["jquery"], a) : a("object" == (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? require("jquery") : jQuery);
}(function (a) {
  var b = function () {
    if (a && a.fn && a.fn.select2 && a.fn.select2.amd) var b = a.fn.select2.amd;
    var b;
    return function () {
      if (!b || !b.requirejs) {
        b ? c = b : b = {};
        var a, c, d;
        !function (b) {
          function e(a, b) {
            return u.call(a, b);
          }

          function f(a, b) {
            var c,
                d,
                e,
                f,
                g,
                h,
                i,
                j,
                k,
                l,
                m,
                n = b && b.split("/"),
                o = s.map,
                p = o && o["*"] || {};
            if (a && "." === a.charAt(0)) if (b) {
              for (n = n.slice(0, n.length - 1), a = a.split("/"), g = a.length - 1, s.nodeIdCompat && w.test(a[g]) && (a[g] = a[g].replace(w, "")), a = n.concat(a), k = 0; k < a.length; k += 1) {
                if (m = a[k], "." === m) a.splice(k, 1), k -= 1;else if (".." === m) {
                  if (1 === k && (".." === a[2] || ".." === a[0])) break;
                  k > 0 && (a.splice(k - 1, 2), k -= 2);
                }
              }

              a = a.join("/");
            } else 0 === a.indexOf("./") && (a = a.substring(2));

            if ((n || p) && o) {
              for (c = a.split("/"), k = c.length; k > 0; k -= 1) {
                if (d = c.slice(0, k).join("/"), n) for (l = n.length; l > 0; l -= 1) {
                  if (e = o[n.slice(0, l).join("/")], e && (e = e[d])) {
                    f = e, h = k;
                    break;
                  }
                }
                if (f) break;
                !i && p && p[d] && (i = p[d], j = k);
              }

              !f && i && (f = i, h = j), f && (c.splice(0, h, f), a = c.join("/"));
            }

            return a;
          }

          function g(a, c) {
            return function () {
              return _n.apply(b, v.call(arguments, 0).concat([a, c]));
            };
          }

          function h(a) {
            return function (b) {
              return f(b, a);
            };
          }

          function i(a) {
            return function (b) {
              q[a] = b;
            };
          }

          function j(a) {
            if (e(r, a)) {
              var c = r[a];
              delete r[a], t[a] = !0, m.apply(b, c);
            }

            if (!e(q, a) && !e(t, a)) throw new Error("No " + a);
            return q[a];
          }

          function k(a) {
            var b,
                c = a ? a.indexOf("!") : -1;
            return c > -1 && (b = a.substring(0, c), a = a.substring(c + 1, a.length)), [b, a];
          }

          function l(a) {
            return function () {
              return s && s.config && s.config[a] || {};
            };
          }

          var m,
              _n,
              o,
              p,
              q = {},
              r = {},
              s = {},
              t = {},
              u = Object.prototype.hasOwnProperty,
              v = [].slice,
              w = /\.js$/;

          o = function o(a, b) {
            var c,
                d = k(a),
                e = d[0];
            return a = d[1], e && (e = f(e, b), c = j(e)), e ? a = c && c.normalize ? c.normalize(a, h(b)) : f(a, b) : (a = f(a, b), d = k(a), e = d[0], a = d[1], e && (c = j(e))), {
              f: e ? e + "!" + a : a,
              n: a,
              pr: e,
              p: c
            };
          }, p = {
            require: function require(a) {
              return g(a);
            },
            exports: function exports(a) {
              var b = q[a];
              return "undefined" != typeof b ? b : q[a] = {};
            },
            module: function module(a) {
              return {
                id: a,
                uri: "",
                exports: q[a],
                config: l(a)
              };
            }
          }, m = function m(a, c, d, f) {
            var h,
                k,
                l,
                m,
                n,
                s,
                u = [],
                v = _typeof(d);

            if (f = f || a, "undefined" === v || "function" === v) {
              for (c = !c.length && d.length ? ["require", "exports", "module"] : c, n = 0; n < c.length; n += 1) {
                if (m = o(c[n], f), k = m.f, "require" === k) u[n] = p.require(a);else if ("exports" === k) u[n] = p.exports(a), s = !0;else if ("module" === k) h = u[n] = p.module(a);else if (e(q, k) || e(r, k) || e(t, k)) u[n] = j(k);else {
                  if (!m.p) throw new Error(a + " missing " + k);
                  m.p.load(m.n, g(f, !0), i(k), {}), u[n] = q[k];
                }
              }

              l = d ? d.apply(q[a], u) : void 0, a && (h && h.exports !== b && h.exports !== q[a] ? q[a] = h.exports : l === b && s || (q[a] = l));
            } else a && (q[a] = d);
          }, a = c = _n = function n(a, c, d, e, f) {
            if ("string" == typeof a) return p[a] ? p[a](c) : j(o(a, c).f);

            if (!a.splice) {
              if (s = a, s.deps && _n(s.deps, s.callback), !c) return;
              c.splice ? (a = c, c = d, d = null) : a = b;
            }

            return c = c || function () {}, "function" == typeof d && (d = e, e = f), e ? m(b, a, c, d) : setTimeout(function () {
              m(b, a, c, d);
            }, 4), _n;
          }, _n.config = function (a) {
            return _n(a);
          }, a._defined = q, d = function d(a, b, c) {
            b.splice || (c = b, b = []), e(q, a) || e(r, a) || (r[a] = [a, b, c]);
          }, d.amd = {
            jQuery: !0
          };
        }(), b.requirejs = a, b.require = c, b.define = d;
      }
    }(), b.define("almond", function () {}), b.define("jquery", [], function () {
      var b = a || $;
      return null == b && console && console.error && console.error("Select2: An instance of jQuery or a jQuery-compatible library was not found. Make sure that you are including jQuery before Select2 on your web page."), b;
    }), b.define("select2/utils", ["jquery"], function (a) {
      function b(a) {
        var b = a.prototype,
            c = [];

        for (var d in b) {
          var e = b[d];
          "function" == typeof e && "constructor" !== d && c.push(d);
        }

        return c;
      }

      var c = {};
      c.Extend = function (a, b) {
        function c() {
          this.constructor = a;
        }

        var d = {}.hasOwnProperty;

        for (var e in b) {
          d.call(b, e) && (a[e] = b[e]);
        }

        return c.prototype = b.prototype, a.prototype = new c(), a.__super__ = b.prototype, a;
      }, c.Decorate = function (a, c) {
        function d() {
          var b = Array.prototype.unshift,
              d = c.prototype.constructor.length,
              e = a.prototype.constructor;
          d > 0 && (b.call(arguments, a.prototype.constructor), e = c.prototype.constructor), e.apply(this, arguments);
        }

        function e() {
          this.constructor = d;
        }

        var f = b(c),
            g = b(a);
        c.displayName = a.displayName, d.prototype = new e();

        for (var h = 0; h < g.length; h++) {
          var i = g[h];
          d.prototype[i] = a.prototype[i];
        }

        for (var j = function j(a) {
          var b = function b() {};

          (a in d.prototype) && (b = d.prototype[a]);
          var e = c.prototype[a];
          return function () {
            var a = Array.prototype.unshift;
            return a.call(arguments, b), e.apply(this, arguments);
          };
        }, k = 0; k < f.length; k++) {
          var l = f[k];
          d.prototype[l] = j(l);
        }

        return d;
      };

      var d = function d() {
        this.listeners = {};
      };

      return d.prototype.on = function (a, b) {
        this.listeners = this.listeners || {}, a in this.listeners ? this.listeners[a].push(b) : this.listeners[a] = [b];
      }, d.prototype.trigger = function (a) {
        var b = Array.prototype.slice;
        this.listeners = this.listeners || {}, a in this.listeners && this.invoke(this.listeners[a], b.call(arguments, 1)), "*" in this.listeners && this.invoke(this.listeners["*"], arguments);
      }, d.prototype.invoke = function (a, b) {
        for (var c = 0, d = a.length; d > c; c++) {
          a[c].apply(this, b);
        }
      }, c.Observable = d, c.generateChars = function (a) {
        for (var b = "", c = 0; a > c; c++) {
          var d = Math.floor(36 * Math.random());
          b += d.toString(36);
        }

        return b;
      }, c.bind = function (a, b) {
        return function () {
          a.apply(b, arguments);
        };
      }, c._convertData = function (a) {
        for (var b in a) {
          var c = b.split("-"),
              d = a;

          if (1 !== c.length) {
            for (var e = 0; e < c.length; e++) {
              var f = c[e];
              f = f.substring(0, 1).toLowerCase() + f.substring(1), f in d || (d[f] = {}), e == c.length - 1 && (d[f] = a[b]), d = d[f];
            }

            delete a[b];
          }
        }

        return a;
      }, c.hasScroll = function (b, c) {
        var d = a(c),
            e = c.style.overflowX,
            f = c.style.overflowY;
        return e !== f || "hidden" !== f && "visible" !== f ? "scroll" === e || "scroll" === f ? !0 : d.innerHeight() < c.scrollHeight || d.innerWidth() < c.scrollWidth : !1;
      }, c.escapeMarkup = function (a) {
        var b = {
          "\\": "&#92;",
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
          "/": "&#47;"
        };
        return "string" != typeof a ? a : String(a).replace(/[&<>"'\/\\]/g, function (a) {
          return b[a];
        });
      }, c.appendMany = function (b, c) {
        if ("1.7" === a.fn.jquery.substr(0, 3)) {
          var d = a();
          a.map(c, function (a) {
            d = d.add(a);
          }), c = d;
        }

        b.append(c);
      }, c;
    }), b.define("select2/results", ["jquery", "./utils"], function (a, b) {
      function c(a, b, d) {
        this.$element = a, this.data = d, this.options = b, c.__super__.constructor.call(this);
      }

      return b.Extend(c, b.Observable), c.prototype.render = function () {
        var b = a('<ul class="select2-results__options" role="tree"></ul>');
        return this.options.get("multiple") && b.attr("aria-multiselectable", "true"), this.$results = b, b;
      }, c.prototype.clear = function () {
        this.$results.empty();
      }, c.prototype.displayMessage = function (b) {
        var c = this.options.get("escapeMarkup");
        this.clear(), this.hideLoading();
        var d = a('<li role="treeitem" class="select2-results__option"></li>'),
            e = this.options.get("translations").get(b.message);
        d.append(c(e(b.args))), this.$results.append(d);
      }, c.prototype.append = function (a) {
        this.hideLoading();
        var b = [];
        if (null == a.results || 0 === a.results.length) return void (0 === this.$results.children().length && this.trigger("results:message", {
          message: "noResults"
        }));
        a.results = this.sort(a.results);

        for (var c = 0; c < a.results.length; c++) {
          var d = a.results[c],
              e = this.option(d);
          b.push(e);
        }

        this.$results.append(b);
      }, c.prototype.position = function (a, b) {
        var c = b.find(".select2-results");
        c.append(a);
      }, c.prototype.sort = function (a) {
        var b = this.options.get("sorter");
        return b(a);
      }, c.prototype.setClasses = function () {
        var b = this;
        this.data.current(function (c) {
          var d = a.map(c, function (a) {
            return a.id.toString();
          }),
              e = b.$results.find(".select2-results__option[aria-selected]");
          e.each(function () {
            var b = a(this),
                c = a.data(this, "data"),
                e = "" + c.id;
            null != c.element && c.element.selected || null == c.element && a.inArray(e, d) > -1 ? b.attr("aria-selected", "true") : b.attr("aria-selected", "false");
          });
          var f = e.filter("[aria-selected=true]");
          f.length > 0 ? f.first().trigger("mouseenter") : e.first().trigger("mouseenter");
        });
      }, c.prototype.showLoading = function (a) {
        this.hideLoading();
        var b = this.options.get("translations").get("searching"),
            c = {
          disabled: !0,
          loading: !0,
          text: b(a)
        },
            d = this.option(c);
        d.className += " loading-results", this.$results.prepend(d);
      }, c.prototype.hideLoading = function () {
        this.$results.find(".loading-results").remove();
      }, c.prototype.option = function (b) {
        var c = document.createElement("li");
        c.className = "select2-results__option";
        var d = {
          role: "treeitem",
          "aria-selected": "false"
        };
        b.disabled && (delete d["aria-selected"], d["aria-disabled"] = "true"), null == b.id && delete d["aria-selected"], null != b._resultId && (c.id = b._resultId), b.title && (c.title = b.title), b.children && (d.role = "group", d["aria-label"] = b.text, delete d["aria-selected"]);

        for (var e in d) {
          var f = d[e];
          c.setAttribute(e, f);
        }

        if (b.children) {
          var g = a(c),
              h = document.createElement("strong");
          h.className = "select2-results__group";
          {
            a(h);
          }
          this.template(b, h);

          for (var i = [], j = 0; j < b.children.length; j++) {
            var k = b.children[j],
                l = this.option(k);
            i.push(l);
          }

          var m = a("<ul></ul>", {
            "class": "select2-results__options select2-results__options--nested"
          });
          m.append(i), g.append(h), g.append(m);
        } else this.template(b, c);

        return a.data(c, "data", b), c;
      }, c.prototype.bind = function (b) {
        var c = this,
            d = b.id + "-results";
        this.$results.attr("id", d), b.on("results:all", function (a) {
          c.clear(), c.append(a.data), b.isOpen() && c.setClasses();
        }), b.on("results:append", function (a) {
          c.append(a.data), b.isOpen() && c.setClasses();
        }), b.on("query", function (a) {
          c.showLoading(a);
        }), b.on("select", function () {
          b.isOpen() && c.setClasses();
        }), b.on("unselect", function () {
          b.isOpen() && c.setClasses();
        }), b.on("open", function () {
          c.$results.attr("aria-expanded", "true"), c.$results.attr("aria-hidden", "false"), c.setClasses(), c.ensureHighlightVisible();
        }), b.on("close", function () {
          c.$results.attr("aria-expanded", "false"), c.$results.attr("aria-hidden", "true"), c.$results.removeAttr("aria-activedescendant");
        }), b.on("results:toggle", function () {
          var a = c.getHighlightedResults();
          0 !== a.length && a.trigger("mouseup");
        }), b.on("results:select", function () {
          var a = c.getHighlightedResults();

          if (0 !== a.length) {
            var b = a.data("data");
            "true" == a.attr("aria-selected") ? c.trigger("close") : c.trigger("select", {
              data: b
            });
          }
        }), b.on("results:previous", function () {
          var a = c.getHighlightedResults(),
              b = c.$results.find("[aria-selected]"),
              d = b.index(a);

          if (0 !== d) {
            var e = d - 1;
            0 === a.length && (e = 0);
            var f = b.eq(e);
            f.trigger("mouseenter");
            var g = c.$results.offset().top,
                h = f.offset().top,
                i = c.$results.scrollTop() + (h - g);
            0 === e ? c.$results.scrollTop(0) : 0 > h - g && c.$results.scrollTop(i);
          }
        }), b.on("results:next", function () {
          var a = c.getHighlightedResults(),
              b = c.$results.find("[aria-selected]"),
              d = b.index(a),
              e = d + 1;

          if (!(e >= b.length)) {
            var f = b.eq(e);
            f.trigger("mouseenter");
            var g = c.$results.offset().top + c.$results.outerHeight(!1),
                h = f.offset().top + f.outerHeight(!1),
                i = c.$results.scrollTop() + h - g;
            0 === e ? c.$results.scrollTop(0) : h > g && c.$results.scrollTop(i);
          }
        }), b.on("results:focus", function (a) {
          a.element.addClass("select2-results__option--highlighted");
        }), b.on("results:message", function (a) {
          c.displayMessage(a);
        }), a.fn.mousewheel && this.$results.on("mousewheel", function (a) {
          var b = c.$results.scrollTop(),
              d = c.$results.get(0).scrollHeight - c.$results.scrollTop() + a.deltaY,
              e = a.deltaY > 0 && b - a.deltaY <= 0,
              f = a.deltaY < 0 && d <= c.$results.height();
          e ? (c.$results.scrollTop(0), a.preventDefault(), a.stopPropagation()) : f && (c.$results.scrollTop(c.$results.get(0).scrollHeight - c.$results.height()), a.preventDefault(), a.stopPropagation());
        }), this.$results.on("mouseup", ".select2-results__option[aria-selected]", function (b) {
          var d = a(this),
              e = d.data("data");
          return "true" === d.attr("aria-selected") ? void (c.options.get("multiple") ? c.trigger("unselect", {
            originalEvent: b,
            data: e
          }) : c.trigger("close")) : void c.trigger("select", {
            originalEvent: b,
            data: e
          });
        }), this.$results.on("mouseenter", ".select2-results__option[aria-selected]", function () {
          var b = a(this).data("data");
          c.getHighlightedResults().removeClass("select2-results__option--highlighted"), c.trigger("results:focus", {
            data: b,
            element: a(this)
          });
        });
      }, c.prototype.getHighlightedResults = function () {
        var a = this.$results.find(".select2-results__option--highlighted");
        return a;
      }, c.prototype.destroy = function () {
        this.$results.remove();
      }, c.prototype.ensureHighlightVisible = function () {
        var a = this.getHighlightedResults();

        if (0 !== a.length) {
          var b = this.$results.find("[aria-selected]"),
              c = b.index(a),
              d = this.$results.offset().top,
              e = a.offset().top,
              f = this.$results.scrollTop() + (e - d),
              g = e - d;
          f -= 2 * a.outerHeight(!1), 2 >= c ? this.$results.scrollTop(0) : (g > this.$results.outerHeight() || 0 > g) && this.$results.scrollTop(f);
        }
      }, c.prototype.template = function (b, c) {
        var d = this.options.get("templateResult"),
            e = this.options.get("escapeMarkup"),
            f = d(b);
        null == f ? c.style.display = "none" : "string" == typeof f ? c.innerHTML = e(f) : a(c).append(f);
      }, c;
    }), b.define("select2/keys", [], function () {
      var a = {
        BACKSPACE: 8,
        TAB: 9,
        ENTER: 13,
        SHIFT: 16,
        CTRL: 17,
        ALT: 18,
        ESC: 27,
        SPACE: 32,
        PAGE_UP: 33,
        PAGE_DOWN: 34,
        END: 35,
        HOME: 36,
        LEFT: 37,
        UP: 38,
        RIGHT: 39,
        DOWN: 40,
        DELETE: 46
      };
      return a;
    }), b.define("select2/selection/base", ["jquery", "../utils", "../keys"], function (a, b, c) {
      function d(a, b) {
        this.$element = a, this.options = b, d.__super__.constructor.call(this);
      }

      return b.Extend(d, b.Observable), d.prototype.render = function () {
        var b = a('<span class="select2-selection" role="combobox" aria-autocomplete="list" aria-haspopup="true" aria-expanded="false"></span>');
        return this._tabindex = 0, null != this.$element.data("old-tabindex") ? this._tabindex = this.$element.data("old-tabindex") : null != this.$element.attr("tabindex") && (this._tabindex = this.$element.attr("tabindex")), b.attr("title", this.$element.attr("title")), b.attr("tabindex", this._tabindex), this.$selection = b, b;
      }, d.prototype.bind = function (a) {
        var b = this,
            d = (a.id + "-container", a.id + "-results");
        this.container = a, this.$selection.on("focus", function (a) {
          b.trigger("focus", a);
        }), this.$selection.on("blur", function (a) {
          b.trigger("blur", a);
        }), this.$selection.on("keydown", function (a) {
          b.trigger("keypress", a), a.which === c.SPACE && a.preventDefault();
        }), a.on("results:focus", function (a) {
          b.$selection.attr("aria-activedescendant", a.data._resultId);
        }), a.on("selection:update", function (a) {
          b.update(a.data);
        }), a.on("open", function () {
          b.$selection.attr("aria-expanded", "true"), b.$selection.attr("aria-owns", d), b._attachCloseHandler(a);
        }), a.on("close", function () {
          b.$selection.attr("aria-expanded", "false"), b.$selection.removeAttr("aria-activedescendant"), b.$selection.removeAttr("aria-owns"), b.$selection.focus(), b._detachCloseHandler(a);
        }), a.on("enable", function () {
          b.$selection.attr("tabindex", b._tabindex);
        }), a.on("disable", function () {
          b.$selection.attr("tabindex", "-1");
        });
      }, d.prototype._attachCloseHandler = function (b) {
        a(document.body).on("mousedown.select2." + b.id, function (b) {
          var c = a(b.target),
              d = c.closest(".select2"),
              e = a(".select2.select2-container--open");
          e.each(function () {
            var b = a(this);

            if (this != d[0]) {
              var c = b.data("element");
              c.select2("close");
            }
          });
        });
      }, d.prototype._detachCloseHandler = function (b) {
        a(document.body).off("mousedown.select2." + b.id);
      }, d.prototype.position = function (a, b) {
        var c = b.find(".selection");
        c.append(a);
      }, d.prototype.destroy = function () {
        this._detachCloseHandler(this.container);
      }, d.prototype.update = function () {
        throw new Error("The `update` method must be defined in child classes.");
      }, d;
    }), b.define("select2/selection/single", ["jquery", "./base", "../utils", "../keys"], function (a, b, c) {
      function d() {
        d.__super__.constructor.apply(this, arguments);
      }

      return c.Extend(d, b), d.prototype.render = function () {
        var a = d.__super__.render.call(this);

        return a.addClass("select2-selection--single"), a.html('<span class="select2-selection__rendered"></span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span>'), a;
      }, d.prototype.bind = function (a) {
        var b = this;

        d.__super__.bind.apply(this, arguments);

        var c = a.id + "-container";
        this.$selection.find(".select2-selection__rendered").attr("id", c), this.$selection.attr("aria-labelledby", c), this.$selection.on("mousedown", function (a) {
          1 === a.which && b.trigger("toggle", {
            originalEvent: a
          });
        }), this.$selection.on("focus", function () {}), this.$selection.on("blur", function () {}), a.on("selection:update", function (a) {
          b.update(a.data);
        });
      }, d.prototype.clear = function () {
        this.$selection.find(".select2-selection__rendered").empty();
      }, d.prototype.display = function (a) {
        var b = this.options.get("templateSelection"),
            c = this.options.get("escapeMarkup");
        return c(b(a));
      }, d.prototype.selectionContainer = function () {
        return a("<span></span>");
      }, d.prototype.update = function (a) {
        if (0 === a.length) return void this.clear();
        var b = a[0],
            c = this.display(b),
            d = this.$selection.find(".select2-selection__rendered");
        d.empty().append(c), d.prop("title", b.title || b.text);
      }, d;
    }), b.define("select2/selection/multiple", ["jquery", "./base", "../utils"], function (a, b, c) {
      function d() {
        d.__super__.constructor.apply(this, arguments);
      }

      return c.Extend(d, b), d.prototype.render = function () {
        var a = d.__super__.render.call(this);

        return a.addClass("select2-selection--multiple"), a.html('<ul class="select2-selection__rendered"></ul>'), a;
      }, d.prototype.bind = function () {
        var b = this;
        d.__super__.bind.apply(this, arguments), this.$selection.on("click", function (a) {
          b.trigger("toggle", {
            originalEvent: a
          });
        }), this.$selection.on("click", ".select2-selection__choice__remove", function (c) {
          var d = a(this),
              e = d.parent(),
              f = e.data("data");
          b.trigger("unselect", {
            originalEvent: c,
            data: f
          });
        });
      }, d.prototype.clear = function () {
        this.$selection.find(".select2-selection__rendered").empty();
      }, d.prototype.display = function (a) {
        var b = this.options.get("templateSelection"),
            c = this.options.get("escapeMarkup");
        return c(b(a));
      }, d.prototype.selectionContainer = function () {
        var b = a('<li class="select2-selection__choice"><span class="select2-selection__choice__remove" role="presentation">&times;</span></li>');
        return b;
      }, d.prototype.update = function (a) {
        if (this.clear(), 0 !== a.length) {
          for (var b = [], d = 0; d < a.length; d++) {
            var e = a[d],
                f = this.display(e),
                g = this.selectionContainer();
            g.append(f), g.prop("title", e.title || e.text), g.data("data", e), b.push(g);
          }

          var h = this.$selection.find(".select2-selection__rendered");
          c.appendMany(h, b);
        }
      }, d;
    }), b.define("select2/selection/placeholder", ["../utils"], function () {
      function a(a, b, c) {
        this.placeholder = this.normalizePlaceholder(c.get("placeholder")), a.call(this, b, c);
      }

      return a.prototype.normalizePlaceholder = function (a, b) {
        return "string" == typeof b && (b = {
          id: "",
          text: b
        }), b;
      }, a.prototype.createPlaceholder = function (a, b) {
        var c = this.selectionContainer();
        return c.html(this.display(b)), c.addClass("select2-selection__placeholder").removeClass("select2-selection__choice"), c;
      }, a.prototype.update = function (a, b) {
        var c = 1 == b.length && b[0].id != this.placeholder.id,
            d = b.length > 1;
        if (d || c) return a.call(this, b);
        this.clear();
        var e = this.createPlaceholder(this.placeholder);
        this.$selection.find(".select2-selection__rendered").append(e);
      }, a;
    }), b.define("select2/selection/allowClear", ["jquery", "../keys"], function (a, b) {
      function c() {}

      return c.prototype.bind = function (a, b, c) {
        var d = this;
        a.call(this, b, c), null == this.placeholder && this.options.get("debug") && window.console && console.error && console.error("Select2: The `allowClear` option should be used in combination with the `placeholder` option."), this.$selection.on("mousedown", ".select2-selection__clear", function (a) {
          d._handleClear(a);
        }), b.on("keypress", function (a) {
          d._handleKeyboardClear(a, b);
        });
      }, c.prototype._handleClear = function (a, b) {
        if (!this.options.get("disabled")) {
          var c = this.$selection.find(".select2-selection__clear");

          if (0 !== c.length) {
            b.stopPropagation();

            for (var d = c.data("data"), e = 0; e < d.length; e++) {
              var f = {
                data: d[e]
              };
              if (this.trigger("unselect", f), f.prevented) return;
            }

            this.$element.val(this.placeholder.id).trigger("change"), this.trigger("toggle");
          }
        }
      }, c.prototype._handleKeyboardClear = function (a, c, d) {
        d.isOpen() || (c.which == b.DELETE || c.which == b.BACKSPACE) && this._handleClear(c);
      }, c.prototype.update = function (b, c) {
        if (b.call(this, c), !(this.$selection.find(".select2-selection__placeholder").length > 0 || 0 === c.length)) {
          var d = a('<span class="select2-selection__clear">&times;</span>');
          d.data("data", c), this.$selection.find(".select2-selection__rendered").prepend(d);
        }
      }, c;
    }), b.define("select2/selection/search", ["jquery", "../utils", "../keys"], function (a, b, c) {
      function d(a, b, c) {
        a.call(this, b, c);
      }

      return d.prototype.render = function (b) {
        var c = a('<li class="select2-search select2-search--inline"><input class="select2-search__field" type="search" tabindex="-1" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" role="textbox" /></li>');
        this.$searchContainer = c, this.$search = c.find("input");
        var d = b.call(this);
        return d;
      }, d.prototype.bind = function (a, b, d) {
        var e = this;
        a.call(this, b, d), b.on("open", function () {
          e.$search.attr("tabindex", 0), e.$search.focus();
        }), b.on("close", function () {
          e.$search.attr("tabindex", -1), e.$search.val(""), e.$search.focus();
        }), b.on("enable", function () {
          e.$search.prop("disabled", !1);
        }), b.on("disable", function () {
          e.$search.prop("disabled", !0);
        }), this.$selection.on("focusin", ".select2-search--inline", function (a) {
          e.trigger("focus", a);
        }), this.$selection.on("focusout", ".select2-search--inline", function (a) {
          e.trigger("blur", a);
        }), this.$selection.on("keydown", ".select2-search--inline", function (a) {
          a.stopPropagation(), e.trigger("keypress", a), e._keyUpPrevented = a.isDefaultPrevented();
          var b = a.which;

          if (b === c.BACKSPACE && "" === e.$search.val()) {
            var d = e.$searchContainer.prev(".select2-selection__choice");

            if (d.length > 0) {
              var f = d.data("data");
              e.searchRemoveChoice(f), a.preventDefault();
            }
          }
        }), this.$selection.on("input", ".select2-search--inline", function () {
          e.$selection.off("keyup.search");
        }), this.$selection.on("keyup.search input", ".select2-search--inline", function (a) {
          e.handleSearch(a);
        });
      }, d.prototype.createPlaceholder = function (a, b) {
        this.$search.attr("placeholder", b.text);
      }, d.prototype.update = function (a, b) {
        this.$search.attr("placeholder", ""), a.call(this, b), this.$selection.find(".select2-selection__rendered").append(this.$searchContainer), this.resizeSearch();
      }, d.prototype.handleSearch = function () {
        if (this.resizeSearch(), !this._keyUpPrevented) {
          var a = this.$search.val();
          this.trigger("query", {
            term: a
          });
        }

        this._keyUpPrevented = !1;
      }, d.prototype.searchRemoveChoice = function (a, b) {
        this.trigger("unselect", {
          data: b
        }), this.trigger("open"), this.$search.val(b.text + " ");
      }, d.prototype.resizeSearch = function () {
        this.$search.css("width", "25px");
        var a = "";
        if ("" !== this.$search.attr("placeholder")) a = this.$selection.find(".select2-selection__rendered").innerWidth();else {
          var b = this.$search.val().length + 1;
          a = .75 * b + "em";
        }
        this.$search.css("width", a);
      }, d;
    }), b.define("select2/selection/eventRelay", ["jquery"], function (a) {
      function b() {}

      return b.prototype.bind = function (b, c, d) {
        var e = this,
            f = ["open", "opening", "close", "closing", "select", "selecting", "unselect", "unselecting"],
            g = ["opening", "closing", "selecting", "unselecting"];
        b.call(this, c, d), c.on("*", function (b, c) {
          if (-1 !== a.inArray(b, f)) {
            c = c || {};
            var d = a.Event("select2:" + b, {
              params: c
            });
            e.$element.trigger(d), -1 !== a.inArray(b, g) && (c.prevented = d.isDefaultPrevented());
          }
        });
      }, b;
    }), b.define("select2/translation", ["jquery", "require"], function (a, b) {
      function c(a) {
        this.dict = a || {};
      }

      return c.prototype.all = function () {
        return this.dict;
      }, c.prototype.get = function (a) {
        return this.dict[a];
      }, c.prototype.extend = function (b) {
        this.dict = a.extend({}, b.all(), this.dict);
      }, c._cache = {}, c.loadPath = function (a) {
        if (!(a in c._cache)) {
          var d = b(a);
          c._cache[a] = d;
        }

        return new c(c._cache[a]);
      }, c;
    }), b.define("select2/diacritics", [], function () {
      var a = {
        "Ⓐ": "A",
        "Ａ": "A",
        "À": "A",
        "Á": "A",
        "Â": "A",
        "Ầ": "A",
        "Ấ": "A",
        "Ẫ": "A",
        "Ẩ": "A",
        "Ã": "A",
        "Ā": "A",
        "Ă": "A",
        "Ằ": "A",
        "Ắ": "A",
        "Ẵ": "A",
        "Ẳ": "A",
        "Ȧ": "A",
        "Ǡ": "A",
        "Ä": "A",
        "Ǟ": "A",
        "Ả": "A",
        "Å": "A",
        "Ǻ": "A",
        "Ǎ": "A",
        "Ȁ": "A",
        "Ȃ": "A",
        "Ạ": "A",
        "Ậ": "A",
        "Ặ": "A",
        "Ḁ": "A",
        "Ą": "A",
        "Ⱥ": "A",
        "Ɐ": "A",
        "Ꜳ": "AA",
        "Æ": "AE",
        "Ǽ": "AE",
        "Ǣ": "AE",
        "Ꜵ": "AO",
        "Ꜷ": "AU",
        "Ꜹ": "AV",
        "Ꜻ": "AV",
        "Ꜽ": "AY",
        "Ⓑ": "B",
        "Ｂ": "B",
        "Ḃ": "B",
        "Ḅ": "B",
        "Ḇ": "B",
        "Ƀ": "B",
        "Ƃ": "B",
        "Ɓ": "B",
        "Ⓒ": "C",
        "Ｃ": "C",
        "Ć": "C",
        "Ĉ": "C",
        "Ċ": "C",
        "Č": "C",
        "Ç": "C",
        "Ḉ": "C",
        "Ƈ": "C",
        "Ȼ": "C",
        "Ꜿ": "C",
        "Ⓓ": "D",
        "Ｄ": "D",
        "Ḋ": "D",
        "Ď": "D",
        "Ḍ": "D",
        "Ḑ": "D",
        "Ḓ": "D",
        "Ḏ": "D",
        "Đ": "D",
        "Ƌ": "D",
        "Ɗ": "D",
        "Ɖ": "D",
        "Ꝺ": "D",
        "Ǳ": "DZ",
        "Ǆ": "DZ",
        "ǲ": "Dz",
        "ǅ": "Dz",
        "Ⓔ": "E",
        "Ｅ": "E",
        "È": "E",
        "É": "E",
        "Ê": "E",
        "Ề": "E",
        "Ế": "E",
        "Ễ": "E",
        "Ể": "E",
        "Ẽ": "E",
        "Ē": "E",
        "Ḕ": "E",
        "Ḗ": "E",
        "Ĕ": "E",
        "Ė": "E",
        "Ë": "E",
        "Ẻ": "E",
        "Ě": "E",
        "Ȅ": "E",
        "Ȇ": "E",
        "Ẹ": "E",
        "Ệ": "E",
        "Ȩ": "E",
        "Ḝ": "E",
        "Ę": "E",
        "Ḙ": "E",
        "Ḛ": "E",
        "Ɛ": "E",
        "Ǝ": "E",
        "Ⓕ": "F",
        "Ｆ": "F",
        "Ḟ": "F",
        "Ƒ": "F",
        "Ꝼ": "F",
        "Ⓖ": "G",
        "Ｇ": "G",
        "Ǵ": "G",
        "Ĝ": "G",
        "Ḡ": "G",
        "Ğ": "G",
        "Ġ": "G",
        "Ǧ": "G",
        "Ģ": "G",
        "Ǥ": "G",
        "Ɠ": "G",
        "Ꞡ": "G",
        "Ᵹ": "G",
        "Ꝿ": "G",
        "Ⓗ": "H",
        "Ｈ": "H",
        "Ĥ": "H",
        "Ḣ": "H",
        "Ḧ": "H",
        "Ȟ": "H",
        "Ḥ": "H",
        "Ḩ": "H",
        "Ḫ": "H",
        "Ħ": "H",
        "Ⱨ": "H",
        "Ⱶ": "H",
        "Ɥ": "H",
        "Ⓘ": "I",
        "Ｉ": "I",
        "Ì": "I",
        "Í": "I",
        "Î": "I",
        "Ĩ": "I",
        "Ī": "I",
        "Ĭ": "I",
        "İ": "I",
        "Ï": "I",
        "Ḯ": "I",
        "Ỉ": "I",
        "Ǐ": "I",
        "Ȉ": "I",
        "Ȋ": "I",
        "Ị": "I",
        "Į": "I",
        "Ḭ": "I",
        "Ɨ": "I",
        "Ⓙ": "J",
        "Ｊ": "J",
        "Ĵ": "J",
        "Ɉ": "J",
        "Ⓚ": "K",
        "Ｋ": "K",
        "Ḱ": "K",
        "Ǩ": "K",
        "Ḳ": "K",
        "Ķ": "K",
        "Ḵ": "K",
        "Ƙ": "K",
        "Ⱪ": "K",
        "Ꝁ": "K",
        "Ꝃ": "K",
        "Ꝅ": "K",
        "Ꞣ": "K",
        "Ⓛ": "L",
        "Ｌ": "L",
        "Ŀ": "L",
        "Ĺ": "L",
        "Ľ": "L",
        "Ḷ": "L",
        "Ḹ": "L",
        "Ļ": "L",
        "Ḽ": "L",
        "Ḻ": "L",
        "Ł": "L",
        "Ƚ": "L",
        "Ɫ": "L",
        "Ⱡ": "L",
        "Ꝉ": "L",
        "Ꝇ": "L",
        "Ꞁ": "L",
        "Ǉ": "LJ",
        "ǈ": "Lj",
        "Ⓜ": "M",
        "Ｍ": "M",
        "Ḿ": "M",
        "Ṁ": "M",
        "Ṃ": "M",
        "Ɱ": "M",
        "Ɯ": "M",
        "Ⓝ": "N",
        "Ｎ": "N",
        "Ǹ": "N",
        "Ń": "N",
        "Ñ": "N",
        "Ṅ": "N",
        "Ň": "N",
        "Ṇ": "N",
        "Ņ": "N",
        "Ṋ": "N",
        "Ṉ": "N",
        "Ƞ": "N",
        "Ɲ": "N",
        "Ꞑ": "N",
        "Ꞥ": "N",
        "Ǌ": "NJ",
        "ǋ": "Nj",
        "Ⓞ": "O",
        "Ｏ": "O",
        "Ò": "O",
        "Ó": "O",
        "Ô": "O",
        "Ồ": "O",
        "Ố": "O",
        "Ỗ": "O",
        "Ổ": "O",
        "Õ": "O",
        "Ṍ": "O",
        "Ȭ": "O",
        "Ṏ": "O",
        "Ō": "O",
        "Ṑ": "O",
        "Ṓ": "O",
        "Ŏ": "O",
        "Ȯ": "O",
        "Ȱ": "O",
        "Ö": "O",
        "Ȫ": "O",
        "Ỏ": "O",
        "Ő": "O",
        "Ǒ": "O",
        "Ȍ": "O",
        "Ȏ": "O",
        "Ơ": "O",
        "Ờ": "O",
        "Ớ": "O",
        "Ỡ": "O",
        "Ở": "O",
        "Ợ": "O",
        "Ọ": "O",
        "Ộ": "O",
        "Ǫ": "O",
        "Ǭ": "O",
        "Ø": "O",
        "Ǿ": "O",
        "Ɔ": "O",
        "Ɵ": "O",
        "Ꝋ": "O",
        "Ꝍ": "O",
        "Ƣ": "OI",
        "Ꝏ": "OO",
        "Ȣ": "OU",
        "Ⓟ": "P",
        "Ｐ": "P",
        "Ṕ": "P",
        "Ṗ": "P",
        "Ƥ": "P",
        "Ᵽ": "P",
        "Ꝑ": "P",
        "Ꝓ": "P",
        "Ꝕ": "P",
        "Ⓠ": "Q",
        "Ｑ": "Q",
        "Ꝗ": "Q",
        "Ꝙ": "Q",
        "Ɋ": "Q",
        "Ⓡ": "R",
        "Ｒ": "R",
        "Ŕ": "R",
        "Ṙ": "R",
        "Ř": "R",
        "Ȑ": "R",
        "Ȓ": "R",
        "Ṛ": "R",
        "Ṝ": "R",
        "Ŗ": "R",
        "Ṟ": "R",
        "Ɍ": "R",
        "Ɽ": "R",
        "Ꝛ": "R",
        "Ꞧ": "R",
        "Ꞃ": "R",
        "Ⓢ": "S",
        "Ｓ": "S",
        "ẞ": "S",
        "Ś": "S",
        "Ṥ": "S",
        "Ŝ": "S",
        "Ṡ": "S",
        "Š": "S",
        "Ṧ": "S",
        "Ṣ": "S",
        "Ṩ": "S",
        "Ș": "S",
        "Ş": "S",
        "Ȿ": "S",
        "Ꞩ": "S",
        "Ꞅ": "S",
        "Ⓣ": "T",
        "Ｔ": "T",
        "Ṫ": "T",
        "Ť": "T",
        "Ṭ": "T",
        "Ț": "T",
        "Ţ": "T",
        "Ṱ": "T",
        "Ṯ": "T",
        "Ŧ": "T",
        "Ƭ": "T",
        "Ʈ": "T",
        "Ⱦ": "T",
        "Ꞇ": "T",
        "Ꜩ": "TZ",
        "Ⓤ": "U",
        "Ｕ": "U",
        "Ù": "U",
        "Ú": "U",
        "Û": "U",
        "Ũ": "U",
        "Ṹ": "U",
        "Ū": "U",
        "Ṻ": "U",
        "Ŭ": "U",
        "Ü": "U",
        "Ǜ": "U",
        "Ǘ": "U",
        "Ǖ": "U",
        "Ǚ": "U",
        "Ủ": "U",
        "Ů": "U",
        "Ű": "U",
        "Ǔ": "U",
        "Ȕ": "U",
        "Ȗ": "U",
        "Ư": "U",
        "Ừ": "U",
        "Ứ": "U",
        "Ữ": "U",
        "Ử": "U",
        "Ự": "U",
        "Ụ": "U",
        "Ṳ": "U",
        "Ų": "U",
        "Ṷ": "U",
        "Ṵ": "U",
        "Ʉ": "U",
        "Ⓥ": "V",
        "Ｖ": "V",
        "Ṽ": "V",
        "Ṿ": "V",
        "Ʋ": "V",
        "Ꝟ": "V",
        "Ʌ": "V",
        "Ꝡ": "VY",
        "Ⓦ": "W",
        "Ｗ": "W",
        "Ẁ": "W",
        "Ẃ": "W",
        "Ŵ": "W",
        "Ẇ": "W",
        "Ẅ": "W",
        "Ẉ": "W",
        "Ⱳ": "W",
        "Ⓧ": "X",
        "Ｘ": "X",
        "Ẋ": "X",
        "Ẍ": "X",
        "Ⓨ": "Y",
        "Ｙ": "Y",
        "Ỳ": "Y",
        "Ý": "Y",
        "Ŷ": "Y",
        "Ỹ": "Y",
        "Ȳ": "Y",
        "Ẏ": "Y",
        "Ÿ": "Y",
        "Ỷ": "Y",
        "Ỵ": "Y",
        "Ƴ": "Y",
        "Ɏ": "Y",
        "Ỿ": "Y",
        "Ⓩ": "Z",
        "Ｚ": "Z",
        "Ź": "Z",
        "Ẑ": "Z",
        "Ż": "Z",
        "Ž": "Z",
        "Ẓ": "Z",
        "Ẕ": "Z",
        "Ƶ": "Z",
        "Ȥ": "Z",
        "Ɀ": "Z",
        "Ⱬ": "Z",
        "Ꝣ": "Z",
        "ⓐ": "a",
        "ａ": "a",
        "ẚ": "a",
        "à": "a",
        "á": "a",
        "â": "a",
        "ầ": "a",
        "ấ": "a",
        "ẫ": "a",
        "ẩ": "a",
        "ã": "a",
        "ā": "a",
        "ă": "a",
        "ằ": "a",
        "ắ": "a",
        "ẵ": "a",
        "ẳ": "a",
        "ȧ": "a",
        "ǡ": "a",
        "ä": "a",
        "ǟ": "a",
        "ả": "a",
        "å": "a",
        "ǻ": "a",
        "ǎ": "a",
        "ȁ": "a",
        "ȃ": "a",
        "ạ": "a",
        "ậ": "a",
        "ặ": "a",
        "ḁ": "a",
        "ą": "a",
        "ⱥ": "a",
        "ɐ": "a",
        "ꜳ": "aa",
        "æ": "ae",
        "ǽ": "ae",
        "ǣ": "ae",
        "ꜵ": "ao",
        "ꜷ": "au",
        "ꜹ": "av",
        "ꜻ": "av",
        "ꜽ": "ay",
        "ⓑ": "b",
        "ｂ": "b",
        "ḃ": "b",
        "ḅ": "b",
        "ḇ": "b",
        "ƀ": "b",
        "ƃ": "b",
        "ɓ": "b",
        "ⓒ": "c",
        "ｃ": "c",
        "ć": "c",
        "ĉ": "c",
        "ċ": "c",
        "č": "c",
        "ç": "c",
        "ḉ": "c",
        "ƈ": "c",
        "ȼ": "c",
        "ꜿ": "c",
        "ↄ": "c",
        "ⓓ": "d",
        "ｄ": "d",
        "ḋ": "d",
        "ď": "d",
        "ḍ": "d",
        "ḑ": "d",
        "ḓ": "d",
        "ḏ": "d",
        "đ": "d",
        "ƌ": "d",
        "ɖ": "d",
        "ɗ": "d",
        "ꝺ": "d",
        "ǳ": "dz",
        "ǆ": "dz",
        "ⓔ": "e",
        "ｅ": "e",
        "è": "e",
        "é": "e",
        "ê": "e",
        "ề": "e",
        "ế": "e",
        "ễ": "e",
        "ể": "e",
        "ẽ": "e",
        "ē": "e",
        "ḕ": "e",
        "ḗ": "e",
        "ĕ": "e",
        "ė": "e",
        "ë": "e",
        "ẻ": "e",
        "ě": "e",
        "ȅ": "e",
        "ȇ": "e",
        "ẹ": "e",
        "ệ": "e",
        "ȩ": "e",
        "ḝ": "e",
        "ę": "e",
        "ḙ": "e",
        "ḛ": "e",
        "ɇ": "e",
        "ɛ": "e",
        "ǝ": "e",
        "ⓕ": "f",
        "ｆ": "f",
        "ḟ": "f",
        "ƒ": "f",
        "ꝼ": "f",
        "ⓖ": "g",
        "ｇ": "g",
        "ǵ": "g",
        "ĝ": "g",
        "ḡ": "g",
        "ğ": "g",
        "ġ": "g",
        "ǧ": "g",
        "ģ": "g",
        "ǥ": "g",
        "ɠ": "g",
        "ꞡ": "g",
        "ᵹ": "g",
        "ꝿ": "g",
        "ⓗ": "h",
        "ｈ": "h",
        "ĥ": "h",
        "ḣ": "h",
        "ḧ": "h",
        "ȟ": "h",
        "ḥ": "h",
        "ḩ": "h",
        "ḫ": "h",
        "ẖ": "h",
        "ħ": "h",
        "ⱨ": "h",
        "ⱶ": "h",
        "ɥ": "h",
        "ƕ": "hv",
        "ⓘ": "i",
        "ｉ": "i",
        "ì": "i",
        "í": "i",
        "î": "i",
        "ĩ": "i",
        "ī": "i",
        "ĭ": "i",
        "ï": "i",
        "ḯ": "i",
        "ỉ": "i",
        "ǐ": "i",
        "ȉ": "i",
        "ȋ": "i",
        "ị": "i",
        "į": "i",
        "ḭ": "i",
        "ɨ": "i",
        "ı": "i",
        "ⓙ": "j",
        "ｊ": "j",
        "ĵ": "j",
        "ǰ": "j",
        "ɉ": "j",
        "ⓚ": "k",
        "ｋ": "k",
        "ḱ": "k",
        "ǩ": "k",
        "ḳ": "k",
        "ķ": "k",
        "ḵ": "k",
        "ƙ": "k",
        "ⱪ": "k",
        "ꝁ": "k",
        "ꝃ": "k",
        "ꝅ": "k",
        "ꞣ": "k",
        "ⓛ": "l",
        "ｌ": "l",
        "ŀ": "l",
        "ĺ": "l",
        "ľ": "l",
        "ḷ": "l",
        "ḹ": "l",
        "ļ": "l",
        "ḽ": "l",
        "ḻ": "l",
        "ſ": "l",
        "ł": "l",
        "ƚ": "l",
        "ɫ": "l",
        "ⱡ": "l",
        "ꝉ": "l",
        "ꞁ": "l",
        "ꝇ": "l",
        "ǉ": "lj",
        "ⓜ": "m",
        "ｍ": "m",
        "ḿ": "m",
        "ṁ": "m",
        "ṃ": "m",
        "ɱ": "m",
        "ɯ": "m",
        "ⓝ": "n",
        "ｎ": "n",
        "ǹ": "n",
        "ń": "n",
        "ñ": "n",
        "ṅ": "n",
        "ň": "n",
        "ṇ": "n",
        "ņ": "n",
        "ṋ": "n",
        "ṉ": "n",
        "ƞ": "n",
        "ɲ": "n",
        "ŉ": "n",
        "ꞑ": "n",
        "ꞥ": "n",
        "ǌ": "nj",
        "ⓞ": "o",
        "ｏ": "o",
        "ò": "o",
        "ó": "o",
        "ô": "o",
        "ồ": "o",
        "ố": "o",
        "ỗ": "o",
        "ổ": "o",
        "õ": "o",
        "ṍ": "o",
        "ȭ": "o",
        "ṏ": "o",
        "ō": "o",
        "ṑ": "o",
        "ṓ": "o",
        "ŏ": "o",
        "ȯ": "o",
        "ȱ": "o",
        "ö": "o",
        "ȫ": "o",
        "ỏ": "o",
        "ő": "o",
        "ǒ": "o",
        "ȍ": "o",
        "ȏ": "o",
        "ơ": "o",
        "ờ": "o",
        "ớ": "o",
        "ỡ": "o",
        "ở": "o",
        "ợ": "o",
        "ọ": "o",
        "ộ": "o",
        "ǫ": "o",
        "ǭ": "o",
        "ø": "o",
        "ǿ": "o",
        "ɔ": "o",
        "ꝋ": "o",
        "ꝍ": "o",
        "ɵ": "o",
        "ƣ": "oi",
        "ȣ": "ou",
        "ꝏ": "oo",
        "ⓟ": "p",
        "ｐ": "p",
        "ṕ": "p",
        "ṗ": "p",
        "ƥ": "p",
        "ᵽ": "p",
        "ꝑ": "p",
        "ꝓ": "p",
        "ꝕ": "p",
        "ⓠ": "q",
        "ｑ": "q",
        "ɋ": "q",
        "ꝗ": "q",
        "ꝙ": "q",
        "ⓡ": "r",
        "ｒ": "r",
        "ŕ": "r",
        "ṙ": "r",
        "ř": "r",
        "ȑ": "r",
        "ȓ": "r",
        "ṛ": "r",
        "ṝ": "r",
        "ŗ": "r",
        "ṟ": "r",
        "ɍ": "r",
        "ɽ": "r",
        "ꝛ": "r",
        "ꞧ": "r",
        "ꞃ": "r",
        "ⓢ": "s",
        "ｓ": "s",
        "ß": "s",
        "ś": "s",
        "ṥ": "s",
        "ŝ": "s",
        "ṡ": "s",
        "š": "s",
        "ṧ": "s",
        "ṣ": "s",
        "ṩ": "s",
        "ș": "s",
        "ş": "s",
        "ȿ": "s",
        "ꞩ": "s",
        "ꞅ": "s",
        "ẛ": "s",
        "ⓣ": "t",
        "ｔ": "t",
        "ṫ": "t",
        "ẗ": "t",
        "ť": "t",
        "ṭ": "t",
        "ț": "t",
        "ţ": "t",
        "ṱ": "t",
        "ṯ": "t",
        "ŧ": "t",
        "ƭ": "t",
        "ʈ": "t",
        "ⱦ": "t",
        "ꞇ": "t",
        "ꜩ": "tz",
        "ⓤ": "u",
        "ｕ": "u",
        "ù": "u",
        "ú": "u",
        "û": "u",
        "ũ": "u",
        "ṹ": "u",
        "ū": "u",
        "ṻ": "u",
        "ŭ": "u",
        "ü": "u",
        "ǜ": "u",
        "ǘ": "u",
        "ǖ": "u",
        "ǚ": "u",
        "ủ": "u",
        "ů": "u",
        "ű": "u",
        "ǔ": "u",
        "ȕ": "u",
        "ȗ": "u",
        "ư": "u",
        "ừ": "u",
        "ứ": "u",
        "ữ": "u",
        "ử": "u",
        "ự": "u",
        "ụ": "u",
        "ṳ": "u",
        "ų": "u",
        "ṷ": "u",
        "ṵ": "u",
        "ʉ": "u",
        "ⓥ": "v",
        "ｖ": "v",
        "ṽ": "v",
        "ṿ": "v",
        "ʋ": "v",
        "ꝟ": "v",
        "ʌ": "v",
        "ꝡ": "vy",
        "ⓦ": "w",
        "ｗ": "w",
        "ẁ": "w",
        "ẃ": "w",
        "ŵ": "w",
        "ẇ": "w",
        "ẅ": "w",
        "ẘ": "w",
        "ẉ": "w",
        "ⱳ": "w",
        "ⓧ": "x",
        "ｘ": "x",
        "ẋ": "x",
        "ẍ": "x",
        "ⓨ": "y",
        "ｙ": "y",
        "ỳ": "y",
        "ý": "y",
        "ŷ": "y",
        "ỹ": "y",
        "ȳ": "y",
        "ẏ": "y",
        "ÿ": "y",
        "ỷ": "y",
        "ẙ": "y",
        "ỵ": "y",
        "ƴ": "y",
        "ɏ": "y",
        "ỿ": "y",
        "ⓩ": "z",
        "ｚ": "z",
        "ź": "z",
        "ẑ": "z",
        "ż": "z",
        "ž": "z",
        "ẓ": "z",
        "ẕ": "z",
        "ƶ": "z",
        "ȥ": "z",
        "ɀ": "z",
        "ⱬ": "z",
        "ꝣ": "z",
        "Ά": "Α",
        "Έ": "Ε",
        "Ή": "Η",
        "Ί": "Ι",
        "Ϊ": "Ι",
        "Ό": "Ο",
        "Ύ": "Υ",
        "Ϋ": "Υ",
        "Ώ": "Ω",
        "ά": "α",
        "έ": "ε",
        "ή": "η",
        "ί": "ι",
        "ϊ": "ι",
        "ΐ": "ι",
        "ό": "ο",
        "ύ": "υ",
        "ϋ": "υ",
        "ΰ": "υ",
        "ω": "ω",
        "ς": "σ"
      };
      return a;
    }), b.define("select2/data/base", ["../utils"], function (a) {
      function b() {
        b.__super__.constructor.call(this);
      }

      return a.Extend(b, a.Observable), b.prototype.current = function () {
        throw new Error("The `current` method must be defined in child classes.");
      }, b.prototype.query = function () {
        throw new Error("The `query` method must be defined in child classes.");
      }, b.prototype.bind = function () {}, b.prototype.destroy = function () {}, b.prototype.generateResultId = function (b, c) {
        var d = b.id + "-result-";
        return d += a.generateChars(4), d += null != c.id ? "-" + c.id.toString() : "-" + a.generateChars(4);
      }, b;
    }), b.define("select2/data/select", ["./base", "../utils", "jquery"], function (a, b, c) {
      function d(a, b) {
        this.$element = a, this.options = b, d.__super__.constructor.call(this);
      }

      return b.Extend(d, a), d.prototype.current = function (a) {
        var b = [],
            d = this;
        this.$element.find(":selected").each(function () {
          var a = c(this),
              e = d.item(a);
          b.push(e);
        }), a(b);
      }, d.prototype.select = function (a) {
        var b = this;
        if (a.selected = !0, c(a.element).is("option")) return a.element.selected = !0, void this.$element.trigger("change");
        if (this.$element.prop("multiple")) this.current(function (d) {
          var e = [];
          a = [a], a.push.apply(a, d);

          for (var f = 0; f < a.length; f++) {
            var g = a[f].id;
            -1 === c.inArray(g, e) && e.push(g);
          }

          b.$element.val(e), b.$element.trigger("change");
        });else {
          var d = a.id;
          this.$element.val(d), this.$element.trigger("change");
        }
      }, d.prototype.unselect = function (a) {
        var b = this;
        if (this.$element.prop("multiple")) return a.selected = !1, c(a.element).is("option") ? (a.element.selected = !1, void this.$element.trigger("change")) : void this.current(function (d) {
          for (var e = [], f = 0; f < d.length; f++) {
            var g = d[f].id;
            g !== a.id && -1 === c.inArray(g, e) && e.push(g);
          }

          b.$element.val(e), b.$element.trigger("change");
        });
      }, d.prototype.bind = function (a) {
        var b = this;
        this.container = a, a.on("select", function (a) {
          b.select(a.data);
        }), a.on("unselect", function (a) {
          b.unselect(a.data);
        });
      }, d.prototype.destroy = function () {
        this.$element.find("*").each(function () {
          c.removeData(this, "data");
        });
      }, d.prototype.query = function (a, b) {
        var d = [],
            e = this,
            f = this.$element.children();
        f.each(function () {
          var b = c(this);

          if (b.is("option") || b.is("optgroup")) {
            var f = e.item(b),
                g = e.matches(a, f);
            null !== g && d.push(g);
          }
        }), b({
          results: d
        });
      }, d.prototype.addOptions = function (a) {
        b.appendMany(this.$element, a);
      }, d.prototype.option = function (a) {
        var b;
        a.children ? (b = document.createElement("optgroup"), b.label = a.text) : (b = document.createElement("option"), void 0 !== b.textContent ? b.textContent = a.text : b.innerText = a.text), a.id && (b.value = a.id), a.disabled && (b.disabled = !0), a.selected && (b.selected = !0), a.title && (b.title = a.title);

        var d = c(b),
            e = this._normalizeItem(a);

        return e.element = b, c.data(b, "data", e), d;
      }, d.prototype.item = function (a) {
        var b = {};
        if (b = c.data(a[0], "data"), null != b) return b;
        if (a.is("option")) b = {
          id: a.val(),
          text: a.text(),
          disabled: a.prop("disabled"),
          selected: a.prop("selected"),
          title: a.prop("title")
        };else if (a.is("optgroup")) {
          b = {
            text: a.prop("label"),
            children: [],
            title: a.prop("title")
          };

          for (var d = a.children("option"), e = [], f = 0; f < d.length; f++) {
            var g = c(d[f]),
                h = this.item(g);
            e.push(h);
          }

          b.children = e;
        }
        return b = this._normalizeItem(b), b.element = a[0], c.data(a[0], "data", b), b;
      }, d.prototype._normalizeItem = function (a) {
        c.isPlainObject(a) || (a = {
          id: a,
          text: a
        }), a = c.extend({}, {
          text: ""
        }, a);
        var b = {
          selected: !1,
          disabled: !1
        };
        return null != a.id && (a.id = a.id.toString()), null != a.text && (a.text = a.text.toString()), null == a._resultId && a.id && null != this.container && (a._resultId = this.generateResultId(this.container, a)), c.extend({}, b, a);
      }, d.prototype.matches = function (a, b) {
        var c = this.options.get("matcher");
        return c(a, b);
      }, d;
    }), b.define("select2/data/array", ["./select", "../utils", "jquery"], function (a, b, c) {
      function d(a, b) {
        var c = b.get("data") || [];
        d.__super__.constructor.call(this, a, b), this.addOptions(this.convertToOptions(c));
      }

      return b.Extend(d, a), d.prototype.select = function (a) {
        var b = this.$element.find("option").filter(function (b, c) {
          return c.value == a.id.toString();
        });
        0 === b.length && (b = this.option(a), this.addOptions(b)), d.__super__.select.call(this, a);
      }, d.prototype.convertToOptions = function (a) {
        function d(a) {
          return function () {
            return c(this).val() == a.id;
          };
        }

        for (var e = this, f = this.$element.find("option"), g = f.map(function () {
          return e.item(c(this)).id;
        }).get(), h = [], i = 0; i < a.length; i++) {
          var j = this._normalizeItem(a[i]);

          if (c.inArray(j.id, g) >= 0) {
            var k = f.filter(d(j)),
                l = this.item(k),
                m = (c.extend(!0, {}, l, j), this.option(l));
            k.replaceWith(m);
          } else {
            var n = this.option(j);

            if (j.children) {
              var o = this.convertToOptions(j.children);
              b.appendMany(n, o);
            }

            h.push(n);
          }
        }

        return h;
      }, d;
    }), b.define("select2/data/ajax", ["./array", "../utils", "jquery"], function (a, b, c) {
      function d(b, c) {
        this.ajaxOptions = this._applyDefaults(c.get("ajax")), null != this.ajaxOptions.processResults && (this.processResults = this.ajaxOptions.processResults), a.__super__.constructor.call(this, b, c);
      }

      return b.Extend(d, a), d.prototype._applyDefaults = function (a) {
        var b = {
          data: function data(a) {
            return {
              q: a.term
            };
          },
          transport: function transport(a, b, d) {
            var e = c.ajax(a);
            return e.then(b), e.fail(d), e;
          }
        };
        return c.extend({}, b, a, !0);
      }, d.prototype.processResults = function (a) {
        return a;
      }, d.prototype.query = function (a, b) {
        function d() {
          var d = f.transport(f, function (d) {
            var f = e.processResults(d, a);
            e.options.get("debug") && window.console && console.error && (f && f.results && c.isArray(f.results) || console.error("Select2: The AJAX results did not return an array in the `results` key of the response.")), b(f);
          }, function () {});
          e._request = d;
        }

        var e = this;
        null != this._request && (c.isFunction(this._request.abort) && this._request.abort(), this._request = null);
        var f = c.extend({
          type: "GET"
        }, this.ajaxOptions);
        "function" == typeof f.url && (f.url = f.url(a)), "function" == typeof f.data && (f.data = f.data(a)), this.ajaxOptions.delay && "" !== a.term ? (this._queryTimeout && window.clearTimeout(this._queryTimeout), this._queryTimeout = window.setTimeout(d, this.ajaxOptions.delay)) : d();
      }, d;
    }), b.define("select2/data/tags", ["jquery"], function (a) {
      function b(b, c, d) {
        var e = d.get("tags"),
            f = d.get("createTag");
        if (void 0 !== f && (this.createTag = f), b.call(this, c, d), a.isArray(e)) for (var g = 0; g < e.length; g++) {
          var h = e[g],
              i = this._normalizeItem(h),
              j = this.option(i);

          this.$element.append(j);
        }
      }

      return b.prototype.query = function (a, b, c) {
        function d(a, f) {
          for (var g = a.results, h = 0; h < g.length; h++) {
            var i = g[h],
                j = null != i.children && !d({
              results: i.children
            }, !0),
                k = i.text === b.term;
            if (k || j) return f ? !1 : (a.data = g, void c(a));
          }

          if (f) return !0;
          var l = e.createTag(b);

          if (null != l) {
            var m = e.option(l);
            m.attr("data-select2-tag", !0), e.addOptions([m]), e.insertTag(g, l);
          }

          a.results = g, c(a);
        }

        var e = this;
        return this._removeOldTags(), null == b.term || null != b.page ? void a.call(this, b, c) : void a.call(this, b, d);
      }, b.prototype.createTag = function (b, c) {
        var d = a.trim(c.term);
        return "" === d ? null : {
          id: d,
          text: d
        };
      }, b.prototype.insertTag = function (a, b, c) {
        b.unshift(c);
      }, b.prototype._removeOldTags = function () {
        var b = (this._lastTag, this.$element.find("option[data-select2-tag]"));
        b.each(function () {
          this.selected || a(this).remove();
        });
      }, b;
    }), b.define("select2/data/tokenizer", ["jquery"], function (a) {
      function b(a, b, c) {
        var d = c.get("tokenizer");
        void 0 !== d && (this.tokenizer = d), a.call(this, b, c);
      }

      return b.prototype.bind = function (a, b, c) {
        a.call(this, b, c), this.$search = b.dropdown.$search || b.selection.$search || c.find(".select2-search__field");
      }, b.prototype.query = function (a, b, c) {
        function d(a) {
          e.select(a);
        }

        var e = this;
        b.term = b.term || "";
        var f = this.tokenizer(b, this.options, d);
        f.term !== b.term && (this.$search.length && (this.$search.val(f.term), this.$search.focus()), b.term = f.term), a.call(this, b, c);
      }, b.prototype.tokenizer = function (b, c, d, e) {
        for (var f = d.get("tokenSeparators") || [], g = c.term, h = 0, i = this.createTag || function (a) {
          return {
            id: a.term,
            text: a.term
          };
        }; h < g.length;) {
          var j = g[h];

          if (-1 !== a.inArray(j, f)) {
            var k = g.substr(0, h),
                l = a.extend({}, c, {
              term: k
            }),
                m = i(l);
            e(m), g = g.substr(h + 1) || "", h = 0;
          } else h++;
        }

        return {
          term: g
        };
      }, b;
    }), b.define("select2/data/minimumInputLength", [], function () {
      function a(a, b, c) {
        this.minimumInputLength = c.get("minimumInputLength"), a.call(this, b, c);
      }

      return a.prototype.query = function (a, b, c) {
        return b.term = b.term || "", b.term.length < this.minimumInputLength ? void this.trigger("results:message", {
          message: "inputTooShort",
          args: {
            minimum: this.minimumInputLength,
            input: b.term,
            params: b
          }
        }) : void a.call(this, b, c);
      }, a;
    }), b.define("select2/data/maximumInputLength", [], function () {
      function a(a, b, c) {
        this.maximumInputLength = c.get("maximumInputLength"), a.call(this, b, c);
      }

      return a.prototype.query = function (a, b, c) {
        return b.term = b.term || "", this.maximumInputLength > 0 && b.term.length > this.maximumInputLength ? void this.trigger("results:message", {
          message: "inputTooLong",
          args: {
            maximum: this.maximumInputLength,
            input: b.term,
            params: b
          }
        }) : void a.call(this, b, c);
      }, a;
    }), b.define("select2/data/maximumSelectionLength", [], function () {
      function a(a, b, c) {
        this.maximumSelectionLength = c.get("maximumSelectionLength"), a.call(this, b, c);
      }

      return a.prototype.query = function (a, b, c) {
        var d = this;
        this.current(function (e) {
          var f = null != e ? e.length : 0;
          return d.maximumSelectionLength > 0 && f >= d.maximumSelectionLength ? void d.trigger("results:message", {
            message: "maximumSelected",
            args: {
              maximum: d.maximumSelectionLength
            }
          }) : void a.call(d, b, c);
        });
      }, a;
    }), b.define("select2/dropdown", ["jquery", "./utils"], function (a, b) {
      function c(a, b) {
        this.$element = a, this.options = b, c.__super__.constructor.call(this);
      }

      return b.Extend(c, b.Observable), c.prototype.render = function () {
        var b = a('<span class="select2-dropdown"><span class="select2-results"></span></span>');
        return b.attr("dir", this.options.get("dir")), this.$dropdown = b, b;
      }, c.prototype.position = function () {}, c.prototype.destroy = function () {
        this.$dropdown.remove();
      }, c;
    }), b.define("select2/dropdown/search", ["jquery", "../utils"], function (a) {
      function b() {}

      return b.prototype.render = function (b) {
        var c = b.call(this),
            d = a('<span class="select2-search select2-search--dropdown"><input class="select2-search__field" type="search" tabindex="-1" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" role="textbox" /></span>');
        return this.$searchContainer = d, this.$search = d.find("input"), c.prepend(d), c;
      }, b.prototype.bind = function (b, c, d) {
        var e = this;
        b.call(this, c, d), this.$search.on("keydown", function (a) {
          e.trigger("keypress", a), e._keyUpPrevented = a.isDefaultPrevented();
        }), this.$search.on("input", function () {
          a(this).off("keyup");
        }), this.$search.on("keyup input", function (a) {
          e.handleSearch(a);
        }), c.on("open", function () {
          e.$search.attr("tabindex", 0), e.$search.focus(), window.setTimeout(function () {
            e.$search.focus();
          }, 0);
        }), c.on("close", function () {
          e.$search.attr("tabindex", -1), e.$search.val("");
        }), c.on("results:all", function (a) {
          if (null == a.query.term || "" === a.query.term) {
            var b = e.showSearch(a);
            b ? e.$searchContainer.removeClass("select2-search--hide") : e.$searchContainer.addClass("select2-search--hide");
          }
        });
      }, b.prototype.handleSearch = function () {
        if (!this._keyUpPrevented) {
          var a = this.$search.val();
          this.trigger("query", {
            term: a
          });
        }

        this._keyUpPrevented = !1;
      }, b.prototype.showSearch = function () {
        return !0;
      }, b;
    }), b.define("select2/dropdown/hidePlaceholder", [], function () {
      function a(a, b, c, d) {
        this.placeholder = this.normalizePlaceholder(c.get("placeholder")), a.call(this, b, c, d);
      }

      return a.prototype.append = function (a, b) {
        b.results = this.removePlaceholder(b.results), a.call(this, b);
      }, a.prototype.normalizePlaceholder = function (a, b) {
        return "string" == typeof b && (b = {
          id: "",
          text: b
        }), b;
      }, a.prototype.removePlaceholder = function (a, b) {
        for (var c = b.slice(0), d = b.length - 1; d >= 0; d--) {
          var e = b[d];
          this.placeholder.id === e.id && c.splice(d, 1);
        }

        return c;
      }, a;
    }), b.define("select2/dropdown/infiniteScroll", ["jquery"], function (a) {
      function b(a, b, c, d) {
        this.lastParams = {}, a.call(this, b, c, d), this.$loadingMore = this.createLoadingMore(), this.loading = !1;
      }

      return b.prototype.append = function (a, b) {
        this.$loadingMore.remove(), this.loading = !1, a.call(this, b), this.showLoadingMore(b) && this.$results.append(this.$loadingMore);
      }, b.prototype.bind = function (b, c, d) {
        var e = this;
        b.call(this, c, d), c.on("query", function (a) {
          e.lastParams = a, e.loading = !0;
        }), c.on("query:append", function (a) {
          e.lastParams = a, e.loading = !0;
        }), this.$results.on("scroll", function () {
          var b = a.contains(document.documentElement, e.$loadingMore[0]);

          if (!e.loading && b) {
            var c = e.$results.offset().top + e.$results.outerHeight(!1),
                d = e.$loadingMore.offset().top + e.$loadingMore.outerHeight(!1);
            c + 50 >= d && e.loadMore();
          }
        });
      }, b.prototype.loadMore = function () {
        this.loading = !0;
        var b = a.extend({}, {
          page: 1
        }, this.lastParams);
        b.page++, this.trigger("query:append", b);
      }, b.prototype.showLoadingMore = function (a, b) {
        return b.pagination && b.pagination.more;
      }, b.prototype.createLoadingMore = function () {
        var b = a('<li class="option load-more" role="treeitem"></li>'),
            c = this.options.get("translations").get("loadingMore");
        return b.html(c(this.lastParams)), b;
      }, b;
    }), b.define("select2/dropdown/attachBody", ["jquery", "../utils"], function (a, b) {
      function c(a, b, c) {
        this.$dropdownParent = c.get("dropdownParent") || document.body, a.call(this, b, c);
      }

      return c.prototype.bind = function (a, b, c) {
        var d = this,
            e = !1;
        a.call(this, b, c), b.on("open", function () {
          d._showDropdown(), d._attachPositioningHandler(b), e || (e = !0, b.on("results:all", function () {
            d._positionDropdown(), d._resizeDropdown();
          }), b.on("results:append", function () {
            d._positionDropdown(), d._resizeDropdown();
          }));
        }), b.on("close", function () {
          d._hideDropdown(), d._detachPositioningHandler(b);
        }), this.$dropdownContainer.on("mousedown", function (a) {
          a.stopPropagation();
        });
      }, c.prototype.position = function (a, b, c) {
        b.attr("class", c.attr("class")), b.removeClass("select2"), b.addClass("select2-container--open"), b.css({
          position: "absolute",
          top: -999999
        }), this.$container = c;
      }, c.prototype.render = function (b) {
        var c = a("<span></span>"),
            d = b.call(this);
        return c.append(d), this.$dropdownContainer = c, c;
      }, c.prototype._hideDropdown = function () {
        this.$dropdownContainer.detach();
      }, c.prototype._attachPositioningHandler = function (c) {
        var d = this,
            e = "scroll.select2." + c.id,
            f = "resize.select2." + c.id,
            g = "orientationchange.select2." + c.id,
            h = this.$container.parents().filter(b.hasScroll);
        h.each(function () {
          a(this).data("select2-scroll-position", {
            x: a(this).scrollLeft(),
            y: a(this).scrollTop()
          });
        }), h.on(e, function () {
          var b = a(this).data("select2-scroll-position");
          a(this).scrollTop(b.y);
        }), a(window).on(e + " " + f + " " + g, function () {
          d._positionDropdown(), d._resizeDropdown();
        });
      }, c.prototype._detachPositioningHandler = function (c) {
        var d = "scroll.select2." + c.id,
            e = "resize.select2." + c.id,
            f = "orientationchange.select2." + c.id,
            g = this.$container.parents().filter(b.hasScroll);
        g.off(d), a(window).off(d + " " + e + " " + f);
      }, c.prototype._positionDropdown = function () {
        var b = a(window),
            c = this.$dropdown.hasClass("select2-dropdown--above"),
            d = this.$dropdown.hasClass("select2-dropdown--below"),
            e = null,
            f = (this.$container.position(), this.$container.offset());
        f.bottom = f.top + this.$container.outerHeight(!1);
        var g = {
          height: this.$container.outerHeight(!1)
        };
        g.top = f.top, g.bottom = f.top + g.height;
        var h = {
          height: this.$dropdown.outerHeight(!1)
        },
            i = {
          top: b.scrollTop(),
          bottom: b.scrollTop() + b.height()
        },
            j = i.top < f.top - h.height,
            k = i.bottom > f.bottom + h.height,
            l = {
          left: f.left,
          top: g.bottom
        };
        c || d || (e = "below"), k || !j || c ? !j && k && c && (e = "below") : e = "above", ("above" == e || c && "below" !== e) && (l.top = g.top - h.height), null != e && (this.$dropdown.removeClass("select2-dropdown--below select2-dropdown--above").addClass("select2-dropdown--" + e), this.$container.removeClass("select2-container--below select2-container--above").addClass("select2-container--" + e)), this.$dropdownContainer.css(l);
      }, c.prototype._resizeDropdown = function () {
        this.$dropdownContainer.width();
        var a = {
          width: this.$container.outerWidth(!1) + "px"
        };
        this.options.get("dropdownAutoWidth") && (a.minWidth = a.width, a.width = "auto"), this.$dropdown.css(a);
      }, c.prototype._showDropdown = function () {
        this.$dropdownContainer.appendTo(this.$dropdownParent), this._positionDropdown(), this._resizeDropdown();
      }, c;
    }), b.define("select2/dropdown/minimumResultsForSearch", [], function () {
      function a(b) {
        for (var c = 0, d = 0; d < b.length; d++) {
          var e = b[d];
          e.children ? c += a(e.children) : c++;
        }

        return c;
      }

      function b(a, b, c, d) {
        this.minimumResultsForSearch = c.get("minimumResultsForSearch"), this.minimumResultsForSearch < 0 && (this.minimumResultsForSearch = 1 / 0), a.call(this, b, c, d);
      }

      return b.prototype.showSearch = function (b, c) {
        return a(c.data.results) < this.minimumResultsForSearch ? !1 : b.call(this, c);
      }, b;
    }), b.define("select2/dropdown/selectOnClose", [], function () {
      function a() {}

      return a.prototype.bind = function (a, b, c) {
        var d = this;
        a.call(this, b, c), b.on("close", function () {
          d._handleSelectOnClose();
        });
      }, a.prototype._handleSelectOnClose = function () {
        var a = this.getHighlightedResults();
        a.length < 1 || this.trigger("select", {
          data: a.data("data")
        });
      }, a;
    }), b.define("select2/dropdown/closeOnSelect", [], function () {
      function a() {}

      return a.prototype.bind = function (a, b, c) {
        var d = this;
        a.call(this, b, c), b.on("select", function (a) {
          d._selectTriggered(a);
        }), b.on("unselect", function (a) {
          d._selectTriggered(a);
        });
      }, a.prototype._selectTriggered = function (a, b) {
        var c = b.originalEvent;
        c && c.ctrlKey || this.trigger("close");
      }, a;
    }), b.define("select2/i18n/en", [], function () {
      return {
        errorLoading: function errorLoading() {
          return "The results could not be loaded.";
        },
        inputTooLong: function inputTooLong(a) {
          var b = a.input.length - a.maximum,
              c = "Please delete " + b + " character";
          return 1 != b && (c += "s"), c;
        },
        inputTooShort: function inputTooShort(a) {
          var b = a.minimum - a.input.length,
              c = "Please enter " + b + " or more characters";
          return c;
        },
        loadingMore: function loadingMore() {
          return "Loading more results…";
        },
        maximumSelected: function maximumSelected(a) {
          var b = "You can only select " + a.maximum + " item";
          return 1 != a.maximum && (b += "s"), b;
        },
        noResults: function noResults() {
          return "No results found";
        },
        searching: function searching() {
          return "Searching…";
        }
      };
    }), b.define("select2/defaults", ["jquery", "require", "./results", "./selection/single", "./selection/multiple", "./selection/placeholder", "./selection/allowClear", "./selection/search", "./selection/eventRelay", "./utils", "./translation", "./diacritics", "./data/select", "./data/array", "./data/ajax", "./data/tags", "./data/tokenizer", "./data/minimumInputLength", "./data/maximumInputLength", "./data/maximumSelectionLength", "./dropdown", "./dropdown/search", "./dropdown/hidePlaceholder", "./dropdown/infiniteScroll", "./dropdown/attachBody", "./dropdown/minimumResultsForSearch", "./dropdown/selectOnClose", "./dropdown/closeOnSelect", "./i18n/en"], function (a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z, A, B, C) {
      function D() {
        this.reset();
      }

      D.prototype.apply = function (l) {
        if (l = a.extend({}, this.defaults, l), null == l.dataAdapter) {
          if (l.dataAdapter = null != l.ajax ? o : null != l.data ? n : m, l.minimumInputLength > 0 && (l.dataAdapter = j.Decorate(l.dataAdapter, r)), l.maximumInputLength > 0 && (l.dataAdapter = j.Decorate(l.dataAdapter, s)), l.maximumSelectionLength > 0 && (l.dataAdapter = j.Decorate(l.dataAdapter, t)), l.tags && (l.dataAdapter = j.Decorate(l.dataAdapter, p)), (null != l.tokenSeparators || null != l.tokenizer) && (l.dataAdapter = j.Decorate(l.dataAdapter, q)), null != l.query) {
            var C = b(l.amdBase + "compat/query");
            l.dataAdapter = j.Decorate(l.dataAdapter, C);
          }

          if (null != l.initSelection) {
            var D = b(l.amdBase + "compat/initSelection");
            l.dataAdapter = j.Decorate(l.dataAdapter, D);
          }
        }

        if (null == l.resultsAdapter && (l.resultsAdapter = c, null != l.ajax && (l.resultsAdapter = j.Decorate(l.resultsAdapter, x)), null != l.placeholder && (l.resultsAdapter = j.Decorate(l.resultsAdapter, w)), l.selectOnClose && (l.resultsAdapter = j.Decorate(l.resultsAdapter, A))), null == l.dropdownAdapter) {
          if (l.multiple) l.dropdownAdapter = u;else {
            var E = j.Decorate(u, v);
            l.dropdownAdapter = E;
          }

          if (0 !== l.minimumResultsForSearch && (l.dropdownAdapter = j.Decorate(l.dropdownAdapter, z)), l.closeOnSelect && (l.dropdownAdapter = j.Decorate(l.dropdownAdapter, B)), null != l.dropdownCssClass || null != l.dropdownCss || null != l.adaptDropdownCssClass) {
            var F = b(l.amdBase + "compat/dropdownCss");
            l.dropdownAdapter = j.Decorate(l.dropdownAdapter, F);
          }

          l.dropdownAdapter = j.Decorate(l.dropdownAdapter, y);
        }

        if (null == l.selectionAdapter) {
          if (l.selectionAdapter = l.multiple ? e : d, null != l.placeholder && (l.selectionAdapter = j.Decorate(l.selectionAdapter, f)), l.allowClear && (l.selectionAdapter = j.Decorate(l.selectionAdapter, g)), l.multiple && (l.selectionAdapter = j.Decorate(l.selectionAdapter, h)), null != l.containerCssClass || null != l.containerCss || null != l.adaptContainerCssClass) {
            var G = b(l.amdBase + "compat/containerCss");
            l.selectionAdapter = j.Decorate(l.selectionAdapter, G);
          }

          l.selectionAdapter = j.Decorate(l.selectionAdapter, i);
        }

        if ("string" == typeof l.language) if (l.language.indexOf("-") > 0) {
          var H = l.language.split("-"),
              I = H[0];
          l.language = [l.language, I];
        } else l.language = [l.language];

        if (a.isArray(l.language)) {
          var J = new k();
          l.language.push("en");

          for (var K = l.language, L = 0; L < K.length; L++) {
            var M = K[L],
                N = {};

            try {
              N = k.loadPath(M);
            } catch (O) {
              try {
                M = this.defaults.amdLanguageBase + M, N = k.loadPath(M);
              } catch (P) {
                l.debug && window.console && console.warn && console.warn('Select2: The language file for "' + M + '" could not be automatically loaded. A fallback will be used instead.');
                continue;
              }
            }

            J.extend(N);
          }

          l.translations = J;
        } else {
          var Q = k.loadPath(this.defaults.amdLanguageBase + "en"),
              R = new k(l.language);
          R.extend(Q), l.translations = R;
        }

        return l;
      }, D.prototype.reset = function () {
        function b(a) {
          function b(a) {
            return l[a] || a;
          }

          return a.replace(/[^\u0000-\u007E]/g, b);
        }

        function c(d, e) {
          if ("" === a.trim(d.term)) return e;

          if (e.children && e.children.length > 0) {
            for (var f = a.extend(!0, {}, e), g = e.children.length - 1; g >= 0; g--) {
              var h = e.children[g],
                  i = c(d, h);
              null == i && f.children.splice(g, 1);
            }

            return f.children.length > 0 ? f : c(d, f);
          }

          var j = b(e.text).toUpperCase(),
              k = b(d.term).toUpperCase();
          return j.indexOf(k) > -1 ? e : null;
        }

        this.defaults = {
          amdBase: "./",
          amdLanguageBase: "./i18n/",
          closeOnSelect: !0,
          debug: !1,
          dropdownAutoWidth: !1,
          escapeMarkup: j.escapeMarkup,
          language: C,
          matcher: c,
          minimumInputLength: 0,
          maximumInputLength: 0,
          maximumSelectionLength: 0,
          minimumResultsForSearch: 0,
          selectOnClose: !1,
          sorter: function sorter(a) {
            return a;
          },
          templateResult: function templateResult(a) {
            return a.text;
          },
          templateSelection: function templateSelection(a) {
            return a.text;
          },
          theme: "default",
          width: "resolve"
        };
      }, D.prototype.set = function (b, c) {
        var d = a.camelCase(b),
            e = {};
        e[d] = c;

        var f = j._convertData(e);

        a.extend(this.defaults, f);
      };
      var E = new D();
      return E;
    }), b.define("select2/options", ["require", "jquery", "./defaults", "./utils"], function (a, b, c, d) {
      function e(b, e) {
        if (this.options = b, null != e && this.fromElement(e), this.options = c.apply(this.options), e && e.is("input")) {
          var f = a(this.get("amdBase") + "compat/inputData");
          this.options.dataAdapter = d.Decorate(this.options.dataAdapter, f);
        }
      }

      return e.prototype.fromElement = function (a) {
        var c = ["select2"];
        null == this.options.multiple && (this.options.multiple = a.prop("multiple")), null == this.options.disabled && (this.options.disabled = a.prop("disabled")), null == this.options.language && (a.prop("lang") ? this.options.language = a.prop("lang").toLowerCase() : a.closest("[lang]").prop("lang") && (this.options.language = a.closest("[lang]").prop("lang"))), null == this.options.dir && (this.options.dir = a.prop("dir") ? a.prop("dir") : a.closest("[dir]").prop("dir") ? a.closest("[dir]").prop("dir") : "ltr"), a.prop("disabled", this.options.disabled), a.prop("multiple", this.options.multiple), a.data("select2Tags") && (this.options.debug && window.console && console.warn && console.warn('Select2: The `data-select2-tags` attribute has been changed to use the `data-data` and `data-tags="true"` attributes and will be removed in future versions of Select2.'), a.data("data", a.data("select2Tags")), a.data("tags", !0)), a.data("ajaxUrl") && (this.options.debug && window.console && console.warn && console.warn("Select2: The `data-ajax-url` attribute has been changed to `data-ajax--url` and support for the old attribute will be removed in future versions of Select2."), a.attr("ajax--url", a.data("ajaxUrl")), a.data("ajax--url", a.data("ajaxUrl")));
        var e = {};
        e = b.fn.jquery && "1." == b.fn.jquery.substr(0, 2) && a[0].dataset ? b.extend(!0, {}, a[0].dataset, a.data()) : a.data();
        var f = b.extend(!0, {}, e);
        f = d._convertData(f);

        for (var g in f) {
          b.inArray(g, c) > -1 || (b.isPlainObject(this.options[g]) ? b.extend(this.options[g], f[g]) : this.options[g] = f[g]);
        }

        return this;
      }, e.prototype.get = function (a) {
        return this.options[a];
      }, e.prototype.set = function (a, b) {
        this.options[a] = b;
      }, e;
    }), b.define("select2/core", ["jquery", "./options", "./utils", "./keys"], function (a, b, c, d) {
      var e = function e(a, c) {
        null != a.data("select2") && a.data("select2").destroy(), this.$element = a, this.id = this._generateId(a), c = c || {}, this.options = new b(c, a), e.__super__.constructor.call(this);
        var d = a.attr("tabindex") || 0;
        a.data("old-tabindex", d), a.attr("tabindex", "-1");
        var f = this.options.get("dataAdapter");
        this.dataAdapter = new f(a, this.options);
        var g = this.render();

        this._placeContainer(g);

        var h = this.options.get("selectionAdapter");
        this.selection = new h(a, this.options), this.$selection = this.selection.render(), this.selection.position(this.$selection, g);
        var i = this.options.get("dropdownAdapter");
        this.dropdown = new i(a, this.options), this.$dropdown = this.dropdown.render(), this.dropdown.position(this.$dropdown, g);
        var j = this.options.get("resultsAdapter");
        this.results = new j(a, this.options, this.dataAdapter), this.$results = this.results.render(), this.results.position(this.$results, this.$dropdown);
        var k = this;
        this._bindAdapters(), this._registerDomEvents(), this._registerDataEvents(), this._registerSelectionEvents(), this._registerDropdownEvents(), this._registerResultsEvents(), this._registerEvents(), this.dataAdapter.current(function (a) {
          k.trigger("selection:update", {
            data: a
          });
        }), a.addClass("select2-hidden-accessible"), a.attr("aria-hidden", "true"), this._syncAttributes(), a.data("select2", this);
      };

      return c.Extend(e, c.Observable), e.prototype._generateId = function (a) {
        var b = "";
        return b = null != a.attr("id") ? a.attr("id") : null != a.attr("name") ? a.attr("name") + "-" + c.generateChars(2) : c.generateChars(4), b = "select2-" + b;
      }, e.prototype._placeContainer = function (a) {
        a.insertAfter(this.$element);

        var b = this._resolveWidth(this.$element, this.options.get("width"));

        null != b && a.css("width", b);
      }, e.prototype._resolveWidth = function (a, b) {
        var c = /^width:(([-+]?([0-9]*\.)?[0-9]+)(px|em|ex|%|in|cm|mm|pt|pc))/i;

        if ("resolve" == b) {
          var d = this._resolveWidth(a, "style");

          return null != d ? d : this._resolveWidth(a, "element");
        }

        if ("element" == b) {
          var e = a.outerWidth(!1);
          return 0 >= e ? "auto" : e + "px";
        }

        if ("style" == b) {
          var f = a.attr("style");
          if ("string" != typeof f) return null;

          for (var g = f.split(";"), h = 0, i = g.length; i > h; h += 1) {
            var j = g[h].replace(/\s/g, ""),
                k = j.match(c);
            if (null !== k && k.length >= 1) return k[1];
          }

          return null;
        }

        return b;
      }, e.prototype._bindAdapters = function () {
        this.dataAdapter.bind(this, this.$container), this.selection.bind(this, this.$container), this.dropdown.bind(this, this.$container), this.results.bind(this, this.$container);
      }, e.prototype._registerDomEvents = function () {
        var b = this;
        this.$element.on("change.select2", function () {
          b.dataAdapter.current(function (a) {
            b.trigger("selection:update", {
              data: a
            });
          });
        }), this._sync = c.bind(this._syncAttributes, this), this.$element[0].attachEvent && this.$element[0].attachEvent("onpropertychange", this._sync);
        var d = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
        null != d ? (this._observer = new d(function (c) {
          a.each(c, b._sync);
        }), this._observer.observe(this.$element[0], {
          attributes: !0,
          subtree: !1
        })) : this.$element[0].addEventListener && this.$element[0].addEventListener("DOMAttrModified", b._sync, !1);
      }, e.prototype._registerDataEvents = function () {
        var a = this;
        this.dataAdapter.on("*", function (b, c) {
          a.trigger(b, c);
        });
      }, e.prototype._registerSelectionEvents = function () {
        var b = this,
            c = ["toggle"];
        this.selection.on("toggle", function () {
          b.toggleDropdown();
        }), this.selection.on("*", function (d, e) {
          -1 === a.inArray(d, c) && b.trigger(d, e);
        });
      }, e.prototype._registerDropdownEvents = function () {
        var a = this;
        this.dropdown.on("*", function (b, c) {
          a.trigger(b, c);
        });
      }, e.prototype._registerResultsEvents = function () {
        var a = this;
        this.results.on("*", function (b, c) {
          a.trigger(b, c);
        });
      }, e.prototype._registerEvents = function () {
        var a = this;
        this.on("open", function () {
          a.$container.addClass("select2-container--open");
        }), this.on("close", function () {
          a.$container.removeClass("select2-container--open");
        }), this.on("enable", function () {
          a.$container.removeClass("select2-container--disabled");
        }), this.on("disable", function () {
          a.$container.addClass("select2-container--disabled");
        }), this.on("focus", function () {
          a.$container.addClass("select2-container--focus");
        }), this.on("blur", function () {
          a.$container.removeClass("select2-container--focus");
        }), this.on("query", function (b) {
          a.isOpen() || a.trigger("open"), this.dataAdapter.query(b, function (c) {
            a.trigger("results:all", {
              data: c,
              query: b
            });
          });
        }), this.on("query:append", function (b) {
          this.dataAdapter.query(b, function (c) {
            a.trigger("results:append", {
              data: c,
              query: b
            });
          });
        }), this.on("keypress", function (b) {
          var c = b.which;
          a.isOpen() ? c === d.ENTER ? (a.trigger("results:select"), b.preventDefault()) : c === d.SPACE && b.ctrlKey ? (a.trigger("results:toggle"), b.preventDefault()) : c === d.UP ? (a.trigger("results:previous"), b.preventDefault()) : c === d.DOWN ? (a.trigger("results:next"), b.preventDefault()) : (c === d.ESC || c === d.TAB) && (a.close(), b.preventDefault()) : (c === d.ENTER || c === d.SPACE || (c === d.DOWN || c === d.UP) && b.altKey) && (a.open(), b.preventDefault());
        });
      }, e.prototype._syncAttributes = function () {
        this.options.set("disabled", this.$element.prop("disabled")), this.options.get("disabled") ? (this.isOpen() && this.close(), this.trigger("disable")) : this.trigger("enable");
      }, e.prototype.trigger = function (a, b) {
        var c = e.__super__.trigger,
            d = {
          open: "opening",
          close: "closing",
          select: "selecting",
          unselect: "unselecting"
        };

        if (a in d) {
          var f = d[a],
              g = {
            prevented: !1,
            name: a,
            args: b
          };
          if (c.call(this, f, g), g.prevented) return void (b.prevented = !0);
        }

        c.call(this, a, b);
      }, e.prototype.toggleDropdown = function () {
        this.options.get("disabled") || (this.isOpen() ? this.close() : this.open());
      }, e.prototype.open = function () {
        this.isOpen() || (this.trigger("query", {}), this.trigger("open"));
      }, e.prototype.close = function () {
        this.isOpen() && this.trigger("close");
      }, e.prototype.isOpen = function () {
        return this.$container.hasClass("select2-container--open");
      }, e.prototype.enable = function (a) {
        this.options.get("debug") && window.console && console.warn && console.warn('Select2: The `select2("enable")` method has been deprecated and will be removed in later Select2 versions. Use $element.prop("disabled") instead.'), (null == a || 0 === a.length) && (a = [!0]);
        var b = !a[0];
        this.$element.prop("disabled", b);
      }, e.prototype.data = function () {
        this.options.get("debug") && arguments.length > 0 && window.console && console.warn && console.warn('Select2: Data can no longer be set using `select2("data")`. You should consider setting the value instead using `$element.val()`.');
        var a = [];
        return this.dataAdapter.current(function (b) {
          a = b;
        }), a;
      }, e.prototype.val = function (b) {
        if (this.options.get("debug") && window.console && console.warn && console.warn('Select2: The `select2("val")` method has been deprecated and will be removed in later Select2 versions. Use $element.val() instead.'), null == b || 0 === b.length) return this.$element.val();
        var c = b[0];
        a.isArray(c) && (c = a.map(c, function (a) {
          return a.toString();
        })), this.$element.val(c).trigger("change");
      }, e.prototype.destroy = function () {
        this.$container.remove(), this.$element[0].detachEvent && this.$element[0].detachEvent("onpropertychange", this._sync), null != this._observer ? (this._observer.disconnect(), this._observer = null) : this.$element[0].removeEventListener && this.$element[0].removeEventListener("DOMAttrModified", this._sync, !1), this._sync = null, this.$element.off(".select2"), this.$element.attr("tabindex", this.$element.data("old-tabindex")), this.$element.removeClass("select2-hidden-accessible"), this.$element.attr("aria-hidden", "false"), this.$element.removeData("select2"), this.dataAdapter.destroy(), this.selection.destroy(), this.dropdown.destroy(), this.results.destroy(), this.dataAdapter = null, this.selection = null, this.dropdown = null, this.results = null;
      }, e.prototype.render = function () {
        var b = a('<span class="select2 select2-container"><span class="selection"></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>');
        return b.attr("dir", this.options.get("dir")), this.$container = b, this.$container.addClass("select2-container--" + this.options.get("theme")), b.data("element", this.$element), b;
      }, e;
    }), b.define("jquery.select2", ["jquery", "require", "./select2/core", "./select2/defaults"], function (a, b, c, d) {
      if (b("jquery.mousewheel"), null == a.fn.select2) {
        var e = ["open", "close", "destroy"];

        a.fn.select2 = function (b) {
          if (b = b || {}, "object" == _typeof(b)) return this.each(function () {
            {
              var d = a.extend({}, b, !0);
              new c(a(this), d);
            }
          }), this;

          if ("string" == typeof b) {
            var d = this.data("select2");
            null == d && window.console && console.error && console.error("The select2('" + b + "') method was called on an element that is not using Select2.");
            var f = Array.prototype.slice.call(arguments, 1),
                g = d[b](f);
            return a.inArray(b, e) > -1 ? this : g;
          }

          throw new Error("Invalid arguments for Select2: " + b);
        };
      }

      return null == a.fn.select2.defaults && (a.fn.select2.defaults = d), c;
    }), b.define("jquery.mousewheel", ["jquery"], function (a) {
      return a;
    }), {
      define: b.define,
      require: b.require
    };
  }(),
      c = b.require("jquery.select2");

  return a.fn.select2.amd = b, c;
});
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

/*
 * This combined file was created by the DataTables downloader builder:
 *   https://datatables.net/download
 *
 * To rebuild or modify this file with the latest versions of the included
 * software please visit:
 *   https://datatables.net/download/#dt/jszip-2.5.0/dt-1.11.5/b-2.2.2/b-colvis-2.2.2/b-html5-2.2.2/b-print-2.2.2/cr-1.5.5/r-2.2.9/sl-1.3.4
 *
 * Included libraries:
 *  JSZip 2.5.0, DataTables 1.11.5, Buttons 2.2.2, Column visibility 2.2.2, HTML5 export 2.2.2, Print view 2.2.2, ColReorder 1.5.5, Responsive 2.2.9, Select 1.3.4
 */

/*!

JSZip - A Javascript class for generating and reading zip files
<http://stuartk.com/jszip>

(c) 2009-2014 Stuart Knightley <stuart [at] stuartk.com>
Dual licenced under the MIT license or GPLv3. See https://raw.github.com/Stuk/jszip/master/LICENSE.markdown.

JSZip uses the library pako released under the MIT license :
https://github.com/nodeca/pako/blob/master/LICENSE
*/
!function (a) {
  if ("object" == (typeof exports === "undefined" ? "undefined" : _typeof(exports)) && "undefined" != typeof module) module.exports = a();else if ("function" == typeof define && define.amd) define([], a);else {
    var b;
    "undefined" != typeof window ? b = window : "undefined" != typeof global ? b = global : "undefined" != typeof self && (b = self), b.JSZip = a();
  }
}(function () {
  return function a(b, c, d) {
    function e(g, h) {
      if (!c[g]) {
        if (!b[g]) {
          var i = "function" == typeof require && require;
          if (!h && i) return i(g, !0);
          if (f) return f(g, !0);
          throw new Error("Cannot find module '" + g + "'");
        }

        var j = c[g] = {
          exports: {}
        };
        b[g][0].call(j.exports, function (a) {
          var c = b[g][1][a];
          return e(c ? c : a);
        }, j, j.exports, a, b, c, d);
      }

      return c[g].exports;
    }

    for (var f = "function" == typeof require && require, g = 0; g < d.length; g++) {
      e(d[g]);
    }

    return e;
  }({
    1: [function (a, b, c) {
      "use strict";

      var d = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
      c.encode = function (a) {
        for (var b, c, e, f, g, h, i, j = "", k = 0; k < a.length;) {
          b = a.charCodeAt(k++), c = a.charCodeAt(k++), e = a.charCodeAt(k++), f = b >> 2, g = (3 & b) << 4 | c >> 4, h = (15 & c) << 2 | e >> 6, i = 63 & e, isNaN(c) ? h = i = 64 : isNaN(e) && (i = 64), j = j + d.charAt(f) + d.charAt(g) + d.charAt(h) + d.charAt(i);
        }

        return j;
      }, c.decode = function (a) {
        var b,
            c,
            e,
            f,
            g,
            h,
            i,
            j = "",
            k = 0;

        for (a = a.replace(/[^A-Za-z0-9\+\/\=]/g, ""); k < a.length;) {
          f = d.indexOf(a.charAt(k++)), g = d.indexOf(a.charAt(k++)), h = d.indexOf(a.charAt(k++)), i = d.indexOf(a.charAt(k++)), b = f << 2 | g >> 4, c = (15 & g) << 4 | h >> 2, e = (3 & h) << 6 | i, j += String.fromCharCode(b), 64 != h && (j += String.fromCharCode(c)), 64 != i && (j += String.fromCharCode(e));
        }

        return j;
      };
    }, {}],
    2: [function (a, b) {
      "use strict";

      function c() {
        this.compressedSize = 0, this.uncompressedSize = 0, this.crc32 = 0, this.compressionMethod = null, this.compressedContent = null;
      }

      c.prototype = {
        getContent: function getContent() {
          return null;
        },
        getCompressedContent: function getCompressedContent() {
          return null;
        }
      }, b.exports = c;
    }, {}],
    3: [function (a, b, c) {
      "use strict";

      c.STORE = {
        magic: "\x00\x00",
        compress: function compress(a) {
          return a;
        },
        uncompress: function uncompress(a) {
          return a;
        },
        compressInputType: null,
        uncompressInputType: null
      }, c.DEFLATE = a("./flate");
    }, {
      "./flate": 8
    }],
    4: [function (a, b) {
      "use strict";

      var c = a("./utils"),
          d = [0, 1996959894, 3993919788, 2567524794, 124634137, 1886057615, 3915621685, 2657392035, 249268274, 2044508324, 3772115230, 2547177864, 162941995, 2125561021, 3887607047, 2428444049, 498536548, 1789927666, 4089016648, 2227061214, 450548861, 1843258603, 4107580753, 2211677639, 325883990, 1684777152, 4251122042, 2321926636, 335633487, 1661365465, 4195302755, 2366115317, 997073096, 1281953886, 3579855332, 2724688242, 1006888145, 1258607687, 3524101629, 2768942443, 901097722, 1119000684, 3686517206, 2898065728, 853044451, 1172266101, 3705015759, 2882616665, 651767980, 1373503546, 3369554304, 3218104598, 565507253, 1454621731, 3485111705, 3099436303, 671266974, 1594198024, 3322730930, 2970347812, 795835527, 1483230225, 3244367275, 3060149565, 1994146192, 31158534, 2563907772, 4023717930, 1907459465, 112637215, 2680153253, 3904427059, 2013776290, 251722036, 2517215374, 3775830040, 2137656763, 141376813, 2439277719, 3865271297, 1802195444, 476864866, 2238001368, 4066508878, 1812370925, 453092731, 2181625025, 4111451223, 1706088902, 314042704, 2344532202, 4240017532, 1658658271, 366619977, 2362670323, 4224994405, 1303535960, 984961486, 2747007092, 3569037538, 1256170817, 1037604311, 2765210733, 3554079995, 1131014506, 879679996, 2909243462, 3663771856, 1141124467, 855842277, 2852801631, 3708648649, 1342533948, 654459306, 3188396048, 3373015174, 1466479909, 544179635, 3110523913, 3462522015, 1591671054, 702138776, 2966460450, 3352799412, 1504918807, 783551873, 3082640443, 3233442989, 3988292384, 2596254646, 62317068, 1957810842, 3939845945, 2647816111, 81470997, 1943803523, 3814918930, 2489596804, 225274430, 2053790376, 3826175755, 2466906013, 167816743, 2097651377, 4027552580, 2265490386, 503444072, 1762050814, 4150417245, 2154129355, 426522225, 1852507879, 4275313526, 2312317920, 282753626, 1742555852, 4189708143, 2394877945, 397917763, 1622183637, 3604390888, 2714866558, 953729732, 1340076626, 3518719985, 2797360999, 1068828381, 1219638859, 3624741850, 2936675148, 906185462, 1090812512, 3747672003, 2825379669, 829329135, 1181335161, 3412177804, 3160834842, 628085408, 1382605366, 3423369109, 3138078467, 570562233, 1426400815, 3317316542, 2998733608, 733239954, 1555261956, 3268935591, 3050360625, 752459403, 1541320221, 2607071920, 3965973030, 1969922972, 40735498, 2617837225, 3943577151, 1913087877, 83908371, 2512341634, 3803740692, 2075208622, 213261112, 2463272603, 3855990285, 2094854071, 198958881, 2262029012, 4057260610, 1759359992, 534414190, 2176718541, 4139329115, 1873836001, 414664567, 2282248934, 4279200368, 1711684554, 285281116, 2405801727, 4167216745, 1634467795, 376229701, 2685067896, 3608007406, 1308918612, 956543938, 2808555105, 3495958263, 1231636301, 1047427035, 2932959818, 3654703836, 1088359270, 936918e3, 2847714899, 3736837829, 1202900863, 817233897, 3183342108, 3401237130, 1404277552, 615818150, 3134207493, 3453421203, 1423857449, 601450431, 3009837614, 3294710456, 1567103746, 711928724, 3020668471, 3272380065, 1510334235, 755167117];

      b.exports = function (a, b) {
        if ("undefined" == typeof a || !a.length) return 0;
        var e = "string" !== c.getTypeOf(a);
        "undefined" == typeof b && (b = 0);
        var f = 0,
            g = 0,
            h = 0;
        b = -1 ^ b;

        for (var i = 0, j = a.length; j > i; i++) {
          h = e ? a[i] : a.charCodeAt(i), g = 255 & (b ^ h), f = d[g], b = b >>> 8 ^ f;
        }

        return -1 ^ b;
      };
    }, {
      "./utils": 21
    }],
    5: [function (a, b) {
      "use strict";

      function c() {
        this.data = null, this.length = 0, this.index = 0;
      }

      var d = a("./utils");
      c.prototype = {
        checkOffset: function checkOffset(a) {
          this.checkIndex(this.index + a);
        },
        checkIndex: function checkIndex(a) {
          if (this.length < a || 0 > a) throw new Error("End of data reached (data length = " + this.length + ", asked index = " + a + "). Corrupted zip ?");
        },
        setIndex: function setIndex(a) {
          this.checkIndex(a), this.index = a;
        },
        skip: function skip(a) {
          this.setIndex(this.index + a);
        },
        byteAt: function byteAt() {},
        readInt: function readInt(a) {
          var b,
              c = 0;

          for (this.checkOffset(a), b = this.index + a - 1; b >= this.index; b--) {
            c = (c << 8) + this.byteAt(b);
          }

          return this.index += a, c;
        },
        readString: function readString(a) {
          return d.transformTo("string", this.readData(a));
        },
        readData: function readData() {},
        lastIndexOfSignature: function lastIndexOfSignature() {},
        readDate: function readDate() {
          var a = this.readInt(4);
          return new Date((a >> 25 & 127) + 1980, (a >> 21 & 15) - 1, a >> 16 & 31, a >> 11 & 31, a >> 5 & 63, (31 & a) << 1);
        }
      }, b.exports = c;
    }, {
      "./utils": 21
    }],
    6: [function (a, b, c) {
      "use strict";

      c.base64 = !1, c.binary = !1, c.dir = !1, c.createFolders = !1, c.date = null, c.compression = null, c.compressionOptions = null, c.comment = null, c.unixPermissions = null, c.dosPermissions = null;
    }, {}],
    7: [function (a, b, c) {
      "use strict";

      var d = a("./utils");
      c.string2binary = function (a) {
        return d.string2binary(a);
      }, c.string2Uint8Array = function (a) {
        return d.transformTo("uint8array", a);
      }, c.uint8Array2String = function (a) {
        return d.transformTo("string", a);
      }, c.string2Blob = function (a) {
        var b = d.transformTo("arraybuffer", a);
        return d.arrayBuffer2Blob(b);
      }, c.arrayBuffer2Blob = function (a) {
        return d.arrayBuffer2Blob(a);
      }, c.transformTo = function (a, b) {
        return d.transformTo(a, b);
      }, c.getTypeOf = function (a) {
        return d.getTypeOf(a);
      }, c.checkSupport = function (a) {
        return d.checkSupport(a);
      }, c.MAX_VALUE_16BITS = d.MAX_VALUE_16BITS, c.MAX_VALUE_32BITS = d.MAX_VALUE_32BITS, c.pretty = function (a) {
        return d.pretty(a);
      }, c.findCompression = function (a) {
        return d.findCompression(a);
      }, c.isRegExp = function (a) {
        return d.isRegExp(a);
      };
    }, {
      "./utils": 21
    }],
    8: [function (a, b, c) {
      "use strict";

      var d = "undefined" != typeof Uint8Array && "undefined" != typeof Uint16Array && "undefined" != typeof Uint32Array,
          e = a("pako");
      c.uncompressInputType = d ? "uint8array" : "array", c.compressInputType = d ? "uint8array" : "array", c.magic = "\b\x00", c.compress = function (a, b) {
        return e.deflateRaw(a, {
          level: b.level || -1
        });
      }, c.uncompress = function (a) {
        return e.inflateRaw(a);
      };
    }, {
      pako: 24
    }],
    9: [function (a, b) {
      "use strict";

      function c(a, b) {
        return this instanceof c ? (this.files = {}, this.comment = null, this.root = "", a && this.load(a, b), void (this.clone = function () {
          var a = new c();

          for (var b in this) {
            "function" != typeof this[b] && (a[b] = this[b]);
          }

          return a;
        })) : new c(a, b);
      }

      var d = a("./base64");
      c.prototype = a("./object"), c.prototype.load = a("./load"), c.support = a("./support"), c.defaults = a("./defaults"), c.utils = a("./deprecatedPublicUtils"), c.base64 = {
        encode: function encode(a) {
          return d.encode(a);
        },
        decode: function decode(a) {
          return d.decode(a);
        }
      }, c.compressions = a("./compressions"), b.exports = c;
    }, {
      "./base64": 1,
      "./compressions": 3,
      "./defaults": 6,
      "./deprecatedPublicUtils": 7,
      "./load": 10,
      "./object": 13,
      "./support": 17
    }],
    10: [function (a, b) {
      "use strict";

      var c = a("./base64"),
          d = a("./zipEntries");

      b.exports = function (a, b) {
        var e, f, g, h;

        for (b = b || {}, b.base64 && (a = c.decode(a)), f = new d(a, b), e = f.files, g = 0; g < e.length; g++) {
          h = e[g], this.file(h.fileName, h.decompressed, {
            binary: !0,
            optimizedBinaryString: !0,
            date: h.date,
            dir: h.dir,
            comment: h.fileComment.length ? h.fileComment : null,
            unixPermissions: h.unixPermissions,
            dosPermissions: h.dosPermissions,
            createFolders: b.createFolders
          });
        }

        return f.zipComment.length && (this.comment = f.zipComment), this;
      };
    }, {
      "./base64": 1,
      "./zipEntries": 22
    }],
    11: [function (a, b) {
      (function (a) {
        "use strict";

        b.exports = function (b, c) {
          return new a(b, c);
        }, b.exports.test = function (b) {
          return a.isBuffer(b);
        };
      }).call(this, "undefined" != typeof Buffer ? Buffer : void 0);
    }, {}],
    12: [function (a, b) {
      "use strict";

      function c(a) {
        this.data = a, this.length = this.data.length, this.index = 0;
      }

      var d = a("./uint8ArrayReader");
      c.prototype = new d(), c.prototype.readData = function (a) {
        this.checkOffset(a);
        var b = this.data.slice(this.index, this.index + a);
        return this.index += a, b;
      }, b.exports = c;
    }, {
      "./uint8ArrayReader": 18
    }],
    13: [function (a, b) {
      "use strict";

      var c = a("./support"),
          d = a("./utils"),
          e = a("./crc32"),
          f = a("./signature"),
          g = a("./defaults"),
          h = a("./base64"),
          i = a("./compressions"),
          j = a("./compressedObject"),
          k = a("./nodeBuffer"),
          l = a("./utf8"),
          m = a("./stringWriter"),
          n = a("./uint8ArrayWriter"),
          o = function o(a) {
        if (a._data instanceof j && (a._data = a._data.getContent(), a.options.binary = !0, a.options.base64 = !1, "uint8array" === d.getTypeOf(a._data))) {
          var b = a._data;
          a._data = new Uint8Array(b.length), 0 !== b.length && a._data.set(b, 0);
        }

        return a._data;
      },
          p = function p(a) {
        var b = o(a),
            e = d.getTypeOf(b);
        return "string" === e ? !a.options.binary && c.nodebuffer ? k(b, "utf-8") : a.asBinary() : b;
      },
          q = function q(a) {
        var b = o(this);
        return null === b || "undefined" == typeof b ? "" : (this.options.base64 && (b = h.decode(b)), b = a && this.options.binary ? D.utf8decode(b) : d.transformTo("string", b), a || this.options.binary || (b = d.transformTo("string", D.utf8encode(b))), b);
      },
          r = function r(a, b, c) {
        this.name = a, this.dir = c.dir, this.date = c.date, this.comment = c.comment, this.unixPermissions = c.unixPermissions, this.dosPermissions = c.dosPermissions, this._data = b, this.options = c, this._initialMetadata = {
          dir: c.dir,
          date: c.date
        };
      };

      r.prototype = {
        asText: function asText() {
          return q.call(this, !0);
        },
        asBinary: function asBinary() {
          return q.call(this, !1);
        },
        asNodeBuffer: function asNodeBuffer() {
          var a = p(this);
          return d.transformTo("nodebuffer", a);
        },
        asUint8Array: function asUint8Array() {
          var a = p(this);
          return d.transformTo("uint8array", a);
        },
        asArrayBuffer: function asArrayBuffer() {
          return this.asUint8Array().buffer;
        }
      };

      var s = function s(a, b) {
        var c,
            d = "";

        for (c = 0; b > c; c++) {
          d += String.fromCharCode(255 & a), a >>>= 8;
        }

        return d;
      },
          t = function t() {
        var a,
            b,
            c = {};

        for (a = 0; a < arguments.length; a++) {
          for (b in arguments[a]) {
            arguments[a].hasOwnProperty(b) && "undefined" == typeof c[b] && (c[b] = arguments[a][b]);
          }
        }

        return c;
      },
          u = function u(a) {
        return a = a || {}, a.base64 !== !0 || null !== a.binary && void 0 !== a.binary || (a.binary = !0), a = t(a, g), a.date = a.date || new Date(), null !== a.compression && (a.compression = a.compression.toUpperCase()), a;
      },
          v = function v(a, b, c) {
        var e,
            f = d.getTypeOf(b);
        if (c = u(c), "string" == typeof c.unixPermissions && (c.unixPermissions = parseInt(c.unixPermissions, 8)), c.unixPermissions && 16384 & c.unixPermissions && (c.dir = !0), c.dosPermissions && 16 & c.dosPermissions && (c.dir = !0), c.dir && (a = x(a)), c.createFolders && (e = w(a)) && y.call(this, e, !0), c.dir || null === b || "undefined" == typeof b) c.base64 = !1, c.binary = !1, b = null, f = null;else if ("string" === f) c.binary && !c.base64 && c.optimizedBinaryString !== !0 && (b = d.string2binary(b));else {
          if (c.base64 = !1, c.binary = !0, !(f || b instanceof j)) throw new Error("The data of '" + a + "' is in an unsupported format !");
          "arraybuffer" === f && (b = d.transformTo("uint8array", b));
        }
        var g = new r(a, b, c);
        return this.files[a] = g, g;
      },
          w = function w(a) {
        "/" == a.slice(-1) && (a = a.substring(0, a.length - 1));
        var b = a.lastIndexOf("/");
        return b > 0 ? a.substring(0, b) : "";
      },
          x = function x(a) {
        return "/" != a.slice(-1) && (a += "/"), a;
      },
          y = function y(a, b) {
        return b = "undefined" != typeof b ? b : !1, a = x(a), this.files[a] || v.call(this, a, null, {
          dir: !0,
          createFolders: b
        }), this.files[a];
      },
          z = function z(a, b, c) {
        var f,
            g = new j();
        return a._data instanceof j ? (g.uncompressedSize = a._data.uncompressedSize, g.crc32 = a._data.crc32, 0 === g.uncompressedSize || a.dir ? (b = i.STORE, g.compressedContent = "", g.crc32 = 0) : a._data.compressionMethod === b.magic ? g.compressedContent = a._data.getCompressedContent() : (f = a._data.getContent(), g.compressedContent = b.compress(d.transformTo(b.compressInputType, f), c))) : (f = p(a), (!f || 0 === f.length || a.dir) && (b = i.STORE, f = ""), g.uncompressedSize = f.length, g.crc32 = e(f), g.compressedContent = b.compress(d.transformTo(b.compressInputType, f), c)), g.compressedSize = g.compressedContent.length, g.compressionMethod = b.magic, g;
      },
          A = function A(a, b) {
        var c = a;
        return a || (c = b ? 16893 : 33204), (65535 & c) << 16;
      },
          B = function B(a) {
        return 63 & (a || 0);
      },
          C = function C(a, b, c, g, h) {
        var i,
            j,
            k,
            m,
            n = (c.compressedContent, d.transformTo("string", l.utf8encode(b.name))),
            o = b.comment || "",
            p = d.transformTo("string", l.utf8encode(o)),
            q = n.length !== b.name.length,
            r = p.length !== o.length,
            t = b.options,
            u = "",
            v = "",
            w = "";
        k = b._initialMetadata.dir !== b.dir ? b.dir : t.dir, m = b._initialMetadata.date !== b.date ? b.date : t.date;
        var x = 0,
            y = 0;
        k && (x |= 16), "UNIX" === h ? (y = 798, x |= A(b.unixPermissions, k)) : (y = 20, x |= B(b.dosPermissions, k)), i = m.getHours(), i <<= 6, i |= m.getMinutes(), i <<= 5, i |= m.getSeconds() / 2, j = m.getFullYear() - 1980, j <<= 4, j |= m.getMonth() + 1, j <<= 5, j |= m.getDate(), q && (v = s(1, 1) + s(e(n), 4) + n, u += "up" + s(v.length, 2) + v), r && (w = s(1, 1) + s(this.crc32(p), 4) + p, u += "uc" + s(w.length, 2) + w);
        var z = "";
        z += "\n\x00", z += q || r ? "\x00\b" : "\x00\x00", z += c.compressionMethod, z += s(i, 2), z += s(j, 2), z += s(c.crc32, 4), z += s(c.compressedSize, 4), z += s(c.uncompressedSize, 4), z += s(n.length, 2), z += s(u.length, 2);
        var C = f.LOCAL_FILE_HEADER + z + n + u,
            D = f.CENTRAL_FILE_HEADER + s(y, 2) + z + s(p.length, 2) + "\x00\x00\x00\x00" + s(x, 4) + s(g, 4) + n + u + p;
        return {
          fileRecord: C,
          dirRecord: D,
          compressedObject: c
        };
      },
          D = {
        load: function load() {
          throw new Error("Load method is not defined. Is the file jszip-load.js included ?");
        },
        filter: function filter(a) {
          var b,
              c,
              d,
              e,
              f = [];

          for (b in this.files) {
            this.files.hasOwnProperty(b) && (d = this.files[b], e = new r(d.name, d._data, t(d.options)), c = b.slice(this.root.length, b.length), b.slice(0, this.root.length) === this.root && a(c, e) && f.push(e));
          }

          return f;
        },
        file: function file(a, b, c) {
          if (1 === arguments.length) {
            if (d.isRegExp(a)) {
              var e = a;
              return this.filter(function (a, b) {
                return !b.dir && e.test(a);
              });
            }

            return this.filter(function (b, c) {
              return !c.dir && b === a;
            })[0] || null;
          }

          return a = this.root + a, v.call(this, a, b, c), this;
        },
        folder: function folder(a) {
          if (!a) return this;
          if (d.isRegExp(a)) return this.filter(function (b, c) {
            return c.dir && a.test(b);
          });
          var b = this.root + a,
              c = y.call(this, b),
              e = this.clone();
          return e.root = c.name, e;
        },
        remove: function remove(a) {
          a = this.root + a;
          var b = this.files[a];
          if (b || ("/" != a.slice(-1) && (a += "/"), b = this.files[a]), b && !b.dir) delete this.files[a];else for (var c = this.filter(function (b, c) {
            return c.name.slice(0, a.length) === a;
          }), d = 0; d < c.length; d++) {
            delete this.files[c[d].name];
          }
          return this;
        },
        generate: function generate(a) {
          a = t(a || {}, {
            base64: !0,
            compression: "STORE",
            compressionOptions: null,
            type: "base64",
            platform: "DOS",
            comment: null,
            mimeType: "application/zip"
          }), d.checkSupport(a.type), ("darwin" === a.platform || "freebsd" === a.platform || "linux" === a.platform || "sunos" === a.platform) && (a.platform = "UNIX"), "win32" === a.platform && (a.platform = "DOS");
          var b,
              c,
              e = [],
              g = 0,
              j = 0,
              k = d.transformTo("string", this.utf8encode(a.comment || this.comment || ""));

          for (var l in this.files) {
            if (this.files.hasOwnProperty(l)) {
              var o = this.files[l],
                  p = o.options.compression || a.compression.toUpperCase(),
                  q = i[p];
              if (!q) throw new Error(p + " is not a valid compression method !");
              var r = o.options.compressionOptions || a.compressionOptions || {},
                  u = z.call(this, o, q, r),
                  v = C.call(this, l, o, u, g, a.platform);
              g += v.fileRecord.length + u.compressedSize, j += v.dirRecord.length, e.push(v);
            }
          }

          var w = "";
          w = f.CENTRAL_DIRECTORY_END + "\x00\x00\x00\x00" + s(e.length, 2) + s(e.length, 2) + s(j, 4) + s(g, 4) + s(k.length, 2) + k;
          var x = a.type.toLowerCase();

          for (b = "uint8array" === x || "arraybuffer" === x || "blob" === x || "nodebuffer" === x ? new n(g + j + w.length) : new m(g + j + w.length), c = 0; c < e.length; c++) {
            b.append(e[c].fileRecord), b.append(e[c].compressedObject.compressedContent);
          }

          for (c = 0; c < e.length; c++) {
            b.append(e[c].dirRecord);
          }

          b.append(w);
          var y = b.finalize();

          switch (a.type.toLowerCase()) {
            case "uint8array":
            case "arraybuffer":
            case "nodebuffer":
              return d.transformTo(a.type.toLowerCase(), y);

            case "blob":
              return d.arrayBuffer2Blob(d.transformTo("arraybuffer", y), a.mimeType);

            case "base64":
              return a.base64 ? h.encode(y) : y;

            default:
              return y;
          }
        },
        crc32: function crc32(a, b) {
          return e(a, b);
        },
        utf8encode: function utf8encode(a) {
          return d.transformTo("string", l.utf8encode(a));
        },
        utf8decode: function utf8decode(a) {
          return l.utf8decode(a);
        }
      };

      b.exports = D;
    }, {
      "./base64": 1,
      "./compressedObject": 2,
      "./compressions": 3,
      "./crc32": 4,
      "./defaults": 6,
      "./nodeBuffer": 11,
      "./signature": 14,
      "./stringWriter": 16,
      "./support": 17,
      "./uint8ArrayWriter": 19,
      "./utf8": 20,
      "./utils": 21
    }],
    14: [function (a, b, c) {
      "use strict";

      c.LOCAL_FILE_HEADER = "PK", c.CENTRAL_FILE_HEADER = "PK", c.CENTRAL_DIRECTORY_END = "PK", c.ZIP64_CENTRAL_DIRECTORY_LOCATOR = "PK", c.ZIP64_CENTRAL_DIRECTORY_END = "PK", c.DATA_DESCRIPTOR = "PK\b";
    }, {}],
    15: [function (a, b) {
      "use strict";

      function c(a, b) {
        this.data = a, b || (this.data = e.string2binary(this.data)), this.length = this.data.length, this.index = 0;
      }

      var d = a("./dataReader"),
          e = a("./utils");
      c.prototype = new d(), c.prototype.byteAt = function (a) {
        return this.data.charCodeAt(a);
      }, c.prototype.lastIndexOfSignature = function (a) {
        return this.data.lastIndexOf(a);
      }, c.prototype.readData = function (a) {
        this.checkOffset(a);
        var b = this.data.slice(this.index, this.index + a);
        return this.index += a, b;
      }, b.exports = c;
    }, {
      "./dataReader": 5,
      "./utils": 21
    }],
    16: [function (a, b) {
      "use strict";

      var c = a("./utils"),
          d = function d() {
        this.data = [];
      };

      d.prototype = {
        append: function append(a) {
          a = c.transformTo("string", a), this.data.push(a);
        },
        finalize: function finalize() {
          return this.data.join("");
        }
      }, b.exports = d;
    }, {
      "./utils": 21
    }],
    17: [function (a, b, c) {
      (function (a) {
        "use strict";

        if (c.base64 = !0, c.array = !0, c.string = !0, c.arraybuffer = "undefined" != typeof ArrayBuffer && "undefined" != typeof Uint8Array, c.nodebuffer = "undefined" != typeof a, c.uint8array = "undefined" != typeof Uint8Array, "undefined" == typeof ArrayBuffer) c.blob = !1;else {
          var b = new ArrayBuffer(0);

          try {
            c.blob = 0 === new Blob([b], {
              type: "application/zip"
            }).size;
          } catch (d) {
            try {
              var e = window.BlobBuilder || window.WebKitBlobBuilder || window.MozBlobBuilder || window.MSBlobBuilder,
                  f = new e();
              f.append(b), c.blob = 0 === f.getBlob("application/zip").size;
            } catch (d) {
              c.blob = !1;
            }
          }
        }
      }).call(this, "undefined" != typeof Buffer ? Buffer : void 0);
    }, {}],
    18: [function (a, b) {
      "use strict";

      function c(a) {
        a && (this.data = a, this.length = this.data.length, this.index = 0);
      }

      var d = a("./dataReader");
      c.prototype = new d(), c.prototype.byteAt = function (a) {
        return this.data[a];
      }, c.prototype.lastIndexOfSignature = function (a) {
        for (var b = a.charCodeAt(0), c = a.charCodeAt(1), d = a.charCodeAt(2), e = a.charCodeAt(3), f = this.length - 4; f >= 0; --f) {
          if (this.data[f] === b && this.data[f + 1] === c && this.data[f + 2] === d && this.data[f + 3] === e) return f;
        }

        return -1;
      }, c.prototype.readData = function (a) {
        if (this.checkOffset(a), 0 === a) return new Uint8Array(0);
        var b = this.data.subarray(this.index, this.index + a);
        return this.index += a, b;
      }, b.exports = c;
    }, {
      "./dataReader": 5
    }],
    19: [function (a, b) {
      "use strict";

      var c = a("./utils"),
          d = function d(a) {
        this.data = new Uint8Array(a), this.index = 0;
      };

      d.prototype = {
        append: function append(a) {
          0 !== a.length && (a = c.transformTo("uint8array", a), this.data.set(a, this.index), this.index += a.length);
        },
        finalize: function finalize() {
          return this.data;
        }
      }, b.exports = d;
    }, {
      "./utils": 21
    }],
    20: [function (a, b, c) {
      "use strict";

      for (var d = a("./utils"), e = a("./support"), f = a("./nodeBuffer"), g = new Array(256), h = 0; 256 > h; h++) {
        g[h] = h >= 252 ? 6 : h >= 248 ? 5 : h >= 240 ? 4 : h >= 224 ? 3 : h >= 192 ? 2 : 1;
      }

      g[254] = g[254] = 1;

      var i = function i(a) {
        var b,
            c,
            d,
            f,
            g,
            h = a.length,
            i = 0;

        for (f = 0; h > f; f++) {
          c = a.charCodeAt(f), 55296 === (64512 & c) && h > f + 1 && (d = a.charCodeAt(f + 1), 56320 === (64512 & d) && (c = 65536 + (c - 55296 << 10) + (d - 56320), f++)), i += 128 > c ? 1 : 2048 > c ? 2 : 65536 > c ? 3 : 4;
        }

        for (b = e.uint8array ? new Uint8Array(i) : new Array(i), g = 0, f = 0; i > g; f++) {
          c = a.charCodeAt(f), 55296 === (64512 & c) && h > f + 1 && (d = a.charCodeAt(f + 1), 56320 === (64512 & d) && (c = 65536 + (c - 55296 << 10) + (d - 56320), f++)), 128 > c ? b[g++] = c : 2048 > c ? (b[g++] = 192 | c >>> 6, b[g++] = 128 | 63 & c) : 65536 > c ? (b[g++] = 224 | c >>> 12, b[g++] = 128 | c >>> 6 & 63, b[g++] = 128 | 63 & c) : (b[g++] = 240 | c >>> 18, b[g++] = 128 | c >>> 12 & 63, b[g++] = 128 | c >>> 6 & 63, b[g++] = 128 | 63 & c);
        }

        return b;
      },
          j = function j(a, b) {
        var c;

        for (b = b || a.length, b > a.length && (b = a.length), c = b - 1; c >= 0 && 128 === (192 & a[c]);) {
          c--;
        }

        return 0 > c ? b : 0 === c ? b : c + g[a[c]] > b ? c : b;
      },
          k = function k(a) {
        var b,
            c,
            e,
            f,
            h = a.length,
            i = new Array(2 * h);

        for (c = 0, b = 0; h > b;) {
          if (e = a[b++], 128 > e) i[c++] = e;else if (f = g[e], f > 4) i[c++] = 65533, b += f - 1;else {
            for (e &= 2 === f ? 31 : 3 === f ? 15 : 7; f > 1 && h > b;) {
              e = e << 6 | 63 & a[b++], f--;
            }

            f > 1 ? i[c++] = 65533 : 65536 > e ? i[c++] = e : (e -= 65536, i[c++] = 55296 | e >> 10 & 1023, i[c++] = 56320 | 1023 & e);
          }
        }

        return i.length !== c && (i.subarray ? i = i.subarray(0, c) : i.length = c), d.applyFromCharCode(i);
      };

      c.utf8encode = function (a) {
        return e.nodebuffer ? f(a, "utf-8") : i(a);
      }, c.utf8decode = function (a) {
        if (e.nodebuffer) return d.transformTo("nodebuffer", a).toString("utf-8");
        a = d.transformTo(e.uint8array ? "uint8array" : "array", a);

        for (var b = [], c = 0, f = a.length, g = 65536; f > c;) {
          var h = j(a, Math.min(c + g, f));
          b.push(e.uint8array ? k(a.subarray(c, h)) : k(a.slice(c, h))), c = h;
        }

        return b.join("");
      };
    }, {
      "./nodeBuffer": 11,
      "./support": 17,
      "./utils": 21
    }],
    21: [function (a, b, c) {
      "use strict";

      function d(a) {
        return a;
      }

      function e(a, b) {
        for (var c = 0; c < a.length; ++c) {
          b[c] = 255 & a.charCodeAt(c);
        }

        return b;
      }

      function f(a) {
        var b = 65536,
            d = [],
            e = a.length,
            f = c.getTypeOf(a),
            g = 0,
            h = !0;

        try {
          switch (f) {
            case "uint8array":
              String.fromCharCode.apply(null, new Uint8Array(0));
              break;

            case "nodebuffer":
              String.fromCharCode.apply(null, j(0));
          }
        } catch (i) {
          h = !1;
        }

        if (!h) {
          for (var k = "", l = 0; l < a.length; l++) {
            k += String.fromCharCode(a[l]);
          }

          return k;
        }

        for (; e > g && b > 1;) {
          try {
            d.push("array" === f || "nodebuffer" === f ? String.fromCharCode.apply(null, a.slice(g, Math.min(g + b, e))) : String.fromCharCode.apply(null, a.subarray(g, Math.min(g + b, e)))), g += b;
          } catch (i) {
            b = Math.floor(b / 2);
          }
        }

        return d.join("");
      }

      function g(a, b) {
        for (var c = 0; c < a.length; c++) {
          b[c] = a[c];
        }

        return b;
      }

      var h = a("./support"),
          i = a("./compressions"),
          j = a("./nodeBuffer");
      c.string2binary = function (a) {
        for (var b = "", c = 0; c < a.length; c++) {
          b += String.fromCharCode(255 & a.charCodeAt(c));
        }

        return b;
      }, c.arrayBuffer2Blob = function (a, b) {
        c.checkSupport("blob"), b = b || "application/zip";

        try {
          return new Blob([a], {
            type: b
          });
        } catch (d) {
          try {
            var e = window.BlobBuilder || window.WebKitBlobBuilder || window.MozBlobBuilder || window.MSBlobBuilder,
                f = new e();
            return f.append(a), f.getBlob(b);
          } catch (d) {
            throw new Error("Bug : can't construct the Blob.");
          }
        }
      }, c.applyFromCharCode = f;
      var k = {};
      k.string = {
        string: d,
        array: function array(a) {
          return e(a, new Array(a.length));
        },
        arraybuffer: function arraybuffer(a) {
          return k.string.uint8array(a).buffer;
        },
        uint8array: function uint8array(a) {
          return e(a, new Uint8Array(a.length));
        },
        nodebuffer: function nodebuffer(a) {
          return e(a, j(a.length));
        }
      }, k.array = {
        string: f,
        array: d,
        arraybuffer: function arraybuffer(a) {
          return new Uint8Array(a).buffer;
        },
        uint8array: function uint8array(a) {
          return new Uint8Array(a);
        },
        nodebuffer: function nodebuffer(a) {
          return j(a);
        }
      }, k.arraybuffer = {
        string: function string(a) {
          return f(new Uint8Array(a));
        },
        array: function array(a) {
          return g(new Uint8Array(a), new Array(a.byteLength));
        },
        arraybuffer: d,
        uint8array: function uint8array(a) {
          return new Uint8Array(a);
        },
        nodebuffer: function nodebuffer(a) {
          return j(new Uint8Array(a));
        }
      }, k.uint8array = {
        string: f,
        array: function array(a) {
          return g(a, new Array(a.length));
        },
        arraybuffer: function arraybuffer(a) {
          return a.buffer;
        },
        uint8array: d,
        nodebuffer: function nodebuffer(a) {
          return j(a);
        }
      }, k.nodebuffer = {
        string: f,
        array: function array(a) {
          return g(a, new Array(a.length));
        },
        arraybuffer: function arraybuffer(a) {
          return k.nodebuffer.uint8array(a).buffer;
        },
        uint8array: function uint8array(a) {
          return g(a, new Uint8Array(a.length));
        },
        nodebuffer: d
      }, c.transformTo = function (a, b) {
        if (b || (b = ""), !a) return b;
        c.checkSupport(a);
        var d = c.getTypeOf(b),
            e = k[d][a](b);
        return e;
      }, c.getTypeOf = function (a) {
        return "string" == typeof a ? "string" : "[object Array]" === Object.prototype.toString.call(a) ? "array" : h.nodebuffer && j.test(a) ? "nodebuffer" : h.uint8array && a instanceof Uint8Array ? "uint8array" : h.arraybuffer && a instanceof ArrayBuffer ? "arraybuffer" : void 0;
      }, c.checkSupport = function (a) {
        var b = h[a.toLowerCase()];
        if (!b) throw new Error(a + " is not supported by this browser");
      }, c.MAX_VALUE_16BITS = 65535, c.MAX_VALUE_32BITS = -1, c.pretty = function (a) {
        var b,
            c,
            d = "";

        for (c = 0; c < (a || "").length; c++) {
          b = a.charCodeAt(c), d += "\\x" + (16 > b ? "0" : "") + b.toString(16).toUpperCase();
        }

        return d;
      }, c.findCompression = function (a) {
        for (var b in i) {
          if (i.hasOwnProperty(b) && i[b].magic === a) return i[b];
        }

        return null;
      }, c.isRegExp = function (a) {
        return "[object RegExp]" === Object.prototype.toString.call(a);
      };
    }, {
      "./compressions": 3,
      "./nodeBuffer": 11,
      "./support": 17
    }],
    22: [function (a, b) {
      "use strict";

      function c(a, b) {
        this.files = [], this.loadOptions = b, a && this.load(a);
      }

      var d = a("./stringReader"),
          e = a("./nodeBufferReader"),
          f = a("./uint8ArrayReader"),
          g = a("./utils"),
          h = a("./signature"),
          i = a("./zipEntry"),
          j = a("./support"),
          k = a("./object");
      c.prototype = {
        checkSignature: function checkSignature(a) {
          var b = this.reader.readString(4);
          if (b !== a) throw new Error("Corrupted zip or bug : unexpected signature (" + g.pretty(b) + ", expected " + g.pretty(a) + ")");
        },
        readBlockEndOfCentral: function readBlockEndOfCentral() {
          this.diskNumber = this.reader.readInt(2), this.diskWithCentralDirStart = this.reader.readInt(2), this.centralDirRecordsOnThisDisk = this.reader.readInt(2), this.centralDirRecords = this.reader.readInt(2), this.centralDirSize = this.reader.readInt(4), this.centralDirOffset = this.reader.readInt(4), this.zipCommentLength = this.reader.readInt(2), this.zipComment = this.reader.readString(this.zipCommentLength), this.zipComment = k.utf8decode(this.zipComment);
        },
        readBlockZip64EndOfCentral: function readBlockZip64EndOfCentral() {
          this.zip64EndOfCentralSize = this.reader.readInt(8), this.versionMadeBy = this.reader.readString(2), this.versionNeeded = this.reader.readInt(2), this.diskNumber = this.reader.readInt(4), this.diskWithCentralDirStart = this.reader.readInt(4), this.centralDirRecordsOnThisDisk = this.reader.readInt(8), this.centralDirRecords = this.reader.readInt(8), this.centralDirSize = this.reader.readInt(8), this.centralDirOffset = this.reader.readInt(8), this.zip64ExtensibleData = {};

          for (var a, b, c, d = this.zip64EndOfCentralSize - 44, e = 0; d > e;) {
            a = this.reader.readInt(2), b = this.reader.readInt(4), c = this.reader.readString(b), this.zip64ExtensibleData[a] = {
              id: a,
              length: b,
              value: c
            };
          }
        },
        readBlockZip64EndOfCentralLocator: function readBlockZip64EndOfCentralLocator() {
          if (this.diskWithZip64CentralDirStart = this.reader.readInt(4), this.relativeOffsetEndOfZip64CentralDir = this.reader.readInt(8), this.disksCount = this.reader.readInt(4), this.disksCount > 1) throw new Error("Multi-volumes zip are not supported");
        },
        readLocalFiles: function readLocalFiles() {
          var a, b;

          for (a = 0; a < this.files.length; a++) {
            b = this.files[a], this.reader.setIndex(b.localHeaderOffset), this.checkSignature(h.LOCAL_FILE_HEADER), b.readLocalPart(this.reader), b.handleUTF8(), b.processAttributes();
          }
        },
        readCentralDir: function readCentralDir() {
          var a;

          for (this.reader.setIndex(this.centralDirOffset); this.reader.readString(4) === h.CENTRAL_FILE_HEADER;) {
            a = new i({
              zip64: this.zip64
            }, this.loadOptions), a.readCentralPart(this.reader), this.files.push(a);
          }
        },
        readEndOfCentral: function readEndOfCentral() {
          var a = this.reader.lastIndexOfSignature(h.CENTRAL_DIRECTORY_END);

          if (-1 === a) {
            var b = !0;

            try {
              this.reader.setIndex(0), this.checkSignature(h.LOCAL_FILE_HEADER), b = !1;
            } catch (c) {}

            throw new Error(b ? "Can't find end of central directory : is this a zip file ? If it is, see http://stuk.github.io/jszip/documentation/howto/read_zip.html" : "Corrupted zip : can't find end of central directory");
          }

          if (this.reader.setIndex(a), this.checkSignature(h.CENTRAL_DIRECTORY_END), this.readBlockEndOfCentral(), this.diskNumber === g.MAX_VALUE_16BITS || this.diskWithCentralDirStart === g.MAX_VALUE_16BITS || this.centralDirRecordsOnThisDisk === g.MAX_VALUE_16BITS || this.centralDirRecords === g.MAX_VALUE_16BITS || this.centralDirSize === g.MAX_VALUE_32BITS || this.centralDirOffset === g.MAX_VALUE_32BITS) {
            if (this.zip64 = !0, a = this.reader.lastIndexOfSignature(h.ZIP64_CENTRAL_DIRECTORY_LOCATOR), -1 === a) throw new Error("Corrupted zip : can't find the ZIP64 end of central directory locator");
            this.reader.setIndex(a), this.checkSignature(h.ZIP64_CENTRAL_DIRECTORY_LOCATOR), this.readBlockZip64EndOfCentralLocator(), this.reader.setIndex(this.relativeOffsetEndOfZip64CentralDir), this.checkSignature(h.ZIP64_CENTRAL_DIRECTORY_END), this.readBlockZip64EndOfCentral();
          }
        },
        prepareReader: function prepareReader(a) {
          var b = g.getTypeOf(a);
          this.reader = "string" !== b || j.uint8array ? "nodebuffer" === b ? new e(a) : new f(g.transformTo("uint8array", a)) : new d(a, this.loadOptions.optimizedBinaryString);
        },
        load: function load(a) {
          this.prepareReader(a), this.readEndOfCentral(), this.readCentralDir(), this.readLocalFiles();
        }
      }, b.exports = c;
    }, {
      "./nodeBufferReader": 12,
      "./object": 13,
      "./signature": 14,
      "./stringReader": 15,
      "./support": 17,
      "./uint8ArrayReader": 18,
      "./utils": 21,
      "./zipEntry": 23
    }],
    23: [function (a, b) {
      "use strict";

      function c(a, b) {
        this.options = a, this.loadOptions = b;
      }

      var d = a("./stringReader"),
          e = a("./utils"),
          f = a("./compressedObject"),
          g = a("./object"),
          h = 0,
          i = 3;
      c.prototype = {
        isEncrypted: function isEncrypted() {
          return 1 === (1 & this.bitFlag);
        },
        useUTF8: function useUTF8() {
          return 2048 === (2048 & this.bitFlag);
        },
        prepareCompressedContent: function prepareCompressedContent(a, b, c) {
          return function () {
            var d = a.index;
            a.setIndex(b);
            var e = a.readData(c);
            return a.setIndex(d), e;
          };
        },
        prepareContent: function prepareContent(a, b, c, d, f) {
          return function () {
            var a = e.transformTo(d.uncompressInputType, this.getCompressedContent()),
                b = d.uncompress(a);
            if (b.length !== f) throw new Error("Bug : uncompressed data size mismatch");
            return b;
          };
        },
        readLocalPart: function readLocalPart(a) {
          var b, c;
          if (a.skip(22), this.fileNameLength = a.readInt(2), c = a.readInt(2), this.fileName = a.readString(this.fileNameLength), a.skip(c), -1 == this.compressedSize || -1 == this.uncompressedSize) throw new Error("Bug or corrupted zip : didn't get enough informations from the central directory (compressedSize == -1 || uncompressedSize == -1)");
          if (b = e.findCompression(this.compressionMethod), null === b) throw new Error("Corrupted zip : compression " + e.pretty(this.compressionMethod) + " unknown (inner file : " + this.fileName + ")");
          if (this.decompressed = new f(), this.decompressed.compressedSize = this.compressedSize, this.decompressed.uncompressedSize = this.uncompressedSize, this.decompressed.crc32 = this.crc32, this.decompressed.compressionMethod = this.compressionMethod, this.decompressed.getCompressedContent = this.prepareCompressedContent(a, a.index, this.compressedSize, b), this.decompressed.getContent = this.prepareContent(a, a.index, this.compressedSize, b, this.uncompressedSize), this.loadOptions.checkCRC32 && (this.decompressed = e.transformTo("string", this.decompressed.getContent()), g.crc32(this.decompressed) !== this.crc32)) throw new Error("Corrupted zip : CRC32 mismatch");
        },
        readCentralPart: function readCentralPart(a) {
          if (this.versionMadeBy = a.readInt(2), this.versionNeeded = a.readInt(2), this.bitFlag = a.readInt(2), this.compressionMethod = a.readString(2), this.date = a.readDate(), this.crc32 = a.readInt(4), this.compressedSize = a.readInt(4), this.uncompressedSize = a.readInt(4), this.fileNameLength = a.readInt(2), this.extraFieldsLength = a.readInt(2), this.fileCommentLength = a.readInt(2), this.diskNumberStart = a.readInt(2), this.internalFileAttributes = a.readInt(2), this.externalFileAttributes = a.readInt(4), this.localHeaderOffset = a.readInt(4), this.isEncrypted()) throw new Error("Encrypted zip are not supported");
          this.fileName = a.readString(this.fileNameLength), this.readExtraFields(a), this.parseZIP64ExtraField(a), this.fileComment = a.readString(this.fileCommentLength);
        },
        processAttributes: function processAttributes() {
          this.unixPermissions = null, this.dosPermissions = null;
          var a = this.versionMadeBy >> 8;
          this.dir = 16 & this.externalFileAttributes ? !0 : !1, a === h && (this.dosPermissions = 63 & this.externalFileAttributes), a === i && (this.unixPermissions = this.externalFileAttributes >> 16 & 65535), this.dir || "/" !== this.fileName.slice(-1) || (this.dir = !0);
        },
        parseZIP64ExtraField: function parseZIP64ExtraField() {
          if (this.extraFields[1]) {
            var a = new d(this.extraFields[1].value);
            this.uncompressedSize === e.MAX_VALUE_32BITS && (this.uncompressedSize = a.readInt(8)), this.compressedSize === e.MAX_VALUE_32BITS && (this.compressedSize = a.readInt(8)), this.localHeaderOffset === e.MAX_VALUE_32BITS && (this.localHeaderOffset = a.readInt(8)), this.diskNumberStart === e.MAX_VALUE_32BITS && (this.diskNumberStart = a.readInt(4));
          }
        },
        readExtraFields: function readExtraFields(a) {
          var b,
              c,
              d,
              e = a.index;

          for (this.extraFields = this.extraFields || {}; a.index < e + this.extraFieldsLength;) {
            b = a.readInt(2), c = a.readInt(2), d = a.readString(c), this.extraFields[b] = {
              id: b,
              length: c,
              value: d
            };
          }
        },
        handleUTF8: function handleUTF8() {
          if (this.useUTF8()) this.fileName = g.utf8decode(this.fileName), this.fileComment = g.utf8decode(this.fileComment);else {
            var a = this.findExtraFieldUnicodePath();
            null !== a && (this.fileName = a);
            var b = this.findExtraFieldUnicodeComment();
            null !== b && (this.fileComment = b);
          }
        },
        findExtraFieldUnicodePath: function findExtraFieldUnicodePath() {
          var a = this.extraFields[28789];

          if (a) {
            var b = new d(a.value);
            return 1 !== b.readInt(1) ? null : g.crc32(this.fileName) !== b.readInt(4) ? null : g.utf8decode(b.readString(a.length - 5));
          }

          return null;
        },
        findExtraFieldUnicodeComment: function findExtraFieldUnicodeComment() {
          var a = this.extraFields[25461];

          if (a) {
            var b = new d(a.value);
            return 1 !== b.readInt(1) ? null : g.crc32(this.fileComment) !== b.readInt(4) ? null : g.utf8decode(b.readString(a.length - 5));
          }

          return null;
        }
      }, b.exports = c;
    }, {
      "./compressedObject": 2,
      "./object": 13,
      "./stringReader": 15,
      "./utils": 21
    }],
    24: [function (a, b) {
      "use strict";

      var c = a("./lib/utils/common").assign,
          d = a("./lib/deflate"),
          e = a("./lib/inflate"),
          f = a("./lib/zlib/constants"),
          g = {};
      c(g, d, e, f), b.exports = g;
    }, {
      "./lib/deflate": 25,
      "./lib/inflate": 26,
      "./lib/utils/common": 27,
      "./lib/zlib/constants": 30
    }],
    25: [function (a, b, c) {
      "use strict";

      function d(a, b) {
        var c = new s(b);
        if (c.push(a, !0), c.err) throw c.msg;
        return c.result;
      }

      function e(a, b) {
        return b = b || {}, b.raw = !0, d(a, b);
      }

      function f(a, b) {
        return b = b || {}, b.gzip = !0, d(a, b);
      }

      var g = a("./zlib/deflate.js"),
          h = a("./utils/common"),
          i = a("./utils/strings"),
          j = a("./zlib/messages"),
          k = a("./zlib/zstream"),
          l = 0,
          m = 4,
          n = 0,
          o = 1,
          p = -1,
          q = 0,
          r = 8,
          s = function s(a) {
        this.options = h.assign({
          level: p,
          method: r,
          chunkSize: 16384,
          windowBits: 15,
          memLevel: 8,
          strategy: q,
          to: ""
        }, a || {});
        var b = this.options;
        b.raw && b.windowBits > 0 ? b.windowBits = -b.windowBits : b.gzip && b.windowBits > 0 && b.windowBits < 16 && (b.windowBits += 16), this.err = 0, this.msg = "", this.ended = !1, this.chunks = [], this.strm = new k(), this.strm.avail_out = 0;
        var c = g.deflateInit2(this.strm, b.level, b.method, b.windowBits, b.memLevel, b.strategy);
        if (c !== n) throw new Error(j[c]);
        b.header && g.deflateSetHeader(this.strm, b.header);
      };

      s.prototype.push = function (a, b) {
        var c,
            d,
            e = this.strm,
            f = this.options.chunkSize;
        if (this.ended) return !1;
        d = b === ~~b ? b : b === !0 ? m : l, e.input = "string" == typeof a ? i.string2buf(a) : a, e.next_in = 0, e.avail_in = e.input.length;

        do {
          if (0 === e.avail_out && (e.output = new h.Buf8(f), e.next_out = 0, e.avail_out = f), c = g.deflate(e, d), c !== o && c !== n) return this.onEnd(c), this.ended = !0, !1;
          (0 === e.avail_out || 0 === e.avail_in && d === m) && this.onData("string" === this.options.to ? i.buf2binstring(h.shrinkBuf(e.output, e.next_out)) : h.shrinkBuf(e.output, e.next_out));
        } while ((e.avail_in > 0 || 0 === e.avail_out) && c !== o);

        return d === m ? (c = g.deflateEnd(this.strm), this.onEnd(c), this.ended = !0, c === n) : !0;
      }, s.prototype.onData = function (a) {
        this.chunks.push(a);
      }, s.prototype.onEnd = function (a) {
        a === n && (this.result = "string" === this.options.to ? this.chunks.join("") : h.flattenChunks(this.chunks)), this.chunks = [], this.err = a, this.msg = this.strm.msg;
      }, c.Deflate = s, c.deflate = d, c.deflateRaw = e, c.gzip = f;
    }, {
      "./utils/common": 27,
      "./utils/strings": 28,
      "./zlib/deflate.js": 32,
      "./zlib/messages": 37,
      "./zlib/zstream": 39
    }],
    26: [function (a, b, c) {
      "use strict";

      function d(a, b) {
        var c = new m(b);
        if (c.push(a, !0), c.err) throw c.msg;
        return c.result;
      }

      function e(a, b) {
        return b = b || {}, b.raw = !0, d(a, b);
      }

      var f = a("./zlib/inflate.js"),
          g = a("./utils/common"),
          h = a("./utils/strings"),
          i = a("./zlib/constants"),
          j = a("./zlib/messages"),
          k = a("./zlib/zstream"),
          l = a("./zlib/gzheader"),
          m = function m(a) {
        this.options = g.assign({
          chunkSize: 16384,
          windowBits: 0,
          to: ""
        }, a || {});
        var b = this.options;
        b.raw && b.windowBits >= 0 && b.windowBits < 16 && (b.windowBits = -b.windowBits, 0 === b.windowBits && (b.windowBits = -15)), !(b.windowBits >= 0 && b.windowBits < 16) || a && a.windowBits || (b.windowBits += 32), b.windowBits > 15 && b.windowBits < 48 && 0 === (15 & b.windowBits) && (b.windowBits |= 15), this.err = 0, this.msg = "", this.ended = !1, this.chunks = [], this.strm = new k(), this.strm.avail_out = 0;
        var c = f.inflateInit2(this.strm, b.windowBits);
        if (c !== i.Z_OK) throw new Error(j[c]);
        this.header = new l(), f.inflateGetHeader(this.strm, this.header);
      };

      m.prototype.push = function (a, b) {
        var c,
            d,
            e,
            j,
            k,
            l = this.strm,
            m = this.options.chunkSize;
        if (this.ended) return !1;
        d = b === ~~b ? b : b === !0 ? i.Z_FINISH : i.Z_NO_FLUSH, l.input = "string" == typeof a ? h.binstring2buf(a) : a, l.next_in = 0, l.avail_in = l.input.length;

        do {
          if (0 === l.avail_out && (l.output = new g.Buf8(m), l.next_out = 0, l.avail_out = m), c = f.inflate(l, i.Z_NO_FLUSH), c !== i.Z_STREAM_END && c !== i.Z_OK) return this.onEnd(c), this.ended = !0, !1;
          l.next_out && (0 === l.avail_out || c === i.Z_STREAM_END || 0 === l.avail_in && d === i.Z_FINISH) && ("string" === this.options.to ? (e = h.utf8border(l.output, l.next_out), j = l.next_out - e, k = h.buf2string(l.output, e), l.next_out = j, l.avail_out = m - j, j && g.arraySet(l.output, l.output, e, j, 0), this.onData(k)) : this.onData(g.shrinkBuf(l.output, l.next_out)));
        } while (l.avail_in > 0 && c !== i.Z_STREAM_END);

        return c === i.Z_STREAM_END && (d = i.Z_FINISH), d === i.Z_FINISH ? (c = f.inflateEnd(this.strm), this.onEnd(c), this.ended = !0, c === i.Z_OK) : !0;
      }, m.prototype.onData = function (a) {
        this.chunks.push(a);
      }, m.prototype.onEnd = function (a) {
        a === i.Z_OK && (this.result = "string" === this.options.to ? this.chunks.join("") : g.flattenChunks(this.chunks)), this.chunks = [], this.err = a, this.msg = this.strm.msg;
      }, c.Inflate = m, c.inflate = d, c.inflateRaw = e, c.ungzip = d;
    }, {
      "./utils/common": 27,
      "./utils/strings": 28,
      "./zlib/constants": 30,
      "./zlib/gzheader": 33,
      "./zlib/inflate.js": 35,
      "./zlib/messages": 37,
      "./zlib/zstream": 39
    }],
    27: [function (a, b, c) {
      "use strict";

      var d = "undefined" != typeof Uint8Array && "undefined" != typeof Uint16Array && "undefined" != typeof Int32Array;
      c.assign = function (a) {
        for (var b = Array.prototype.slice.call(arguments, 1); b.length;) {
          var c = b.shift();

          if (c) {
            if ("object" != _typeof(c)) throw new TypeError(c + "must be non-object");

            for (var d in c) {
              c.hasOwnProperty(d) && (a[d] = c[d]);
            }
          }
        }

        return a;
      }, c.shrinkBuf = function (a, b) {
        return a.length === b ? a : a.subarray ? a.subarray(0, b) : (a.length = b, a);
      };
      var e = {
        arraySet: function arraySet(a, b, c, d, e) {
          if (b.subarray && a.subarray) return void a.set(b.subarray(c, c + d), e);

          for (var f = 0; d > f; f++) {
            a[e + f] = b[c + f];
          }
        },
        flattenChunks: function flattenChunks(a) {
          var b, c, d, e, f, g;

          for (d = 0, b = 0, c = a.length; c > b; b++) {
            d += a[b].length;
          }

          for (g = new Uint8Array(d), e = 0, b = 0, c = a.length; c > b; b++) {
            f = a[b], g.set(f, e), e += f.length;
          }

          return g;
        }
      },
          f = {
        arraySet: function arraySet(a, b, c, d, e) {
          for (var f = 0; d > f; f++) {
            a[e + f] = b[c + f];
          }
        },
        flattenChunks: function flattenChunks(a) {
          return [].concat.apply([], a);
        }
      };
      c.setTyped = function (a) {
        a ? (c.Buf8 = Uint8Array, c.Buf16 = Uint16Array, c.Buf32 = Int32Array, c.assign(c, e)) : (c.Buf8 = Array, c.Buf16 = Array, c.Buf32 = Array, c.assign(c, f));
      }, c.setTyped(d);
    }, {}],
    28: [function (a, b, c) {
      "use strict";

      function d(a, b) {
        if (65537 > b && (a.subarray && g || !a.subarray && f)) return String.fromCharCode.apply(null, e.shrinkBuf(a, b));

        for (var c = "", d = 0; b > d; d++) {
          c += String.fromCharCode(a[d]);
        }

        return c;
      }

      var e = a("./common"),
          f = !0,
          g = !0;

      try {
        String.fromCharCode.apply(null, [0]);
      } catch (h) {
        f = !1;
      }

      try {
        String.fromCharCode.apply(null, new Uint8Array(1));
      } catch (h) {
        g = !1;
      }

      for (var i = new e.Buf8(256), j = 0; 256 > j; j++) {
        i[j] = j >= 252 ? 6 : j >= 248 ? 5 : j >= 240 ? 4 : j >= 224 ? 3 : j >= 192 ? 2 : 1;
      }

      i[254] = i[254] = 1, c.string2buf = function (a) {
        var b,
            c,
            d,
            f,
            g,
            h = a.length,
            i = 0;

        for (f = 0; h > f; f++) {
          c = a.charCodeAt(f), 55296 === (64512 & c) && h > f + 1 && (d = a.charCodeAt(f + 1), 56320 === (64512 & d) && (c = 65536 + (c - 55296 << 10) + (d - 56320), f++)), i += 128 > c ? 1 : 2048 > c ? 2 : 65536 > c ? 3 : 4;
        }

        for (b = new e.Buf8(i), g = 0, f = 0; i > g; f++) {
          c = a.charCodeAt(f), 55296 === (64512 & c) && h > f + 1 && (d = a.charCodeAt(f + 1), 56320 === (64512 & d) && (c = 65536 + (c - 55296 << 10) + (d - 56320), f++)), 128 > c ? b[g++] = c : 2048 > c ? (b[g++] = 192 | c >>> 6, b[g++] = 128 | 63 & c) : 65536 > c ? (b[g++] = 224 | c >>> 12, b[g++] = 128 | c >>> 6 & 63, b[g++] = 128 | 63 & c) : (b[g++] = 240 | c >>> 18, b[g++] = 128 | c >>> 12 & 63, b[g++] = 128 | c >>> 6 & 63, b[g++] = 128 | 63 & c);
        }

        return b;
      }, c.buf2binstring = function (a) {
        return d(a, a.length);
      }, c.binstring2buf = function (a) {
        for (var b = new e.Buf8(a.length), c = 0, d = b.length; d > c; c++) {
          b[c] = a.charCodeAt(c);
        }

        return b;
      }, c.buf2string = function (a, b) {
        var c,
            e,
            f,
            g,
            h = b || a.length,
            j = new Array(2 * h);

        for (e = 0, c = 0; h > c;) {
          if (f = a[c++], 128 > f) j[e++] = f;else if (g = i[f], g > 4) j[e++] = 65533, c += g - 1;else {
            for (f &= 2 === g ? 31 : 3 === g ? 15 : 7; g > 1 && h > c;) {
              f = f << 6 | 63 & a[c++], g--;
            }

            g > 1 ? j[e++] = 65533 : 65536 > f ? j[e++] = f : (f -= 65536, j[e++] = 55296 | f >> 10 & 1023, j[e++] = 56320 | 1023 & f);
          }
        }

        return d(j, e);
      }, c.utf8border = function (a, b) {
        var c;

        for (b = b || a.length, b > a.length && (b = a.length), c = b - 1; c >= 0 && 128 === (192 & a[c]);) {
          c--;
        }

        return 0 > c ? b : 0 === c ? b : c + i[a[c]] > b ? c : b;
      };
    }, {
      "./common": 27
    }],
    29: [function (a, b) {
      "use strict";

      function c(a, b, c, d) {
        for (var e = 65535 & a | 0, f = a >>> 16 & 65535 | 0, g = 0; 0 !== c;) {
          g = c > 2e3 ? 2e3 : c, c -= g;

          do {
            e = e + b[d++] | 0, f = f + e | 0;
          } while (--g);

          e %= 65521, f %= 65521;
        }

        return e | f << 16 | 0;
      }

      b.exports = c;
    }, {}],
    30: [function (a, b) {
      b.exports = {
        Z_NO_FLUSH: 0,
        Z_PARTIAL_FLUSH: 1,
        Z_SYNC_FLUSH: 2,
        Z_FULL_FLUSH: 3,
        Z_FINISH: 4,
        Z_BLOCK: 5,
        Z_TREES: 6,
        Z_OK: 0,
        Z_STREAM_END: 1,
        Z_NEED_DICT: 2,
        Z_ERRNO: -1,
        Z_STREAM_ERROR: -2,
        Z_DATA_ERROR: -3,
        Z_BUF_ERROR: -5,
        Z_NO_COMPRESSION: 0,
        Z_BEST_SPEED: 1,
        Z_BEST_COMPRESSION: 9,
        Z_DEFAULT_COMPRESSION: -1,
        Z_FILTERED: 1,
        Z_HUFFMAN_ONLY: 2,
        Z_RLE: 3,
        Z_FIXED: 4,
        Z_DEFAULT_STRATEGY: 0,
        Z_BINARY: 0,
        Z_TEXT: 1,
        Z_UNKNOWN: 2,
        Z_DEFLATED: 8
      };
    }, {}],
    31: [function (a, b) {
      "use strict";

      function c() {
        for (var a, b = [], c = 0; 256 > c; c++) {
          a = c;

          for (var d = 0; 8 > d; d++) {
            a = 1 & a ? 3988292384 ^ a >>> 1 : a >>> 1;
          }

          b[c] = a;
        }

        return b;
      }

      function d(a, b, c, d) {
        var f = e,
            g = d + c;
        a = -1 ^ a;

        for (var h = d; g > h; h++) {
          a = a >>> 8 ^ f[255 & (a ^ b[h])];
        }

        return -1 ^ a;
      }

      var e = c();
      b.exports = d;
    }, {}],
    32: [function (a, b, c) {
      "use strict";

      function d(a, b) {
        return a.msg = G[b], b;
      }

      function e(a) {
        return (a << 1) - (a > 4 ? 9 : 0);
      }

      function f(a) {
        for (var b = a.length; --b >= 0;) {
          a[b] = 0;
        }
      }

      function g(a) {
        var b = a.state,
            c = b.pending;
        c > a.avail_out && (c = a.avail_out), 0 !== c && (C.arraySet(a.output, b.pending_buf, b.pending_out, c, a.next_out), a.next_out += c, b.pending_out += c, a.total_out += c, a.avail_out -= c, b.pending -= c, 0 === b.pending && (b.pending_out = 0));
      }

      function h(a, b) {
        D._tr_flush_block(a, a.block_start >= 0 ? a.block_start : -1, a.strstart - a.block_start, b), a.block_start = a.strstart, g(a.strm);
      }

      function i(a, b) {
        a.pending_buf[a.pending++] = b;
      }

      function j(a, b) {
        a.pending_buf[a.pending++] = b >>> 8 & 255, a.pending_buf[a.pending++] = 255 & b;
      }

      function k(a, b, c, d) {
        var e = a.avail_in;
        return e > d && (e = d), 0 === e ? 0 : (a.avail_in -= e, C.arraySet(b, a.input, a.next_in, e, c), 1 === a.state.wrap ? a.adler = E(a.adler, b, e, c) : 2 === a.state.wrap && (a.adler = F(a.adler, b, e, c)), a.next_in += e, a.total_in += e, e);
      }

      function l(a, b) {
        var c,
            d,
            e = a.max_chain_length,
            f = a.strstart,
            g = a.prev_length,
            h = a.nice_match,
            i = a.strstart > a.w_size - jb ? a.strstart - (a.w_size - jb) : 0,
            j = a.window,
            k = a.w_mask,
            l = a.prev,
            m = a.strstart + ib,
            n = j[f + g - 1],
            o = j[f + g];
        a.prev_length >= a.good_match && (e >>= 2), h > a.lookahead && (h = a.lookahead);

        do {
          if (c = b, j[c + g] === o && j[c + g - 1] === n && j[c] === j[f] && j[++c] === j[f + 1]) {
            f += 2, c++;

            do {
              ;
            } while (j[++f] === j[++c] && j[++f] === j[++c] && j[++f] === j[++c] && j[++f] === j[++c] && j[++f] === j[++c] && j[++f] === j[++c] && j[++f] === j[++c] && j[++f] === j[++c] && m > f);

            if (d = ib - (m - f), f = m - ib, d > g) {
              if (a.match_start = b, g = d, d >= h) break;
              n = j[f + g - 1], o = j[f + g];
            }
          }
        } while ((b = l[b & k]) > i && 0 !== --e);

        return g <= a.lookahead ? g : a.lookahead;
      }

      function m(a) {
        var b,
            c,
            d,
            e,
            f,
            g = a.w_size;

        do {
          if (e = a.window_size - a.lookahead - a.strstart, a.strstart >= g + (g - jb)) {
            C.arraySet(a.window, a.window, g, g, 0), a.match_start -= g, a.strstart -= g, a.block_start -= g, c = a.hash_size, b = c;

            do {
              d = a.head[--b], a.head[b] = d >= g ? d - g : 0;
            } while (--c);

            c = g, b = c;

            do {
              d = a.prev[--b], a.prev[b] = d >= g ? d - g : 0;
            } while (--c);

            e += g;
          }

          if (0 === a.strm.avail_in) break;
          if (c = k(a.strm, a.window, a.strstart + a.lookahead, e), a.lookahead += c, a.lookahead + a.insert >= hb) for (f = a.strstart - a.insert, a.ins_h = a.window[f], a.ins_h = (a.ins_h << a.hash_shift ^ a.window[f + 1]) & a.hash_mask; a.insert && (a.ins_h = (a.ins_h << a.hash_shift ^ a.window[f + hb - 1]) & a.hash_mask, a.prev[f & a.w_mask] = a.head[a.ins_h], a.head[a.ins_h] = f, f++, a.insert--, !(a.lookahead + a.insert < hb));) {
            ;
          }
        } while (a.lookahead < jb && 0 !== a.strm.avail_in);
      }

      function n(a, b) {
        var c = 65535;

        for (c > a.pending_buf_size - 5 && (c = a.pending_buf_size - 5);;) {
          if (a.lookahead <= 1) {
            if (m(a), 0 === a.lookahead && b === H) return sb;
            if (0 === a.lookahead) break;
          }

          a.strstart += a.lookahead, a.lookahead = 0;
          var d = a.block_start + c;
          if ((0 === a.strstart || a.strstart >= d) && (a.lookahead = a.strstart - d, a.strstart = d, h(a, !1), 0 === a.strm.avail_out)) return sb;
          if (a.strstart - a.block_start >= a.w_size - jb && (h(a, !1), 0 === a.strm.avail_out)) return sb;
        }

        return a.insert = 0, b === K ? (h(a, !0), 0 === a.strm.avail_out ? ub : vb) : a.strstart > a.block_start && (h(a, !1), 0 === a.strm.avail_out) ? sb : sb;
      }

      function o(a, b) {
        for (var c, d;;) {
          if (a.lookahead < jb) {
            if (m(a), a.lookahead < jb && b === H) return sb;
            if (0 === a.lookahead) break;
          }

          if (c = 0, a.lookahead >= hb && (a.ins_h = (a.ins_h << a.hash_shift ^ a.window[a.strstart + hb - 1]) & a.hash_mask, c = a.prev[a.strstart & a.w_mask] = a.head[a.ins_h], a.head[a.ins_h] = a.strstart), 0 !== c && a.strstart - c <= a.w_size - jb && (a.match_length = l(a, c)), a.match_length >= hb) {
            if (d = D._tr_tally(a, a.strstart - a.match_start, a.match_length - hb), a.lookahead -= a.match_length, a.match_length <= a.max_lazy_match && a.lookahead >= hb) {
              a.match_length--;

              do {
                a.strstart++, a.ins_h = (a.ins_h << a.hash_shift ^ a.window[a.strstart + hb - 1]) & a.hash_mask, c = a.prev[a.strstart & a.w_mask] = a.head[a.ins_h], a.head[a.ins_h] = a.strstart;
              } while (0 !== --a.match_length);

              a.strstart++;
            } else a.strstart += a.match_length, a.match_length = 0, a.ins_h = a.window[a.strstart], a.ins_h = (a.ins_h << a.hash_shift ^ a.window[a.strstart + 1]) & a.hash_mask;
          } else d = D._tr_tally(a, 0, a.window[a.strstart]), a.lookahead--, a.strstart++;
          if (d && (h(a, !1), 0 === a.strm.avail_out)) return sb;
        }

        return a.insert = a.strstart < hb - 1 ? a.strstart : hb - 1, b === K ? (h(a, !0), 0 === a.strm.avail_out ? ub : vb) : a.last_lit && (h(a, !1), 0 === a.strm.avail_out) ? sb : tb;
      }

      function p(a, b) {
        for (var c, d, e;;) {
          if (a.lookahead < jb) {
            if (m(a), a.lookahead < jb && b === H) return sb;
            if (0 === a.lookahead) break;
          }

          if (c = 0, a.lookahead >= hb && (a.ins_h = (a.ins_h << a.hash_shift ^ a.window[a.strstart + hb - 1]) & a.hash_mask, c = a.prev[a.strstart & a.w_mask] = a.head[a.ins_h], a.head[a.ins_h] = a.strstart), a.prev_length = a.match_length, a.prev_match = a.match_start, a.match_length = hb - 1, 0 !== c && a.prev_length < a.max_lazy_match && a.strstart - c <= a.w_size - jb && (a.match_length = l(a, c), a.match_length <= 5 && (a.strategy === S || a.match_length === hb && a.strstart - a.match_start > 4096) && (a.match_length = hb - 1)), a.prev_length >= hb && a.match_length <= a.prev_length) {
            e = a.strstart + a.lookahead - hb, d = D._tr_tally(a, a.strstart - 1 - a.prev_match, a.prev_length - hb), a.lookahead -= a.prev_length - 1, a.prev_length -= 2;

            do {
              ++a.strstart <= e && (a.ins_h = (a.ins_h << a.hash_shift ^ a.window[a.strstart + hb - 1]) & a.hash_mask, c = a.prev[a.strstart & a.w_mask] = a.head[a.ins_h], a.head[a.ins_h] = a.strstart);
            } while (0 !== --a.prev_length);

            if (a.match_available = 0, a.match_length = hb - 1, a.strstart++, d && (h(a, !1), 0 === a.strm.avail_out)) return sb;
          } else if (a.match_available) {
            if (d = D._tr_tally(a, 0, a.window[a.strstart - 1]), d && h(a, !1), a.strstart++, a.lookahead--, 0 === a.strm.avail_out) return sb;
          } else a.match_available = 1, a.strstart++, a.lookahead--;
        }

        return a.match_available && (d = D._tr_tally(a, 0, a.window[a.strstart - 1]), a.match_available = 0), a.insert = a.strstart < hb - 1 ? a.strstart : hb - 1, b === K ? (h(a, !0), 0 === a.strm.avail_out ? ub : vb) : a.last_lit && (h(a, !1), 0 === a.strm.avail_out) ? sb : tb;
      }

      function q(a, b) {
        for (var c, d, e, f, g = a.window;;) {
          if (a.lookahead <= ib) {
            if (m(a), a.lookahead <= ib && b === H) return sb;
            if (0 === a.lookahead) break;
          }

          if (a.match_length = 0, a.lookahead >= hb && a.strstart > 0 && (e = a.strstart - 1, d = g[e], d === g[++e] && d === g[++e] && d === g[++e])) {
            f = a.strstart + ib;

            do {
              ;
            } while (d === g[++e] && d === g[++e] && d === g[++e] && d === g[++e] && d === g[++e] && d === g[++e] && d === g[++e] && d === g[++e] && f > e);

            a.match_length = ib - (f - e), a.match_length > a.lookahead && (a.match_length = a.lookahead);
          }

          if (a.match_length >= hb ? (c = D._tr_tally(a, 1, a.match_length - hb), a.lookahead -= a.match_length, a.strstart += a.match_length, a.match_length = 0) : (c = D._tr_tally(a, 0, a.window[a.strstart]), a.lookahead--, a.strstart++), c && (h(a, !1), 0 === a.strm.avail_out)) return sb;
        }

        return a.insert = 0, b === K ? (h(a, !0), 0 === a.strm.avail_out ? ub : vb) : a.last_lit && (h(a, !1), 0 === a.strm.avail_out) ? sb : tb;
      }

      function r(a, b) {
        for (var c;;) {
          if (0 === a.lookahead && (m(a), 0 === a.lookahead)) {
            if (b === H) return sb;
            break;
          }

          if (a.match_length = 0, c = D._tr_tally(a, 0, a.window[a.strstart]), a.lookahead--, a.strstart++, c && (h(a, !1), 0 === a.strm.avail_out)) return sb;
        }

        return a.insert = 0, b === K ? (h(a, !0), 0 === a.strm.avail_out ? ub : vb) : a.last_lit && (h(a, !1), 0 === a.strm.avail_out) ? sb : tb;
      }

      function s(a) {
        a.window_size = 2 * a.w_size, f(a.head), a.max_lazy_match = B[a.level].max_lazy, a.good_match = B[a.level].good_length, a.nice_match = B[a.level].nice_length, a.max_chain_length = B[a.level].max_chain, a.strstart = 0, a.block_start = 0, a.lookahead = 0, a.insert = 0, a.match_length = a.prev_length = hb - 1, a.match_available = 0, a.ins_h = 0;
      }

      function t() {
        this.strm = null, this.status = 0, this.pending_buf = null, this.pending_buf_size = 0, this.pending_out = 0, this.pending = 0, this.wrap = 0, this.gzhead = null, this.gzindex = 0, this.method = Y, this.last_flush = -1, this.w_size = 0, this.w_bits = 0, this.w_mask = 0, this.window = null, this.window_size = 0, this.prev = null, this.head = null, this.ins_h = 0, this.hash_size = 0, this.hash_bits = 0, this.hash_mask = 0, this.hash_shift = 0, this.block_start = 0, this.match_length = 0, this.prev_match = 0, this.match_available = 0, this.strstart = 0, this.match_start = 0, this.lookahead = 0, this.prev_length = 0, this.max_chain_length = 0, this.max_lazy_match = 0, this.level = 0, this.strategy = 0, this.good_match = 0, this.nice_match = 0, this.dyn_ltree = new C.Buf16(2 * fb), this.dyn_dtree = new C.Buf16(2 * (2 * db + 1)), this.bl_tree = new C.Buf16(2 * (2 * eb + 1)), f(this.dyn_ltree), f(this.dyn_dtree), f(this.bl_tree), this.l_desc = null, this.d_desc = null, this.bl_desc = null, this.bl_count = new C.Buf16(gb + 1), this.heap = new C.Buf16(2 * cb + 1), f(this.heap), this.heap_len = 0, this.heap_max = 0, this.depth = new C.Buf16(2 * cb + 1), f(this.depth), this.l_buf = 0, this.lit_bufsize = 0, this.last_lit = 0, this.d_buf = 0, this.opt_len = 0, this.static_len = 0, this.matches = 0, this.insert = 0, this.bi_buf = 0, this.bi_valid = 0;
      }

      function u(a) {
        var b;
        return a && a.state ? (a.total_in = a.total_out = 0, a.data_type = X, b = a.state, b.pending = 0, b.pending_out = 0, b.wrap < 0 && (b.wrap = -b.wrap), b.status = b.wrap ? lb : qb, a.adler = 2 === b.wrap ? 0 : 1, b.last_flush = H, D._tr_init(b), M) : d(a, O);
      }

      function v(a) {
        var b = u(a);
        return b === M && s(a.state), b;
      }

      function w(a, b) {
        return a && a.state ? 2 !== a.state.wrap ? O : (a.state.gzhead = b, M) : O;
      }

      function x(a, b, c, e, f, g) {
        if (!a) return O;
        var h = 1;
        if (b === R && (b = 6), 0 > e ? (h = 0, e = -e) : e > 15 && (h = 2, e -= 16), 1 > f || f > Z || c !== Y || 8 > e || e > 15 || 0 > b || b > 9 || 0 > g || g > V) return d(a, O);
        8 === e && (e = 9);
        var i = new t();
        return a.state = i, i.strm = a, i.wrap = h, i.gzhead = null, i.w_bits = e, i.w_size = 1 << i.w_bits, i.w_mask = i.w_size - 1, i.hash_bits = f + 7, i.hash_size = 1 << i.hash_bits, i.hash_mask = i.hash_size - 1, i.hash_shift = ~~((i.hash_bits + hb - 1) / hb), i.window = new C.Buf8(2 * i.w_size), i.head = new C.Buf16(i.hash_size), i.prev = new C.Buf16(i.w_size), i.lit_bufsize = 1 << f + 6, i.pending_buf_size = 4 * i.lit_bufsize, i.pending_buf = new C.Buf8(i.pending_buf_size), i.d_buf = i.lit_bufsize >> 1, i.l_buf = 3 * i.lit_bufsize, i.level = b, i.strategy = g, i.method = c, v(a);
      }

      function y(a, b) {
        return x(a, b, Y, $, _, W);
      }

      function z(a, b) {
        var c, h, k, l;
        if (!a || !a.state || b > L || 0 > b) return a ? d(a, O) : O;
        if (h = a.state, !a.output || !a.input && 0 !== a.avail_in || h.status === rb && b !== K) return d(a, 0 === a.avail_out ? Q : O);
        if (h.strm = a, c = h.last_flush, h.last_flush = b, h.status === lb) if (2 === h.wrap) a.adler = 0, i(h, 31), i(h, 139), i(h, 8), h.gzhead ? (i(h, (h.gzhead.text ? 1 : 0) + (h.gzhead.hcrc ? 2 : 0) + (h.gzhead.extra ? 4 : 0) + (h.gzhead.name ? 8 : 0) + (h.gzhead.comment ? 16 : 0)), i(h, 255 & h.gzhead.time), i(h, h.gzhead.time >> 8 & 255), i(h, h.gzhead.time >> 16 & 255), i(h, h.gzhead.time >> 24 & 255), i(h, 9 === h.level ? 2 : h.strategy >= T || h.level < 2 ? 4 : 0), i(h, 255 & h.gzhead.os), h.gzhead.extra && h.gzhead.extra.length && (i(h, 255 & h.gzhead.extra.length), i(h, h.gzhead.extra.length >> 8 & 255)), h.gzhead.hcrc && (a.adler = F(a.adler, h.pending_buf, h.pending, 0)), h.gzindex = 0, h.status = mb) : (i(h, 0), i(h, 0), i(h, 0), i(h, 0), i(h, 0), i(h, 9 === h.level ? 2 : h.strategy >= T || h.level < 2 ? 4 : 0), i(h, wb), h.status = qb);else {
          var m = Y + (h.w_bits - 8 << 4) << 8,
              n = -1;
          n = h.strategy >= T || h.level < 2 ? 0 : h.level < 6 ? 1 : 6 === h.level ? 2 : 3, m |= n << 6, 0 !== h.strstart && (m |= kb), m += 31 - m % 31, h.status = qb, j(h, m), 0 !== h.strstart && (j(h, a.adler >>> 16), j(h, 65535 & a.adler)), a.adler = 1;
        }
        if (h.status === mb) if (h.gzhead.extra) {
          for (k = h.pending; h.gzindex < (65535 & h.gzhead.extra.length) && (h.pending !== h.pending_buf_size || (h.gzhead.hcrc && h.pending > k && (a.adler = F(a.adler, h.pending_buf, h.pending - k, k)), g(a), k = h.pending, h.pending !== h.pending_buf_size));) {
            i(h, 255 & h.gzhead.extra[h.gzindex]), h.gzindex++;
          }

          h.gzhead.hcrc && h.pending > k && (a.adler = F(a.adler, h.pending_buf, h.pending - k, k)), h.gzindex === h.gzhead.extra.length && (h.gzindex = 0, h.status = nb);
        } else h.status = nb;
        if (h.status === nb) if (h.gzhead.name) {
          k = h.pending;

          do {
            if (h.pending === h.pending_buf_size && (h.gzhead.hcrc && h.pending > k && (a.adler = F(a.adler, h.pending_buf, h.pending - k, k)), g(a), k = h.pending, h.pending === h.pending_buf_size)) {
              l = 1;
              break;
            }

            l = h.gzindex < h.gzhead.name.length ? 255 & h.gzhead.name.charCodeAt(h.gzindex++) : 0, i(h, l);
          } while (0 !== l);

          h.gzhead.hcrc && h.pending > k && (a.adler = F(a.adler, h.pending_buf, h.pending - k, k)), 0 === l && (h.gzindex = 0, h.status = ob);
        } else h.status = ob;
        if (h.status === ob) if (h.gzhead.comment) {
          k = h.pending;

          do {
            if (h.pending === h.pending_buf_size && (h.gzhead.hcrc && h.pending > k && (a.adler = F(a.adler, h.pending_buf, h.pending - k, k)), g(a), k = h.pending, h.pending === h.pending_buf_size)) {
              l = 1;
              break;
            }

            l = h.gzindex < h.gzhead.comment.length ? 255 & h.gzhead.comment.charCodeAt(h.gzindex++) : 0, i(h, l);
          } while (0 !== l);

          h.gzhead.hcrc && h.pending > k && (a.adler = F(a.adler, h.pending_buf, h.pending - k, k)), 0 === l && (h.status = pb);
        } else h.status = pb;

        if (h.status === pb && (h.gzhead.hcrc ? (h.pending + 2 > h.pending_buf_size && g(a), h.pending + 2 <= h.pending_buf_size && (i(h, 255 & a.adler), i(h, a.adler >> 8 & 255), a.adler = 0, h.status = qb)) : h.status = qb), 0 !== h.pending) {
          if (g(a), 0 === a.avail_out) return h.last_flush = -1, M;
        } else if (0 === a.avail_in && e(b) <= e(c) && b !== K) return d(a, Q);

        if (h.status === rb && 0 !== a.avail_in) return d(a, Q);

        if (0 !== a.avail_in || 0 !== h.lookahead || b !== H && h.status !== rb) {
          var o = h.strategy === T ? r(h, b) : h.strategy === U ? q(h, b) : B[h.level].func(h, b);
          if ((o === ub || o === vb) && (h.status = rb), o === sb || o === ub) return 0 === a.avail_out && (h.last_flush = -1), M;
          if (o === tb && (b === I ? D._tr_align(h) : b !== L && (D._tr_stored_block(h, 0, 0, !1), b === J && (f(h.head), 0 === h.lookahead && (h.strstart = 0, h.block_start = 0, h.insert = 0))), g(a), 0 === a.avail_out)) return h.last_flush = -1, M;
        }

        return b !== K ? M : h.wrap <= 0 ? N : (2 === h.wrap ? (i(h, 255 & a.adler), i(h, a.adler >> 8 & 255), i(h, a.adler >> 16 & 255), i(h, a.adler >> 24 & 255), i(h, 255 & a.total_in), i(h, a.total_in >> 8 & 255), i(h, a.total_in >> 16 & 255), i(h, a.total_in >> 24 & 255)) : (j(h, a.adler >>> 16), j(h, 65535 & a.adler)), g(a), h.wrap > 0 && (h.wrap = -h.wrap), 0 !== h.pending ? M : N);
      }

      function A(a) {
        var b;
        return a && a.state ? (b = a.state.status, b !== lb && b !== mb && b !== nb && b !== ob && b !== pb && b !== qb && b !== rb ? d(a, O) : (a.state = null, b === qb ? d(a, P) : M)) : O;
      }

      var B,
          C = a("../utils/common"),
          D = a("./trees"),
          E = a("./adler32"),
          F = a("./crc32"),
          G = a("./messages"),
          H = 0,
          I = 1,
          J = 3,
          K = 4,
          L = 5,
          M = 0,
          N = 1,
          O = -2,
          P = -3,
          Q = -5,
          R = -1,
          S = 1,
          T = 2,
          U = 3,
          V = 4,
          W = 0,
          X = 2,
          Y = 8,
          Z = 9,
          $ = 15,
          _ = 8,
          ab = 29,
          bb = 256,
          cb = bb + 1 + ab,
          db = 30,
          eb = 19,
          fb = 2 * cb + 1,
          gb = 15,
          hb = 3,
          ib = 258,
          jb = ib + hb + 1,
          kb = 32,
          lb = 42,
          mb = 69,
          nb = 73,
          ob = 91,
          pb = 103,
          qb = 113,
          rb = 666,
          sb = 1,
          tb = 2,
          ub = 3,
          vb = 4,
          wb = 3,
          xb = function xb(a, b, c, d, e) {
        this.good_length = a, this.max_lazy = b, this.nice_length = c, this.max_chain = d, this.func = e;
      };

      B = [new xb(0, 0, 0, 0, n), new xb(4, 4, 8, 4, o), new xb(4, 5, 16, 8, o), new xb(4, 6, 32, 32, o), new xb(4, 4, 16, 16, p), new xb(8, 16, 32, 32, p), new xb(8, 16, 128, 128, p), new xb(8, 32, 128, 256, p), new xb(32, 128, 258, 1024, p), new xb(32, 258, 258, 4096, p)], c.deflateInit = y, c.deflateInit2 = x, c.deflateReset = v, c.deflateResetKeep = u, c.deflateSetHeader = w, c.deflate = z, c.deflateEnd = A, c.deflateInfo = "pako deflate (from Nodeca project)";
    }, {
      "../utils/common": 27,
      "./adler32": 29,
      "./crc32": 31,
      "./messages": 37,
      "./trees": 38
    }],
    33: [function (a, b) {
      "use strict";

      function c() {
        this.text = 0, this.time = 0, this.xflags = 0, this.os = 0, this.extra = null, this.extra_len = 0, this.name = "", this.comment = "", this.hcrc = 0, this.done = !1;
      }

      b.exports = c;
    }, {}],
    34: [function (a, b) {
      "use strict";

      var c = 30,
          d = 12;

      b.exports = function (a, b) {
        var e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z, A, B, C;
        e = a.state, f = a.next_in, B = a.input, g = f + (a.avail_in - 5), h = a.next_out, C = a.output, i = h - (b - a.avail_out), j = h + (a.avail_out - 257), k = e.dmax, l = e.wsize, m = e.whave, n = e.wnext, o = e.window, p = e.hold, q = e.bits, r = e.lencode, s = e.distcode, t = (1 << e.lenbits) - 1, u = (1 << e.distbits) - 1;

        a: do {
          15 > q && (p += B[f++] << q, q += 8, p += B[f++] << q, q += 8), v = r[p & t];

          b: for (;;) {
            if (w = v >>> 24, p >>>= w, q -= w, w = v >>> 16 & 255, 0 === w) C[h++] = 65535 & v;else {
              if (!(16 & w)) {
                if (0 === (64 & w)) {
                  v = r[(65535 & v) + (p & (1 << w) - 1)];
                  continue b;
                }

                if (32 & w) {
                  e.mode = d;
                  break a;
                }

                a.msg = "invalid literal/length code", e.mode = c;
                break a;
              }

              x = 65535 & v, w &= 15, w && (w > q && (p += B[f++] << q, q += 8), x += p & (1 << w) - 1, p >>>= w, q -= w), 15 > q && (p += B[f++] << q, q += 8, p += B[f++] << q, q += 8), v = s[p & u];

              c: for (;;) {
                if (w = v >>> 24, p >>>= w, q -= w, w = v >>> 16 & 255, !(16 & w)) {
                  if (0 === (64 & w)) {
                    v = s[(65535 & v) + (p & (1 << w) - 1)];
                    continue c;
                  }

                  a.msg = "invalid distance code", e.mode = c;
                  break a;
                }

                if (y = 65535 & v, w &= 15, w > q && (p += B[f++] << q, q += 8, w > q && (p += B[f++] << q, q += 8)), y += p & (1 << w) - 1, y > k) {
                  a.msg = "invalid distance too far back", e.mode = c;
                  break a;
                }

                if (p >>>= w, q -= w, w = h - i, y > w) {
                  if (w = y - w, w > m && e.sane) {
                    a.msg = "invalid distance too far back", e.mode = c;
                    break a;
                  }

                  if (z = 0, A = o, 0 === n) {
                    if (z += l - w, x > w) {
                      x -= w;

                      do {
                        C[h++] = o[z++];
                      } while (--w);

                      z = h - y, A = C;
                    }
                  } else if (w > n) {
                    if (z += l + n - w, w -= n, x > w) {
                      x -= w;

                      do {
                        C[h++] = o[z++];
                      } while (--w);

                      if (z = 0, x > n) {
                        w = n, x -= w;

                        do {
                          C[h++] = o[z++];
                        } while (--w);

                        z = h - y, A = C;
                      }
                    }
                  } else if (z += n - w, x > w) {
                    x -= w;

                    do {
                      C[h++] = o[z++];
                    } while (--w);

                    z = h - y, A = C;
                  }

                  for (; x > 2;) {
                    C[h++] = A[z++], C[h++] = A[z++], C[h++] = A[z++], x -= 3;
                  }

                  x && (C[h++] = A[z++], x > 1 && (C[h++] = A[z++]));
                } else {
                  z = h - y;

                  do {
                    C[h++] = C[z++], C[h++] = C[z++], C[h++] = C[z++], x -= 3;
                  } while (x > 2);

                  x && (C[h++] = C[z++], x > 1 && (C[h++] = C[z++]));
                }

                break;
              }
            }
            break;
          }
        } while (g > f && j > h);

        x = q >> 3, f -= x, q -= x << 3, p &= (1 << q) - 1, a.next_in = f, a.next_out = h, a.avail_in = g > f ? 5 + (g - f) : 5 - (f - g), a.avail_out = j > h ? 257 + (j - h) : 257 - (h - j), e.hold = p, e.bits = q;
      };
    }, {}],
    35: [function (a, b, c) {
      "use strict";

      function d(a) {
        return (a >>> 24 & 255) + (a >>> 8 & 65280) + ((65280 & a) << 8) + ((255 & a) << 24);
      }

      function e() {
        this.mode = 0, this.last = !1, this.wrap = 0, this.havedict = !1, this.flags = 0, this.dmax = 0, this.check = 0, this.total = 0, this.head = null, this.wbits = 0, this.wsize = 0, this.whave = 0, this.wnext = 0, this.window = null, this.hold = 0, this.bits = 0, this.length = 0, this.offset = 0, this.extra = 0, this.lencode = null, this.distcode = null, this.lenbits = 0, this.distbits = 0, this.ncode = 0, this.nlen = 0, this.ndist = 0, this.have = 0, this.next = null, this.lens = new r.Buf16(320), this.work = new r.Buf16(288), this.lendyn = null, this.distdyn = null, this.sane = 0, this.back = 0, this.was = 0;
      }

      function f(a) {
        var b;
        return a && a.state ? (b = a.state, a.total_in = a.total_out = b.total = 0, a.msg = "", b.wrap && (a.adler = 1 & b.wrap), b.mode = K, b.last = 0, b.havedict = 0, b.dmax = 32768, b.head = null, b.hold = 0, b.bits = 0, b.lencode = b.lendyn = new r.Buf32(ob), b.distcode = b.distdyn = new r.Buf32(pb), b.sane = 1, b.back = -1, C) : F;
      }

      function g(a) {
        var b;
        return a && a.state ? (b = a.state, b.wsize = 0, b.whave = 0, b.wnext = 0, f(a)) : F;
      }

      function h(a, b) {
        var c, d;
        return a && a.state ? (d = a.state, 0 > b ? (c = 0, b = -b) : (c = (b >> 4) + 1, 48 > b && (b &= 15)), b && (8 > b || b > 15) ? F : (null !== d.window && d.wbits !== b && (d.window = null), d.wrap = c, d.wbits = b, g(a))) : F;
      }

      function i(a, b) {
        var c, d;
        return a ? (d = new e(), a.state = d, d.window = null, c = h(a, b), c !== C && (a.state = null), c) : F;
      }

      function j(a) {
        return i(a, rb);
      }

      function k(a) {
        if (sb) {
          var b;

          for (p = new r.Buf32(512), q = new r.Buf32(32), b = 0; 144 > b;) {
            a.lens[b++] = 8;
          }

          for (; 256 > b;) {
            a.lens[b++] = 9;
          }

          for (; 280 > b;) {
            a.lens[b++] = 7;
          }

          for (; 288 > b;) {
            a.lens[b++] = 8;
          }

          for (v(x, a.lens, 0, 288, p, 0, a.work, {
            bits: 9
          }), b = 0; 32 > b;) {
            a.lens[b++] = 5;
          }

          v(y, a.lens, 0, 32, q, 0, a.work, {
            bits: 5
          }), sb = !1;
        }

        a.lencode = p, a.lenbits = 9, a.distcode = q, a.distbits = 5;
      }

      function l(a, b, c, d) {
        var e,
            f = a.state;
        return null === f.window && (f.wsize = 1 << f.wbits, f.wnext = 0, f.whave = 0, f.window = new r.Buf8(f.wsize)), d >= f.wsize ? (r.arraySet(f.window, b, c - f.wsize, f.wsize, 0), f.wnext = 0, f.whave = f.wsize) : (e = f.wsize - f.wnext, e > d && (e = d), r.arraySet(f.window, b, c - d, e, f.wnext), d -= e, d ? (r.arraySet(f.window, b, c - d, d, 0), f.wnext = d, f.whave = f.wsize) : (f.wnext += e, f.wnext === f.wsize && (f.wnext = 0), f.whave < f.wsize && (f.whave += e))), 0;
      }

      function m(a, b) {
        var c,
            e,
            f,
            g,
            h,
            i,
            j,
            m,
            n,
            o,
            p,
            q,
            ob,
            pb,
            qb,
            rb,
            sb,
            tb,
            ub,
            vb,
            wb,
            xb,
            yb,
            zb,
            Ab = 0,
            Bb = new r.Buf8(4),
            Cb = [16, 17, 18, 0, 8, 7, 9, 6, 10, 5, 11, 4, 12, 3, 13, 2, 14, 1, 15];
        if (!a || !a.state || !a.output || !a.input && 0 !== a.avail_in) return F;
        c = a.state, c.mode === V && (c.mode = W), h = a.next_out, f = a.output, j = a.avail_out, g = a.next_in, e = a.input, i = a.avail_in, m = c.hold, n = c.bits, o = i, p = j, xb = C;

        a: for (;;) {
          switch (c.mode) {
            case K:
              if (0 === c.wrap) {
                c.mode = W;
                break;
              }

              for (; 16 > n;) {
                if (0 === i) break a;
                i--, m += e[g++] << n, n += 8;
              }

              if (2 & c.wrap && 35615 === m) {
                c.check = 0, Bb[0] = 255 & m, Bb[1] = m >>> 8 & 255, c.check = t(c.check, Bb, 2, 0), m = 0, n = 0, c.mode = L;
                break;
              }

              if (c.flags = 0, c.head && (c.head.done = !1), !(1 & c.wrap) || (((255 & m) << 8) + (m >> 8)) % 31) {
                a.msg = "incorrect header check", c.mode = lb;
                break;
              }

              if ((15 & m) !== J) {
                a.msg = "unknown compression method", c.mode = lb;
                break;
              }

              if (m >>>= 4, n -= 4, wb = (15 & m) + 8, 0 === c.wbits) c.wbits = wb;else if (wb > c.wbits) {
                a.msg = "invalid window size", c.mode = lb;
                break;
              }
              c.dmax = 1 << wb, a.adler = c.check = 1, c.mode = 512 & m ? T : V, m = 0, n = 0;
              break;

            case L:
              for (; 16 > n;) {
                if (0 === i) break a;
                i--, m += e[g++] << n, n += 8;
              }

              if (c.flags = m, (255 & c.flags) !== J) {
                a.msg = "unknown compression method", c.mode = lb;
                break;
              }

              if (57344 & c.flags) {
                a.msg = "unknown header flags set", c.mode = lb;
                break;
              }

              c.head && (c.head.text = m >> 8 & 1), 512 & c.flags && (Bb[0] = 255 & m, Bb[1] = m >>> 8 & 255, c.check = t(c.check, Bb, 2, 0)), m = 0, n = 0, c.mode = M;

            case M:
              for (; 32 > n;) {
                if (0 === i) break a;
                i--, m += e[g++] << n, n += 8;
              }

              c.head && (c.head.time = m), 512 & c.flags && (Bb[0] = 255 & m, Bb[1] = m >>> 8 & 255, Bb[2] = m >>> 16 & 255, Bb[3] = m >>> 24 & 255, c.check = t(c.check, Bb, 4, 0)), m = 0, n = 0, c.mode = N;

            case N:
              for (; 16 > n;) {
                if (0 === i) break a;
                i--, m += e[g++] << n, n += 8;
              }

              c.head && (c.head.xflags = 255 & m, c.head.os = m >> 8), 512 & c.flags && (Bb[0] = 255 & m, Bb[1] = m >>> 8 & 255, c.check = t(c.check, Bb, 2, 0)), m = 0, n = 0, c.mode = O;

            case O:
              if (1024 & c.flags) {
                for (; 16 > n;) {
                  if (0 === i) break a;
                  i--, m += e[g++] << n, n += 8;
                }

                c.length = m, c.head && (c.head.extra_len = m), 512 & c.flags && (Bb[0] = 255 & m, Bb[1] = m >>> 8 & 255, c.check = t(c.check, Bb, 2, 0)), m = 0, n = 0;
              } else c.head && (c.head.extra = null);

              c.mode = P;

            case P:
              if (1024 & c.flags && (q = c.length, q > i && (q = i), q && (c.head && (wb = c.head.extra_len - c.length, c.head.extra || (c.head.extra = new Array(c.head.extra_len)), r.arraySet(c.head.extra, e, g, q, wb)), 512 & c.flags && (c.check = t(c.check, e, q, g)), i -= q, g += q, c.length -= q), c.length)) break a;
              c.length = 0, c.mode = Q;

            case Q:
              if (2048 & c.flags) {
                if (0 === i) break a;
                q = 0;

                do {
                  wb = e[g + q++], c.head && wb && c.length < 65536 && (c.head.name += String.fromCharCode(wb));
                } while (wb && i > q);

                if (512 & c.flags && (c.check = t(c.check, e, q, g)), i -= q, g += q, wb) break a;
              } else c.head && (c.head.name = null);

              c.length = 0, c.mode = R;

            case R:
              if (4096 & c.flags) {
                if (0 === i) break a;
                q = 0;

                do {
                  wb = e[g + q++], c.head && wb && c.length < 65536 && (c.head.comment += String.fromCharCode(wb));
                } while (wb && i > q);

                if (512 & c.flags && (c.check = t(c.check, e, q, g)), i -= q, g += q, wb) break a;
              } else c.head && (c.head.comment = null);

              c.mode = S;

            case S:
              if (512 & c.flags) {
                for (; 16 > n;) {
                  if (0 === i) break a;
                  i--, m += e[g++] << n, n += 8;
                }

                if (m !== (65535 & c.check)) {
                  a.msg = "header crc mismatch", c.mode = lb;
                  break;
                }

                m = 0, n = 0;
              }

              c.head && (c.head.hcrc = c.flags >> 9 & 1, c.head.done = !0), a.adler = c.check = 0, c.mode = V;
              break;

            case T:
              for (; 32 > n;) {
                if (0 === i) break a;
                i--, m += e[g++] << n, n += 8;
              }

              a.adler = c.check = d(m), m = 0, n = 0, c.mode = U;

            case U:
              if (0 === c.havedict) return a.next_out = h, a.avail_out = j, a.next_in = g, a.avail_in = i, c.hold = m, c.bits = n, E;
              a.adler = c.check = 1, c.mode = V;

            case V:
              if (b === A || b === B) break a;

            case W:
              if (c.last) {
                m >>>= 7 & n, n -= 7 & n, c.mode = ib;
                break;
              }

              for (; 3 > n;) {
                if (0 === i) break a;
                i--, m += e[g++] << n, n += 8;
              }

              switch (c.last = 1 & m, m >>>= 1, n -= 1, 3 & m) {
                case 0:
                  c.mode = X;
                  break;

                case 1:
                  if (k(c), c.mode = bb, b === B) {
                    m >>>= 2, n -= 2;
                    break a;
                  }

                  break;

                case 2:
                  c.mode = $;
                  break;

                case 3:
                  a.msg = "invalid block type", c.mode = lb;
              }

              m >>>= 2, n -= 2;
              break;

            case X:
              for (m >>>= 7 & n, n -= 7 & n; 32 > n;) {
                if (0 === i) break a;
                i--, m += e[g++] << n, n += 8;
              }

              if ((65535 & m) !== (m >>> 16 ^ 65535)) {
                a.msg = "invalid stored block lengths", c.mode = lb;
                break;
              }

              if (c.length = 65535 & m, m = 0, n = 0, c.mode = Y, b === B) break a;

            case Y:
              c.mode = Z;

            case Z:
              if (q = c.length) {
                if (q > i && (q = i), q > j && (q = j), 0 === q) break a;
                r.arraySet(f, e, g, q, h), i -= q, g += q, j -= q, h += q, c.length -= q;
                break;
              }

              c.mode = V;
              break;

            case $:
              for (; 14 > n;) {
                if (0 === i) break a;
                i--, m += e[g++] << n, n += 8;
              }

              if (c.nlen = (31 & m) + 257, m >>>= 5, n -= 5, c.ndist = (31 & m) + 1, m >>>= 5, n -= 5, c.ncode = (15 & m) + 4, m >>>= 4, n -= 4, c.nlen > 286 || c.ndist > 30) {
                a.msg = "too many length or distance symbols", c.mode = lb;
                break;
              }

              c.have = 0, c.mode = _;

            case _:
              for (; c.have < c.ncode;) {
                for (; 3 > n;) {
                  if (0 === i) break a;
                  i--, m += e[g++] << n, n += 8;
                }

                c.lens[Cb[c.have++]] = 7 & m, m >>>= 3, n -= 3;
              }

              for (; c.have < 19;) {
                c.lens[Cb[c.have++]] = 0;
              }

              if (c.lencode = c.lendyn, c.lenbits = 7, yb = {
                bits: c.lenbits
              }, xb = v(w, c.lens, 0, 19, c.lencode, 0, c.work, yb), c.lenbits = yb.bits, xb) {
                a.msg = "invalid code lengths set", c.mode = lb;
                break;
              }

              c.have = 0, c.mode = ab;

            case ab:
              for (; c.have < c.nlen + c.ndist;) {
                for (; Ab = c.lencode[m & (1 << c.lenbits) - 1], qb = Ab >>> 24, rb = Ab >>> 16 & 255, sb = 65535 & Ab, !(n >= qb);) {
                  if (0 === i) break a;
                  i--, m += e[g++] << n, n += 8;
                }

                if (16 > sb) m >>>= qb, n -= qb, c.lens[c.have++] = sb;else {
                  if (16 === sb) {
                    for (zb = qb + 2; zb > n;) {
                      if (0 === i) break a;
                      i--, m += e[g++] << n, n += 8;
                    }

                    if (m >>>= qb, n -= qb, 0 === c.have) {
                      a.msg = "invalid bit length repeat", c.mode = lb;
                      break;
                    }

                    wb = c.lens[c.have - 1], q = 3 + (3 & m), m >>>= 2, n -= 2;
                  } else if (17 === sb) {
                    for (zb = qb + 3; zb > n;) {
                      if (0 === i) break a;
                      i--, m += e[g++] << n, n += 8;
                    }

                    m >>>= qb, n -= qb, wb = 0, q = 3 + (7 & m), m >>>= 3, n -= 3;
                  } else {
                    for (zb = qb + 7; zb > n;) {
                      if (0 === i) break a;
                      i--, m += e[g++] << n, n += 8;
                    }

                    m >>>= qb, n -= qb, wb = 0, q = 11 + (127 & m), m >>>= 7, n -= 7;
                  }

                  if (c.have + q > c.nlen + c.ndist) {
                    a.msg = "invalid bit length repeat", c.mode = lb;
                    break;
                  }

                  for (; q--;) {
                    c.lens[c.have++] = wb;
                  }
                }
              }

              if (c.mode === lb) break;

              if (0 === c.lens[256]) {
                a.msg = "invalid code -- missing end-of-block", c.mode = lb;
                break;
              }

              if (c.lenbits = 9, yb = {
                bits: c.lenbits
              }, xb = v(x, c.lens, 0, c.nlen, c.lencode, 0, c.work, yb), c.lenbits = yb.bits, xb) {
                a.msg = "invalid literal/lengths set", c.mode = lb;
                break;
              }

              if (c.distbits = 6, c.distcode = c.distdyn, yb = {
                bits: c.distbits
              }, xb = v(y, c.lens, c.nlen, c.ndist, c.distcode, 0, c.work, yb), c.distbits = yb.bits, xb) {
                a.msg = "invalid distances set", c.mode = lb;
                break;
              }

              if (c.mode = bb, b === B) break a;

            case bb:
              c.mode = cb;

            case cb:
              if (i >= 6 && j >= 258) {
                a.next_out = h, a.avail_out = j, a.next_in = g, a.avail_in = i, c.hold = m, c.bits = n, u(a, p), h = a.next_out, f = a.output, j = a.avail_out, g = a.next_in, e = a.input, i = a.avail_in, m = c.hold, n = c.bits, c.mode === V && (c.back = -1);
                break;
              }

              for (c.back = 0; Ab = c.lencode[m & (1 << c.lenbits) - 1], qb = Ab >>> 24, rb = Ab >>> 16 & 255, sb = 65535 & Ab, !(n >= qb);) {
                if (0 === i) break a;
                i--, m += e[g++] << n, n += 8;
              }

              if (rb && 0 === (240 & rb)) {
                for (tb = qb, ub = rb, vb = sb; Ab = c.lencode[vb + ((m & (1 << tb + ub) - 1) >> tb)], qb = Ab >>> 24, rb = Ab >>> 16 & 255, sb = 65535 & Ab, !(n >= tb + qb);) {
                  if (0 === i) break a;
                  i--, m += e[g++] << n, n += 8;
                }

                m >>>= tb, n -= tb, c.back += tb;
              }

              if (m >>>= qb, n -= qb, c.back += qb, c.length = sb, 0 === rb) {
                c.mode = hb;
                break;
              }

              if (32 & rb) {
                c.back = -1, c.mode = V;
                break;
              }

              if (64 & rb) {
                a.msg = "invalid literal/length code", c.mode = lb;
                break;
              }

              c.extra = 15 & rb, c.mode = db;

            case db:
              if (c.extra) {
                for (zb = c.extra; zb > n;) {
                  if (0 === i) break a;
                  i--, m += e[g++] << n, n += 8;
                }

                c.length += m & (1 << c.extra) - 1, m >>>= c.extra, n -= c.extra, c.back += c.extra;
              }

              c.was = c.length, c.mode = eb;

            case eb:
              for (; Ab = c.distcode[m & (1 << c.distbits) - 1], qb = Ab >>> 24, rb = Ab >>> 16 & 255, sb = 65535 & Ab, !(n >= qb);) {
                if (0 === i) break a;
                i--, m += e[g++] << n, n += 8;
              }

              if (0 === (240 & rb)) {
                for (tb = qb, ub = rb, vb = sb; Ab = c.distcode[vb + ((m & (1 << tb + ub) - 1) >> tb)], qb = Ab >>> 24, rb = Ab >>> 16 & 255, sb = 65535 & Ab, !(n >= tb + qb);) {
                  if (0 === i) break a;
                  i--, m += e[g++] << n, n += 8;
                }

                m >>>= tb, n -= tb, c.back += tb;
              }

              if (m >>>= qb, n -= qb, c.back += qb, 64 & rb) {
                a.msg = "invalid distance code", c.mode = lb;
                break;
              }

              c.offset = sb, c.extra = 15 & rb, c.mode = fb;

            case fb:
              if (c.extra) {
                for (zb = c.extra; zb > n;) {
                  if (0 === i) break a;
                  i--, m += e[g++] << n, n += 8;
                }

                c.offset += m & (1 << c.extra) - 1, m >>>= c.extra, n -= c.extra, c.back += c.extra;
              }

              if (c.offset > c.dmax) {
                a.msg = "invalid distance too far back", c.mode = lb;
                break;
              }

              c.mode = gb;

            case gb:
              if (0 === j) break a;

              if (q = p - j, c.offset > q) {
                if (q = c.offset - q, q > c.whave && c.sane) {
                  a.msg = "invalid distance too far back", c.mode = lb;
                  break;
                }

                q > c.wnext ? (q -= c.wnext, ob = c.wsize - q) : ob = c.wnext - q, q > c.length && (q = c.length), pb = c.window;
              } else pb = f, ob = h - c.offset, q = c.length;

              q > j && (q = j), j -= q, c.length -= q;

              do {
                f[h++] = pb[ob++];
              } while (--q);

              0 === c.length && (c.mode = cb);
              break;

            case hb:
              if (0 === j) break a;
              f[h++] = c.length, j--, c.mode = cb;
              break;

            case ib:
              if (c.wrap) {
                for (; 32 > n;) {
                  if (0 === i) break a;
                  i--, m |= e[g++] << n, n += 8;
                }

                if (p -= j, a.total_out += p, c.total += p, p && (a.adler = c.check = c.flags ? t(c.check, f, p, h - p) : s(c.check, f, p, h - p)), p = j, (c.flags ? m : d(m)) !== c.check) {
                  a.msg = "incorrect data check", c.mode = lb;
                  break;
                }

                m = 0, n = 0;
              }

              c.mode = jb;

            case jb:
              if (c.wrap && c.flags) {
                for (; 32 > n;) {
                  if (0 === i) break a;
                  i--, m += e[g++] << n, n += 8;
                }

                if (m !== (4294967295 & c.total)) {
                  a.msg = "incorrect length check", c.mode = lb;
                  break;
                }

                m = 0, n = 0;
              }

              c.mode = kb;

            case kb:
              xb = D;
              break a;

            case lb:
              xb = G;
              break a;

            case mb:
              return H;

            case nb:
            default:
              return F;
          }
        }

        return a.next_out = h, a.avail_out = j, a.next_in = g, a.avail_in = i, c.hold = m, c.bits = n, (c.wsize || p !== a.avail_out && c.mode < lb && (c.mode < ib || b !== z)) && l(a, a.output, a.next_out, p - a.avail_out) ? (c.mode = mb, H) : (o -= a.avail_in, p -= a.avail_out, a.total_in += o, a.total_out += p, c.total += p, c.wrap && p && (a.adler = c.check = c.flags ? t(c.check, f, p, a.next_out - p) : s(c.check, f, p, a.next_out - p)), a.data_type = c.bits + (c.last ? 64 : 0) + (c.mode === V ? 128 : 0) + (c.mode === bb || c.mode === Y ? 256 : 0), (0 === o && 0 === p || b === z) && xb === C && (xb = I), xb);
      }

      function n(a) {
        if (!a || !a.state) return F;
        var b = a.state;
        return b.window && (b.window = null), a.state = null, C;
      }

      function o(a, b) {
        var c;
        return a && a.state ? (c = a.state, 0 === (2 & c.wrap) ? F : (c.head = b, b.done = !1, C)) : F;
      }

      var p,
          q,
          r = a("../utils/common"),
          s = a("./adler32"),
          t = a("./crc32"),
          u = a("./inffast"),
          v = a("./inftrees"),
          w = 0,
          x = 1,
          y = 2,
          z = 4,
          A = 5,
          B = 6,
          C = 0,
          D = 1,
          E = 2,
          F = -2,
          G = -3,
          H = -4,
          I = -5,
          J = 8,
          K = 1,
          L = 2,
          M = 3,
          N = 4,
          O = 5,
          P = 6,
          Q = 7,
          R = 8,
          S = 9,
          T = 10,
          U = 11,
          V = 12,
          W = 13,
          X = 14,
          Y = 15,
          Z = 16,
          $ = 17,
          _ = 18,
          ab = 19,
          bb = 20,
          cb = 21,
          db = 22,
          eb = 23,
          fb = 24,
          gb = 25,
          hb = 26,
          ib = 27,
          jb = 28,
          kb = 29,
          lb = 30,
          mb = 31,
          nb = 32,
          ob = 852,
          pb = 592,
          qb = 15,
          rb = qb,
          sb = !0;
      c.inflateReset = g, c.inflateReset2 = h, c.inflateResetKeep = f, c.inflateInit = j, c.inflateInit2 = i, c.inflate = m, c.inflateEnd = n, c.inflateGetHeader = o, c.inflateInfo = "pako inflate (from Nodeca project)";
    }, {
      "../utils/common": 27,
      "./adler32": 29,
      "./crc32": 31,
      "./inffast": 34,
      "./inftrees": 36
    }],
    36: [function (a, b) {
      "use strict";

      var c = a("../utils/common"),
          d = 15,
          e = 852,
          f = 592,
          g = 0,
          h = 1,
          i = 2,
          j = [3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 15, 17, 19, 23, 27, 31, 35, 43, 51, 59, 67, 83, 99, 115, 131, 163, 195, 227, 258, 0, 0],
          k = [16, 16, 16, 16, 16, 16, 16, 16, 17, 17, 17, 17, 18, 18, 18, 18, 19, 19, 19, 19, 20, 20, 20, 20, 21, 21, 21, 21, 16, 72, 78],
          l = [1, 2, 3, 4, 5, 7, 9, 13, 17, 25, 33, 49, 65, 97, 129, 193, 257, 385, 513, 769, 1025, 1537, 2049, 3073, 4097, 6145, 8193, 12289, 16385, 24577, 0, 0],
          m = [16, 16, 16, 16, 17, 17, 18, 18, 19, 19, 20, 20, 21, 21, 22, 22, 23, 23, 24, 24, 25, 25, 26, 26, 27, 27, 28, 28, 29, 29, 64, 64];

      b.exports = function (a, b, n, o, p, q, r, s) {
        var t,
            u,
            v,
            w,
            x,
            y,
            z,
            A,
            B,
            C = s.bits,
            D = 0,
            E = 0,
            F = 0,
            G = 0,
            H = 0,
            I = 0,
            J = 0,
            K = 0,
            L = 0,
            M = 0,
            N = null,
            O = 0,
            P = new c.Buf16(d + 1),
            Q = new c.Buf16(d + 1),
            R = null,
            S = 0;

        for (D = 0; d >= D; D++) {
          P[D] = 0;
        }

        for (E = 0; o > E; E++) {
          P[b[n + E]]++;
        }

        for (H = C, G = d; G >= 1 && 0 === P[G]; G--) {
          ;
        }

        if (H > G && (H = G), 0 === G) return p[q++] = 20971520, p[q++] = 20971520, s.bits = 1, 0;

        for (F = 1; G > F && 0 === P[F]; F++) {
          ;
        }

        for (F > H && (H = F), K = 1, D = 1; d >= D; D++) {
          if (K <<= 1, K -= P[D], 0 > K) return -1;
        }

        if (K > 0 && (a === g || 1 !== G)) return -1;

        for (Q[1] = 0, D = 1; d > D; D++) {
          Q[D + 1] = Q[D] + P[D];
        }

        for (E = 0; o > E; E++) {
          0 !== b[n + E] && (r[Q[b[n + E]]++] = E);
        }

        if (a === g ? (N = R = r, y = 19) : a === h ? (N = j, O -= 257, R = k, S -= 257, y = 256) : (N = l, R = m, y = -1), M = 0, E = 0, D = F, x = q, I = H, J = 0, v = -1, L = 1 << H, w = L - 1, a === h && L > e || a === i && L > f) return 1;

        for (var T = 0;;) {
          T++, z = D - J, r[E] < y ? (A = 0, B = r[E]) : r[E] > y ? (A = R[S + r[E]], B = N[O + r[E]]) : (A = 96, B = 0), t = 1 << D - J, u = 1 << I, F = u;

          do {
            u -= t, p[x + (M >> J) + u] = z << 24 | A << 16 | B | 0;
          } while (0 !== u);

          for (t = 1 << D - 1; M & t;) {
            t >>= 1;
          }

          if (0 !== t ? (M &= t - 1, M += t) : M = 0, E++, 0 === --P[D]) {
            if (D === G) break;
            D = b[n + r[E]];
          }

          if (D > H && (M & w) !== v) {
            for (0 === J && (J = H), x += F, I = D - J, K = 1 << I; G > I + J && (K -= P[I + J], !(0 >= K));) {
              I++, K <<= 1;
            }

            if (L += 1 << I, a === h && L > e || a === i && L > f) return 1;
            v = M & w, p[v] = H << 24 | I << 16 | x - q | 0;
          }
        }

        return 0 !== M && (p[x + M] = D - J << 24 | 64 << 16 | 0), s.bits = H, 0;
      };
    }, {
      "../utils/common": 27
    }],
    37: [function (a, b) {
      "use strict";

      b.exports = {
        2: "need dictionary",
        1: "stream end",
        0: "",
        "-1": "file error",
        "-2": "stream error",
        "-3": "data error",
        "-4": "insufficient memory",
        "-5": "buffer error",
        "-6": "incompatible version"
      };
    }, {}],
    38: [function (a, b, c) {
      "use strict";

      function d(a) {
        for (var b = a.length; --b >= 0;) {
          a[b] = 0;
        }
      }

      function e(a) {
        return 256 > a ? gb[a] : gb[256 + (a >>> 7)];
      }

      function f(a, b) {
        a.pending_buf[a.pending++] = 255 & b, a.pending_buf[a.pending++] = b >>> 8 & 255;
      }

      function g(a, b, c) {
        a.bi_valid > V - c ? (a.bi_buf |= b << a.bi_valid & 65535, f(a, a.bi_buf), a.bi_buf = b >> V - a.bi_valid, a.bi_valid += c - V) : (a.bi_buf |= b << a.bi_valid & 65535, a.bi_valid += c);
      }

      function h(a, b, c) {
        g(a, c[2 * b], c[2 * b + 1]);
      }

      function i(a, b) {
        var c = 0;

        do {
          c |= 1 & a, a >>>= 1, c <<= 1;
        } while (--b > 0);

        return c >>> 1;
      }

      function j(a) {
        16 === a.bi_valid ? (f(a, a.bi_buf), a.bi_buf = 0, a.bi_valid = 0) : a.bi_valid >= 8 && (a.pending_buf[a.pending++] = 255 & a.bi_buf, a.bi_buf >>= 8, a.bi_valid -= 8);
      }

      function k(a, b) {
        var c,
            d,
            e,
            f,
            g,
            h,
            i = b.dyn_tree,
            j = b.max_code,
            k = b.stat_desc.static_tree,
            l = b.stat_desc.has_stree,
            m = b.stat_desc.extra_bits,
            n = b.stat_desc.extra_base,
            o = b.stat_desc.max_length,
            p = 0;

        for (f = 0; U >= f; f++) {
          a.bl_count[f] = 0;
        }

        for (i[2 * a.heap[a.heap_max] + 1] = 0, c = a.heap_max + 1; T > c; c++) {
          d = a.heap[c], f = i[2 * i[2 * d + 1] + 1] + 1, f > o && (f = o, p++), i[2 * d + 1] = f, d > j || (a.bl_count[f]++, g = 0, d >= n && (g = m[d - n]), h = i[2 * d], a.opt_len += h * (f + g), l && (a.static_len += h * (k[2 * d + 1] + g)));
        }

        if (0 !== p) {
          do {
            for (f = o - 1; 0 === a.bl_count[f];) {
              f--;
            }

            a.bl_count[f]--, a.bl_count[f + 1] += 2, a.bl_count[o]--, p -= 2;
          } while (p > 0);

          for (f = o; 0 !== f; f--) {
            for (d = a.bl_count[f]; 0 !== d;) {
              e = a.heap[--c], e > j || (i[2 * e + 1] !== f && (a.opt_len += (f - i[2 * e + 1]) * i[2 * e], i[2 * e + 1] = f), d--);
            }
          }
        }
      }

      function l(a, b, c) {
        var d,
            e,
            f = new Array(U + 1),
            g = 0;

        for (d = 1; U >= d; d++) {
          f[d] = g = g + c[d - 1] << 1;
        }

        for (e = 0; b >= e; e++) {
          var h = a[2 * e + 1];
          0 !== h && (a[2 * e] = i(f[h]++, h));
        }
      }

      function m() {
        var a,
            b,
            c,
            d,
            e,
            f = new Array(U + 1);

        for (c = 0, d = 0; O - 1 > d; d++) {
          for (ib[d] = c, a = 0; a < 1 << _[d]; a++) {
            hb[c++] = d;
          }
        }

        for (hb[c - 1] = d, e = 0, d = 0; 16 > d; d++) {
          for (jb[d] = e, a = 0; a < 1 << ab[d]; a++) {
            gb[e++] = d;
          }
        }

        for (e >>= 7; R > d; d++) {
          for (jb[d] = e << 7, a = 0; a < 1 << ab[d] - 7; a++) {
            gb[256 + e++] = d;
          }
        }

        for (b = 0; U >= b; b++) {
          f[b] = 0;
        }

        for (a = 0; 143 >= a;) {
          eb[2 * a + 1] = 8, a++, f[8]++;
        }

        for (; 255 >= a;) {
          eb[2 * a + 1] = 9, a++, f[9]++;
        }

        for (; 279 >= a;) {
          eb[2 * a + 1] = 7, a++, f[7]++;
        }

        for (; 287 >= a;) {
          eb[2 * a + 1] = 8, a++, f[8]++;
        }

        for (l(eb, Q + 1, f), a = 0; R > a; a++) {
          fb[2 * a + 1] = 5, fb[2 * a] = i(a, 5);
        }

        kb = new nb(eb, _, P + 1, Q, U), lb = new nb(fb, ab, 0, R, U), mb = new nb(new Array(0), bb, 0, S, W);
      }

      function n(a) {
        var b;

        for (b = 0; Q > b; b++) {
          a.dyn_ltree[2 * b] = 0;
        }

        for (b = 0; R > b; b++) {
          a.dyn_dtree[2 * b] = 0;
        }

        for (b = 0; S > b; b++) {
          a.bl_tree[2 * b] = 0;
        }

        a.dyn_ltree[2 * X] = 1, a.opt_len = a.static_len = 0, a.last_lit = a.matches = 0;
      }

      function o(a) {
        a.bi_valid > 8 ? f(a, a.bi_buf) : a.bi_valid > 0 && (a.pending_buf[a.pending++] = a.bi_buf), a.bi_buf = 0, a.bi_valid = 0;
      }

      function p(a, b, c, d) {
        o(a), d && (f(a, c), f(a, ~c)), E.arraySet(a.pending_buf, a.window, b, c, a.pending), a.pending += c;
      }

      function q(a, b, c, d) {
        var e = 2 * b,
            f = 2 * c;
        return a[e] < a[f] || a[e] === a[f] && d[b] <= d[c];
      }

      function r(a, b, c) {
        for (var d = a.heap[c], e = c << 1; e <= a.heap_len && (e < a.heap_len && q(b, a.heap[e + 1], a.heap[e], a.depth) && e++, !q(b, d, a.heap[e], a.depth));) {
          a.heap[c] = a.heap[e], c = e, e <<= 1;
        }

        a.heap[c] = d;
      }

      function s(a, b, c) {
        var d,
            f,
            i,
            j,
            k = 0;
        if (0 !== a.last_lit) do {
          d = a.pending_buf[a.d_buf + 2 * k] << 8 | a.pending_buf[a.d_buf + 2 * k + 1], f = a.pending_buf[a.l_buf + k], k++, 0 === d ? h(a, f, b) : (i = hb[f], h(a, i + P + 1, b), j = _[i], 0 !== j && (f -= ib[i], g(a, f, j)), d--, i = e(d), h(a, i, c), j = ab[i], 0 !== j && (d -= jb[i], g(a, d, j)));
        } while (k < a.last_lit);
        h(a, X, b);
      }

      function t(a, b) {
        var c,
            d,
            e,
            f = b.dyn_tree,
            g = b.stat_desc.static_tree,
            h = b.stat_desc.has_stree,
            i = b.stat_desc.elems,
            j = -1;

        for (a.heap_len = 0, a.heap_max = T, c = 0; i > c; c++) {
          0 !== f[2 * c] ? (a.heap[++a.heap_len] = j = c, a.depth[c] = 0) : f[2 * c + 1] = 0;
        }

        for (; a.heap_len < 2;) {
          e = a.heap[++a.heap_len] = 2 > j ? ++j : 0, f[2 * e] = 1, a.depth[e] = 0, a.opt_len--, h && (a.static_len -= g[2 * e + 1]);
        }

        for (b.max_code = j, c = a.heap_len >> 1; c >= 1; c--) {
          r(a, f, c);
        }

        e = i;

        do {
          c = a.heap[1], a.heap[1] = a.heap[a.heap_len--], r(a, f, 1), d = a.heap[1], a.heap[--a.heap_max] = c, a.heap[--a.heap_max] = d, f[2 * e] = f[2 * c] + f[2 * d], a.depth[e] = (a.depth[c] >= a.depth[d] ? a.depth[c] : a.depth[d]) + 1, f[2 * c + 1] = f[2 * d + 1] = e, a.heap[1] = e++, r(a, f, 1);
        } while (a.heap_len >= 2);

        a.heap[--a.heap_max] = a.heap[1], k(a, b), l(f, j, a.bl_count);
      }

      function u(a, b, c) {
        var d,
            e,
            f = -1,
            g = b[1],
            h = 0,
            i = 7,
            j = 4;

        for (0 === g && (i = 138, j = 3), b[2 * (c + 1) + 1] = 65535, d = 0; c >= d; d++) {
          e = g, g = b[2 * (d + 1) + 1], ++h < i && e === g || (j > h ? a.bl_tree[2 * e] += h : 0 !== e ? (e !== f && a.bl_tree[2 * e]++, a.bl_tree[2 * Y]++) : 10 >= h ? a.bl_tree[2 * Z]++ : a.bl_tree[2 * $]++, h = 0, f = e, 0 === g ? (i = 138, j = 3) : e === g ? (i = 6, j = 3) : (i = 7, j = 4));
        }
      }

      function v(a, b, c) {
        var d,
            e,
            f = -1,
            i = b[1],
            j = 0,
            k = 7,
            l = 4;

        for (0 === i && (k = 138, l = 3), d = 0; c >= d; d++) {
          if (e = i, i = b[2 * (d + 1) + 1], !(++j < k && e === i)) {
            if (l > j) {
              do {
                h(a, e, a.bl_tree);
              } while (0 !== --j);
            } else 0 !== e ? (e !== f && (h(a, e, a.bl_tree), j--), h(a, Y, a.bl_tree), g(a, j - 3, 2)) : 10 >= j ? (h(a, Z, a.bl_tree), g(a, j - 3, 3)) : (h(a, $, a.bl_tree), g(a, j - 11, 7));

            j = 0, f = e, 0 === i ? (k = 138, l = 3) : e === i ? (k = 6, l = 3) : (k = 7, l = 4);
          }
        }
      }

      function w(a) {
        var b;

        for (u(a, a.dyn_ltree, a.l_desc.max_code), u(a, a.dyn_dtree, a.d_desc.max_code), t(a, a.bl_desc), b = S - 1; b >= 3 && 0 === a.bl_tree[2 * cb[b] + 1]; b--) {
          ;
        }

        return a.opt_len += 3 * (b + 1) + 5 + 5 + 4, b;
      }

      function x(a, b, c, d) {
        var e;

        for (g(a, b - 257, 5), g(a, c - 1, 5), g(a, d - 4, 4), e = 0; d > e; e++) {
          g(a, a.bl_tree[2 * cb[e] + 1], 3);
        }

        v(a, a.dyn_ltree, b - 1), v(a, a.dyn_dtree, c - 1);
      }

      function y(a) {
        var b,
            c = 4093624447;

        for (b = 0; 31 >= b; b++, c >>>= 1) {
          if (1 & c && 0 !== a.dyn_ltree[2 * b]) return G;
        }

        if (0 !== a.dyn_ltree[18] || 0 !== a.dyn_ltree[20] || 0 !== a.dyn_ltree[26]) return H;

        for (b = 32; P > b; b++) {
          if (0 !== a.dyn_ltree[2 * b]) return H;
        }

        return G;
      }

      function z(a) {
        pb || (m(), pb = !0), a.l_desc = new ob(a.dyn_ltree, kb), a.d_desc = new ob(a.dyn_dtree, lb), a.bl_desc = new ob(a.bl_tree, mb), a.bi_buf = 0, a.bi_valid = 0, n(a);
      }

      function A(a, b, c, d) {
        g(a, (J << 1) + (d ? 1 : 0), 3), p(a, b, c, !0);
      }

      function B(a) {
        g(a, K << 1, 3), h(a, X, eb), j(a);
      }

      function C(a, b, c, d) {
        var e,
            f,
            h = 0;
        a.level > 0 ? (a.strm.data_type === I && (a.strm.data_type = y(a)), t(a, a.l_desc), t(a, a.d_desc), h = w(a), e = a.opt_len + 3 + 7 >>> 3, f = a.static_len + 3 + 7 >>> 3, e >= f && (e = f)) : e = f = c + 5, e >= c + 4 && -1 !== b ? A(a, b, c, d) : a.strategy === F || f === e ? (g(a, (K << 1) + (d ? 1 : 0), 3), s(a, eb, fb)) : (g(a, (L << 1) + (d ? 1 : 0), 3), x(a, a.l_desc.max_code + 1, a.d_desc.max_code + 1, h + 1), s(a, a.dyn_ltree, a.dyn_dtree)), n(a), d && o(a);
      }

      function D(a, b, c) {
        return a.pending_buf[a.d_buf + 2 * a.last_lit] = b >>> 8 & 255, a.pending_buf[a.d_buf + 2 * a.last_lit + 1] = 255 & b, a.pending_buf[a.l_buf + a.last_lit] = 255 & c, a.last_lit++, 0 === b ? a.dyn_ltree[2 * c]++ : (a.matches++, b--, a.dyn_ltree[2 * (hb[c] + P + 1)]++, a.dyn_dtree[2 * e(b)]++), a.last_lit === a.lit_bufsize - 1;
      }

      var E = a("../utils/common"),
          F = 4,
          G = 0,
          H = 1,
          I = 2,
          J = 0,
          K = 1,
          L = 2,
          M = 3,
          N = 258,
          O = 29,
          P = 256,
          Q = P + 1 + O,
          R = 30,
          S = 19,
          T = 2 * Q + 1,
          U = 15,
          V = 16,
          W = 7,
          X = 256,
          Y = 16,
          Z = 17,
          $ = 18,
          _ = [0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 0],
          ab = [0, 0, 0, 0, 1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9, 9, 10, 10, 11, 11, 12, 12, 13, 13],
          bb = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 3, 7],
          cb = [16, 17, 18, 0, 8, 7, 9, 6, 10, 5, 11, 4, 12, 3, 13, 2, 14, 1, 15],
          db = 512,
          eb = new Array(2 * (Q + 2));
      d(eb);
      var fb = new Array(2 * R);
      d(fb);
      var gb = new Array(db);
      d(gb);
      var hb = new Array(N - M + 1);
      d(hb);
      var ib = new Array(O);
      d(ib);
      var jb = new Array(R);
      d(jb);

      var kb,
          lb,
          mb,
          nb = function nb(a, b, c, d, e) {
        this.static_tree = a, this.extra_bits = b, this.extra_base = c, this.elems = d, this.max_length = e, this.has_stree = a && a.length;
      },
          ob = function ob(a, b) {
        this.dyn_tree = a, this.max_code = 0, this.stat_desc = b;
      },
          pb = !1;

      c._tr_init = z, c._tr_stored_block = A, c._tr_flush_block = C, c._tr_tally = D, c._tr_align = B;
    }, {
      "../utils/common": 27
    }],
    39: [function (a, b) {
      "use strict";

      function c() {
        this.input = null, this.next_in = 0, this.avail_in = 0, this.total_in = 0, this.output = null, this.next_out = 0, this.avail_out = 0, this.total_out = 0, this.msg = "", this.state = null, this.data_type = 2, this.adler = 0;
      }

      b.exports = c;
    }, {}]
  }, {}, [9])(9);
});
/*!
   Copyright 2008-2021 SpryMedia Ltd.

 This source file is free software, available under the following license:
   MIT license - http://datatables.net/license

 This source file is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 or FITNESS FOR A PARTICULAR PURPOSE. See the license files for details.

 For details please refer to: http://www.datatables.net
 DataTables 1.11.5
 ©2008-2021 SpryMedia Ltd - datatables.net/license
*/

var $jscomp = $jscomp || {};
$jscomp.scope = {};

$jscomp.findInternal = function (l, z, A) {
  l instanceof String && (l = String(l));

  for (var q = l.length, E = 0; E < q; E++) {
    var P = l[E];
    if (z.call(A, P, E, l)) return {
      i: E,
      v: P
    };
  }

  return {
    i: -1,
    v: void 0
  };
};

$jscomp.ASSUME_ES5 = !1;
$jscomp.ASSUME_NO_NATIVE_MAP = !1;
$jscomp.ASSUME_NO_NATIVE_SET = !1;
$jscomp.SIMPLE_FROUND_POLYFILL = !1;
$jscomp.ISOLATE_POLYFILLS = !1;
$jscomp.defineProperty = $jscomp.ASSUME_ES5 || "function" == typeof Object.defineProperties ? Object.defineProperty : function (l, z, A) {
  if (l == Array.prototype || l == Object.prototype) return l;
  l[z] = A.value;
  return l;
};

$jscomp.getGlobal = function (l) {
  l = ["object" == (typeof globalThis === "undefined" ? "undefined" : _typeof(globalThis)) && globalThis, l, "object" == (typeof window === "undefined" ? "undefined" : _typeof(window)) && window, "object" == (typeof self === "undefined" ? "undefined" : _typeof(self)) && self, "object" == (typeof global === "undefined" ? "undefined" : _typeof(global)) && global];

  for (var z = 0; z < l.length; ++z) {
    var A = l[z];
    if (A && A.Math == Math) return A;
  }

  throw Error("Cannot find global object");
};

$jscomp.global = $jscomp.getGlobal(void 0);
$jscomp.IS_SYMBOL_NATIVE = "function" === typeof Symbol && "symbol" === _typeof(Symbol("x"));
$jscomp.TRUST_ES6_POLYFILLS = !$jscomp.ISOLATE_POLYFILLS || $jscomp.IS_SYMBOL_NATIVE;
$jscomp.polyfills = {};
$jscomp.propertyToPolyfillSymbol = {};
$jscomp.POLYFILL_PREFIX = "$jscp$";

var $jscomp$lookupPolyfilledValue = function $jscomp$lookupPolyfilledValue(l, z) {
  var A = $jscomp.propertyToPolyfillSymbol[z];
  if (null == A) return l[z];
  A = l[A];
  return void 0 !== A ? A : l[z];
};

$jscomp.polyfill = function (l, z, A, q) {
  z && ($jscomp.ISOLATE_POLYFILLS ? $jscomp.polyfillIsolated(l, z, A, q) : $jscomp.polyfillUnisolated(l, z, A, q));
};

$jscomp.polyfillUnisolated = function (l, z, A, q) {
  A = $jscomp.global;
  l = l.split(".");

  for (q = 0; q < l.length - 1; q++) {
    var E = l[q];
    if (!(E in A)) return;
    A = A[E];
  }

  l = l[l.length - 1];
  q = A[l];
  z = z(q);
  z != q && null != z && $jscomp.defineProperty(A, l, {
    configurable: !0,
    writable: !0,
    value: z
  });
};

$jscomp.polyfillIsolated = function (l, z, A, q) {
  var E = l.split(".");
  l = 1 === E.length;
  q = E[0];
  q = !l && q in $jscomp.polyfills ? $jscomp.polyfills : $jscomp.global;

  for (var P = 0; P < E.length - 1; P++) {
    var ma = E[P];
    if (!(ma in q)) return;
    q = q[ma];
  }

  E = E[E.length - 1];
  A = $jscomp.IS_SYMBOL_NATIVE && "es6" === A ? q[E] : null;
  z = z(A);
  null != z && (l ? $jscomp.defineProperty($jscomp.polyfills, E, {
    configurable: !0,
    writable: !0,
    value: z
  }) : z !== A && ($jscomp.propertyToPolyfillSymbol[E] = $jscomp.IS_SYMBOL_NATIVE ? $jscomp.global.Symbol(E) : $jscomp.POLYFILL_PREFIX + E, E = $jscomp.propertyToPolyfillSymbol[E], $jscomp.defineProperty(q, E, {
    configurable: !0,
    writable: !0,
    value: z
  })));
};

$jscomp.polyfill("Array.prototype.find", function (l) {
  return l ? l : function (z, A) {
    return $jscomp.findInternal(this, z, A).v;
  };
}, "es6", "es3");

(function (l) {
  "function" === typeof define && define.amd ? define(["jquery"], function (z) {
    return l(z, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (z, A) {
    z || (z = window);
    A || (A = "undefined" !== typeof window ? require("jquery") : require("jquery")(z));
    return l(A, z, z.document);
  } : window.DataTable = l(jQuery, window, document);
})(function (l, z, A, q) {
  function E(a) {
    var b,
        c,
        d = {};
    l.each(a, function (e, h) {
      (b = e.match(/^([^A-Z]+?)([A-Z])/)) && -1 !== "a aa ai ao as b fn i m o s ".indexOf(b[1] + " ") && (c = e.replace(b[0], b[2].toLowerCase()), d[c] = e, "o" === b[1] && E(a[e]));
    });
    a._hungarianMap = d;
  }

  function P(a, b, c) {
    a._hungarianMap || E(a);
    var d;
    l.each(b, function (e, h) {
      d = a._hungarianMap[e];
      d === q || !c && b[d] !== q || ("o" === d.charAt(0) ? (b[d] || (b[d] = {}), l.extend(!0, b[d], b[e]), P(a[d], b[d], c)) : b[d] = b[e]);
    });
  }

  function ma(a) {
    var b = u.defaults.oLanguage,
        c = b.sDecimal;
    c && Xa(c);

    if (a) {
      var d = a.sZeroRecords;
      !a.sEmptyTable && d && "No data available in table" === b.sEmptyTable && X(a, a, "sZeroRecords", "sEmptyTable");
      !a.sLoadingRecords && d && "Loading..." === b.sLoadingRecords && X(a, a, "sZeroRecords", "sLoadingRecords");
      a.sInfoThousands && (a.sThousands = a.sInfoThousands);
      (a = a.sDecimal) && c !== a && Xa(a);
    }
  }

  function zb(a) {
    S(a, "ordering", "bSort");
    S(a, "orderMulti", "bSortMulti");
    S(a, "orderClasses", "bSortClasses");
    S(a, "orderCellsTop", "bSortCellsTop");
    S(a, "order", "aaSorting");
    S(a, "orderFixed", "aaSortingFixed");
    S(a, "paging", "bPaginate");
    S(a, "pagingType", "sPaginationType");
    S(a, "pageLength", "iDisplayLength");
    S(a, "searching", "bFilter");
    "boolean" === typeof a.sScrollX && (a.sScrollX = a.sScrollX ? "100%" : "");
    "boolean" === typeof a.scrollX && (a.scrollX = a.scrollX ? "100%" : "");
    if (a = a.aoSearchCols) for (var b = 0, c = a.length; b < c; b++) {
      a[b] && P(u.models.oSearch, a[b]);
    }
  }

  function Ab(a) {
    S(a, "orderable", "bSortable");
    S(a, "orderData", "aDataSort");
    S(a, "orderSequence", "asSorting");
    S(a, "orderDataType", "sortDataType");
    var b = a.aDataSort;
    "number" !== typeof b || Array.isArray(b) || (a.aDataSort = [b]);
  }

  function Bb(a) {
    if (!u.__browser) {
      var b = {};
      u.__browser = b;
      var c = l("<div/>").css({
        position: "fixed",
        top: 0,
        left: -1 * l(z).scrollLeft(),
        height: 1,
        width: 1,
        overflow: "hidden"
      }).append(l("<div/>").css({
        position: "absolute",
        top: 1,
        left: 1,
        width: 100,
        overflow: "scroll"
      }).append(l("<div/>").css({
        width: "100%",
        height: 10
      }))).appendTo("body"),
          d = c.children(),
          e = d.children();
      b.barWidth = d[0].offsetWidth - d[0].clientWidth;
      b.bScrollOversize = 100 === e[0].offsetWidth && 100 !== d[0].clientWidth;
      b.bScrollbarLeft = 1 !== Math.round(e.offset().left);
      b.bBounding = c[0].getBoundingClientRect().width ? !0 : !1;
      c.remove();
    }

    l.extend(a.oBrowser, u.__browser);
    a.oScroll.iBarWidth = u.__browser.barWidth;
  }

  function Cb(a, b, c, d, e, h) {
    var f = !1;

    if (c !== q) {
      var g = c;
      f = !0;
    }

    for (; d !== e;) {
      a.hasOwnProperty(d) && (g = f ? b(g, a[d], d, a) : a[d], f = !0, d += h);
    }

    return g;
  }

  function Ya(a, b) {
    var c = u.defaults.column,
        d = a.aoColumns.length;
    c = l.extend({}, u.models.oColumn, c, {
      nTh: b ? b : A.createElement("th"),
      sTitle: c.sTitle ? c.sTitle : b ? b.innerHTML : "",
      aDataSort: c.aDataSort ? c.aDataSort : [d],
      mData: c.mData ? c.mData : d,
      idx: d
    });
    a.aoColumns.push(c);
    c = a.aoPreSearchCols;
    c[d] = l.extend({}, u.models.oSearch, c[d]);
    Ga(a, d, l(b).data());
  }

  function Ga(a, b, c) {
    b = a.aoColumns[b];
    var d = a.oClasses,
        e = l(b.nTh);

    if (!b.sWidthOrig) {
      b.sWidthOrig = e.attr("width") || null;
      var h = (e.attr("style") || "").match(/width:\s*(\d+[pxem%]+)/);
      h && (b.sWidthOrig = h[1]);
    }

    c !== q && null !== c && (Ab(c), P(u.defaults.column, c, !0), c.mDataProp === q || c.mData || (c.mData = c.mDataProp), c.sType && (b._sManualType = c.sType), c.className && !c.sClass && (c.sClass = c.className), c.sClass && e.addClass(c.sClass), l.extend(b, c), X(b, c, "sWidth", "sWidthOrig"), c.iDataSort !== q && (b.aDataSort = [c.iDataSort]), X(b, c, "aDataSort"));
    var f = b.mData,
        g = na(f),
        k = b.mRender ? na(b.mRender) : null;

    c = function c(m) {
      return "string" === typeof m && -1 !== m.indexOf("@");
    };

    b._bAttrSrc = l.isPlainObject(f) && (c(f.sort) || c(f.type) || c(f.filter));
    b._setter = null;

    b.fnGetData = function (m, n, p) {
      var t = g(m, n, q, p);
      return k && n ? k(t, n, m, p) : t;
    };

    b.fnSetData = function (m, n, p) {
      return ha(f)(m, n, p);
    };

    "number" !== typeof f && (a._rowReadObject = !0);
    a.oFeatures.bSort || (b.bSortable = !1, e.addClass(d.sSortableNone));
    a = -1 !== l.inArray("asc", b.asSorting);
    c = -1 !== l.inArray("desc", b.asSorting);
    b.bSortable && (a || c) ? a && !c ? (b.sSortingClass = d.sSortableAsc, b.sSortingClassJUI = d.sSortJUIAscAllowed) : !a && c ? (b.sSortingClass = d.sSortableDesc, b.sSortingClassJUI = d.sSortJUIDescAllowed) : (b.sSortingClass = d.sSortable, b.sSortingClassJUI = d.sSortJUI) : (b.sSortingClass = d.sSortableNone, b.sSortingClassJUI = "");
  }

  function sa(a) {
    if (!1 !== a.oFeatures.bAutoWidth) {
      var b = a.aoColumns;
      Za(a);

      for (var c = 0, d = b.length; c < d; c++) {
        b[c].nTh.style.width = b[c].sWidth;
      }
    }

    b = a.oScroll;
    "" === b.sY && "" === b.sX || Ha(a);
    F(a, null, "column-sizing", [a]);
  }

  function ta(a, b) {
    a = Ia(a, "bVisible");
    return "number" === typeof a[b] ? a[b] : null;
  }

  function ua(a, b) {
    a = Ia(a, "bVisible");
    b = l.inArray(b, a);
    return -1 !== b ? b : null;
  }

  function oa(a) {
    var b = 0;
    l.each(a.aoColumns, function (c, d) {
      d.bVisible && "none" !== l(d.nTh).css("display") && b++;
    });
    return b;
  }

  function Ia(a, b) {
    var c = [];
    l.map(a.aoColumns, function (d, e) {
      d[b] && c.push(e);
    });
    return c;
  }

  function $a(a) {
    var b = a.aoColumns,
        c = a.aoData,
        d = u.ext.type.detect,
        e,
        h,
        f;
    var g = 0;

    for (e = b.length; g < e; g++) {
      var k = b[g];
      var m = [];
      if (!k.sType && k._sManualType) k.sType = k._sManualType;else if (!k.sType) {
        var n = 0;

        for (h = d.length; n < h; n++) {
          var p = 0;

          for (f = c.length; p < f; p++) {
            m[p] === q && (m[p] = T(a, p, g, "type"));
            var t = d[n](m[p], a);
            if (!t && n !== d.length - 1) break;
            if ("html" === t && !Z(m[p])) break;
          }

          if (t) {
            k.sType = t;
            break;
          }
        }

        k.sType || (k.sType = "string");
      }
    }
  }

  function Db(a, b, c, d) {
    var e,
        h,
        f,
        g = a.aoColumns;
    if (b) for (e = b.length - 1; 0 <= e; e--) {
      var k = b[e];
      var m = k.targets !== q ? k.targets : k.aTargets;
      Array.isArray(m) || (m = [m]);
      var n = 0;

      for (h = m.length; n < h; n++) {
        if ("number" === typeof m[n] && 0 <= m[n]) {
          for (; g.length <= m[n];) {
            Ya(a);
          }

          d(m[n], k);
        } else if ("number" === typeof m[n] && 0 > m[n]) d(g.length + m[n], k);else if ("string" === typeof m[n]) {
          var p = 0;

          for (f = g.length; p < f; p++) {
            ("_all" == m[n] || l(g[p].nTh).hasClass(m[n])) && d(p, k);
          }
        }
      }
    }
    if (c) for (e = 0, a = c.length; e < a; e++) {
      d(e, c[e]);
    }
  }

  function ia(a, b, c, d) {
    var e = a.aoData.length,
        h = l.extend(!0, {}, u.models.oRow, {
      src: c ? "dom" : "data",
      idx: e
    });
    h._aData = b;
    a.aoData.push(h);

    for (var f = a.aoColumns, g = 0, k = f.length; g < k; g++) {
      f[g].sType = null;
    }

    a.aiDisplayMaster.push(e);
    b = a.rowIdFn(b);
    b !== q && (a.aIds[b] = h);
    !c && a.oFeatures.bDeferRender || ab(a, e, c, d);
    return e;
  }

  function Ja(a, b) {
    var c;
    b instanceof l || (b = l(b));
    return b.map(function (d, e) {
      c = bb(a, e);
      return ia(a, c.data, e, c.cells);
    });
  }

  function T(a, b, c, d) {
    "search" === d ? d = "filter" : "order" === d && (d = "sort");
    var e = a.iDraw,
        h = a.aoColumns[c],
        f = a.aoData[b]._aData,
        g = h.sDefaultContent,
        k = h.fnGetData(f, d, {
      settings: a,
      row: b,
      col: c
    });
    if (k === q) return a.iDrawError != e && null === g && (da(a, 0, "Requested unknown parameter " + ("function" == typeof h.mData ? "{function}" : "'" + h.mData + "'") + " for row " + b + ", column " + c, 4), a.iDrawError = e), g;
    if ((k === f || null === k) && null !== g && d !== q) k = g;else if ("function" === typeof k) return k.call(f);
    if (null === k && "display" === d) return "";
    "filter" === d && (a = u.ext.type.search, a[h.sType] && (k = a[h.sType](k)));
    return k;
  }

  function Eb(a, b, c, d) {
    a.aoColumns[c].fnSetData(a.aoData[b]._aData, d, {
      settings: a,
      row: b,
      col: c
    });
  }

  function cb(a) {
    return l.map(a.match(/(\\.|[^\.])+/g) || [""], function (b) {
      return b.replace(/\\\./g, ".");
    });
  }

  function db(a) {
    return U(a.aoData, "_aData");
  }

  function Ka(a) {
    a.aoData.length = 0;
    a.aiDisplayMaster.length = 0;
    a.aiDisplay.length = 0;
    a.aIds = {};
  }

  function La(a, b, c) {
    for (var d = -1, e = 0, h = a.length; e < h; e++) {
      a[e] == b ? d = e : a[e] > b && a[e]--;
    }

    -1 != d && c === q && a.splice(d, 1);
  }

  function va(a, b, c, d) {
    var e = a.aoData[b],
        h,
        f = function f(k, m) {
      for (; k.childNodes.length;) {
        k.removeChild(k.firstChild);
      }

      k.innerHTML = T(a, b, m, "display");
    };

    if ("dom" !== c && (c && "auto" !== c || "dom" !== e.src)) {
      var g = e.anCells;
      if (g) if (d !== q) f(g[d], d);else for (c = 0, h = g.length; c < h; c++) {
        f(g[c], c);
      }
    } else e._aData = bb(a, e, d, d === q ? q : e._aData).data;

    e._aSortData = null;
    e._aFilterData = null;
    f = a.aoColumns;
    if (d !== q) f[d].sType = null;else {
      c = 0;

      for (h = f.length; c < h; c++) {
        f[c].sType = null;
      }

      eb(a, e);
    }
  }

  function bb(a, b, c, d) {
    var e = [],
        h = b.firstChild,
        f,
        g = 0,
        k,
        m = a.aoColumns,
        n = a._rowReadObject;
    d = d !== q ? d : n ? {} : [];

    var p = function p(x, w) {
      if ("string" === typeof x) {
        var r = x.indexOf("@");
        -1 !== r && (r = x.substring(r + 1), ha(x)(d, w.getAttribute(r)));
      }
    },
        t = function t(x) {
      if (c === q || c === g) f = m[g], k = x.innerHTML.trim(), f && f._bAttrSrc ? (ha(f.mData._)(d, k), p(f.mData.sort, x), p(f.mData.type, x), p(f.mData.filter, x)) : n ? (f._setter || (f._setter = ha(f.mData)), f._setter(d, k)) : d[g] = k;
      g++;
    };

    if (h) for (; h;) {
      var v = h.nodeName.toUpperCase();
      if ("TD" == v || "TH" == v) t(h), e.push(h);
      h = h.nextSibling;
    } else for (e = b.anCells, h = 0, v = e.length; h < v; h++) {
      t(e[h]);
    }
    (b = b.firstChild ? b : b.nTr) && (b = b.getAttribute("id")) && ha(a.rowId)(d, b);
    return {
      data: d,
      cells: e
    };
  }

  function ab(a, b, c, d) {
    var e = a.aoData[b],
        h = e._aData,
        f = [],
        g,
        k;

    if (null === e.nTr) {
      var m = c || A.createElement("tr");
      e.nTr = m;
      e.anCells = f;
      m._DT_RowIndex = b;
      eb(a, e);
      var n = 0;

      for (g = a.aoColumns.length; n < g; n++) {
        var p = a.aoColumns[n];
        e = (k = c ? !1 : !0) ? A.createElement(p.sCellType) : d[n];
        e._DT_CellIndex = {
          row: b,
          column: n
        };
        f.push(e);
        if (k || !(!p.mRender && p.mData === n || l.isPlainObject(p.mData) && p.mData._ === n + ".display")) e.innerHTML = T(a, b, n, "display");
        p.sClass && (e.className += " " + p.sClass);
        p.bVisible && !c ? m.appendChild(e) : !p.bVisible && c && e.parentNode.removeChild(e);
        p.fnCreatedCell && p.fnCreatedCell.call(a.oInstance, e, T(a, b, n), h, b, n);
      }

      F(a, "aoRowCreatedCallback", null, [m, h, b, f]);
    }
  }

  function eb(a, b) {
    var c = b.nTr,
        d = b._aData;

    if (c) {
      if (a = a.rowIdFn(d)) c.id = a;
      d.DT_RowClass && (a = d.DT_RowClass.split(" "), b.__rowc = b.__rowc ? Ma(b.__rowc.concat(a)) : a, l(c).removeClass(b.__rowc.join(" ")).addClass(d.DT_RowClass));
      d.DT_RowAttr && l(c).attr(d.DT_RowAttr);
      d.DT_RowData && l(c).data(d.DT_RowData);
    }
  }

  function Fb(a) {
    var b,
        c,
        d = a.nTHead,
        e = a.nTFoot,
        h = 0 === l("th, td", d).length,
        f = a.oClasses,
        g = a.aoColumns;
    h && (c = l("<tr/>").appendTo(d));
    var k = 0;

    for (b = g.length; k < b; k++) {
      var m = g[k];
      var n = l(m.nTh).addClass(m.sClass);
      h && n.appendTo(c);
      a.oFeatures.bSort && (n.addClass(m.sSortingClass), !1 !== m.bSortable && (n.attr("tabindex", a.iTabIndex).attr("aria-controls", a.sTableId), fb(a, m.nTh, k)));
      m.sTitle != n[0].innerHTML && n.html(m.sTitle);
      gb(a, "header")(a, n, m, f);
    }

    h && wa(a.aoHeader, d);
    l(d).children("tr").children("th, td").addClass(f.sHeaderTH);
    l(e).children("tr").children("th, td").addClass(f.sFooterTH);
    if (null !== e) for (a = a.aoFooter[0], k = 0, b = a.length; k < b; k++) {
      m = g[k], m.nTf = a[k].cell, m.sClass && l(m.nTf).addClass(m.sClass);
    }
  }

  function xa(a, b, c) {
    var d,
        e,
        h = [],
        f = [],
        g = a.aoColumns.length;

    if (b) {
      c === q && (c = !1);
      var k = 0;

      for (d = b.length; k < d; k++) {
        h[k] = b[k].slice();
        h[k].nTr = b[k].nTr;

        for (e = g - 1; 0 <= e; e--) {
          a.aoColumns[e].bVisible || c || h[k].splice(e, 1);
        }

        f.push([]);
      }

      k = 0;

      for (d = h.length; k < d; k++) {
        if (a = h[k].nTr) for (; e = a.firstChild;) {
          a.removeChild(e);
        }
        e = 0;

        for (b = h[k].length; e < b; e++) {
          var m = g = 1;

          if (f[k][e] === q) {
            a.appendChild(h[k][e].cell);

            for (f[k][e] = 1; h[k + g] !== q && h[k][e].cell == h[k + g][e].cell;) {
              f[k + g][e] = 1, g++;
            }

            for (; h[k][e + m] !== q && h[k][e].cell == h[k][e + m].cell;) {
              for (c = 0; c < g; c++) {
                f[k + c][e + m] = 1;
              }

              m++;
            }

            l(h[k][e].cell).attr("rowspan", g).attr("colspan", m);
          }
        }
      }
    }
  }

  function ja(a, b) {
    var c = "ssp" == Q(a),
        d = a.iInitDisplayStart;
    d !== q && -1 !== d && (a._iDisplayStart = c ? d : d >= a.fnRecordsDisplay() ? 0 : d, a.iInitDisplayStart = -1);
    c = F(a, "aoPreDrawCallback", "preDraw", [a]);
    if (-1 !== l.inArray(!1, c)) V(a, !1);else {
      c = [];
      var e = 0;
      d = a.asStripeClasses;
      var h = d.length,
          f = a.oLanguage,
          g = "ssp" == Q(a),
          k = a.aiDisplay,
          m = a._iDisplayStart,
          n = a.fnDisplayEnd();
      a.bDrawing = !0;
      if (a.bDeferLoading) a.bDeferLoading = !1, a.iDraw++, V(a, !1);else if (!g) a.iDraw++;else if (!a.bDestroying && !b) {
        Gb(a);
        return;
      }
      if (0 !== k.length) for (b = g ? a.aoData.length : n, f = g ? 0 : m; f < b; f++) {
        g = k[f];
        var p = a.aoData[g];
        null === p.nTr && ab(a, g);
        var t = p.nTr;

        if (0 !== h) {
          var v = d[e % h];
          p._sRowStripe != v && (l(t).removeClass(p._sRowStripe).addClass(v), p._sRowStripe = v);
        }

        F(a, "aoRowCallback", null, [t, p._aData, e, f, g]);
        c.push(t);
        e++;
      } else e = f.sZeroRecords, 1 == a.iDraw && "ajax" == Q(a) ? e = f.sLoadingRecords : f.sEmptyTable && 0 === a.fnRecordsTotal() && (e = f.sEmptyTable), c[0] = l("<tr/>", {
        "class": h ? d[0] : ""
      }).append(l("<td />", {
        valign: "top",
        colSpan: oa(a),
        "class": a.oClasses.sRowEmpty
      }).html(e))[0];
      F(a, "aoHeaderCallback", "header", [l(a.nTHead).children("tr")[0], db(a), m, n, k]);
      F(a, "aoFooterCallback", "footer", [l(a.nTFoot).children("tr")[0], db(a), m, n, k]);
      d = l(a.nTBody);
      d.children().detach();
      d.append(l(c));
      F(a, "aoDrawCallback", "draw", [a]);
      a.bSorted = !1;
      a.bFiltered = !1;
      a.bDrawing = !1;
    }
  }

  function ka(a, b) {
    var c = a.oFeatures,
        d = c.bFilter;
    c.bSort && Hb(a);
    d ? ya(a, a.oPreviousSearch) : a.aiDisplay = a.aiDisplayMaster.slice();
    !0 !== b && (a._iDisplayStart = 0);
    a._drawHold = b;
    ja(a);
    a._drawHold = !1;
  }

  function Ib(a) {
    var b = a.oClasses,
        c = l(a.nTable);
    c = l("<div/>").insertBefore(c);
    var d = a.oFeatures,
        e = l("<div/>", {
      id: a.sTableId + "_wrapper",
      "class": b.sWrapper + (a.nTFoot ? "" : " " + b.sNoFooter)
    });
    a.nHolding = c[0];
    a.nTableWrapper = e[0];
    a.nTableReinsertBefore = a.nTable.nextSibling;

    for (var h = a.sDom.split(""), f, g, k, m, n, p, t = 0; t < h.length; t++) {
      f = null;
      g = h[t];

      if ("<" == g) {
        k = l("<div/>")[0];
        m = h[t + 1];

        if ("'" == m || '"' == m) {
          n = "";

          for (p = 2; h[t + p] != m;) {
            n += h[t + p], p++;
          }

          "H" == n ? n = b.sJUIHeader : "F" == n && (n = b.sJUIFooter);
          -1 != n.indexOf(".") ? (m = n.split("."), k.id = m[0].substr(1, m[0].length - 1), k.className = m[1]) : "#" == n.charAt(0) ? k.id = n.substr(1, n.length - 1) : k.className = n;
          t += p;
        }

        e.append(k);
        e = l(k);
      } else if (">" == g) e = e.parent();else if ("l" == g && d.bPaginate && d.bLengthChange) f = Jb(a);else if ("f" == g && d.bFilter) f = Kb(a);else if ("r" == g && d.bProcessing) f = Lb(a);else if ("t" == g) f = Mb(a);else if ("i" == g && d.bInfo) f = Nb(a);else if ("p" == g && d.bPaginate) f = Ob(a);else if (0 !== u.ext.feature.length) for (k = u.ext.feature, p = 0, m = k.length; p < m; p++) {
        if (g == k[p].cFeature) {
          f = k[p].fnInit(a);
          break;
        }
      }

      f && (k = a.aanFeatures, k[g] || (k[g] = []), k[g].push(f), e.append(f));
    }

    c.replaceWith(e);
    a.nHolding = null;
  }

  function wa(a, b) {
    b = l(b).children("tr");
    var c, d, e;
    a.splice(0, a.length);
    var h = 0;

    for (e = b.length; h < e; h++) {
      a.push([]);
    }

    h = 0;

    for (e = b.length; h < e; h++) {
      var f = b[h];

      for (c = f.firstChild; c;) {
        if ("TD" == c.nodeName.toUpperCase() || "TH" == c.nodeName.toUpperCase()) {
          var g = 1 * c.getAttribute("colspan");
          var k = 1 * c.getAttribute("rowspan");
          g = g && 0 !== g && 1 !== g ? g : 1;
          k = k && 0 !== k && 1 !== k ? k : 1;
          var m = 0;

          for (d = a[h]; d[m];) {
            m++;
          }

          var n = m;
          var p = 1 === g ? !0 : !1;

          for (d = 0; d < g; d++) {
            for (m = 0; m < k; m++) {
              a[h + m][n + d] = {
                cell: c,
                unique: p
              }, a[h + m].nTr = f;
            }
          }
        }

        c = c.nextSibling;
      }
    }
  }

  function Na(a, b, c) {
    var d = [];
    c || (c = a.aoHeader, b && (c = [], wa(c, b)));
    b = 0;

    for (var e = c.length; b < e; b++) {
      for (var h = 0, f = c[b].length; h < f; h++) {
        !c[b][h].unique || d[h] && a.bSortCellsTop || (d[h] = c[b][h].cell);
      }
    }

    return d;
  }

  function Oa(a, b, c) {
    F(a, "aoServerParams", "serverParams", [b]);

    if (b && Array.isArray(b)) {
      var d = {},
          e = /(.*?)\[\]$/;
      l.each(b, function (n, p) {
        (n = p.name.match(e)) ? (n = n[0], d[n] || (d[n] = []), d[n].push(p.value)) : d[p.name] = p.value;
      });
      b = d;
    }

    var h = a.ajax,
        f = a.oInstance,
        g = function g(n) {
      var p = a.jqXHR ? a.jqXHR.status : null;
      if (null === n || "number" === typeof p && 204 == p) n = {}, za(a, n, []);
      (p = n.error || n.sError) && da(a, 0, p);
      a.json = n;
      F(a, null, "xhr", [a, n, a.jqXHR]);
      c(n);
    };

    if (l.isPlainObject(h) && h.data) {
      var k = h.data;
      var m = "function" === typeof k ? k(b, a) : k;
      b = "function" === typeof k && m ? m : l.extend(!0, b, m);
      delete h.data;
    }

    m = {
      data: b,
      success: g,
      dataType: "json",
      cache: !1,
      type: a.sServerMethod,
      error: function error(n, p, t) {
        t = F(a, null, "xhr", [a, null, a.jqXHR]);
        -1 === l.inArray(!0, t) && ("parsererror" == p ? da(a, 0, "Invalid JSON response", 1) : 4 === n.readyState && da(a, 0, "Ajax error", 7));
        V(a, !1);
      }
    };
    a.oAjaxData = b;
    F(a, null, "preXhr", [a, b]);
    a.fnServerData ? a.fnServerData.call(f, a.sAjaxSource, l.map(b, function (n, p) {
      return {
        name: p,
        value: n
      };
    }), g, a) : a.sAjaxSource || "string" === typeof h ? a.jqXHR = l.ajax(l.extend(m, {
      url: h || a.sAjaxSource
    })) : "function" === typeof h ? a.jqXHR = h.call(f, b, g, a) : (a.jqXHR = l.ajax(l.extend(m, h)), h.data = k);
  }

  function Gb(a) {
    a.iDraw++;
    V(a, !0);
    Oa(a, Pb(a), function (b) {
      Qb(a, b);
    });
  }

  function Pb(a) {
    var b = a.aoColumns,
        c = b.length,
        d = a.oFeatures,
        e = a.oPreviousSearch,
        h = a.aoPreSearchCols,
        f = [],
        g = pa(a);
    var k = a._iDisplayStart;
    var m = !1 !== d.bPaginate ? a._iDisplayLength : -1;

    var n = function n(x, w) {
      f.push({
        name: x,
        value: w
      });
    };

    n("sEcho", a.iDraw);
    n("iColumns", c);
    n("sColumns", U(b, "sName").join(","));
    n("iDisplayStart", k);
    n("iDisplayLength", m);
    var p = {
      draw: a.iDraw,
      columns: [],
      order: [],
      start: k,
      length: m,
      search: {
        value: e.sSearch,
        regex: e.bRegex
      }
    };

    for (k = 0; k < c; k++) {
      var t = b[k];
      var v = h[k];
      m = "function" == typeof t.mData ? "function" : t.mData;
      p.columns.push({
        data: m,
        name: t.sName,
        searchable: t.bSearchable,
        orderable: t.bSortable,
        search: {
          value: v.sSearch,
          regex: v.bRegex
        }
      });
      n("mDataProp_" + k, m);
      d.bFilter && (n("sSearch_" + k, v.sSearch), n("bRegex_" + k, v.bRegex), n("bSearchable_" + k, t.bSearchable));
      d.bSort && n("bSortable_" + k, t.bSortable);
    }

    d.bFilter && (n("sSearch", e.sSearch), n("bRegex", e.bRegex));
    d.bSort && (l.each(g, function (x, w) {
      p.order.push({
        column: w.col,
        dir: w.dir
      });
      n("iSortCol_" + x, w.col);
      n("sSortDir_" + x, w.dir);
    }), n("iSortingCols", g.length));
    b = u.ext.legacy.ajax;
    return null === b ? a.sAjaxSource ? f : p : b ? f : p;
  }

  function Qb(a, b) {
    var c = function c(f, g) {
      return b[f] !== q ? b[f] : b[g];
    },
        d = za(a, b),
        e = c("sEcho", "draw"),
        h = c("iTotalRecords", "recordsTotal");

    c = c("iTotalDisplayRecords", "recordsFiltered");

    if (e !== q) {
      if (1 * e < a.iDraw) return;
      a.iDraw = 1 * e;
    }

    d || (d = []);
    Ka(a);
    a._iRecordsTotal = parseInt(h, 10);
    a._iRecordsDisplay = parseInt(c, 10);
    e = 0;

    for (h = d.length; e < h; e++) {
      ia(a, d[e]);
    }

    a.aiDisplay = a.aiDisplayMaster.slice();
    ja(a, !0);
    a._bInitComplete || Pa(a, b);
    V(a, !1);
  }

  function za(a, b, c) {
    a = l.isPlainObject(a.ajax) && a.ajax.dataSrc !== q ? a.ajax.dataSrc : a.sAjaxDataProp;
    if (!c) return "data" === a ? b.aaData || b[a] : "" !== a ? na(a)(b) : b;
    ha(a)(b, c);
  }

  function Kb(a) {
    var b = a.oClasses,
        c = a.sTableId,
        d = a.oLanguage,
        e = a.oPreviousSearch,
        h = a.aanFeatures,
        f = '<input type="search" class="' + b.sFilterInput + '"/>',
        g = d.sSearch;
    g = g.match(/_INPUT_/) ? g.replace("_INPUT_", f) : g + f;
    b = l("<div/>", {
      id: h.f ? null : c + "_filter",
      "class": b.sFilter
    }).append(l("<label/>").append(g));

    var k = function k(n) {
      var p = this.value ? this.value : "";
      e["return"] && "Enter" !== n.key || p == e.sSearch || (ya(a, {
        sSearch: p,
        bRegex: e.bRegex,
        bSmart: e.bSmart,
        bCaseInsensitive: e.bCaseInsensitive,
        "return": e["return"]
      }), a._iDisplayStart = 0, ja(a));
    };

    h = null !== a.searchDelay ? a.searchDelay : "ssp" === Q(a) ? 400 : 0;
    var m = l("input", b).val(e.sSearch).attr("placeholder", d.sSearchPlaceholder).on("keyup.DT search.DT input.DT paste.DT cut.DT", h ? hb(k, h) : k).on("mouseup", function (n) {
      setTimeout(function () {
        k.call(m[0], n);
      }, 10);
    }).on("keypress.DT", function (n) {
      if (13 == n.keyCode) return !1;
    }).attr("aria-controls", c);
    l(a.nTable).on("search.dt.DT", function (n, p) {
      if (a === p) try {
        m[0] !== A.activeElement && m.val(e.sSearch);
      } catch (t) {}
    });
    return b[0];
  }

  function ya(a, b, c) {
    var d = a.oPreviousSearch,
        e = a.aoPreSearchCols,
        h = function h(g) {
      d.sSearch = g.sSearch;
      d.bRegex = g.bRegex;
      d.bSmart = g.bSmart;
      d.bCaseInsensitive = g.bCaseInsensitive;
      d["return"] = g["return"];
    },
        f = function f(g) {
      return g.bEscapeRegex !== q ? !g.bEscapeRegex : g.bRegex;
    };

    $a(a);

    if ("ssp" != Q(a)) {
      Rb(a, b.sSearch, c, f(b), b.bSmart, b.bCaseInsensitive, b["return"]);
      h(b);

      for (b = 0; b < e.length; b++) {
        Sb(a, e[b].sSearch, b, f(e[b]), e[b].bSmart, e[b].bCaseInsensitive);
      }

      Tb(a);
    } else h(b);

    a.bFiltered = !0;
    F(a, null, "search", [a]);
  }

  function Tb(a) {
    for (var b = u.ext.search, c = a.aiDisplay, d, e, h = 0, f = b.length; h < f; h++) {
      for (var g = [], k = 0, m = c.length; k < m; k++) {
        e = c[k], d = a.aoData[e], b[h](a, d._aFilterData, e, d._aData, k) && g.push(e);
      }

      c.length = 0;
      l.merge(c, g);
    }
  }

  function Sb(a, b, c, d, e, h) {
    if ("" !== b) {
      var f = [],
          g = a.aiDisplay;
      d = ib(b, d, e, h);

      for (e = 0; e < g.length; e++) {
        b = a.aoData[g[e]]._aFilterData[c], d.test(b) && f.push(g[e]);
      }

      a.aiDisplay = f;
    }
  }

  function Rb(a, b, c, d, e, h) {
    e = ib(b, d, e, h);
    var f = a.oPreviousSearch.sSearch,
        g = a.aiDisplayMaster;
    h = [];
    0 !== u.ext.search.length && (c = !0);
    var k = Ub(a);
    if (0 >= b.length) a.aiDisplay = g.slice();else {
      if (k || c || d || f.length > b.length || 0 !== b.indexOf(f) || a.bSorted) a.aiDisplay = g.slice();
      b = a.aiDisplay;

      for (c = 0; c < b.length; c++) {
        e.test(a.aoData[b[c]]._sFilterRow) && h.push(b[c]);
      }

      a.aiDisplay = h;
    }
  }

  function ib(a, b, c, d) {
    a = b ? a : jb(a);
    c && (a = "^(?=.*?" + l.map(a.match(/"[^"]+"|[^ ]+/g) || [""], function (e) {
      if ('"' === e.charAt(0)) {
        var h = e.match(/^"(.*)"$/);
        e = h ? h[1] : e;
      }

      return e.replace('"', "");
    }).join(")(?=.*?") + ").*$");
    return new RegExp(a, d ? "i" : "");
  }

  function Ub(a) {
    var b = a.aoColumns,
        c,
        d;
    var e = !1;
    var h = 0;

    for (c = a.aoData.length; h < c; h++) {
      var f = a.aoData[h];

      if (!f._aFilterData) {
        var g = [];
        e = 0;

        for (d = b.length; e < d; e++) {
          var k = b[e];
          k.bSearchable ? (k = T(a, h, e, "filter"), null === k && (k = ""), "string" !== typeof k && k.toString && (k = k.toString())) : k = "";
          k.indexOf && -1 !== k.indexOf("&") && (Qa.innerHTML = k, k = tc ? Qa.textContent : Qa.innerText);
          k.replace && (k = k.replace(/[\r\n\u2028]/g, ""));
          g.push(k);
        }

        f._aFilterData = g;
        f._sFilterRow = g.join("  ");
        e = !0;
      }
    }

    return e;
  }

  function Vb(a) {
    return {
      search: a.sSearch,
      smart: a.bSmart,
      regex: a.bRegex,
      caseInsensitive: a.bCaseInsensitive
    };
  }

  function Wb(a) {
    return {
      sSearch: a.search,
      bSmart: a.smart,
      bRegex: a.regex,
      bCaseInsensitive: a.caseInsensitive
    };
  }

  function Nb(a) {
    var b = a.sTableId,
        c = a.aanFeatures.i,
        d = l("<div/>", {
      "class": a.oClasses.sInfo,
      id: c ? null : b + "_info"
    });
    c || (a.aoDrawCallback.push({
      fn: Xb,
      sName: "information"
    }), d.attr("role", "status").attr("aria-live", "polite"), l(a.nTable).attr("aria-describedby", b + "_info"));
    return d[0];
  }

  function Xb(a) {
    var b = a.aanFeatures.i;

    if (0 !== b.length) {
      var c = a.oLanguage,
          d = a._iDisplayStart + 1,
          e = a.fnDisplayEnd(),
          h = a.fnRecordsTotal(),
          f = a.fnRecordsDisplay(),
          g = f ? c.sInfo : c.sInfoEmpty;
      f !== h && (g += " " + c.sInfoFiltered);
      g += c.sInfoPostFix;
      g = Yb(a, g);
      c = c.fnInfoCallback;
      null !== c && (g = c.call(a.oInstance, a, d, e, h, f, g));
      l(b).html(g);
    }
  }

  function Yb(a, b) {
    var c = a.fnFormatNumber,
        d = a._iDisplayStart + 1,
        e = a._iDisplayLength,
        h = a.fnRecordsDisplay(),
        f = -1 === e;
    return b.replace(/_START_/g, c.call(a, d)).replace(/_END_/g, c.call(a, a.fnDisplayEnd())).replace(/_MAX_/g, c.call(a, a.fnRecordsTotal())).replace(/_TOTAL_/g, c.call(a, h)).replace(/_PAGE_/g, c.call(a, f ? 1 : Math.ceil(d / e))).replace(/_PAGES_/g, c.call(a, f ? 1 : Math.ceil(h / e)));
  }

  function Aa(a) {
    var b = a.iInitDisplayStart,
        c = a.aoColumns;
    var d = a.oFeatures;
    var e = a.bDeferLoading;

    if (a.bInitialised) {
      Ib(a);
      Fb(a);
      xa(a, a.aoHeader);
      xa(a, a.aoFooter);
      V(a, !0);
      d.bAutoWidth && Za(a);
      var h = 0;

      for (d = c.length; h < d; h++) {
        var f = c[h];
        f.sWidth && (f.nTh.style.width = K(f.sWidth));
      }

      F(a, null, "preInit", [a]);
      ka(a);
      c = Q(a);
      if ("ssp" != c || e) "ajax" == c ? Oa(a, [], function (g) {
        var k = za(a, g);

        for (h = 0; h < k.length; h++) {
          ia(a, k[h]);
        }

        a.iInitDisplayStart = b;
        ka(a);
        V(a, !1);
        Pa(a, g);
      }, a) : (V(a, !1), Pa(a));
    } else setTimeout(function () {
      Aa(a);
    }, 200);
  }

  function Pa(a, b) {
    a._bInitComplete = !0;
    (b || a.oInit.aaData) && sa(a);
    F(a, null, "plugin-init", [a, b]);
    F(a, "aoInitComplete", "init", [a, b]);
  }

  function kb(a, b) {
    b = parseInt(b, 10);
    a._iDisplayLength = b;
    lb(a);
    F(a, null, "length", [a, b]);
  }

  function Jb(a) {
    var b = a.oClasses,
        c = a.sTableId,
        d = a.aLengthMenu,
        e = Array.isArray(d[0]),
        h = e ? d[0] : d;
    d = e ? d[1] : d;
    e = l("<select/>", {
      name: c + "_length",
      "aria-controls": c,
      "class": b.sLengthSelect
    });

    for (var f = 0, g = h.length; f < g; f++) {
      e[0][f] = new Option("number" === typeof d[f] ? a.fnFormatNumber(d[f]) : d[f], h[f]);
    }

    var k = l("<div><label/></div>").addClass(b.sLength);
    a.aanFeatures.l || (k[0].id = c + "_length");
    k.children().append(a.oLanguage.sLengthMenu.replace("_MENU_", e[0].outerHTML));
    l("select", k).val(a._iDisplayLength).on("change.DT", function (m) {
      kb(a, l(this).val());
      ja(a);
    });
    l(a.nTable).on("length.dt.DT", function (m, n, p) {
      a === n && l("select", k).val(p);
    });
    return k[0];
  }

  function Ob(a) {
    var b = a.sPaginationType,
        c = u.ext.pager[b],
        d = "function" === typeof c,
        e = function e(f) {
      ja(f);
    };

    b = l("<div/>").addClass(a.oClasses.sPaging + b)[0];
    var h = a.aanFeatures;
    d || c.fnInit(a, b, e);
    h.p || (b.id = a.sTableId + "_paginate", a.aoDrawCallback.push({
      fn: function fn(f) {
        if (d) {
          var g = f._iDisplayStart,
              k = f._iDisplayLength,
              m = f.fnRecordsDisplay(),
              n = -1 === k;
          g = n ? 0 : Math.ceil(g / k);
          k = n ? 1 : Math.ceil(m / k);
          m = c(g, k);
          var p;
          n = 0;

          for (p = h.p.length; n < p; n++) {
            gb(f, "pageButton")(f, h.p[n], n, m, g, k);
          }
        } else c.fnUpdate(f, e);
      },
      sName: "pagination"
    }));
    return b;
  }

  function Ra(a, b, c) {
    var d = a._iDisplayStart,
        e = a._iDisplayLength,
        h = a.fnRecordsDisplay();
    0 === h || -1 === e ? d = 0 : "number" === typeof b ? (d = b * e, d > h && (d = 0)) : "first" == b ? d = 0 : "previous" == b ? (d = 0 <= e ? d - e : 0, 0 > d && (d = 0)) : "next" == b ? d + e < h && (d += e) : "last" == b ? d = Math.floor((h - 1) / e) * e : da(a, 0, "Unknown paging action: " + b, 5);
    b = a._iDisplayStart !== d;
    a._iDisplayStart = d;
    b && (F(a, null, "page", [a]), c && ja(a));
    return b;
  }

  function Lb(a) {
    return l("<div/>", {
      id: a.aanFeatures.r ? null : a.sTableId + "_processing",
      "class": a.oClasses.sProcessing
    }).html(a.oLanguage.sProcessing).insertBefore(a.nTable)[0];
  }

  function V(a, b) {
    a.oFeatures.bProcessing && l(a.aanFeatures.r).css("display", b ? "block" : "none");
    F(a, null, "processing", [a, b]);
  }

  function Mb(a) {
    var b = l(a.nTable),
        c = a.oScroll;
    if ("" === c.sX && "" === c.sY) return a.nTable;
    var d = c.sX,
        e = c.sY,
        h = a.oClasses,
        f = b.children("caption"),
        g = f.length ? f[0]._captionSide : null,
        k = l(b[0].cloneNode(!1)),
        m = l(b[0].cloneNode(!1)),
        n = b.children("tfoot");
    n.length || (n = null);
    k = l("<div/>", {
      "class": h.sScrollWrapper
    }).append(l("<div/>", {
      "class": h.sScrollHead
    }).css({
      overflow: "hidden",
      position: "relative",
      border: 0,
      width: d ? d ? K(d) : null : "100%"
    }).append(l("<div/>", {
      "class": h.sScrollHeadInner
    }).css({
      "box-sizing": "content-box",
      width: c.sXInner || "100%"
    }).append(k.removeAttr("id").css("margin-left", 0).append("top" === g ? f : null).append(b.children("thead"))))).append(l("<div/>", {
      "class": h.sScrollBody
    }).css({
      position: "relative",
      overflow: "auto",
      width: d ? K(d) : null
    }).append(b));
    n && k.append(l("<div/>", {
      "class": h.sScrollFoot
    }).css({
      overflow: "hidden",
      border: 0,
      width: d ? d ? K(d) : null : "100%"
    }).append(l("<div/>", {
      "class": h.sScrollFootInner
    }).append(m.removeAttr("id").css("margin-left", 0).append("bottom" === g ? f : null).append(b.children("tfoot")))));
    b = k.children();
    var p = b[0];
    h = b[1];
    var t = n ? b[2] : null;
    if (d) l(h).on("scroll.DT", function (v) {
      v = this.scrollLeft;
      p.scrollLeft = v;
      n && (t.scrollLeft = v);
    });
    l(h).css("max-height", e);
    c.bCollapse || l(h).css("height", e);
    a.nScrollHead = p;
    a.nScrollBody = h;
    a.nScrollFoot = t;
    a.aoDrawCallback.push({
      fn: Ha,
      sName: "scrolling"
    });
    return k[0];
  }

  function Ha(a) {
    var b = a.oScroll,
        c = b.sX,
        d = b.sXInner,
        e = b.sY;
    b = b.iBarWidth;
    var h = l(a.nScrollHead),
        f = h[0].style,
        g = h.children("div"),
        k = g[0].style,
        m = g.children("table");
    g = a.nScrollBody;
    var n = l(g),
        p = g.style,
        t = l(a.nScrollFoot).children("div"),
        v = t.children("table"),
        x = l(a.nTHead),
        w = l(a.nTable),
        r = w[0],
        C = r.style,
        G = a.nTFoot ? l(a.nTFoot) : null,
        aa = a.oBrowser,
        L = aa.bScrollOversize;
    U(a.aoColumns, "nTh");

    var O = [],
        I = [],
        H = [],
        ea = [],
        Y,
        Ba = function Ba(D) {
      D = D.style;
      D.paddingTop = "0";
      D.paddingBottom = "0";
      D.borderTopWidth = "0";
      D.borderBottomWidth = "0";
      D.height = 0;
    };

    var fa = g.scrollHeight > g.clientHeight;
    if (a.scrollBarVis !== fa && a.scrollBarVis !== q) a.scrollBarVis = fa, sa(a);else {
      a.scrollBarVis = fa;
      w.children("thead, tfoot").remove();

      if (G) {
        var ba = G.clone().prependTo(w);
        var la = G.find("tr");
        ba = ba.find("tr");
      }

      var mb = x.clone().prependTo(w);
      x = x.find("tr");
      fa = mb.find("tr");
      mb.find("th, td").removeAttr("tabindex");
      c || (p.width = "100%", h[0].style.width = "100%");
      l.each(Na(a, mb), function (D, W) {
        Y = ta(a, D);
        W.style.width = a.aoColumns[Y].sWidth;
      });
      G && ca(function (D) {
        D.style.width = "";
      }, ba);
      h = w.outerWidth();
      "" === c ? (C.width = "100%", L && (w.find("tbody").height() > g.offsetHeight || "scroll" == n.css("overflow-y")) && (C.width = K(w.outerWidth() - b)), h = w.outerWidth()) : "" !== d && (C.width = K(d), h = w.outerWidth());
      ca(Ba, fa);
      ca(function (D) {
        var W = z.getComputedStyle ? z.getComputedStyle(D).width : K(l(D).width());
        H.push(D.innerHTML);
        O.push(W);
      }, fa);
      ca(function (D, W) {
        D.style.width = O[W];
      }, x);
      l(fa).css("height", 0);
      G && (ca(Ba, ba), ca(function (D) {
        ea.push(D.innerHTML);
        I.push(K(l(D).css("width")));
      }, ba), ca(function (D, W) {
        D.style.width = I[W];
      }, la), l(ba).height(0));
      ca(function (D, W) {
        D.innerHTML = '<div class="dataTables_sizing">' + H[W] + "</div>";
        D.childNodes[0].style.height = "0";
        D.childNodes[0].style.overflow = "hidden";
        D.style.width = O[W];
      }, fa);
      G && ca(function (D, W) {
        D.innerHTML = '<div class="dataTables_sizing">' + ea[W] + "</div>";
        D.childNodes[0].style.height = "0";
        D.childNodes[0].style.overflow = "hidden";
        D.style.width = I[W];
      }, ba);
      Math.round(w.outerWidth()) < Math.round(h) ? (la = g.scrollHeight > g.offsetHeight || "scroll" == n.css("overflow-y") ? h + b : h, L && (g.scrollHeight > g.offsetHeight || "scroll" == n.css("overflow-y")) && (C.width = K(la - b)), "" !== c && "" === d || da(a, 1, "Possible column misalignment", 6)) : la = "100%";
      p.width = K(la);
      f.width = K(la);
      G && (a.nScrollFoot.style.width = K(la));
      !e && L && (p.height = K(r.offsetHeight + b));
      c = w.outerWidth();
      m[0].style.width = K(c);
      k.width = K(c);
      d = w.height() > g.clientHeight || "scroll" == n.css("overflow-y");
      e = "padding" + (aa.bScrollbarLeft ? "Left" : "Right");
      k[e] = d ? b + "px" : "0px";
      G && (v[0].style.width = K(c), t[0].style.width = K(c), t[0].style[e] = d ? b + "px" : "0px");
      w.children("colgroup").insertBefore(w.children("thead"));
      n.trigger("scroll");
      !a.bSorted && !a.bFiltered || a._drawHold || (g.scrollTop = 0);
    }
  }

  function ca(a, b, c) {
    for (var d = 0, e = 0, h = b.length, f, g; e < h;) {
      f = b[e].firstChild;

      for (g = c ? c[e].firstChild : null; f;) {
        1 === f.nodeType && (c ? a(f, g, d) : a(f, d), d++), f = f.nextSibling, g = c ? g.nextSibling : null;
      }

      e++;
    }
  }

  function Za(a) {
    var b = a.nTable,
        c = a.aoColumns,
        d = a.oScroll,
        e = d.sY,
        h = d.sX,
        f = d.sXInner,
        g = c.length,
        k = Ia(a, "bVisible"),
        m = l("th", a.nTHead),
        n = b.getAttribute("width"),
        p = b.parentNode,
        t = !1,
        v,
        x = a.oBrowser;
    d = x.bScrollOversize;
    (v = b.style.width) && -1 !== v.indexOf("%") && (n = v);

    for (v = 0; v < k.length; v++) {
      var w = c[k[v]];
      null !== w.sWidth && (w.sWidth = Zb(w.sWidthOrig, p), t = !0);
    }

    if (d || !t && !h && !e && g == oa(a) && g == m.length) for (v = 0; v < g; v++) {
      k = ta(a, v), null !== k && (c[k].sWidth = K(m.eq(v).width()));
    } else {
      g = l(b).clone().css("visibility", "hidden").removeAttr("id");
      g.find("tbody tr").remove();
      var r = l("<tr/>").appendTo(g.find("tbody"));
      g.find("thead, tfoot").remove();
      g.append(l(a.nTHead).clone()).append(l(a.nTFoot).clone());
      g.find("tfoot th, tfoot td").css("width", "");
      m = Na(a, g.find("thead")[0]);

      for (v = 0; v < k.length; v++) {
        w = c[k[v]], m[v].style.width = null !== w.sWidthOrig && "" !== w.sWidthOrig ? K(w.sWidthOrig) : "", w.sWidthOrig && h && l(m[v]).append(l("<div/>").css({
          width: w.sWidthOrig,
          margin: 0,
          padding: 0,
          border: 0,
          height: 1
        }));
      }

      if (a.aoData.length) for (v = 0; v < k.length; v++) {
        t = k[v], w = c[t], l($b(a, t)).clone(!1).append(w.sContentPadding).appendTo(r);
      }
      l("[name]", g).removeAttr("name");
      w = l("<div/>").css(h || e ? {
        position: "absolute",
        top: 0,
        left: 0,
        height: 1,
        right: 0,
        overflow: "hidden"
      } : {}).append(g).appendTo(p);
      h && f ? g.width(f) : h ? (g.css("width", "auto"), g.removeAttr("width"), g.width() < p.clientWidth && n && g.width(p.clientWidth)) : e ? g.width(p.clientWidth) : n && g.width(n);

      for (v = e = 0; v < k.length; v++) {
        p = l(m[v]), f = p.outerWidth() - p.width(), p = x.bBounding ? Math.ceil(m[v].getBoundingClientRect().width) : p.outerWidth(), e += p, c[k[v]].sWidth = K(p - f);
      }

      b.style.width = K(e);
      w.remove();
    }
    n && (b.style.width = K(n));
    !n && !h || a._reszEvt || (b = function b() {
      l(z).on("resize.DT-" + a.sInstance, hb(function () {
        sa(a);
      }));
    }, d ? setTimeout(b, 1E3) : b(), a._reszEvt = !0);
  }

  function Zb(a, b) {
    if (!a) return 0;
    a = l("<div/>").css("width", K(a)).appendTo(b || A.body);
    b = a[0].offsetWidth;
    a.remove();
    return b;
  }

  function $b(a, b) {
    var c = ac(a, b);
    if (0 > c) return null;
    var d = a.aoData[c];
    return d.nTr ? d.anCells[b] : l("<td/>").html(T(a, c, b, "display"))[0];
  }

  function ac(a, b) {
    for (var c, d = -1, e = -1, h = 0, f = a.aoData.length; h < f; h++) {
      c = T(a, h, b, "display") + "", c = c.replace(uc, ""), c = c.replace(/&nbsp;/g, " "), c.length > d && (d = c.length, e = h);
    }

    return e;
  }

  function K(a) {
    return null === a ? "0px" : "number" == typeof a ? 0 > a ? "0px" : a + "px" : a.match(/\d$/) ? a + "px" : a;
  }

  function pa(a) {
    var b = [],
        c = a.aoColumns;
    var d = a.aaSortingFixed;
    var e = l.isPlainObject(d);
    var h = [];

    var f = function f(n) {
      n.length && !Array.isArray(n[0]) ? h.push(n) : l.merge(h, n);
    };

    Array.isArray(d) && f(d);
    e && d.pre && f(d.pre);
    f(a.aaSorting);
    e && d.post && f(d.post);

    for (a = 0; a < h.length; a++) {
      var g = h[a][0];
      f = c[g].aDataSort;
      d = 0;

      for (e = f.length; d < e; d++) {
        var k = f[d];
        var m = c[k].sType || "string";
        h[a]._idx === q && (h[a]._idx = l.inArray(h[a][1], c[k].asSorting));
        b.push({
          src: g,
          col: k,
          dir: h[a][1],
          index: h[a]._idx,
          type: m,
          formatter: u.ext.type.order[m + "-pre"]
        });
      }
    }

    return b;
  }

  function Hb(a) {
    var b,
        c = [],
        d = u.ext.type.order,
        e = a.aoData,
        h = 0,
        f = a.aiDisplayMaster;
    $a(a);
    var g = pa(a);
    var k = 0;

    for (b = g.length; k < b; k++) {
      var m = g[k];
      m.formatter && h++;
      bc(a, m.col);
    }

    if ("ssp" != Q(a) && 0 !== g.length) {
      k = 0;

      for (b = f.length; k < b; k++) {
        c[f[k]] = k;
      }

      h === g.length ? f.sort(function (n, p) {
        var t,
            v = g.length,
            x = e[n]._aSortData,
            w = e[p]._aSortData;

        for (t = 0; t < v; t++) {
          var r = g[t];
          var C = x[r.col];
          var G = w[r.col];
          C = C < G ? -1 : C > G ? 1 : 0;
          if (0 !== C) return "asc" === r.dir ? C : -C;
        }

        C = c[n];
        G = c[p];
        return C < G ? -1 : C > G ? 1 : 0;
      }) : f.sort(function (n, p) {
        var t,
            v = g.length,
            x = e[n]._aSortData,
            w = e[p]._aSortData;

        for (t = 0; t < v; t++) {
          var r = g[t];
          var C = x[r.col];
          var G = w[r.col];
          r = d[r.type + "-" + r.dir] || d["string-" + r.dir];
          C = r(C, G);
          if (0 !== C) return C;
        }

        C = c[n];
        G = c[p];
        return C < G ? -1 : C > G ? 1 : 0;
      });
    }

    a.bSorted = !0;
  }

  function cc(a) {
    var b = a.aoColumns,
        c = pa(a);
    a = a.oLanguage.oAria;

    for (var d = 0, e = b.length; d < e; d++) {
      var h = b[d];
      var f = h.asSorting;
      var g = h.ariaTitle || h.sTitle.replace(/<.*?>/g, "");
      var k = h.nTh;
      k.removeAttribute("aria-sort");
      h.bSortable && (0 < c.length && c[0].col == d ? (k.setAttribute("aria-sort", "asc" == c[0].dir ? "ascending" : "descending"), h = f[c[0].index + 1] || f[0]) : h = f[0], g += "asc" === h ? a.sSortAscending : a.sSortDescending);
      k.setAttribute("aria-label", g);
    }
  }

  function nb(a, b, c, d) {
    var e = a.aaSorting,
        h = a.aoColumns[b].asSorting,
        f = function f(g, k) {
      var m = g._idx;
      m === q && (m = l.inArray(g[1], h));
      return m + 1 < h.length ? m + 1 : k ? null : 0;
    };

    "number" === typeof e[0] && (e = a.aaSorting = [e]);
    c && a.oFeatures.bSortMulti ? (c = l.inArray(b, U(e, "0")), -1 !== c ? (b = f(e[c], !0), null === b && 1 === e.length && (b = 0), null === b ? e.splice(c, 1) : (e[c][1] = h[b], e[c]._idx = b)) : (e.push([b, h[0], 0]), e[e.length - 1]._idx = 0)) : e.length && e[0][0] == b ? (b = f(e[0]), e.length = 1, e[0][1] = h[b], e[0]._idx = b) : (e.length = 0, e.push([b, h[0]]), e[0]._idx = 0);
    ka(a);
    "function" == typeof d && d(a);
  }

  function fb(a, b, c, d) {
    var e = a.aoColumns[c];
    ob(b, {}, function (h) {
      !1 !== e.bSortable && (a.oFeatures.bProcessing ? (V(a, !0), setTimeout(function () {
        nb(a, c, h.shiftKey, d);
        "ssp" !== Q(a) && V(a, !1);
      }, 0)) : nb(a, c, h.shiftKey, d));
    });
  }

  function Sa(a) {
    var b = a.aLastSort,
        c = a.oClasses.sSortColumn,
        d = pa(a),
        e = a.oFeatures,
        h;

    if (e.bSort && e.bSortClasses) {
      e = 0;

      for (h = b.length; e < h; e++) {
        var f = b[e].src;
        l(U(a.aoData, "anCells", f)).removeClass(c + (2 > e ? e + 1 : 3));
      }

      e = 0;

      for (h = d.length; e < h; e++) {
        f = d[e].src, l(U(a.aoData, "anCells", f)).addClass(c + (2 > e ? e + 1 : 3));
      }
    }

    a.aLastSort = d;
  }

  function bc(a, b) {
    var c = a.aoColumns[b],
        d = u.ext.order[c.sSortDataType],
        e;
    d && (e = d.call(a.oInstance, a, b, ua(a, b)));

    for (var h, f = u.ext.type.order[c.sType + "-pre"], g = 0, k = a.aoData.length; g < k; g++) {
      if (c = a.aoData[g], c._aSortData || (c._aSortData = []), !c._aSortData[b] || d) h = d ? e[g] : T(a, g, b, "sort"), c._aSortData[b] = f ? f(h) : h;
    }
  }

  function Ca(a) {
    if (!a._bLoadingState) {
      var b = {
        time: +new Date(),
        start: a._iDisplayStart,
        length: a._iDisplayLength,
        order: l.extend(!0, [], a.aaSorting),
        search: Vb(a.oPreviousSearch),
        columns: l.map(a.aoColumns, function (c, d) {
          return {
            visible: c.bVisible,
            search: Vb(a.aoPreSearchCols[d])
          };
        })
      };
      a.oSavedState = b;
      F(a, "aoStateSaveParams", "stateSaveParams", [a, b]);
      a.oFeatures.bStateSave && !a.bDestroying && a.fnStateSaveCallback.call(a.oInstance, a, b);
    }
  }

  function dc(a, b, c) {
    if (a.oFeatures.bStateSave) return b = a.fnStateLoadCallback.call(a.oInstance, a, function (d) {
      pb(a, d, c);
    }), b !== q && pb(a, b, c), !0;
    c();
  }

  function pb(a, b, c) {
    var d,
        e = a.aoColumns;
    a._bLoadingState = !0;
    var h = a._bInitComplete ? new u.Api(a) : null;

    if (b && b.time) {
      var f = F(a, "aoStateLoadParams", "stateLoadParams", [a, b]);
      if (-1 !== l.inArray(!1, f)) a._bLoadingState = !1;else if (f = a.iStateDuration, 0 < f && b.time < +new Date() - 1E3 * f) a._bLoadingState = !1;else if (b.columns && e.length !== b.columns.length) a._bLoadingState = !1;else {
        a.oLoadedState = l.extend(!0, {}, b);
        b.start !== q && (null === h ? (a._iDisplayStart = b.start, a.iInitDisplayStart = b.start) : Ra(a, b.start / b.length));
        b.length !== q && (a._iDisplayLength = b.length);
        b.order !== q && (a.aaSorting = [], l.each(b.order, function (k, m) {
          a.aaSorting.push(m[0] >= e.length ? [0, m[1]] : m);
        }));
        b.search !== q && l.extend(a.oPreviousSearch, Wb(b.search));

        if (b.columns) {
          f = 0;

          for (d = b.columns.length; f < d; f++) {
            var g = b.columns[f];
            g.visible !== q && (h ? h.column(f).visible(g.visible, !1) : e[f].bVisible = g.visible);
            g.search !== q && l.extend(a.aoPreSearchCols[f], Wb(g.search));
          }

          h && h.columns.adjust();
        }

        a._bLoadingState = !1;
        F(a, "aoStateLoaded", "stateLoaded", [a, b]);
      }
    } else a._bLoadingState = !1;

    c();
  }

  function Ta(a) {
    var b = u.settings;
    a = l.inArray(a, U(b, "nTable"));
    return -1 !== a ? b[a] : null;
  }

  function da(a, b, c, d) {
    c = "DataTables warning: " + (a ? "table id=" + a.sTableId + " - " : "") + c;
    d && (c += ". For more information about this error, please see http://datatables.net/tn/" + d);
    if (b) z.console && console.log && console.log(c);else if (b = u.ext, b = b.sErrMode || b.errMode, a && F(a, null, "error", [a, d, c]), "alert" == b) alert(c);else {
      if ("throw" == b) throw Error(c);
      "function" == typeof b && b(a, d, c);
    }
  }

  function X(a, b, c, d) {
    Array.isArray(c) ? l.each(c, function (e, h) {
      Array.isArray(h) ? X(a, b, h[0], h[1]) : X(a, b, h);
    }) : (d === q && (d = c), b[c] !== q && (a[d] = b[c]));
  }

  function qb(a, b, c) {
    var d;

    for (d in b) {
      if (b.hasOwnProperty(d)) {
        var e = b[d];
        l.isPlainObject(e) ? (l.isPlainObject(a[d]) || (a[d] = {}), l.extend(!0, a[d], e)) : c && "data" !== d && "aaData" !== d && Array.isArray(e) ? a[d] = e.slice() : a[d] = e;
      }
    }

    return a;
  }

  function ob(a, b, c) {
    l(a).on("click.DT", b, function (d) {
      l(a).trigger("blur");
      c(d);
    }).on("keypress.DT", b, function (d) {
      13 === d.which && (d.preventDefault(), c(d));
    }).on("selectstart.DT", function () {
      return !1;
    });
  }

  function R(a, b, c, d) {
    c && a[b].push({
      fn: c,
      sName: d
    });
  }

  function F(a, b, c, d) {
    var e = [];
    b && (e = l.map(a[b].slice().reverse(), function (h, f) {
      return h.fn.apply(a.oInstance, d);
    }));
    null !== c && (b = l.Event(c + ".dt"), l(a.nTable).trigger(b, d), e.push(b.result));
    return e;
  }

  function lb(a) {
    var b = a._iDisplayStart,
        c = a.fnDisplayEnd(),
        d = a._iDisplayLength;
    b >= c && (b = c - d);
    b -= b % d;
    if (-1 === d || 0 > b) b = 0;
    a._iDisplayStart = b;
  }

  function gb(a, b) {
    a = a.renderer;
    var c = u.ext.renderer[b];
    return l.isPlainObject(a) && a[b] ? c[a[b]] || c._ : "string" === typeof a ? c[a] || c._ : c._;
  }

  function Q(a) {
    return a.oFeatures.bServerSide ? "ssp" : a.ajax || a.sAjaxSource ? "ajax" : "dom";
  }

  function Da(a, b) {
    var c = ec.numbers_length,
        d = Math.floor(c / 2);
    b <= c ? a = qa(0, b) : a <= d ? (a = qa(0, c - 2), a.push("ellipsis"), a.push(b - 1)) : (a >= b - 1 - d ? a = qa(b - (c - 2), b) : (a = qa(a - d + 2, a + d - 1), a.push("ellipsis"), a.push(b - 1)), a.splice(0, 0, "ellipsis"), a.splice(0, 0, 0));
    a.DT_el = "span";
    return a;
  }

  function Xa(a) {
    l.each({
      num: function num(b) {
        return Ua(b, a);
      },
      "num-fmt": function numFmt(b) {
        return Ua(b, a, rb);
      },
      "html-num": function htmlNum(b) {
        return Ua(b, a, Va);
      },
      "html-num-fmt": function htmlNumFmt(b) {
        return Ua(b, a, Va, rb);
      }
    }, function (b, c) {
      M.type.order[b + a + "-pre"] = c;
      b.match(/^html\-/) && (M.type.search[b + a] = M.type.search.html);
    });
  }

  function fc(a) {
    return function () {
      var b = [Ta(this[u.ext.iApiIndex])].concat(Array.prototype.slice.call(arguments));
      return u.ext.internal[a].apply(this, b);
    };
  }

  var u = function u(a, b) {
    if (this instanceof u) return l(a).DataTable(b);
    b = a;

    this.$ = function (f, g) {
      return this.api(!0).$(f, g);
    };

    this._ = function (f, g) {
      return this.api(!0).rows(f, g).data();
    };

    this.api = function (f) {
      return f ? new B(Ta(this[M.iApiIndex])) : new B(this);
    };

    this.fnAddData = function (f, g) {
      var k = this.api(!0);
      f = Array.isArray(f) && (Array.isArray(f[0]) || l.isPlainObject(f[0])) ? k.rows.add(f) : k.row.add(f);
      (g === q || g) && k.draw();
      return f.flatten().toArray();
    };

    this.fnAdjustColumnSizing = function (f) {
      var g = this.api(!0).columns.adjust(),
          k = g.settings()[0],
          m = k.oScroll;
      f === q || f ? g.draw(!1) : ("" !== m.sX || "" !== m.sY) && Ha(k);
    };

    this.fnClearTable = function (f) {
      var g = this.api(!0).clear();
      (f === q || f) && g.draw();
    };

    this.fnClose = function (f) {
      this.api(!0).row(f).child.hide();
    };

    this.fnDeleteRow = function (f, g, k) {
      var m = this.api(!0);
      f = m.rows(f);
      var n = f.settings()[0],
          p = n.aoData[f[0][0]];
      f.remove();
      g && g.call(this, n, p);
      (k === q || k) && m.draw();
      return p;
    };

    this.fnDestroy = function (f) {
      this.api(!0).destroy(f);
    };

    this.fnDraw = function (f) {
      this.api(!0).draw(f);
    };

    this.fnFilter = function (f, g, k, m, n, p) {
      n = this.api(!0);
      null === g || g === q ? n.search(f, k, m, p) : n.column(g).search(f, k, m, p);
      n.draw();
    };

    this.fnGetData = function (f, g) {
      var k = this.api(!0);

      if (f !== q) {
        var m = f.nodeName ? f.nodeName.toLowerCase() : "";
        return g !== q || "td" == m || "th" == m ? k.cell(f, g).data() : k.row(f).data() || null;
      }

      return k.data().toArray();
    };

    this.fnGetNodes = function (f) {
      var g = this.api(!0);
      return f !== q ? g.row(f).node() : g.rows().nodes().flatten().toArray();
    };

    this.fnGetPosition = function (f) {
      var g = this.api(!0),
          k = f.nodeName.toUpperCase();
      return "TR" == k ? g.row(f).index() : "TD" == k || "TH" == k ? (f = g.cell(f).index(), [f.row, f.columnVisible, f.column]) : null;
    };

    this.fnIsOpen = function (f) {
      return this.api(!0).row(f).child.isShown();
    };

    this.fnOpen = function (f, g, k) {
      return this.api(!0).row(f).child(g, k).show().child()[0];
    };

    this.fnPageChange = function (f, g) {
      f = this.api(!0).page(f);
      (g === q || g) && f.draw(!1);
    };

    this.fnSetColumnVis = function (f, g, k) {
      f = this.api(!0).column(f).visible(g);
      (k === q || k) && f.columns.adjust().draw();
    };

    this.fnSettings = function () {
      return Ta(this[M.iApiIndex]);
    };

    this.fnSort = function (f) {
      this.api(!0).order(f).draw();
    };

    this.fnSortListener = function (f, g, k) {
      this.api(!0).order.listener(f, g, k);
    };

    this.fnUpdate = function (f, g, k, m, n) {
      var p = this.api(!0);
      k === q || null === k ? p.row(g).data(f) : p.cell(g, k).data(f);
      (n === q || n) && p.columns.adjust();
      (m === q || m) && p.draw();
      return 0;
    };

    this.fnVersionCheck = M.fnVersionCheck;
    var c = this,
        d = b === q,
        e = this.length;
    d && (b = {});
    this.oApi = this.internal = M.internal;

    for (var h in u.ext.internal) {
      h && (this[h] = fc(h));
    }

    this.each(function () {
      var f = {},
          g = 1 < e ? qb(f, b, !0) : b,
          k = 0,
          m;
      f = this.getAttribute("id");
      var n = !1,
          p = u.defaults,
          t = l(this);
      if ("table" != this.nodeName.toLowerCase()) da(null, 0, "Non-table node initialisation (" + this.nodeName + ")", 2);else {
        zb(p);
        Ab(p.column);
        P(p, p, !0);
        P(p.column, p.column, !0);
        P(p, l.extend(g, t.data()), !0);
        var v = u.settings;
        k = 0;

        for (m = v.length; k < m; k++) {
          var x = v[k];

          if (x.nTable == this || x.nTHead && x.nTHead.parentNode == this || x.nTFoot && x.nTFoot.parentNode == this) {
            var w = g.bRetrieve !== q ? g.bRetrieve : p.bRetrieve;
            if (d || w) return x.oInstance;

            if (g.bDestroy !== q ? g.bDestroy : p.bDestroy) {
              x.oInstance.fnDestroy();
              break;
            } else {
              da(x, 0, "Cannot reinitialise DataTable", 3);
              return;
            }
          }

          if (x.sTableId == this.id) {
            v.splice(k, 1);
            break;
          }
        }

        if (null === f || "" === f) this.id = f = "DataTables_Table_" + u.ext._unique++;
        var r = l.extend(!0, {}, u.models.oSettings, {
          sDestroyWidth: t[0].style.width,
          sInstance: f,
          sTableId: f
        });
        r.nTable = this;
        r.oApi = c.internal;
        r.oInit = g;
        v.push(r);
        r.oInstance = 1 === c.length ? c : t.dataTable();
        zb(g);
        ma(g.oLanguage);
        g.aLengthMenu && !g.iDisplayLength && (g.iDisplayLength = Array.isArray(g.aLengthMenu[0]) ? g.aLengthMenu[0][0] : g.aLengthMenu[0]);
        g = qb(l.extend(!0, {}, p), g);
        X(r.oFeatures, g, "bPaginate bLengthChange bFilter bSort bSortMulti bInfo bProcessing bAutoWidth bSortClasses bServerSide bDeferRender".split(" "));
        X(r, g, ["asStripeClasses", "ajax", "fnServerData", "fnFormatNumber", "sServerMethod", "aaSorting", "aaSortingFixed", "aLengthMenu", "sPaginationType", "sAjaxSource", "sAjaxDataProp", "iStateDuration", "sDom", "bSortCellsTop", "iTabIndex", "fnStateLoadCallback", "fnStateSaveCallback", "renderer", "searchDelay", "rowId", ["iCookieDuration", "iStateDuration"], ["oSearch", "oPreviousSearch"], ["aoSearchCols", "aoPreSearchCols"], ["iDisplayLength", "_iDisplayLength"]]);
        X(r.oScroll, g, [["sScrollX", "sX"], ["sScrollXInner", "sXInner"], ["sScrollY", "sY"], ["bScrollCollapse", "bCollapse"]]);
        X(r.oLanguage, g, "fnInfoCallback");
        R(r, "aoDrawCallback", g.fnDrawCallback, "user");
        R(r, "aoServerParams", g.fnServerParams, "user");
        R(r, "aoStateSaveParams", g.fnStateSaveParams, "user");
        R(r, "aoStateLoadParams", g.fnStateLoadParams, "user");
        R(r, "aoStateLoaded", g.fnStateLoaded, "user");
        R(r, "aoRowCallback", g.fnRowCallback, "user");
        R(r, "aoRowCreatedCallback", g.fnCreatedRow, "user");
        R(r, "aoHeaderCallback", g.fnHeaderCallback, "user");
        R(r, "aoFooterCallback", g.fnFooterCallback, "user");
        R(r, "aoInitComplete", g.fnInitComplete, "user");
        R(r, "aoPreDrawCallback", g.fnPreDrawCallback, "user");
        r.rowIdFn = na(g.rowId);
        Bb(r);
        var C = r.oClasses;
        l.extend(C, u.ext.classes, g.oClasses);
        t.addClass(C.sTable);
        r.iInitDisplayStart === q && (r.iInitDisplayStart = g.iDisplayStart, r._iDisplayStart = g.iDisplayStart);
        null !== g.iDeferLoading && (r.bDeferLoading = !0, f = Array.isArray(g.iDeferLoading), r._iRecordsDisplay = f ? g.iDeferLoading[0] : g.iDeferLoading, r._iRecordsTotal = f ? g.iDeferLoading[1] : g.iDeferLoading);
        var G = r.oLanguage;
        l.extend(!0, G, g.oLanguage);
        G.sUrl ? (l.ajax({
          dataType: "json",
          url: G.sUrl,
          success: function success(I) {
            P(p.oLanguage, I);
            ma(I);
            l.extend(!0, G, I);
            F(r, null, "i18n", [r]);
            Aa(r);
          },
          error: function error() {
            Aa(r);
          }
        }), n = !0) : F(r, null, "i18n", [r]);
        null === g.asStripeClasses && (r.asStripeClasses = [C.sStripeOdd, C.sStripeEven]);
        f = r.asStripeClasses;
        var aa = t.children("tbody").find("tr").eq(0);
        -1 !== l.inArray(!0, l.map(f, function (I, H) {
          return aa.hasClass(I);
        })) && (l("tbody tr", this).removeClass(f.join(" ")), r.asDestroyStripes = f.slice());
        f = [];
        v = this.getElementsByTagName("thead");
        0 !== v.length && (wa(r.aoHeader, v[0]), f = Na(r));
        if (null === g.aoColumns) for (v = [], k = 0, m = f.length; k < m; k++) {
          v.push(null);
        } else v = g.aoColumns;
        k = 0;

        for (m = v.length; k < m; k++) {
          Ya(r, f ? f[k] : null);
        }

        Db(r, g.aoColumnDefs, v, function (I, H) {
          Ga(r, I, H);
        });

        if (aa.length) {
          var L = function L(I, H) {
            return null !== I.getAttribute("data-" + H) ? H : null;
          };

          l(aa[0]).children("th, td").each(function (I, H) {
            var ea = r.aoColumns[I];

            if (ea.mData === I) {
              var Y = L(H, "sort") || L(H, "order");
              H = L(H, "filter") || L(H, "search");
              if (null !== Y || null !== H) ea.mData = {
                _: I + ".display",
                sort: null !== Y ? I + ".@data-" + Y : q,
                type: null !== Y ? I + ".@data-" + Y : q,
                filter: null !== H ? I + ".@data-" + H : q
              }, Ga(r, I);
            }
          });
        }

        var O = r.oFeatures;

        f = function f() {
          if (g.aaSorting === q) {
            var I = r.aaSorting;
            k = 0;

            for (m = I.length; k < m; k++) {
              I[k][1] = r.aoColumns[k].asSorting[0];
            }
          }

          Sa(r);
          O.bSort && R(r, "aoDrawCallback", function () {
            if (r.bSorted) {
              var Y = pa(r),
                  Ba = {};
              l.each(Y, function (fa, ba) {
                Ba[ba.src] = ba.dir;
              });
              F(r, null, "order", [r, Y, Ba]);
              cc(r);
            }
          });
          R(r, "aoDrawCallback", function () {
            (r.bSorted || "ssp" === Q(r) || O.bDeferRender) && Sa(r);
          }, "sc");
          I = t.children("caption").each(function () {
            this._captionSide = l(this).css("caption-side");
          });
          var H = t.children("thead");
          0 === H.length && (H = l("<thead/>").appendTo(t));
          r.nTHead = H[0];
          var ea = t.children("tbody");
          0 === ea.length && (ea = l("<tbody/>").insertAfter(H));
          r.nTBody = ea[0];
          H = t.children("tfoot");
          0 === H.length && 0 < I.length && ("" !== r.oScroll.sX || "" !== r.oScroll.sY) && (H = l("<tfoot/>").appendTo(t));
          0 === H.length || 0 === H.children().length ? t.addClass(C.sNoFooter) : 0 < H.length && (r.nTFoot = H[0], wa(r.aoFooter, r.nTFoot));
          if (g.aaData) for (k = 0; k < g.aaData.length; k++) {
            ia(r, g.aaData[k]);
          } else (r.bDeferLoading || "dom" == Q(r)) && Ja(r, l(r.nTBody).children("tr"));
          r.aiDisplay = r.aiDisplayMaster.slice();
          r.bInitialised = !0;
          !1 === n && Aa(r);
        };

        R(r, "aoDrawCallback", Ca, "state_save");
        g.bStateSave ? (O.bStateSave = !0, dc(r, g, f)) : f();
      }
    });
    c = null;
    return this;
  },
      M,
      y,
      J,
      sb = {},
      gc = /[\r\n\u2028]/g,
      Va = /<.*?>/g,
      vc = /^\d{2,4}[\.\/\-]\d{1,2}[\.\/\-]\d{1,2}([T ]{1}\d{1,2}[:\.]\d{2}([\.:]\d{2})?)?$/,
      wc = /(\/|\.|\*|\+|\?|\||\(|\)|\[|\]|\{|\}|\\|\$|\^|\-)/g,
      rb = /['\u00A0,$£€¥%\u2009\u202F\u20BD\u20a9\u20BArfkɃΞ]/gi,
      Z = function Z(a) {
    return a && !0 !== a && "-" !== a ? !1 : !0;
  },
      hc = function hc(a) {
    var b = parseInt(a, 10);
    return !isNaN(b) && isFinite(a) ? b : null;
  },
      ic = function ic(a, b) {
    sb[b] || (sb[b] = new RegExp(jb(b), "g"));
    return "string" === typeof a && "." !== b ? a.replace(/\./g, "").replace(sb[b], ".") : a;
  },
      tb = function tb(a, b, c) {
    var d = "string" === typeof a;
    if (Z(a)) return !0;
    b && d && (a = ic(a, b));
    c && d && (a = a.replace(rb, ""));
    return !isNaN(parseFloat(a)) && isFinite(a);
  },
      jc = function jc(a, b, c) {
    return Z(a) ? !0 : Z(a) || "string" === typeof a ? tb(a.replace(Va, ""), b, c) ? !0 : null : null;
  },
      U = function U(a, b, c) {
    var d = [],
        e = 0,
        h = a.length;
    if (c !== q) for (; e < h; e++) {
      a[e] && a[e][b] && d.push(a[e][b][c]);
    } else for (; e < h; e++) {
      a[e] && d.push(a[e][b]);
    }
    return d;
  },
      Ea = function Ea(a, b, c, d) {
    var e = [],
        h = 0,
        f = b.length;
    if (d !== q) for (; h < f; h++) {
      a[b[h]][c] && e.push(a[b[h]][c][d]);
    } else for (; h < f; h++) {
      e.push(a[b[h]][c]);
    }
    return e;
  },
      qa = function qa(a, b) {
    var c = [];

    if (b === q) {
      b = 0;
      var d = a;
    } else d = b, b = a;

    for (a = b; a < d; a++) {
      c.push(a);
    }

    return c;
  },
      kc = function kc(a) {
    for (var b = [], c = 0, d = a.length; c < d; c++) {
      a[c] && b.push(a[c]);
    }

    return b;
  },
      Ma = function Ma(a) {
    a: {
      if (!(2 > a.length)) {
        var b = a.slice().sort();

        for (var c = b[0], d = 1, e = b.length; d < e; d++) {
          if (b[d] === c) {
            b = !1;
            break a;
          }

          c = b[d];
        }
      }

      b = !0;
    }

    if (b) return a.slice();
    b = [];
    e = a.length;
    var h,
        f = 0;
    d = 0;

    a: for (; d < e; d++) {
      c = a[d];

      for (h = 0; h < f; h++) {
        if (b[h] === c) continue a;
      }

      b.push(c);
      f++;
    }

    return b;
  },
      lc = function lc(a, b) {
    if (Array.isArray(b)) for (var c = 0; c < b.length; c++) {
      lc(a, b[c]);
    } else a.push(b);
    return a;
  },
      mc = function mc(a, b) {
    b === q && (b = 0);
    return -1 !== this.indexOf(a, b);
  };

  Array.isArray || (Array.isArray = function (a) {
    return "[object Array]" === Object.prototype.toString.call(a);
  });
  Array.prototype.includes || (Array.prototype.includes = mc);
  String.prototype.trim || (String.prototype.trim = function () {
    return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, "");
  });
  String.prototype.includes || (String.prototype.includes = mc);
  u.util = {
    throttle: function throttle(a, b) {
      var c = b !== q ? b : 200,
          d,
          e;
      return function () {
        var h = this,
            f = +new Date(),
            g = arguments;
        d && f < d + c ? (clearTimeout(e), e = setTimeout(function () {
          d = q;
          a.apply(h, g);
        }, c)) : (d = f, a.apply(h, g));
      };
    },
    escapeRegex: function escapeRegex(a) {
      return a.replace(wc, "\\$1");
    },
    set: function set(a) {
      if (l.isPlainObject(a)) return u.util.set(a._);
      if (null === a) return function () {};
      if ("function" === typeof a) return function (c, d, e) {
        a(c, "set", d, e);
      };
      if ("string" !== typeof a || -1 === a.indexOf(".") && -1 === a.indexOf("[") && -1 === a.indexOf("(")) return function (c, d) {
        c[a] = d;
      };

      var b = function b(c, d, e) {
        e = cb(e);
        var h = e[e.length - 1];

        for (var f, g, k = 0, m = e.length - 1; k < m; k++) {
          if ("__proto__" === e[k] || "constructor" === e[k]) throw Error("Cannot set prototype values");
          f = e[k].match(Fa);
          g = e[k].match(ra);

          if (f) {
            e[k] = e[k].replace(Fa, "");
            c[e[k]] = [];
            h = e.slice();
            h.splice(0, k + 1);
            f = h.join(".");
            if (Array.isArray(d)) for (g = 0, m = d.length; g < m; g++) {
              h = {}, b(h, d[g], f), c[e[k]].push(h);
            } else c[e[k]] = d;
            return;
          }

          g && (e[k] = e[k].replace(ra, ""), c = c[e[k]](d));
          if (null === c[e[k]] || c[e[k]] === q) c[e[k]] = {};
          c = c[e[k]];
        }

        if (h.match(ra)) c[h.replace(ra, "")](d);else c[h.replace(Fa, "")] = d;
      };

      return function (c, d) {
        return b(c, d, a);
      };
    },
    get: function get(a) {
      if (l.isPlainObject(a)) {
        var b = {};
        l.each(a, function (d, e) {
          e && (b[d] = u.util.get(e));
        });
        return function (d, e, h, f) {
          var g = b[e] || b._;
          return g !== q ? g(d, e, h, f) : d;
        };
      }

      if (null === a) return function (d) {
        return d;
      };
      if ("function" === typeof a) return function (d, e, h, f) {
        return a(d, e, h, f);
      };
      if ("string" !== typeof a || -1 === a.indexOf(".") && -1 === a.indexOf("[") && -1 === a.indexOf("(")) return function (d, e) {
        return d[a];
      };

      var c = function c(d, e, h) {
        if ("" !== h) {
          var f = cb(h);

          for (var g = 0, k = f.length; g < k; g++) {
            h = f[g].match(Fa);
            var m = f[g].match(ra);

            if (h) {
              f[g] = f[g].replace(Fa, "");
              "" !== f[g] && (d = d[f[g]]);
              m = [];
              f.splice(0, g + 1);
              f = f.join(".");
              if (Array.isArray(d)) for (g = 0, k = d.length; g < k; g++) {
                m.push(c(d[g], e, f));
              }
              d = h[0].substring(1, h[0].length - 1);
              d = "" === d ? m : m.join(d);
              break;
            } else if (m) {
              f[g] = f[g].replace(ra, "");
              d = d[f[g]]();
              continue;
            }

            if (null === d || d[f[g]] === q) return q;
            d = d[f[g]];
          }
        }

        return d;
      };

      return function (d, e) {
        return c(d, e, a);
      };
    }
  };

  var S = function S(a, b, c) {
    a[b] !== q && (a[c] = a[b]);
  },
      Fa = /\[.*?\]$/,
      ra = /\(\)$/,
      na = u.util.get,
      ha = u.util.set,
      jb = u.util.escapeRegex,
      Qa = l("<div>")[0],
      tc = Qa.textContent !== q,
      uc = /<.*?>/g,
      hb = u.util.throttle,
      nc = [],
      N = Array.prototype,
      xc = function xc(a) {
    var b,
        c = u.settings,
        d = l.map(c, function (h, f) {
      return h.nTable;
    });

    if (a) {
      if (a.nTable && a.oApi) return [a];

      if (a.nodeName && "table" === a.nodeName.toLowerCase()) {
        var e = l.inArray(a, d);
        return -1 !== e ? [c[e]] : null;
      }

      if (a && "function" === typeof a.settings) return a.settings().toArray();
      "string" === typeof a ? b = l(a) : a instanceof l && (b = a);
    } else return [];

    if (b) return b.map(function (h) {
      e = l.inArray(this, d);
      return -1 !== e ? c[e] : null;
    }).toArray();
  };

  var B = function B(a, b) {
    if (!(this instanceof B)) return new B(a, b);

    var c = [],
        d = function d(f) {
      (f = xc(f)) && c.push.apply(c, f);
    };

    if (Array.isArray(a)) for (var e = 0, h = a.length; e < h; e++) {
      d(a[e]);
    } else d(a);
    this.context = Ma(c);
    b && l.merge(this, b);
    this.selector = {
      rows: null,
      cols: null,
      opts: null
    };
    B.extend(this, this, nc);
  };

  u.Api = B;
  l.extend(B.prototype, {
    any: function any() {
      return 0 !== this.count();
    },
    concat: N.concat,
    context: [],
    count: function count() {
      return this.flatten().length;
    },
    each: function each(a) {
      for (var b = 0, c = this.length; b < c; b++) {
        a.call(this, this[b], b, this);
      }

      return this;
    },
    eq: function eq(a) {
      var b = this.context;
      return b.length > a ? new B(b[a], this[a]) : null;
    },
    filter: function filter(a) {
      var b = [];
      if (N.filter) b = N.filter.call(this, a, this);else for (var c = 0, d = this.length; c < d; c++) {
        a.call(this, this[c], c, this) && b.push(this[c]);
      }
      return new B(this.context, b);
    },
    flatten: function flatten() {
      var a = [];
      return new B(this.context, a.concat.apply(a, this.toArray()));
    },
    join: N.join,
    indexOf: N.indexOf || function (a, b) {
      b = b || 0;

      for (var c = this.length; b < c; b++) {
        if (this[b] === a) return b;
      }

      return -1;
    },
    iterator: function iterator(a, b, c, d) {
      var e = [],
          h,
          f,
          g = this.context,
          k,
          m = this.selector;
      "string" === typeof a && (d = c, c = b, b = a, a = !1);
      var n = 0;

      for (h = g.length; n < h; n++) {
        var p = new B(g[n]);

        if ("table" === b) {
          var t = c.call(p, g[n], n);
          t !== q && e.push(t);
        } else if ("columns" === b || "rows" === b) t = c.call(p, g[n], this[n], n), t !== q && e.push(t);else if ("column" === b || "column-rows" === b || "row" === b || "cell" === b) {
          var v = this[n];
          "column-rows" === b && (k = Wa(g[n], m.opts));
          var x = 0;

          for (f = v.length; x < f; x++) {
            t = v[x], t = "cell" === b ? c.call(p, g[n], t.row, t.column, n, x) : c.call(p, g[n], t, n, x, k), t !== q && e.push(t);
          }
        }
      }

      return e.length || d ? (a = new B(g, a ? e.concat.apply([], e) : e), b = a.selector, b.rows = m.rows, b.cols = m.cols, b.opts = m.opts, a) : this;
    },
    lastIndexOf: N.lastIndexOf || function (a, b) {
      return this.indexOf.apply(this.toArray.reverse(), arguments);
    },
    length: 0,
    map: function map(a) {
      var b = [];
      if (N.map) b = N.map.call(this, a, this);else for (var c = 0, d = this.length; c < d; c++) {
        b.push(a.call(this, this[c], c));
      }
      return new B(this.context, b);
    },
    pluck: function pluck(a) {
      return this.map(function (b) {
        return b[a];
      });
    },
    pop: N.pop,
    push: N.push,
    reduce: N.reduce || function (a, b) {
      return Cb(this, a, b, 0, this.length, 1);
    },
    reduceRight: N.reduceRight || function (a, b) {
      return Cb(this, a, b, this.length - 1, -1, -1);
    },
    reverse: N.reverse,
    selector: null,
    shift: N.shift,
    slice: function slice() {
      return new B(this.context, this);
    },
    sort: N.sort,
    splice: N.splice,
    toArray: function toArray() {
      return N.slice.call(this);
    },
    to$: function to$() {
      return l(this);
    },
    toJQuery: function toJQuery() {
      return l(this);
    },
    unique: function unique() {
      return new B(this.context, Ma(this));
    },
    unshift: N.unshift
  });

  B.extend = function (a, b, c) {
    if (c.length && b && (b instanceof B || b.__dt_wrapper)) {
      var d,
          e = function e(g, k, m) {
        return function () {
          var n = k.apply(g, arguments);
          B.extend(n, n, m.methodExt);
          return n;
        };
      };

      var h = 0;

      for (d = c.length; h < d; h++) {
        var f = c[h];
        b[f.name] = "function" === f.type ? e(a, f.val, f) : "object" === f.type ? {} : f.val;
        b[f.name].__dt_wrapper = !0;
        B.extend(a, b[f.name], f.propExt);
      }
    }
  };

  B.register = y = function y(a, b) {
    if (Array.isArray(a)) for (var c = 0, d = a.length; c < d; c++) {
      B.register(a[c], b);
    } else {
      d = a.split(".");
      var e = nc,
          h;
      a = 0;

      for (c = d.length; a < c; a++) {
        var f = (h = -1 !== d[a].indexOf("()")) ? d[a].replace("()", "") : d[a];

        a: {
          var g = 0;

          for (var k = e.length; g < k; g++) {
            if (e[g].name === f) {
              g = e[g];
              break a;
            }
          }

          g = null;
        }

        g || (g = {
          name: f,
          val: {},
          methodExt: [],
          propExt: [],
          type: "object"
        }, e.push(g));
        a === c - 1 ? (g.val = b, g.type = "function" === typeof b ? "function" : l.isPlainObject(b) ? "object" : "other") : e = h ? g.methodExt : g.propExt;
      }
    }
  };

  B.registerPlural = J = function J(a, b, c) {
    B.register(a, c);
    B.register(b, function () {
      var d = c.apply(this, arguments);
      return d === this ? this : d instanceof B ? d.length ? Array.isArray(d[0]) ? new B(d.context, d[0]) : d[0] : q : d;
    });
  };

  var oc = function oc(a, b) {
    if (Array.isArray(a)) return l.map(a, function (d) {
      return oc(d, b);
    });
    if ("number" === typeof a) return [b[a]];
    var c = l.map(b, function (d, e) {
      return d.nTable;
    });
    return l(c).filter(a).map(function (d) {
      d = l.inArray(this, c);
      return b[d];
    }).toArray();
  };

  y("tables()", function (a) {
    return a !== q && null !== a ? new B(oc(a, this.context)) : this;
  });
  y("table()", function (a) {
    a = this.tables(a);
    var b = a.context;
    return b.length ? new B(b[0]) : a;
  });
  J("tables().nodes()", "table().node()", function () {
    return this.iterator("table", function (a) {
      return a.nTable;
    }, 1);
  });
  J("tables().body()", "table().body()", function () {
    return this.iterator("table", function (a) {
      return a.nTBody;
    }, 1);
  });
  J("tables().header()", "table().header()", function () {
    return this.iterator("table", function (a) {
      return a.nTHead;
    }, 1);
  });
  J("tables().footer()", "table().footer()", function () {
    return this.iterator("table", function (a) {
      return a.nTFoot;
    }, 1);
  });
  J("tables().containers()", "table().container()", function () {
    return this.iterator("table", function (a) {
      return a.nTableWrapper;
    }, 1);
  });
  y("draw()", function (a) {
    return this.iterator("table", function (b) {
      "page" === a ? ja(b) : ("string" === typeof a && (a = "full-hold" === a ? !1 : !0), ka(b, !1 === a));
    });
  });
  y("page()", function (a) {
    return a === q ? this.page.info().page : this.iterator("table", function (b) {
      Ra(b, a);
    });
  });
  y("page.info()", function (a) {
    if (0 === this.context.length) return q;
    a = this.context[0];
    var b = a._iDisplayStart,
        c = a.oFeatures.bPaginate ? a._iDisplayLength : -1,
        d = a.fnRecordsDisplay(),
        e = -1 === c;
    return {
      page: e ? 0 : Math.floor(b / c),
      pages: e ? 1 : Math.ceil(d / c),
      start: b,
      end: a.fnDisplayEnd(),
      length: c,
      recordsTotal: a.fnRecordsTotal(),
      recordsDisplay: d,
      serverSide: "ssp" === Q(a)
    };
  });
  y("page.len()", function (a) {
    return a === q ? 0 !== this.context.length ? this.context[0]._iDisplayLength : q : this.iterator("table", function (b) {
      kb(b, a);
    });
  });

  var pc = function pc(a, b, c) {
    if (c) {
      var d = new B(a);
      d.one("draw", function () {
        c(d.ajax.json());
      });
    }

    if ("ssp" == Q(a)) ka(a, b);else {
      V(a, !0);
      var e = a.jqXHR;
      e && 4 !== e.readyState && e.abort();
      Oa(a, [], function (h) {
        Ka(a);
        h = za(a, h);

        for (var f = 0, g = h.length; f < g; f++) {
          ia(a, h[f]);
        }

        ka(a, b);
        V(a, !1);
      });
    }
  };

  y("ajax.json()", function () {
    var a = this.context;
    if (0 < a.length) return a[0].json;
  });
  y("ajax.params()", function () {
    var a = this.context;
    if (0 < a.length) return a[0].oAjaxData;
  });
  y("ajax.reload()", function (a, b) {
    return this.iterator("table", function (c) {
      pc(c, !1 === b, a);
    });
  });
  y("ajax.url()", function (a) {
    var b = this.context;

    if (a === q) {
      if (0 === b.length) return q;
      b = b[0];
      return b.ajax ? l.isPlainObject(b.ajax) ? b.ajax.url : b.ajax : b.sAjaxSource;
    }

    return this.iterator("table", function (c) {
      l.isPlainObject(c.ajax) ? c.ajax.url = a : c.ajax = a;
    });
  });
  y("ajax.url().load()", function (a, b) {
    return this.iterator("table", function (c) {
      pc(c, !1 === b, a);
    });
  });

  var ub = function ub(a, b, c, d, e) {
    var h = [],
        f,
        g,
        k;

    var m = _typeof(b);

    b && "string" !== m && "function" !== m && b.length !== q || (b = [b]);
    m = 0;

    for (g = b.length; m < g; m++) {
      var n = b[m] && b[m].split && !b[m].match(/[\[\(:]/) ? b[m].split(",") : [b[m]];
      var p = 0;

      for (k = n.length; p < k; p++) {
        (f = c("string" === typeof n[p] ? n[p].trim() : n[p])) && f.length && (h = h.concat(f));
      }
    }

    a = M.selector[a];
    if (a.length) for (m = 0, g = a.length; m < g; m++) {
      h = a[m](d, e, h);
    }
    return Ma(h);
  },
      vb = function vb(a) {
    a || (a = {});
    a.filter && a.search === q && (a.search = a.filter);
    return l.extend({
      search: "none",
      order: "current",
      page: "all"
    }, a);
  },
      wb = function wb(a) {
    for (var b = 0, c = a.length; b < c; b++) {
      if (0 < a[b].length) return a[0] = a[b], a[0].length = 1, a.length = 1, a.context = [a.context[b]], a;
    }

    a.length = 0;
    return a;
  },
      Wa = function Wa(a, b) {
    var c = [],
        d = a.aiDisplay;
    var e = a.aiDisplayMaster;
    var h = b.search;
    var f = b.order;
    b = b.page;
    if ("ssp" == Q(a)) return "removed" === h ? [] : qa(0, e.length);
    if ("current" == b) for (f = a._iDisplayStart, a = a.fnDisplayEnd(); f < a; f++) {
      c.push(d[f]);
    } else if ("current" == f || "applied" == f) {
      if ("none" == h) c = e.slice();else if ("applied" == h) c = d.slice();else {
        if ("removed" == h) {
          var g = {};
          f = 0;

          for (a = d.length; f < a; f++) {
            g[d[f]] = null;
          }

          c = l.map(e, function (k) {
            return g.hasOwnProperty(k) ? null : k;
          });
        }
      }
    } else if ("index" == f || "original" == f) for (f = 0, a = a.aoData.length; f < a; f++) {
      "none" == h ? c.push(f) : (e = l.inArray(f, d), (-1 === e && "removed" == h || 0 <= e && "applied" == h) && c.push(f));
    }
    return c;
  },
      yc = function yc(a, b, c) {
    var d;
    return ub("row", b, function (e) {
      var h = hc(e),
          f = a.aoData;
      if (null !== h && !c) return [h];
      d || (d = Wa(a, c));
      if (null !== h && -1 !== l.inArray(h, d)) return [h];
      if (null === e || e === q || "" === e) return d;
      if ("function" === typeof e) return l.map(d, function (k) {
        var m = f[k];
        return e(k, m._aData, m.nTr) ? k : null;
      });

      if (e.nodeName) {
        h = e._DT_RowIndex;
        var g = e._DT_CellIndex;
        if (h !== q) return f[h] && f[h].nTr === e ? [h] : [];
        if (g) return f[g.row] && f[g.row].nTr === e.parentNode ? [g.row] : [];
        h = l(e).closest("*[data-dt-row]");
        return h.length ? [h.data("dt-row")] : [];
      }

      if ("string" === typeof e && "#" === e.charAt(0) && (h = a.aIds[e.replace(/^#/, "")], h !== q)) return [h.idx];
      h = kc(Ea(a.aoData, d, "nTr"));
      return l(h).filter(e).map(function () {
        return this._DT_RowIndex;
      }).toArray();
    }, a, c);
  };

  y("rows()", function (a, b) {
    a === q ? a = "" : l.isPlainObject(a) && (b = a, a = "");
    b = vb(b);
    var c = this.iterator("table", function (d) {
      return yc(d, a, b);
    }, 1);
    c.selector.rows = a;
    c.selector.opts = b;
    return c;
  });
  y("rows().nodes()", function () {
    return this.iterator("row", function (a, b) {
      return a.aoData[b].nTr || q;
    }, 1);
  });
  y("rows().data()", function () {
    return this.iterator(!0, "rows", function (a, b) {
      return Ea(a.aoData, b, "_aData");
    }, 1);
  });
  J("rows().cache()", "row().cache()", function (a) {
    return this.iterator("row", function (b, c) {
      b = b.aoData[c];
      return "search" === a ? b._aFilterData : b._aSortData;
    }, 1);
  });
  J("rows().invalidate()", "row().invalidate()", function (a) {
    return this.iterator("row", function (b, c) {
      va(b, c, a);
    });
  });
  J("rows().indexes()", "row().index()", function () {
    return this.iterator("row", function (a, b) {
      return b;
    }, 1);
  });
  J("rows().ids()", "row().id()", function (a) {
    for (var b = [], c = this.context, d = 0, e = c.length; d < e; d++) {
      for (var h = 0, f = this[d].length; h < f; h++) {
        var g = c[d].rowIdFn(c[d].aoData[this[d][h]]._aData);
        b.push((!0 === a ? "#" : "") + g);
      }
    }

    return new B(c, b);
  });
  J("rows().remove()", "row().remove()", function () {
    var a = this;
    this.iterator("row", function (b, c, d) {
      var e = b.aoData,
          h = e[c],
          f,
          g;
      e.splice(c, 1);
      var k = 0;

      for (f = e.length; k < f; k++) {
        var m = e[k];
        var n = m.anCells;
        null !== m.nTr && (m.nTr._DT_RowIndex = k);
        if (null !== n) for (m = 0, g = n.length; m < g; m++) {
          n[m]._DT_CellIndex.row = k;
        }
      }

      La(b.aiDisplayMaster, c);
      La(b.aiDisplay, c);
      La(a[d], c, !1);
      0 < b._iRecordsDisplay && b._iRecordsDisplay--;
      lb(b);
      c = b.rowIdFn(h._aData);
      c !== q && delete b.aIds[c];
    });
    this.iterator("table", function (b) {
      for (var c = 0, d = b.aoData.length; c < d; c++) {
        b.aoData[c].idx = c;
      }
    });
    return this;
  });
  y("rows.add()", function (a) {
    var b = this.iterator("table", function (d) {
      var e,
          h = [];
      var f = 0;

      for (e = a.length; f < e; f++) {
        var g = a[f];
        g.nodeName && "TR" === g.nodeName.toUpperCase() ? h.push(Ja(d, g)[0]) : h.push(ia(d, g));
      }

      return h;
    }, 1),
        c = this.rows(-1);
    c.pop();
    l.merge(c, b);
    return c;
  });
  y("row()", function (a, b) {
    return wb(this.rows(a, b));
  });
  y("row().data()", function (a) {
    var b = this.context;
    if (a === q) return b.length && this.length ? b[0].aoData[this[0]]._aData : q;
    var c = b[0].aoData[this[0]];
    c._aData = a;
    Array.isArray(a) && c.nTr && c.nTr.id && ha(b[0].rowId)(a, c.nTr.id);
    va(b[0], this[0], "data");
    return this;
  });
  y("row().node()", function () {
    var a = this.context;
    return a.length && this.length ? a[0].aoData[this[0]].nTr || null : null;
  });
  y("row.add()", function (a) {
    a instanceof l && a.length && (a = a[0]);
    var b = this.iterator("table", function (c) {
      return a.nodeName && "TR" === a.nodeName.toUpperCase() ? Ja(c, a)[0] : ia(c, a);
    });
    return this.row(b[0]);
  });
  l(A).on("plugin-init.dt", function (a, b) {
    a = new B(b);
    a.on("stateSaveParams", function (d, e, h) {
      d = e.rowIdFn;
      e = e.aoData;

      for (var f = [], g = 0; g < e.length; g++) {
        e[g]._detailsShow && f.push("#" + d(e[g]._aData));
      }

      h.childRows = f;
    });
    var c = a.state.loaded();
    c && c.childRows && a.rows(l.map(c.childRows, function (d) {
      return d.replace(/:/g, "\\:");
    })).every(function () {
      F(b, null, "requestChild", [this]);
    });
  });

  var zc = function zc(a, b, c, d) {
    var e = [],
        h = function h(f, g) {
      if (Array.isArray(f) || f instanceof l) for (var k = 0, m = f.length; k < m; k++) {
        h(f[k], g);
      } else f.nodeName && "tr" === f.nodeName.toLowerCase() ? e.push(f) : (k = l("<tr><td></td></tr>").addClass(g), l("td", k).addClass(g).html(f)[0].colSpan = oa(a), e.push(k[0]));
    };

    h(c, d);
    b._details && b._details.detach();
    b._details = l(e);
    b._detailsShow && b._details.insertAfter(b.nTr);
  },
      qc = u.util.throttle(function (a) {
    Ca(a[0]);
  }, 500),
      xb = function xb(a, b) {
    var c = a.context;
    c.length && (a = c[0].aoData[b !== q ? b : a[0]]) && a._details && (a._details.remove(), a._detailsShow = q, a._details = q, l(a.nTr).removeClass("dt-hasChild"), qc(c));
  },
      rc = function rc(a, b) {
    var c = a.context;

    if (c.length && a.length) {
      var d = c[0].aoData[a[0]];
      d._details && ((d._detailsShow = b) ? (d._details.insertAfter(d.nTr), l(d.nTr).addClass("dt-hasChild")) : (d._details.detach(), l(d.nTr).removeClass("dt-hasChild")), F(c[0], null, "childRow", [b, a.row(a[0])]), Ac(c[0]), qc(c));
    }
  },
      Ac = function Ac(a) {
    var b = new B(a),
        c = a.aoData;
    b.off("draw.dt.DT_details column-visibility.dt.DT_details destroy.dt.DT_details");
    0 < U(c, "_details").length && (b.on("draw.dt.DT_details", function (d, e) {
      a === e && b.rows({
        page: "current"
      }).eq(0).each(function (h) {
        h = c[h];
        h._detailsShow && h._details.insertAfter(h.nTr);
      });
    }), b.on("column-visibility.dt.DT_details", function (d, e, h, f) {
      if (a === e) for (e = oa(e), h = 0, f = c.length; h < f; h++) {
        d = c[h], d._details && d._details.children("td[colspan]").attr("colspan", e);
      }
    }), b.on("destroy.dt.DT_details", function (d, e) {
      if (a === e) for (d = 0, e = c.length; d < e; d++) {
        c[d]._details && xb(b, d);
      }
    }));
  };

  y("row().child()", function (a, b) {
    var c = this.context;
    if (a === q) return c.length && this.length ? c[0].aoData[this[0]]._details : q;
    !0 === a ? this.child.show() : !1 === a ? xb(this) : c.length && this.length && zc(c[0], c[0].aoData[this[0]], a, b);
    return this;
  });
  y(["row().child.show()", "row().child().show()"], function (a) {
    rc(this, !0);
    return this;
  });
  y(["row().child.hide()", "row().child().hide()"], function () {
    rc(this, !1);
    return this;
  });
  y(["row().child.remove()", "row().child().remove()"], function () {
    xb(this);
    return this;
  });
  y("row().child.isShown()", function () {
    var a = this.context;
    return a.length && this.length ? a[0].aoData[this[0]]._detailsShow || !1 : !1;
  });

  var Bc = /^([^:]+):(name|visIdx|visible)$/,
      sc = function sc(a, b, c, d, e) {
    c = [];
    d = 0;

    for (var h = e.length; d < h; d++) {
      c.push(T(a, e[d], b));
    }

    return c;
  },
      Cc = function Cc(a, b, c) {
    var d = a.aoColumns,
        e = U(d, "sName"),
        h = U(d, "nTh");
    return ub("column", b, function (f) {
      var g = hc(f);
      if ("" === f) return qa(d.length);
      if (null !== g) return [0 <= g ? g : d.length + g];

      if ("function" === typeof f) {
        var k = Wa(a, c);
        return l.map(d, function (p, t) {
          return f(t, sc(a, t, 0, 0, k), h[t]) ? t : null;
        });
      }

      var m = "string" === typeof f ? f.match(Bc) : "";
      if (m) switch (m[2]) {
        case "visIdx":
        case "visible":
          g = parseInt(m[1], 10);

          if (0 > g) {
            var n = l.map(d, function (p, t) {
              return p.bVisible ? t : null;
            });
            return [n[n.length + g]];
          }

          return [ta(a, g)];

        case "name":
          return l.map(e, function (p, t) {
            return p === m[1] ? t : null;
          });

        default:
          return [];
      }
      if (f.nodeName && f._DT_CellIndex) return [f._DT_CellIndex.column];
      g = l(h).filter(f).map(function () {
        return l.inArray(this, h);
      }).toArray();
      if (g.length || !f.nodeName) return g;
      g = l(f).closest("*[data-dt-column]");
      return g.length ? [g.data("dt-column")] : [];
    }, a, c);
  };

  y("columns()", function (a, b) {
    a === q ? a = "" : l.isPlainObject(a) && (b = a, a = "");
    b = vb(b);
    var c = this.iterator("table", function (d) {
      return Cc(d, a, b);
    }, 1);
    c.selector.cols = a;
    c.selector.opts = b;
    return c;
  });
  J("columns().header()", "column().header()", function (a, b) {
    return this.iterator("column", function (c, d) {
      return c.aoColumns[d].nTh;
    }, 1);
  });
  J("columns().footer()", "column().footer()", function (a, b) {
    return this.iterator("column", function (c, d) {
      return c.aoColumns[d].nTf;
    }, 1);
  });
  J("columns().data()", "column().data()", function () {
    return this.iterator("column-rows", sc, 1);
  });
  J("columns().dataSrc()", "column().dataSrc()", function () {
    return this.iterator("column", function (a, b) {
      return a.aoColumns[b].mData;
    }, 1);
  });
  J("columns().cache()", "column().cache()", function (a) {
    return this.iterator("column-rows", function (b, c, d, e, h) {
      return Ea(b.aoData, h, "search" === a ? "_aFilterData" : "_aSortData", c);
    }, 1);
  });
  J("columns().nodes()", "column().nodes()", function () {
    return this.iterator("column-rows", function (a, b, c, d, e) {
      return Ea(a.aoData, e, "anCells", b);
    }, 1);
  });
  J("columns().visible()", "column().visible()", function (a, b) {
    var c = this,
        d = this.iterator("column", function (e, h) {
      if (a === q) return e.aoColumns[h].bVisible;
      var f = e.aoColumns,
          g = f[h],
          k = e.aoData,
          m;

      if (a !== q && g.bVisible !== a) {
        if (a) {
          var n = l.inArray(!0, U(f, "bVisible"), h + 1);
          f = 0;

          for (m = k.length; f < m; f++) {
            var p = k[f].nTr;
            e = k[f].anCells;
            p && p.insertBefore(e[h], e[n] || null);
          }
        } else l(U(e.aoData, "anCells", h)).detach();

        g.bVisible = a;
      }
    });
    a !== q && this.iterator("table", function (e) {
      xa(e, e.aoHeader);
      xa(e, e.aoFooter);
      e.aiDisplay.length || l(e.nTBody).find("td[colspan]").attr("colspan", oa(e));
      Ca(e);
      c.iterator("column", function (h, f) {
        F(h, null, "column-visibility", [h, f, a, b]);
      });
      (b === q || b) && c.columns.adjust();
    });
    return d;
  });
  J("columns().indexes()", "column().index()", function (a) {
    return this.iterator("column", function (b, c) {
      return "visible" === a ? ua(b, c) : c;
    }, 1);
  });
  y("columns.adjust()", function () {
    return this.iterator("table", function (a) {
      sa(a);
    }, 1);
  });
  y("column.index()", function (a, b) {
    if (0 !== this.context.length) {
      var c = this.context[0];
      if ("fromVisible" === a || "toData" === a) return ta(c, b);
      if ("fromData" === a || "toVisible" === a) return ua(c, b);
    }
  });
  y("column()", function (a, b) {
    return wb(this.columns(a, b));
  });

  var Dc = function Dc(a, b, c) {
    var d = a.aoData,
        e = Wa(a, c),
        h = kc(Ea(d, e, "anCells")),
        f = l(lc([], h)),
        g,
        k = a.aoColumns.length,
        m,
        n,
        p,
        t,
        v,
        x;
    return ub("cell", b, function (w) {
      var r = "function" === typeof w;

      if (null === w || w === q || r) {
        m = [];
        n = 0;

        for (p = e.length; n < p; n++) {
          for (g = e[n], t = 0; t < k; t++) {
            v = {
              row: g,
              column: t
            }, r ? (x = d[g], w(v, T(a, g, t), x.anCells ? x.anCells[t] : null) && m.push(v)) : m.push(v);
          }
        }

        return m;
      }

      if (l.isPlainObject(w)) return w.column !== q && w.row !== q && -1 !== l.inArray(w.row, e) ? [w] : [];
      r = f.filter(w).map(function (C, G) {
        return {
          row: G._DT_CellIndex.row,
          column: G._DT_CellIndex.column
        };
      }).toArray();
      if (r.length || !w.nodeName) return r;
      x = l(w).closest("*[data-dt-row]");
      return x.length ? [{
        row: x.data("dt-row"),
        column: x.data("dt-column")
      }] : [];
    }, a, c);
  };

  y("cells()", function (a, b, c) {
    l.isPlainObject(a) && (a.row === q ? (c = a, a = null) : (c = b, b = null));
    l.isPlainObject(b) && (c = b, b = null);
    if (null === b || b === q) return this.iterator("table", function (n) {
      return Dc(n, a, vb(c));
    });
    var d = c ? {
      page: c.page,
      order: c.order,
      search: c.search
    } : {},
        e = this.columns(b, d),
        h = this.rows(a, d),
        f,
        g,
        k,
        m;
    d = this.iterator("table", function (n, p) {
      n = [];
      f = 0;

      for (g = h[p].length; f < g; f++) {
        for (k = 0, m = e[p].length; k < m; k++) {
          n.push({
            row: h[p][f],
            column: e[p][k]
          });
        }
      }

      return n;
    }, 1);
    d = c && c.selected ? this.cells(d, c) : d;
    l.extend(d.selector, {
      cols: b,
      rows: a,
      opts: c
    });
    return d;
  });
  J("cells().nodes()", "cell().node()", function () {
    return this.iterator("cell", function (a, b, c) {
      return (a = a.aoData[b]) && a.anCells ? a.anCells[c] : q;
    }, 1);
  });
  y("cells().data()", function () {
    return this.iterator("cell", function (a, b, c) {
      return T(a, b, c);
    }, 1);
  });
  J("cells().cache()", "cell().cache()", function (a) {
    a = "search" === a ? "_aFilterData" : "_aSortData";
    return this.iterator("cell", function (b, c, d) {
      return b.aoData[c][a][d];
    }, 1);
  });
  J("cells().render()", "cell().render()", function (a) {
    return this.iterator("cell", function (b, c, d) {
      return T(b, c, d, a);
    }, 1);
  });
  J("cells().indexes()", "cell().index()", function () {
    return this.iterator("cell", function (a, b, c) {
      return {
        row: b,
        column: c,
        columnVisible: ua(a, c)
      };
    }, 1);
  });
  J("cells().invalidate()", "cell().invalidate()", function (a) {
    return this.iterator("cell", function (b, c, d) {
      va(b, c, a, d);
    });
  });
  y("cell()", function (a, b, c) {
    return wb(this.cells(a, b, c));
  });
  y("cell().data()", function (a) {
    var b = this.context,
        c = this[0];
    if (a === q) return b.length && c.length ? T(b[0], c[0].row, c[0].column) : q;
    Eb(b[0], c[0].row, c[0].column, a);
    va(b[0], c[0].row, "data", c[0].column);
    return this;
  });
  y("order()", function (a, b) {
    var c = this.context;
    if (a === q) return 0 !== c.length ? c[0].aaSorting : q;
    "number" === typeof a ? a = [[a, b]] : a.length && !Array.isArray(a[0]) && (a = Array.prototype.slice.call(arguments));
    return this.iterator("table", function (d) {
      d.aaSorting = a.slice();
    });
  });
  y("order.listener()", function (a, b, c) {
    return this.iterator("table", function (d) {
      fb(d, a, b, c);
    });
  });
  y("order.fixed()", function (a) {
    if (!a) {
      var b = this.context;
      b = b.length ? b[0].aaSortingFixed : q;
      return Array.isArray(b) ? {
        pre: b
      } : b;
    }

    return this.iterator("table", function (c) {
      c.aaSortingFixed = l.extend(!0, {}, a);
    });
  });
  y(["columns().order()", "column().order()"], function (a) {
    var b = this;
    return this.iterator("table", function (c, d) {
      var e = [];
      l.each(b[d], function (h, f) {
        e.push([f, a]);
      });
      c.aaSorting = e;
    });
  });
  y("search()", function (a, b, c, d) {
    var e = this.context;
    return a === q ? 0 !== e.length ? e[0].oPreviousSearch.sSearch : q : this.iterator("table", function (h) {
      h.oFeatures.bFilter && ya(h, l.extend({}, h.oPreviousSearch, {
        sSearch: a + "",
        bRegex: null === b ? !1 : b,
        bSmart: null === c ? !0 : c,
        bCaseInsensitive: null === d ? !0 : d
      }), 1);
    });
  });
  J("columns().search()", "column().search()", function (a, b, c, d) {
    return this.iterator("column", function (e, h) {
      var f = e.aoPreSearchCols;
      if (a === q) return f[h].sSearch;
      e.oFeatures.bFilter && (l.extend(f[h], {
        sSearch: a + "",
        bRegex: null === b ? !1 : b,
        bSmart: null === c ? !0 : c,
        bCaseInsensitive: null === d ? !0 : d
      }), ya(e, e.oPreviousSearch, 1));
    });
  });
  y("state()", function () {
    return this.context.length ? this.context[0].oSavedState : null;
  });
  y("state.clear()", function () {
    return this.iterator("table", function (a) {
      a.fnStateSaveCallback.call(a.oInstance, a, {});
    });
  });
  y("state.loaded()", function () {
    return this.context.length ? this.context[0].oLoadedState : null;
  });
  y("state.save()", function () {
    return this.iterator("table", function (a) {
      Ca(a);
    });
  });

  u.versionCheck = u.fnVersionCheck = function (a) {
    var b = u.version.split(".");
    a = a.split(".");

    for (var c, d, e = 0, h = a.length; e < h; e++) {
      if (c = parseInt(b[e], 10) || 0, d = parseInt(a[e], 10) || 0, c !== d) return c > d;
    }

    return !0;
  };

  u.isDataTable = u.fnIsDataTable = function (a) {
    var b = l(a).get(0),
        c = !1;
    if (a instanceof u.Api) return !0;
    l.each(u.settings, function (d, e) {
      d = e.nScrollHead ? l("table", e.nScrollHead)[0] : null;
      var h = e.nScrollFoot ? l("table", e.nScrollFoot)[0] : null;
      if (e.nTable === b || d === b || h === b) c = !0;
    });
    return c;
  };

  u.tables = u.fnTables = function (a) {
    var b = !1;
    l.isPlainObject(a) && (b = a.api, a = a.visible);
    var c = l.map(u.settings, function (d) {
      if (!a || a && l(d.nTable).is(":visible")) return d.nTable;
    });
    return b ? new B(c) : c;
  };

  u.camelToHungarian = P;
  y("$()", function (a, b) {
    b = this.rows(b).nodes();
    b = l(b);
    return l([].concat(b.filter(a).toArray(), b.find(a).toArray()));
  });
  l.each(["on", "one", "off"], function (a, b) {
    y(b + "()", function () {
      var c = Array.prototype.slice.call(arguments);
      c[0] = l.map(c[0].split(/\s/), function (e) {
        return e.match(/\.dt\b/) ? e : e + ".dt";
      }).join(" ");
      var d = l(this.tables().nodes());
      d[b].apply(d, c);
      return this;
    });
  });
  y("clear()", function () {
    return this.iterator("table", function (a) {
      Ka(a);
    });
  });
  y("settings()", function () {
    return new B(this.context, this.context);
  });
  y("init()", function () {
    var a = this.context;
    return a.length ? a[0].oInit : null;
  });
  y("data()", function () {
    return this.iterator("table", function (a) {
      return U(a.aoData, "_aData");
    }).flatten();
  });
  y("destroy()", function (a) {
    a = a || !1;
    return this.iterator("table", function (b) {
      var c = b.nTableWrapper.parentNode,
          d = b.oClasses,
          e = b.nTable,
          h = b.nTBody,
          f = b.nTHead,
          g = b.nTFoot,
          k = l(e);
      h = l(h);
      var m = l(b.nTableWrapper),
          n = l.map(b.aoData, function (t) {
        return t.nTr;
      }),
          p;
      b.bDestroying = !0;
      F(b, "aoDestroyCallback", "destroy", [b]);
      a || new B(b).columns().visible(!0);
      m.off(".DT").find(":not(tbody *)").off(".DT");
      l(z).off(".DT-" + b.sInstance);
      e != f.parentNode && (k.children("thead").detach(), k.append(f));
      g && e != g.parentNode && (k.children("tfoot").detach(), k.append(g));
      b.aaSorting = [];
      b.aaSortingFixed = [];
      Sa(b);
      l(n).removeClass(b.asStripeClasses.join(" "));
      l("th, td", f).removeClass(d.sSortable + " " + d.sSortableAsc + " " + d.sSortableDesc + " " + d.sSortableNone);
      h.children().detach();
      h.append(n);
      f = a ? "remove" : "detach";
      k[f]();
      m[f]();
      !a && c && (c.insertBefore(e, b.nTableReinsertBefore), k.css("width", b.sDestroyWidth).removeClass(d.sTable), (p = b.asDestroyStripes.length) && h.children().each(function (t) {
        l(this).addClass(b.asDestroyStripes[t % p]);
      }));
      c = l.inArray(b, u.settings);
      -1 !== c && u.settings.splice(c, 1);
    });
  });
  l.each(["column", "row", "cell"], function (a, b) {
    y(b + "s().every()", function (c) {
      var d = this.selector.opts,
          e = this;
      return this.iterator(b, function (h, f, g, k, m) {
        c.call(e[b](f, "cell" === b ? g : d, "cell" === b ? d : q), f, g, k, m);
      });
    });
  });
  y("i18n()", function (a, b, c) {
    var d = this.context[0];
    a = na(a)(d.oLanguage);
    a === q && (a = b);
    c !== q && l.isPlainObject(a) && (a = a[c] !== q ? a[c] : a._);
    return a.replace("%d", c);
  });
  u.version = "1.11.5";
  u.settings = [];
  u.models = {};
  u.models.oSearch = {
    bCaseInsensitive: !0,
    sSearch: "",
    bRegex: !1,
    bSmart: !0,
    "return": !1
  };
  u.models.oRow = {
    nTr: null,
    anCells: null,
    _aData: [],
    _aSortData: null,
    _aFilterData: null,
    _sFilterRow: null,
    _sRowStripe: "",
    src: null,
    idx: -1
  };
  u.models.oColumn = {
    idx: null,
    aDataSort: null,
    asSorting: null,
    bSearchable: null,
    bSortable: null,
    bVisible: null,
    _sManualType: null,
    _bAttrSrc: !1,
    fnCreatedCell: null,
    fnGetData: null,
    fnSetData: null,
    mData: null,
    mRender: null,
    nTh: null,
    nTf: null,
    sClass: null,
    sContentPadding: null,
    sDefaultContent: null,
    sName: null,
    sSortDataType: "std",
    sSortingClass: null,
    sSortingClassJUI: null,
    sTitle: null,
    sType: null,
    sWidth: null,
    sWidthOrig: null
  };
  u.defaults = {
    aaData: null,
    aaSorting: [[0, "asc"]],
    aaSortingFixed: [],
    ajax: null,
    aLengthMenu: [10, 25, 50, 100],
    aoColumns: null,
    aoColumnDefs: null,
    aoSearchCols: [],
    asStripeClasses: null,
    bAutoWidth: !0,
    bDeferRender: !1,
    bDestroy: !1,
    bFilter: !0,
    bInfo: !0,
    bLengthChange: !0,
    bPaginate: !0,
    bProcessing: !1,
    bRetrieve: !1,
    bScrollCollapse: !1,
    bServerSide: !1,
    bSort: !0,
    bSortMulti: !0,
    bSortCellsTop: !1,
    bSortClasses: !0,
    bStateSave: !1,
    fnCreatedRow: null,
    fnDrawCallback: null,
    fnFooterCallback: null,
    fnFormatNumber: function fnFormatNumber(a) {
      return a.toString().replace(/\B(?=(\d{3})+(?!\d))/g, this.oLanguage.sThousands);
    },
    fnHeaderCallback: null,
    fnInfoCallback: null,
    fnInitComplete: null,
    fnPreDrawCallback: null,
    fnRowCallback: null,
    fnServerData: null,
    fnServerParams: null,
    fnStateLoadCallback: function fnStateLoadCallback(a) {
      try {
        return JSON.parse((-1 === a.iStateDuration ? sessionStorage : localStorage).getItem("DataTables_" + a.sInstance + "_" + location.pathname));
      } catch (b) {
        return {};
      }
    },
    fnStateLoadParams: null,
    fnStateLoaded: null,
    fnStateSaveCallback: function fnStateSaveCallback(a, b) {
      try {
        (-1 === a.iStateDuration ? sessionStorage : localStorage).setItem("DataTables_" + a.sInstance + "_" + location.pathname, JSON.stringify(b));
      } catch (c) {}
    },
    fnStateSaveParams: null,
    iStateDuration: 7200,
    iDeferLoading: null,
    iDisplayLength: 10,
    iDisplayStart: 0,
    iTabIndex: 0,
    oClasses: {},
    oLanguage: {
      oAria: {
        sSortAscending: ": activate to sort column ascending",
        sSortDescending: ": activate to sort column descending"
      },
      oPaginate: {
        sFirst: "First",
        sLast: "Last",
        sNext: "Next",
        sPrevious: "Previous"
      },
      sEmptyTable: "No data available in table",
      sInfo: "Showing _START_ to _END_ of _TOTAL_ entries",
      sInfoEmpty: "Showing 0 to 0 of 0 entries",
      sInfoFiltered: "(filtered from _MAX_ total entries)",
      sInfoPostFix: "",
      sDecimal: "",
      sThousands: ",",
      sLengthMenu: "Show _MENU_ entries",
      sLoadingRecords: "Loading...",
      sProcessing: "Processing...",
      sSearch: "Search:",
      sSearchPlaceholder: "",
      sUrl: "",
      sZeroRecords: "No matching records found"
    },
    oSearch: l.extend({}, u.models.oSearch),
    sAjaxDataProp: "data",
    sAjaxSource: null,
    sDom: "lfrtip",
    searchDelay: null,
    sPaginationType: "simple_numbers",
    sScrollX: "",
    sScrollXInner: "",
    sScrollY: "",
    sServerMethod: "GET",
    renderer: null,
    rowId: "DT_RowId"
  };
  E(u.defaults);
  u.defaults.column = {
    aDataSort: null,
    iDataSort: -1,
    asSorting: ["asc", "desc"],
    bSearchable: !0,
    bSortable: !0,
    bVisible: !0,
    fnCreatedCell: null,
    mData: null,
    mRender: null,
    sCellType: "td",
    sClass: "",
    sContentPadding: "",
    sDefaultContent: null,
    sName: "",
    sSortDataType: "std",
    sTitle: null,
    sType: null,
    sWidth: null
  };
  E(u.defaults.column);
  u.models.oSettings = {
    oFeatures: {
      bAutoWidth: null,
      bDeferRender: null,
      bFilter: null,
      bInfo: null,
      bLengthChange: null,
      bPaginate: null,
      bProcessing: null,
      bServerSide: null,
      bSort: null,
      bSortMulti: null,
      bSortClasses: null,
      bStateSave: null
    },
    oScroll: {
      bCollapse: null,
      iBarWidth: 0,
      sX: null,
      sXInner: null,
      sY: null
    },
    oLanguage: {
      fnInfoCallback: null
    },
    oBrowser: {
      bScrollOversize: !1,
      bScrollbarLeft: !1,
      bBounding: !1,
      barWidth: 0
    },
    ajax: null,
    aanFeatures: [],
    aoData: [],
    aiDisplay: [],
    aiDisplayMaster: [],
    aIds: {},
    aoColumns: [],
    aoHeader: [],
    aoFooter: [],
    oPreviousSearch: {},
    aoPreSearchCols: [],
    aaSorting: null,
    aaSortingFixed: [],
    asStripeClasses: null,
    asDestroyStripes: [],
    sDestroyWidth: 0,
    aoRowCallback: [],
    aoHeaderCallback: [],
    aoFooterCallback: [],
    aoDrawCallback: [],
    aoRowCreatedCallback: [],
    aoPreDrawCallback: [],
    aoInitComplete: [],
    aoStateSaveParams: [],
    aoStateLoadParams: [],
    aoStateLoaded: [],
    sTableId: "",
    nTable: null,
    nTHead: null,
    nTFoot: null,
    nTBody: null,
    nTableWrapper: null,
    bDeferLoading: !1,
    bInitialised: !1,
    aoOpenRows: [],
    sDom: null,
    searchDelay: null,
    sPaginationType: "two_button",
    iStateDuration: 0,
    aoStateSave: [],
    aoStateLoad: [],
    oSavedState: null,
    oLoadedState: null,
    sAjaxSource: null,
    sAjaxDataProp: null,
    jqXHR: null,
    json: q,
    oAjaxData: q,
    fnServerData: null,
    aoServerParams: [],
    sServerMethod: null,
    fnFormatNumber: null,
    aLengthMenu: null,
    iDraw: 0,
    bDrawing: !1,
    iDrawError: -1,
    _iDisplayLength: 10,
    _iDisplayStart: 0,
    _iRecordsTotal: 0,
    _iRecordsDisplay: 0,
    oClasses: {},
    bFiltered: !1,
    bSorted: !1,
    bSortCellsTop: null,
    oInit: null,
    aoDestroyCallback: [],
    fnRecordsTotal: function fnRecordsTotal() {
      return "ssp" == Q(this) ? 1 * this._iRecordsTotal : this.aiDisplayMaster.length;
    },
    fnRecordsDisplay: function fnRecordsDisplay() {
      return "ssp" == Q(this) ? 1 * this._iRecordsDisplay : this.aiDisplay.length;
    },
    fnDisplayEnd: function fnDisplayEnd() {
      var a = this._iDisplayLength,
          b = this._iDisplayStart,
          c = b + a,
          d = this.aiDisplay.length,
          e = this.oFeatures,
          h = e.bPaginate;
      return e.bServerSide ? !1 === h || -1 === a ? b + d : Math.min(b + a, this._iRecordsDisplay) : !h || c > d || -1 === a ? d : c;
    },
    oInstance: null,
    sInstance: null,
    iTabIndex: 0,
    nScrollHead: null,
    nScrollFoot: null,
    aLastSort: [],
    oPlugins: {},
    rowIdFn: null,
    rowId: null
  };
  u.ext = M = {
    buttons: {},
    classes: {},
    builder: "dt/jszip-2.5.0/dt-1.11.5/b-2.2.2/b-colvis-2.2.2/b-html5-2.2.2/b-print-2.2.2/cr-1.5.5/r-2.2.9/sl-1.3.4",
    errMode: "alert",
    feature: [],
    search: [],
    selector: {
      cell: [],
      column: [],
      row: []
    },
    internal: {},
    legacy: {
      ajax: null
    },
    pager: {},
    renderer: {
      pageButton: {},
      header: {}
    },
    order: {},
    type: {
      detect: [],
      search: {},
      order: {}
    },
    _unique: 0,
    fnVersionCheck: u.fnVersionCheck,
    iApiIndex: 0,
    oJUIClasses: {},
    sVersion: u.version
  };
  l.extend(M, {
    afnFiltering: M.search,
    aTypes: M.type.detect,
    ofnSearch: M.type.search,
    oSort: M.type.order,
    afnSortData: M.order,
    aoFeatures: M.feature,
    oApi: M.internal,
    oStdClasses: M.classes,
    oPagination: M.pager
  });
  l.extend(u.ext.classes, {
    sTable: "dataTable",
    sNoFooter: "no-footer",
    sPageButton: "paginate_button",
    sPageButtonActive: "current",
    sPageButtonDisabled: "disabled",
    sStripeOdd: "odd",
    sStripeEven: "even",
    sRowEmpty: "dataTables_empty",
    sWrapper: "dataTables_wrapper",
    sFilter: "dataTables_filter",
    sInfo: "dataTables_info",
    sPaging: "dataTables_paginate paging_",
    sLength: "dataTables_length",
    sProcessing: "dataTables_processing",
    sSortAsc: "sorting_asc",
    sSortDesc: "sorting_desc",
    sSortable: "sorting",
    sSortableAsc: "sorting_desc_disabled",
    sSortableDesc: "sorting_asc_disabled",
    sSortableNone: "sorting_disabled",
    sSortColumn: "sorting_",
    sFilterInput: "",
    sLengthSelect: "",
    sScrollWrapper: "dataTables_scroll",
    sScrollHead: "dataTables_scrollHead",
    sScrollHeadInner: "dataTables_scrollHeadInner",
    sScrollBody: "dataTables_scrollBody",
    sScrollFoot: "dataTables_scrollFoot",
    sScrollFootInner: "dataTables_scrollFootInner",
    sHeaderTH: "",
    sFooterTH: "",
    sSortJUIAsc: "",
    sSortJUIDesc: "",
    sSortJUI: "",
    sSortJUIAscAllowed: "",
    sSortJUIDescAllowed: "",
    sSortJUIWrapper: "",
    sSortIcon: "",
    sJUIHeader: "",
    sJUIFooter: ""
  });
  var ec = u.ext.pager;
  l.extend(ec, {
    simple: function simple(a, b) {
      return ["previous", "next"];
    },
    full: function full(a, b) {
      return ["first", "previous", "next", "last"];
    },
    numbers: function numbers(a, b) {
      return [Da(a, b)];
    },
    simple_numbers: function simple_numbers(a, b) {
      return ["previous", Da(a, b), "next"];
    },
    full_numbers: function full_numbers(a, b) {
      return ["first", "previous", Da(a, b), "next", "last"];
    },
    first_last_numbers: function first_last_numbers(a, b) {
      return ["first", Da(a, b), "last"];
    },
    _numbers: Da,
    numbers_length: 7
  });
  l.extend(!0, u.ext.renderer, {
    pageButton: {
      _: function _(a, b, c, d, e, h) {
        var f = a.oClasses,
            g = a.oLanguage.oPaginate,
            k = a.oLanguage.oAria.paginate || {},
            m,
            n,
            p = 0,
            t = function t(x, w) {
          var r,
              C = f.sPageButtonDisabled,
              G = function G(I) {
            Ra(a, I.data.action, !0);
          };

          var aa = 0;

          for (r = w.length; aa < r; aa++) {
            var L = w[aa];

            if (Array.isArray(L)) {
              var O = l("<" + (L.DT_el || "div") + "/>").appendTo(x);
              t(O, L);
            } else {
              m = null;
              n = L;
              O = a.iTabIndex;

              switch (L) {
                case "ellipsis":
                  x.append('<span class="ellipsis">&#x2026;</span>');
                  break;

                case "first":
                  m = g.sFirst;
                  0 === e && (O = -1, n += " " + C);
                  break;

                case "previous":
                  m = g.sPrevious;
                  0 === e && (O = -1, n += " " + C);
                  break;

                case "next":
                  m = g.sNext;
                  if (0 === h || e === h - 1) O = -1, n += " " + C;
                  break;

                case "last":
                  m = g.sLast;
                  if (0 === h || e === h - 1) O = -1, n += " " + C;
                  break;

                default:
                  m = a.fnFormatNumber(L + 1), n = e === L ? f.sPageButtonActive : "";
              }

              null !== m && (O = l("<a>", {
                "class": f.sPageButton + " " + n,
                "aria-controls": a.sTableId,
                "aria-label": k[L],
                "data-dt-idx": p,
                tabindex: O,
                id: 0 === c && "string" === typeof L ? a.sTableId + "_" + L : null
              }).html(m).appendTo(x), ob(O, {
                action: L
              }, G), p++);
            }
          }
        };

        try {
          var v = l(b).find(A.activeElement).data("dt-idx");
        } catch (x) {}

        t(l(b).empty(), d);
        v !== q && l(b).find("[data-dt-idx=" + v + "]").trigger("focus");
      }
    }
  });
  l.extend(u.ext.type.detect, [function (a, b) {
    b = b.oLanguage.sDecimal;
    return tb(a, b) ? "num" + b : null;
  }, function (a, b) {
    if (a && !(a instanceof Date) && !vc.test(a)) return null;
    b = Date.parse(a);
    return null !== b && !isNaN(b) || Z(a) ? "date" : null;
  }, function (a, b) {
    b = b.oLanguage.sDecimal;
    return tb(a, b, !0) ? "num-fmt" + b : null;
  }, function (a, b) {
    b = b.oLanguage.sDecimal;
    return jc(a, b) ? "html-num" + b : null;
  }, function (a, b) {
    b = b.oLanguage.sDecimal;
    return jc(a, b, !0) ? "html-num-fmt" + b : null;
  }, function (a, b) {
    return Z(a) || "string" === typeof a && -1 !== a.indexOf("<") ? "html" : null;
  }]);
  l.extend(u.ext.type.search, {
    html: function html(a) {
      return Z(a) ? a : "string" === typeof a ? a.replace(gc, " ").replace(Va, "") : "";
    },
    string: function string(a) {
      return Z(a) ? a : "string" === typeof a ? a.replace(gc, " ") : a;
    }
  });

  var Ua = function Ua(a, b, c, d) {
    if (0 !== a && (!a || "-" === a)) return -Infinity;
    b && (a = ic(a, b));
    a.replace && (c && (a = a.replace(c, "")), d && (a = a.replace(d, "")));
    return 1 * a;
  };

  l.extend(M.type.order, {
    "date-pre": function datePre(a) {
      a = Date.parse(a);
      return isNaN(a) ? -Infinity : a;
    },
    "html-pre": function htmlPre(a) {
      return Z(a) ? "" : a.replace ? a.replace(/<.*?>/g, "").toLowerCase() : a + "";
    },
    "string-pre": function stringPre(a) {
      return Z(a) ? "" : "string" === typeof a ? a.toLowerCase() : a.toString ? a.toString() : "";
    },
    "string-asc": function stringAsc(a, b) {
      return a < b ? -1 : a > b ? 1 : 0;
    },
    "string-desc": function stringDesc(a, b) {
      return a < b ? 1 : a > b ? -1 : 0;
    }
  });
  Xa("");
  l.extend(!0, u.ext.renderer, {
    header: {
      _: function _(a, b, c, d) {
        l(a.nTable).on("order.dt.DT", function (e, h, f, g) {
          a === h && (e = c.idx, b.removeClass(d.sSortAsc + " " + d.sSortDesc).addClass("asc" == g[e] ? d.sSortAsc : "desc" == g[e] ? d.sSortDesc : c.sSortingClass));
        });
      },
      jqueryui: function jqueryui(a, b, c, d) {
        l("<div/>").addClass(d.sSortJUIWrapper).append(b.contents()).append(l("<span/>").addClass(d.sSortIcon + " " + c.sSortingClassJUI)).appendTo(b);
        l(a.nTable).on("order.dt.DT", function (e, h, f, g) {
          a === h && (e = c.idx, b.removeClass(d.sSortAsc + " " + d.sSortDesc).addClass("asc" == g[e] ? d.sSortAsc : "desc" == g[e] ? d.sSortDesc : c.sSortingClass), b.find("span." + d.sSortIcon).removeClass(d.sSortJUIAsc + " " + d.sSortJUIDesc + " " + d.sSortJUI + " " + d.sSortJUIAscAllowed + " " + d.sSortJUIDescAllowed).addClass("asc" == g[e] ? d.sSortJUIAsc : "desc" == g[e] ? d.sSortJUIDesc : c.sSortingClassJUI));
        });
      }
    }
  });

  var yb = function yb(a) {
    Array.isArray(a) && (a = a.join(","));
    return "string" === typeof a ? a.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;") : a;
  };

  u.render = {
    number: function number(a, b, c, d, e) {
      return {
        display: function display(h) {
          if ("number" !== typeof h && "string" !== typeof h) return h;
          var f = 0 > h ? "-" : "",
              g = parseFloat(h);
          if (isNaN(g)) return yb(h);
          g = g.toFixed(c);
          h = Math.abs(g);
          g = parseInt(h, 10);
          h = c ? b + (h - g).toFixed(c).substring(2) : "";
          0 === g && 0 === parseFloat(h) && (f = "");
          return f + (d || "") + g.toString().replace(/\B(?=(\d{3})+(?!\d))/g, a) + h + (e || "");
        }
      };
    },
    text: function text() {
      return {
        display: yb,
        filter: yb
      };
    }
  };
  l.extend(u.ext.internal, {
    _fnExternApiFunc: fc,
    _fnBuildAjax: Oa,
    _fnAjaxUpdate: Gb,
    _fnAjaxParameters: Pb,
    _fnAjaxUpdateDraw: Qb,
    _fnAjaxDataSrc: za,
    _fnAddColumn: Ya,
    _fnColumnOptions: Ga,
    _fnAdjustColumnSizing: sa,
    _fnVisibleToColumnIndex: ta,
    _fnColumnIndexToVisible: ua,
    _fnVisbleColumns: oa,
    _fnGetColumns: Ia,
    _fnColumnTypes: $a,
    _fnApplyColumnDefs: Db,
    _fnHungarianMap: E,
    _fnCamelToHungarian: P,
    _fnLanguageCompat: ma,
    _fnBrowserDetect: Bb,
    _fnAddData: ia,
    _fnAddTr: Ja,
    _fnNodeToDataIndex: function _fnNodeToDataIndex(a, b) {
      return b._DT_RowIndex !== q ? b._DT_RowIndex : null;
    },
    _fnNodeToColumnIndex: function _fnNodeToColumnIndex(a, b, c) {
      return l.inArray(c, a.aoData[b].anCells);
    },
    _fnGetCellData: T,
    _fnSetCellData: Eb,
    _fnSplitObjNotation: cb,
    _fnGetObjectDataFn: na,
    _fnSetObjectDataFn: ha,
    _fnGetDataMaster: db,
    _fnClearTable: Ka,
    _fnDeleteIndex: La,
    _fnInvalidate: va,
    _fnGetRowElements: bb,
    _fnCreateTr: ab,
    _fnBuildHead: Fb,
    _fnDrawHead: xa,
    _fnDraw: ja,
    _fnReDraw: ka,
    _fnAddOptionsHtml: Ib,
    _fnDetectHeader: wa,
    _fnGetUniqueThs: Na,
    _fnFeatureHtmlFilter: Kb,
    _fnFilterComplete: ya,
    _fnFilterCustom: Tb,
    _fnFilterColumn: Sb,
    _fnFilter: Rb,
    _fnFilterCreateSearch: ib,
    _fnEscapeRegex: jb,
    _fnFilterData: Ub,
    _fnFeatureHtmlInfo: Nb,
    _fnUpdateInfo: Xb,
    _fnInfoMacros: Yb,
    _fnInitialise: Aa,
    _fnInitComplete: Pa,
    _fnLengthChange: kb,
    _fnFeatureHtmlLength: Jb,
    _fnFeatureHtmlPaginate: Ob,
    _fnPageChange: Ra,
    _fnFeatureHtmlProcessing: Lb,
    _fnProcessingDisplay: V,
    _fnFeatureHtmlTable: Mb,
    _fnScrollDraw: Ha,
    _fnApplyToChildren: ca,
    _fnCalculateColumnWidths: Za,
    _fnThrottle: hb,
    _fnConvertToWidth: Zb,
    _fnGetWidestNode: $b,
    _fnGetMaxLenString: ac,
    _fnStringToCss: K,
    _fnSortFlatten: pa,
    _fnSort: Hb,
    _fnSortAria: cc,
    _fnSortListener: nb,
    _fnSortAttachListener: fb,
    _fnSortingClasses: Sa,
    _fnSortData: bc,
    _fnSaveState: Ca,
    _fnLoadState: dc,
    _fnImplementState: pb,
    _fnSettingsFromNode: Ta,
    _fnLog: da,
    _fnMap: X,
    _fnBindAction: ob,
    _fnCallbackReg: R,
    _fnCallbackFire: F,
    _fnLengthOverflow: lb,
    _fnRenderer: gb,
    _fnDataSource: Q,
    _fnRowAttributes: eb,
    _fnExtend: qb,
    _fnCalculateEnd: function _fnCalculateEnd() {}
  });
  l.fn.dataTable = u;
  u.$ = l;
  l.fn.dataTableSettings = u.settings;
  l.fn.dataTableExt = u.ext;

  l.fn.DataTable = function (a) {
    return l(this).dataTable(a).api();
  };

  l.each(u, function (a, b) {
    l.fn.DataTable[a] = b;
  });
  return u;
});
/*!
 DataTables styling integration
 ©2018 SpryMedia Ltd - datatables.net/license
*/


(function (c) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net"], function (a) {
    return c(a, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (a, b) {
    a || (a = window);
    b && b.fn.dataTable || (b = require("datatables.net")(a, b).$);
    return c(b, a, a.document);
  } : c(jQuery, window, document);
})(function (c, a, b, d) {
  return c.fn.dataTable;
});
/*!
 Buttons for DataTables 2.2.2
 ©2016-2022 SpryMedia Ltd - datatables.net/license
*/


(function (d) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net"], function (z) {
    return d(z, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (z, B) {
    z || (z = window);
    B && B.fn.dataTable || (B = require("datatables.net")(z, B).$);
    return d(B, z, z.document);
  } : d(jQuery, window, document);
})(function (d, z, B, p) {
  function I(a, b, c) {
    d.fn.animate ? a.stop().fadeIn(b, c) : (a.css("display", "block"), c && c.call(a));
  }

  function J(a, b, c) {
    d.fn.animate ? a.stop().fadeOut(b, c) : (a.css("display", "none"), c && c.call(a));
  }

  function L(a, b) {
    a = new u.Api(a);
    b = b ? b : a.init().buttons || u.defaults.buttons;
    return new x(a, b).container();
  }

  var u = d.fn.dataTable,
      O = 0,
      P = 0,
      C = u.ext.buttons,
      x = function x(a, b) {
    if (!(this instanceof x)) return function (c) {
      return new x(c, a).container();
    };
    "undefined" === typeof b && (b = {});
    !0 === b && (b = {});
    Array.isArray(b) && (b = {
      buttons: b
    });
    this.c = d.extend(!0, {}, x.defaults, b);
    b.buttons && (this.c.buttons = b.buttons);
    this.s = {
      dt: new u.Api(a),
      buttons: [],
      listenKeys: "",
      namespace: "dtb" + O++
    };
    this.dom = {
      container: d("<" + this.c.dom.container.tag + "/>").addClass(this.c.dom.container.className)
    };

    this._constructor();
  };

  d.extend(x.prototype, {
    action: function action(a, b) {
      a = this._nodeToButton(a);
      if (b === p) return a.conf.action;
      a.conf.action = b;
      return this;
    },
    active: function active(a, b) {
      var c = this._nodeToButton(a);

      a = this.c.dom.button.active;
      c = d(c.node);
      if (b === p) return c.hasClass(a);
      c.toggleClass(a, b === p ? !0 : b);
      return this;
    },
    add: function add(a, b, c) {
      var e = this.s.buttons;

      if ("string" === typeof b) {
        b = b.split("-");
        var h = this.s;
        e = 0;

        for (var f = b.length - 1; e < f; e++) {
          h = h.buttons[1 * b[e]];
        }

        e = h.buttons;
        b = 1 * b[b.length - 1];
      }

      this._expandButton(e, a, a !== p ? a.split : p, (a === p || a.split === p || 0 === a.split.length) && h !== p, !1, b);

      c !== p && !0 !== c || this._draw();
      return this;
    },
    collectionRebuild: function collectionRebuild(a, b) {
      a = this._nodeToButton(a);

      if (b !== p) {
        var c;

        for (c = a.buttons.length - 1; 0 <= c; c--) {
          this.remove(a.buttons[c].node);
        }

        for (c = 0; c < b.length; c++) {
          var e = b[c];

          this._expandButton(a.buttons, e, e !== p && e.config !== p && e.config.split !== p, !0, e.parentConf !== p && e.parentConf.split !== p, c, e.parentConf);
        }
      }

      this._draw(a.collection, a.buttons);
    },
    container: function container() {
      return this.dom.container;
    },
    disable: function disable(a) {
      a = this._nodeToButton(a);
      d(a.node).addClass(this.c.dom.button.disabled).attr("disabled", !0);
      return this;
    },
    destroy: function destroy() {
      d("body").off("keyup." + this.s.namespace);
      var a = this.s.buttons.slice(),
          b;
      var c = 0;

      for (b = a.length; c < b; c++) {
        this.remove(a[c].node);
      }

      this.dom.container.remove();
      a = this.s.dt.settings()[0];
      c = 0;

      for (b = a.length; c < b; c++) {
        if (a.inst === this) {
          a.splice(c, 1);
          break;
        }
      }

      return this;
    },
    enable: function enable(a, b) {
      if (!1 === b) return this.disable(a);
      a = this._nodeToButton(a);
      d(a.node).removeClass(this.c.dom.button.disabled).removeAttr("disabled");
      return this;
    },
    index: function index(a, b, c) {
      b || (b = "", c = this.s.buttons);

      for (var e = 0, h = c.length; e < h; e++) {
        var f = c[e].buttons;
        if (c[e].node === a) return b + e;
        if (f && f.length && (f = this.index(a, e + "-", f), null !== f)) return f;
      }

      return null;
    },
    name: function name() {
      return this.c.name;
    },
    node: function node(a) {
      if (!a) return this.dom.container;
      a = this._nodeToButton(a);
      return d(a.node);
    },
    processing: function processing(a, b) {
      var c = this.s.dt,
          e = this._nodeToButton(a);

      if (b === p) return d(e.node).hasClass("processing");
      d(e.node).toggleClass("processing", b);
      d(c.table().node()).triggerHandler("buttons-processing.dt", [b, c.button(a), c, d(a), e.conf]);
      return this;
    },
    remove: function remove(a) {
      var b = this._nodeToButton(a),
          c = this._nodeToHost(a),
          e = this.s.dt;

      if (b.buttons.length) for (var h = b.buttons.length - 1; 0 <= h; h--) {
        this.remove(b.buttons[h].node);
      }
      b.conf.destroying = !0;
      b.conf.destroy && b.conf.destroy.call(e.button(a), e, d(a), b.conf);

      this._removeKey(b.conf);

      d(b.node).remove();
      a = d.inArray(b, c);
      c.splice(a, 1);
      return this;
    },
    text: function text(a, b) {
      var c = this._nodeToButton(a);

      a = this.c.dom.collection.buttonLiner;
      a = c.inCollection && a && a.tag ? a.tag : this.c.dom.buttonLiner.tag;

      var e = this.s.dt,
          h = d(c.node),
          f = function f(g) {
        return "function" === typeof g ? g(e, h, c.conf) : g;
      };

      if (b === p) return f(c.conf.text);
      c.conf.text = b;
      a ? h.children(a).eq(0).filter(":not(.dt-down-arrow)").html(f(b)) : h.html(f(b));
      return this;
    },
    _constructor: function _constructor() {
      var a = this,
          b = this.s.dt,
          c = b.settings()[0],
          e = this.c.buttons;
      c._buttons || (c._buttons = []);

      c._buttons.push({
        inst: this,
        name: this.c.name
      });

      for (var h = 0, f = e.length; h < f; h++) {
        this.add(e[h]);
      }

      b.on("destroy", function (g, l) {
        l === c && a.destroy();
      });
      d("body").on("keyup." + this.s.namespace, function (g) {
        if (!B.activeElement || B.activeElement === B.body) {
          var l = String.fromCharCode(g.keyCode).toLowerCase();
          -1 !== a.s.listenKeys.toLowerCase().indexOf(l) && a._keypress(l, g);
        }
      });
    },
    _addKey: function _addKey(a) {
      a.key && (this.s.listenKeys += d.isPlainObject(a.key) ? a.key.key : a.key);
    },
    _draw: function _draw(a, b) {
      a || (a = this.dom.container, b = this.s.buttons);
      a.children().detach();

      for (var c = 0, e = b.length; c < e; c++) {
        a.append(b[c].inserter), a.append(" "), b[c].buttons && b[c].buttons.length && this._draw(b[c].collection, b[c].buttons);
      }
    },
    _expandButton: function _expandButton(a, b, c, e, h, f, g) {
      var l = this.s.dt,
          m = 0,
          r = Array.isArray(b) ? b : [b];
      b === p && (r = Array.isArray(c) ? c : [c]);
      c = 0;

      for (var q = r.length; c < q; c++) {
        var n = this._resolveExtends(r[c]);

        if (n) if (b = n.config !== p && n.config.split ? !0 : !1, Array.isArray(n)) this._expandButton(a, n, k !== p && k.conf !== p ? k.conf.split : p, e, g !== p && g.split !== p, f, g);else {
          var k = this._buildButton(n, e, n.split !== p || n.config !== p && n.config.split !== p, h);

          if (k) {
            f !== p && null !== f ? (a.splice(f, 0, k), f++) : a.push(k);

            if (k.conf.buttons || k.conf.split) {
              k.collection = d("<" + (b ? this.c.dom.splitCollection.tag : this.c.dom.collection.tag) + "/>");
              k.conf._collection = k.collection;
              if (k.conf.split) for (var t = 0; t < k.conf.split.length; t++) {
                "object" === _typeof(k.conf.split[t]) && (k.conf.split[t].parent = g, k.conf.split[t].collectionLayout === p && (k.conf.split[t].collectionLayout = k.conf.collectionLayout), k.conf.split[t].dropup === p && (k.conf.split[t].dropup = k.conf.dropup), k.conf.split[t].fade === p && (k.conf.split[t].fade = k.conf.fade));
              } else d(k.node).append(d('<span class="dt-down-arrow">' + this.c.dom.splitDropdown.text + "</span>"));

              this._expandButton(k.buttons, k.conf.buttons, k.conf.split, !b, b, f, k.conf);
            }

            k.conf.parent = g;
            n.init && n.init.call(l.button(k.node), l, d(k.node), n);
            m++;
          }
        }
      }
    },
    _buildButton: function _buildButton(a, b, c, e) {
      var h = this.c.dom.button,
          f = this.c.dom.buttonLiner,
          g = this.c.dom.collection,
          l = this.c.dom.splitCollection,
          m = this.c.dom.splitDropdownButton,
          r = this.s.dt,
          q = function q(w) {
        return "function" === typeof w ? w(r, k, a) : w;
      };

      if (a.spacer) {
        var n = d("<span></span>").addClass("dt-button-spacer " + a.style + " " + h.spacerClass).html(q(a.text));
        return {
          conf: a,
          node: n,
          inserter: n,
          buttons: [],
          inCollection: b,
          isSplit: c,
          inSplit: e,
          collection: null
        };
      }

      !c && e && l ? h = m : !c && b && g.button && (h = g.button);
      !c && e && l.buttonLiner ? f = l.buttonLiner : !c && b && g.buttonLiner && (f = g.buttonLiner);
      if (a.available && !a.available(r, a) && !a.hasOwnProperty("html")) return !1;
      if (a.hasOwnProperty("html")) var k = d(a.html);else {
        var t = function t(w, D, F, G) {
          G.action.call(D.button(F), w, D, F, G);
          d(D.table().node()).triggerHandler("buttons-action.dt", [D.button(F), D, F, G]);
        };

        g = a.tag || h.tag;
        var y = a.clickBlurs === p ? !0 : a.clickBlurs;
        k = d("<" + g + "/>").addClass(h.className).addClass(e ? this.c.dom.splitDropdownButton.className : "").attr("tabindex", this.s.dt.settings()[0].iTabIndex).attr("aria-controls", this.s.dt.table().node().id).on("click.dtb", function (w) {
          w.preventDefault();
          !k.hasClass(h.disabled) && a.action && t(w, r, k, a);
          y && k.trigger("blur");
        }).on("keypress.dtb", function (w) {
          13 === w.keyCode && (w.preventDefault(), !k.hasClass(h.disabled) && a.action && t(w, r, k, a));
        });
        "a" === g.toLowerCase() && k.attr("href", "#");
        "button" === g.toLowerCase() && k.attr("type", "button");
        f.tag ? (g = d("<" + f.tag + "/>").html(q(a.text)).addClass(f.className), "a" === f.tag.toLowerCase() && g.attr("href", "#"), k.append(g)) : k.html(q(a.text));
        !1 === a.enabled && k.addClass(h.disabled);
        a.className && k.addClass(a.className);
        a.titleAttr && k.attr("title", q(a.titleAttr));
        a.attr && k.attr(a.attr);
        a.namespace || (a.namespace = ".dt-button-" + P++);
        a.config !== p && a.config.split && (a.split = a.config.split);
      }
      f = (f = this.c.dom.buttonContainer) && f.tag ? d("<" + f.tag + "/>").addClass(f.className).append(k) : k;

      this._addKey(a);

      this.c.buttonCreated && (f = this.c.buttonCreated(a, f));

      if (c) {
        n = d("<div/>").addClass(this.c.dom.splitWrapper.className);
        n.append(k);
        var v = d.extend(a, {
          text: this.c.dom.splitDropdown.text,
          className: this.c.dom.splitDropdown.className,
          closeButton: !1,
          attr: {
            "aria-haspopup": !0,
            "aria-expanded": !1
          },
          align: this.c.dom.splitDropdown.align,
          splitAlignClass: this.c.dom.splitDropdown.splitAlignClass
        });

        this._addKey(v);

        var E = function E(w, D, F, G) {
          C.split.action.call(D.button(d("div.dt-btn-split-wrapper")[0]), w, D, F, G);
          d(D.table().node()).triggerHandler("buttons-action.dt", [D.button(F), D, F, G]);
          F.attr("aria-expanded", !0);
        },
            A = d('<button class="' + this.c.dom.splitDropdown.className + ' dt-button"><span class="dt-btn-split-drop-arrow">' + this.c.dom.splitDropdown.text + "</span></button>").on("click.dtb", function (w) {
          w.preventDefault();
          w.stopPropagation();
          A.hasClass(h.disabled) || E(w, r, A, v);
          y && A.trigger("blur");
        }).on("keypress.dtb", function (w) {
          13 === w.keyCode && (w.preventDefault(), A.hasClass(h.disabled) || E(w, r, A, v));
        });

        0 === a.split.length && A.addClass("dtb-hide-drop");
        n.append(A).attr(v.attr);
      }

      return {
        conf: a,
        node: c ? n.get(0) : k.get(0),
        inserter: c ? n : f,
        buttons: [],
        inCollection: b,
        isSplit: c,
        inSplit: e,
        collection: null
      };
    },
    _nodeToButton: function _nodeToButton(a, b) {
      b || (b = this.s.buttons);

      for (var c = 0, e = b.length; c < e; c++) {
        if (b[c].node === a) return b[c];

        if (b[c].buttons.length) {
          var h = this._nodeToButton(a, b[c].buttons);

          if (h) return h;
        }
      }
    },
    _nodeToHost: function _nodeToHost(a, b) {
      b || (b = this.s.buttons);

      for (var c = 0, e = b.length; c < e; c++) {
        if (b[c].node === a) return b;

        if (b[c].buttons.length) {
          var h = this._nodeToHost(a, b[c].buttons);

          if (h) return h;
        }
      }
    },
    _keypress: function _keypress(a, b) {
      if (!b._buttonsHandled) {
        var c = function c(e) {
          for (var h = 0, f = e.length; h < f; h++) {
            var g = e[h].conf,
                l = e[h].node;
            g.key && (g.key === a ? (b._buttonsHandled = !0, d(l).click()) : !d.isPlainObject(g.key) || g.key.key !== a || g.key.shiftKey && !b.shiftKey || g.key.altKey && !b.altKey || g.key.ctrlKey && !b.ctrlKey || g.key.metaKey && !b.metaKey || (b._buttonsHandled = !0, d(l).click()));
            e[h].buttons.length && c(e[h].buttons);
          }
        };

        c(this.s.buttons);
      }
    },
    _removeKey: function _removeKey(a) {
      if (a.key) {
        var b = d.isPlainObject(a.key) ? a.key.key : a.key;
        a = this.s.listenKeys.split("");
        b = d.inArray(b, a);
        a.splice(b, 1);
        this.s.listenKeys = a.join("");
      }
    },
    _resolveExtends: function _resolveExtends(a) {
      var b = this,
          c = this.s.dt,
          e,
          h = function h(m) {
        for (var r = 0; !d.isPlainObject(m) && !Array.isArray(m);) {
          if (m === p) return;

          if ("function" === typeof m) {
            if (m = m.call(b, c, a), !m) return !1;
          } else if ("string" === typeof m) {
            if (!C[m]) return {
              html: m
            };
            m = C[m];
          }

          r++;
          if (30 < r) throw "Buttons: Too many iterations";
        }

        return Array.isArray(m) ? m : d.extend({}, m);
      };

      for (a = h(a); a && a.extend;) {
        if (!C[a.extend]) throw "Cannot extend unknown button type: " + a.extend;
        var f = h(C[a.extend]);
        if (Array.isArray(f)) return f;
        if (!f) return !1;
        var g = f.className;
        a.config !== p && f.config !== p && (a.config = d.extend({}, f.config, a.config));
        a = d.extend({}, f, a);
        g && a.className !== g && (a.className = g + " " + a.className);
        var l = a.postfixButtons;

        if (l) {
          a.buttons || (a.buttons = []);
          g = 0;

          for (e = l.length; g < e; g++) {
            a.buttons.push(l[g]);
          }

          a.postfixButtons = null;
        }

        if (l = a.prefixButtons) {
          a.buttons || (a.buttons = []);
          g = 0;

          for (e = l.length; g < e; g++) {
            a.buttons.splice(g, 0, l[g]);
          }

          a.prefixButtons = null;
        }

        a.extend = f.extend;
      }

      return a;
    },
    _popover: function _popover(a, b, c, e) {
      e = this.c;

      var h = !1,
          f = d.extend({
        align: "button-left",
        autoClose: !1,
        background: !0,
        backgroundClassName: "dt-button-background",
        closeButton: !0,
        contentClassName: e.dom.collection.className,
        collectionLayout: "",
        collectionTitle: "",
        dropup: !1,
        fade: 400,
        popoverTitle: "",
        rightAlignClassName: "dt-button-right",
        tag: e.dom.collection.tag
      }, c),
          g = b.node(),
          l = function l() {
        h = !0;
        J(d(".dt-button-collection"), f.fade, function () {
          d(this).detach();
        });
        d(b.buttons('[aria-haspopup="true"][aria-expanded="true"]').nodes()).attr("aria-expanded", "false");
        d("div.dt-button-background").off("click.dtb-collection");
        x.background(!1, f.backgroundClassName, f.fade, g);
        d(z).off("resize.resize.dtb-collection");
        d("body").off(".dtb-collection");
        b.off("buttons-action.b-internal");
        b.off("destroy");
      };

      if (!1 === a) l();else {
        c = d(b.buttons('[aria-haspopup="true"][aria-expanded="true"]').nodes());
        c.length && (g.closest("div.dt-button-collection").length && (g = c.eq(0)), l());
        c = d(".dt-button", a).length;
        e = "";
        3 === c ? e = "dtb-b3" : 2 === c ? e = "dtb-b2" : 1 === c && (e = "dtb-b1");
        var m = d("<div/>").addClass("dt-button-collection").addClass(f.collectionLayout).addClass(f.splitAlignClass).addClass(e).css("display", "none");
        a = d(a).addClass(f.contentClassName).attr("role", "menu").appendTo(m);
        g.attr("aria-expanded", "true");
        g.parents("body")[0] !== B.body && (g = B.body.lastChild);
        f.popoverTitle ? m.prepend('<div class="dt-button-collection-title">' + f.popoverTitle + "</div>") : f.collectionTitle && m.prepend('<div class="dt-button-collection-title">' + f.collectionTitle + "</div>");
        f.closeButton && m.prepend('<div class="dtb-popover-close">x</div>').addClass("dtb-collection-closeable");
        I(m.insertAfter(g), f.fade);
        c = d(b.table().container());
        var r = m.css("position");
        if ("container" === f.span || "dt-container" === f.align) g = g.parent(), m.css("width", c.width());

        if ("absolute" === r) {
          var q = d(g[0].offsetParent);
          c = g.position();
          e = g.offset();
          var n = q.offset(),
              k = q.position(),
              t = z.getComputedStyle(q[0]);
          n.height = q.outerHeight();
          n.width = q.width() + parseFloat(t.paddingLeft);
          n.right = n.left + n.width;
          n.bottom = n.top + n.height;
          q = c.top + g.outerHeight();
          var y = c.left;
          m.css({
            top: q,
            left: y
          });
          t = z.getComputedStyle(m[0]);
          var v = m.offset();
          v.height = m.outerHeight();
          v.width = m.outerWidth();
          v.right = v.left + v.width;
          v.bottom = v.top + v.height;
          v.marginTop = parseFloat(t.marginTop);
          v.marginBottom = parseFloat(t.marginBottom);
          f.dropup && (q = c.top - v.height - v.marginTop - v.marginBottom);
          if ("button-right" === f.align || m.hasClass(f.rightAlignClassName)) y = c.left - v.width + g.outerWidth();
          if ("dt-container" === f.align || "container" === f.align) y < c.left && (y = -c.left), y + v.width > n.width && (y = n.width - v.width);
          k.left + y + v.width > d(z).width() && (y = d(z).width() - v.width - k.left);
          0 > e.left + y && (y = -e.left);
          k.top + q + v.height > d(z).height() + d(z).scrollTop() && (q = c.top - v.height - v.marginTop - v.marginBottom);
          k.top + q < d(z).scrollTop() && (q = c.top + g.outerHeight());
          m.css({
            top: q,
            left: y
          });
        } else r = function r() {
          var E = d(z).height() / 2,
              A = m.height() / 2;
          A > E && (A = E);
          m.css("marginTop", -1 * A);
        }, r(), d(z).on("resize.dtb-collection", function () {
          r();
        });

        f.background && x.background(!0, f.backgroundClassName, f.fade, f.backgroundHost || g);
        d("div.dt-button-background").on("click.dtb-collection", function () {});
        f.autoClose && setTimeout(function () {
          b.on("buttons-action.b-internal", function (E, A, w, D) {
            D[0] !== g[0] && l();
          });
        }, 0);
        d(m).trigger("buttons-popover.dt");
        b.on("destroy", l);
        setTimeout(function () {
          h = !1;
          d("body").on("click.dtb-collection", function (E) {
            if (!h) {
              var A = d.fn.addBack ? "addBack" : "andSelf",
                  w = d(E.target).parent()[0];
              (!d(E.target).parents()[A]().filter(a).length && !d(w).hasClass("dt-buttons") || d(E.target).hasClass("dt-button-background")) && l();
            }
          }).on("keyup.dtb-collection", function (E) {
            27 === E.keyCode && l();
          });
        }, 0);
      }
    }
  });

  x.background = function (a, b, c, e) {
    c === p && (c = 400);
    e || (e = B.body);
    a ? I(d("<div/>").addClass(b).css("display", "none").insertAfter(e), c) : J(d("div." + b), c, function () {
      d(this).removeClass(b).remove();
    });
  };

  x.instanceSelector = function (a, b) {
    if (a === p || null === a) return d.map(b, function (f) {
      return f.inst;
    });

    var c = [],
        e = d.map(b, function (f) {
      return f.name;
    }),
        h = function h(f) {
      if (Array.isArray(f)) for (var g = 0, l = f.length; g < l; g++) {
        h(f[g]);
      } else "string" === typeof f ? -1 !== f.indexOf(",") ? h(f.split(",")) : (f = d.inArray(f.trim(), e), -1 !== f && c.push(b[f].inst)) : "number" === typeof f ? c.push(b[f].inst) : "object" === _typeof(f) && c.push(f);
    };

    h(a);
    return c;
  };

  x.buttonSelector = function (a, b) {
    for (var c = [], e = function e(l, m, r) {
      for (var q, n, k = 0, t = m.length; k < t; k++) {
        if (q = m[k]) n = r !== p ? r + k : k + "", l.push({
          node: q.node,
          name: q.conf.name,
          idx: n
        }), q.buttons && e(l, q.buttons, n + "-");
      }
    }, h = function h(l, m) {
      var r,
          q = [];
      e(q, m.s.buttons);
      var n = d.map(q, function (k) {
        return k.node;
      });
      if (Array.isArray(l) || l instanceof d) for (n = 0, r = l.length; n < r; n++) {
        h(l[n], m);
      } else if (null === l || l === p || "*" === l) for (n = 0, r = q.length; n < r; n++) {
        c.push({
          inst: m,
          node: q[n].node
        });
      } else if ("number" === typeof l) m.s.buttons[l] && c.push({
        inst: m,
        node: m.s.buttons[l].node
      });else if ("string" === typeof l) {
        if (-1 !== l.indexOf(",")) for (q = l.split(","), n = 0, r = q.length; n < r; n++) {
          h(q[n].trim(), m);
        } else if (l.match(/^\d+(\-\d+)*$/)) n = d.map(q, function (k) {
          return k.idx;
        }), c.push({
          inst: m,
          node: q[d.inArray(l, n)].node
        });else if (-1 !== l.indexOf(":name")) for (l = l.replace(":name", ""), n = 0, r = q.length; n < r; n++) {
          q[n].name === l && c.push({
            inst: m,
            node: q[n].node
          });
        } else d(n).filter(l).each(function () {
          c.push({
            inst: m,
            node: this
          });
        });
      } else "object" === _typeof(l) && l.nodeName && (q = d.inArray(l, n), -1 !== q && c.push({
        inst: m,
        node: n[q]
      }));
    }, f = 0, g = a.length; f < g; f++) {
      h(b, a[f]);
    }

    return c;
  };

  x.stripData = function (a, b) {
    if ("string" !== typeof a) return a;
    a = a.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, "");
    a = a.replace(/<!\-\-.*?\-\->/g, "");
    if (!b || b.stripHtml) a = a.replace(/<[^>]*>/g, "");
    if (!b || b.trim) a = a.replace(/^\s+|\s+$/g, "");
    if (!b || b.stripNewlines) a = a.replace(/\n/g, " ");
    if (!b || b.decodeEntities) M.innerHTML = a, a = M.value;
    return a;
  };

  x.defaults = {
    buttons: ["copy", "excel", "csv", "pdf", "print"],
    name: "main",
    tabIndex: 0,
    dom: {
      container: {
        tag: "div",
        className: "dt-buttons"
      },
      collection: {
        tag: "div",
        className: ""
      },
      button: {
        tag: "button",
        className: "dt-button",
        active: "active",
        disabled: "disabled",
        spacerClass: ""
      },
      buttonLiner: {
        tag: "span",
        className: ""
      },
      split: {
        tag: "div",
        className: "dt-button-split"
      },
      splitWrapper: {
        tag: "div",
        className: "dt-btn-split-wrapper"
      },
      splitDropdown: {
        tag: "button",
        text: "&#x25BC;",
        className: "dt-btn-split-drop",
        align: "split-right",
        splitAlignClass: "dt-button-split-left"
      },
      splitDropdownButton: {
        tag: "button",
        className: "dt-btn-split-drop-button dt-button"
      },
      splitCollection: {
        tag: "div",
        className: "dt-button-split-collection"
      }
    }
  };
  x.version = "2.2.2";
  d.extend(C, {
    collection: {
      text: function text(a) {
        return a.i18n("buttons.collection", "Collection");
      },
      className: "buttons-collection",
      closeButton: !1,
      init: function init(a, b, c) {
        b.attr("aria-expanded", !1);
      },
      action: function action(a, b, c, e) {
        e._collection.parents("body").length ? this.popover(!1, e) : this.popover(e._collection, e);
      },
      attr: {
        "aria-haspopup": !0
      }
    },
    split: {
      text: function text(a) {
        return a.i18n("buttons.split", "Split");
      },
      className: "buttons-split",
      closeButton: !1,
      init: function init(a, b, c) {
        return b.attr("aria-expanded", !1);
      },
      action: function action(a, b, c, e) {
        this.popover(e._collection, e);
      },
      attr: {
        "aria-haspopup": !0
      }
    },
    copy: function copy(a, b) {
      if (C.copyHtml5) return "copyHtml5";
    },
    csv: function csv(a, b) {
      if (C.csvHtml5 && C.csvHtml5.available(a, b)) return "csvHtml5";
    },
    excel: function excel(a, b) {
      if (C.excelHtml5 && C.excelHtml5.available(a, b)) return "excelHtml5";
    },
    pdf: function pdf(a, b) {
      if (C.pdfHtml5 && C.pdfHtml5.available(a, b)) return "pdfHtml5";
    },
    pageLength: function pageLength(a) {
      a = a.settings()[0].aLengthMenu;
      var b = [],
          c = [];
      if (Array.isArray(a[0])) b = a[0], c = a[1];else for (var e = 0; e < a.length; e++) {
        var h = a[e];
        d.isPlainObject(h) ? (b.push(h.value), c.push(h.label)) : (b.push(h), c.push(h));
      }
      return {
        extend: "collection",
        text: function text(f) {
          return f.i18n("buttons.pageLength", {
            "-1": "Show all rows",
            _: "Show %d rows"
          }, f.page.len());
        },
        className: "buttons-page-length",
        autoClose: !0,
        buttons: d.map(b, function (f, g) {
          return {
            text: c[g],
            className: "button-page-length",
            action: function action(l, m) {
              m.page.len(f).draw();
            },
            init: function init(l, m, r) {
              var q = this;

              m = function m() {
                q.active(l.page.len() === f);
              };

              l.on("length.dt" + r.namespace, m);
              m();
            },
            destroy: function destroy(l, m, r) {
              l.off("length.dt" + r.namespace);
            }
          };
        }),
        init: function init(f, g, l) {
          var m = this;
          f.on("length.dt" + l.namespace, function () {
            m.text(l.text);
          });
        },
        destroy: function destroy(f, g, l) {
          f.off("length.dt" + l.namespace);
        }
      };
    },
    spacer: {
      style: "empty",
      spacer: !0,
      text: function text(a) {
        return a.i18n("buttons.spacer", "");
      }
    }
  });
  u.Api.register("buttons()", function (a, b) {
    b === p && (b = a, a = p);
    this.selector.buttonGroup = a;
    var c = this.iterator(!0, "table", function (e) {
      if (e._buttons) return x.buttonSelector(x.instanceSelector(a, e._buttons), b);
    }, !0);
    c._groupSelector = a;
    return c;
  });
  u.Api.register("button()", function (a, b) {
    a = this.buttons(a, b);
    1 < a.length && a.splice(1, a.length);
    return a;
  });
  u.Api.registerPlural("buttons().active()", "button().active()", function (a) {
    return a === p ? this.map(function (b) {
      return b.inst.active(b.node);
    }) : this.each(function (b) {
      b.inst.active(b.node, a);
    });
  });
  u.Api.registerPlural("buttons().action()", "button().action()", function (a) {
    return a === p ? this.map(function (b) {
      return b.inst.action(b.node);
    }) : this.each(function (b) {
      b.inst.action(b.node, a);
    });
  });
  u.Api.registerPlural("buttons().collectionRebuild()", "button().collectionRebuild()", function (a) {
    return this.each(function (b) {
      for (var c = 0; c < a.length; c++) {
        "object" === _typeof(a[c]) && (a[c].parentConf = b);
      }

      b.inst.collectionRebuild(b.node, a);
    });
  });
  u.Api.register(["buttons().enable()", "button().enable()"], function (a) {
    return this.each(function (b) {
      b.inst.enable(b.node, a);
    });
  });
  u.Api.register(["buttons().disable()", "button().disable()"], function () {
    return this.each(function (a) {
      a.inst.disable(a.node);
    });
  });
  u.Api.register("button().index()", function () {
    var a = null;
    this.each(function (b) {
      b = b.inst.index(b.node);
      null !== b && (a = b);
    });
    return a;
  });
  u.Api.registerPlural("buttons().nodes()", "button().node()", function () {
    var a = d();
    d(this.each(function (b) {
      a = a.add(b.inst.node(b.node));
    }));
    return a;
  });
  u.Api.registerPlural("buttons().processing()", "button().processing()", function (a) {
    return a === p ? this.map(function (b) {
      return b.inst.processing(b.node);
    }) : this.each(function (b) {
      b.inst.processing(b.node, a);
    });
  });
  u.Api.registerPlural("buttons().text()", "button().text()", function (a) {
    return a === p ? this.map(function (b) {
      return b.inst.text(b.node);
    }) : this.each(function (b) {
      b.inst.text(b.node, a);
    });
  });
  u.Api.registerPlural("buttons().trigger()", "button().trigger()", function () {
    return this.each(function (a) {
      a.inst.node(a.node).trigger("click");
    });
  });
  u.Api.register("button().popover()", function (a, b) {
    return this.map(function (c) {
      return c.inst._popover(a, this.button(this[0].node), b);
    });
  });
  u.Api.register("buttons().containers()", function () {
    var a = d(),
        b = this._groupSelector;
    this.iterator(!0, "table", function (c) {
      if (c._buttons) {
        c = x.instanceSelector(b, c._buttons);

        for (var e = 0, h = c.length; e < h; e++) {
          a = a.add(c[e].container());
        }
      }
    });
    return a;
  });
  u.Api.register("buttons().container()", function () {
    return this.containers().eq(0);
  });
  u.Api.register("button().add()", function (a, b, c) {
    var e = this.context;
    e.length && (e = x.instanceSelector(this._groupSelector, e[0]._buttons), e.length && e[0].add(b, a, c));
    return this.button(this._groupSelector, a);
  });
  u.Api.register("buttons().destroy()", function () {
    this.pluck("inst").unique().each(function (a) {
      a.destroy();
    });
    return this;
  });
  u.Api.registerPlural("buttons().remove()", "buttons().remove()", function () {
    this.each(function (a) {
      a.inst.remove(a.node);
    });
    return this;
  });
  var H;
  u.Api.register("buttons.info()", function (a, b, c) {
    var e = this;
    if (!1 === a) return this.off("destroy.btn-info"), J(d("#datatables_buttons_info"), 400, function () {
      d(this).remove();
    }), clearTimeout(H), H = null, this;
    H && clearTimeout(H);
    d("#datatables_buttons_info").length && d("#datatables_buttons_info").remove();
    a = a ? "<h2>" + a + "</h2>" : "";
    I(d('<div id="datatables_buttons_info" class="dt-button-info"/>').html(a).append(d("<div/>")["string" === typeof b ? "html" : "append"](b)).css("display", "none").appendTo("body"));
    c !== p && 0 !== c && (H = setTimeout(function () {
      e.buttons.info(!1);
    }, c));
    this.on("destroy.btn-info", function () {
      e.buttons.info(!1);
    });
    return this;
  });
  u.Api.register("buttons.exportData()", function (a) {
    if (this.context.length) return Q(new u.Api(this.context[0]), a);
  });
  u.Api.register("buttons.exportInfo()", function (a) {
    a || (a = {});
    var b = a;
    var c = "*" === b.filename && "*" !== b.title && b.title !== p && null !== b.title && "" !== b.title ? b.title : b.filename;
    "function" === typeof c && (c = c());
    c === p || null === c ? c = null : (-1 !== c.indexOf("*") && (c = c.replace("*", d("head > title").text()).trim()), c = c.replace(/[^a-zA-Z0-9_\u00A1-\uFFFF\.,\-_ !\(\)]/g, ""), (b = K(b.extension)) || (b = ""), c += b);
    b = K(a.title);
    b = null === b ? null : -1 !== b.indexOf("*") ? b.replace("*", d("head > title").text() || "Exported data") : b;
    return {
      filename: c,
      title: b,
      messageTop: N(this, a.message || a.messageTop, "top"),
      messageBottom: N(this, a.messageBottom, "bottom")
    };
  });

  var K = function K(a) {
    return null === a || a === p ? null : "function" === typeof a ? a() : a;
  },
      N = function N(a, b, c) {
    b = K(b);
    if (null === b) return null;
    a = d("caption", a.table().container()).eq(0);
    return "*" === b ? a.css("caption-side") !== c ? null : a.length ? a.text() : "" : b;
  },
      M = d("<textarea/>")[0],
      Q = function Q(a, b) {
    var c = d.extend(!0, {}, {
      rows: null,
      columns: "",
      modifier: {
        search: "applied",
        order: "applied"
      },
      orthogonal: "display",
      stripHtml: !0,
      stripNewlines: !0,
      decodeEntities: !0,
      trim: !0,
      format: {
        header: function header(t) {
          return x.stripData(t, c);
        },
        footer: function footer(t) {
          return x.stripData(t, c);
        },
        body: function body(t) {
          return x.stripData(t, c);
        }
      },
      customizeData: null
    }, b);
    b = a.columns(c.columns).indexes().map(function (t) {
      var y = a.column(t).header();
      return c.format.header(y.innerHTML, t, y);
    }).toArray();
    var e = a.table().footer() ? a.columns(c.columns).indexes().map(function (t) {
      var y = a.column(t).footer();
      return c.format.footer(y ? y.innerHTML : "", t, y);
    }).toArray() : null,
        h = d.extend({}, c.modifier);
    a.select && "function" === typeof a.select.info && h.selected === p && a.rows(c.rows, d.extend({
      selected: !0
    }, h)).any() && d.extend(h, {
      selected: !0
    });
    h = a.rows(c.rows, h).indexes().toArray();
    var f = a.cells(h, c.columns);
    h = f.render(c.orthogonal).toArray();
    f = f.nodes().toArray();

    for (var g = b.length, l = [], m = 0, r = 0, q = 0 < g ? h.length / g : 0; r < q; r++) {
      for (var n = [g], k = 0; k < g; k++) {
        n[k] = c.format.body(h[m], r, k, f[m]), m++;
      }

      l[r] = n;
    }

    b = {
      header: b,
      footer: e,
      body: l
    };
    c.customizeData && c.customizeData(b);
    return b;
  };

  d.fn.dataTable.Buttons = x;
  d.fn.DataTable.Buttons = x;
  d(B).on("init.dt plugin-init.dt", function (a, b) {
    "dt" === a.namespace && (a = b.oInit.buttons || u.defaults.buttons) && !b._buttons && new x(b, a).container();
  });
  u.ext.feature.push({
    fnInit: L,
    cFeature: "B"
  });
  u.ext.features && u.ext.features.register("buttons", L);
  return x;
});
/*!
 DataTables styling wrapper for Buttons
 ©2018 SpryMedia Ltd - datatables.net/license
*/


(function (c) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net-dt", "datatables.net-buttons"], function (a) {
    return c(a, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (a, b) {
    a || (a = window);
    b && b.fn.dataTable || (b = require("datatables.net-dt")(a, b).$);
    b.fn.dataTable.Buttons || require("datatables.net-buttons")(a, b);
    return c(b, a, a.document);
  } : c(jQuery, window, document);
})(function (c, a, b, d) {
  return c.fn.dataTable;
});
/*!
 Column visibility buttons for Buttons and DataTables.
 2016 SpryMedia Ltd - datatables.net/license
*/


(function (h) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net", "datatables.net-buttons"], function (e) {
    return h(e, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (e, g) {
    e || (e = window);
    g && g.fn.dataTable || (g = require("datatables.net")(e, g).$);
    g.fn.dataTable.Buttons || require("datatables.net-buttons")(e, g);
    return h(g, e, e.document);
  } : h(jQuery, window, document);
})(function (h, e, g, l) {
  e = h.fn.dataTable;
  h.extend(e.ext.buttons, {
    colvis: function colvis(b, a) {
      var c = null,
          d = {
        extend: "collection",
        init: function init(f, k) {
          c = k;
        },
        text: function text(f) {
          return f.i18n("buttons.colvis", "Column visibility");
        },
        className: "buttons-colvis",
        closeButton: !1,
        buttons: [{
          extend: "columnsToggle",
          columns: a.columns,
          columnText: a.columnText
        }]
      };
      b.on("column-reorder.dt" + a.namespace, function (f, k, m) {
        b.button(null, b.button(null, c).node()).collectionRebuild([{
          extend: "columnsToggle",
          columns: a.columns,
          columnText: a.columnText
        }]);
      });
      return d;
    },
    columnsToggle: function columnsToggle(b, a) {
      return b.columns(a.columns).indexes().map(function (c) {
        return {
          extend: "columnToggle",
          columns: c,
          columnText: a.columnText
        };
      }).toArray();
    },
    columnToggle: function columnToggle(b, a) {
      return {
        extend: "columnVisibility",
        columns: a.columns,
        columnText: a.columnText
      };
    },
    columnsVisibility: function columnsVisibility(b, a) {
      return b.columns(a.columns).indexes().map(function (c) {
        return {
          extend: "columnVisibility",
          columns: c,
          visibility: a.visibility,
          columnText: a.columnText
        };
      }).toArray();
    },
    columnVisibility: {
      columns: l,
      text: function text(b, a, c) {
        return c._columnText(b, c);
      },
      className: "buttons-columnVisibility",
      action: function action(b, a, c, d) {
        b = a.columns(d.columns);
        a = b.visible();
        b.visible(d.visibility !== l ? d.visibility : !(a.length && a[0]));
      },
      init: function init(b, a, c) {
        var d = this;
        a.attr("data-cv-idx", c.columns);
        b.on("column-visibility.dt" + c.namespace, function (f, k) {
          k.bDestroying || k.nTable != b.settings()[0].nTable || d.active(b.column(c.columns).visible());
        }).on("column-reorder.dt" + c.namespace, function (f, k, m) {
          c.destroying || 1 !== b.columns(c.columns).count() || (d.text(c._columnText(b, c)), d.active(b.column(c.columns).visible()));
        });
        this.active(b.column(c.columns).visible());
      },
      destroy: function destroy(b, a, c) {
        b.off("column-visibility.dt" + c.namespace).off("column-reorder.dt" + c.namespace);
      },
      _columnText: function _columnText(b, a) {
        var c = b.column(a.columns).index(),
            d = b.settings()[0].aoColumns[c].sTitle;
        d || (d = b.column(c).header().innerHTML);
        d = d.replace(/\n/g, " ").replace(/<br\s*\/?>/gi, " ").replace(/<select(.*?)<\/select>/g, "").replace(/<!\-\-.*?\-\->/g, "").replace(/<.*?>/g, "").replace(/^\s+|\s+$/g, "");
        return a.columnText ? a.columnText(b, c, d) : d;
      }
    },
    colvisRestore: {
      className: "buttons-colvisRestore",
      text: function text(b) {
        return b.i18n("buttons.colvisRestore", "Restore visibility");
      },
      init: function init(b, a, c) {
        c._visOriginal = b.columns().indexes().map(function (d) {
          return b.column(d).visible();
        }).toArray();
      },
      action: function action(b, a, c, d) {
        a.columns().every(function (f) {
          f = a.colReorder && a.colReorder.transpose ? a.colReorder.transpose(f, "toOriginal") : f;
          this.visible(d._visOriginal[f]);
        });
      }
    },
    colvisGroup: {
      className: "buttons-colvisGroup",
      action: function action(b, a, c, d) {
        a.columns(d.show).visible(!0, !1);
        a.columns(d.hide).visible(!1, !1);
        a.columns.adjust();
      },
      show: [],
      hide: []
    }
  });
  return e.Buttons;
});
/*!
 HTML5 export buttons for Buttons and DataTables.
 2016 SpryMedia Ltd - datatables.net/license

 FileSaver.js (1.3.3) - MIT license
 Copyright © 2016 Eli Grey - http://eligrey.com
*/


(function (n) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net", "datatables.net-buttons"], function (u) {
    return n(u, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (u, x, E, F) {
    u || (u = window);
    x && x.fn.dataTable || (x = require("datatables.net")(u, x).$);
    x.fn.dataTable.Buttons || require("datatables.net-buttons")(u, x);
    return n(x, u, u.document, E, F);
  } : n(jQuery, window, document);
})(function (n, u, x, E, F, B) {
  function I(a) {
    for (var c = ""; 0 <= a;) {
      c = String.fromCharCode(a % 26 + 65) + c, a = Math.floor(a / 26) - 1;
    }

    return c;
  }

  function O(a, c) {
    J === B && (J = -1 === M.serializeToString(new u.DOMParser().parseFromString(P["xl/worksheets/sheet1.xml"], "text/xml")).indexOf("xmlns:r"));
    n.each(c, function (d, b) {
      if (n.isPlainObject(b)) d = a.folder(d), O(d, b);else {
        if (J) {
          var m = b.childNodes[0],
              e,
              f = [];

          for (e = m.attributes.length - 1; 0 <= e; e--) {
            var g = m.attributes[e].nodeName;
            var p = m.attributes[e].nodeValue;
            -1 !== g.indexOf(":") && (f.push({
              name: g,
              value: p
            }), m.removeAttribute(g));
          }

          e = 0;

          for (g = f.length; e < g; e++) {
            p = b.createAttribute(f[e].name.replace(":", "_dt_b_namespace_token_")), p.value = f[e].value, m.setAttributeNode(p);
          }
        }

        b = M.serializeToString(b);
        J && (-1 === b.indexOf("<?xml") && (b = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' + b), b = b.replace(/_dt_b_namespace_token_/g, ":"), b = b.replace(/xmlns:NS[\d]+="" NS[\d]+:/g, ""));
        b = b.replace(/<([^<>]*?) xmlns=""([^<>]*?)>/g, "<$1 $2>");
        a.file(d, b);
      }
    });
  }

  function y(a, c, d) {
    var b = a.createElement(c);
    d && (d.attr && n(b).attr(d.attr), d.children && n.each(d.children, function (m, e) {
      b.appendChild(e);
    }), null !== d.text && d.text !== B && b.appendChild(a.createTextNode(d.text)));
    return b;
  }

  function V(a, c) {
    var d = a.header[c].length;
    a.footer && a.footer[c].length > d && (d = a.footer[c].length);

    for (var b = 0, m = a.body.length; b < m; b++) {
      var e = a.body[b][c];
      e = null !== e && e !== B ? e.toString() : "";
      -1 !== e.indexOf("\n") ? (e = e.split("\n"), e.sort(function (f, g) {
        return g.length - f.length;
      }), e = e[0].length) : e = e.length;
      e > d && (d = e);
      if (40 < d) return 54;
    }

    d *= 1.35;
    return 6 < d ? d : 6;
  }

  var D = n.fn.dataTable;

  D.Buttons.pdfMake = function (a) {
    if (!a) return F || u.pdfMake;
    F = a;
  };

  D.Buttons.jszip = function (a) {
    if (!a) return E || u.JSZip;
    E = a;
  };

  var K = function (a) {
    if (!("undefined" === typeof a || "undefined" !== typeof navigator && /MSIE [1-9]\./.test(navigator.userAgent))) {
      var c = a.document.createElementNS("http://www.w3.org/1999/xhtml", "a"),
          d = ("download" in c),
          b = /constructor/i.test(a.HTMLElement) || a.safari,
          m = /CriOS\/[\d]+/.test(navigator.userAgent),
          e = function e(h) {
        (a.setImmediate || a.setTimeout)(function () {
          throw h;
        }, 0);
      },
          f = function f(h) {
        setTimeout(function () {
          "string" === typeof h ? (a.URL || a.webkitURL || a).revokeObjectURL(h) : h.remove();
        }, 4E4);
      },
          g = function g(h) {
        return /^\s*(?:text\/\S*|application\/xml|\S*\/\S*\+xml)\s*;.*charset\s*=\s*utf-8/i.test(h.type) ? new Blob([String.fromCharCode(65279), h], {
          type: h.type
        }) : h;
      },
          p = function p(h, q, v) {
        v || (h = g(h));

        var r = this,
            w = "application/octet-stream" === h.type,
            C = function C() {
          var l = ["writestart", "progress", "write", "writeend"];
          l = [].concat(l);

          for (var z = l.length; z--;) {
            var G = r["on" + l[z]];
            if ("function" === typeof G) try {
              G.call(r, r);
            } catch (A) {
              e(A);
            }
          }
        };

        r.readyState = r.INIT;

        if (d) {
          var k = (a.URL || a.webkitURL || a).createObjectURL(h);
          setTimeout(function () {
            c.href = k;
            c.download = q;
            var l = new MouseEvent("click");
            c.dispatchEvent(l);
            C();
            f(k);
            r.readyState = r.DONE;
          });
        } else (function () {
          if ((m || w && b) && a.FileReader) {
            var l = new FileReader();

            l.onloadend = function () {
              var z = m ? l.result : l.result.replace(/^data:[^;]*;/, "data:attachment/file;");
              a.open(z, "_blank") || (a.location.href = z);
              r.readyState = r.DONE;
              C();
            };

            l.readAsDataURL(h);
            r.readyState = r.INIT;
          } else k || (k = (a.URL || a.webkitURL || a).createObjectURL(h)), w ? a.location.href = k : a.open(k, "_blank") || (a.location.href = k), r.readyState = r.DONE, C(), f(k);
        })();
      },
          t = p.prototype;

      if ("undefined" !== typeof navigator && navigator.msSaveOrOpenBlob) return function (h, q, v) {
        q = q || h.name || "download";
        v || (h = g(h));
        return navigator.msSaveOrOpenBlob(h, q);
      };

      t.abort = function () {};

      t.readyState = t.INIT = 0;
      t.WRITING = 1;
      t.DONE = 2;
      t.error = t.onwritestart = t.onprogress = t.onwrite = t.onabort = t.onerror = t.onwriteend = null;
      return function (h, q, v) {
        return new p(h, q || h.name || "download", v);
      };
    }
  }("undefined" !== typeof self && self || "undefined" !== typeof u && u || this.content);

  D.fileSave = K;

  var Q = function Q(a) {
    var c = "Sheet1";
    a.sheetName && (c = a.sheetName.replace(/[\[\]\*\/\\\?:]/g, ""));
    return c;
  },
      R = function R(a) {
    return a.newline ? a.newline : navigator.userAgent.match(/Windows/) ? "\r\n" : "\n";
  },
      S = function S(a, c) {
    var d = R(c);
    a = a.buttons.exportData(c.exportOptions);

    var b = c.fieldBoundary,
        m = c.fieldSeparator,
        e = new RegExp(b, "g"),
        f = c.escapeChar !== B ? c.escapeChar : "\\",
        g = function g(v) {
      for (var r = "", w = 0, C = v.length; w < C; w++) {
        0 < w && (r += m), r += b ? b + ("" + v[w]).replace(e, f + b) + b : v[w];
      }

      return r;
    },
        p = c.header ? g(a.header) + d : "";

    c = c.footer && a.footer ? d + g(a.footer) : "";

    for (var t = [], h = 0, q = a.body.length; h < q; h++) {
      t.push(g(a.body[h]));
    }

    return {
      str: p + t.join(d) + c,
      rows: t.length
    };
  },
      T = function T() {
    if (-1 === navigator.userAgent.indexOf("Safari") || -1 !== navigator.userAgent.indexOf("Chrome") || -1 !== navigator.userAgent.indexOf("Opera")) return !1;
    var a = navigator.userAgent.match(/AppleWebKit\/(\d+\.\d+)/);
    return a && 1 < a.length && 603.1 > 1 * a[1] ? !0 : !1;
  };

  try {
    var M = new XMLSerializer(),
        J;
  } catch (a) {}

  var P = {
    "_rels/.rels": '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
    "xl/_rels/workbook.xml.rels": '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
    "[Content_Types].xml": '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="xml" ContentType="application/xml" /><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml" /><Default Extension="jpeg" ContentType="image/jpeg" /><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml" /><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml" /><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml" /></Types>',
    "xl/workbook.xml": '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><fileVersion appName="xl" lastEdited="5" lowestEdited="5" rupBuild="24816"/><workbookPr showInkAnnotation="0" autoCompressPictures="0"/><bookViews><workbookView xWindow="0" yWindow="0" windowWidth="25600" windowHeight="19020" tabRatio="500"/></bookViews><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets><definedNames/></workbook>',
    "xl/worksheets/sheet1.xml": '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" mc:Ignorable="x14ac" xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac"><sheetData/><mergeCells count="0"/></worksheet>',
    "xl/styles.xml": '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" mc:Ignorable="x14ac" xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac"><numFmts count="6"><numFmt numFmtId="164" formatCode="#,##0.00_- [$$-45C]"/><numFmt numFmtId="165" formatCode="&quot;£&quot;#,##0.00"/><numFmt numFmtId="166" formatCode="[$€-2] #,##0.00"/><numFmt numFmtId="167" formatCode="0.0%"/><numFmt numFmtId="168" formatCode="#,##0;(#,##0)"/><numFmt numFmtId="169" formatCode="#,##0.00;(#,##0.00)"/></numFmts><fonts count="5" x14ac:knownFonts="1"><font><sz val="11" /><name val="Calibri" /></font><font><sz val="11" /><name val="Calibri" /><color rgb="FFFFFFFF" /></font><font><sz val="11" /><name val="Calibri" /><b /></font><font><sz val="11" /><name val="Calibri" /><i /></font><font><sz val="11" /><name val="Calibri" /><u /></font></fonts><fills count="6"><fill><patternFill patternType="none" /></fill><fill><patternFill patternType="none" /></fill><fill><patternFill patternType="solid"><fgColor rgb="FFD9D9D9" /><bgColor indexed="64" /></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFD99795" /><bgColor indexed="64" /></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="ffc6efce" /><bgColor indexed="64" /></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="ffc6cfef" /><bgColor indexed="64" /></patternFill></fill></fills><borders count="2"><border><left /><right /><top /><bottom /><diagonal /></border><border diagonalUp="false" diagonalDown="false"><left style="thin"><color auto="1" /></left><right style="thin"><color auto="1" /></right><top style="thin"><color auto="1" /></top><bottom style="thin"><color auto="1" /></bottom><diagonal /></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" /></cellStyleXfs><cellXfs count="68"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="3" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="3" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="3" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="3" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="3" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment horizontal="left"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment horizontal="center"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment horizontal="right"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment horizontal="fill"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment textRotation="90"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment wrapText="1"/></xf><xf numFmtId="9"   fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="164" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="165" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="166" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="167" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="168" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="169" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="3" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="4" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="1" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="2" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="14" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0" /></cellStyles><dxfs count="0" /><tableStyles count="0" defaultTableStyle="TableStyleMedium9" defaultPivotStyle="PivotStyleMedium4" /></styleSheet>'
  },
      U = [{
    match: /^\-?\d+\.\d%$/,
    style: 60,
    fmt: function fmt(a) {
      return a / 100;
    }
  }, {
    match: /^\-?\d+\.?\d*%$/,
    style: 56,
    fmt: function fmt(a) {
      return a / 100;
    }
  }, {
    match: /^\-?\$[\d,]+.?\d*$/,
    style: 57
  }, {
    match: /^\-?£[\d,]+.?\d*$/,
    style: 58
  }, {
    match: /^\-?€[\d,]+.?\d*$/,
    style: 59
  }, {
    match: /^\-?\d+$/,
    style: 65
  }, {
    match: /^\-?\d+\.\d{2}$/,
    style: 66
  }, {
    match: /^\([\d,]+\)$/,
    style: 61,
    fmt: function fmt(a) {
      return -1 * a.replace(/[\(\)]/g, "");
    }
  }, {
    match: /^\([\d,]+\.\d{2}\)$/,
    style: 62,
    fmt: function fmt(a) {
      return -1 * a.replace(/[\(\)]/g, "");
    }
  }, {
    match: /^\-?[\d,]+$/,
    style: 63
  }, {
    match: /^\-?[\d,]+\.\d{2}$/,
    style: 64
  }, {
    match: /^[\d]{4}\-[\d]{2}\-[\d]{2}$/,
    style: 67,
    fmt: function fmt(a) {
      return Math.round(25569 + Date.parse(a) / 864E5);
    }
  }];
  D.ext.buttons.copyHtml5 = {
    className: "buttons-copy buttons-html5",
    text: function text(a) {
      return a.i18n("buttons.copy", "Copy");
    },
    action: function action(a, c, d, b) {
      this.processing(!0);
      var m = this;
      a = S(c, b);
      var e = c.buttons.exportInfo(b),
          f = R(b),
          g = a.str;
      d = n("<div/>").css({
        height: 1,
        width: 1,
        overflow: "hidden",
        position: "fixed",
        top: 0,
        left: 0
      });
      e.title && (g = e.title + f + f + g);
      e.messageTop && (g = e.messageTop + f + f + g);
      e.messageBottom && (g = g + f + f + e.messageBottom);
      b.customize && (g = b.customize(g, b, c));
      b = n("<textarea readonly/>").val(g).appendTo(d);

      if (x.queryCommandSupported("copy")) {
        d.appendTo(c.table().container());
        b[0].focus();
        b[0].select();

        try {
          var p = x.execCommand("copy");
          d.remove();

          if (p) {
            c.buttons.info(c.i18n("buttons.copyTitle", "Copy to clipboard"), c.i18n("buttons.copySuccess", {
              1: "Copied one row to clipboard",
              _: "Copied %d rows to clipboard"
            }, a.rows), 2E3);
            this.processing(!1);
            return;
          }
        } catch (q) {}
      }

      p = n("<span>" + c.i18n("buttons.copyKeys", "Press <i>ctrl</i> or <i>⌘</i> + <i>C</i> to copy the table data<br>to your system clipboard.<br><br>To cancel, click this message or press escape.") + "</span>").append(d);
      c.buttons.info(c.i18n("buttons.copyTitle", "Copy to clipboard"), p, 0);
      b[0].focus();
      b[0].select();

      var t = n(p).closest(".dt-button-info"),
          h = function h() {
        t.off("click.buttons-copy");
        n(x).off(".buttons-copy");
        c.buttons.info(!1);
      };

      t.on("click.buttons-copy", h);
      n(x).on("keydown.buttons-copy", function (q) {
        27 === q.keyCode && (h(), m.processing(!1));
      }).on("copy.buttons-copy cut.buttons-copy", function () {
        h();
        m.processing(!1);
      });
    },
    exportOptions: {},
    fieldSeparator: "\t",
    fieldBoundary: "",
    header: !0,
    footer: !1,
    title: "*",
    messageTop: "*",
    messageBottom: "*"
  };
  D.ext.buttons.csvHtml5 = {
    bom: !1,
    className: "buttons-csv buttons-html5",
    available: function available() {
      return u.FileReader !== B && u.Blob;
    },
    text: function text(a) {
      return a.i18n("buttons.csv", "CSV");
    },
    action: function action(a, c, d, b) {
      this.processing(!0);
      a = S(c, b).str;
      d = c.buttons.exportInfo(b);
      var m = b.charset;
      b.customize && (a = b.customize(a, b, c));
      !1 !== m ? (m || (m = x.characterSet || x.charset), m && (m = ";charset=" + m)) : m = "";
      b.bom && (a = String.fromCharCode(65279) + a);
      K(new Blob([a], {
        type: "text/csv" + m
      }), d.filename, !0);
      this.processing(!1);
    },
    filename: "*",
    extension: ".csv",
    exportOptions: {},
    fieldSeparator: ",",
    fieldBoundary: '"',
    escapeChar: '"',
    charset: null,
    header: !0,
    footer: !1
  };
  D.ext.buttons.excelHtml5 = {
    className: "buttons-excel buttons-html5",
    available: function available() {
      return u.FileReader !== B && (E || u.JSZip) !== B && !T() && M;
    },
    text: function text(a) {
      return a.i18n("buttons.excel", "Excel");
    },
    action: function action(a, c, d, b) {
      this.processing(!0);
      var m = this,
          e = 0;

      a = function a(k) {
        return n.parseXML(P[k]);
      };

      var f = a("xl/worksheets/sheet1.xml"),
          g = f.getElementsByTagName("sheetData")[0];
      a = {
        _rels: {
          ".rels": a("_rels/.rels")
        },
        xl: {
          _rels: {
            "workbook.xml.rels": a("xl/_rels/workbook.xml.rels")
          },
          "workbook.xml": a("xl/workbook.xml"),
          "styles.xml": a("xl/styles.xml"),
          worksheets: {
            "sheet1.xml": f
          }
        },
        "[Content_Types].xml": a("[Content_Types].xml")
      };

      var p = c.buttons.exportData(b.exportOptions),
          t,
          h,
          q = function q(k) {
        t = e + 1;
        h = y(f, "row", {
          attr: {
            r: t
          }
        });

        for (var l = 0, z = k.length; l < z; l++) {
          var G = I(l) + "" + t,
              A = null;
          if (null === k[l] || k[l] === B || "" === k[l]) if (!0 === b.createEmptyCells) k[l] = "";else continue;
          var H = k[l];
          k[l] = "function" === typeof k[l].trim ? k[l].trim() : k[l];

          for (var N = 0, W = U.length; N < W; N++) {
            var L = U[N];

            if (k[l].match && !k[l].match(/^0\d+/) && k[l].match(L.match)) {
              A = k[l].replace(/[^\d\.\-]/g, "");
              L.fmt && (A = L.fmt(A));
              A = y(f, "c", {
                attr: {
                  r: G,
                  s: L.style
                },
                children: [y(f, "v", {
                  text: A
                })]
              });
              break;
            }
          }

          A || ("number" === typeof k[l] || k[l].match && k[l].match(/^-?\d+(\.\d+)?([eE]\-?\d+)?$/) && !k[l].match(/^0\d+/) ? A = y(f, "c", {
            attr: {
              t: "n",
              r: G
            },
            children: [y(f, "v", {
              text: k[l]
            })]
          }) : (H = H.replace ? H.replace(/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F-\x9F]/g, "") : H, A = y(f, "c", {
            attr: {
              t: "inlineStr",
              r: G
            },
            children: {
              row: y(f, "is", {
                children: {
                  row: y(f, "t", {
                    text: H,
                    attr: {
                      "xml:space": "preserve"
                    }
                  })
                }
              })
            }
          })));
          h.appendChild(A);
        }

        g.appendChild(h);
        e++;
      };

      b.customizeData && b.customizeData(p);

      var v = function v(k, l) {
        var z = n("mergeCells", f);
        z[0].appendChild(y(f, "mergeCell", {
          attr: {
            ref: "A" + k + ":" + I(l) + k
          }
        }));
        z.attr("count", parseFloat(z.attr("count")) + 1);
        n("row:eq(" + (k - 1) + ") c", f).attr("s", "51");
      },
          r = c.buttons.exportInfo(b);

      r.title && (q([r.title], e), v(e, p.header.length - 1));
      r.messageTop && (q([r.messageTop], e), v(e, p.header.length - 1));
      b.header && (q(p.header, e), n("row:last c", f).attr("s", "2"));
      d = e;
      var w = 0;

      for (var C = p.body.length; w < C; w++) {
        q(p.body[w], e);
      }

      w = e;
      b.footer && p.footer && (q(p.footer, e), n("row:last c", f).attr("s", "2"));
      r.messageBottom && (q([r.messageBottom], e), v(e, p.header.length - 1));
      q = y(f, "cols");
      n("worksheet", f).prepend(q);
      v = 0;

      for (C = p.header.length; v < C; v++) {
        q.appendChild(y(f, "col", {
          attr: {
            min: v + 1,
            max: v + 1,
            width: V(p, v),
            customWidth: 1
          }
        }));
      }

      q = a.xl["workbook.xml"];
      n("sheets sheet", q).attr("name", Q(b));
      b.autoFilter && (n("mergeCells", f).before(y(f, "autoFilter", {
        attr: {
          ref: "A" + d + ":" + I(p.header.length - 1) + w
        }
      })), n("definedNames", q).append(y(q, "definedName", {
        attr: {
          name: "_xlnm._FilterDatabase",
          localSheetId: "0",
          hidden: 1
        },
        text: Q(b) + "!$A$" + d + ":" + I(p.header.length - 1) + w
      })));
      b.customize && b.customize(a, b, c);
      0 === n("mergeCells", f).children().length && n("mergeCells", f).remove();
      c = new (E || u.JSZip)();
      d = {
        type: "blob",
        mimeType: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
      };
      O(c, a);
      c.generateAsync ? c.generateAsync(d).then(function (k) {
        K(k, r.filename);
        m.processing(!1);
      }) : (K(c.generate(d), r.filename), this.processing(!1));
    },
    filename: "*",
    extension: ".xlsx",
    exportOptions: {},
    header: !0,
    footer: !1,
    title: "*",
    messageTop: "*",
    messageBottom: "*",
    createEmptyCells: !1,
    autoFilter: !1,
    sheetName: ""
  };
  D.ext.buttons.pdfHtml5 = {
    className: "buttons-pdf buttons-html5",
    available: function available() {
      return u.FileReader !== B && (F || u.pdfMake);
    },
    text: function text(a) {
      return a.i18n("buttons.pdf", "PDF");
    },
    action: function action(a, c, d, b) {
      this.processing(!0);
      d = c.buttons.exportData(b.exportOptions);
      a = c.buttons.exportInfo(b);
      var m = [];
      b.header && m.push(n.map(d.header, function (g) {
        return {
          text: "string" === typeof g ? g : g + "",
          style: "tableHeader"
        };
      }));

      for (var e = 0, f = d.body.length; e < f; e++) {
        m.push(n.map(d.body[e], function (g) {
          if (null === g || g === B) g = "";
          return {
            text: "string" === typeof g ? g : g + "",
            style: e % 2 ? "tableBodyEven" : "tableBodyOdd"
          };
        }));
      }

      b.footer && d.footer && m.push(n.map(d.footer, function (g) {
        return {
          text: "string" === typeof g ? g : g + "",
          style: "tableFooter"
        };
      }));
      d = {
        pageSize: b.pageSize,
        pageOrientation: b.orientation,
        content: [{
          table: {
            headerRows: 1,
            body: m
          },
          layout: "noBorders"
        }],
        styles: {
          tableHeader: {
            bold: !0,
            fontSize: 11,
            color: "white",
            fillColor: "#2d4154",
            alignment: "center"
          },
          tableBodyEven: {},
          tableBodyOdd: {
            fillColor: "#f3f3f3"
          },
          tableFooter: {
            bold: !0,
            fontSize: 11,
            color: "white",
            fillColor: "#2d4154"
          },
          title: {
            alignment: "center",
            fontSize: 15
          },
          message: {}
        },
        defaultStyle: {
          fontSize: 10
        }
      };
      a.messageTop && d.content.unshift({
        text: a.messageTop,
        style: "message",
        margin: [0, 0, 0, 12]
      });
      a.messageBottom && d.content.push({
        text: a.messageBottom,
        style: "message",
        margin: [0, 0, 0, 12]
      });
      a.title && d.content.unshift({
        text: a.title,
        style: "title",
        margin: [0, 0, 0, 12]
      });
      b.customize && b.customize(d, b, c);
      c = (F || u.pdfMake).createPdf(d);
      "open" !== b.download || T() ? c.download(a.filename) : c.open();
      this.processing(!1);
    },
    title: "*",
    filename: "*",
    extension: ".pdf",
    exportOptions: {},
    orientation: "portrait",
    pageSize: "A4",
    header: !0,
    footer: !1,
    messageTop: "*",
    messageBottom: "*",
    customize: null,
    download: "download"
  };
  return D.Buttons;
});
/*!
 Print button for Buttons and DataTables.
 2016 SpryMedia Ltd - datatables.net/license
*/


(function (b) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net", "datatables.net-buttons"], function (d) {
    return b(d, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (d, h) {
    d || (d = window);
    h && h.fn.dataTable || (h = require("datatables.net")(d, h).$);
    h.fn.dataTable.Buttons || require("datatables.net-buttons")(d, h);
    return b(h, d, d.document);
  } : b(jQuery, window, document);
})(function (b, d, h, y) {
  var u = b.fn.dataTable,
      n = h.createElement("a"),
      v = function v(a) {
    n.href = a;
    a = n.host;
    -1 === a.indexOf("/") && 0 !== n.pathname.indexOf("/") && (a += "/");
    return n.protocol + "//" + a + n.pathname + n.search;
  };

  u.ext.buttons.print = {
    className: "buttons-print",
    text: function text(a) {
      return a.i18n("buttons.print", "Print");
    },
    action: function action(a, e, p, k) {
      a = e.buttons.exportData(b.extend({
        decodeEntities: !1
      }, k.exportOptions));
      p = e.buttons.exportInfo(k);

      var w = e.columns(k.exportOptions.columns).flatten().map(function (f) {
        return e.settings()[0].aoColumns[e.column(f).index()].sClass;
      }).toArray(),
          r = function r(f, g) {
        for (var x = "<tr>", l = 0, z = f.length; l < z; l++) {
          x += "<" + g + " " + (w[l] ? 'class="' + w[l] + '"' : "") + ">" + (null === f[l] || f[l] === y ? "" : f[l]) + "</" + g + ">";
        }

        return x + "</tr>";
      },
          m = '<table class="' + e.table().node().className + '">';

      k.header && (m += "<thead>" + r(a.header, "th") + "</thead>");
      m += "<tbody>";

      for (var t = 0, A = a.body.length; t < A; t++) {
        m += r(a.body[t], "td");
      }

      m += "</tbody>";
      k.footer && a.footer && (m += "<tfoot>" + r(a.footer, "th") + "</tfoot>");
      m += "</table>";
      var c = d.open("", "");

      if (c) {
        c.document.close();
        var q = "<title>" + p.title + "</title>";
        b("style, link").each(function () {
          var f = q,
              g = b(this).clone()[0];
          "link" === g.nodeName.toLowerCase() && (g.href = v(g.href));
          q = f + g.outerHTML;
        });

        try {
          c.document.head.innerHTML = q;
        } catch (f) {
          b(c.document.head).html(q);
        }

        c.document.body.innerHTML = "<h1>" + p.title + "</h1><div>" + (p.messageTop || "") + "</div>" + m + "<div>" + (p.messageBottom || "") + "</div>";
        b(c.document.body).addClass("dt-print-view");
        b("img", c.document.body).each(function (f, g) {
          g.setAttribute("src", v(g.getAttribute("src")));
        });
        k.customize && k.customize(c, k, e);

        a = function a() {
          k.autoPrint && (c.print(), c.close());
        };

        navigator.userAgent.match(/Trident\/\d.\d/) ? a() : c.setTimeout(a, 1E3);
      } else e.buttons.info(e.i18n("buttons.printErrorTitle", "Unable to open print view"), e.i18n("buttons.printErrorMsg", "Please allow popups in your browser for this site to be able to view the print view."), 5E3);
    },
    title: "*",
    messageTop: "*",
    messageBottom: "*",
    exportOptions: {},
    header: !0,
    footer: !1,
    autoPrint: !0,
    customize: null
  };
  return u.Buttons;
});
/*!
   Copyright 2010-2021 SpryMedia Ltd.

 This source file is free software, available under the following license:
   MIT license - http://datatables.net/license/mit

 This source file is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 or FITNESS FOR A PARTICULAR PURPOSE. See the license files for details.

 For details please refer to: http://www.datatables.net
 ColReorder 1.5.5
 ©2010-2021 SpryMedia Ltd - datatables.net/license
*/


(function (e) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net"], function (u) {
    return e(u, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (u, t) {
    u || (u = window);
    t && t.fn.dataTable || (t = require("datatables.net")(u, t).$);
    return e(t, u, u.document);
  } : e(jQuery, window, document);
})(function (e, u, t, z) {
  function y(a) {
    for (var b = [], c = 0, f = a.length; c < f; c++) {
      b[a[c]] = c;
    }

    return b;
  }

  function v(a, b, c) {
    b = a.splice(b, 1)[0];
    a.splice(c, 0, b);
  }

  function A(a, b, c) {
    for (var f = [], h = 0, g = a.childNodes.length; h < g; h++) {
      1 == a.childNodes[h].nodeType && f.push(a.childNodes[h]);
    }

    b = f[b];
    null !== c ? a.insertBefore(b, f[c]) : a.appendChild(b);
  }

  var D = e.fn.dataTable;

  e.fn.dataTableExt.oApi.fnColReorder = function (a, b, c, f, h) {
    var g,
        l,
        k = a.aoColumns.length;

    var p = function p(w, x, E) {
      if (w[x] && "function" !== typeof w[x]) {
        var B = w[x].split("."),
            C = B.shift();
        isNaN(1 * C) || (w[x] = E[1 * C] + "." + B.join("."));
      }
    };

    if (b != c) if (0 > b || b >= k) this.oApi._fnLog(a, 1, "ColReorder 'from' index is out of bounds: " + b);else if (0 > c || c >= k) this.oApi._fnLog(a, 1, "ColReorder 'to' index is out of bounds: " + c);else {
      var m = [];
      var d = 0;

      for (g = k; d < g; d++) {
        m[d] = d;
      }

      v(m, b, c);
      var q = y(m);
      d = 0;

      for (g = a.aaSorting.length; d < g; d++) {
        a.aaSorting[d][0] = q[a.aaSorting[d][0]];
      }

      if (null !== a.aaSortingFixed) for (d = 0, g = a.aaSortingFixed.length; d < g; d++) {
        a.aaSortingFixed[d][0] = q[a.aaSortingFixed[d][0]];
      }
      d = 0;

      for (g = k; d < g; d++) {
        var n = a.aoColumns[d];
        m = 0;

        for (l = n.aDataSort.length; m < l; m++) {
          n.aDataSort[m] = q[n.aDataSort[m]];
        }

        n.idx = q[n.idx];
      }

      e.each(a.aLastSort, function (w, x) {
        a.aLastSort[w].src = q[x.src];
      });
      d = 0;

      for (g = k; d < g; d++) {
        n = a.aoColumns[d], "number" == typeof n.mData ? n.mData = q[n.mData] : e.isPlainObject(n.mData) && (p(n.mData, "_", q), p(n.mData, "filter", q), p(n.mData, "sort", q), p(n.mData, "type", q));
      }

      if (a.aoColumns[b].bVisible) {
        p = this.oApi._fnColumnIndexToVisible(a, b);
        l = null;

        for (d = c < b ? c : c + 1; null === l && d < k;) {
          l = this.oApi._fnColumnIndexToVisible(a, d), d++;
        }

        m = a.nTHead.getElementsByTagName("tr");
        d = 0;

        for (g = m.length; d < g; d++) {
          A(m[d], p, l);
        }

        if (null !== a.nTFoot) for (m = a.nTFoot.getElementsByTagName("tr"), d = 0, g = m.length; d < g; d++) {
          A(m[d], p, l);
        }
        d = 0;

        for (g = a.aoData.length; d < g; d++) {
          null !== a.aoData[d].nTr && A(a.aoData[d].nTr, p, l);
        }
      }

      v(a.aoColumns, b, c);
      d = 0;

      for (g = k; d < g; d++) {
        a.oApi._fnColumnOptions(a, d, {});
      }

      v(a.aoPreSearchCols, b, c);
      d = 0;

      for (g = a.aoData.length; d < g; d++) {
        l = a.aoData[d];
        if (n = l.anCells) for (v(n, b, c), m = 0, p = n.length; m < p; m++) {
          n[m] && n[m]._DT_CellIndex && (n[m]._DT_CellIndex.column = m);
        }
        "dom" !== l.src && Array.isArray(l._aData) && v(l._aData, b, c);
      }

      d = 0;

      for (g = a.aoHeader.length; d < g; d++) {
        v(a.aoHeader[d], b, c);
      }

      if (null !== a.aoFooter) for (d = 0, g = a.aoFooter.length; d < g; d++) {
        v(a.aoFooter[d], b, c);
      }
      (h || h === z) && e.fn.dataTable.Api(a).rows().invalidate();
      d = 0;

      for (g = k; d < g; d++) {
        e(a.aoColumns[d].nTh).off(".DT"), this.oApi._fnSortAttachListener(a, a.aoColumns[d].nTh, d);
      }

      e(a.oInstance).trigger("column-reorder.dt", [a, {
        from: b,
        to: c,
        mapping: q,
        drop: f,
        iFrom: b,
        iTo: c,
        aiInvertMapping: q
      }]);
    }
  };

  var r = function r(a, b) {
    a = new e.fn.dataTable.Api(a).settings()[0];
    if (a._colReorder) return a._colReorder;
    !0 === b && (b = {});
    var c = e.fn.dataTable.camelToHungarian;
    c && (c(r.defaults, r.defaults, !0), c(r.defaults, b || {}));
    this.s = {
      dt: null,
      enable: null,
      init: e.extend(!0, {}, r.defaults, b),
      fixed: 0,
      fixedRight: 0,
      reorderCallback: null,
      mouse: {
        startX: -1,
        startY: -1,
        offsetX: -1,
        offsetY: -1,
        target: -1,
        targetIndex: -1,
        fromIndex: -1
      },
      aoTargets: []
    };
    this.dom = {
      drag: null,
      pointer: null
    };
    this.s.enable = this.s.init.bEnable;
    this.s.dt = a;
    this.s.dt._colReorder = this;

    this._fnConstruct();

    return this;
  };

  e.extend(r.prototype, {
    fnEnable: function fnEnable(a) {
      if (!1 === a) return fnDisable();
      this.s.enable = !0;
    },
    fnDisable: function fnDisable() {
      this.s.enable = !1;
    },
    fnReset: function fnReset() {
      this._fnOrderColumns(this.fnOrder());

      return this;
    },
    fnGetCurrentOrder: function fnGetCurrentOrder() {
      return this.fnOrder();
    },
    fnOrder: function fnOrder(a, b) {
      var c = [],
          f,
          h = this.s.dt.aoColumns;

      if (a === z) {
        b = 0;

        for (f = h.length; b < f; b++) {
          c.push(h[b]._ColReorder_iOrigCol);
        }

        return c;
      }

      if (b) {
        h = this.fnOrder();
        b = 0;

        for (f = a.length; b < f; b++) {
          c.push(e.inArray(a[b], h));
        }

        a = c;
      }

      this._fnOrderColumns(y(a));

      return this;
    },
    fnTranspose: function fnTranspose(a, b) {
      b || (b = "toCurrent");
      var c = this.fnOrder(),
          f = this.s.dt.aoColumns;
      return "toCurrent" === b ? Array.isArray(a) ? e.map(a, function (h) {
        return e.inArray(h, c);
      }) : e.inArray(a, c) : Array.isArray(a) ? e.map(a, function (h) {
        return f[h]._ColReorder_iOrigCol;
      }) : f[a]._ColReorder_iOrigCol;
    },
    _fnConstruct: function _fnConstruct() {
      var a = this,
          b = this.s.dt.aoColumns.length,
          c = this.s.dt.nTable,
          f;
      this.s.init.iFixedColumns && (this.s.fixed = this.s.init.iFixedColumns);
      this.s.init.iFixedColumnsLeft && (this.s.fixed = this.s.init.iFixedColumnsLeft);
      this.s.fixedRight = this.s.init.iFixedColumnsRight ? this.s.init.iFixedColumnsRight : 0;
      this.s.init.fnReorderCallback && (this.s.reorderCallback = this.s.init.fnReorderCallback);

      for (f = 0; f < b; f++) {
        f > this.s.fixed - 1 && f < b - this.s.fixedRight && this._fnMouseListener(f, this.s.dt.aoColumns[f].nTh), this.s.dt.aoColumns[f]._ColReorder_iOrigCol = f;
      }

      this.s.dt.oApi._fnCallbackReg(this.s.dt, "aoStateSaveParams", function (l, k) {
        a._fnStateSave.call(a, k);
      }, "ColReorder_State");

      this.s.dt.oApi._fnCallbackReg(this.s.dt, "aoStateLoadParams", function (l, k) {
        a.s.dt._colReorder.fnOrder(k.ColReorder, !0);
      });

      var h = null;
      this.s.init.aiOrder && (h = this.s.init.aiOrder.slice());
      this.s.dt.oLoadedState && "undefined" != typeof this.s.dt.oLoadedState.ColReorder && this.s.dt.oLoadedState.ColReorder.length == this.s.dt.aoColumns.length && (h = this.s.dt.oLoadedState.ColReorder);
      if (h) {
        if (a.s.dt._bInitComplete) b = y(h), a._fnOrderColumns.call(a, b);else {
          var g = !1;
          e(c).on("draw.dt.colReorder", function () {
            if (!a.s.dt._bInitComplete && !g) {
              g = !0;
              var l = y(h);

              a._fnOrderColumns.call(a, l);
            }
          });
        }
      } else this._fnSetColumnIndexes();
      e(c).on("destroy.dt.colReorder", function () {
        e(c).off("destroy.dt.colReorder draw.dt.colReorder");
        e.each(a.s.dt.aoColumns, function (l, k) {
          e(k.nTh).off(".ColReorder");
          e(k.nTh).removeAttr("data-column-index");
        });
        a.s.dt._colReorder = null;
        a.s = null;
      });
    },
    _fnOrderColumns: function _fnOrderColumns(a) {
      var b = !1;
      if (a.length != this.s.dt.aoColumns.length) this.s.dt.oInstance.oApi._fnLog(this.s.dt, 1, "ColReorder - array reorder does not match known number of columns. Skipping.");else {
        for (var c = 0, f = a.length; c < f; c++) {
          var h = e.inArray(c, a);
          c != h && (v(a, h, c), this.s.dt.oInstance.fnColReorder(h, c, !0, !1), b = !0);
        }

        this._fnSetColumnIndexes();

        b && (e.fn.dataTable.Api(this.s.dt).rows().invalidate(), "" === this.s.dt.oScroll.sX && "" === this.s.dt.oScroll.sY || this.s.dt.oInstance.fnAdjustColumnSizing(!1), this.s.dt.oInstance.oApi._fnSaveState(this.s.dt), null !== this.s.reorderCallback && this.s.reorderCallback.call(this));
      }
    },
    _fnStateSave: function _fnStateSave(a) {
      if (null !== this.s) {
        var b,
            c,
            f = this.s.dt.aoColumns;
        a.ColReorder = [];

        if (a.aaSorting) {
          for (b = 0; b < a.aaSorting.length; b++) {
            a.aaSorting[b][0] = f[a.aaSorting[b][0]]._ColReorder_iOrigCol;
          }

          var h = e.extend(!0, [], a.aoSearchCols);
          b = 0;

          for (c = f.length; b < c; b++) {
            var g = f[b]._ColReorder_iOrigCol;
            a.aoSearchCols[g] = h[b];
            a.abVisCols[g] = f[b].bVisible;
            a.ColReorder.push(g);
          }
        } else if (a.order) {
          for (b = 0; b < a.order.length; b++) {
            a.order[b][0] = f[a.order[b][0]]._ColReorder_iOrigCol;
          }

          h = e.extend(!0, [], a.columns);
          b = 0;

          for (c = f.length; b < c; b++) {
            g = f[b]._ColReorder_iOrigCol, a.columns[g] = h[b], a.ColReorder.push(g);
          }
        }
      }
    },
    _fnMouseListener: function _fnMouseListener(a, b) {
      var c = this;
      e(b).on("mousedown.ColReorder", function (f) {
        c.s.enable && 1 === f.which && c._fnMouseDown.call(c, f, b);
      }).on("touchstart.ColReorder", function (f) {
        c.s.enable && c._fnMouseDown.call(c, f, b);
      });
    },
    _fnMouseDown: function _fnMouseDown(a, b) {
      var c = this,
          f = e(a.target).closest("th, td").offset();
      b = parseInt(e(b).attr("data-column-index"), 10);
      b !== z && (this.s.mouse.startX = this._fnCursorPosition(a, "pageX"), this.s.mouse.startY = this._fnCursorPosition(a, "pageY"), this.s.mouse.offsetX = this._fnCursorPosition(a, "pageX") - f.left, this.s.mouse.offsetY = this._fnCursorPosition(a, "pageY") - f.top, this.s.mouse.target = this.s.dt.aoColumns[b].nTh, this.s.mouse.targetIndex = b, this.s.mouse.fromIndex = b, this._fnRegions(), e(t).on("mousemove.ColReorder touchmove.ColReorder", function (h) {
        c._fnMouseMove.call(c, h);
      }).on("mouseup.ColReorder touchend.ColReorder", function (h) {
        c._fnMouseUp.call(c, h);
      }));
    },
    _fnMouseMove: function _fnMouseMove(a) {
      var b = this;

      if (null === this.dom.drag) {
        if (5 > Math.pow(Math.pow(this._fnCursorPosition(a, "pageX") - this.s.mouse.startX, 2) + Math.pow(this._fnCursorPosition(a, "pageY") - this.s.mouse.startY, 2), .5)) return;

        this._fnCreateDragNode();
      }

      this.dom.drag.css({
        left: this._fnCursorPosition(a, "pageX") - this.s.mouse.offsetX,
        top: this._fnCursorPosition(a, "pageY") - this.s.mouse.offsetY
      });
      var c = this.s.mouse.toIndex;
      a = this._fnCursorPosition(a, "pageX");

      for (var f = function f(d) {
        for (; 0 <= d;) {
          d--;
          if (0 >= d) return null;
          if (b.s.aoTargets[d + 1].x !== b.s.aoTargets[d].x) return b.s.aoTargets[d];
        }
      }, h = function h() {
        for (var d = 0; d < b.s.aoTargets.length - 1; d++) {
          if (b.s.aoTargets[d].x !== b.s.aoTargets[d + 1].x) return b.s.aoTargets[d];
        }
      }, g = function g() {
        for (var d = b.s.aoTargets.length - 1; 0 < d; d--) {
          if (b.s.aoTargets[d].x !== b.s.aoTargets[d - 1].x) return b.s.aoTargets[d];
        }
      }, l = 1; l < this.s.aoTargets.length; l++) {
        var k = f(l);
        k || (k = h());
        var p = k.x + (this.s.aoTargets[l].x - k.x) / 2;

        if (this._fnIsLtr()) {
          if (a < p) {
            var m = k;
            break;
          }
        } else if (a > p) {
          m = k;
          break;
        }
      }

      m ? (this.dom.pointer.css("left", m.x), this.s.mouse.toIndex = m.to) : (this.dom.pointer.css("left", g().x), this.s.mouse.toIndex = g().to);
      this.s.init.bRealtime && c !== this.s.mouse.toIndex && (this.s.dt.oInstance.fnColReorder(this.s.mouse.fromIndex, this.s.mouse.toIndex), this.s.mouse.fromIndex = this.s.mouse.toIndex, "" === this.s.dt.oScroll.sX && "" === this.s.dt.oScroll.sY || this.s.dt.oInstance.fnAdjustColumnSizing(!1), this._fnRegions());
    },
    _fnMouseUp: function _fnMouseUp(a) {
      e(t).off(".ColReorder");
      null !== this.dom.drag && (this.dom.drag.remove(), this.dom.pointer.remove(), this.dom.drag = null, this.dom.pointer = null, this.s.dt.oInstance.fnColReorder(this.s.mouse.fromIndex, this.s.mouse.toIndex, !0), this._fnSetColumnIndexes(), "" === this.s.dt.oScroll.sX && "" === this.s.dt.oScroll.sY || this.s.dt.oInstance.fnAdjustColumnSizing(!1), this.s.dt.oInstance.oApi._fnSaveState(this.s.dt), null !== this.s.reorderCallback && this.s.reorderCallback.call(this));
    },
    _fnRegions: function _fnRegions() {
      var a = this.s.dt.aoColumns,
          b = this._fnIsLtr();

      this.s.aoTargets.splice(0, this.s.aoTargets.length);
      var c = e(this.s.dt.nTable).offset().left,
          f = [];
      e.each(a, function (l, k) {
        if (k.bVisible && "none" !== k.nTh.style.display) {
          k = e(k.nTh);
          var p = k.offset().left;
          b && (p += k.outerWidth());
          f.push({
            index: l,
            bound: p
          });
          c = p;
        } else f.push({
          index: l,
          bound: c
        });
      });
      var h = f[0];
      a = e(a[h.index].nTh).outerWidth();
      this.s.aoTargets.push({
        to: 0,
        x: h.bound - a
      });

      for (h = 0; h < f.length; h++) {
        a = f[h];
        var g = a.index;
        a.index < this.s.mouse.fromIndex && g++;
        this.s.aoTargets.push({
          to: g,
          x: a.bound
        });
      }

      0 !== this.s.fixedRight && this.s.aoTargets.splice(this.s.aoTargets.length - this.s.fixedRight);
      0 !== this.s.fixed && this.s.aoTargets.splice(0, this.s.fixed);
    },
    _fnCreateDragNode: function _fnCreateDragNode() {
      var a = "" !== this.s.dt.oScroll.sX || "" !== this.s.dt.oScroll.sY,
          b = this.s.dt.aoColumns[this.s.mouse.targetIndex].nTh,
          c = b.parentNode,
          f = c.parentNode,
          h = f.parentNode,
          g = e(b).clone();
      this.dom.drag = e(h.cloneNode(!1)).addClass("DTCR_clonedTable").append(e(f.cloneNode(!1)).append(e(c.cloneNode(!1)).append(g[0]))).css({
        position: "absolute",
        top: 0,
        left: 0,
        width: e(b).outerWidth(),
        height: e(b).outerHeight()
      }).appendTo("body");
      this.dom.pointer = e("<div></div>").addClass("DTCR_pointer").css({
        position: "absolute",
        top: a ? e(e(this.s.dt.nScrollBody).parent()).offset().top : e(this.s.dt.nTable).offset().top,
        height: a ? e(e(this.s.dt.nScrollBody).parent()).height() : e(this.s.dt.nTable).height()
      }).appendTo("body");
    },
    _fnSetColumnIndexes: function _fnSetColumnIndexes() {
      e.each(this.s.dt.aoColumns, function (a, b) {
        e(b.nTh).attr("data-column-index", a);
      });
    },
    _fnCursorPosition: function _fnCursorPosition(a, b) {
      return -1 !== a.type.indexOf("touch") ? a.originalEvent.touches[0][b] : a[b];
    },
    _fnIsLtr: function _fnIsLtr() {
      return "rtl" !== e(this.s.dt.nTable).css("direction");
    }
  });
  r.defaults = {
    aiOrder: null,
    bEnable: !0,
    bRealtime: !0,
    iFixedColumnsLeft: 0,
    iFixedColumnsRight: 0,
    fnReorderCallback: null
  };
  r.version = "1.5.5";
  e.fn.dataTable.ColReorder = r;
  e.fn.DataTable.ColReorder = r;
  "function" == typeof e.fn.dataTable && "function" == typeof e.fn.dataTableExt.fnVersionCheck && e.fn.dataTableExt.fnVersionCheck("1.10.8") ? e.fn.dataTableExt.aoFeatures.push({
    fnInit: function fnInit(a) {
      var b = a.oInstance;
      a._colReorder ? b.oApi._fnLog(a, 1, "ColReorder attempted to initialise twice. Ignoring second") : (b = a.oInit, new r(a, b.colReorder || b.oColReorder || {}));
      return null;
    },
    cFeature: "R",
    sFeature: "ColReorder"
  }) : alert("Warning: ColReorder requires DataTables 1.10.8 or greater - www.datatables.net/download");
  e(t).on("preInit.dt.colReorder", function (a, b) {
    if ("dt" === a.namespace) {
      a = b.oInit.colReorder;
      var c = D.defaults.colReorder;
      if (a || c) c = e.extend({}, a, c), !1 !== a && new r(b, c);
    }
  });
  e.fn.dataTable.Api.register("colReorder.reset()", function () {
    return this.iterator("table", function (a) {
      a._colReorder.fnReset();
    });
  });
  e.fn.dataTable.Api.register("colReorder.order()", function (a, b) {
    return a ? this.iterator("table", function (c) {
      c._colReorder.fnOrder(a, b);
    }) : this.context.length ? this.context[0]._colReorder.fnOrder() : null;
  });
  e.fn.dataTable.Api.register("colReorder.transpose()", function (a, b) {
    return this.context.length && this.context[0]._colReorder ? this.context[0]._colReorder.fnTranspose(a, b) : a;
  });
  e.fn.dataTable.Api.register("colReorder.move()", function (a, b, c, f) {
    this.context.length && (this.context[0]._colReorder.s.dt.oInstance.fnColReorder(a, b, c, f), this.context[0]._colReorder._fnSetColumnIndexes());
    return this;
  });
  e.fn.dataTable.Api.register("colReorder.enable()", function (a) {
    return this.iterator("table", function (b) {
      b._colReorder && b._colReorder.fnEnable(a);
    });
  });
  e.fn.dataTable.Api.register("colReorder.disable()", function () {
    return this.iterator("table", function (a) {
      a._colReorder && a._colReorder.fnDisable();
    });
  });
  return r;
});
/*!
 DataTables styling wrapper for ColReorder
 ©2018 SpryMedia Ltd - datatables.net/license
*/


(function (c) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net-dt", "datatables.net-colreorder"], function (a) {
    return c(a, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (a, b) {
    a || (a = window);
    b && b.fn.dataTable || (b = require("datatables.net-dt")(a, b).$);
    b.fn.dataTable.ColReorder || require("datatables.net-colreorder")(a, b);
    return c(b, a, a.document);
  } : c(jQuery, window, document);
})(function (c, a, b, d) {
  return c.fn.dataTable;
});
/*!
   Copyright 2014-2021 SpryMedia Ltd.

 This source file is free software, available under the following license:
   MIT license - http://datatables.net/license/mit

 This source file is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 or FITNESS FOR A PARTICULAR PURPOSE. See the license files for details.

 For details please refer to: http://www.datatables.net
 Responsive 2.2.9
 2014-2021 SpryMedia Ltd - datatables.net/license
*/


var $jscomp = $jscomp || {};
$jscomp.scope = {};

$jscomp.findInternal = function (b, k, m) {
  b instanceof String && (b = String(b));

  for (var n = b.length, p = 0; p < n; p++) {
    var y = b[p];
    if (k.call(m, y, p, b)) return {
      i: p,
      v: y
    };
  }

  return {
    i: -1,
    v: void 0
  };
};

$jscomp.ASSUME_ES5 = !1;
$jscomp.ASSUME_NO_NATIVE_MAP = !1;
$jscomp.ASSUME_NO_NATIVE_SET = !1;
$jscomp.SIMPLE_FROUND_POLYFILL = !1;
$jscomp.ISOLATE_POLYFILLS = !1;
$jscomp.defineProperty = $jscomp.ASSUME_ES5 || "function" == typeof Object.defineProperties ? Object.defineProperty : function (b, k, m) {
  if (b == Array.prototype || b == Object.prototype) return b;
  b[k] = m.value;
  return b;
};

$jscomp.getGlobal = function (b) {
  b = ["object" == (typeof globalThis === "undefined" ? "undefined" : _typeof(globalThis)) && globalThis, b, "object" == (typeof window === "undefined" ? "undefined" : _typeof(window)) && window, "object" == (typeof self === "undefined" ? "undefined" : _typeof(self)) && self, "object" == (typeof global === "undefined" ? "undefined" : _typeof(global)) && global];

  for (var k = 0; k < b.length; ++k) {
    var m = b[k];
    if (m && m.Math == Math) return m;
  }

  throw Error("Cannot find global object");
};

$jscomp.global = $jscomp.getGlobal(void 0);
$jscomp.IS_SYMBOL_NATIVE = "function" === typeof Symbol && "symbol" === _typeof(Symbol("x"));
$jscomp.TRUST_ES6_POLYFILLS = !$jscomp.ISOLATE_POLYFILLS || $jscomp.IS_SYMBOL_NATIVE;
$jscomp.polyfills = {};
$jscomp.propertyToPolyfillSymbol = {};
$jscomp.POLYFILL_PREFIX = "$jscp$";

var $jscomp$lookupPolyfilledValue = function $jscomp$lookupPolyfilledValue(b, k) {
  var m = $jscomp.propertyToPolyfillSymbol[k];
  if (null == m) return b[k];
  m = b[m];
  return void 0 !== m ? m : b[k];
};

$jscomp.polyfill = function (b, k, m, n) {
  k && ($jscomp.ISOLATE_POLYFILLS ? $jscomp.polyfillIsolated(b, k, m, n) : $jscomp.polyfillUnisolated(b, k, m, n));
};

$jscomp.polyfillUnisolated = function (b, k, m, n) {
  m = $jscomp.global;
  b = b.split(".");

  for (n = 0; n < b.length - 1; n++) {
    var p = b[n];
    if (!(p in m)) return;
    m = m[p];
  }

  b = b[b.length - 1];
  n = m[b];
  k = k(n);
  k != n && null != k && $jscomp.defineProperty(m, b, {
    configurable: !0,
    writable: !0,
    value: k
  });
};

$jscomp.polyfillIsolated = function (b, k, m, n) {
  var p = b.split(".");
  b = 1 === p.length;
  n = p[0];
  n = !b && n in $jscomp.polyfills ? $jscomp.polyfills : $jscomp.global;

  for (var y = 0; y < p.length - 1; y++) {
    var z = p[y];
    if (!(z in n)) return;
    n = n[z];
  }

  p = p[p.length - 1];
  m = $jscomp.IS_SYMBOL_NATIVE && "es6" === m ? n[p] : null;
  k = k(m);
  null != k && (b ? $jscomp.defineProperty($jscomp.polyfills, p, {
    configurable: !0,
    writable: !0,
    value: k
  }) : k !== m && ($jscomp.propertyToPolyfillSymbol[p] = $jscomp.IS_SYMBOL_NATIVE ? $jscomp.global.Symbol(p) : $jscomp.POLYFILL_PREFIX + p, p = $jscomp.propertyToPolyfillSymbol[p], $jscomp.defineProperty(n, p, {
    configurable: !0,
    writable: !0,
    value: k
  })));
};

$jscomp.polyfill("Array.prototype.find", function (b) {
  return b ? b : function (k, m) {
    return $jscomp.findInternal(this, k, m).v;
  };
}, "es6", "es3");

(function (b) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net"], function (k) {
    return b(k, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (k, m) {
    k || (k = window);
    m && m.fn.dataTable || (m = require("datatables.net")(k, m).$);
    return b(m, k, k.document);
  } : b(jQuery, window, document);
})(function (b, k, m, n) {
  function p(a, c, d) {
    var f = c + "-" + d;
    if (A[f]) return A[f];
    var g = [];
    a = a.cell(c, d).node().childNodes;
    c = 0;

    for (d = a.length; c < d; c++) {
      g.push(a[c]);
    }

    return A[f] = g;
  }

  function y(a, c, d) {
    var f = c + "-" + d;

    if (A[f]) {
      a = a.cell(c, d).node();
      d = A[f][0].parentNode.childNodes;
      c = [];

      for (var g = 0, l = d.length; g < l; g++) {
        c.push(d[g]);
      }

      d = 0;

      for (g = c.length; d < g; d++) {
        a.appendChild(c[d]);
      }

      A[f] = n;
    }
  }

  var z = b.fn.dataTable,
      u = function u(a, c) {
    if (!z.versionCheck || !z.versionCheck("1.10.10")) throw "DataTables Responsive requires DataTables 1.10.10 or newer";
    this.s = {
      dt: new z.Api(a),
      columns: [],
      current: []
    };
    this.s.dt.settings()[0].responsive || (c && "string" === typeof c.details ? c.details = {
      type: c.details
    } : c && !1 === c.details ? c.details = {
      type: !1
    } : c && !0 === c.details && (c.details = {
      type: "inline"
    }), this.c = b.extend(!0, {}, u.defaults, z.defaults.responsive, c), a.responsive = this, this._constructor());
  };

  b.extend(u.prototype, {
    _constructor: function _constructor() {
      var a = this,
          c = this.s.dt,
          d = c.settings()[0],
          f = b(k).innerWidth();
      c.settings()[0]._responsive = this;
      b(k).on("resize.dtr orientationchange.dtr", z.util.throttle(function () {
        var g = b(k).innerWidth();
        g !== f && (a._resize(), f = g);
      }));

      d.oApi._fnCallbackReg(d, "aoRowCreatedCallback", function (g, l, h) {
        -1 !== b.inArray(!1, a.s.current) && b(">td, >th", g).each(function (e) {
          e = c.column.index("toData", e);
          !1 === a.s.current[e] && b(this).css("display", "none");
        });
      });

      c.on("destroy.dtr", function () {
        c.off(".dtr");
        b(c.table().body()).off(".dtr");
        b(k).off("resize.dtr orientationchange.dtr");
        c.cells(".dtr-control").nodes().to$().removeClass("dtr-control");
        b.each(a.s.current, function (g, l) {
          !1 === l && a._setColumnVis(g, !0);
        });
      });
      this.c.breakpoints.sort(function (g, l) {
        return g.width < l.width ? 1 : g.width > l.width ? -1 : 0;
      });

      this._classLogic();

      this._resizeAuto();

      d = this.c.details;
      !1 !== d.type && (a._detailsInit(), c.on("column-visibility.dtr", function () {
        a._timer && clearTimeout(a._timer);
        a._timer = setTimeout(function () {
          a._timer = null;

          a._classLogic();

          a._resizeAuto();

          a._resize(!0);

          a._redrawChildren();
        }, 100);
      }), c.on("draw.dtr", function () {
        a._redrawChildren();
      }), b(c.table().node()).addClass("dtr-" + d.type));
      c.on("column-reorder.dtr", function (g, l, h) {
        a._classLogic();

        a._resizeAuto();

        a._resize(!0);
      });
      c.on("column-sizing.dtr", function () {
        a._resizeAuto();

        a._resize();
      });
      c.on("preXhr.dtr", function () {
        var g = [];
        c.rows().every(function () {
          this.child.isShown() && g.push(this.id(!0));
        });
        c.one("draw.dtr", function () {
          a._resizeAuto();

          a._resize();

          c.rows(g).every(function () {
            a._detailsDisplay(this, !1);
          });
        });
      });
      c.on("draw.dtr", function () {
        a._controlClass();
      }).on("init.dtr", function (g, l, h) {
        "dt" === g.namespace && (a._resizeAuto(), a._resize(), b.inArray(!1, a.s.current) && c.columns.adjust());
      });

      this._resize();
    },
    _columnsVisiblity: function _columnsVisiblity(a) {
      var c = this.s.dt,
          d = this.s.columns,
          f,
          g = d.map(function (t, v) {
        return {
          columnIdx: v,
          priority: t.priority
        };
      }).sort(function (t, v) {
        return t.priority !== v.priority ? t.priority - v.priority : t.columnIdx - v.columnIdx;
      }),
          l = b.map(d, function (t, v) {
        return !1 === c.column(v).visible() ? "not-visible" : t.auto && null === t.minWidth ? !1 : !0 === t.auto ? "-" : -1 !== b.inArray(a, t.includeIn);
      }),
          h = 0;
      var e = 0;

      for (f = l.length; e < f; e++) {
        !0 === l[e] && (h += d[e].minWidth);
      }

      e = c.settings()[0].oScroll;
      e = e.sY || e.sX ? e.iBarWidth : 0;
      h = c.table().container().offsetWidth - e - h;
      e = 0;

      for (f = l.length; e < f; e++) {
        d[e].control && (h -= d[e].minWidth);
      }

      var r = !1;
      e = 0;

      for (f = g.length; e < f; e++) {
        var q = g[e].columnIdx;
        "-" === l[q] && !d[q].control && d[q].minWidth && (r || 0 > h - d[q].minWidth ? (r = !0, l[q] = !1) : l[q] = !0, h -= d[q].minWidth);
      }

      g = !1;
      e = 0;

      for (f = d.length; e < f; e++) {
        if (!d[e].control && !d[e].never && !1 === l[e]) {
          g = !0;
          break;
        }
      }

      e = 0;

      for (f = d.length; e < f; e++) {
        d[e].control && (l[e] = g), "not-visible" === l[e] && (l[e] = !1);
      }

      -1 === b.inArray(!0, l) && (l[0] = !0);
      return l;
    },
    _classLogic: function _classLogic() {
      var a = this,
          c = this.c.breakpoints,
          d = this.s.dt,
          f = d.columns().eq(0).map(function (h) {
        var e = this.column(h),
            r = e.header().className;
        h = d.settings()[0].aoColumns[h].responsivePriority;
        e = e.header().getAttribute("data-priority");
        h === n && (h = e === n || null === e ? 1E4 : 1 * e);
        return {
          className: r,
          includeIn: [],
          auto: !1,
          control: !1,
          never: r.match(/\bnever\b/) ? !0 : !1,
          priority: h
        };
      }),
          g = function g(h, e) {
        h = f[h].includeIn;
        -1 === b.inArray(e, h) && h.push(e);
      },
          l = function l(h, e, r, q) {
        if (!r) f[h].includeIn.push(e);else if ("max-" === r) for (q = a._find(e).width, e = 0, r = c.length; e < r; e++) {
          c[e].width <= q && g(h, c[e].name);
        } else if ("min-" === r) for (q = a._find(e).width, e = 0, r = c.length; e < r; e++) {
          c[e].width >= q && g(h, c[e].name);
        } else if ("not-" === r) for (e = 0, r = c.length; e < r; e++) {
          -1 === c[e].name.indexOf(q) && g(h, c[e].name);
        }
      };

      f.each(function (h, e) {
        for (var r = h.className.split(" "), q = !1, t = 0, v = r.length; t < v; t++) {
          var B = r[t].trim();

          if ("all" === B) {
            q = !0;
            h.includeIn = b.map(c, function (w) {
              return w.name;
            });
            return;
          }

          if ("none" === B || h.never) {
            q = !0;
            return;
          }

          if ("control" === B || "dtr-control" === B) {
            q = !0;
            h.control = !0;
            return;
          }

          b.each(c, function (w, D) {
            w = D.name.split("-");
            var x = B.match(new RegExp("(min\\-|max\\-|not\\-)?(" + w[0] + ")(\\-[_a-zA-Z0-9])?"));
            x && (q = !0, x[2] === w[0] && x[3] === "-" + w[1] ? l(e, D.name, x[1], x[2] + x[3]) : x[2] !== w[0] || x[3] || l(e, D.name, x[1], x[2]));
          });
        }

        q || (h.auto = !0);
      });
      this.s.columns = f;
    },
    _controlClass: function _controlClass() {
      if ("inline" === this.c.details.type) {
        var a = this.s.dt,
            c = b.inArray(!0, this.s.current);
        a.cells(null, function (d) {
          return d !== c;
        }, {
          page: "current"
        }).nodes().to$().filter(".dtr-control").removeClass("dtr-control");
        a.cells(null, c, {
          page: "current"
        }).nodes().to$().addClass("dtr-control");
      }
    },
    _detailsDisplay: function _detailsDisplay(a, c) {
      var d = this,
          f = this.s.dt,
          g = this.c.details;

      if (g && !1 !== g.type) {
        var l = g.display(a, c, function () {
          return g.renderer(f, a[0], d._detailsObj(a[0]));
        });
        !0 !== l && !1 !== l || b(f.table().node()).triggerHandler("responsive-display.dt", [f, a, l, c]);
      }
    },
    _detailsInit: function _detailsInit() {
      var a = this,
          c = this.s.dt,
          d = this.c.details;
      "inline" === d.type && (d.target = "td.dtr-control, th.dtr-control");
      c.on("draw.dtr", function () {
        a._tabIndexes();
      });

      a._tabIndexes();

      b(c.table().body()).on("keyup.dtr", "td, th", function (g) {
        13 === g.keyCode && b(this).data("dtr-keyboard") && b(this).click();
      });
      var f = d.target;
      d = "string" === typeof f ? f : "td, th";
      if (f !== n || null !== f) b(c.table().body()).on("click.dtr mousedown.dtr mouseup.dtr", d, function (g) {
        if (b(c.table().node()).hasClass("collapsed") && -1 !== b.inArray(b(this).closest("tr").get(0), c.rows().nodes().toArray())) {
          if ("number" === typeof f) {
            var l = 0 > f ? c.columns().eq(0).length + f : f;
            if (c.cell(this).index().column !== l) return;
          }

          l = c.row(b(this).closest("tr"));
          "click" === g.type ? a._detailsDisplay(l, !1) : "mousedown" === g.type ? b(this).css("outline", "none") : "mouseup" === g.type && b(this).trigger("blur").css("outline", "");
        }
      });
    },
    _detailsObj: function _detailsObj(a) {
      var c = this,
          d = this.s.dt;
      return b.map(this.s.columns, function (f, g) {
        if (!f.never && !f.control) return f = d.settings()[0].aoColumns[g], {
          className: f.sClass,
          columnIndex: g,
          data: d.cell(a, g).render(c.c.orthogonal),
          hidden: d.column(g).visible() && !c.s.current[g],
          rowIndex: a,
          title: null !== f.sTitle ? f.sTitle : b(d.column(g).header()).text()
        };
      });
    },
    _find: function _find(a) {
      for (var c = this.c.breakpoints, d = 0, f = c.length; d < f; d++) {
        if (c[d].name === a) return c[d];
      }
    },
    _redrawChildren: function _redrawChildren() {
      var a = this,
          c = this.s.dt;
      c.rows({
        page: "current"
      }).iterator("row", function (d, f) {
        c.row(f);

        a._detailsDisplay(c.row(f), !0);
      });
    },
    _resize: function _resize(a) {
      var c = this,
          d = this.s.dt,
          f = b(k).innerWidth(),
          g = this.c.breakpoints,
          l = g[0].name,
          h = this.s.columns,
          e,
          r = this.s.current.slice();

      for (e = g.length - 1; 0 <= e; e--) {
        if (f <= g[e].width) {
          l = g[e].name;
          break;
        }
      }

      var q = this._columnsVisiblity(l);

      this.s.current = q;
      g = !1;
      e = 0;

      for (f = h.length; e < f; e++) {
        if (!1 === q[e] && !h[e].never && !h[e].control && !1 === !d.column(e).visible()) {
          g = !0;
          break;
        }
      }

      b(d.table().node()).toggleClass("collapsed", g);
      var t = !1,
          v = 0;
      d.columns().eq(0).each(function (B, w) {
        !0 === q[w] && v++;
        if (a || q[w] !== r[w]) t = !0, c._setColumnVis(B, q[w]);
      });
      t && (this._redrawChildren(), b(d.table().node()).trigger("responsive-resize.dt", [d, this.s.current]), 0 === d.page.info().recordsDisplay && b("td", d.table().body()).eq(0).attr("colspan", v));

      c._controlClass();
    },
    _resizeAuto: function _resizeAuto() {
      var a = this.s.dt,
          c = this.s.columns;

      if (this.c.auto && -1 !== b.inArray(!0, b.map(c, function (e) {
        return e.auto;
      }))) {
        b.isEmptyObject(A) || b.each(A, function (e) {
          e = e.split("-");
          y(a, 1 * e[0], 1 * e[1]);
        });
        a.table().node();
        var d = a.table().node().cloneNode(!1),
            f = b(a.table().header().cloneNode(!1)).appendTo(d),
            g = b(a.table().body()).clone(!1, !1).empty().appendTo(d);
        d.style.width = "auto";
        var l = a.columns().header().filter(function (e) {
          return a.column(e).visible();
        }).to$().clone(!1).css("display", "table-cell").css("width", "auto").css("min-width", 0);
        b(g).append(b(a.rows({
          page: "current"
        }).nodes()).clone(!1)).find("th, td").css("display", "");

        if (g = a.table().footer()) {
          g = b(g.cloneNode(!1)).appendTo(d);
          var h = a.columns().footer().filter(function (e) {
            return a.column(e).visible();
          }).to$().clone(!1).css("display", "table-cell");
          b("<tr/>").append(h).appendTo(g);
        }

        b("<tr/>").append(l).appendTo(f);
        "inline" === this.c.details.type && b(d).addClass("dtr-inline collapsed");
        b(d).find("[name]").removeAttr("name");
        b(d).css("position", "relative");
        d = b("<div/>").css({
          width: 1,
          height: 1,
          overflow: "hidden",
          clear: "both"
        }).append(d);
        d.insertBefore(a.table().node());
        l.each(function (e) {
          e = a.column.index("fromVisible", e);
          c[e].minWidth = this.offsetWidth || 0;
        });
        d.remove();
      }
    },
    _responsiveOnlyHidden: function _responsiveOnlyHidden() {
      var a = this.s.dt;
      return b.map(this.s.current, function (c, d) {
        return !1 === a.column(d).visible() ? !0 : c;
      });
    },
    _setColumnVis: function _setColumnVis(a, c) {
      var d = this.s.dt;
      c = c ? "" : "none";
      b(d.column(a).header()).css("display", c);
      b(d.column(a).footer()).css("display", c);
      d.column(a).nodes().to$().css("display", c);
      b.isEmptyObject(A) || d.cells(null, a).indexes().each(function (f) {
        y(d, f.row, f.column);
      });
    },
    _tabIndexes: function _tabIndexes() {
      var a = this.s.dt,
          c = a.cells({
        page: "current"
      }).nodes().to$(),
          d = a.settings()[0],
          f = this.c.details.target;
      c.filter("[data-dtr-keyboard]").removeData("[data-dtr-keyboard]");
      "number" === typeof f ? a.cells(null, f, {
        page: "current"
      }).nodes().to$().attr("tabIndex", d.iTabIndex).data("dtr-keyboard", 1) : ("td:first-child, th:first-child" === f && (f = ">td:first-child, >th:first-child"), b(f, a.rows({
        page: "current"
      }).nodes()).attr("tabIndex", d.iTabIndex).data("dtr-keyboard", 1));
    }
  });
  u.breakpoints = [{
    name: "desktop",
    width: Infinity
  }, {
    name: "tablet-l",
    width: 1024
  }, {
    name: "tablet-p",
    width: 768
  }, {
    name: "mobile-l",
    width: 480
  }, {
    name: "mobile-p",
    width: 320
  }];
  u.display = {
    childRow: function childRow(a, c, d) {
      if (c) {
        if (b(a.node()).hasClass("parent")) return a.child(d(), "child").show(), !0;
      } else {
        if (a.child.isShown()) return a.child(!1), b(a.node()).removeClass("parent"), !1;
        a.child(d(), "child").show();
        b(a.node()).addClass("parent");
        return !0;
      }
    },
    childRowImmediate: function childRowImmediate(a, c, d) {
      if (!c && a.child.isShown() || !a.responsive.hasHidden()) return a.child(!1), b(a.node()).removeClass("parent"), !1;
      a.child(d(), "child").show();
      b(a.node()).addClass("parent");
      return !0;
    },
    modal: function modal(a) {
      return function (c, d, f) {
        if (d) b("div.dtr-modal-content").empty().append(f());else {
          var g = function g() {
            l.remove();
            b(m).off("keypress.dtr");
          },
              l = b('<div class="dtr-modal"/>').append(b('<div class="dtr-modal-display"/>').append(b('<div class="dtr-modal-content"/>').append(f())).append(b('<div class="dtr-modal-close">&times;</div>').click(function () {
            g();
          }))).append(b('<div class="dtr-modal-background"/>').click(function () {
            g();
          })).appendTo("body");

          b(m).on("keyup.dtr", function (h) {
            27 === h.keyCode && (h.stopPropagation(), g());
          });
        }
        a && a.header && b("div.dtr-modal-content").prepend("<h2>" + a.header(c) + "</h2>");
      };
    }
  };
  var A = {};
  u.renderer = {
    listHiddenNodes: function listHiddenNodes() {
      return function (a, c, d) {
        var f = b('<ul data-dtr-index="' + c + '" class="dtr-details"/>'),
            g = !1;
        b.each(d, function (l, h) {
          h.hidden && (b("<li " + (h.className ? 'class="' + h.className + '"' : "") + ' data-dtr-index="' + h.columnIndex + '" data-dt-row="' + h.rowIndex + '" data-dt-column="' + h.columnIndex + '"><span class="dtr-title">' + h.title + "</span> </li>").append(b('<span class="dtr-data"/>').append(p(a, h.rowIndex, h.columnIndex))).appendTo(f), g = !0);
        });
        return g ? f : !1;
      };
    },
    listHidden: function listHidden() {
      return function (a, c, d) {
        return (a = b.map(d, function (f) {
          var g = f.className ? 'class="' + f.className + '"' : "";
          return f.hidden ? "<li " + g + ' data-dtr-index="' + f.columnIndex + '" data-dt-row="' + f.rowIndex + '" data-dt-column="' + f.columnIndex + '"><span class="dtr-title">' + f.title + '</span> <span class="dtr-data">' + f.data + "</span></li>" : "";
        }).join("")) ? b('<ul data-dtr-index="' + c + '" class="dtr-details"/>').append(a) : !1;
      };
    },
    tableAll: function tableAll(a) {
      a = b.extend({
        tableClass: ""
      }, a);
      return function (c, d, f) {
        c = b.map(f, function (g) {
          return "<tr " + (g.className ? 'class="' + g.className + '"' : "") + ' data-dt-row="' + g.rowIndex + '" data-dt-column="' + g.columnIndex + '"><td>' + g.title + ":</td> <td>" + g.data + "</td></tr>";
        }).join("");
        return b('<table class="' + a.tableClass + ' dtr-details" width="100%"/>').append(c);
      };
    }
  };
  u.defaults = {
    breakpoints: u.breakpoints,
    auto: !0,
    details: {
      display: u.display.childRow,
      renderer: u.renderer.listHidden(),
      target: 0,
      type: "inline"
    },
    orthogonal: "display"
  };
  var C = b.fn.dataTable.Api;
  C.register("responsive()", function () {
    return this;
  });
  C.register("responsive.index()", function (a) {
    a = b(a);
    return {
      column: a.data("dtr-index"),
      row: a.parent().data("dtr-index")
    };
  });
  C.register("responsive.rebuild()", function () {
    return this.iterator("table", function (a) {
      a._responsive && a._responsive._classLogic();
    });
  });
  C.register("responsive.recalc()", function () {
    return this.iterator("table", function (a) {
      a._responsive && (a._responsive._resizeAuto(), a._responsive._resize());
    });
  });
  C.register("responsive.hasHidden()", function () {
    var a = this.context[0];
    return a._responsive ? -1 !== b.inArray(!1, a._responsive._responsiveOnlyHidden()) : !1;
  });
  C.registerPlural("columns().responsiveHidden()", "column().responsiveHidden()", function () {
    return this.iterator("column", function (a, c) {
      return a._responsive ? a._responsive._responsiveOnlyHidden()[c] : !1;
    }, 1);
  });
  u.version = "2.2.9";
  b.fn.dataTable.Responsive = u;
  b.fn.DataTable.Responsive = u;
  b(m).on("preInit.dt.dtr", function (a, c, d) {
    "dt" === a.namespace && (b(c.nTable).hasClass("responsive") || b(c.nTable).hasClass("dt-responsive") || c.oInit.responsive || z.defaults.responsive) && (a = c.oInit.responsive, !1 !== a && new u(c, b.isPlainObject(a) ? a : {}));
  });
  return u;
});
/*!
 DataTables styling wrapper for Responsive
 ©2018 SpryMedia Ltd - datatables.net/license
*/


(function (c) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net-dt", "datatables.net-responsive"], function (a) {
    return c(a, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (a, b) {
    a || (a = window);
    b && b.fn.dataTable || (b = require("datatables.net-dt")(a, b).$);
    b.fn.dataTable.Responsive || require("datatables.net-responsive")(a, b);
    return c(b, a, a.document);
  } : c(jQuery, window, document);
})(function (c, a, b, d) {
  return c.fn.dataTable;
});
/*!
   Copyright 2015-2021 SpryMedia Ltd.

 This source file is free software, available under the following license:
   MIT license - http://datatables.net/license/mit

 This source file is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 or FITNESS FOR A PARTICULAR PURPOSE. See the license files for details.

 For details please refer to: http://www.datatables.net/extensions/select
 Select for DataTables 1.3.4
 2015-2021 SpryMedia Ltd - datatables.net/license/mit
*/


(function (h) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net"], function (r) {
    return h(r, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (r, v) {
    r || (r = window);
    v && v.fn.dataTable || (v = require("datatables.net")(r, v).$);
    return h(v, r, r.document);
  } : h(jQuery, window, document);
})(function (h, r, v, l) {
  function I(a, b, c) {
    var d = function d(g, f) {
      if (g > f) {
        var k = f;
        f = g;
        g = k;
      }

      var n = !1;
      return a.columns(":visible").indexes().filter(function (q) {
        q === g && (n = !0);
        return q === f ? (n = !1, !0) : n;
      });
    };

    var e = function e(g, f) {
      var k = a.rows({
        search: "applied"
      }).indexes();

      if (k.indexOf(g) > k.indexOf(f)) {
        var n = f;
        f = g;
        g = n;
      }

      var q = !1;
      return k.filter(function (y) {
        y === g && (q = !0);
        return y === f ? (q = !1, !0) : q;
      });
    };

    a.cells({
      selected: !0
    }).any() || c ? (d = d(c.column, b.column), c = e(c.row, b.row)) : (d = d(0, b.column), c = e(0, b.row));
    c = a.cells(c, d).flatten();
    a.cells(b, {
      selected: !0
    }).any() ? a.cells(c).deselect() : a.cells(c).select();
  }

  function C(a) {
    var b = a.settings()[0]._select.selector;

    h(a.table().container()).off("mousedown.dtSelect", b).off("mouseup.dtSelect", b).off("click.dtSelect", b);
    h("body").off("click.dtSelect" + D(a.table().node()));
  }

  function J(a) {
    var b = h(a.table().container()),
        c = a.settings()[0],
        d = c._select.selector,
        e;
    b.on("mousedown.dtSelect", d, function (g) {
      if (g.shiftKey || g.metaKey || g.ctrlKey) b.css("-moz-user-select", "none").one("selectstart.dtSelect", d, function () {
        return !1;
      });
      r.getSelection && (e = r.getSelection());
    }).on("mouseup.dtSelect", d, function () {
      b.css("-moz-user-select", "");
    }).on("click.dtSelect", d, function (g) {
      var f = a.select.items();

      if (e) {
        var k = r.getSelection();
        if ((!k.anchorNode || h(k.anchorNode).closest("table")[0] === a.table().node()) && k !== e) return;
      }

      k = a.settings()[0];
      var n = a.settings()[0].oClasses.sWrapper.trim().replace(/ +/g, ".");

      if (h(g.target).closest("div." + n)[0] == a.table().container() && (n = a.cell(h(g.target).closest("td, th")), n.any())) {
        var q = h.Event("user-select.dt");
        u(a, q, [f, n, g]);
        q.isDefaultPrevented() || (q = n.index(), "row" === f ? (f = q.row, E(g, a, k, "row", f)) : "column" === f ? (f = n.index().column, E(g, a, k, "column", f)) : "cell" === f && (f = n.index(), E(g, a, k, "cell", f)), k._select_lastCell = q);
      }
    });
    h("body").on("click.dtSelect" + D(a.table().node()), function (g) {
      !c._select.blurable || h(g.target).parents().filter(a.table().container()).length || 0 === h(g.target).parents("html").length || h(g.target).parents("div.DTE").length || z(c, !0);
    });
  }

  function u(a, b, c, d) {
    if (!d || a.flatten().length) "string" === typeof b && (b += ".dt"), c.unshift(a), h(a.table().node()).trigger(b, c);
  }

  function N(a) {
    var b = a.settings()[0];

    if (b._select.info && b.aanFeatures.i && "api" !== a.select.style()) {
      var c = a.rows({
        selected: !0
      }).flatten().length,
          d = a.columns({
        selected: !0
      }).flatten().length,
          e = a.cells({
        selected: !0
      }).flatten().length,
          g = function g(f, k, n) {
        f.append(h('<span class="select-item"/>').append(a.i18n("select." + k + "s", {
          _: "%d " + k + "s selected",
          0: "",
          1: "1 " + k + " selected"
        }, n)));
      };

      h.each(b.aanFeatures.i, function (f, k) {
        k = h(k);
        f = h('<span class="select-info"/>');
        g(f, "row", c);
        g(f, "column", d);
        g(f, "cell", e);
        var n = k.children("span.select-info");
        n.length && n.remove();
        "" !== f.text() && k.append(f);
      });
    }
  }

  function O(a) {
    var b = new m.Api(a);
    a._select_init = !0;
    a.aoRowCreatedCallback.push({
      fn: function fn(c, d, e) {
        d = a.aoData[e];
        d._select_selected && h(c).addClass(a._select.className);
        c = 0;

        for (e = a.aoColumns.length; c < e; c++) {
          (a.aoColumns[c]._select_selected || d._selected_cells && d._selected_cells[c]) && h(d.anCells[c]).addClass(a._select.className);
        }
      },
      sName: "select-deferRender"
    });
    b.on("preXhr.dt.dtSelect", function (c, d) {
      if (d === b.settings()[0]) {
        var e = b.rows({
          selected: !0
        }).ids(!0).filter(function (f) {
          return f !== l;
        }),
            g = b.cells({
          selected: !0
        }).eq(0).map(function (f) {
          var k = b.row(f.row).id(!0);
          return k ? {
            row: k,
            column: f.column
          } : l;
        }).filter(function (f) {
          return f !== l;
        });
        b.one("draw.dt.dtSelect", function () {
          b.rows(e).select();
          g.any() && g.each(function (f) {
            b.cells(f.row, f.column).select();
          });
        });
      }
    });
    b.on("draw.dtSelect.dt select.dtSelect.dt deselect.dtSelect.dt info.dt", function () {
      N(b);
      b.state.save();
    });
    b.on("destroy.dtSelect", function () {
      b.rows({
        selected: !0
      }).deselect();
      C(b);
      b.off(".dtSelect");
      h("body").off(".dtSelect" + D(b.table().node()));
    });
  }

  function K(a, b, c, d) {
    var e = a[b + "s"]({
      search: "applied"
    }).indexes();
    d = h.inArray(d, e);
    var g = h.inArray(c, e);

    if (a[b + "s"]({
      selected: !0
    }).any() || -1 !== d) {
      if (d > g) {
        var f = g;
        g = d;
        d = f;
      }

      e.splice(g + 1, e.length);
      e.splice(0, d);
    } else e.splice(h.inArray(c, e) + 1, e.length);

    a[b](c, {
      selected: !0
    }).any() ? (e.splice(h.inArray(c, e), 1), a[b + "s"](e).deselect()) : a[b + "s"](e).select();
  }

  function z(a, b) {
    if (b || "single" === a._select.style) a = new m.Api(a), a.rows({
      selected: !0
    }).deselect(), a.columns({
      selected: !0
    }).deselect(), a.cells({
      selected: !0
    }).deselect();
  }

  function E(a, b, c, d, e) {
    var g = b.select.style(),
        f = b.select.toggleable(),
        k = b[d](e, {
      selected: !0
    }).any();
    if (!k || f) "os" === g ? a.ctrlKey || a.metaKey ? b[d](e).select(!k) : a.shiftKey ? "cell" === d ? I(b, e, c._select_lastCell || null) : K(b, d, e, c._select_lastCell ? c._select_lastCell[d] : null) : (a = b[d + "s"]({
      selected: !0
    }), k && 1 === a.flatten().length ? b[d](e).deselect() : (a.deselect(), b[d](e).select())) : "multi+shift" == g ? a.shiftKey ? "cell" === d ? I(b, e, c._select_lastCell || null) : K(b, d, e, c._select_lastCell ? c._select_lastCell[d] : null) : b[d](e).select(!k) : b[d](e).select(!k);
  }

  function D(a) {
    return a.id.replace(/[^a-zA-Z0-9\-_]/g, "-");
  }

  function A(a, b) {
    return function (c) {
      return c.i18n("buttons." + a, b);
    };
  }

  function F(a) {
    a = a._eventNamespace;
    return "draw.dt.DT" + a + " select.dt.DT" + a + " deselect.dt.DT" + a;
  }

  function P(a, b) {
    return -1 !== h.inArray("rows", b.limitTo) && a.rows({
      selected: !0
    }).any() || -1 !== h.inArray("columns", b.limitTo) && a.columns({
      selected: !0
    }).any() || -1 !== h.inArray("cells", b.limitTo) && a.cells({
      selected: !0
    }).any() ? !0 : !1;
  }

  var m = h.fn.dataTable;
  m.select = {};
  m.select.version = "1.3.4";

  m.select.init = function (a) {
    var b = a.settings()[0];

    if (!b._select) {
      var c = a.state.loaded(),
          d = function d(t, G, p) {
        if (null !== p && p.select !== l) {
          a.rows().deselect();
          a.columns().deselect();
          a.cells().deselect();
          p.select.rows !== l && a.rows(p.select.rows).select();
          p.select.columns !== l && a.columns(p.select.columns).select();
          if (p.select.cells !== l) for (t = 0; t < p.select.cells.length; t++) {
            a.cell(p.select.cells[t].row, p.select.cells[t].column).select();
          }
          a.state.save();
        }
      };

      a.one("init", function () {
        a.on("stateSaveParams", function (t, G, p) {
          p.select = {};
          p.select.rows = a.rows({
            selected: !0
          }).ids(!0).toArray();
          p.select.columns = a.columns({
            selected: !0
          })[0];
          p.select.cells = a.cells({
            selected: !0
          })[0].map(function (L) {
            return {
              row: a.row(L.row).id(!0),
              column: L.column
            };
          });
        });
        d(l, l, c);
        a.on("stateLoaded stateLoadParams", d);
      });
      var e = b.oInit.select,
          g = m.defaults.select;
      e = e === l ? g : e;
      g = "row";
      var f = "api",
          k = !1,
          n = !0,
          q = !0,
          y = "td, th",
          M = "selected",
          B = !1;
      b._select = {};
      !0 === e ? (f = "os", B = !0) : "string" === typeof e ? (f = e, B = !0) : h.isPlainObject(e) && (e.blurable !== l && (k = e.blurable), e.toggleable !== l && (n = e.toggleable), e.info !== l && (q = e.info), e.items !== l && (g = e.items), f = e.style !== l ? e.style : "os", B = !0, e.selector !== l && (y = e.selector), e.className !== l && (M = e.className));
      a.select.selector(y);
      a.select.items(g);
      a.select.style(f);
      a.select.blurable(k);
      a.select.toggleable(n);
      a.select.info(q);
      b._select.className = M;

      h.fn.dataTable.ext.order["select-checkbox"] = function (t, G) {
        return this.api().column(G, {
          order: "index"
        }).nodes().map(function (p) {
          return "row" === t._select.items ? h(p).parent().hasClass(t._select.className) : "cell" === t._select.items ? h(p).hasClass(t._select.className) : !1;
        });
      };

      !B && h(a.table().node()).hasClass("selectable") && a.select.style("os");
    }
  };

  h.each([{
    type: "row",
    prop: "aoData"
  }, {
    type: "column",
    prop: "aoColumns"
  }], function (a, b) {
    m.ext.selector[b.type].push(function (c, d, e) {
      d = d.selected;
      var g = [];
      if (!0 !== d && !1 !== d) return e;

      for (var f = 0, k = e.length; f < k; f++) {
        var n = c[b.prop][e[f]];
        (!0 === d && !0 === n._select_selected || !1 === d && !n._select_selected) && g.push(e[f]);
      }

      return g;
    });
  });
  m.ext.selector.cell.push(function (a, b, c) {
    b = b.selected;
    var d = [];
    if (b === l) return c;

    for (var e = 0, g = c.length; e < g; e++) {
      var f = a.aoData[c[e].row];
      (!0 === b && f._selected_cells && !0 === f._selected_cells[c[e].column] || !(!1 !== b || f._selected_cells && f._selected_cells[c[e].column])) && d.push(c[e]);
    }

    return d;
  });
  var w = m.Api.register,
      x = m.Api.registerPlural;
  w("select()", function () {
    return this.iterator("table", function (a) {
      m.select.init(new m.Api(a));
    });
  });
  w("select.blurable()", function (a) {
    return a === l ? this.context[0]._select.blurable : this.iterator("table", function (b) {
      b._select.blurable = a;
    });
  });
  w("select.toggleable()", function (a) {
    return a === l ? this.context[0]._select.toggleable : this.iterator("table", function (b) {
      b._select.toggleable = a;
    });
  });
  w("select.info()", function (a) {
    return a === l ? this.context[0]._select.info : this.iterator("table", function (b) {
      b._select.info = a;
    });
  });
  w("select.items()", function (a) {
    return a === l ? this.context[0]._select.items : this.iterator("table", function (b) {
      b._select.items = a;
      u(new m.Api(b), "selectItems", [a]);
    });
  });
  w("select.style()", function (a) {
    return a === l ? this.context[0]._select.style : this.iterator("table", function (b) {
      b._select || m.select.init(new m.Api(b));
      b._select_init || O(b);
      b._select.style = a;
      var c = new m.Api(b);
      C(c);
      "api" !== a && J(c);
      u(new m.Api(b), "selectStyle", [a]);
    });
  });
  w("select.selector()", function (a) {
    return a === l ? this.context[0]._select.selector : this.iterator("table", function (b) {
      C(new m.Api(b));
      b._select.selector = a;
      "api" !== b._select.style && J(new m.Api(b));
    });
  });
  x("rows().select()", "row().select()", function (a) {
    var b = this;
    if (!1 === a) return this.deselect();
    this.iterator("row", function (c, d) {
      z(c);
      c.aoData[d]._select_selected = !0;
      h(c.aoData[d].nTr).addClass(c._select.className);
    });
    this.iterator("table", function (c, d) {
      u(b, "select", ["row", b[d]], !0);
    });
    return this;
  });
  x("columns().select()", "column().select()", function (a) {
    var b = this;
    if (!1 === a) return this.deselect();
    this.iterator("column", function (c, d) {
      z(c);
      c.aoColumns[d]._select_selected = !0;
      d = new m.Api(c).column(d);
      h(d.header()).addClass(c._select.className);
      h(d.footer()).addClass(c._select.className);
      d.nodes().to$().addClass(c._select.className);
    });
    this.iterator("table", function (c, d) {
      u(b, "select", ["column", b[d]], !0);
    });
    return this;
  });
  x("cells().select()", "cell().select()", function (a) {
    var b = this;
    if (!1 === a) return this.deselect();
    this.iterator("cell", function (c, d, e) {
      z(c);
      d = c.aoData[d];
      d._selected_cells === l && (d._selected_cells = []);
      d._selected_cells[e] = !0;
      d.anCells && h(d.anCells[e]).addClass(c._select.className);
    });
    this.iterator("table", function (c, d) {
      u(b, "select", ["cell", b.cells(b[d]).indexes().toArray()], !0);
    });
    return this;
  });
  x("rows().deselect()", "row().deselect()", function () {
    var a = this;
    this.iterator("row", function (b, c) {
      b.aoData[c]._select_selected = !1;
      b._select_lastCell = null;
      h(b.aoData[c].nTr).removeClass(b._select.className);
    });
    this.iterator("table", function (b, c) {
      u(a, "deselect", ["row", a[c]], !0);
    });
    return this;
  });
  x("columns().deselect()", "column().deselect()", function () {
    var a = this;
    this.iterator("column", function (b, c) {
      b.aoColumns[c]._select_selected = !1;
      var d = new m.Api(b),
          e = d.column(c);
      h(e.header()).removeClass(b._select.className);
      h(e.footer()).removeClass(b._select.className);
      d.cells(null, c).indexes().each(function (g) {
        var f = b.aoData[g.row],
            k = f._selected_cells;
        !f.anCells || k && k[g.column] || h(f.anCells[g.column]).removeClass(b._select.className);
      });
    });
    this.iterator("table", function (b, c) {
      u(a, "deselect", ["column", a[c]], !0);
    });
    return this;
  });
  x("cells().deselect()", "cell().deselect()", function () {
    var a = this;
    this.iterator("cell", function (b, c, d) {
      c = b.aoData[c];
      c._selected_cells !== l && (c._selected_cells[d] = !1);
      c.anCells && !b.aoColumns[d]._select_selected && h(c.anCells[d]).removeClass(b._select.className);
    });
    this.iterator("table", function (b, c) {
      u(a, "deselect", ["cell", a[c]], !0);
    });
    return this;
  });
  var H = 0;
  h.extend(m.ext.buttons, {
    selected: {
      text: A("selected", "Selected"),
      className: "buttons-selected",
      limitTo: ["rows", "columns", "cells"],
      init: function init(a, b, c) {
        var d = this;
        c._eventNamespace = ".select" + H++;
        a.on(F(c), function () {
          d.enable(P(a, c));
        });
        this.disable();
      },
      destroy: function destroy(a, b, c) {
        a.off(c._eventNamespace);
      }
    },
    selectedSingle: {
      text: A("selectedSingle", "Selected single"),
      className: "buttons-selected-single",
      init: function init(a, b, c) {
        var d = this;
        c._eventNamespace = ".select" + H++;
        a.on(F(c), function () {
          var e = a.rows({
            selected: !0
          }).flatten().length + a.columns({
            selected: !0
          }).flatten().length + a.cells({
            selected: !0
          }).flatten().length;
          d.enable(1 === e);
        });
        this.disable();
      },
      destroy: function destroy(a, b, c) {
        a.off(c._eventNamespace);
      }
    },
    selectAll: {
      text: A("selectAll", "Select all"),
      className: "buttons-select-all",
      action: function action() {
        this[this.select.items() + "s"]().select();
      }
    },
    selectNone: {
      text: A("selectNone", "Deselect all"),
      className: "buttons-select-none",
      action: function action() {
        z(this.settings()[0], !0);
      },
      init: function init(a, b, c) {
        var d = this;
        c._eventNamespace = ".select" + H++;
        a.on(F(c), function () {
          var e = a.rows({
            selected: !0
          }).flatten().length + a.columns({
            selected: !0
          }).flatten().length + a.cells({
            selected: !0
          }).flatten().length;
          d.enable(0 < e);
        });
        this.disable();
      },
      destroy: function destroy(a, b, c) {
        a.off(c._eventNamespace);
      }
    }
  });
  h.each(["Row", "Column", "Cell"], function (a, b) {
    var c = b.toLowerCase();
    m.ext.buttons["select" + b + "s"] = {
      text: A("select" + b + "s", "Select " + c + "s"),
      className: "buttons-select-" + c + "s",
      action: function action() {
        this.select.items(c);
      },
      init: function init(d) {
        var e = this;
        d.on("selectItems.dt.DT", function (g, f, k) {
          e.active(k === c);
        });
      }
    };
  });
  h(v).on("preInit.dt.dtSelect", function (a, b) {
    "dt" === a.namespace && m.select.init(new m.Api(b));
  });
  return m.select;
});
/*!
 DataTables styling wrapper for Select
 ©2018 SpryMedia Ltd - datatables.net/license
*/


(function (c) {
  "function" === typeof define && define.amd ? define(["jquery", "datatables.net-dt", "datatables.net-select"], function (a) {
    return c(a, window, document);
  }) : "object" === (typeof exports === "undefined" ? "undefined" : _typeof(exports)) ? module.exports = function (a, b) {
    a || (a = window);
    b && b.fn.dataTable || (b = require("datatables.net-dt")(a, b).$);
    b.fn.dataTable.select || require("datatables.net-select")(a, b);
    return c(b, a, a.document);
  } : c(jQuery, window, document);
})(function (c, a, b, d) {
  return c.fn.dataTable;
});
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

(function () {
  var d;
  window.AmCharts ? d = window.AmCharts : (d = {}, window.AmCharts = d, d.themes = {}, d.maps = {}, d.inheriting = {}, d.charts = [], d.onReadyArray = [], d.useUTC = !1, d.updateRate = 60, d.uid = 0, d.lang = {}, d.translations = {}, d.mapTranslations = {}, d.windows = {}, d.initHandlers = [], d.amString = "am", d.pmString = "pm");

  d.Class = function (a) {
    var b = function b() {
      arguments[0] !== d.inheriting && (this.events = {}, this.construct.apply(this, arguments));
    };

    a.inherits ? (b.prototype = new a.inherits(d.inheriting), b.base = a.inherits.prototype, delete a.inherits) : (b.prototype.createEvents = function () {
      for (var a = 0; a < arguments.length; a++) {
        this.events[arguments[a]] = [];
      }
    }, b.prototype.listenTo = function (a, b, c) {
      this.removeListener(a, b, c);
      a.events[b].push({
        handler: c,
        scope: this
      });
    }, b.prototype.addListener = function (a, b, c) {
      this.removeListener(this, a, b);
      a && this.events[a] && this.events[a].push({
        handler: b,
        scope: c
      });
    }, b.prototype.removeListener = function (a, b, c) {
      if (a && a.events && (a = a.events[b])) for (b = a.length - 1; 0 <= b; b--) {
        a[b].handler === c && a.splice(b, 1);
      }
    }, b.prototype.fire = function (a) {
      for (var b = this.events[a.type], c = 0; c < b.length; c++) {
        var d = b[c];
        d.handler.call(d.scope, a);
      }
    });

    for (var c in a) {
      b.prototype[c] = a[c];
    }

    return b;
  };

  d.addChart = function (a) {
    window.requestAnimationFrame ? d.animationRequested || (d.animationRequested = !0, window.requestAnimationFrame(d.update)) : d.updateInt || (d.updateInt = setInterval(function () {
      d.update();
    }, Math.round(1E3 / d.updateRate)));
    d.charts.push(a);
  };

  d.removeChart = function (a) {
    for (var b = d.charts, c = b.length - 1; 0 <= c; c--) {
      b[c] == a && b.splice(c, 1);
    }

    0 === b.length && (d.requestAnimation && (window.cancelAnimationFrame(d.requestAnimation), d.animationRequested = !1), d.updateInt && (clearInterval(d.updateInt), d.updateInt = NaN));
  };

  d.isModern = !0;

  d.getIEVersion = function () {
    var a = 0,
        b,
        c;
    "Microsoft Internet Explorer" == navigator.appName && (b = navigator.userAgent, c = /MSIE ([0-9]{1,}[.0-9]{0,})/, null !== c.exec(b) && (a = parseFloat(RegExp.$1)));
    return a;
  };

  d.applyLang = function (a, b) {
    var c = d.translations;
    b.dayNames = d.extend({}, d.dayNames);
    b.shortDayNames = d.extend({}, d.shortDayNames);
    b.monthNames = d.extend({}, d.monthNames);
    b.shortMonthNames = d.extend({}, d.shortMonthNames);
    b.amString = "am";
    b.pmString = "pm";
    c && (c = c[a]) && (d.lang = c, b.langObj = c, c.monthNames && (b.dayNames = d.extend({}, c.dayNames), b.shortDayNames = d.extend({}, c.shortDayNames), b.monthNames = d.extend({}, c.monthNames), b.shortMonthNames = d.extend({}, c.shortMonthNames)), c.am && (b.amString = c.am), c.pm && (b.pmString = c.pm));
    d.amString = b.amString;
    d.pmString = b.pmString;
  };

  d.IEversion = d.getIEVersion();
  9 > d.IEversion && 0 < d.IEversion && (d.isModern = !1, d.isIE = !0);
  d.dx = 0;
  d.dy = 0;
  if (document.addEventListener || window.opera) d.isNN = !0, d.isIE = !1, d.dx = .5, d.dy = .5;
  document.attachEvent && (d.isNN = !1, d.isIE = !0, d.isModern || (d.dx = 0, d.dy = 0));
  window.chrome && (d.chrome = !0);

  d.handleMouseUp = function (a) {
    for (var b = d.charts, c = 0; c < b.length; c++) {
      var e = b[c];
      e && e.handleReleaseOutside && e.handleReleaseOutside(a);
    }
  };

  d.handleMouseMove = function (a) {
    for (var b = d.charts, c = 0; c < b.length; c++) {
      var e = b[c];
      e && e.handleMouseMove && e.handleMouseMove(a);
    }
  };

  d.handleKeyUp = function (a) {
    for (var b = d.charts, c = 0; c < b.length; c++) {
      var e = b[c];
      e && e.handleKeyUp && e.handleKeyUp(a);
    }
  };

  d.handleWheel = function (a) {
    for (var b = d.charts, c = 0; c < b.length; c++) {
      var e = b[c];

      if (e && e.mouseIsOver) {
        (e.mouseWheelScrollEnabled || e.mouseWheelZoomEnabled) && e.handleWheel && (e.handleMouseMove(a), e.handleWheel(a));
        break;
      }
    }
  };

  d.resetMouseOver = function () {
    for (var a = d.charts, b = 0; b < a.length; b++) {
      var c = a[b];
      c && (c.mouseIsOver = !1);
    }
  };

  d.ready = function (a) {
    d.onReadyArray.push(a);
  };

  d.handleLoad = function () {
    d.isReady = !0;

    for (var a = d.onReadyArray, b = 0; b < a.length; b++) {
      var c = a[b];
      isNaN(d.processDelay) ? c() : setTimeout(c, d.processDelay * b);
    }

    d.onReadyArray = [];
  };

  d.addInitHandler = function (a, b) {
    d.initHandlers.push({
      method: a,
      types: b
    });
  };

  d.callInitHandler = function (a) {
    var b = d.initHandlers;
    if (d.initHandlers) for (var c = 0; c < b.length; c++) {
      var e = b[c];
      e.types ? d.isInArray(e.types, a.type) && e.method(a) : e.method(a);
    }
  };

  d.getUniqueId = function () {
    d.uid++;
    return "AmChartsEl-" + d.uid;
  };

  d.addGlobalListeners = function () {
    d.globalListenersAdded || (d.globalListenersAdded = !0, d.isNN && (document.addEventListener("mousemove", d.handleMouseMove), document.addEventListener("keyup", d.handleKeyUp), document.addEventListener("mouseup", d.handleMouseUp, !0), window.addEventListener("load", d.handleLoad, !0)), d.isIE && (document.attachEvent("onmousemove", d.handleMouseMove), document.attachEvent("onmouseup", d.handleMouseUp), window.attachEvent("onload", d.handleLoad)));
  };

  d.addGlobalListeners();

  d.addWheelListeners = function () {
    d.wheelIsListened || (d.isNN && (window.addEventListener("DOMMouseScroll", d.handleWheel, {
      passive: !1,
      useCapture: !0
    }), document.addEventListener("mousewheel", d.handleWheel, {
      passive: !1,
      useCapture: !0
    })), d.isIE && document.attachEvent("onmousewheel", d.handleWheel));
    d.wheelIsListened = !0;
  };

  d.clear = function () {
    var a = d.charts;
    if (a) for (var b = a.length - 1; 0 <= b; b--) {
      a[b].clear();
    }
    d.updateInt && clearInterval(d.updateInt);
    d.requestAnimation && window.cancelAnimationFrame(d.requestAnimation);
    d.charts = [];
    d.isNN && (document.removeEventListener("mousemove", d.handleMouseMove, !0), document.removeEventListener("keyup", d.handleKeyUp, !0), document.removeEventListener("mouseup", d.handleMouseUp, !0), window.removeEventListener("load", d.handleLoad, !0), window.removeEventListener("DOMMouseScroll", d.handleWheel, !0), document.removeEventListener("mousewheel", d.handleWheel, !0));
    d.isIE && (document.detachEvent("onmousemove", d.handleMouseMove), document.detachEvent("onmouseup", d.handleMouseUp), window.detachEvent("onload", d.handleLoad));
    d.globalListenersAdded = !1;
    d.wheelIsListened = !1;
  };

  d.makeChart = function (a, b, c) {
    var e = b.type,
        g = b.theme;
    d.addGlobalListeners();
    d.isString(g) && (g = d.themes[g], b.theme = g);
    var f;

    switch (e) {
      case "serial":
        f = new d.AmSerialChart(g);
        break;

      case "xy":
        f = new d.AmXYChart(g);
        break;

      case "pie":
        f = new d.AmPieChart(g);
        break;

      case "radar":
        f = new d.AmRadarChart(g);
        break;

      case "gauge":
        f = new d.AmAngularGauge(g);
        break;

      case "funnel":
        f = new d.AmFunnelChart(g);
        break;

      case "map":
        f = new d.AmMap(g);
        break;

      case "stock":
        f = new d.AmStockChart(g);
        break;

      case "gantt":
        f = new d.AmGanttChart(g);
    }

    d.extend(f, b);
    d.isReady ? isNaN(c) ? f.write(a) : setTimeout(function () {
      d.realWrite(f, a);
    }, c) : d.ready(function () {
      isNaN(c) ? f.write(a) : setTimeout(function () {
        d.realWrite(f, a);
      }, c);
    });
    return f;
  };

  d.realWrite = function (a, b) {
    a.write(b);
  };

  d.updateCount = 0;
  d.validateAt = Math.round(d.updateRate / 10);

  d.update = function () {
    var a = d.charts;
    d.updateCount++;
    var b = !1;
    d.updateCount == d.validateAt && (b = !0, d.updateCount = 0);
    if (a) for (var c = a.length - 1; 0 <= c; c--) {
      a[c].update && a[c].update(), b && (a[c].autoResize ? a[c].validateSize && a[c].validateSize() : a[c].premeasure && a[c].premeasure());
    }
    window.requestAnimationFrame && (d.requestAnimation = window.requestAnimationFrame(d.update));
  };

  "complete" == document.readyState && d.handleLoad();
})();

(function () {
  var d = window.AmCharts;

  d.toBoolean = function (a, b) {
    if (void 0 === a) return b;

    switch (String(a).toLowerCase()) {
      case "true":
      case "yes":
      case "1":
        return !0;

      case "false":
      case "no":
      case "0":
      case null:
        return !1;

      default:
        return !!a;
    }
  };

  d.removeFromArray = function (a, b) {
    var c;
    if (void 0 !== b && void 0 !== a) for (c = a.length - 1; 0 <= c; c--) {
      a[c] == b && a.splice(c, 1);
    }
  };

  d.getPath = function () {
    var a = document.getElementsByTagName("script");
    if (a) for (var b = 0; b < a.length; b++) {
      var c = a[b].src;
      if (-1 !== c.search(/\/(amcharts|ammap)\.js/)) return c.replace(/\/(amcharts|ammap)\.js.*/, "/");
    }
  };

  d.normalizeUrl = function (a) {
    return "" !== a && -1 === a.search(/\/$/) ? a + "/" : a;
  };

  d.isAbsolute = function (a) {
    return 0 === a.search(/^http[s]?:|^\//);
  };

  d.isInArray = function (a, b) {
    for (var c = 0; c < a.length; c++) {
      if (a[c] == b) return !0;
    }

    return !1;
  };

  d.getDecimals = function (a) {
    var b = 0;
    isNaN(a) || (a = String(a), -1 != a.indexOf("e-") ? b = Number(a.split("-")[1]) : -1 != a.indexOf(".") && (b = a.split(".")[1].length));
    return b;
  };

  d.wordwrap = function (a, b, c, e) {
    var g, f, h, k;
    a += "";
    if (1 > b) return a;
    g = -1;

    for (a = (k = a.split(/\r\n|\n|\r/)).length; ++g < a; k[g] += h) {
      h = k[g];

      for (k[g] = ""; h.length > b; k[g] += d.trim(h.slice(0, f)) + ((h = h.slice(f)).length ? c : "")) {
        f = 2 == e || (f = h.slice(0, b + 1).match(/\S*(\s)?$/))[1] ? b : f.input.length - f[0].length || 1 == e && b || f.input.length + (f = h.slice(b).match(/^\S*/))[0].length;
      }

      h = d.trim(h);
    }

    return k.join(c);
  };

  d.trim = function (a) {
    return a.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, "");
  };

  d.wrappedText = function (a, b, c, e, g, f, h, k) {
    var l = d.text(a, b, c, e, g, f, h);

    if (l) {
      var m = l.getBBox();

      if (m.width > k) {
        var n = "\n";
        d.isModern || (n = "<br>");
        k = Math.floor(k / (m.width / b.length));
        2 < k && (k -= 2);
        b = d.wordwrap(b, k, n, !0);
        l.remove();
        l = d.text(a, b, c, e, g, f, h);
      }
    }

    return l;
  };

  d.getStyle = function (a, b) {
    var c = "";
    if (document.defaultView && document.defaultView.getComputedStyle) try {
      c = document.defaultView.getComputedStyle(a, "").getPropertyValue(b);
    } catch (e) {} else a.currentStyle && (b = b.replace(/\-(\w)/g, function (a, b) {
      return b.toUpperCase();
    }), c = a.currentStyle[b]);
    return c;
  };

  d.removePx = function (a) {
    if (void 0 !== a) return Number(a.substring(0, a.length - 2));
  };

  d.getURL = function (a, b) {
    if (a) if ("_self" != b && b) {
      if ("_top" == b && window.top) window.top.location.href = a;else if ("_parent" == b && window.parent) window.parent.location.href = a;else if ("_blank" == b) window.open(a);else {
        var c = document.getElementsByName(b)[0];
        c ? c.src = a : (c = d.windows[b]) ? c.opener && !c.opener.closed ? c.location.href = a : d.windows[b] = window.open(a) : d.windows[b] = window.open(a);
      }
    } else window.location.href = a;
  };

  d.ifArray = function (a) {
    return a && "object" == _typeof(a) && 0 < a.length ? !0 : !1;
  };

  d.callMethod = function (a, b) {
    var c;

    for (c = 0; c < b.length; c++) {
      var e = b[c];

      if (e) {
        if (e[a]) e[a]();
        var d = e.length;

        if (0 < d) {
          var f;

          for (f = 0; f < d; f++) {
            var h = e[f];
            if (h && h[a]) h[a]();
          }
        }
      }
    }
  };

  d.toNumber = function (a) {
    return "number" == typeof a ? a : Number(String(a).replace(/[^0-9\-.]+/g, ""));
  };

  d.toColor = function (a) {
    if ("" !== a && void 0 !== a) if (-1 != a.indexOf(",")) {
      a = a.split(",");
      var b;

      for (b = 0; b < a.length; b++) {
        var c = a[b].substring(a[b].length - 6, a[b].length);
        a[b] = "#" + c;
      }
    } else a = a.substring(a.length - 6, a.length), a = "#" + a;
    return a;
  };

  d.toCoordinate = function (a, b, c) {
    var e;
    void 0 !== a && (a = String(a), c && c < b && (b = c), e = Number(a), -1 != a.indexOf("!") && (e = b - Number(a.substr(1))), -1 != a.indexOf("%") && (e = b * Number(a.substr(0, a.length - 1)) / 100));
    return e;
  };

  d.fitToBounds = function (a, b, c) {
    a < b && (a = b);
    a > c && (a = c);
    return a;
  };

  d.isDefined = function (a) {
    return void 0 === a ? !1 : !0;
  };

  d.stripNumbers = function (a) {
    return a.replace(/[0-9]+/g, "");
  };

  d.roundTo = function (a, b) {
    if (0 > b) return a;
    var c = Math.pow(10, b);
    return Math.round(a * c) / c;
  };

  d.toFixed = function (a, b) {
    var c = !1;
    0 > a && (c = !0, a = Math.abs(a));
    var e = String(Math.round(a * Math.pow(10, b)));

    if (0 < b) {
      var d = e.length;

      if (d < b) {
        var f;

        for (f = 0; f < b - d; f++) {
          e = "0" + e;
        }
      }

      d = e.substring(0, e.length - b);
      "" === d && (d = 0);
      e = d + "." + e.substring(e.length - b, e.length);
      return c ? "-" + e : e;
    }

    return String(e);
  };

  d.formatDuration = function (a, b, c, e, g, f) {
    var h = d.intervals,
        k = f.decimalSeparator;

    if (a >= h[b].contains) {
      var l = a - Math.floor(a / h[b].contains) * h[b].contains;
      "ss" == b ? (l = d.formatNumber(l, f), 1 == l.split(k)[0].length && (l = "0" + l)) : l = d.roundTo(l, f.precision);
      ("mm" == b || "hh" == b) && 10 > l && (l = "0" + l);
      c = l + "" + e[b] + "" + c;
      a = Math.floor(a / h[b].contains);
      b = h[b].nextInterval;
      return d.formatDuration(a, b, c, e, g, f);
    }

    "ss" == b && (a = d.formatNumber(a, f), 1 == a.split(k)[0].length && (a = "0" + a));
    "mm" == b && (a = d.roundTo(a, f.precision));
    ("mm" == b || "hh" == b) && 10 > a && (a = "0" + a);
    c = a + "" + e[b] + "" + c;
    if (h[g].count > h[b].count) for (a = h[b].count; a < h[g].count; a++) {
      b = h[b].nextInterval, "ss" == b || "mm" == b || "hh" == b ? c = "00" + e[b] + "" + c : "DD" == b && (c = "0" + e[b] + "" + c);
    }
    ":" == c.charAt(c.length - 1) && (c = c.substring(0, c.length - 1));
    return c;
  };

  d.formatNumber = function (a, b, c, e, g) {
    a = d.roundTo(a, b.precision);
    isNaN(c) && (c = b.precision);
    var f = b.decimalSeparator;
    b = b.thousandsSeparator;
    void 0 == f && (f = ",");
    void 0 == b && (b = " ");
    var h;
    h = 0 > a ? "-" : "";
    a = Math.abs(a);
    var k = String(a),
        l = !1;
    -1 != k.indexOf("e") && (l = !0);
    0 <= c && !l && (k = d.toFixed(a, c));
    var m = "";
    if (l) m = k;else {
      var k = k.split("."),
          l = String(k[0]),
          n;

      for (n = l.length; 0 <= n; n -= 3) {
        m = n != l.length ? 0 !== n ? l.substring(n - 3, n) + b + m : l.substring(n - 3, n) + m : l.substring(n - 3, n);
      }

      void 0 !== k[1] && (m = m + f + k[1]);
      void 0 !== c && 0 < c && "0" != m && (m = d.addZeroes(m, f, c));
    }
    m = h + m;
    "" === h && !0 === e && 0 !== a && (m = "+" + m);
    !0 === g && (m += "%");
    return m;
  };

  d.addZeroes = function (a, b, c) {
    a = a.split(b);
    void 0 === a[1] && 0 < c && (a[1] = "0");
    return a[1].length < c ? (a[1] += "0", d.addZeroes(a[0] + b + a[1], b, c)) : void 0 !== a[1] ? a[0] + b + a[1] : a[0];
  };

  d.scientificToNormal = function (a) {
    var b;
    a = String(a).split("e");
    var c;

    if ("-" == a[1].substr(0, 1)) {
      b = "0.";

      for (c = 0; c < Math.abs(Number(a[1])) - 1; c++) {
        b += "0";
      }

      b += a[0].split(".").join("");
    } else {
      var e = 0;
      b = a[0].split(".");
      b[1] && (e = b[1].length);
      b = a[0].split(".").join("");

      for (c = 0; c < Math.abs(Number(a[1])) - e; c++) {
        b += "0";
      }
    }

    return b;
  };

  d.toScientific = function (a, b) {
    if (0 === a) return "0";
    var c = Math.floor(Math.log(Math.abs(a)) * Math.LOG10E),
        e = String(e).split(".").join(b);
    return String(e) + "e" + c;
  };

  d.randomColor = function () {
    return "#" + ("00000" + (16777216 * Math.random() << 0).toString(16)).substr(-6);
  };

  d.hitTest = function (a, b, c) {
    var e = !1,
        g = a.x,
        f = a.x + a.width,
        h = a.y,
        k = a.y + a.height,
        l = d.isInRectangle;
    e || (e = l(g, h, b));
    e || (e = l(g, k, b));
    e || (e = l(f, h, b));
    e || (e = l(f, k, b));
    e || !0 === c || (e = d.hitTest(b, a, !0));
    return e;
  };

  d.isInRectangle = function (a, b, c) {
    return a >= c.x - 5 && a <= c.x + c.width + 5 && b >= c.y - 5 && b <= c.y + c.height + 5 ? !0 : !1;
  };

  d.isPercents = function (a) {
    if (-1 != String(a).indexOf("%")) return !0;
  };

  d.formatValue = function (a, b, c, e, g, f, h, k) {
    if (b) {
      void 0 === g && (g = "");
      var l;

      for (l = 0; l < c.length; l++) {
        var m = c[l],
            n = b[m];
        void 0 !== n && (n = f ? d.addPrefix(n, k, h, e) : d.formatNumber(n, e), a = a.replace(new RegExp("\\[\\[" + g + "" + m + "\\]\\]", "g"), n));
      }
    }

    return a;
  };

  d.formatDataContextValue = function (a, b) {
    if (a) {
      var c = a.match(/\[\[.*?\]\]/g),
          e;

      for (e = 0; e < c.length; e++) {
        var d = c[e],
            d = d.substr(2, d.length - 4);
        void 0 !== b[d] && (a = a.replace(new RegExp("\\[\\[" + d + "\\]\\]", "g"), b[d]));
      }
    }

    return a;
  };

  d.massReplace = function (a, b) {
    for (var c in b) {
      if (b.hasOwnProperty(c)) {
        var e = b[c];
        void 0 === e && (e = "");
        a = a.replace(c, e);
      }
    }

    return a;
  };

  d.cleanFromEmpty = function (a) {
    return a.replace(/\[\[[^\]]*\]\]/g, "");
  };

  d.addPrefix = function (a, b, c, e, g) {
    var f = d.formatNumber(a, e),
        h = "",
        k,
        l,
        m;
    if (0 === a) return "0";
    0 > a && (h = "-");
    a = Math.abs(a);
    if (1 < a) for (k = b.length - 1; -1 < k; k--) {
      if (a >= b[k].number && (l = a / b[k].number, m = Number(e.precision), 1 > m && (m = 1), c = d.roundTo(l, m), m = d.formatNumber(c, {
        precision: -1,
        decimalSeparator: e.decimalSeparator,
        thousandsSeparator: e.thousandsSeparator
      }), !g || l == c)) {
        f = h + "" + m + "" + b[k].prefix;
        break;
      }
    } else for (k = 0; k < c.length; k++) {
      if (a <= c[k].number) {
        l = a / c[k].number;
        m = Math.abs(Math.floor(Math.log(l) * Math.LOG10E));
        l = d.roundTo(l, m);
        f = h + "" + l + "" + c[k].prefix;
        break;
      }
    }
    return f;
  };

  d.remove = function (a) {
    a && a.remove();
  };

  d.getEffect = function (a) {
    ">" == a && (a = "easeOutSine");
    "<" == a && (a = "easeInSine");
    "elastic" == a && (a = "easeOutElastic");
    return a;
  };

  d.getObjById = function (a, b) {
    var c, e;

    for (e = 0; e < a.length; e++) {
      var d = a[e];

      if (d.id == b) {
        c = d;
        break;
      }
    }

    return c;
  };

  d.applyTheme = function (a, b, c) {
    b || (b = d.theme);

    try {
      b = JSON.parse(JSON.stringify(b));
    } catch (e) {}

    b && b[c] && d.extend(a, b[c]);
  };

  d.isString = function (a) {
    return "string" == typeof a ? !0 : !1;
  };

  d.extend = function (a, b, c) {
    var e;
    a || (a = {});

    for (e in b) {
      c ? a.hasOwnProperty(e) || (a[e] = b[e]) : a[e] = b[e];
    }

    return a;
  };

  d.copyProperties = function (a, b) {
    for (var c in a) {
      a.hasOwnProperty(c) && "events" != c && void 0 !== a[c] && "function" != typeof a[c] && "cname" != c && (b[c] = a[c]);
    }
  };

  d.processObject = function (a, b, c, e) {
    if (!1 === a instanceof b && (a = e ? d.extend(new b(c), a) : d.extend(a, new b(c), !0), a.listeners)) for (var g in a.listeners) {
      b = a.listeners[g], a.addListener(b.event, b.method);
    }
    return a;
  };

  d.fixNewLines = function (a) {
    var b = RegExp("\\n", "g");
    a && (a = a.replace(b, "<br />"));
    return a;
  };

  d.fixBrakes = function (a) {
    if (d.isModern) {
      var b = RegExp("<br>", "g");
      a && (a = a.replace(b, "\n"));
    } else a = d.fixNewLines(a);

    return a;
  };

  d.deleteObject = function (a, b) {
    if (a) {
      if (void 0 === b || null === b) b = 20;
      if (0 !== b) if ("[object Array]" === Object.prototype.toString.call(a)) for (var c = 0; c < a.length; c++) {
        d.deleteObject(a[c], b - 1), a[c] = null;
      } else if (a && !a.tagName) try {
        for (c in a.theme = null, a) {
          a[c] && ("object" == _typeof(a[c]) && d.deleteObject(a[c], b - 1), "function" != typeof a[c] && (a[c] = null));
        }
      } catch (e) {}
    }
  };

  d.bounce = function (a, b, c, e, d) {
    return (b /= d) < 1 / 2.75 ? 7.5625 * e * b * b + c : b < 2 / 2.75 ? e * (7.5625 * (b -= 1.5 / 2.75) * b + .75) + c : b < 2.5 / 2.75 ? e * (7.5625 * (b -= 2.25 / 2.75) * b + .9375) + c : e * (7.5625 * (b -= 2.625 / 2.75) * b + .984375) + c;
  };

  d.easeInOutQuad = function (a, b, c, e, d) {
    b /= d / 2;
    if (1 > b) return e / 2 * b * b + c;
    b--;
    return -e / 2 * (b * (b - 2) - 1) + c;
  };

  d.easeInSine = function (a, b, c, e, d) {
    return -e * Math.cos(b / d * (Math.PI / 2)) + e + c;
  };

  d.easeOutSine = function (a, b, c, e, d) {
    return e * Math.sin(b / d * (Math.PI / 2)) + c;
  };

  d.easeOutElastic = function (a, b, c, e, d) {
    a = 1.70158;
    var f = 0,
        h = e;
    if (0 === b) return c;
    if (1 == (b /= d)) return c + e;
    f || (f = .3 * d);
    h < Math.abs(e) ? (h = e, a = f / 4) : a = f / (2 * Math.PI) * Math.asin(e / h);
    return h * Math.pow(2, -10 * b) * Math.sin(2 * (b * d - a) * Math.PI / f) + e + c;
  };

  d.fixStepE = function (a) {
    a = a.toExponential(0).split("e");
    var b = Number(a[1]);
    9 == Number(a[0]) && b++;
    return d.generateNumber(1, b);
  };

  d.generateNumber = function (a, b) {
    var c = "",
        e;
    e = 0 > b ? Math.abs(b) - 1 : Math.abs(b);
    var d;

    for (d = 0; d < e; d++) {
      c += "0";
    }

    return 0 > b ? Number("0." + c + String(a)) : Number(String(a) + c);
  };

  d.setCN = function (a, b, c, e) {
    if (a.addClassNames && b && (b = b.node) && c) {
      var d = b.getAttribute("class");
      a = a.classNamePrefix + "-";
      e && (a = "");
      d ? b.setAttribute("class", d + " " + a + c) : b.setAttribute("class", a + c);
    }
  };

  d.removeCN = function (a, b, c) {
    b && (b = b.node) && c && (b = b.classList) && b.remove(a.classNamePrefix + "-" + c);
  };

  d.parseDefs = function (a, b) {
    for (var c in a) {
      var e = _typeof(a[c]);

      if (0 < a[c].length && "object" == e) for (var g = 0; g < a[c].length; g++) {
        e = document.createElementNS(d.SVG_NS, c), b.appendChild(e), d.parseDefs(a[c][g], e);
      } else "object" == e ? (e = document.createElementNS(d.SVG_NS, c), b.appendChild(e), d.parseDefs(a[c], e)) : b.setAttribute(c, a[c]);
    }
  };
})();

(function () {
  var d = window.AmCharts;
  d.AxisBase = d.Class({
    construct: function construct(a) {
      this.createEvents("clickItem", "rollOverItem", "rollOutItem", "rollOverGuide", "rollOutGuide", "clickGuide");
      this.titleDY = this.y = this.x = this.dy = this.dx = 0;
      this.axisThickness = 1;
      this.axisColor = "#000000";
      this.axisAlpha = 1;
      this.gridCount = this.tickLength = 5;
      this.gridAlpha = .15;
      this.gridThickness = 1;
      this.gridColor = "#000000";
      this.dashLength = 0;
      this.labelFrequency = 1;
      this.showLastLabel = this.showFirstLabel = !0;
      this.fillColor = "#FFFFFF";
      this.fillAlpha = 0;
      this.labelsEnabled = !0;
      this.labelRotation = 0;
      this.autoGridCount = !0;
      this.offset = 0;
      this.guides = [];
      this.visible = !0;
      this.counter = 0;
      this.guides = [];
      this.ignoreAxisWidth = this.inside = !1;
      this.minHorizontalGap = 75;
      this.minVerticalGap = 35;
      this.titleBold = !0;
      this.minorGridEnabled = !1;
      this.minorGridAlpha = .07;
      this.autoWrap = !1;
      this.titleAlign = "middle";
      this.labelOffset = 0;
      this.bcn = "axis-";
      this.centerLabels = !1;
      this.firstDayOfWeek = 1;
      this.centerLabelOnFullPeriod = this.markPeriodChange = this.boldPeriodBeginning = !0;
      this.titleWidth = 0;
      this.periods = [{
        period: "fff",
        count: 1
      }, {
        period: "fff",
        count: 5
      }, {
        period: "fff",
        count: 10
      }, {
        period: "fff",
        count: 50
      }, {
        period: "fff",
        count: 100
      }, {
        period: "fff",
        count: 500
      }, {
        period: "ss",
        count: 1
      }, {
        period: "ss",
        count: 5
      }, {
        period: "ss",
        count: 10
      }, {
        period: "ss",
        count: 30
      }, {
        period: "mm",
        count: 1
      }, {
        period: "mm",
        count: 5
      }, {
        period: "mm",
        count: 10
      }, {
        period: "mm",
        count: 30
      }, {
        period: "hh",
        count: 1
      }, {
        period: "hh",
        count: 3
      }, {
        period: "hh",
        count: 6
      }, {
        period: "hh",
        count: 12
      }, {
        period: "DD",
        count: 1
      }, {
        period: "DD",
        count: 2
      }, {
        period: "DD",
        count: 3
      }, {
        period: "DD",
        count: 4
      }, {
        period: "DD",
        count: 5
      }, {
        period: "WW",
        count: 1
      }, {
        period: "MM",
        count: 1
      }, {
        period: "MM",
        count: 2
      }, {
        period: "MM",
        count: 3
      }, {
        period: "MM",
        count: 6
      }, {
        period: "YYYY",
        count: 1
      }, {
        period: "YYYY",
        count: 2
      }, {
        period: "YYYY",
        count: 5
      }, {
        period: "YYYY",
        count: 10
      }, {
        period: "YYYY",
        count: 50
      }, {
        period: "YYYY",
        count: 100
      }];
      this.dateFormats = [{
        period: "fff",
        format: "NN:SS.QQQ"
      }, {
        period: "ss",
        format: "JJ:NN:SS"
      }, {
        period: "mm",
        format: "JJ:NN"
      }, {
        period: "hh",
        format: "JJ:NN"
      }, {
        period: "DD",
        format: "MMM DD"
      }, {
        period: "WW",
        format: "MMM DD"
      }, {
        period: "MM",
        format: "MMM"
      }, {
        period: "YYYY",
        format: "YYYY"
      }];
      this.nextPeriod = {
        fff: "ss",
        ss: "mm",
        mm: "hh",
        hh: "DD",
        DD: "MM",
        MM: "YYYY"
      };
      d.applyTheme(this, a, "AxisBase");
    },
    zoom: function zoom(a, b) {
      this.start = a;
      this.end = b;
      this.dataChanged = !0;
      this.draw();
    },
    fixAxisPosition: function fixAxisPosition() {
      var a = this.position;
      "H" == this.orientation ? ("left" == a && (a = "bottom"), "right" == a && (a = "top")) : ("bottom" == a && (a = "left"), "top" == a && (a = "right"));
      this.position = a;
    },
    init: function init() {
      this.createBalloon();
    },
    draw: function draw() {
      var a = this.chart;
      this.prevBY = this.prevBX = NaN;
      this.allLabels = [];
      this.counter = 0;
      this.destroy();
      this.fixAxisPosition();
      this.setBalloonBounds();
      this.labels = [];
      var b = a.container,
          c = b.set();
      a.gridSet.push(c);
      this.set = c;
      b = b.set();
      a.axesLabelsSet.push(b);
      this.labelsSet = b;
      this.axisLine = new this.axisRenderer(this);
      this.autoGridCount ? ("V" == this.orientation ? (a = this.height / this.minVerticalGap, 3 > a && (a = 3)) : a = this.width / this.minHorizontalGap, this.gridCountR = Math.max(a, 1)) : this.gridCountR = this.gridCount;
      this.axisWidth = this.axisLine.axisWidth;
      this.addTitle();
    },
    setOrientation: function setOrientation(a) {
      this.orientation = a ? "H" : "V";
    },
    addTitle: function addTitle() {
      var a = this.title;
      this.titleLabel = null;

      if (a) {
        var b = this.chart,
            c = this.titleColor;
        void 0 === c && (c = b.color);
        var e = this.titleFontSize;
        isNaN(e) && (e = b.fontSize + 1);
        a = d.text(b.container, a, c, b.fontFamily, e, this.titleAlign, this.titleBold);
        d.setCN(b, a, this.bcn + "title");
        this.titleLabel = a;
      }
    },
    positionTitle: function positionTitle() {
      var a = this.titleLabel;

      if (a) {
        var b,
            c,
            e = this.labelsSet,
            g = {};
        0 < e.length() ? g = e.getBBox() : (g.x = 0, g.y = 0, g.width = this.width, g.height = this.height, d.VML && (g.y += this.y, g.x += this.x));
        e.push(a);
        var e = g.x,
            f = g.y;
        d.VML && (f -= this.y, e -= this.x);
        var h = g.width,
            g = g.height,
            k = this.width,
            l = this.height,
            m = 0,
            n = a.getBBox().height / 2,
            q = this.inside,
            p = this.titleAlign;

        switch (this.position) {
          case "top":
            b = "left" == p ? -1 : "right" == p ? k : k / 2;
            c = f - 10 - n;
            break;

          case "bottom":
            b = "left" == p ? -1 : "right" == p ? k : k / 2;
            c = f + g + 10 + n;
            break;

          case "left":
            b = e - 10 - n;
            q && (b -= 5);
            m = -90;
            c = ("left" == p ? l + 1 : "right" == p ? -1 : l / 2) + this.titleDY;
            this.titleWidth = n + 10;
            break;

          case "right":
            b = e + h + 10 + n, q && (b += 7), c = ("left" == p ? l + 2 : "right" == p ? -2 : l / 2) + this.titleDY, this.titleWidth = n + 10, m = -90;
        }

        this.marginsChanged ? (a.translate(b, c), this.tx = b, this.ty = c) : a.translate(this.tx, this.ty);
        this.marginsChanged = !1;
        isNaN(this.titleRotation) || (m = this.titleRotation);
        0 !== m && a.rotate(m);
      }
    },
    pushAxisItem: function pushAxisItem(a, b) {
      var c = this,
          e = a.graphics();
      0 < e.length() && (b ? c.labelsSet.push(e) : c.set.push(e));
      if (e = a.getLabel()) c.labelsSet.push(e), e.click(function (b) {
        c.handleMouse(b, a, "clickItem");
      }).touchend(function (b) {
        c.handleMouse(b, a, "clickItem");
      }).mouseover(function (b) {
        c.handleMouse(b, a, "rollOverItem");
      }).mouseout(function (b) {
        c.handleMouse(b, a, "rollOutItem");
      });
    },
    handleMouse: function handleMouse(a, b, c) {
      this.fire({
        type: c,
        value: b.value,
        serialDataItem: b.serialDataItem,
        axis: this,
        target: b.label,
        chart: this.chart,
        event: a
      });
    },
    addGuide: function addGuide(a) {
      for (var b = this.guides, c = !1, e = b.length, g = 0; g < b.length; g++) {
        b[g] == a && (c = !0, e = g);
      }

      a = d.processObject(a, d.Guide, this.theme);
      a.id || (a.id = "guideAuto" + e + "_" + new Date().getTime());
      c || b.push(a);
    },
    removeGuide: function removeGuide(a) {
      var b = this.guides,
          c;

      for (c = 0; c < b.length; c++) {
        b[c] == a && b.splice(c, 1);
      }
    },
    handleGuideOver: function handleGuideOver(a) {
      clearTimeout(this.chart.hoverInt);
      var b = {
        x: 0,
        y: 0,
        width: 0,
        height: 0
      };
      a.graphics && (b = a.graphics.getBBox());
      var c = this.x + b.x + b.width / 2,
          b = this.y + b.y + b.height / 2,
          e = a.fillColor;
      void 0 === e && (e = a.lineColor);
      this.chart.showBalloon(a.balloonText, e, !0, c, b);
      this.fire({
        type: "rollOverGuide",
        guide: a,
        chart: this.chart
      });
    },
    handleGuideOut: function handleGuideOut(a) {
      this.chart.hideBalloon();
      this.fire({
        type: "rollOutGuide",
        guide: a,
        chart: this.chart
      });
    },
    handleGuideClick: function handleGuideClick(a) {
      this.chart.hideBalloon();
      this.fire({
        type: "clickGuide",
        guide: a,
        chart: this.chart
      });
    },
    addEventListeners: function addEventListeners(a, b) {
      var c = this;
      a.mouseover(function () {
        c.handleGuideOver(b);
      });
      a.mouseup(function () {
        c.handleGuideClick(b);
      });
      a.touchstart(function () {
        c.handleGuideOver(b);
      });
      a.mouseout(function () {
        c.handleGuideOut(b);
      });
    },
    getBBox: function getBBox() {
      var a;
      this.labelsSet && (a = this.labelsSet.getBBox());
      a ? d.VML || (a = {
        x: a.x + this.x,
        y: a.y + this.y,
        width: a.width,
        height: a.height
      }) : a = {
        x: 0,
        y: 0,
        width: 0,
        height: 0
      };
      return a;
    },
    destroy: function destroy() {
      d.remove(this.set);
      d.remove(this.labelsSet);
      var a = this.axisLine;
      a && d.remove(a.axisSet);
      d.remove(this.grid0);
    },
    chooseMinorFrequency: function chooseMinorFrequency(a) {
      for (var b = 10; 0 < b; b--) {
        if (a / b == Math.round(a / b)) return a / b;
      }
    },
    parseDatesDraw: function parseDatesDraw() {
      var a,
          b = this.chart,
          c = this.showFirstLabel,
          e = this.showLastLabel,
          g,
          f = "",
          h = d.extractPeriod(this.minPeriod),
          k = d.getPeriodDuration(h.period, h.count),
          l,
          m,
          n,
          q,
          p,
          t = this.firstDayOfWeek,
          r = this.boldPeriodBeginning;
      a = this.minorGridEnabled;
      var w,
          z = this.gridAlpha,
          x,
          u = this.choosePeriod(0),
          A = u.period,
          u = u.count,
          y = d.getPeriodDuration(A, u);
      y < k && (A = h.period, u = h.count, y = k);
      h = A;
      "WW" == h && (h = "DD");
      this.stepWidth = this.getStepWidth(this.timeDifference);
      var B = Math.ceil(this.timeDifference / y) + 5,
          D = l = d.resetDateToMin(new Date(this.startTime - y), A, u, t).getTime();
      if (h == A && 1 == u && this.centerLabelOnFullPeriod || this.autoWrap || this.centerLabels) n = y * this.stepWidth, this.autoWrap && !this.centerLabels && (n = -n);
      this.cellWidth = k * this.stepWidth;
      q = Math.round(l / y);
      k = -1;
      q / 2 == Math.round(q / 2) && (k = -2, l -= y);
      q = this.firstTime;
      var C = 0,
          I = 0;
      a && 1 < u && (w = this.chooseMinorFrequency(u), x = d.getPeriodDuration(A, w), "DD" == A && (x += d.getPeriodDuration("hh")), "fff" == A && (x = 1));
      if (0 < this.gridCountR) for (B - 5 - k > this.autoRotateCount && !isNaN(this.autoRotateAngle) && (this.labelRotationR = this.autoRotateAngle), a = k; a <= B; a++) {
        p = q + y * (a + Math.floor((D - q) / y)) - C;
        "DD" == A && (p += 36E5);
        p = d.resetDateToMin(new Date(p), A, u, t).getTime();
        "MM" == A && (g = (p - l) / y, 1.5 <= (p - l) / y && (p = p - (g - 1) * y + d.getPeriodDuration("DD", 3), p = d.resetDateToMin(new Date(p), A, 1).getTime(), C += y));
        g = (p - this.startTime) * this.stepWidth;

        if ("radar" == b.type) {
          if (g = this.axisWidth - g, 0 > g || g > this.axisWidth) continue;
        } else this.rotate ? "date" == this.type && "middle" == this.gridPosition && (I = -y * this.stepWidth / 2) : "date" == this.type && (g = this.axisWidth - g);

        f = !1;
        this.nextPeriod[h] && (f = this.checkPeriodChange(this.nextPeriod[h], 1, p, l, h));
        l = !1;
        f && this.markPeriodChange ? (f = this.dateFormatsObject[this.nextPeriod[h]], this.twoLineMode && (f = this.dateFormatsObject[h] + "\n" + f, f = d.fixBrakes(f)), l = !0) : f = this.dateFormatsObject[h];
        r || (l = !1);
        this.currentDateFormat = f;
        f = d.formatDate(new Date(p), f, b);
        if (a == k && !c || a == B && !e) f = " ";
        this.labelFunction && (f = this.labelFunction(f, new Date(p), this, A, u, m).toString());
        this.boldLabels && (l = !0);
        m = new this.axisItemRenderer(this, g, f, !1, n, I, !1, l);
        this.pushAxisItem(m);
        m = l = p;
        if (!isNaN(w)) for (g = 1; g < u; g += w) {
          this.gridAlpha = this.minorGridAlpha, f = p + x * g, f = d.resetDateToMin(new Date(f), A, w, t).getTime(), f = new this.axisItemRenderer(this, (f - this.startTime) * this.stepWidth, void 0, void 0, void 0, void 0, void 0, void 0, void 0, !0), this.pushAxisItem(f);
        }
        this.gridAlpha = z;
      }
    },
    choosePeriod: function choosePeriod(a) {
      var b = d.getPeriodDuration(this.periods[a].period, this.periods[a].count),
          c = this.periods;
      return this.timeDifference < b && 0 < a ? c[a - 1] : Math.ceil(this.timeDifference / b) <= this.gridCountR ? c[a] : a + 1 < c.length ? this.choosePeriod(a + 1) : c[a];
    },
    getStepWidth: function getStepWidth(a) {
      var b;
      this.startOnAxis ? (b = this.axisWidth / (a - 1), 1 == a && (b = this.axisWidth)) : b = this.axisWidth / a;
      return b;
    },
    timeZoom: function timeZoom(a, b) {
      this.startTime = a;
      this.endTime = b;
    },
    minDuration: function minDuration() {
      var a = d.extractPeriod(this.minPeriod);
      return d.getPeriodDuration(a.period, a.count);
    },
    checkPeriodChange: function checkPeriodChange(a, b, c, e, g) {
      c = new Date(c);
      var f = new Date(e),
          h = this.firstDayOfWeek;
      e = b;
      "DD" == a && (b = 1);
      c = d.resetDateToMin(c, a, b, h).getTime();
      b = d.resetDateToMin(f, a, b, h).getTime();
      return "DD" == a && "hh" != g && c - b < d.getPeriodDuration(a, e) - d.getPeriodDuration("hh", 1) ? !1 : c != b ? !0 : !1;
    },
    generateDFObject: function generateDFObject() {
      this.dateFormatsObject = {};
      var a;

      for (a = 0; a < this.dateFormats.length; a++) {
        var b = this.dateFormats[a];
        this.dateFormatsObject[b.period] = b.format;
      }
    },
    hideBalloon: function hideBalloon() {
      this.balloon && this.balloon.hide && this.balloon.hide();
      this.prevBY = this.prevBX = NaN;
    },
    formatBalloonText: function formatBalloonText(a) {
      return a;
    },
    showBalloon: function showBalloon(a, b, c, e) {
      var d = this.offset;

      switch (this.position) {
        case "bottom":
          b = this.height + d;
          break;

        case "top":
          b = -d;
          break;

        case "left":
          a = -d;
          break;

        case "right":
          a = this.width + d;
      }

      c || (c = this.currentDateFormat);

      if ("V" == this.orientation) {
        if (0 > b || b > this.height) return;

        if (isNaN(b)) {
          this.hideBalloon();
          return;
        }

        b = this.adjustBalloonCoordinate(b, e);
        e = this.coordinateToValue(b);
      } else {
        if (0 > a || a > this.width) return;

        if (isNaN(a)) {
          this.hideBalloon();
          return;
        }

        a = this.adjustBalloonCoordinate(a, e);
        e = this.coordinateToValue(a);
      }

      var f;
      if (d = this.chart.chartCursor) f = d.index;

      if (this.balloon && void 0 !== e && this.balloon.enabled) {
        if (this.balloonTextFunction) {
          if ("date" == this.type || !0 === this.parseDates) e = new Date(e);
          e = this.balloonTextFunction(e);
        } else this.balloonText ? e = this.formatBalloonText(this.balloonText, f, c) : isNaN(e) || (e = this.formatValue(e, c));

        if (a != this.prevBX || b != this.prevBY) this.balloon.setPosition(a, b), this.prevBX = a, this.prevBY = b, e && this.balloon.showBalloon(e);
      }
    },
    adjustBalloonCoordinate: function adjustBalloonCoordinate(a) {
      return a;
    },
    createBalloon: function createBalloon() {
      var a = this.chart,
          b = a.chartCursor;
      b && (b = b.cursorPosition, "mouse" != b && (this.stickBalloonToCategory = !0), "start" == b && (this.stickBalloonToStart = !0), "ValueAxis" == this.cname && (this.stickBalloonToCategory = !1));
      this.balloon && (this.balloon.destroy && this.balloon.destroy(), d.extend(this.balloon, a.balloon, !0));
    },
    setBalloonBounds: function setBalloonBounds() {
      var a = this.balloon;

      if (a) {
        var b = this.chart;
        a.cornerRadius = 0;
        a.shadowAlpha = 0;
        a.borderThickness = 1;
        a.borderAlpha = 1;
        a.adjustBorderColor = !1;
        a.showBullet = !1;
        this.balloon = a;
        a.chart = b;
        a.mainSet = b.plotBalloonsSet;
        a.pointerWidth = this.tickLength;
        if (this.parseDates || "date" == this.type) a.pointerWidth = 0;
        a.className = this.id;
        b = "V";
        "V" == this.orientation && (b = "H");
        this.stickBalloonToCategory || (a.animationDuration = 0);
        var c,
            e,
            d,
            f,
            h = this.inside,
            k = this.width,
            l = this.height;

        switch (this.position) {
          case "bottom":
            c = 0;
            e = k;
            h ? (d = 0, f = l) : (d = l, f = l + 1E3);
            break;

          case "top":
            c = 0;
            e = k;
            h ? (d = 0, f = l) : (d = -1E3, f = 0);
            break;

          case "left":
            d = 0;
            f = l;
            h ? (c = 0, e = k) : (c = -1E3, e = 0);
            break;

          case "right":
            d = 0, f = l, h ? (c = 0, e = k) : (c = k, e = k + 1E3);
        }

        a.drop || (a.pointerOrientation = b);
        a.setBounds(c, d, e, f);
      }
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.ValueAxis = d.Class({
    inherits: d.AxisBase,
    construct: function construct(a) {
      this.cname = "ValueAxis";
      this.createEvents("axisChanged", "logarithmicAxisFailed", "axisZoomed", "axisIntZoomed");
      d.ValueAxis.base.construct.call(this, a);
      this.dataChanged = !0;
      this.stackType = "none";
      this.position = "left";
      this.unitPosition = "right";
      this.includeAllValues = this.recalculateToPercents = this.includeHidden = this.includeGuidesInMinMax = this.integersOnly = !1;
      this.durationUnits = {
        DD: "d. ",
        hh: ":",
        mm: ":",
        ss: ""
      };
      this.scrollbar = !1;
      this.baseValue = 0;
      this.radarCategoriesEnabled = !0;
      this.axisFrequency = 1;
      this.gridType = "polygons";
      this.useScientificNotation = !1;
      this.axisTitleOffset = 10;
      this.pointPosition = "axis";
      this.minMaxMultiplier = 1;
      this.logGridLimit = 2;
      this.totalTextOffset = this.treatZeroAs = 0;
      this.minPeriod = "ss";
      this.relativeStart = 0;
      this.relativeEnd = 1;
      d.applyTheme(this, a, this.cname);
    },
    updateData: function updateData() {
      0 >= this.gridCountR && (this.gridCountR = 1);
      this.totals = [];
      this.data = this.chart.chartData;
      var a = this.chart;
      "xy" != a.type && (this.stackGraphs("smoothedLine"), this.stackGraphs("line"), this.stackGraphs("column"), this.stackGraphs("step"));
      this.recalculateToPercents && this.recalculate();
      if (this.synchronizationMultiplier && this.synchronizeWith) d.isString(this.synchronizeWith) && (this.synchronizeWith = a.getValueAxisById(this.synchronizeWith)), this.synchronizeWith && (this.synchronizeWithAxis(this.synchronizeWith), this.foundGraphs = !0);else if (this.foundGraphs = !1, this.getMinMax(), 0 === this.start && this.end == this.data.length - 1 && isNaN(this.minZoom) && isNaN(this.maxZoom) || isNaN(this.fullMin) && isNaN(this.fullMax)) this.fullMin = this.min, this.fullMax = this.max, "date" != this.type && this.strictMinMax && (isNaN(this.minimum) || (this.fullMin = this.minimum), isNaN(this.maximum) || (this.fullMax = this.maximum)), this.logarithmic && (this.fullMin = this.logMin, 0 === this.fullMin && (this.fullMin = this.treatZeroAs)), "date" == this.type && (this.minimumDate || (this.fullMin = this.minRR), this.maximumDate || (this.fullMax = this.maxRR), this.strictMinMax && (this.minimumDate && (this.fullMin = this.minimumDate.getTime()), this.maximumDate && (this.fullMax = this.maximumDate.getTime())));
    },
    draw: function draw() {
      d.ValueAxis.base.draw.call(this);
      var a = this.chart,
          b = this.set;
      this.labelRotationR = this.labelRotation;
      d.setCN(a, this.set, "value-axis value-axis-" + this.id);
      d.setCN(a, this.labelsSet, "value-axis value-axis-" + this.id);
      d.setCN(a, this.axisLine.axisSet, "value-axis value-axis-" + this.id);
      var c = this.type;
      "duration" == c && (this.duration = "ss");
      !0 === this.dataChanged && (this.updateData(), this.dataChanged = !1);
      "date" == c && (this.logarithmic = !1, this.min = this.minRR, this.max = this.maxRR, this.reversed = !1, this.getDateMinMax());

      if (this.logarithmic) {
        var e = this.treatZeroAs,
            g = this.getExtremes(0, this.data.length - 1).min;
        !isNaN(this.minimum) && this.minimum < g && (g = this.minimum);
        this.logMin = g;
        this.minReal < g && (this.minReal = g);
        isNaN(this.minReal) && (this.minReal = g);
        0 < e && 0 === g && (this.minReal = g = e);

        if (0 >= g || 0 >= this.minimum) {
          this.fire({
            type: "logarithmicAxisFailed",
            chart: a
          });
          return;
        }
      }

      this.grid0 = null;
      var f,
          h,
          k = a.dx,
          l = a.dy,
          e = !1,
          g = this.logarithmic;
      if (isNaN(this.min) || isNaN(this.max) || !this.foundGraphs || Infinity == this.min || -Infinity == this.max) e = !0;else {
        "date" == this.type && this.min == this.max && (this.max += this.minDuration(), this.min -= this.minDuration());
        var m = this.labelFrequency,
            n = this.showFirstLabel,
            q = this.showLastLabel,
            p = 1,
            t = 0;
        this.minCalc = this.min;
        this.maxCalc = this.max;
        if (this.strictMinMax && (isNaN(this.minimum) || (this.min = this.minimum), isNaN(this.maximum) || (this.max = this.maximum), this.min == this.max)) return;
        isNaN(this.minZoom) || (this.minReal = this.min = this.minZoom);
        isNaN(this.maxZoom) || (this.max = this.maxZoom);

        if (this.logarithmic) {
          h = this.fullMin;
          var r = this.fullMax;
          isNaN(this.minimum) || (h = this.minimum);
          isNaN(this.maximum) || (r = this.maximum);
          var r = Math.log(r) * Math.LOG10E - Math.log(h) * Math.LOG10E,
              w = Math.log(this.max) / Math.LN10 - Math.log(h) * Math.LOG10E;
          this.relativeStart = d.roundTo((Math.log(this.minReal) / Math.LN10 - Math.log(h) * Math.LOG10E) / r, 5);
          this.relativeEnd = d.roundTo(w / r, 5);
        } else this.relativeStart = d.roundTo(d.fitToBounds((this.min - this.fullMin) / (this.fullMax - this.fullMin), 0, 1), 5), this.relativeEnd = d.roundTo(d.fitToBounds((this.max - this.fullMin) / (this.fullMax - this.fullMin), 0, 1), 5);

        var r = Math.round((this.maxCalc - this.minCalc) / this.step) + 1,
            z;
        !0 === g ? (z = Math.log(this.max) * Math.LOG10E - Math.log(this.minReal) * Math.LOG10E, this.stepWidth = this.axisWidth / z, z > this.logGridLimit && (r = Math.ceil(Math.log(this.max) * Math.LOG10E) + 1, t = Math.round(Math.log(this.minReal) * Math.LOG10E), r > this.gridCountR && (p = Math.ceil(r / this.gridCountR)))) : this.stepWidth = this.axisWidth / (this.max - this.min);
        var x = 0;
        1 > this.step && -1 < this.step && (x = d.getDecimals(this.step));
        this.integersOnly && (x = 0);
        x > this.maxDecCount && (x = this.maxDecCount);
        w = this.precision;
        isNaN(w) || (x = w);
        isNaN(this.maxZoom) && (this.max = d.roundTo(this.max, this.maxDecCount), this.min = d.roundTo(this.min, this.maxDecCount));
        h = {};
        h.precision = x;
        h.decimalSeparator = a.nf.decimalSeparator;
        h.thousandsSeparator = a.nf.thousandsSeparator;
        this.numberFormatter = h;
        var u;
        this.exponential = !1;

        for (h = t; h < r; h += p) {
          var A = d.roundTo(this.step * h + this.min, x);
          -1 != String(A).indexOf("e") && (this.exponential = !0);
        }

        this.duration && (this.maxInterval = d.getMaxInterval(this.max, this.duration));
        var x = this.step,
            y,
            A = this.minorGridAlpha;
        this.minorGridEnabled && (y = this.getMinorGridStep(x, this.stepWidth * x));
        if (this.autoGridCount || 0 !== this.gridCount) if ("date" == c) this.generateDFObject(), this.timeDifference = this.max - this.min, this.maxTime = this.lastTime = this.max, this.startTime = this.firstTime = this.min, this.parseDatesDraw();else for (r >= this.autoRotateCount && !isNaN(this.autoRotateAngle) && (this.labelRotationR = this.autoRotateAngle), c = this.minCalc, g && (r++, c = this.maxCalc - r * x), this.gridCountReal = r, h = this.startCount = t; h < r; h += p) {
          if (t = x * h + c, t = d.roundTo(t, this.maxDecCount + 1), !this.integersOnly || Math.round(t) == t) if (isNaN(w) || Number(d.toFixed(t, w)) == t) {
            if (!0 === g) if (z > this.logGridLimit) {
              if (t = Math.pow(10, h), t > this.max) continue;
            } else if (0 >= t && (t = c + x * h + x / 2, 0 >= t)) continue;
            u = this.formatValue(t, !1, h);
            Math.round(h / m) != h / m && (u = void 0);
            if (0 === h && !n || h == r - 1 && !q) u = " ";
            f = this.getCoordinate(t);
            var B;
            this.rotate && this.autoWrap && (B = this.stepWidth * x - 10);
            u = new this.axisItemRenderer(this, f, u, void 0, B, void 0, void 0, this.boldLabels);
            this.pushAxisItem(u);

            if (t == this.baseValue && "radar" != a.type) {
              var D,
                  C,
                  I = this.width,
                  H = this.height;
              "H" == this.orientation ? 0 <= f && f <= I + 1 && (D = [f, f, f + k], C = [H, 0, l]) : 0 <= f && f <= H + 1 && (D = [0, I, I + k], C = [f, f, f + l]);
              D && (f = d.fitToBounds(2 * this.gridAlpha, 0, 1), isNaN(this.zeroGridAlpha) || (f = this.zeroGridAlpha), f = d.line(a.container, D, C, this.gridColor, f, 1, this.dashLength), f.translate(this.x, this.y), this.grid0 = f, a.axesSet.push(f), f.toBack(), d.setCN(a, f, this.bcn + "zero-grid-" + this.id), d.setCN(a, f, this.bcn + "zero-grid"));
            }

            if (!isNaN(y) && 0 < A && h < r - 1) {
              f = x / y;
              g && (y = x * (h + p) + this.minCalc, y = d.roundTo(y, this.maxDecCount + 1), z > this.logGridLimit && (y = Math.pow(10, h + p)), f = 9, y = (y - t) / f);
              I = this.gridAlpha;
              this.gridAlpha = this.minorGridAlpha;

              for (H = 1; H < f; H++) {
                var Q = this.getCoordinate(t + y * H),
                    Q = new this.axisItemRenderer(this, Q, "", !1, 0, 0, !1, !1, 0, !0);
                this.pushAxisItem(Q);
              }

              this.gridAlpha = I;
            }
          }
        }
        z = this.guides;
        B = z.length;

        if (0 < B) {
          D = this.fillAlpha;

          for (h = this.fillAlpha = 0; h < B; h++) {
            C = z[h], k = NaN, y = C.above, isNaN(C.toValue) || (k = this.getCoordinate(C.toValue), u = new this.axisItemRenderer(this, k, "", !0, NaN, NaN, C), this.pushAxisItem(u, y)), l = NaN, isNaN(C.value) || (l = this.getCoordinate(C.value), u = new this.axisItemRenderer(this, l, C.label, !0, NaN, (k - l) / 2, C), this.pushAxisItem(u, y)), isNaN(k) && (l -= 3, k = l + 3), u && (m = u.label) && this.addEventListeners(m, C), isNaN(k - l) || 0 > l && 0 > k || (k = new this.guideFillRenderer(this, l, k, C), this.pushAxisItem(k, y), y = k.graphics(), C.graphics = y, this.addEventListeners(y, C));
          }

          this.fillAlpha = D;
        }

        u = this.baseValue;
        this.min > this.baseValue && this.max > this.baseValue && (u = this.min);
        this.min < this.baseValue && this.max < this.baseValue && (u = this.max);
        g && u < this.minReal && (u = this.minReal);
        this.baseCoord = this.getCoordinate(u, !0);
        u = {
          type: "axisChanged",
          target: this,
          chart: a
        };
        u.min = g ? this.minReal : this.min;
        u.max = this.max;
        this.fire(u);
        this.axisCreated = !0;
      }
      g = this.axisLine.set;
      u = this.labelsSet;
      b.translate(this.x, this.y);
      u.translate(this.x, this.y);
      this.positionTitle();
      "radar" != a.type && g.toFront();
      !this.visible || e ? (b.hide(), g.hide(), u.hide()) : (b.show(), g.show(), u.show());
      this.axisY = this.y;
      this.axisX = this.x;
    },
    getDateMinMax: function getDateMinMax() {
      this.minimumDate && (this.minimumDate instanceof Date || (this.minimumDate = d.getDate(this.minimumDate, this.chart.dataDateFormat, "fff")), this.min = this.minimumDate.getTime());
      this.maximumDate && (this.maximumDate instanceof Date || (this.maximumDate = d.getDate(this.maximumDate, this.chart.dataDateFormat, "fff")), this.max = this.maximumDate.getTime());
    },
    formatValue: function formatValue(a, b, c) {
      var e = this.exponential,
          g = this.logarithmic,
          f = this.numberFormatter,
          h = this.chart;
      if (f) return !0 === this.logarithmic && (e = -1 != String(a).indexOf("e") ? !0 : !1), this.useScientificNotation && (e = !0), this.usePrefixes && (e = !1), e ? (c = -1 == String(a).indexOf("e") ? a.toExponential(15) : String(a), e = c.split("e"), c = Number(e[0]), e = Number(e[1]), c = d.roundTo(c, 14), b || isNaN(this.precision) || (c = d.roundTo(c, this.precision)), 10 == c && (c = 1, e += 1), c = c + "e" + e, 0 === a && (c = "0"), 1 == a && (c = "1")) : (g && (e = String(a).split("."), e[1] ? (f.precision = e[1].length, 0 > c && (f.precision = Math.abs(c)), b && 1 < a && (f.precision = 0), b || isNaN(this.precision) || (f.precision = this.precision)) : f.precision = -1), c = this.usePrefixes ? d.addPrefix(a, h.prefixesOfBigNumbers, h.prefixesOfSmallNumbers, f, !b) : d.formatNumber(a, f, f.precision)), this.duration && (b && (f.precision = 0), c = d.formatDuration(a, this.duration, "", this.durationUnits, this.maxInterval, f)), "date" == this.type && (c = d.formatDate(new Date(a), this.currentDateFormat, h)), this.recalculateToPercents ? c += "%" : (b = this.unit) && (c = "left" == this.unitPosition ? b + c : c + b), this.labelFunction && (c = "date" == this.type ? this.labelFunction(c, new Date(a), this).toString() : this.labelFunction(a, c, this).toString()), c;
    },
    getMinorGridStep: function getMinorGridStep(a, b) {
      var c = [5, 4, 2];
      60 > b && c.shift();

      for (var e = Math.floor(Math.log(Math.abs(a)) * Math.LOG10E), d = 0; d < c.length; d++) {
        var f = a / c[d],
            h = Math.floor(Math.log(Math.abs(f)) * Math.LOG10E);
        if (!(1 < Math.abs(e - h))) if (1 > a) {
          if (h = Math.pow(10, -h) * f, h == Math.round(h)) return f;
        } else if (f == Math.round(f)) return f;
      }

      return 1;
    },
    stackGraphs: function stackGraphs(a) {
      var b = this.stackType;
      "stacked" == b && (b = "regular");
      "line" == b && (b = "none");
      "100% stacked" == b && (b = "100%");
      this.stackType = b;
      var c = [],
          e = [],
          g = [],
          f = [],
          h,
          k = this.chart.graphs,
          l,
          m,
          n,
          q,
          p,
          t = this.baseValue,
          r = !1;
      if ("line" == a || "step" == a || "smoothedLine" == a) r = !0;
      if (r && ("regular" == b || "100%" == b)) for (q = 0; q < k.length; q++) {
        n = k[q], n.stackGraph = null, n.hidden || (m = n.type, n.chart == this.chart && n.valueAxis == this && a == m && n.stackable && (l && (n.stackGraph = l), l = n));
      }
      n = this.start - 10;
      l = this.end + 10;
      q = this.data.length - 1;
      n = d.fitToBounds(n, 0, q);
      l = d.fitToBounds(l, 0, q);

      for (p = n; p <= l; p++) {
        var w = 0;

        for (q = 0; q < k.length; q++) {
          if (n = k[q], n.hidden) n.newStack && (g[p] = NaN, e[p] = NaN);else if (m = n.type, n.chart == this.chart && n.valueAxis == this && a == m && n.stackable) if (m = this.data[p].axes[this.id].graphs[n.id], h = m.values.value, isNaN(h)) n.newStack && (g[p] = NaN, e[p] = NaN);else {
            var z = d.getDecimals(h);
            w < z && (w = z);
            isNaN(f[p]) ? f[p] = Math.abs(h) : f[p] += Math.abs(h);
            f[p] = d.roundTo(f[p], w);
            z = n.fillToGraph;
            r && z && (z = this.data[p].axes[this.id].graphs[z.id]) && (m.values.open = z.values.value);
            "regular" == b && (r && (isNaN(c[p]) ? (c[p] = h, m.values.close = h, m.values.open = this.baseValue) : (isNaN(h) ? m.values.close = c[p] : m.values.close = h + c[p], m.values.open = c[p], c[p] = m.values.close)), "column" == a && (n.newStack && (g[p] = NaN, e[p] = NaN), m.values.close = h, 0 > h ? (m.values.close = h, isNaN(e[p]) ? m.values.open = t : (m.values.close += e[p], m.values.open = e[p]), e[p] = m.values.close) : (m.values.close = h, isNaN(g[p]) ? m.values.open = t : (m.values.close += g[p], m.values.open = g[p]), g[p] = m.values.close)));
          }
        }
      }

      for (p = this.start; p <= this.end; p++) {
        for (q = 0; q < k.length; q++) {
          (n = k[q], n.hidden) ? n.newStack && (g[p] = NaN, e[p] = NaN) : (m = n.type, n.chart == this.chart && n.valueAxis == this && a == m && n.stackable && (m = this.data[p].axes[this.id].graphs[n.id], h = m.values.value, isNaN(h) || (c = h / f[p] * 100, m.values.percents = c, m.values.total = f[p], n.newStack && (g[p] = NaN, e[p] = NaN), "100%" == b && (isNaN(e[p]) && (e[p] = 0), isNaN(g[p]) && (g[p] = 0), 0 > c ? (m.values.close = d.fitToBounds(c + e[p], -100, 100), m.values.open = e[p], e[p] = m.values.close) : (m.values.close = d.fitToBounds(c + g[p], -100, 100), m.values.open = g[p], g[p] = m.values.close)))));
        }
      }
    },
    recalculate: function recalculate() {
      var a = this.chart,
          b = a.graphs,
          c;

      for (c = 0; c < b.length; c++) {
        var e = b[c];

        if (e.valueAxis == this) {
          var g = "value";
          if ("candlestick" == e.type || "ohlc" == e.type) g = "open";
          var f,
              h,
              k = this.end + 2,
              k = d.fitToBounds(this.end + 1, 0, this.data.length - 1),
              l = this.start;
          0 < l && l--;
          var m;
          h = this.start;
          e.compareFromStart && (h = 0);

          if (!isNaN(a.startTime) && (m = a.categoryAxis)) {
            var n = m.minDuration(),
                n = new Date(a.startTime + n / 2),
                q = d.resetDateToMin(new Date(a.startTime), m.minPeriod).getTime();
            d.resetDateToMin(new Date(n), m.minPeriod).getTime() > q && h++;
          }

          if (m = a.recalculateFromDate) m = d.getDate(m, a.dataDateFormat, "fff"), h = a.getClosestIndex(a.chartData, "time", m.getTime(), !0, 0, a.chartData.length), k = a.chartData.length - 1;

          for (m = h; m <= k && (h = this.data[m].axes[this.id].graphs[e.id], f = h.values[g], e.recalculateValue && (f = h.dataContext[e.valueField + e.recalculateValue]), isNaN(f)); m++) {
            ;
          }

          this.recBaseValue = f;

          for (g = l; g <= k; g++) {
            h = this.data[g].axes[this.id].graphs[e.id];
            h.percents = {};
            var l = h.values,
                p;

            for (p in l) {
              h.percents[p] = "percents" != p ? l[p] / f * 100 - 100 : l[p];
            }
          }
        }
      }
    },
    getMinMax: function getMinMax() {
      var a = !1,
          b = this.chart,
          c = b.graphs,
          e;

      for (e = 0; e < c.length; e++) {
        var g = c[e].type;
        ("line" == g || "step" == g || "smoothedLine" == g) && this.expandMinMax && (a = !0);
      }

      a && (0 < this.start && this.start--, this.end < this.data.length - 1 && this.end++);
      "serial" == b.type && (!0 !== b.categoryAxis.parseDates || a || this.end < this.data.length - 1 && this.end++);
      this.includeAllValues && (this.start = 0, this.end = this.data.length - 1);
      a = this.minMaxMultiplier;
      b = this.getExtremes(this.start, this.end);
      this.min = b.min;
      this.max = b.max;
      this.minRR = this.min;
      this.maxRR = this.max;
      a = (this.max - this.min) * (a - 1);
      this.min -= a;
      this.max += a;
      a = this.guides.length;
      if (this.includeGuidesInMinMax && 0 < a) for (b = 0; b < a; b++) {
        c = this.guides[b], c.toValue < this.min && (this.min = c.toValue), c.value < this.min && (this.min = c.value), c.toValue > this.max && (this.max = c.toValue), c.value > this.max && (this.max = c.value);
      }
      isNaN(this.minimum) || (this.min = this.minimum);
      isNaN(this.maximum) || (this.max = this.maximum);
      "date" == this.type && this.getDateMinMax();
      this.min > this.max && (a = this.max, this.max = this.min, this.min = a);
      isNaN(this.minZoom) || (this.min = this.minZoom);
      isNaN(this.maxZoom) || (this.max = this.maxZoom);
      this.minCalc = this.min;
      this.maxCalc = this.max;
      this.minReal = this.min;
      this.maxReal = this.max;
      0 === this.min && 0 === this.max && (this.max = 9);
      this.min > this.max && (this.min = this.max - 1);
      a = this.min;
      b = this.max;
      c = this.max - this.min;
      e = 0 === c ? Math.pow(10, Math.floor(Math.log(Math.abs(this.max)) * Math.LOG10E)) / 10 : Math.pow(10, Math.floor(Math.log(Math.abs(c)) * Math.LOG10E)) / 10;
      isNaN(this.maximum) && (this.max = Math.ceil(this.max / e) * e + e);
      isNaN(this.minimum) && (this.min = Math.floor(this.min / e) * e - e);
      0 > this.min && 0 <= a && (this.min = 0);
      0 < this.max && 0 >= b && (this.max = 0);
      "100%" == this.stackType && (this.min = 0 > this.min ? -100 : 0, this.max = 0 > this.max ? 0 : 100);
      c = this.max - this.min;
      e = Math.pow(10, Math.floor(Math.log(Math.abs(c)) * Math.LOG10E)) / 10;
      this.step = Math.ceil(c / this.gridCountR / e) * e;
      c = Math.pow(10, Math.floor(Math.log(Math.abs(this.step)) * Math.LOG10E));
      c = d.fixStepE(c);
      e = Math.ceil(this.step / c);
      5 < e && (e = 10);
      5 >= e && 2 < e && (e = 5);
      this.step = Math.ceil(this.step / (c * e)) * c * e;
      isNaN(this.setStep) || (this.step = this.setStep);
      1 > c ? (this.maxDecCount = Math.abs(Math.log(Math.abs(c)) * Math.LOG10E), this.maxDecCount = Math.round(this.maxDecCount), this.step = d.roundTo(this.step, this.maxDecCount + 1)) : this.maxDecCount = 0;
      this.min = this.step * Math.floor(this.min / this.step);
      this.max = this.step * Math.ceil(this.max / this.step);
      0 > this.min && 0 <= a && (this.min = 0);
      0 < this.max && 0 >= b && (this.max = 0);
      1 < this.minReal && 1 < this.max - this.minReal && (this.minReal = Math.floor(this.minReal));
      c = Math.pow(10, Math.floor(Math.log(Math.abs(this.minReal)) * Math.LOG10E));
      0 === this.min && (this.minReal = c);
      0 === this.min && 1 < this.minReal && (this.minReal = 1);
      0 < this.min && 0 < this.minReal - this.step && (this.minReal = this.min + this.step < this.minReal ? this.min + this.step : this.min);
      this.logarithmic && (2 < Math.log(b) * Math.LOG10E - Math.log(a) * Math.LOG10E ? (this.minReal = this.min = Math.pow(10, Math.floor(Math.log(Math.abs(a)) * Math.LOG10E)), this.maxReal = this.max = Math.pow(10, Math.ceil(Math.log(Math.abs(b)) * Math.LOG10E))) : (a = Math.pow(10, Math.floor(Math.log(Math.abs(a)) * Math.LOG10E)) / 10, Math.pow(10, Math.floor(Math.log(Math.abs(this.min)) * Math.LOG10E)) / 10 < a && (this.minReal = this.min = 10 * a)));
    },
    getExtremes: function getExtremes(a, b) {
      var c, e, d;

      for (d = a; d <= b; d++) {
        var f = this.data[d].axes[this.id].graphs,
            h;

        for (h in f) {
          if (f.hasOwnProperty(h)) {
            var k = this.chart.graphsById[h];

            if (k.includeInMinMax && (!k.hidden || this.includeHidden)) {
              isNaN(c) && (c = Infinity);
              isNaN(e) && (e = -Infinity);
              this.foundGraphs = !0;
              k = f[h].values;
              this.recalculateToPercents && (k = f[h].percents);
              var l;
              if (this.minMaxField) l = k[this.minMaxField], l < c && (c = l), l > e && (e = l);else for (var m in k) {
                k.hasOwnProperty(m) && "percents" != m && "total" != m && "error" != m && (l = k[m], l < c && (c = l), l > e && (e = l));
              }
            }
          }
        }
      }

      return {
        min: c,
        max: e
      };
    },
    zoomOut: function zoomOut(a) {
      this.maxZoom = this.minZoom = NaN;
      this.zoomToRelativeValues(0, 1, a);
    },
    zoomToRelativeValues: function zoomToRelativeValues(a, b, c) {
      if (this.reversed) {
        var e = a;
        a = 1 - b;
        b = 1 - e;
      }

      var d = this.fullMax,
          e = this.fullMin,
          f = e + (d - e) * a,
          h = e + (d - e) * b;
      0 <= this.minimum && 0 > f && (f = 0);
      this.logarithmic && (isNaN(this.minimum) || (e = this.minimum), isNaN(this.maximum) || (d = this.maximum), d = Math.log(d) * Math.LOG10E - Math.log(e) * Math.LOG10E, f = Math.pow(10, d * a + Math.log(e) * Math.LOG10E), h = Math.pow(10, d * b + Math.log(e) * Math.LOG10E));
      return this.zoomToValues(f, h, c);
    },
    zoomToValues: function zoomToValues(a, b, c) {
      if (b < a) {
        var e = b;
        b = a;
        a = e;
      }

      var g = this.fullMax,
          e = this.fullMin;
      this.relativeStart = d.roundTo((a - e) / (g - e), 9);
      this.relativeEnd = d.roundTo((b - e) / (g - e), 9);

      if (this.logarithmic) {
        isNaN(this.minimum) || (e = this.minimum);
        isNaN(this.maximum) || (g = this.maximum);
        var g = Math.log(g) * Math.LOG10E - Math.log(e) * Math.LOG10E,
            f = Math.log(b) / Math.LN10 - Math.log(e) * Math.LOG10E;
        this.relativeStart = d.roundTo((Math.log(a) / Math.LN10 - Math.log(e) * Math.LOG10E) / g, 9);
        this.relativeEnd = d.roundTo(f / g, 9);
      }

      if (this.minZoom != a || this.maxZoom != b) return this.minZoom = a, this.maxZoom = b, e = {
        type: "axisZoomed"
      }, e.chart = this.chart, e.valueAxis = this, e.startValue = a, e.endValue = b, e.relativeStart = this.relativeStart, e.relativeEnd = this.relativeEnd, this.prevStartValue == a && this.prevEndValue == b || this.fire(e), this.prevStartValue = a, this.prevEndValue = b, c || (a = {}, d.copyProperties(e, a), a.type = "axisIntZoomed", this.fire(a)), 0 === this.relativeStart && 1 == this.relativeEnd && (this.maxZoom = this.minZoom = NaN), !0;
    },
    coordinateToValue: function coordinateToValue(a) {
      if (isNaN(a)) return NaN;
      var b = this.axisWidth,
          c = this.stepWidth,
          e = this.reversed,
          d = this.rotate,
          f = this.min,
          h = this.minReal;
      return !0 === this.logarithmic ? Math.pow(10, (d ? !0 === e ? (b - a) / c : a / c : !0 === e ? a / c : (b - a) / c) + Math.log(h) * Math.LOG10E) : !0 === e ? d ? f - (a - b) / c : a / c + f : d ? a / c + f : f - (a - b) / c;
    },
    getCoordinate: function getCoordinate(a, b) {
      if (isNaN(a)) return NaN;
      var c = this.rotate,
          e = this.reversed,
          d = this.axisWidth,
          f = this.stepWidth,
          h = this.min,
          k = this.minReal;
      !0 === this.logarithmic ? (0 === a && (a = this.treatZeroAs), h = Math.log(a) * Math.LOG10E - Math.log(k) * Math.LOG10E, c = c ? !0 === e ? d - f * h : f * h : !0 === e ? f * h : d - f * h) : c = !0 === e ? c ? d - f * (a - h) : f * (a - h) : c ? f * (a - h) : d - f * (a - h);
      1E7 < Math.abs(c) && (c = c / Math.abs(c) * 1E7);
      b || (c = Math.round(c));
      return c;
    },
    synchronizeWithAxis: function synchronizeWithAxis(a) {
      this.synchronizeWith = a;
      this.listenTo(this.synchronizeWith, "axisChanged", this.handleSynchronization);
    },
    handleSynchronization: function handleSynchronization() {
      if (this.synchronizeWith) {
        d.isString(this.synchronizeWith) && (this.synchronizeWith = this.chart.getValueAxisById(this.synchronizeWith));
        var a = this.synchronizeWith,
            b = a.min,
            c = a.max,
            a = a.step,
            e = this.synchronizationMultiplier;
        e && (this.min = b * e, this.max = c * e, this.step = a * e, b = Math.abs(Math.log(Math.abs(Math.pow(10, Math.floor(Math.log(Math.abs(this.step)) * Math.LOG10E)))) * Math.LOG10E), this.maxDecCount = b = Math.round(b), this.draw());
      }
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.RecAxis = d.Class({
    construct: function construct(a) {
      var b = a.chart,
          c = a.axisThickness,
          e = a.axisColor,
          g = a.axisAlpha,
          f = a.offset,
          h = a.dx,
          k = a.dy,
          l = a.x,
          m = a.y,
          n = a.height,
          q = a.width,
          p = b.container;
      "H" == a.orientation ? (e = d.line(p, [0, q], [0, 0], e, g, c), this.axisWidth = a.width, "bottom" == a.position ? (k = c / 2 + f + n + m - 1, c = l) : (k = -c / 2 - f + m + k, c = h + l)) : (this.axisWidth = a.height, "right" == a.position ? (e = d.line(p, [0, 0, -h], [0, n, n - k], e, g, c), k = m + k, c = c / 2 + f + h + q + l - 1) : (e = d.line(p, [0, 0], [0, n], e, g, c), k = m, c = -c / 2 - f + l));
      e.translate(c, k);
      c = b.container.set();
      c.push(e);
      b.axesSet.push(c);
      d.setCN(b, e, a.bcn + "line");
      this.axisSet = c;
      this.set = e;
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.RecItem = d.Class({
    construct: function construct(a, b, c, e, g, f, h, k, l, m, n, q) {
      b = Math.round(b);
      var p = a.chart;
      this.value = c;
      void 0 == c && (c = "");
      l || (l = 0);
      void 0 == e && (e = !0);
      var t = p.fontFamily,
          r = a.fontSize;
      void 0 == r && (r = p.fontSize);
      var w = a.color;
      void 0 == w && (w = p.color);
      void 0 !== n && (w = n);
      var z = a.chart.container,
          x = z.set();
      this.set = x;
      var u = a.axisThickness,
          A = a.axisColor,
          y = a.axisAlpha,
          B = a.tickLength,
          D = a.gridAlpha,
          C = a.gridThickness,
          I = a.gridColor,
          H = a.dashLength,
          Q = a.fillColor,
          M = a.fillAlpha,
          P = a.labelsEnabled;
      n = a.labelRotationR;
      var ia = a.counter,
          J = a.inside,
          aa = a.labelOffset,
          ma = a.dx,
          na = a.dy,
          Pa = a.orientation,
          Z = a.position,
          da = a.previousCoord,
          X = a.height,
          xa = a.width,
          ea = a.offset,
          fa,
          Ba;
      h ? (void 0 !== h.id && (q = p.classNamePrefix + "-guide-" + h.id), P = !0, isNaN(h.tickLength) || (B = h.tickLength), void 0 != h.lineColor && (I = h.lineColor), void 0 != h.color && (w = h.color), isNaN(h.lineAlpha) || (D = h.lineAlpha), isNaN(h.dashLength) || (H = h.dashLength), isNaN(h.lineThickness) || (C = h.lineThickness), !0 === h.inside && (J = !0, 0 < ea && (ea = 0)), isNaN(h.labelRotation) || (n = h.labelRotation), isNaN(h.fontSize) || (r = h.fontSize), h.position && (Z = h.position), void 0 !== h.boldLabel && (k = h.boldLabel), isNaN(h.labelOffset) || (aa = h.labelOffset)) : "" === c && (B = 0);
      m && !isNaN(a.minorTickLength) && (B = a.minorTickLength);
      var ga = "start";
      0 < g && (ga = "middle");
      a.centerLabels && (ga = "middle");
      var V = n * Math.PI / 180,
          Y,
          Da,
          G = 0,
          v = 0,
          oa = 0,
          ha = Y = 0,
          Qa = 0;
      "V" == Pa && (n = 0);
      var ca;
      P && "" !== c && (ca = a.autoWrap && 0 === n ? d.wrappedText(z, c, w, t, r, ga, k, Math.abs(g), 0) : d.text(z, c, w, t, r, ga, k), ga = ca.getBBox(), ha = ga.width, Qa = ga.height);

      if ("H" == Pa) {
        if (0 <= b && b <= xa + 1 && (0 < B && 0 < y && b + l <= xa + 1 && (fa = d.line(z, [b + l, b + l], [0, B], A, y, C), x.push(fa)), 0 < D && (Ba = d.line(z, [b, b + ma, b + ma], [X, X + na, na], I, D, C, H), x.push(Ba))), v = 0, G = b, h && 90 == n && J && (G -= r), !1 === e ? (ga = "start", v = "bottom" == Z ? J ? v + B : v - B : J ? v - B : v + B, G += 3, 0 < g && (G += g / 2 - 3, ga = "middle"), 0 < n && (ga = "middle")) : ga = "middle", 1 == ia && 0 < M && !h && !m && da < xa && (e = d.fitToBounds(b, 0, xa), da = d.fitToBounds(da, 0, xa), Y = e - da, 0 < Y && (Da = d.rect(z, Y, a.height, Q, M), Da.translate(e - Y + ma, na), x.push(Da))), "bottom" == Z ? (v += X + r / 2 + ea, J ? (0 < n ? (v = X - ha / 2 * Math.sin(V) - B - 3, a.centerRotatedLabels || (G += ha / 2 * Math.cos(V) - 4 + 2)) : 0 > n ? (v = X + ha * Math.sin(V) - B - 3 + 2, G += -ha * Math.cos(V) - Qa * Math.sin(V) - 4) : v -= B + r + 3 + 3, v -= aa) : (0 < n ? (v = X + ha / 2 * Math.sin(V) + B + 3, a.centerRotatedLabels || (G -= ha / 2 * Math.cos(V))) : 0 > n ? (v = X + B + 3 - ha / 2 * Math.sin(V) + 2, G += ha / 2 * Math.cos(V)) : v += B + u + 3 + 3, v += aa)) : (v += na + r / 2 - ea, G += ma, J ? (0 < n ? (v = ha / 2 * Math.sin(V) + B + 3, a.centerRotatedLabels || (G -= ha / 2 * Math.cos(V))) : v += B + 3, v += aa) : (0 < n ? (v = -(ha / 2) * Math.sin(V) - B - 6, a.centerRotatedLabels || (G += ha / 2 * Math.cos(V))) : v -= B + r + 3 + u + 3, v -= aa)), "bottom" == Z ? Y = (J ? X - B - 1 : X + u - 1) + ea : (oa = ma, Y = (J ? na : na - B - u + 1) - ea), f && (G += f), r = G, 0 < n && (r += ha / 2 * Math.cos(V)), ca && (f = 0, J && (f = ha / 2 * Math.cos(V)), r + f > xa + 2 || 0 > r)) ca.remove(), ca = null;
      } else {
        0 <= b && b <= X + 1 && (0 < B && 0 < y && b + l <= X + 1 && (fa = d.line(z, [0, B + 1], [b + l, b + l], A, y, C), x.push(fa)), 0 < D && (Ba = d.line(z, [0, ma, xa + ma], [b, b + na, b + na], I, D, C, H), x.push(Ba)));
        ga = "end";
        if (!0 === J && "left" == Z || !1 === J && "right" == Z) ga = "start";
        v = b - Qa / 2 + 2;
        1 == ia && 0 < M && !h && !m && (e = d.fitToBounds(b, 0, X), da = d.fitToBounds(da, 0, X), V = e - da, Da = d.polygon(z, [0, a.width, a.width, 0], [0, 0, V, V], Q, M), Da.translate(ma, e - V + na), x.push(Da));
        v += r / 2;
        "right" == Z ? (G += ma + xa + ea, v += na, J ? (f || (v -= r / 2 + 3), G = G - (B + 4) - aa) : (G += B + 4 + u, v -= 2, G += aa)) : J ? (G += B + 4 - ea, f || (v -= r / 2 + 3), h && (G += ma, v += na), G += aa) : (G += -B - u - 4 - 2 - ea, v -= 2, G -= aa);
        fa && ("right" == Z ? (oa += ma + ea + xa - 1, Y += na, oa = J ? oa - u : oa + u) : (oa -= ea, J || (oa -= B + u)));
        f && (v += f);
        J = -3;
        "right" == Z && (J += na);
        ca && (v > X + 1 || v < J - r / 10) && (ca.remove(), ca = null);
      }

      fa && (fa.translate(oa, Y), d.setCN(p, fa, a.bcn + "tick"), d.setCN(p, fa, q, !0), h && d.setCN(p, fa, "guide"));
      !1 === a.visible && (fa && fa.remove(), ca && (ca.remove(), ca = null));
      ca && (ca.attr({
        "text-anchor": ga
      }), ca.translate(G, v, NaN, !0), 0 !== n && ca.rotate(-n, a.chart.backgroundColor), a.allLabels.push(ca), this.label = ca, d.setCN(p, ca, a.bcn + "label"), d.setCN(p, ca, q, !0), h && d.setCN(p, ca, "guide"));
      Ba && (d.setCN(p, Ba, a.bcn + "grid"), d.setCN(p, Ba, q, !0), h && d.setCN(p, Ba, "guide"));
      Da && (d.setCN(p, Da, a.bcn + "fill"), d.setCN(p, Da, q, !0));
      m ? Ba && d.setCN(p, Ba, a.bcn + "grid-minor") : (a.counter = 0 === ia ? 1 : 0, a.previousCoord = b);
      0 === this.set.node.childNodes.length && this.set.remove();
    },
    graphics: function graphics() {
      return this.set;
    },
    getLabel: function getLabel() {
      return this.label;
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.RecFill = d.Class({
    construct: function construct(a, b, c, e) {
      var g = a.dx,
          f = a.dy,
          h = a.orientation,
          k = 0;

      if (c < b) {
        var l = b;
        b = c;
        c = l;
      }

      var m = e.fillAlpha;
      isNaN(m) && (m = 0);
      var l = a.chart.container,
          n = e.fillColor;
      "V" == h ? (b = d.fitToBounds(b, 0, a.height), c = d.fitToBounds(c, 0, a.height)) : (b = d.fitToBounds(b, 0, a.width), c = d.fitToBounds(c, 0, a.width));
      c -= b;
      isNaN(c) && (c = 4, k = 2, m = 0);
      0 > c && "object" == _typeof(n) && (n = n.join(",").split(",").reverse());
      "V" == h ? (h = d.rect(l, a.width, c, n, m), h.translate(g, b - k + f)) : (h = d.rect(l, c, a.height, n, m), h.translate(b - k + g, f));
      d.setCN(a.chart, h, "guide-fill");
      e.id && d.setCN(a.chart, h, "guide-fill-" + e.id);
      this.set = l.set([h]);
    },
    graphics: function graphics() {
      return this.set;
    },
    getLabel: function getLabel() {}
  });
})();

(function () {
  var d = window.AmCharts;
  d.AmChart = d.Class({
    construct: function construct(a) {
      this.svgIcons = this.tapToActivate = !0;
      this.theme = a;
      this.classNamePrefix = "amcharts";
      this.addClassNames = !1;
      this.version = "3.21.15";
      d.addChart(this);
      this.createEvents("buildStarted", "dataUpdated", "init", "rendered", "drawn", "failed", "resized", "animationFinished");
      this.height = this.width = "100%";
      this.dataChanged = !0;
      this.chartCreated = !1;
      this.previousWidth = this.previousHeight = 0;
      this.backgroundColor = "#FFFFFF";
      this.borderAlpha = this.backgroundAlpha = 0;
      this.color = this.borderColor = "#000000";
      this.fontFamily = "Verdana";
      this.fontSize = 11;
      this.usePrefixes = !1;
      this.autoResize = !0;
      this.autoDisplay = !1;
      this.addCodeCredits = this.accessible = !0;
      this.touchStartTime = this.touchClickDuration = 0;
      this.precision = -1;
      this.percentPrecision = 2;
      this.decimalSeparator = ".";
      this.thousandsSeparator = ",";
      this.labels = [];
      this.allLabels = [];
      this.titles = [];
      this.marginRight = this.marginLeft = this.autoMarginOffset = 0;
      this.timeOuts = [];
      this.creditsPosition = "top-left";
      var b = document.createElement("div"),
          c = b.style;
      c.overflow = "hidden";
      c.position = "relative";
      c.textAlign = "left";
      this.chartDiv = b;
      b = document.createElement("div");
      c = b.style;
      c.overflow = "hidden";
      c.position = "relative";
      c.textAlign = "left";
      this.legendDiv = b;
      this.titleHeight = 0;
      this.hideBalloonTime = 150;
      this.handDrawScatter = 2;
      this.cssScale = this.handDrawThickness = 1;
      this.cssAngle = 0;
      this.prefixesOfBigNumbers = [{
        number: 1E3,
        prefix: "k"
      }, {
        number: 1E6,
        prefix: "M"
      }, {
        number: 1E9,
        prefix: "G"
      }, {
        number: 1E12,
        prefix: "T"
      }, {
        number: 1E15,
        prefix: "P"
      }, {
        number: 1E18,
        prefix: "E"
      }, {
        number: 1E21,
        prefix: "Z"
      }, {
        number: 1E24,
        prefix: "Y"
      }];
      this.prefixesOfSmallNumbers = [{
        number: 1E-24,
        prefix: "y"
      }, {
        number: 1E-21,
        prefix: "z"
      }, {
        number: 1E-18,
        prefix: "a"
      }, {
        number: 1E-15,
        prefix: "f"
      }, {
        number: 1E-12,
        prefix: "p"
      }, {
        number: 1E-9,
        prefix: "n"
      }, {
        number: 1E-6,
        prefix: "\u03BC"
      }, {
        number: .001,
        prefix: "m"
      }];
      this.panEventsEnabled = !0;
      this.product = "amcharts";
      this.animations = [];
      this.balloon = new d.AmBalloon(this.theme);
      this.balloon.chart = this;
      this.processTimeout = 0;
      this.processCount = 1E3;
      this.animatable = [];
      this.langObj = {};
      d.applyTheme(this, a, "AmChart");
    },
    drawChart: function drawChart() {
      0 < this.realWidth && 0 < this.realHeight && (this.drawBackground(), this.redrawLabels(), this.drawTitles(), this.brr(), this.renderFix(), this.chartDiv && (this.boundingRect = this.chartDiv.getBoundingClientRect()));
    },
    makeAccessible: function makeAccessible(a, b, c) {
      this.accessible && a && (c && a.setAttr("role", c), a.setAttr("aria-label", b));
    },
    drawBackground: function drawBackground() {
      d.remove(this.background);
      var a = this.container,
          b = this.backgroundColor,
          c = this.backgroundAlpha,
          e = this.set;
      d.isModern || 0 !== c || (c = .001);
      var g = this.updateWidth();
      this.realWidth = g;
      var f = this.updateHeight();
      this.realHeight = f;
      b = d.polygon(a, [0, g - 1, g - 1, 0], [0, 0, f - 1, f - 1], b, c, 1, this.borderColor, this.borderAlpha);
      d.setCN(this, b, "bg");
      this.background = b;
      e.push(b);
      if (b = this.backgroundImage) a = a.image(b, 0, 0, g, f), d.setCN(this, b, "bg-image"), this.bgImg = a, e.push(a);
    },
    drawTitles: function drawTitles(a) {
      var b = this.titles;
      this.titleHeight = 0;

      if (d.ifArray(b)) {
        var c = 20,
            e;

        for (e = 0; e < b.length; e++) {
          var g = b[e],
              g = d.processObject(g, d.Title, this.theme);

          if (!1 !== g.enabled) {
            var f = g.color;
            void 0 === f && (f = this.color);
            var h = g.size;
            isNaN(h) && (h = this.fontSize + 2);
            isNaN(g.alpha);
            var k = this.marginLeft,
                l = !0;
            void 0 !== g.bold && (l = g.bold);
            f = d.wrappedText(this.container, g.text, f, this.fontFamily, h, "middle", l, this.realWidth - 35 - this.marginRight - k);
            f.translate(k + (this.realWidth - this.marginRight - k) / 2, c);
            f.node.style.pointerEvents = "none";
            g.sprite = f;
            void 0 !== g.tabIndex && f.setAttr("tabindex", g.tabIndex);
            d.setCN(this, f, "title");
            g.id && d.setCN(this, f, "title-" + g.id);
            f.attr({
              opacity: g.alpha
            });
            c += f.getBBox().height + 5;
            a ? f.remove() : this.freeLabelsSet.push(f);
          }
        }

        this.titleHeight = c - 10;
      }
    },
    write: function write(a) {
      var b = this;
      if (b.listeners) for (var c = 0; c < b.listeners.length; c++) {
        var e = b.listeners[c];
        b.addListener(e.event, e.method);
      }
      b.fire({
        type: "buildStarted",
        chart: b
      });
      b.afterWriteTO && clearTimeout(b.afterWriteTO);
      0 < b.processTimeout ? b.afterWriteTO = setTimeout(function () {
        b.afterWrite.call(b, a);
      }, b.processTimeout) : b.afterWrite(a);
    },
    afterWrite: function afterWrite(a) {
      var b;

      if (b = "object" != _typeof(a) ? document.getElementById(a) : a) {
        for (; b.firstChild;) {
          b.removeChild(b.firstChild);
        }

        this.div = b;
        b.style.overflow = "hidden";
        b.style.textAlign = "left";
        a = this.chartDiv;
        var c = this.legendDiv,
            e = this.legend,
            g = c.style,
            f = a.style;
        this.measure();
        this.previousHeight = this.divRealHeight;
        this.previousWidth = this.divRealWidth;
        var h,
            k = document.createElement("div");
        h = k.style;
        h.position = "relative";
        this.containerDiv = k;
        k.className = this.classNamePrefix + "-main-div";
        a.className = this.classNamePrefix + "-chart-div";
        b.appendChild(k);
        (b = this.exportConfig) && d.AmExport && !this.AmExport && (this.AmExport = new d.AmExport(this, b));
        this.amExport && d.AmExport && (this.AmExport = d.extend(this.amExport, new d.AmExport(this), !0));
        this.AmExport && this.AmExport.init && this.AmExport.init();

        if (e) {
          e = this.addLegend(e, e.divId);
          if (e.enabled) switch (g.left = null, g.top = null, g.right = null, f.left = null, f.right = null, f.top = null, g.position = "relative", f.position = "relative", h.width = "100%", h.height = "100%", e.position) {
            case "bottom":
              k.appendChild(a);
              k.appendChild(c);
              break;

            case "top":
              k.appendChild(c);
              k.appendChild(a);
              break;

            case "absolute":
              g.position = "absolute";
              f.position = "absolute";
              void 0 !== e.left && (g.left = e.left + "px");
              void 0 !== e.right && (g.right = e.right + "px");
              void 0 !== e.top && (g.top = e.top + "px");
              void 0 !== e.bottom && (g.bottom = e.bottom + "px");
              e.marginLeft = 0;
              e.marginRight = 0;
              k.appendChild(a);
              k.appendChild(c);
              break;

            case "right":
              g.position = "relative";
              f.position = "absolute";
              k.appendChild(a);
              k.appendChild(c);
              break;

            case "left":
              g.position = "absolute";
              f.position = "relative";
              k.appendChild(a);
              k.appendChild(c);
              break;

            case "outside":
              k.appendChild(a);
          } else k.appendChild(a);
          this.prevLegendPosition = e.position;
        } else k.appendChild(a);

        this.listenersAdded || (this.addListeners(), this.listenersAdded = !0);
        (this.mouseWheelScrollEnabled || this.mouseWheelZoomEnabled) && d.addWheelListeners();
        this.initChart();
      }
    },
    createLabelsSet: function createLabelsSet() {
      d.remove(this.labelsSet);
      this.labelsSet = this.container.set();
      this.freeLabelsSet.push(this.labelsSet);
    },
    initChart: function initChart() {
      this.balloon = d.processObject(this.balloon, d.AmBalloon, this.theme);
      window.AmCharts_path && (this.path = window.AmCharts_path);
      void 0 === this.path && (this.path = d.getPath());
      void 0 === this.path && (this.path = "amcharts/");
      this.path = d.normalizeUrl(this.path);
      void 0 === this.pathToImages && (this.pathToImages = this.path + "images/");
      this.initHC || (d.callInitHandler(this), this.initHC = !0);
      d.applyLang(this.language, this);
      var a = this.numberFormatter;
      a && (isNaN(a.precision) || (this.precision = a.precision), void 0 !== a.thousandsSeparator && (this.thousandsSeparator = a.thousandsSeparator), void 0 !== a.decimalSeparator && (this.decimalSeparator = a.decimalSeparator));
      (a = this.percentFormatter) && !isNaN(a.precision) && (this.percentPrecision = a.precision);
      this.nf = {
        precision: this.precision,
        thousandsSeparator: this.thousandsSeparator,
        decimalSeparator: this.decimalSeparator
      };
      this.pf = {
        precision: this.percentPrecision,
        thousandsSeparator: this.thousandsSeparator,
        decimalSeparator: this.decimalSeparator
      };
      this.destroy();
      (a = this.container) ? (a.container.innerHTML = "", a.width = this.realWidth, a.height = this.realHeight, a.addDefs(this), this.chartDiv.appendChild(a.container)) : a = new d.AmDraw(this.chartDiv, this.realWidth, this.realHeight, this);
      this.container = a;
      this.extension = ".png";
      this.svgIcons && d.SVG && (this.extension = ".svg");
      this.checkDisplay();
      this.checkTransform(this.div);
      a.chart = this;
      d.VML || d.SVG ? (a.handDrawn = this.handDrawn, a.handDrawScatter = this.handDrawScatter, a.handDrawThickness = this.handDrawThickness, d.remove(this.set), this.set = a.set(), d.remove(this.gridSet), this.gridSet = a.set(), d.remove(this.cursorLineSet), this.cursorLineSet = a.set(), d.remove(this.graphsBehindSet), this.graphsBehindSet = a.set(), d.remove(this.bulletBehindSet), this.bulletBehindSet = a.set(), d.remove(this.columnSet), this.columnSet = a.set(), d.remove(this.graphsSet), this.graphsSet = a.set(), d.remove(this.trendLinesSet), this.trendLinesSet = a.set(), d.remove(this.axesSet), this.axesSet = a.set(), d.remove(this.cursorSet), this.cursorSet = a.set(), d.remove(this.scrollbarsSet), this.scrollbarsSet = a.set(), d.remove(this.bulletSet), this.bulletSet = a.set(), d.remove(this.freeLabelsSet), this.freeLabelsSet = a.set(), d.remove(this.axesLabelsSet), this.axesLabelsSet = a.set(), d.remove(this.balloonsSet), this.balloonsSet = a.set(), d.remove(this.plotBalloonsSet), this.plotBalloonsSet = a.set(), d.remove(this.zoomButtonSet), this.zoomButtonSet = a.set(), d.remove(this.zbSet), this.zbSet = null, d.remove(this.linkSet), this.linkSet = a.set()) : this.fire({
        type: "failed",
        chart: this
      });
    },
    premeasure: function premeasure() {
      var a = this.div;

      if (a) {
        try {
          this.boundingRect = this.chartDiv.getBoundingClientRect();
        } catch (e) {}

        var b = a.offsetWidth,
            c = a.offsetHeight;
        a.clientHeight && (b = a.clientWidth, c = a.clientHeight);
        if (b != this.mw || c != this.mh) this.mw = b, this.mh = c, this.measure();
      }
    },
    measure: function measure() {
      var a = this.div;

      if (a) {
        var b = this.chartDiv,
            c = a.offsetWidth,
            e = a.offsetHeight,
            g = this.container;
        a.clientHeight && (c = a.clientWidth, e = a.clientHeight);
        var e = Math.round(e),
            c = Math.round(c),
            a = Math.round(d.toCoordinate(this.width, c)),
            f = Math.round(d.toCoordinate(this.height, e));
        (c != this.previousWidth || e != this.previousHeight) && 0 < a && 0 < f && (b.style.width = a + "px", b.style.height = f + "px", b.style.padding = 0, g && g.setSize(a, f), this.balloon = d.processObject(this.balloon, d.AmBalloon, this.theme));
        this.balloon && this.balloon.setBounds && this.balloon.setBounds(2, 2, a - 2, f);
        this.updateWidth();
        this.balloon.chart = this;
        this.realWidth = a;
        this.realHeight = f;
        this.divRealWidth = c;
        this.divRealHeight = e;
      }
    },
    checkDisplay: function checkDisplay() {
      if (this.autoDisplay && this.container) {
        var a = d.rect(this.container, 10, 10),
            b = a.getBBox();
        0 === b.width && 0 === b.height && (this.divRealHeight = this.divRealWidth = this.realHeight = this.realWidth = 0, this.previousWidth = this.previousHeight = NaN);
        a.remove();
      }
    },
    checkTransform: function checkTransform(a) {
      if (this.autoTransform && window.getComputedStyle && a) {
        if (a.style) {
          var b = window.getComputedStyle(a, null);

          if (b && (b = b.getPropertyValue("-webkit-transform") || b.getPropertyValue("-moz-transform") || b.getPropertyValue("-ms-transform") || b.getPropertyValue("-o-transform") || b.getPropertyValue("transform")) && "none" !== b) {
            var c = b.split("(")[1].split(")")[0].split(","),
                b = c[0],
                c = c[1],
                b = Math.sqrt(b * b + c * c);
            isNaN(b) || (this.cssScale *= b);
          }
        }

        a.parentNode && this.checkTransform(a.parentNode);
      }
    },
    destroy: function destroy() {
      this.chartDiv.innerHTML = "";
      this.clearTimeOuts();
      this.legend && this.legend.destroy && this.legend.destroy();
    },
    clearTimeOuts: function clearTimeOuts() {
      var a = this.timeOuts;

      if (a) {
        var b;

        for (b = 0; b < a.length; b++) {
          clearTimeout(a[b]);
        }
      }

      this.timeOuts = [];
    },
    clear: function clear(a) {
      try {
        document.removeEventListener("touchstart", this.docfn1, !0), document.removeEventListener("touchend", this.docfn2, !0);
      } catch (b) {}

      d.callMethod("clear", [this.chartScrollbar, this.scrollbarV, this.scrollbarH, this.chartCursor]);
      this.chartCursor = this.scrollbarH = this.scrollbarV = this.chartScrollbar = null;
      this.clearTimeOuts();
      this.container && (this.container.remove(this.chartDiv), this.container.remove(this.legendDiv));
      a || d.removeChart(this);
      if (a = this.div) for (; a.firstChild;) {
        a.removeChild(a.firstChild);
      }
      this.legend && this.legend.destroy && this.legend.destroy();
      this.AmExport && this.AmExport.clear && this.AmExport.clear();
    },
    setMouseCursor: function setMouseCursor(a) {
      "auto" == a && d.isNN && (a = "default");
      this.chartDiv.style.cursor = a;
      this.legendDiv.style.cursor = a;
    },
    redrawLabels: function redrawLabels() {
      this.labels = [];
      var a = this.allLabels;
      this.createLabelsSet();
      var b;

      for (b = 0; b < a.length; b++) {
        this.drawLabel(a[b]);
      }
    },
    drawLabel: function drawLabel(a) {
      var b = this;

      if (b.container && !1 !== a.enabled) {
        a = d.processObject(a, d.Label, b.theme);
        var c = a.y,
            e = a.text,
            g = a.align,
            f = a.size,
            h = a.color,
            k = a.rotation,
            l = a.alpha,
            m = a.bold,
            n = d.toCoordinate(a.x, b.realWidth),
            c = d.toCoordinate(c, b.realHeight);
        n || (n = 0);
        c || (c = 0);
        void 0 === h && (h = b.color);
        isNaN(f) && (f = b.fontSize);
        g || (g = "start");
        "left" == g && (g = "start");
        "right" == g && (g = "end");
        "center" == g && (g = "middle", k ? c = b.realHeight - c + c / 2 : n = b.realWidth / 2 - n);
        void 0 === l && (l = 1);
        void 0 === k && (k = 0);
        c += f / 2;
        e = d.text(b.container, e, h, b.fontFamily, f, g, m, l);
        e.translate(n, c);
        void 0 !== a.tabIndex && e.setAttr("tabindex", a.tabIndex);
        d.setCN(b, e, "label");
        a.id && d.setCN(b, e, "label-" + a.id);
        0 !== k && e.rotate(k);
        a.url ? (e.setAttr("cursor", "pointer"), e.click(function () {
          d.getURL(a.url, b.urlTarget);
        })) : e.node.style.pointerEvents = "none";
        b.labelsSet.push(e);
        b.labels.push(e);
      }
    },
    addLabel: function addLabel(a, b, c, e, d, f, h, k, l, m) {
      a = {
        x: a,
        y: b,
        text: c,
        align: e,
        size: d,
        color: f,
        alpha: k,
        rotation: h,
        bold: l,
        url: m,
        enabled: !0
      };
      this.container && this.drawLabel(a);
      this.allLabels.push(a);
    },
    clearLabels: function clearLabels() {
      var a = this.labels,
          b;

      for (b = a.length - 1; 0 <= b; b--) {
        a[b].remove();
      }

      this.labels = [];
      this.allLabels = [];
    },
    updateHeight: function updateHeight() {
      var a = this.divRealHeight,
          b = this.legend;

      if (b) {
        var c = this.legendDiv.offsetHeight,
            b = b.position;

        if ("top" == b || "bottom" == b) {
          a -= c;
          if (0 > a || isNaN(a)) a = 0;
          this.chartDiv.style.height = a + "px";
        }
      }

      return a;
    },
    updateWidth: function updateWidth() {
      var a = this.divRealWidth,
          b = this.divRealHeight,
          c = this.legend;

      if (c) {
        var e = this.legendDiv,
            d = e.offsetWidth;
        isNaN(c.width) || (d = c.width);
        c.ieW && (d = c.ieW);
        var f = e.offsetHeight,
            e = e.style,
            h = this.chartDiv.style,
            k = c.position;

        if (("right" == k || "left" == k) && void 0 === c.divId) {
          a -= d;
          if (0 > a || isNaN(a)) a = 0;
          h.width = a + "px";
          this.balloon && this.balloon.setBounds && this.balloon.setBounds(2, 2, a - 2, this.realHeight);
          "left" == k ? (h.left = d + "px", e.left = "0px") : (h.left = "0px", e.left = a + "px");
          b > f && (e.top = (b - f) / 2 + "px");
        }
      }

      return a;
    },
    getTitleHeight: function getTitleHeight() {
      this.drawTitles(!0);
      return this.titleHeight;
    },
    addTitle: function addTitle(a, b, c, e, d) {
      isNaN(b) && (b = this.fontSize + 2);
      a = {
        text: a,
        size: b,
        color: c,
        alpha: e,
        bold: d,
        enabled: !0
      };
      this.titles.push(a);
      return a;
    },
    handleWheel: function handleWheel(a) {
      var b = 0;
      a || (a = window.event);
      a.wheelDelta ? b = a.wheelDelta / 120 : a.detail && (b = -a.detail / 3);
      b && this.handleWheelReal(b, a.shiftKey);
      a.preventDefault && a.preventDefault();
    },
    handleWheelReal: function handleWheelReal() {},
    handleDocTouchStart: function handleDocTouchStart() {
      this.handleMouseMove();
      this.tmx = this.mouseX;
      this.tmy = this.mouseY;
      this.touchStartTime = new Date().getTime();
    },
    handleDocTouchEnd: function handleDocTouchEnd() {
      -.5 < this.tmx && this.tmx < this.divRealWidth + 1 && 0 < this.tmy && this.tmy < this.divRealHeight ? (this.handleMouseMove(), 4 > Math.abs(this.mouseX - this.tmx) && 4 > Math.abs(this.mouseY - this.tmy) ? (this.tapped = !0, this.panRequired && this.panEventsEnabled && this.chartDiv && (this.chartDiv.style.msTouchAction = "none", this.chartDiv.style.touchAction = "none")) : this.mouseIsOver || this.resetTouchStyle()) : (this.tapped = !1, this.resetTouchStyle());
    },
    resetTouchStyle: function resetTouchStyle() {
      this.panEventsEnabled && this.chartDiv && (this.chartDiv.style.msTouchAction = "auto", this.chartDiv.style.touchAction = "auto");
    },
    checkTouchDuration: function checkTouchDuration(a) {
      var b = this,
          c = new Date().getTime();
      if (a) if (a.touches) b.isTouchEvent = !0;else if (!b.isTouchEvent) return !0;
      if (c - b.touchStartTime > b.touchClickDuration) return !0;
      setTimeout(function () {
        b.resetTouchDuration();
      }, 300);
    },
    resetTouchDuration: function resetTouchDuration() {
      this.isTouchEvent = !1;
    },
    checkTouchMoved: function checkTouchMoved() {
      if (4 < Math.abs(this.mouseX - this.tmx) || 4 < Math.abs(this.mouseY - this.tmy)) return !0;
    },
    addListeners: function addListeners() {
      var a = this,
          b = a.chartDiv;
      document.addEventListener ? ("ontouchstart" in document.documentElement && (b.addEventListener("touchstart", function (b) {
        a.handleTouchStart.call(a, b);
      }, !0), b.addEventListener("touchmove", function (b) {
        a.handleMouseMove.call(a, b);
      }, !0), b.addEventListener("touchend", function (b) {
        a.handleTouchEnd.call(a, b);
      }, !0), a.docfn1 = function (b) {
        a.handleDocTouchStart.call(a, b);
      }, a.docfn2 = function (b) {
        a.handleDocTouchEnd.call(a, b);
      }, document.addEventListener("touchstart", a.docfn1, !0), document.addEventListener("touchend", a.docfn2, !0)), b.addEventListener("mousedown", function (b) {
        a.mouseIsOver = !0;
        a.handleMouseMove.call(a, b);
        a.handleMouseDown.call(a, b);
        a.handleDocTouchStart.call(a, b);
      }, !0), b.addEventListener("mouseover", function (b) {
        a.handleMouseOver.call(a, b);
      }, !0), b.addEventListener("mouseout", function (b) {
        a.handleMouseOut.call(a, b);
      }, !0), b.addEventListener("mouseup", function (b) {
        a.handleDocTouchEnd.call(a, b);
      }, !0)) : (b.attachEvent("onmousedown", function (b) {
        a.handleMouseDown.call(a, b);
      }), b.attachEvent("onmouseover", function (b) {
        a.handleMouseOver.call(a, b);
      }), b.attachEvent("onmouseout", function (b) {
        a.handleMouseOut.call(a, b);
      }));
    },
    dispDUpd: function dispDUpd() {
      this.skipEvents || (this.dispatchDataUpdated && (this.dispatchDataUpdated = !1, this.fire({
        type: "dataUpdated",
        chart: this
      })), this.chartCreated || (this.chartCreated = !0, this.fire({
        type: "init",
        chart: this
      })), !this.chartRendered && 0 < this.divRealWidth && 0 < this.divRealHeight && (this.fire({
        type: "rendered",
        chart: this
      }), this.chartRendered = !0), this.fire({
        type: "drawn",
        chart: this
      }));
      this.skipEvents = !1;
    },
    validateSize: function validateSize() {
      var a = this;
      a.premeasure();
      a.checkDisplay();
      a.cssScale = 1;
      a.cssAngle = 0;
      a.checkTransform(a.div);

      if (a.divRealWidth != a.previousWidth || a.divRealHeight != a.previousHeight) {
        var b = a.legend;

        if (0 < a.realWidth && 0 < a.realHeight) {
          a.sizeChanged = !0;

          if (b) {
            a.legendInitTO && clearTimeout(a.legendInitTO);
            var c = setTimeout(function () {
              b.invalidateSize();
            }, 10);
            a.timeOuts.push(c);
            a.legendInitTO = c;
          }

          a.marginsUpdated = !1;
          clearTimeout(a.initTO);
          c = setTimeout(function () {
            a.initChart();
          }, 10);
          a.timeOuts.push(c);
          a.initTO = c;
        }

        a.renderFix();
        b && b.renderFix && b.renderFix();
        a.positionCred();
        clearTimeout(a.resizedTO);
        a.resizedTO = setTimeout(function () {
          a.fire({
            type: "resized",
            chart: a
          });
        }, 10);
        a.previousHeight = a.divRealHeight;
        a.previousWidth = a.divRealWidth;
      }
    },
    invalidateSize: function invalidateSize() {
      this.previousHeight = this.previousWidth = NaN;
      this.invalidateSizeReal();
    },
    invalidateSizeReal: function invalidateSizeReal() {
      var a = this;
      a.marginsUpdated = !1;
      clearTimeout(a.validateTO);
      var b = setTimeout(function () {
        a.validateSize();
      }, 5);
      a.timeOuts.push(b);
      a.validateTO = b;
    },
    validateData: function validateData(a) {
      this.chartCreated && (this.dataChanged = !0, this.marginsUpdated = !1, this.initChart(a));
    },
    validateNow: function validateNow(a, b) {
      this.initTO && clearTimeout(this.initTO);
      a && (this.dataChanged = !0, this.marginsUpdated = !1);
      this.skipEvents = b;
      this.chartRendered = !1;
      var c = this.legend;
      c && c.position != this.prevLegendPosition && (this.previousWidth = this.mw = 0, c.invalidateSize && (c.invalidateSize(), this.validateSize()));
      this.write(this.div);
    },
    showItem: function showItem(a) {
      a.hidden = !1;
      this.initChart();
    },
    hideItem: function hideItem(a) {
      a.hidden = !0;
      this.initChart();
    },
    hideBalloon: function hideBalloon() {
      var a = this;
      clearTimeout(a.hoverInt);
      clearTimeout(a.balloonTO);
      a.hoverInt = setTimeout(function () {
        a.hideBalloonReal.call(a);
      }, a.hideBalloonTime);
    },
    cleanChart: function cleanChart() {},
    hideBalloonReal: function hideBalloonReal() {
      var a = this.balloon;
      a && a.hide && a.hide();
    },
    showBalloon: function showBalloon(a, b, c, e, d) {
      var f = this;
      clearTimeout(f.balloonTO);
      clearTimeout(f.hoverInt);
      f.balloonTO = setTimeout(function () {
        f.showBalloonReal.call(f, a, b, c, e, d);
      }, 1);
    },
    showBalloonReal: function showBalloonReal(a, b, c, e, d) {
      this.handleMouseMove();
      var f = this.balloon;
      f.enabled && (f.followCursor(!1), f.changeColor(b), !c || f.fixedPosition ? (f.setPosition(e, d), isNaN(e) || isNaN(d) ? f.followCursor(!0) : f.followCursor(!1)) : f.followCursor(!0), a && f.showBalloon(a));
    },
    handleMouseOver: function handleMouseOver() {
      this.outTO && clearTimeout(this.outTO);
      d.resetMouseOver();
      this.mouseIsOver = !0;
    },
    handleMouseOut: function handleMouseOut() {
      var a = this;
      d.resetMouseOver();
      a.outTO && clearTimeout(a.outTO);
      a.outTO = setTimeout(function () {
        a.handleMouseOutReal();
      }, 10);
    },
    handleMouseOutReal: function handleMouseOutReal() {
      this.mouseIsOver = !1;
    },
    handleMouseMove: function handleMouseMove(a) {
      a || (a = window.event);
      this.mouse2Y = this.mouse2X = NaN;
      var b, c, e, d;

      if (a) {
        if (a.touches) {
          var f = a.touches.item(1);
          f && this.panEventsEnabled && this.boundingRect && (e = f.clientX - this.boundingRect.left, d = f.clientY - this.boundingRect.top);
          a = a.touches.item(0);
          if (!a) return;
        } else this.wasTouched = !1;

        this.boundingRect && a.clientX && (b = a.clientX - this.boundingRect.left, c = a.clientY - this.boundingRect.top);
        isNaN(e) ? this.mouseX = b : (this.mouseX = Math.min(b, e), this.mouse2X = Math.max(b, e));
        isNaN(d) ? this.mouseY = c : (this.mouseY = Math.min(c, d), this.mouse2Y = Math.max(c, d));
        this.autoTransform && (this.mouseX /= this.cssScale, this.mouseY /= this.cssScale);
      }
    },
    handleTouchStart: function handleTouchStart(a) {
      this.hideBalloonReal();
      a && (a.touches && this.tapToActivate && !this.tapped || !this.panRequired) || (this.handleMouseMove(a), this.handleMouseDown(a));
    },
    handleTouchEnd: function handleTouchEnd(a) {
      this.wasTouched = !0;
      this.handleMouseMove(a);
      d.resetMouseOver();
      this.handleReleaseOutside(a);
    },
    handleReleaseOutside: function handleReleaseOutside() {
      this.handleDocTouchEnd.call(this);
    },
    handleMouseDown: function handleMouseDown(a) {
      d.resetMouseOver();
      this.mouseIsOver = !0;
      a && a.preventDefault && (this.panEventsEnabled ? a.preventDefault() : a.touches || a.preventDefault());
    },
    handleKeyUp: function handleKeyUp(a) {},
    addLegend: function addLegend(a, b) {
      a = d.processObject(a, d.AmLegend, this.theme);
      a.divId = b;
      a.ieW = 0;
      var c;
      c = "object" != _typeof(b) && b ? document.getElementById(b) : b;
      this.legend = a;
      a.chart = this;
      c ? (a.div = c, a.position = "outside", a.autoMargins = !1) : a.div = this.legendDiv;
      return a;
    },
    removeLegend: function removeLegend() {
      this.legend = void 0;
      this.previousWidth = 0;
      this.legendDiv.innerHTML = "";
    },
    handleResize: function handleResize() {
      (d.isPercents(this.width) || d.isPercents(this.height)) && this.invalidateSizeReal();
      this.renderFix();
    },
    renderFix: function renderFix() {
      if (!d.VML) {
        var a = this.container;
        a && a.renderFix();
      }
    },
    getSVG: function getSVG() {
      if (d.hasSVG) return this.container;
    },
    animate: function animate(a, b, c, e, g, f, h) {
      a["an_" + b] && d.removeFromArray(this.animations, a["an_" + b]);
      c = {
        obj: a,
        frame: 0,
        attribute: b,
        from: c,
        to: e,
        time: g,
        effect: f,
        suffix: h
      };
      a["an_" + b] = c;
      this.animations.push(c);
      return c;
    },
    setLegendData: function setLegendData(a) {
      var b = this.legend;
      b && b.setData(a);
    },
    stopAnim: function stopAnim(a) {
      d.removeFromArray(this.animations, a);
    },
    updateAnimations: function updateAnimations() {
      var a;
      this.container && this.container.update();
      if (this.animations) for (a = this.animations.length - 1; 0 <= a; a--) {
        var b = this.animations[a],
            c = d.updateRate * b.time,
            e = b.frame + 1,
            g = b.obj,
            f = b.attribute;

        if (e <= c) {
          b.frame++;
          var h = Number(b.from),
              k = Number(b.to) - h,
              c = d[b.effect](0, e, h, k, c);
          0 === k ? (this.animations.splice(a, 1), g.node.style[f] = Number(b.to) + b.suffix) : g.node.style[f] = c + b.suffix;
        } else g.node.style[f] = Number(b.to) + b.suffix, g.animationFinished = !0, this.animations.splice(a, 1);
      }
    },
    update: function update() {
      this.updateAnimations();
      var a = this.animatable;

      if (0 < a.length) {
        for (var b = !0, c = a.length - 1; 0 <= c; c--) {
          var e = a[c];
          e && (e.animationFinished ? a.splice(c, 1) : b = !1);
        }

        b && (this.fire({
          type: "animationFinished",
          chart: this
        }), this.animatable = []);
      }
    },
    inIframe: function inIframe() {
      try {
        return window.self !== window.top;
      } catch (a) {
        return !0;
      }
    },
    brr: function brr() {},
    positionCred: function positionCred() {
      var a = this.amLink;

      if (a) {
        var b = this.creditsPosition,
            c = a.style,
            e = a.offsetWidth,
            a = a.offsetHeight,
            d = 0,
            f = 0,
            h = this.realWidth,
            k = this.realHeight,
            l = this.type;
        if ("serial" == l || "xy" == l || "gantt" == l) d = this.marginLeftReal, f = this.marginTopReal, h = d + this.plotAreaWidth, k = f + this.plotAreaHeight;
        var l = 5 + d,
            m = f + 5;
        "bottom-left" == b && (l = 5 + d, m = k - a - 3);
        "bottom-right" == b && (l = h - e - 5, m = k - a - 3);
        "top-right" == b && (l = h - e - 5, m = f + 5);
        c.left = l + "px";
        c.top = m + "px";
      }
    }
  });
  d.Slice = d.Class({
    construct: function construct() {}
  });
  d.SerialDataItem = d.Class({
    construct: function construct() {}
  });
  d.GraphDataItem = d.Class({
    construct: function construct() {}
  });
  d.Guide = d.Class({
    construct: function construct(a) {
      this.cname = "Guide";
      d.applyTheme(this, a, this.cname);
    }
  });
  d.Title = d.Class({
    construct: function construct(a) {
      this.cname = "Title";
      d.applyTheme(this, a, this.cname);
    }
  });
  d.Label = d.Class({
    construct: function construct(a) {
      this.cname = "Label";
      d.applyTheme(this, a, this.cname);
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.AmGraph = d.Class({
    construct: function construct(a) {
      this.cname = "AmGraph";
      this.createEvents("rollOverGraphItem", "rollOutGraphItem", "clickGraphItem", "doubleClickGraphItem", "rightClickGraphItem", "clickGraph", "rollOverGraph", "rollOutGraph");
      this.type = "line";
      this.stackable = !0;
      this.columnCount = 1;
      this.columnIndex = 0;
      this.centerCustomBullets = this.showBalloon = !0;
      this.maxBulletSize = 50;
      this.minBulletSize = 4;
      this.balloonText = "[[value]]";
      this.hidden = this.scrollbar = this.animationPlayed = !1;
      this.pointPosition = "middle";
      this.depthCount = 1;
      this.includeInMinMax = !0;
      this.negativeBase = 0;
      this.visibleInLegend = !0;
      this.showAllValueLabels = !1;
      this.showBulletsAt = this.showBalloonAt = "close";
      this.lineThickness = 1;
      this.dashLength = 0;
      this.connect = !0;
      this.lineAlpha = 1;
      this.bullet = "none";
      this.bulletBorderThickness = 2;
      this.bulletBorderAlpha = 0;
      this.bulletAlpha = 1;
      this.bulletSize = 8;
      this.cornerRadiusTop = this.hideBulletsCount = this.bulletOffset = 0;
      this.cursorBulletAlpha = 1;
      this.gradientOrientation = "vertical";
      this.dy = this.dx = 0;
      this.periodValue = "";
      this.clustered = !0;
      this.periodSpan = 1;
      this.accessibleLabel = "[[title]] [[category]] [[value]]";
      this.accessibleSkipText = "Press enter to skip [[title]]";
      this.y = this.x = 0;
      this.switchable = !0;
      this.minDistance = .8;
      this.tcc = 1;
      this.labelRotation = 0;
      this.labelAnchor = "auto";
      this.labelOffset = 3;
      this.bcn = "graph-";
      this.dateFormat = "MMM DD, YYYY";
      this.noRounding = !0;
      d.applyTheme(this, a, this.cname);
    },
    init: function init() {
      this.createBalloon();
    },
    draw: function draw() {
      var a = this.chart;
      a.isRolledOverBullet = !1;
      var b = a.type;

      if (a.drawGraphs) {
        isNaN(this.precision) || (this.numberFormatter ? this.numberFormatter.precision = this.precision : this.numberFormatter = {
          precision: this.precision,
          decimalSeparator: a.decimalSeparator,
          thousandsSeparator: a.thousandsSeparator
        });
        var c = a.container;
        this.container = c;
        this.destroy();
        var e = c.set();
        this.set = e;
        e.translate(this.x, this.y);
        var g = c.set();
        this.bulletSet = g;
        g.translate(this.x, this.y);
        this.behindColumns ? (a.graphsBehindSet.push(e), a.bulletBehindSet.push(g)) : (a.graphsSet.push(e), a.bulletSet.push(g));
        var f = this.bulletAxis;
        d.isString(f) && (this.bulletAxis = a.getValueAxisById(f));
        c = c.set();
        d.remove(this.columnsSet);
        this.columnsSet = c;
        d.setCN(a, e, "graph-" + this.type);
        d.setCN(a, e, "graph-" + this.id);
        d.setCN(a, g, "graph-" + this.type);
        d.setCN(a, g, "graph-" + this.id);
        this.columnsArray = [];
        this.ownColumns = [];
        this.allBullets = [];
        this.animationArray = [];
        g = this.labelPosition;
        g || (f = this.valueAxis.stackType, g = "top", "column" == this.type && (a.rotate && (g = "right"), "100%" == f || "regular" == f) && (g = "middle"), this.labelPosition = g);
        d.ifArray(this.data) && (a = !1, "xy" == b ? this.xAxis.axisCreated && this.yAxis.axisCreated && (a = !0) : this.valueAxis.axisCreated && (a = !0), !this.hidden && a && this.createGraph());
        e.push(c);
      }
    },
    createGraph: function createGraph() {
      var a = this,
          b = a.chart;
      a.startAlpha = b.startAlpha;
      a.seqAn = b.sequencedAnimation;
      a.baseCoord = a.valueAxis.baseCoord;
      void 0 === a.fillAlphas && (a.fillAlphas = 0);
      a.bulletColorR = a.bulletColor;
      void 0 === a.bulletColorR && (a.bulletColorR = a.lineColorR, a.bulletColorNegative = a.negativeLineColor);
      void 0 === a.bulletAlpha && (a.bulletAlpha = a.lineAlpha);
      if ("step" == c || d.VML) a.noRounding = !1;
      var c = b.type;
      "gantt" == c && (c = "serial");
      clearTimeout(a.playedTO);

      if (!isNaN(a.valueAxis.min) && !isNaN(a.valueAxis.max)) {
        switch (c) {
          case "serial":
            a.categoryAxis && (a.createSerialGraph(), "candlestick" == a.type && 1 > a.valueAxis.minMaxMultiplier && a.positiveClip(a.set));
            break;

          case "radar":
            a.createRadarGraph();
            break;

          case "xy":
            a.createXYGraph();
        }

        a.playedTO = setTimeout(function () {
          a.setAnimationPlayed.call(a);
        }, 500 * a.chart.startDuration);
      }
    },
    setAnimationPlayed: function setAnimationPlayed() {
      this.animationPlayed = !0;
    },
    createXYGraph: function createXYGraph() {
      var a = [],
          b = [],
          c = this.xAxis,
          e = this.yAxis;
      this.pmh = e.height;
      this.pmw = c.width;
      this.pmy = this.pmx = 0;
      var d;

      for (d = this.start; d <= this.end; d++) {
        var f = this.data[d].axes[c.id].graphs[this.id],
            h = f.values,
            k = h.x,
            l = h.y,
            h = c.getCoordinate(k, this.noRounding),
            m = e.getCoordinate(l, this.noRounding);

        if (!isNaN(k) && !isNaN(l) && (a.push(h), b.push(m), f.x = h, f.y = m, k = this.createBullet(f, h, m, d), l = this.labelText)) {
          var l = this.createLabel(f, l),
              n = 0;
          k && (n = k.size);
          this.positionLabel(f, h, m, l, n);
        }
      }

      this.drawLineGraph(a, b);
      this.launchAnimation();
    },
    createRadarGraph: function createRadarGraph() {
      var a = this.valueAxis.stackType,
          b = [],
          c = [],
          e = [],
          d = [],
          f,
          h,
          k,
          l,
          m;

      for (m = this.start; m <= this.end; m++) {
        var n = this.data[m].axes[this.valueAxis.id].graphs[this.id],
            q,
            p;
        "none" == a || "3d" == a ? q = n.values.value : (q = n.values.close, p = n.values.open);
        if (isNaN(q)) this.connect || (this.drawLineGraph(b, c, e, d), b = [], c = [], e = [], d = []);else {
          var t = this.valueAxis.getCoordinate(q, this.noRounding) - this.height,
              t = t * this.valueAxis.rMultiplier,
              r = -360 / (this.end - this.start + 1) * m;
          "middle" == this.valueAxis.pointPosition && (r -= 180 / (this.end - this.start + 1));
          q = t * Math.sin(r / 180 * Math.PI);
          t *= Math.cos(r / 180 * Math.PI);
          b.push(q);
          c.push(t);

          if (!isNaN(p)) {
            var w = this.valueAxis.getCoordinate(p, this.noRounding) - this.height,
                w = w * this.valueAxis.rMultiplier,
                z = w * Math.sin(r / 180 * Math.PI),
                r = w * Math.cos(r / 180 * Math.PI);
            e.push(z);
            d.push(r);
            isNaN(k) && (k = z);
            isNaN(l) && (l = r);
          }

          r = this.createBullet(n, q, t, m);
          n.x = q;
          n.y = t;
          if (z = this.labelText) z = this.createLabel(n, z), w = 0, r && (w = r.size), this.positionLabel(n, q, t, z, w);
          isNaN(f) && (f = q);
          isNaN(h) && (h = t);
        }
      }

      b.push(f);
      c.push(h);
      isNaN(k) || (e.push(k), d.push(l));
      this.drawLineGraph(b, c, e, d);
      this.launchAnimation();
    },
    positionLabel: function positionLabel(a, b, c, e, d) {
      if (e) {
        var f = this.chart,
            h = this.valueAxis,
            k = "middle",
            l = !1,
            m = this.labelPosition,
            n = e.getBBox(),
            q = this.chart.rotate,
            p = a.isNegative,
            t = this.fontSize;
        void 0 === t && (t = this.chart.fontSize);
        c -= n.height / 2 - t / 2 - 1;
        void 0 !== a.labelIsNegative && (p = a.labelIsNegative);

        switch (m) {
          case "right":
            m = q ? p ? "left" : "right" : "right";
            break;

          case "top":
            m = q ? "top" : p ? "bottom" : "top";
            break;

          case "bottom":
            m = q ? "bottom" : p ? "top" : "bottom";
            break;

          case "left":
            m = q ? p ? "right" : "left" : "left";
        }

        var t = a.columnGraphics,
            r = 0,
            w = 0;
        t && (r = t.x, w = t.y);
        var z = this.labelOffset;

        switch (m) {
          case "right":
            k = "start";
            b += d / 2 + z;
            break;

          case "top":
            c = h.reversed ? c + (d / 2 + n.height / 2 + z) : c - (d / 2 + n.height / 2 + z);
            break;

          case "bottom":
            c = h.reversed ? c - (d / 2 + n.height / 2 + z) : c + (d / 2 + n.height / 2 + z);
            break;

          case "left":
            k = "end";
            b -= d / 2 + z;
            break;

          case "inside":
            "column" == this.type && (l = !0, q ? p ? (k = "end", b = r - 3 - z) : (k = "start", b = r + 3 + z) : c = p ? w + 7 + z : w - 10 - z);
            break;

          case "middle":
            "column" == this.type && (l = !0, q ? b -= (b - r) / 2 + z - 3 : c -= (c - w) / 2 + z - 3);
        }

        "auto" != this.labelAnchor && (k = this.labelAnchor);
        e.attr({
          "text-anchor": k
        });
        this.labelRotation && e.rotate(this.labelRotation);
        e.translate(b, c);
        !this.showAllValueLabels && t && l && (n = e.getBBox(), n.height > a.columnHeight || n.width > a.columnWidth) && (e.remove(), e = null);
        if (e && "radar" != f.type) if (q) {
          if (0 > c || c > this.height) e.remove(), e = null;
          !this.showAllValueLabels && e && (0 > b || b > this.width) && (e.remove(), e = null);
        } else {
          if (0 > b || b > this.width) e.remove(), e = null;
          !this.showAllValueLabels && e && (0 > c || c > this.height) && (e.remove(), e = null);
        }
        e && this.allBullets.push(e);
        return e;
      }
    },
    getGradRotation: function getGradRotation() {
      var a = 270;
      "horizontal" == this.gradientOrientation && (a = 0);
      return this.gradientRotation = a;
    },
    createSerialGraph: function createSerialGraph() {
      this.dashLengthSwitched = this.fillColorsSwitched = this.lineColorSwitched = void 0;
      var a = this.chart,
          b = this.id,
          c = this.index,
          e = this.data,
          g = this.chart.container,
          f = this.valueAxis,
          h = this.type,
          k = this.columnWidthReal,
          l = this.showBulletsAt;
      isNaN(this.columnWidth) || (k = this.columnWidth);
      isNaN(k) && (k = .8);
      var m = this.useNegativeColorIfDown,
          n = this.width,
          q = this.height,
          p = this.y,
          t = this.rotate,
          r = this.columnCount,
          w = d.toCoordinate(this.cornerRadiusTop, k / 2),
          z = this.connect,
          x = [],
          u = [],
          A,
          y,
          B,
          D,
          C = this.chart.graphs.length,
          I,
          H = this.dx / this.tcc,
          Q = this.dy / this.tcc,
          M = f.stackType,
          P = this.start,
          ia = this.end,
          J = this.scrollbar,
          aa = "graph-column-";
      J && (aa = "scrollbar-graph-column-");
      var ma = this.categoryAxis,
          na = this.baseCoord,
          Pa = this.negativeBase,
          Z = this.columnIndex,
          da = this.lineThickness,
          X = this.lineAlpha,
          xa = this.lineColorR,
          ea = this.dashLength,
          fa = this.set,
          Ba,
          ga = this.getGradRotation(),
          V = this.chart.columnSpacing,
          Y = ma.cellWidth,
          Da = (Y * k - r) / r;
      V > Da && (V = Da);
      var G,
          v,
          oa,
          ha = q,
          Qa = n,
          ca = 0,
          tb = 0,
          ub = 0,
          vb = 0,
          lb = 0,
          mb = 0,
          wb = this.fillColorsR,
          Ra = this.negativeFillColors,
          Ja = this.negativeLineColor,
          bb = this.fillAlphas,
          cb = this.negativeFillAlphas;
      "object" == _typeof(bb) && (bb = bb[0]);
      "object" == _typeof(cb) && (cb = cb[0]);
      var xb = this.noRounding;
      "step" == h && (xb = !1);
      var nb = f.getCoordinate(f.min);
      f.logarithmic && (nb = f.getCoordinate(f.minReal));
      this.minCoord = nb;
      this.resetBullet && (this.bullet = "none");

      if (!(J || "line" != h && "smoothedLine" != h && "step" != h || (1 == e.length && "step" != h && "none" == this.bullet && (this.bullet = "round", this.resetBullet = !0), !Ra && void 0 == Ja || m))) {
        var Ua = Pa;
        Ua > f.max && (Ua = f.max);
        Ua < f.min && (Ua = f.min);
        f.logarithmic && (Ua = f.minReal);
        var Ka = f.getCoordinate(Ua) + .5,
            Mb = f.getCoordinate(f.max);
        t ? (ha = q, Qa = Math.abs(Mb - Ka), ub = q, vb = Math.abs(nb - Ka), mb = tb = 0, f.reversed ? (ca = 0, lb = Ka) : (ca = Ka, lb = 0)) : (Qa = n, ha = Math.abs(Mb - Ka), vb = n, ub = Math.abs(nb - Ka), lb = ca = 0, f.reversed ? (mb = p, tb = Ka) : mb = Ka);
      }

      var La = Math.round;
      this.pmx = La(ca);
      this.pmy = La(tb);
      this.pmh = La(ha);
      this.pmw = La(Qa);
      this.nmx = La(lb);
      this.nmy = La(mb);
      this.nmh = La(ub);
      this.nmw = La(vb);
      d.isModern || (this.nmy = this.nmx = 0, this.nmh = this.height);
      this.clustered || (r = 1);
      k = "column" == h ? (Y * k - V * (r - 1)) / r : Y * k;
      1 > k && (k = 1);
      var Nb = this.fixedColumnWidth;
      isNaN(Nb) || (k = Nb);
      var L;

      if ("line" == h || "step" == h || "smoothedLine" == h) {
        if (0 < P) {
          for (L = P - 1; -1 < L; L--) {
            if (G = e[L], v = G.axes[f.id].graphs[b], oa = v.values.value, !isNaN(oa)) {
              P = L;
              break;
            }
          }

          if (this.lineColorField) for (L = P; -1 < L; L--) {
            if (G = e[L], v = G.axes[f.id].graphs[b], v.lineColor) {
              this.lineColorSwitched = v.lineColor;
              void 0 === this.bulletColor && (this.bulletColorSwitched = this.lineColorSwitched);
              break;
            }
          }
          if (this.fillColorsField) for (L = P; -1 < L; L--) {
            if (G = e[L], v = G.axes[f.id].graphs[b], v.fillColors) {
              this.fillColorsSwitched = v.fillColors;
              break;
            }
          }
          if (this.dashLengthField) for (L = P; -1 < L; L--) {
            if (G = e[L], v = G.axes[f.id].graphs[b], !isNaN(v.dashLength)) {
              this.dashLengthSwitched = v.dashLength;
              break;
            }
          }
        }

        if (ia < e.length - 1) for (L = ia + 1; L < e.length; L++) {
          if (G = e[L], v = G.axes[f.id].graphs[b], oa = v.values.value, !isNaN(oa)) {
            ia = L;
            break;
          }
        }
      }

      ia < e.length - 1 && ia++;
      var T = [],
          U = [],
          Ma = !1;
      if ("line" == h || "step" == h || "smoothedLine" == h) if (this.stackable && "regular" == M || "100%" == M || this.fillToGraph) Ma = !0;
      var Ob = this.noStepRisers,
          db = -1E3,
          eb = -1E3,
          ob = this.minDistance,
          Sa = !0,
          Va = !1;

      for (L = P; L <= ia; L++) {
        G = e[L];
        v = G.axes[f.id].graphs[b];
        v.index = L;
        var fb,
            Ta = NaN;
        if (m && void 0 == this.openField) for (var yb = L + 1; yb < e.length && (!e[yb] || !(fb = e[L + 1].axes[f.id].graphs[b]) || !fb.values || (Ta = fb.values.value, isNaN(Ta))); yb++) {
          ;
        }
        var S,
            R,
            K,
            ba,
            ja = NaN,
            E = NaN,
            F = NaN,
            O = NaN,
            N = NaN,
            qa = NaN,
            ra = NaN,
            sa = NaN,
            ta = NaN,
            ya = NaN,
            Ea = NaN,
            ka = NaN,
            la = NaN,
            W = NaN,
            zb = NaN,
            Ab = NaN,
            ua = NaN,
            va = void 0,
            Na = wb,
            Wa = bb,
            Ha = xa,
            Ca,
            za,
            Bb = this.proCandlesticks,
            pb = this.topRadius,
            Fa = q - 1,
            pa = n - 1,
            gb = this.pattern;
        void 0 != v.pattern && (gb = v.pattern);
        isNaN(v.alpha) || (Wa = v.alpha);
        isNaN(v.dashLength) || (ea = v.dashLength);
        var Ia = v.values;
        f.recalculateToPercents && (Ia = v.percents);
        "none" == M && (Z = isNaN(v.columnIndex) ? this.columnIndex : v.columnIndex);

        if (Ia) {
          W = this.stackable && "none" != M && "3d" != M ? Ia.close : Ia.value;
          if ("candlestick" == h || "ohlc" == h) W = Ia.close, Ab = Ia.low, ra = f.getCoordinate(Ab), zb = Ia.high, ta = f.getCoordinate(zb);
          ua = Ia.open;
          F = f.getCoordinate(W, xb);
          isNaN(ua) || (N = f.getCoordinate(ua, xb), m && "regular" != M && "100%" != M && (Ta = ua, ua = N = NaN));
          m && (void 0 == this.openField ? fb && (fb.isNegative = Ta < W ? !0 : !1, isNaN(Ta) && (v.isNegative = !Sa)) : v.isNegative = Ta > W ? !0 : !1);
          if (!J) switch (this.showBalloonAt) {
            case "close":
              v.y = F;
              break;

            case "open":
              v.y = N;
              break;

            case "high":
              v.y = ta;
              break;

            case "low":
              v.y = ra;
          }
          var ja = G.x[ma.id],
              Xa = this.periodSpan - 1;
          "step" != h || isNaN(G.cellWidth) || (Y = G.cellWidth);
          var wa = Math.floor(Y / 2) + Math.floor(Xa * Y / 2),
              Ga = wa,
              qb = 0;
          "left" == this.stepDirection && (qb = (2 * Y + Xa * Y) / 2, ja -= qb);
          "center" == this.stepDirection && (qb = Y / 2, ja -= qb);
          "start" == this.pointPosition && (ja -= Y / 2 + Math.floor(Xa * Y / 2), wa = 0, Ga = Math.floor(Y) + Math.floor(Xa * Y));
          "end" == this.pointPosition && (ja += Y / 2 + Math.floor(Xa * Y / 2), wa = Math.floor(Y) + Math.floor(Xa * Y), Ga = 0);

          if (Ob) {
            var Cb = this.columnWidth;
            isNaN(Cb) || (wa *= Cb, Ga *= Cb);
          }

          J || (v.x = ja);
          -1E5 > ja && (ja = -1E5);
          ja > n + 1E5 && (ja = n + 1E5);
          t ? (E = F, O = N, N = F = ja, isNaN(ua) && !this.fillToGraph && (O = na), qa = ra, sa = ta) : (O = E = ja, isNaN(ua) && !this.fillToGraph && (N = na));
          if (!Bb && W < ua || Bb && W < Ba) v.isNegative = !0, Ra && (Na = Ra), cb && (Wa = cb), void 0 != Ja && (Ha = Ja);
          Va = !1;
          isNaN(W) || (m ? W > Ta ? (Sa && (Va = !0), Sa = !1) : (Sa || (Va = !0), Sa = !0) : v.isNegative = W < Pa ? !0 : !1, Ba = W);
          var Pb = !1;
          J && a.chartScrollbar.ignoreCustomColors && (Pb = !0);
          Pb || (void 0 != v.color && (Na = v.color), v.fillColors && (Na = v.fillColors));
          F = d.fitToBounds(F, -3E4, 3E4);

          switch (h) {
            case "line":
              if (isNaN(W)) z || (this.drawLineGraph(x, u, T, U), x = [], u = [], T = [], U = []);else {
                if (Math.abs(E - db) >= ob || Math.abs(F - eb) >= ob) x.push(E), u.push(F), db = E, eb = F;
                ya = E;
                Ea = F;
                ka = E;
                la = F;
                !Ma || isNaN(N) || isNaN(O) || (T.push(O), U.push(N));
                if (Va || void 0 != v.lineColor && v.lineColor != this.lineColorSwitched || void 0 != v.fillColors && v.fillColors != this.fillColorsSwitched || !isNaN(v.dashLength)) this.drawLineGraph(x, u, T, U), x = [E], u = [F], T = [], U = [], !Ma || isNaN(N) || isNaN(O) || (T.push(O), U.push(N)), m ? (Sa ? (this.lineColorSwitched = xa, this.fillColorsSwitched = wb) : (this.lineColorSwitched = Ja, this.fillColorsSwitched = Ra), void 0 === this.bulletColor && (this.bulletColorSwitched = xa)) : (this.lineColorSwitched = v.lineColor, this.fillColorsSwitched = v.fillColors, void 0 === this.bulletColor && (this.bulletColorSwitched = this.lineColorSwitched)), this.dashLengthSwitched = v.dashLength;
                v.gap && (this.drawLineGraph(x, u, T, U), x = [], u = [], T = [], U = [], eb = db = -1E3);
              }
              break;

            case "smoothedLine":
              if (isNaN(W)) z || (this.drawSmoothedGraph(x, u, T, U), x = [], u = [], T = [], U = []);else {
                if (Math.abs(E - db) >= ob || Math.abs(F - eb) >= ob) x.push(E), u.push(F), db = E, eb = F;
                ya = E;
                Ea = F;
                ka = E;
                la = F;
                !Ma || isNaN(N) || isNaN(O) || (T.push(O), U.push(N));
                if (Va || void 0 != v.lineColor && v.lineColor != this.lineColorSwitched || void 0 != v.fillColors && v.fillColors != this.fillColorsSwitched || !isNaN(v.dashLength)) this.drawSmoothedGraph(x, u, T, U), x = [E], u = [F], T = [], U = [], !Ma || isNaN(N) || isNaN(O) || (T.push(O), U.push(N)), this.lineColorSwitched = v.lineColor, this.fillColorsSwitched = v.fillColors, this.dashLengthSwitched = v.dashLength;
                v.gap && (this.drawSmoothedGraph(x, u, T, U), x = [], u = [], T = [], U = []);
              }
              break;

            case "step":
              if (!isNaN(W)) {
                t ? (isNaN(A) || (x.push(A), u.push(F - wa)), u.push(F - wa), x.push(E), u.push(F + Ga), x.push(E), !Ma || isNaN(N) || isNaN(O) || (isNaN(B) || (T.push(B), U.push(N - wa)), T.push(O), U.push(N - wa), T.push(O), U.push(N + Ga))) : (isNaN(y) || (u.push(y), x.push(E - wa)), x.push(E - wa), u.push(F), x.push(E + Ga), u.push(F), !Ma || isNaN(N) || isNaN(O) || (isNaN(D) || (T.push(O - wa), U.push(D)), T.push(O - wa), U.push(N), T.push(O + Ga), U.push(N)));
                A = E;
                y = F;
                B = O;
                D = N;
                ya = E;
                Ea = F;
                ka = E;
                la = F;

                if (Va || void 0 != v.lineColor || void 0 != v.fillColors || !isNaN(v.dashLength)) {
                  var Db = x[x.length - 2],
                      dc = u[u.length - 2];
                  x.pop();
                  u.pop();
                  T.pop();
                  U.pop();
                  this.drawLineGraph(x, u, T, U);
                  x = [Db];
                  u = [dc];
                  T = [];
                  U = [];
                  Ma && (T = [Db, Db + wa + Ga], U = [D, D]);
                  t ? (u.push(F + Ga), x.push(E)) : (x.push(E + Ga), u.push(F));
                  this.lineColorSwitched = v.lineColor;
                  this.fillColorsSwitched = v.fillColors;
                  this.dashLengthSwitched = v.dashLength;
                  m && (Sa ? (this.lineColorSwitched = xa, this.fillColorsSwitched = wb) : (this.lineColorSwitched = Ja, this.fillColorsSwitched = Ra));
                }

                if (Ob || v.gap) A = y = NaN, v.gap && 2 >= x.length || this.drawLineGraph(x, u, T, U), x = [], u = [], T = [], U = [];
              } else if (!z) {
                if (1 >= this.periodSpan || 1 < this.periodSpan && E - A > wa + Ga) A = y = NaN;
                this.drawLineGraph(x, u, T, U);
                x = [];
                u = [];
                T = [];
                U = [];
              }

              break;

            case "column":
              Ca = Ha;
              void 0 != v.lineColor && (Ca = v.lineColor);

              if (!isNaN(W)) {
                m || (v.isNegative = W < Pa ? !0 : !1);
                v.isNegative && (Ra && (Na = Ra), void 0 != Ja && (Ca = Ja));
                var Qb = f.min,
                    Rb = f.max,
                    rb = ua;
                isNaN(rb) && (rb = Pa);

                if (!(W < Qb && rb < Qb || W > Rb && rb > Rb)) {
                  var Aa;

                  if (t) {
                    "3d" == M ? (R = F - (r / 2 - this.depthCount + 1) * (k + V) + V / 2 + Q * Z, S = O + H * Z, Aa = Z) : (R = Math.floor(F - (r / 2 - Z) * (k + V) + V / 2), S = O, Aa = 0);
                    K = k;
                    ya = E;
                    Ea = R + k / 2;
                    ka = E;
                    la = R + k / 2;
                    R + K > q + Aa * Q && (K = q - R + Aa * Q);
                    R < Aa * Q && (K += R, R = Aa * Q);
                    ba = E - O;
                    var ec = S;
                    S = d.fitToBounds(S, 0, n);
                    ba += ec - S;
                    ba = d.fitToBounds(ba, -S, n - S + H * Z);
                    v.labelIsNegative = 0 > ba ? !0 : !1;
                    0 === ba && 1 / W === 1 / -0 && (v.labelIsNegative = !0);
                    isNaN(G.percentWidthValue) || (K = this.height * G.percentWidthValue / 100, R = ja - K / 2, Ea = R + K / 2);
                    K = d.roundTo(K, 2);
                    ba = d.roundTo(ba, 2);
                    R < q && 0 < K && (va = new d.Cuboid(g, ba, K, H - a.d3x, Q - a.d3y, Na, Wa, da, Ca, X, ga, w, t, ea, gb, pb, aa), v.columnWidth = Math.abs(ba), v.columnHeight = Math.abs(K));
                  } else {
                    "3d" == M ? (S = E - (r / 2 - this.depthCount + 1) * (k + V) + V / 2 + H * Z, R = N + Q * Z, Aa = Z) : (S = E - (r / 2 - Z) * (k + V) + V / 2, R = N, Aa = 0);
                    K = k;
                    ya = S + k / 2;
                    Ea = F;
                    ka = S + k / 2;
                    la = F;
                    S + K > n + Aa * H && (K = n - S + Aa * H);
                    S < Aa * H && (K += S - Aa * H, S = Aa * H);
                    ba = F - N;
                    v.labelIsNegative = 0 < ba ? !0 : !1;
                    0 === ba && 1 / W !== 1 / Math.abs(W) && (v.labelIsNegative = !0);
                    var fc = R;
                    R = d.fitToBounds(R, this.dy, q);
                    ba += fc - R;
                    ba = d.fitToBounds(ba, -R + Q * Aa, q - R);
                    isNaN(G.percentWidthValue) || (K = this.width * G.percentWidthValue / 100, S = ja - K / 2, ya = S + K / 2);
                    K = d.roundTo(K, 2);
                    ba = d.roundTo(ba, 2);
                    S < n + Z * H && 0 < K && (this.showOnAxis && (R -= Q / 2), va = new d.Cuboid(g, K, ba, H - a.d3x, Q - a.d3y, Na, Wa, da, Ca, this.lineAlpha, ga, w, t, ea, gb, pb, aa), v.columnHeight = Math.abs(ba), v.columnWidth = Math.abs(K));
                  }
                }

                if (va) {
                  za = va.set;
                  d.setCN(a, va.set, "graph-" + this.type);
                  d.setCN(a, va.set, "graph-" + this.id);
                  v.className && d.setCN(a, va.set, v.className, !0);
                  v.columnGraphics = za;
                  S = d.roundTo(S, 2);
                  R = d.roundTo(R, 2);
                  za.translate(S, R);
                  (v.url || this.showHandOnHover) && za.setAttr("cursor", "pointer");

                  if (!J) {
                    "none" == M && (I = t ? (this.end + 1 - L) * C - c : C * L + c);
                    "3d" == M && (t ? (I = (this.end + 1 - L) * C - c - 1E3 * this.depthCount, ya += H * Z, ka += H * Z, v.y += H * Z) : (I = (C - c) * (L + 1) + 1E3 * this.depthCount, Ea += Q * Z, la += Q * Z, v.y += Q * Z));
                    if ("regular" == M || "100%" == M) I = t ? 0 < Ia.value ? (this.end + 1 - L) * C + c + 1E3 * this.depthCount : (this.end + 1 - L) * C - c + 1E3 * this.depthCount : 0 < Ia.value ? C * L + c : C * L - c;
                    this.columnsArray.push({
                      column: va,
                      depth: I
                    });
                    v.x = t ? R + K / 2 : S + K / 2;
                    this.ownColumns.push(va);
                    this.animateColumns(va, L, E, O, F, N);
                    this.addListeners(za, v);
                    void 0 !== this.tabIndex && za.setAttr("tabindex", this.tabIndex);
                  }

                  this.columnsSet.push(za);
                }
              }

              break;

            case "candlestick":
              if (!isNaN(ua) && !isNaN(W)) {
                var Ya, hb;
                Ca = Ha;
                void 0 != v.lineColor && (Ca = v.lineColor);
                ya = E;
                la = Ea = F;
                ka = E;

                if (t) {
                  "open" == l && (ka = O);
                  "high" == l && (ka = sa);
                  "low" == l && (ka = qa);
                  E = d.fitToBounds(E, 0, pa);
                  O = d.fitToBounds(O, 0, pa);
                  qa = d.fitToBounds(qa, 0, pa);
                  sa = d.fitToBounds(sa, 0, pa);
                  if (0 === E && 0 === O && 0 === qa && 0 === sa) continue;
                  if (E == pa && O == pa && qa == pa && sa == pa) continue;
                  R = F - k / 2;
                  S = O;
                  K = k;
                  R + K > q && (K = q - R);
                  0 > R && (K += R, R = 0);

                  if (R < q && 0 < K) {
                    var Eb, Fb;
                    W > ua ? (Eb = [E, sa], Fb = [O, qa]) : (Eb = [O, sa], Fb = [E, qa]);
                    !isNaN(sa) && !isNaN(qa) && F < q && 0 < F && (Ya = d.line(g, Eb, [F, F], Ca, X, da), hb = d.line(g, Fb, [F, F], Ca, X, da));
                    ba = E - O;
                    va = new d.Cuboid(g, ba, K, H, Q, Na, bb, da, Ca, X, ga, w, t, ea, gb, pb, aa);
                  }
                } else {
                  "open" == l && (la = N);
                  "high" == l && (la = ta);
                  "low" == l && (la = ra);
                  F = d.fitToBounds(F, 0, Fa);
                  N = d.fitToBounds(N, 0, Fa);
                  ra = d.fitToBounds(ra, 0, Fa);
                  ta = d.fitToBounds(ta, 0, Fa);
                  if (0 === F && 0 === N && 0 === ra && 0 === ta) continue;
                  if (F == Fa && N == Fa && ra == Fa && ta == Fa) continue;
                  S = E - k / 2;
                  R = N + da / 2;
                  K = k;
                  S + K > n && (K = n - S);
                  0 > S && (K += S, S = 0);
                  ba = F - N;

                  if (S < n && 0 < K) {
                    Bb && W >= ua && (Wa = 0);
                    var va = new d.Cuboid(g, K, ba, H, Q, Na, Wa, da, Ca, X, ga, w, t, ea, gb, pb, aa),
                        Gb,
                        Hb;
                    W > ua ? (Gb = [F, ta], Hb = [N, ra]) : (Gb = [N, ta], Hb = [F, ra]);
                    !isNaN(ta) && !isNaN(ra) && E < n && 0 < E && (Ya = d.line(g, [E, E], Gb, Ca, X, da), hb = d.line(g, [E, E], Hb, Ca, X, da), d.setCN(a, Ya, this.bcn + "line-high"), v.className && d.setCN(a, Ya, v.className, !0), d.setCN(a, hb, this.bcn + "line-low"), v.className && d.setCN(a, hb, v.className, !0));
                  }
                }

                va && (za = va.set, v.columnGraphics = za, fa.push(za), za.translate(S, R - da / 2), (v.url || this.showHandOnHover) && za.setAttr("cursor", "pointer"), Ya && (fa.push(Ya), fa.push(hb)), J || (v.x = t ? R + K / 2 : S + K / 2, this.animateColumns(va, L, E, O, F, N), this.addListeners(za, v), void 0 !== this.tabIndex && za.setAttr("tabindex", this.tabIndex)));
              }

              break;

            case "ohlc":
              if (!(isNaN(ua) || isNaN(zb) || isNaN(Ab) || isNaN(W))) {
                var Sb = g.set();
                fa.push(Sb);
                W < ua && (v.isNegative = !0, void 0 != Ja && (Ha = Ja));
                void 0 != v.lineColor && (Ha = v.lineColor);
                var Za, $a, ab;

                if (t) {
                  la = F;
                  ka = E;
                  "open" == l && (ka = O);
                  "high" == l && (ka = sa);
                  "low" == l && (ka = qa);
                  qa = d.fitToBounds(qa, 0, pa);
                  sa = d.fitToBounds(sa, 0, pa);
                  if (0 === E && 0 === O && 0 === qa && 0 === sa) continue;
                  if (E == pa && O == pa && qa == pa && sa == pa) continue;
                  var Ib = F - k / 2,
                      Ib = d.fitToBounds(Ib, 0, q),
                      Tb = d.fitToBounds(F, 0, q),
                      Jb = F + k / 2,
                      Jb = d.fitToBounds(Jb, 0, q);
                  0 <= O && O <= pa && ($a = d.line(g, [O, O], [Ib, Tb], Ha, X, da, ea));
                  0 < F && F < q && (Za = d.line(g, [qa, sa], [F, F], Ha, X, da, ea));
                  0 <= E && E <= pa && (ab = d.line(g, [E, E], [Tb, Jb], Ha, X, da, ea));
                } else {
                  la = F;
                  "open" == l && (la = N);
                  "high" == l && (la = ta);
                  "low" == l && (la = ra);
                  var ka = E,
                      ra = d.fitToBounds(ra, 0, Fa),
                      ta = d.fitToBounds(ta, 0, Fa),
                      Kb = E - k / 2,
                      Kb = d.fitToBounds(Kb, 0, n),
                      Ub = d.fitToBounds(E, 0, n),
                      Lb = E + k / 2,
                      Lb = d.fitToBounds(Lb, 0, n);
                  0 <= N && N <= Fa && ($a = d.line(g, [Kb, Ub], [N, N], Ha, X, da, ea));
                  0 < E && E < n && (Za = d.line(g, [E, E], [ra, ta], Ha, X, da, ea));
                  0 <= F && F <= Fa && (ab = d.line(g, [Ub, Lb], [F, F], Ha, X, da, ea));
                }

                fa.push($a);
                fa.push(Za);
                fa.push(ab);
                d.setCN(a, $a, this.bcn + "stroke-open");
                d.setCN(a, ab, this.bcn + "stroke-close");
                d.setCN(a, Za, this.bcn + "stroke");
                v.className && d.setCN(a, Sb, v.className, !0);
                Za && this.addListeners(Za, v);
                ab && this.addListeners(ab, v);
                $a && this.addListeners($a, v);
                ya = E;
                Ea = F;
              }

          }

          if (!J && !isNaN(W)) {
            var Vb = this.hideBulletsCount;

            if (this.end - this.start <= Vb || 0 === Vb) {
              var Wb = this.createBullet(v, ka, la, L),
                  Xb = this.labelText;

              if (Xb && !isNaN(ya) && !isNaN(ya)) {
                var gc = this.createLabel(v, Xb),
                    Yb = 0;
                Wb && (Yb = Wb.size);
                this.positionLabel(v, ya, Ea, gc, Yb);
              }

              if ("regular" == M || "100%" == M) {
                var Zb = f.totalText;

                if (Zb) {
                  var Oa = this.createLabel(v, Zb, f.totalTextColor);
                  d.setCN(a, Oa, this.bcn + "label-total");
                  this.allBullets.push(Oa);

                  if (Oa) {
                    var $b = Oa.getBBox(),
                        ac = $b.width,
                        bc = $b.height,
                        ib,
                        jb,
                        sb = f.totalTextOffset,
                        cc = f.totals[L];
                    cc && cc.remove();
                    var kb = 0;
                    "column" != h && (kb = this.bulletSize);
                    t ? (jb = Ea, ib = 0 > W ? E - ac / 2 - 2 - kb - sb : E + ac / 2 + 3 + kb + sb) : (ib = ya, jb = 0 > W ? F + bc / 2 + kb + sb : F - bc / 2 - 3 - kb - sb);
                    Oa.translate(ib, jb);
                    f.totals[L] = Oa;
                    t ? (0 > jb || jb > q) && Oa.remove() : (0 > ib || ib > n) && Oa.remove();
                  }
                }
              }
            }
          }
        }
      }

      this.lastDataItem = v;
      if ("line" == h || "step" == h || "smoothedLine" == h) "smoothedLine" == h ? this.drawSmoothedGraph(x, u, T, U) : this.drawLineGraph(x, u, T, U), J || this.launchAnimation();
      this.bulletsHidden && this.hideBullets();
      this.customBulletsHidden && this.hideCustomBullets();
    },
    animateColumns: function animateColumns(a, b) {
      var c = this,
          e = c.chart.startDuration;
      0 < e && !c.animationPlayed && (c.seqAn ? (a.set.hide(), c.animationArray.push(a), e = setTimeout(function () {
        c.animate.call(c);
      }, e / (c.end - c.start + 1) * (b - c.start) * 1E3), c.timeOuts.push(e)) : c.animate(a), c.chart.animatable.push(a));
    },
    createLabel: function createLabel(a, b, c) {
      var e = this.chart,
          g = a.labelColor;
      g || (g = this.color);
      g || (g = e.color);
      c && (g = c);
      c = this.fontSize;
      void 0 === c && (this.fontSize = c = e.fontSize);
      var f = this.labelFunction;
      b = e.formatString(b, a);
      b = d.cleanFromEmpty(b);
      f && (b = f(a, b));
      if (void 0 !== b && "" !== b) return a = d.text(this.container, b, g, e.fontFamily, c), a.node.style.pointerEvents = "none", d.setCN(e, a, this.bcn + "label"), this.bulletSet.push(a), a;
    },
    positiveClip: function positiveClip(a) {
      a.clipRect(this.pmx, this.pmy, this.pmw, this.pmh);
    },
    negativeClip: function negativeClip(a) {
      a.clipRect(this.nmx, this.nmy, this.nmw, this.nmh);
    },
    drawLineGraph: function drawLineGraph(a, b, c, e) {
      var g = this;

      if (1 < a.length) {
        var f = g.noRounding,
            h = g.set,
            k = g.chart,
            l = g.container,
            m = l.set(),
            n = l.set();
        h.push(n);
        h.push(m);
        var q = g.lineAlpha,
            p = g.lineThickness,
            h = g.fillAlphas,
            t = g.lineColorR,
            r = g.negativeLineAlpha;
        isNaN(r) && (r = q);
        var w = g.lineColorSwitched;
        w && (t = w);
        var w = g.fillColorsR,
            z = g.fillColorsSwitched;
        z && (w = z);
        var x = g.dashLength;
        (z = g.dashLengthSwitched) && (x = z);
        var z = g.negativeLineColor,
            u = g.negativeFillColors,
            A = g.negativeFillAlphas,
            y = g.baseCoord;
        0 !== g.negativeBase && (y = g.valueAxis.getCoordinate(g.negativeBase, f), y > g.height && (y = g.height), 0 > y && (y = 0));
        q = d.line(l, a, b, t, q, p, x, !1, !1, f);
        q.node.setAttribute("stroke-linejoin", "round");
        d.setCN(k, q, g.bcn + "stroke");
        m.push(q);
        m.click(function (a) {
          g.handleGraphEvent(a, "clickGraph");
        }).mouseover(function (a) {
          g.handleGraphEvent(a, "rollOverGraph");
        }).mouseout(function (a) {
          g.handleGraphEvent(a, "rollOutGraph");
        }).touchmove(function (a) {
          g.chart.handleMouseMove(a);
        }).touchend(function (a) {
          g.chart.handleTouchEnd(a);
        });
        void 0 === z || g.useNegativeColorIfDown || (p = d.line(l, a, b, z, r, p, x, !1, !1, f), p.node.setAttribute("stroke-linejoin", "round"), d.setCN(k, p, g.bcn + "stroke"), d.setCN(k, p, g.bcn + "stroke-negative"), n.push(p));
        if (0 < h || 0 < A) if (p = a.join(";").split(";"), r = b.join(";").split(";"), q = k.type, "serial" == q || "radar" == q ? 0 < c.length ? (c.reverse(), e.reverse(), p = a.concat(c), r = b.concat(e)) : "radar" == q ? (r.push(0), p.push(0)) : g.rotate ? (r.push(r[r.length - 1]), p.push(y), r.push(r[0]), p.push(y), r.push(r[0]), p.push(p[0])) : (p.push(p[p.length - 1]), r.push(y), p.push(p[0]), r.push(y), p.push(a[0]), r.push(r[0])) : "xy" == q && (b = g.fillToAxis) && (d.isString(b) && (b = k.getValueAxisById(b)), "H" == b.orientation ? (y = "top" == b.position ? 0 : b.height, p.push(p[p.length - 1]), r.push(y), p.push(p[0]), r.push(y), p.push(a[0]), r.push(r[0])) : (y = "left" == b.position ? 0 : b.width, r.push(r[r.length - 1]), p.push(y), r.push(r[0]), p.push(y), r.push(r[0]), p.push(p[0]))), a = g.gradientRotation, 0 < h && (b = d.polygon(l, p, r, w, h, 1, "#000", 0, a, f), b.pattern(g.pattern, NaN, k.path), d.setCN(k, b, g.bcn + "fill"), m.push(b), b.toBack()), u || void 0 !== z) isNaN(A) && (A = h), u || (u = z), f = d.polygon(l, p, r, u, A, 1, "#000", 0, a, f), d.setCN(k, f, g.bcn + "fill"), d.setCN(k, f, g.bcn + "fill-negative"), f.pattern(g.pattern, NaN, k.path), n.push(f), f.toBack(), n.click(function (a) {
          g.handleGraphEvent(a, "clickGraph");
        }).mouseover(function (a) {
          g.handleGraphEvent(a, "rollOverGraph");
        }).mouseout(function (a) {
          g.handleGraphEvent(a, "rollOutGraph");
        }).touchmove(function (a) {
          g.chart.handleMouseMove(a);
        }).touchend(function (a) {
          g.chart.handleTouchEnd(a);
        });
        g.applyMask(n, m);
      }
    },
    applyMask: function applyMask(a, b) {
      var c = a.length();
      "serial" != this.chart.type || this.scrollbar || (this.positiveClip(b), 0 < c && this.negativeClip(a));
    },
    drawSmoothedGraph: function drawSmoothedGraph(a, b, c, e) {
      if (1 < a.length) {
        var g = this.set,
            f = this.chart,
            h = this.container,
            k = h.set(),
            l = h.set();
        g.push(l);
        g.push(k);
        var m = this.lineAlpha,
            n = this.lineThickness,
            g = this.dashLength,
            q = this.fillAlphas,
            p = this.lineColorR,
            t = this.fillColorsR,
            r = this.negativeLineColor,
            w = this.negativeFillColors,
            z = this.negativeFillAlphas,
            x = this.baseCoord,
            u = this.lineColorSwitched;
        u && (p = u);
        (u = this.fillColorsSwitched) && (t = u);
        var A = this.negativeLineAlpha;
        isNaN(A) && (A = m);
        u = this.getGradRotation();
        m = new d.Bezier(h, a, b, p, m, n, t, 0, g, void 0, u);
        d.setCN(f, m, this.bcn + "stroke");
        k.push(m.path);
        void 0 !== r && (n = new d.Bezier(h, a, b, r, A, n, t, 0, g, void 0, u), d.setCN(f, n, this.bcn + "stroke"), d.setCN(f, n, this.bcn + "stroke-negative"), l.push(n.path));
        0 < q && (n = a.join(";").split(";"), m = b.join(";").split(";"), p = "", 0 < c.length ? (c.push("M"), e.push("M"), c.reverse(), e.reverse(), n = a.concat(c), m = b.concat(e)) : (this.rotate ? (p += " L" + x + "," + b[b.length - 1], p += " L" + x + "," + b[0]) : (p += " L" + a[a.length - 1] + "," + x, p += " L" + a[0] + "," + x), p += " L" + a[0] + "," + b[0]), a = new d.Bezier(h, n, m, NaN, 0, 0, t, q, g, p, u), d.setCN(f, a, this.bcn + "fill"), a.path.pattern(this.pattern, NaN, f.path), k.push(a.path), w || void 0 !== r) && (z || (z = q), w || (w = r), h = new d.Bezier(h, n, m, NaN, 0, 0, w, z, g, p, u), h.path.pattern(this.pattern, NaN, f.path), d.setCN(f, h, this.bcn + "fill"), d.setCN(f, h, this.bcn + "fill-negative"), l.push(h.path));
        this.applyMask(l, k);
      }
    },
    launchAnimation: function launchAnimation() {
      var a = this,
          b = a.chart.startDuration;

      if (0 < b && !a.animationPlayed) {
        var c = a.set,
            e = a.bulletSet;
        d.VML || (c.attr({
          opacity: a.startAlpha
        }), e.attr({
          opacity: a.startAlpha
        }));
        c.hide();
        e.hide();
        a.seqAn ? (b = setTimeout(function () {
          a.animateGraphs.call(a);
        }, a.index * b * 1E3), a.timeOuts.push(b)) : a.animateGraphs();
      }
    },
    animateGraphs: function animateGraphs() {
      var a = this.chart,
          b = this.set,
          c = this.bulletSet,
          e = this.x,
          d = this.y;
      b.show();
      c.show();
      var f = a.startDuration,
          h = a.startEffect;
      b && (this.rotate ? (b.translate(-1E3, d), c.translate(-1E3, d)) : (b.translate(e, -1E3), c.translate(e, -1E3)), b.animate({
        opacity: 1,
        translate: e + "," + d
      }, f, h), c.animate({
        opacity: 1,
        translate: e + "," + d
      }, f, h), a.animatable.push(b));
    },
    animate: function animate(a) {
      var b = this.chart,
          c = this.animationArray;
      !a && 0 < c.length && (a = c[0], c.shift());
      c = d[d.getEffect(b.startEffect)];
      b = b.startDuration;
      a && (this.rotate ? a.animateWidth(b, c) : a.animateHeight(b, c), a.set.show());
    },
    legendKeyColor: function legendKeyColor() {
      var a = this.legendColor,
          b = this.lineAlpha;
      void 0 === a && (a = this.lineColorR, 0 === b && (b = this.fillColorsR) && (a = "object" == _typeof(b) ? b[0] : b));
      return a;
    },
    legendKeyAlpha: function legendKeyAlpha() {
      var a = this.legendAlpha;
      void 0 === a && (a = this.lineAlpha, this.fillAlphas > a && (a = this.fillAlphas), 0 === a && (a = this.bulletAlpha), 0 === a && (a = 1));
      return a;
    },
    createBullet: function createBullet(a, b, c) {
      if (!isNaN(b) && !isNaN(c) && ("none" != this.bullet || this.customBullet || a.bullet || a.customBullet)) {
        var e = this.chart,
            g = this.container,
            f = this.bulletOffset,
            h = this.bulletSize;
        isNaN(a.bulletSize) || (h = a.bulletSize);
        var k = a.values.value,
            l = this.maxValue,
            m = this.minValue,
            n = this.maxBulletSize,
            q = this.minBulletSize;
        isNaN(l) || (isNaN(k) || (h = (k - m) / (l - m) * (n - q) + q), m == l && (h = n));
        l = h;
        this.bulletAxis && (h = a.values.error, isNaN(h) || (k = h), h = this.bulletAxis.stepWidth * k);
        h < this.minBulletSize && (h = this.minBulletSize);
        this.rotate ? b = a.isNegative ? b - f : b + f : c = a.isNegative ? c + f : c - f;
        q = this.bulletColorR;
        a.lineColor && void 0 === this.bulletColor && (this.bulletColorSwitched = a.lineColor);
        this.bulletColorSwitched && (q = this.bulletColorSwitched);
        a.isNegative && void 0 !== this.bulletColorNegative && (q = this.bulletColorNegative);
        void 0 !== a.color && (q = a.color);
        var p;
        "xy" == e.type && this.valueField && (p = this.pattern, a.pattern && (p = a.pattern));
        f = this.bullet;
        a.bullet && (f = a.bullet);
        var k = this.bulletBorderThickness,
            m = this.bulletBorderColorR,
            n = this.bulletBorderAlpha,
            t = this.bulletAlpha;
        m || (m = q);
        this.useLineColorForBulletBorder && (m = this.lineColorR, a.isNegative && this.negativeLineColor && (m = this.negativeLineColor), this.lineColorSwitched && (m = this.lineColorSwitched));
        var r = a.alpha;
        isNaN(r) || (t = r);
        p = d.bullet(g, f, h, q, t, k, m, n, l, 0, p, e.path);
        l = this.customBullet;
        a.customBullet && (l = a.customBullet);
        l && (p && p.remove(), "function" == typeof l ? (l = new l(), l.chart = e, a.bulletConfig && (l.availableSpace = c, l.graph = this, l.graphDataItem = a, l.bulletY = c, a.bulletConfig.minCoord = this.minCoord - c, l.bulletConfig = a.bulletConfig), l.write(g), p && l.showBullet && l.set.push(p), a.customBulletGraphics = l.cset, p = l.set) : (p = g.set(), l = g.image(l, 0, 0, h, h), p.push(l), this.centerCustomBullets && l.translate(-h / 2, -h / 2)));

        if (p) {
          (a.url || this.showHandOnHover) && p.setAttr("cursor", "pointer");
          if ("serial" == e.type || "gantt" == e.type) if (-.5 > b || b > this.width || c < -h / 2 || c > this.height) p.remove(), p = null;
          p && (this.bulletSet.push(p), p.translate(b, c), this.addListeners(p, a), this.allBullets.push(p));
          a.bx = b;
          a.by = c;
          d.setCN(e, p, this.bcn + "bullet");
          a.className && d.setCN(e, p, a.className, !0);
        }

        if (p) {
          p.size = h || 0;
          if (e = this.bulletHitAreaSize) g = d.circle(g, e, "#FFFFFF", .001, 0), g.translate(b, c), a.hitBullet = g, this.bulletSet.push(g), this.addListeners(g, a);
          a.bulletGraphics = p;
          void 0 !== this.tabIndex && p.setAttr("tabindex", this.tabIndex);
        } else p = {
          size: 0
        };

        p.graphDataItem = a;
        return p;
      }
    },
    showBullets: function showBullets() {
      var a = this.allBullets,
          b;
      this.bulletsHidden = !1;

      for (b = 0; b < a.length; b++) {
        a[b].show();
      }
    },
    hideBullets: function hideBullets() {
      var a = this.allBullets,
          b;
      this.bulletsHidden = !0;

      for (b = 0; b < a.length; b++) {
        a[b].hide();
      }
    },
    showCustomBullets: function showCustomBullets() {
      var a = this.allBullets,
          b;
      this.customBulletsHidden = !1;

      for (b = 0; b < a.length; b++) {
        var c = a[b].graphDataItem;
        c && c.customBulletGraphics && c.customBulletGraphics.show();
      }
    },
    hideCustomBullets: function hideCustomBullets() {
      var a = this.allBullets,
          b;
      this.customBulletsHidden = !0;

      for (b = 0; b < a.length; b++) {
        var c = a[b].graphDataItem;
        c && c.customBulletGraphics && c.customBulletGraphics.hide();
      }
    },
    addListeners: function addListeners(a, b) {
      var c = this;
      a.mouseover(function (a) {
        c.handleRollOver(b, a);
      }).mouseout(function (a) {
        c.handleRollOut(b, a);
      }).touchend(function (a) {
        c.handleRollOver(b, a);
        c.chart.panEventsEnabled && c.handleClick(b, a);
      }).touchstart(function (a) {
        c.handleRollOver(b, a);
      }).click(function (a) {
        c.handleClick(b, a);
      }).dblclick(function (a) {
        c.handleDoubleClick(b, a);
      }).contextmenu(function (a) {
        c.handleRightClick(b, a);
      });
      var e = c.chart;

      if (e.accessible && c.accessibleLabel) {
        var d = e.formatString(c.accessibleLabel, b);
        e.makeAccessible(a, d);
      }
    },
    handleRollOver: function handleRollOver(a, b) {
      this.handleGraphEvent(b, "rollOverGraph");

      if (a) {
        var c = this.chart;
        a.bulletConfig && (c.isRolledOverBullet = !0);
        var e = {
          type: "rollOverGraphItem",
          item: a,
          index: a.index,
          graph: this,
          target: this,
          chart: this.chart,
          event: b
        };
        this.fire(e);
        c.fire(e);
        clearTimeout(c.hoverInt);
        (c = c.chartCursor) && c.valueBalloonsEnabled || this.showGraphBalloon(a, "V", !0);
      }
    },
    handleRollOut: function handleRollOut(a, b) {
      var c = this.chart;

      if (a) {
        var e = {
          type: "rollOutGraphItem",
          item: a,
          index: a.index,
          graph: this,
          target: this,
          chart: this.chart,
          event: b
        };
        this.fire(e);
        c.fire(e);
        c.isRolledOverBullet = !1;
      }

      this.handleGraphEvent(b, "rollOutGraph");
      (c = c.chartCursor) && c.valueBalloonsEnabled || this.hideBalloon();
    },
    handleClick: function handleClick(a, b) {
      if (!this.chart.checkTouchMoved() && this.chart.checkTouchDuration(b)) {
        if (a) {
          var c = {
            type: "clickGraphItem",
            item: a,
            index: a.index,
            graph: this,
            target: this,
            chart: this.chart,
            event: b
          };
          this.fire(c);
          this.chart.fire(c);
          d.getURL(a.url, this.urlTarget);
        }

        this.handleGraphEvent(b, "clickGraph");
      }
    },
    handleGraphEvent: function handleGraphEvent(a, b) {
      var c = {
        type: b,
        graph: this,
        target: this,
        chart: this.chart,
        event: a
      };
      this.fire(c);
      this.chart.fire(c);
    },
    handleRightClick: function handleRightClick(a, b) {
      if (a) {
        var c = {
          type: "rightClickGraphItem",
          item: a,
          index: a.index,
          graph: this,
          target: this,
          chart: this.chart,
          event: b
        };
        this.fire(c);
        this.chart.fire(c);
      }
    },
    handleDoubleClick: function handleDoubleClick(a, b) {
      if (a) {
        var c = {
          type: "doubleClickGraphItem",
          item: a,
          index: a.index,
          graph: this,
          target: this,
          chart: this.chart,
          event: b
        };
        this.fire(c);
        this.chart.fire(c);
      }
    },
    zoom: function zoom(a, b) {
      this.start = a;
      this.end = b;
      this.draw();
    },
    changeOpacity: function changeOpacity(a) {
      var b = this.set;
      b && b.setAttr("opacity", a);

      if (b = this.ownColumns) {
        var c;

        for (c = 0; c < b.length; c++) {
          var e = b[c].set;
          e && e.setAttr("opacity", a);
        }
      }

      (b = this.bulletSet) && b.setAttr("opacity", a);
    },
    destroy: function destroy() {
      d.remove(this.set);
      d.remove(this.bulletSet);
      var a = this.timeOuts;

      if (a) {
        var b;

        for (b = 0; b < a.length; b++) {
          clearTimeout(a[b]);
        }
      }

      this.timeOuts = [];
    },
    createBalloon: function createBalloon() {
      var a = this.chart;
      this.balloon ? this.balloon.destroy && this.balloon.destroy() : this.balloon = {};
      var b = this.balloon;
      d.extend(b, a.balloon, !0);
      b.chart = a;
      b.mainSet = a.plotBalloonsSet;
      b.className = this.id;
    },
    hideBalloon: function hideBalloon() {
      var a = this,
          b = a.chart;
      b.chartCursor ? b.chartCursor.valueBalloonsEnabled || b.hideBalloon() : b.hideBalloon();
      clearTimeout(a.hoverInt);
      a.hoverInt = setTimeout(function () {
        a.hideBalloonReal.call(a);
      }, b.hideBalloonTime);
    },
    hideBalloonReal: function hideBalloonReal() {
      this.balloon && this.balloon.hide();
      this.fixBulletSize();
    },
    fixBulletSize: function fixBulletSize() {
      if (d.isModern) {
        var a = this.resizedDItem;

        if (a) {
          var b = a.bulletGraphics;

          if (b && !b.doNotScale) {
            b.translate(a.bx, a.by, 1);
            var c = this.bulletAlpha;
            isNaN(a.alpha) || (c = a.alpha);
            b.setAttr("fill-opacity", c);
            b.setAttr("stroke-opacity", this.bulletBorderAlpha);
          }
        }

        this.resizedDItem = null;
      }
    },
    showGraphBalloon: function showGraphBalloon(a, b, c, e, g) {
      if (a) {
        var f = this.chart,
            h = this.balloon,
            k = 0,
            l = 0,
            m = f.chartCursor,
            n = !0;
        m ? m.valueBalloonsEnabled || (h = f.balloon, k = this.x, l = this.y, n = !1) : (h = f.balloon, k = this.x, l = this.y, n = !1);
        clearTimeout(this.hoverInt);

        if (f.chartCursor && (this.currentDataItem = a, "serial" == f.type && f.isRolledOverBullet && f.chartCursor.valueBalloonsEnabled)) {
          this.hideBalloonReal();
          return;
        }

        this.resizeBullet(a, e, g);

        if (h && h.enabled && this.showBalloon && !this.hidden) {
          var m = f.formatString(this.balloonText, a, !0),
              q = this.balloonFunction;
          q && (m = q(a, a.graph));
          m && (m = d.cleanFromEmpty(m));
          m && "" !== m ? (e = f.getBalloonColor(this, a), h.drop || (h.pointerOrientation = b), b = a.x, g = a.y, f.rotate && (b = a.y, g = a.x), b += k, g += l, isNaN(b) || isNaN(g) ? this.hideBalloonReal() : (a = this.width, q = this.height, n && h.setBounds(k, l, a + k, q + l), h.changeColor(e), h.setPosition(b, g), h.fixPrevious(), h.fixedPosition && (c = !1), !c && "radar" != f.type && (b < k - .5 || b > a + k || g < l - .5 || g > q + l) ? (h.showBalloon(m), h.hide(0)) : (h.followCursor(c), h.showBalloon(m)))) : (this.hideBalloonReal(), h.hide(), this.resizeBullet(a, e, g));
        } else this.hideBalloonReal();
      }
    },
    resizeBullet: function resizeBullet(a, b, c) {
      this.fixBulletSize();

      if (a && d.isModern && (1 != b || !isNaN(c))) {
        var e = a.bulletGraphics;
        e && !e.doNotScale && (e.translate(a.bx, a.by, b), isNaN(c) || (e.setAttr("fill-opacity", c), e.setAttr("stroke-opacity", c)), this.resizedDItem = a);
      }
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.ChartCursor = d.Class({
    construct: function construct(a) {
      this.cname = "ChartCursor";
      this.createEvents("changed", "zoomed", "onHideCursor", "onShowCursor", "draw", "selected", "moved", "panning", "zoomStarted");
      this.enabled = !0;
      this.cursorAlpha = 1;
      this.selectionAlpha = .2;
      this.cursorColor = "#CC0000";
      this.categoryBalloonAlpha = 1;
      this.color = "#FFFFFF";
      this.type = "cursor";
      this.zoomed = !1;
      this.zoomable = !0;
      this.pan = !1;
      this.categoryBalloonDateFormat = "MMM DD, YYYY";
      this.categoryBalloonText = "[[category]]";
      this.categoryBalloonEnabled = this.valueBalloonsEnabled = !0;
      this.rolledOver = !1;
      this.cursorPosition = "middle";
      this.bulletsEnabled = this.skipZoomDispatch = !1;
      this.bulletSize = 8;
      this.selectWithoutZooming = this.oneBalloonOnly = !1;
      this.graphBulletSize = 1.7;
      this.animationDuration = .3;
      this.zooming = !1;
      this.adjustment = 0;
      this.avoidBalloonOverlapping = !0;
      this.leaveCursor = !1;
      this.leaveAfterTouch = !0;
      this.valueZoomable = !1;
      this.balloonPointerOrientation = "horizontal";
      this.hLineEnabled = this.vLineEnabled = !0;
      this.vZoomEnabled = this.hZoomEnabled = !1;
      d.applyTheme(this, a, this.cname);
    },
    draw: function draw() {
      this.destroy();
      var a = this.chart;
      a.panRequired = !0;
      var b = a.container;
      this.rotate = a.rotate;
      this.container = b;
      this.prevLineHeight = this.prevLineWidth = NaN;
      b = b.set();
      b.translate(this.x, this.y);
      this.set = b;
      a.cursorSet.push(b);
      this.createElements();
      d.isString(this.limitToGraph) && (this.limitToGraph = d.getObjById(a.graphs, this.limitToGraph), this.fullWidth = !1, this.cursorPosition = "middle");
      this.pointer = this.balloonPointerOrientation.substr(0, 1).toUpperCase();
      this.isHidden = !1;
      this.hideLines();
      this.valueLineAxis || (this.valueLineAxis = a.valueAxes[0]);
    },
    createElements: function createElements() {
      var a = this,
          b = a.chart,
          c = b.dx,
          e = b.dy,
          g = a.width,
          f = a.height,
          h,
          k,
          l = a.cursorAlpha,
          m = a.valueLineAlpha;
      a.rotate ? (h = m, k = l) : (k = m, h = l);
      "xy" == b.type && (k = l, void 0 !== m && (k = m), h = l);
      a.vvLine = d.line(a.container, [c, 0, 0], [e, 0, f], a.cursorColor, h, 1);
      d.setCN(b, a.vvLine, "cursor-line");
      d.setCN(b, a.vvLine, "cursor-line-vertical");
      a.hhLine = d.line(a.container, [0, g, g + c], [0, 0, e], a.cursorColor, k, 1);
      d.setCN(b, a.hhLine, "cursor-line");
      d.setCN(b, a.hhLine, "cursor-line-horizontal");
      a.vLine = a.rotate ? a.vvLine : a.hhLine;
      a.set.push(a.vvLine);
      a.set.push(a.hhLine);
      a.set.node.style.pointerEvents = "none";
      a.fullLines = a.container.set();
      b = b.cursorLineSet;
      b.push(a.fullLines);
      b.translate(a.x, a.y);
      b.clipRect(-1, -1, g + 2, f + 2);
      void 0 !== a.tabIndex && (b.setAttr("tabindex", a.tabIndex), b.keyup(function (b) {
        a.handleKeys(b);
      }).focus(function (b) {
        a.showCursor();
      }).blur(function (b) {
        a.hideCursor();
      }));
      a.set.clipRect(0, 0, g, f);
    },
    handleKeys: function handleKeys(a) {
      var b = this.prevIndex,
          c = this.chart;

      if (c) {
        var e = c.chartData;
        e && (isNaN(b) && (b = e.length - 1), 37 != a.keyCode && 40 != a.keyCode || b--, 39 != a.keyCode && 38 != a.keyCode || b++, b = d.fitToBounds(b, c.startIndex, c.endIndex), (a = this.chart.chartData[b]) && this.setPosition(a.x.categoryAxis), this.prevIndex = b);
      }
    },
    update: function update() {
      var a = this.chart;

      if (a) {
        var b = a.mouseX - this.x,
            c = a.mouseY - this.y;
        this.mouseX = b;
        this.mouseY = c;
        this.mouse2X = a.mouse2X - this.x;
        this.mouse2Y = a.mouse2Y - this.y;
        var e;

        if (a.chartData && 0 < a.chartData.length) {
          this.mouseIsOver() ? (this.hideGraphBalloons = !1, this.rolledOver = e = !0, this.updateDrawing(), this.vvLine && isNaN(this.fx) && (a.rotate || !this.limitToGraph) && this.vvLine.translate(b, 0), !this.hhLine || !isNaN(this.fy) || a.rotate && this.limitToGraph || this.hhLine.translate(0, c), isNaN(this.mouse2X) ? this.dispatchMovedEvent(b, c) : e = !1) : this.forceShow || this.hideCursor();

          if (this.zooming) {
            if (!isNaN(this.mouse2X)) {
              isNaN(this.mouse2X0) || this.dispatchPanEvent();
              return;
            }

            if (this.pan) {
              this.dispatchPanEvent();
              return;
            }

            (this.hZoomEnabled || this.vZoomEnabled) && this.zooming && this.updateSelection();
          }

          e && this.showCursor();
        }
      }
    },
    updateDrawing: function updateDrawing() {
      this.drawing && this.chart.setMouseCursor("crosshair");

      if (this.drawingNow && (d.remove(this.drawingLine), 1 < Math.abs(this.drawStartX - this.mouseX) || 1 < Math.abs(this.drawStartY - this.mouseY))) {
        var a = this.chart,
            b = a.marginTop,
            a = a.marginLeft;
        this.drawingLine = d.line(this.container, [this.drawStartX + a, this.mouseX + a], [this.drawStartY + b, this.mouseY + b], this.cursorColor, 1, 1);
      }
    },
    fixWidth: function fixWidth(a) {
      if (this.fullWidth && this.prevLineWidth != a) {
        var b = this.vvLine,
            c = 0;
        b && (b.remove(), c = b.x);
        b = this.container.set();
        b.translate(c, 0);
        c = d.rect(this.container, a, this.height, this.cursorColor, this.cursorAlpha, this.cursorAlpha, this.cursorColor);
        d.setCN(this.chart, c, "cursor-fill");
        c.translate(-a / 2 - 1, 0);
        b.push(c);
        this.vvLine = b;
        this.fullLines.push(b);
        this.prevLineWidth = a;
      }
    },
    fixHeight: function fixHeight(a) {
      if (this.fullWidth && this.prevLineHeight != a) {
        var b = this.hhLine,
            c = 0;
        b && (b.remove(), c = b.y);
        b = this.container.set();
        b.translate(0, c);
        c = d.rect(this.container, this.width, a, this.cursorColor, this.cursorAlpha);
        c.translate(0, -a / 2);
        b.push(c);
        this.fullLines.push(b);
        this.hhLine = b;
        this.prevLineHeight = a;
      }
    },
    fixVLine: function fixVLine(a, b) {
      if (!isNaN(a) && this.vvLine) {
        if (isNaN(this.prevLineX)) {
          var c = 0,
              e = this.mouseX;

          if (this.limitToGraph) {
            var d = this.chart.categoryAxis;
            d && (this.chart.rotate || (c = "bottom" == d.position ? this.height : -this.height), e = a);
          }

          this.vvLine.translate(e, c);
        } else this.prevLineX != a && this.vvLine.translate(this.prevLineX, this.prevLineY);

        this.fx = a;
        this.prevLineX != a && (c = this.animationDuration, this.zooming && (c = 0), this.vvLine.stop(), this.vvLine.animate({
          translate: a + "," + b
        }, c, "easeOutSine"), this.prevLineX = a, this.prevLineY = b);
      }
    },
    fixHLine: function fixHLine(a, b) {
      if (!isNaN(a) && this.hhLine) {
        if (isNaN(this.prevLineY)) {
          var c = 0,
              e = this.mouseY;

          if (this.limitToGraph) {
            var d = this.chart.categoryAxis;
            d && (this.chart.rotate && (c = "right" == d.position ? this.width : -this.width), e = a);
          }

          this.hhLine.translate(c, e);
        } else this.prevLineY != a && this.hhLine.translate(this.prevLineX, this.prevLineY);

        this.fy = a;
        this.prevLineY != a && (c = this.animationDuration, this.zooming && (c = 0), this.hhLine.stop(), this.hhLine.animate({
          translate: b + "," + a
        }, c, "easeOutSine"), this.prevLineY = a, this.prevLineX = b);
      }
    },
    hideCursor: function hideCursor(a) {
      this.forceShow = !1;
      this.chart.wasTouched && this.leaveAfterTouch || this.isHidden || this.leaveCursor || (this.hideCursorReal(), a ? this.chart.handleCursorHide() : this.fire({
        target: this,
        chart: this.chart,
        type: "onHideCursor"
      }), this.chart.setMouseCursor("auto"));
    },
    hideCursorReal: function hideCursorReal() {
      this.hideLines();
      this.isHidden = !0;
      this.index = this.prevLineY = this.prevLineX = this.mouseY0 = this.mouseX0 = this.fy = this.fx = NaN;
    },
    hideLines: function hideLines() {
      this.vvLine && this.vvLine.hide();
      this.hhLine && this.hhLine.hide();
      this.fullLines && this.fullLines.hide();
      this.isHidden = !0;
      this.chart.handleCursorHide();
    },
    showCursor: function showCursor(a) {
      !this.drawing && this.enabled && (this.vLineEnabled && this.vvLine && this.vvLine.show(), this.hLineEnabled && this.hhLine && this.hhLine.show(), this.isHidden = !1, this.updateFullLine(), a || this.fire({
        target: this,
        chart: this.chart,
        type: "onShowCursor"
      }), this.pan && this.chart.setMouseCursor("move"));
    },
    updateFullLine: function updateFullLine() {
      this.zooming && this.fullWidth && this.selection && (this.rotate ? 0 < this.selection.height && this.hhLine.hide() : 0 < this.selection.width && this.vvLine.hide());
    },
    updateSelection: function updateSelection() {
      if (!this.pan && this.enabled) {
        var a = this.mouseX,
            b = this.mouseY;
        isNaN(this.fx) || (a = this.fx);
        isNaN(this.fy) || (b = this.fy);
        this.clearSelection();
        var c = this.mouseX0,
            e = this.mouseY0,
            g = this.width,
            f = this.height,
            a = d.fitToBounds(a, 0, g),
            b = d.fitToBounds(b, 0, f),
            h;
        a < c && (h = a, a = c, c = h);
        b < e && (h = b, b = e, e = h);
        this.hZoomEnabled ? g = a - c : c = 0;
        this.vZoomEnabled ? f = b - e : e = 0;
        isNaN(this.mouse2X) && 0 < Math.abs(g) && 0 < Math.abs(f) && (a = this.chart, b = d.rect(this.container, g, f, this.cursorColor, this.selectionAlpha), d.setCN(a, b, "cursor-selection"), b.width = g, b.height = f, b.translate(c, e), this.set.push(b), this.selection = b);
        this.updateFullLine();
      }
    },
    mouseIsOver: function mouseIsOver() {
      var a = this.mouseX,
          b = this.mouseY;
      if (this.justReleased) return this.justReleased = !1, !0;
      if (this.mouseIsDown) return !0;
      if (!this.chart.mouseIsOver) return this.handleMouseOut(), !1;
      if (0 < a && a < this.width && 0 < b && b < this.height) return !0;
      this.handleMouseOut();
      return !1;
    },
    fixPosition: function fixPosition() {
      this.prevY = this.prevX = NaN;
    },
    handleMouseDown: function handleMouseDown() {
      this.update();
      if (this.mouseIsOver()) if (this.mouseIsDown = !0, this.mouseX0 = this.mouseX, this.mouseY0 = this.mouseY, this.mouse2X0 = this.mouse2X, this.mouse2Y0 = this.mouse2Y, this.drawing) this.drawStartY = this.mouseY, this.drawStartX = this.mouseX, this.drawingNow = !0;else if (this.dispatchMovedEvent(this.mouseX, this.mouseY), !this.pan && isNaN(this.mouse2X0) && (isNaN(this.fx) || (this.mouseX0 = this.fx), isNaN(this.fy) || (this.mouseY0 = this.fy)), this.hZoomEnabled || this.vZoomEnabled) {
        this.zooming = !0;
        var a = {
          chart: this.chart,
          target: this,
          type: "zoomStarted"
        };
        a.x = this.mouseX / this.width;
        a.y = this.mouseY / this.height;
        this.index0 = a.index = this.index;
        this.timestamp0 = this.timestamp;
        this.fire(a);
      }
    },
    registerInitialMouse: function registerInitialMouse() {},
    handleReleaseOutside: function handleReleaseOutside() {
      this.mouseIsDown = !1;

      if (this.drawingNow) {
        this.drawingNow = !1;
        d.remove(this.drawingLine);
        var a = this.drawStartX,
            b = this.drawStartY,
            c = this.mouseX,
            e = this.mouseY,
            g = this.chart;
        (2 < Math.abs(a - c) || 2 < Math.abs(b - e)) && this.fire({
          type: "draw",
          target: this,
          chart: g,
          initialX: a,
          initialY: b,
          finalX: c,
          finalY: e
        });
      }

      this.zooming && (this.zooming = !1, this.selectWithoutZooming ? this.dispatchZoomEvent("selected") : (this.hZoomEnabled || this.vZoomEnabled) && this.dispatchZoomEvent("zoomed"), this.rolledOver && this.dispatchMovedEvent(this.mouseX, this.mouseY));
      this.mouse2Y0 = this.mouse2X0 = this.mouseY0 = this.mouseX0 = NaN;
    },
    dispatchZoomEvent: function dispatchZoomEvent(a) {
      if (!this.pan) {
        var b = this.selection;

        if (b && 3 < Math.abs(b.width) && 3 < Math.abs(b.height)) {
          var c = Math.min(this.index, this.index0),
              e = Math.max(this.index, this.index0),
              d = c,
              f = e,
              h = this.chart,
              k = h.chartData,
              l = h.categoryAxis;
          l && l.parseDates && !l.equalSpacing && (d = k[c] ? k[c].time : Math.min(this.timestamp0, this.timestamp), f = k[e] ? h.getEndTime(k[e].time) : Math.max(this.timestamp0, this.timestamp));
          var b = {
            type: a,
            chart: this.chart,
            target: this,
            end: f,
            start: d,
            startIndex: c,
            endIndex: e,
            selectionHeight: b.height,
            selectionWidth: b.width,
            selectionY: b.y,
            selectionX: b.x
          },
              m;
          this.hZoomEnabled && 4 < Math.abs(this.mouseX0 - this.mouseX) && (b.startX = this.mouseX0 / this.width, b.endX = this.mouseX / this.width, m = !0);
          this.vZoomEnabled && 4 < Math.abs(this.mouseY0 - this.mouseY) && (b.startY = 1 - this.mouseY0 / this.height, b.endY = 1 - this.mouseY / this.height, m = !0);
          m && (this.prevY = this.prevX = NaN, this.fire(b), "selected" != a && this.clearSelection());
          this.hideCursor();
        }
      }
    },
    dispatchMovedEvent: function dispatchMovedEvent(a, b, c, e) {
      a = Math.round(a);
      b = Math.round(b);

      if (!this.isHidden && (a != this.prevX || b != this.prevY || "changed" == c)) {
        c || (c = "moved");
        var d = this.fx,
            f = this.fy;
        isNaN(d) && (d = a);
        isNaN(f) && (f = b);
        var h = !1;
        this.zooming && this.pan && (h = !0);
        h = {
          hidden: this.isHidden,
          type: c,
          chart: this.chart,
          target: this,
          x: a,
          y: b,
          finalX: d,
          finalY: f,
          zooming: this.zooming,
          panning: h,
          mostCloseGraph: this.mostCloseGraph,
          index: this.index,
          skip: e,
          hideBalloons: this.hideGraphBalloons
        };
        this.prevIndex = this.index;
        this.rotate ? (h.position = b, h.finalPosition = f) : (h.position = a, h.finalPosition = d);
        this.prevX = a;
        this.prevY = b;
        e ? this.chart.handleCursorMove(h) : (this.fire(h), "changed" == c && this.chart.fire(h));
      }
    },
    dispatchPanEvent: function dispatchPanEvent() {
      if (this.mouseIsDown) {
        var a = d.roundTo((this.mouseX - this.mouseX0) / this.width, 3),
            b = d.roundTo((this.mouseY - this.mouseY0) / this.height, 3),
            c = d.roundTo((this.mouse2X - this.mouse2X0) / this.width, 3),
            e = d.roundTo((this.mouse2Y - this.mouse2Y0) / this.height, 2),
            g = !1;
        0 !== Math.abs(a) && 0 !== Math.abs(b) && (g = !0);
        if (this.prevDeltaX == a || this.prevDeltaY == b) g = !1;
        isNaN(c) || isNaN(e) || (0 !== Math.abs(c) && 0 !== Math.abs(e) && (g = !0), this.prevDelta2X != c && this.prevDelta2Y != e) || (g = !1);
        g && (this.hideLines(), this.fire({
          type: "panning",
          chart: this.chart,
          target: this,
          deltaX: a,
          deltaY: b,
          delta2X: c,
          delta2Y: e,
          index: this.index
        }), this.prevDeltaX = a, this.prevDeltaY = b, this.prevDelta2X = c, this.prevDelta2Y = e);
      }
    },
    clearSelection: function clearSelection() {
      var a = this.selection;
      a && (a.width = 0, a.height = 0, a.remove());
    },
    destroy: function destroy() {
      this.clear();
      d.remove(this.selection);
      this.selection = null;
      clearTimeout(this.syncTO);
      d.remove(this.set);
    },
    clear: function clear() {},
    setTimestamp: function setTimestamp(a) {
      this.timestamp = a;
    },
    setIndex: function setIndex(a, b) {
      a != this.index && (this.index = a, b || this.isHidden || this.dispatchMovedEvent(this.mouseX, this.mouseY, "changed"));
    },
    handleMouseOut: function handleMouseOut() {
      this.enabled && this.rolledOver && (this.leaveCursor || this.setIndex(void 0), this.forceShow = !1, this.hideCursor(), this.rolledOver = !1);
    },
    showCursorAt: function showCursorAt(a) {
      var b = this.chart.categoryAxis;
      b && this.setPosition(b.categoryToCoordinate(a), a);
    },
    setPosition: function setPosition(a, b) {
      var c = this.chart,
          e = c.categoryAxis;

      if (e) {
        var d, f;
        void 0 === b && (b = e.coordinateToValue(a));
        e.showBalloonAt(b, a);
        this.forceShow = !0;
        e.stickBalloonToCategory ? c.rotate ? this.fixHLine(a, 0) : this.fixVLine(a, 0) : (this.showCursor(), c.rotate ? this.hhLine.translate(0, a) : this.vvLine.translate(a, 0));
        c.rotate ? d = a : f = a;
        c.rotate ? (this.vvLine && this.vvLine.hide(), this.hhLine && this.hhLine.show()) : (this.hhLine && this.hhLine.hide(), this.vvLine && this.vvLine.show());
        this.updateFullLine();
        this.isHidden = !1;
        this.dispatchMovedEvent(f, d, "moved", !0);
      }
    },
    enableDrawing: function enableDrawing(a) {
      this.enabled = !a;
      this.hideCursor();
      this.drawing = a;
    },
    syncWithCursor: function syncWithCursor(a, b) {
      clearTimeout(this.syncTO);
      a && (a.isHidden ? this.hideCursor(!0) : this.syncWithCursorReal(a, b));
    },
    isZooming: function isZooming(a) {
      this.zooming = a;
    },
    syncWithCursorReal: function syncWithCursorReal(a, b) {
      var c = a.vvLine,
          e = a.hhLine;
      this.index = a.index;
      this.forceShow = !0;
      this.zooming && this.pan || this.showCursor(!0);
      this.hideGraphBalloons = b;
      this.justReleased = a.justReleased;
      this.zooming = a.zooming;
      this.index0 = a.index0;
      this.mouseX0 = a.mouseX0;
      this.mouseY0 = a.mouseY0;
      this.mouse2X0 = a.mouse2X0;
      this.mouse2Y0 = a.mouse2Y0;
      this.timestamp0 = a.timestamp0;
      this.prevDeltaX = a.prevDeltaX;
      this.prevDeltaY = a.prevDeltaY;
      this.prevDelta2X = a.prevDelta2X;
      this.prevDelta2Y = a.prevDelta2Y;
      this.fx = a.fx;
      this.fy = a.fy;
      a.zooming && this.updateSelection();
      var d = a.mouseX,
          f = a.mouseY;
      this.rotate ? (d = NaN, this.vvLine && this.vvLine.hide(), this.hhLine && e && (isNaN(a.fy) ? this.hhLine.translate(0, a.mouseY) : this.fixHLine(a.fy, 0))) : (f = NaN, this.hhLine && this.hhLine.hide(), this.vvLine && c && (isNaN(a.fx) ? this.vvLine.translate(a.mouseX, 0) : this.fixVLine(a.fx, 0)));
      this.dispatchMovedEvent(d, f, "moved", !0);
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.SimpleChartScrollbar = d.Class({
    construct: function construct(a) {
      this.createEvents("zoomed", "zoomStarted", "zoomEnded");
      this.backgroundColor = "#D4D4D4";
      this.backgroundAlpha = 1;
      this.selectedBackgroundColor = "#EFEFEF";
      this.scrollDuration = this.selectedBackgroundAlpha = 1;
      this.resizeEnabled = !0;
      this.hideResizeGrips = !1;
      this.scrollbarHeight = 20;
      this.updateOnReleaseOnly = !1;
      9 > document.documentMode && (this.updateOnReleaseOnly = !0);
      this.dragIconHeight = this.dragIconWidth = 35;
      this.dragIcon = "dragIconRoundBig";
      this.dragCursorHover = "cursor: move; cursor: grab; cursor: -moz-grab; cursor: -webkit-grab;";
      this.dragCursorDown = "cursor: move; cursor: grab; cursor: -moz-grabbing; cursor: -webkit-grabbing;";
      this.vResizeCursor = "ns-resize";
      this.hResizeCursor = "ew-resize";
      this.enabled = !0;
      this.percentStart = this.offset = 0;
      this.percentEnd = 1;
      d.applyTheme(this, a, "SimpleChartScrollbar");
    },
    getPercents: function getPercents() {
      var a = this.getDBox(),
          b = a.x,
          c = a.y,
          e = a.width,
          a = a.height;
      this.rotate ? (b = 1 - c / this.height, c = 1 - (c + a) / this.height) : (c = b / this.width, b = (b + e) / this.width);
      this.percentStart = c;
      this.percentEnd = b;
    },
    draw: function draw() {
      var a = this;
      a.destroy();

      if (a.enabled) {
        var b = a.chart.container,
            c = a.rotate,
            e = a.chart;
        e.panRequired = !0;
        var g = b.set();
        a.set = g;
        c ? d.setCN(e, g, "scrollbar-vertical") : d.setCN(e, g, "scrollbar-horizontal");
        e.scrollbarsSet.push(g);
        var f, h;
        c ? (f = a.scrollbarHeight, h = e.plotAreaHeight) : (h = a.scrollbarHeight, f = e.plotAreaWidth);
        a.width = f;

        if ((a.height = h) && f) {
          var k = d.rect(b, f, h, a.backgroundColor, a.backgroundAlpha, 1, a.backgroundColor, a.backgroundAlpha);
          d.setCN(e, k, "scrollbar-bg");
          a.bg = k;
          g.push(k);
          k = d.rect(b, f, h, "#000", .005);
          g.push(k);
          a.invisibleBg = k;
          k.click(function () {
            a.handleBgClick();
          }).mouseover(function () {
            a.handleMouseOver();
          }).mouseout(function () {
            a.handleMouseOut();
          }).touchend(function () {
            a.handleBgClick();
          });
          k = d.rect(b, f, h, a.selectedBackgroundColor, a.selectedBackgroundAlpha);
          d.setCN(e, k, "scrollbar-bg-selected");
          a.selectedBG = k;
          g.push(k);
          f = d.rect(b, f, h, "#000", .005);
          a.dragger = f;
          g.push(f);
          f.mousedown(function (b) {
            a.handleDragStart(b);
          }).mouseup(function () {
            a.handleDragStop();
          }).mouseover(function () {
            a.handleDraggerOver();
          }).mouseout(function () {
            a.handleMouseOut();
          }).touchstart(function (b) {
            a.handleDragStart(b);
          }).touchend(function () {
            a.handleDragStop();
          });
          h = e.pathToImages;
          var l,
              k = a.dragIcon.replace(/\.[a-z]*$/i, "");
          d.isAbsolute(k) && (h = "");
          c ? (l = h + k + "H" + e.extension, h = a.dragIconWidth, c = a.dragIconHeight) : (l = h + k + e.extension, c = a.dragIconWidth, h = a.dragIconHeight);
          k = b.image(l, 0, 0, c, h);
          d.setCN(e, k, "scrollbar-grip-left");
          l = b.image(l, 0, 0, c, h);
          d.setCN(e, l, "scrollbar-grip-right");
          var m = 10,
              n = 20;
          e.panEventsEnabled && (m = 25, n = a.scrollbarHeight);
          var q = d.rect(b, m, n, "#000", .005),
              p = d.rect(b, m, n, "#000", .005);
          p.translate(-(m - c) / 2, -(n - h) / 2);
          q.translate(-(m - c) / 2, -(n - h) / 2);
          c = b.set([k, p]);
          b = b.set([l, q]);
          a.iconLeft = c;
          g.push(a.iconLeft);
          a.iconRight = b;
          g.push(b);
          a.updateGripCursor(!1);
          e.makeAccessible(c, a.accessibleLabel);
          e.makeAccessible(b, a.accessibleLabel);
          e.makeAccessible(f, a.accessibleLabel);
          c.setAttr("role", "menuitem");
          b.setAttr("role", "menuitem");
          f.setAttr("role", "menuitem");
          void 0 !== a.tabIndex && (c.setAttr("tabindex", a.tabIndex), c.keyup(function (b) {
            a.handleKeys(b, 1, 0);
          }));
          void 0 !== a.tabIndex && (f.setAttr("tabindex", a.tabIndex), f.keyup(function (b) {
            a.handleKeys(b, 1, 1);
          }));
          void 0 !== a.tabIndex && (b.setAttr("tabindex", a.tabIndex), b.keyup(function (b) {
            a.handleKeys(b, 0, 1);
          }));
          c.mousedown(function () {
            a.leftDragStart();
          }).mouseup(function () {
            a.leftDragStop();
          }).mouseover(function () {
            a.iconRollOver();
          }).mouseout(function () {
            a.iconRollOut();
          }).touchstart(function () {
            a.leftDragStart();
          }).touchend(function () {
            a.leftDragStop();
          });
          b.mousedown(function () {
            a.rightDragStart();
          }).mouseup(function () {
            a.rightDragStop();
          }).mouseover(function () {
            a.iconRollOver();
          }).mouseout(function () {
            a.iconRollOut();
          }).touchstart(function () {
            a.rightDragStart();
          }).touchend(function () {
            a.rightDragStop();
          });
          d.ifArray(e.chartData) ? g.show() : g.hide();
          a.hideDragIcons();
          a.clipDragger(!1);
        }

        g.translate(a.x, a.y);
        g.node.style.msTouchAction = "none";
        g.node.style.touchAction = "none";
      }
    },
    handleKeys: function handleKeys(a, b, c) {
      this.getPercents();
      var e = this.percentStart,
          d = this.percentEnd;
      if (this.rotate) var f = d,
          d = e,
          e = f;
      if (37 == a.keyCode || 40 == a.keyCode) e -= .02 * b, d -= .02 * c;
      if (39 == a.keyCode || 38 == a.keyCode) e += .02 * b, d += .02 * c;
      this.rotate && (a = d, d = e, e = a);
      isNaN(d) || isNaN(e) || this.percentZoom(e, d, !0);
    },
    updateScrollbarSize: function updateScrollbarSize(a, b) {
      if (!isNaN(a) && !isNaN(b)) {
        a = Math.round(a);
        b = Math.round(b);
        var c = this.dragger,
            e,
            d,
            f,
            h,
            k;
        this.rotate ? (e = 0, d = a, f = this.width + 1, h = b - a, c.setAttr("height", b - a), c.setAttr("y", d)) : (e = a, d = 0, f = b - a, h = this.height + 1, k = b - a, c.setAttr("x", e), c.setAttr("width", k));
        this.clipAndUpdate(e, d, f, h);
      }
    },
    update: function update() {
      var a,
          b = !1,
          c,
          e,
          d = this.x,
          f = this.y,
          h = this.dragger,
          k = this.getDBox();

      if (k) {
        c = k.x + d;
        e = k.y + f;
        var l = k.width,
            k = k.height,
            m = this.rotate,
            n = this.chart,
            q = this.width,
            p = this.height,
            t = n.mouseX,
            n = n.mouseY;
        a = this.initialMouse;
        this.forceClip && this.clipDragger(!0);

        if (this.dragging) {
          var r = this.initialCoord;
          if (m) a = r + (n - a), 0 > a && (a = 0), r = p - k, a > r && (a = r), h.setAttr("y", a);else {
            a = r + (t - a);
            0 > a && (a = 0);
            r = q - l;
            if (a > r || isNaN(a)) a = r;
            h.setAttr("x", a);
          }
          this.clipDragger(!0);
        }

        if (this.resizingRight) {
          if (m) {
            if (a = n - e, !isNaN(this.maxHeight) && a > this.maxHeight && (a = this.maxHeight), a + e > p + f && (a = p - e + f), 0 > a) this.resizingRight = !1, b = this.resizingLeft = !0;else {
              if (0 === a || isNaN(a)) a = .1;
              h.setAttr("height", a);
            }
          } else if (a = t - c, !isNaN(this.maxWidth) && a > this.maxWidth && (a = this.maxWidth), a + c > q + d && (a = q - c + d), 0 > a) this.resizingRight = !1, b = this.resizingLeft = !0;else {
            if (0 === a || isNaN(a)) a = .1;
            h.setAttr("width", a);
          }
          this.clipDragger(!0);
        }

        if (this.resizingLeft) {
          if (m) {
            if (c = e, e = n, e < f && (e = f), isNaN(e) && (e = f), e > p + f && (e = p + f), a = !0 === b ? c - e : k + c - e, !isNaN(this.maxHeight) && a > this.maxHeight && (a = this.maxHeight, e = c), 0 > a) this.resizingRight = !0, this.resizingLeft = !1, h.setAttr("y", c + k - f);else {
              if (0 === a || isNaN(a)) a = .1;
              h.setAttr("y", e - f);
              h.setAttr("height", a);
            }
          } else if (e = t, e < d && (e = d), isNaN(e) && (e = d), e > q + d && (e = q + d), a = !0 === b ? c - e : l + c - e, !isNaN(this.maxWidth) && a > this.maxWidth && (a = this.maxWidth, e = c), 0 > a) this.resizingRight = !0, this.resizingLeft = !1, h.setAttr("x", c + l - d);else {
            if (0 === a || isNaN(a)) a = .1;
            h.setAttr("x", e - d);
            h.setAttr("width", a);
          }
          this.clipDragger(!0);
        }
      }
    },
    stopForceClip: function stopForceClip() {
      this.animating = this.forceClip = !1;
    },
    clipDragger: function clipDragger(a) {
      var b = this.getDBox();

      if (b) {
        var c = b.x,
            e = b.y,
            d = b.width,
            b = b.height,
            f = !1;

        if (this.rotate) {
          if (c = 0, d = this.width + 1, this.clipY != e || this.clipH != b) f = !0;
        } else if (e = 0, b = this.height + 1, this.clipX != c || this.clipW != d) f = !0;

        f && this.clipAndUpdate(c, e, d, b);
        a && (this.updateOnReleaseOnly || this.dispatchScrollbarEvent());
      }
    },
    maskGraphs: function maskGraphs() {},
    clipAndUpdate: function clipAndUpdate(a, b, c, e) {
      this.clipX = a;
      this.clipY = b;
      this.clipW = c;
      this.clipH = e;
      this.selectedBG.setAttr("width", c);
      this.selectedBG.setAttr("height", e);
      this.selectedBG.translate(a, b);
      this.updateDragIconPositions();
      this.maskGraphs(a, b, c, e);
    },
    dispatchScrollbarEvent: function dispatchScrollbarEvent() {
      if (this.skipEvent) this.skipEvent = !1;else {
        var a = this.chart;
        a.hideBalloon();
        var b = this.getDBox(),
            c = b.x,
            e = b.y,
            d = b.width,
            b = b.height;
        this.getPercents();
        this.rotate ? (c = e, d = this.height / b) : d = this.width / d;
        a = {
          type: "zoomed",
          position: c,
          chart: a,
          target: this,
          multiplier: d,
          relativeStart: this.percentStart,
          relativeEnd: this.percentEnd
        };
        if (this.percentStart != this.prevPercentStart || this.percentEnd != this.prevPercentEnd || this.prevMultiplier != d) this.fire(a), this.prevPercentStart = this.percentStart, this.prevPercentEnd = this.percentEnd, this.prevMultiplier = d;
      }
    },
    updateDragIconPositions: function updateDragIconPositions() {
      var a = this.getDBox(),
          b = a.x,
          c = a.y,
          d = this.iconLeft,
          g = this.iconRight,
          f,
          h,
          k = this.scrollbarHeight;
      this.rotate ? (f = this.dragIconWidth, h = this.dragIconHeight, d.translate((k - h) / 2, c - f / 2), g.translate((k - h) / 2, c + a.height - f / 2)) : (f = this.dragIconHeight, h = this.dragIconWidth, d.translate(b - h / 2, (k - f) / 2), g.translate(b - h / 2 + a.width, (k - f) / 2));
    },
    showDragIcons: function showDragIcons() {
      this.resizeEnabled && (this.iconLeft.show(), this.iconRight.show());
    },
    hideDragIcons: function hideDragIcons() {
      if (!this.resizingLeft && !this.resizingRight && !this.dragging) {
        if (this.hideResizeGrips || !this.resizeEnabled) this.iconLeft.hide(), this.iconRight.hide();
        this.removeCursors();
      }
    },
    removeCursors: function removeCursors() {
      this.chart.setMouseCursor("auto");
    },
    fireZoomEvent: function fireZoomEvent(a) {
      this.fire({
        type: a,
        chart: this.chart,
        target: this
      });
    },
    percentZoom: function percentZoom(a, b, c) {
      a = d.fitToBounds(a, 0, b);
      b = d.fitToBounds(b, a, 1);

      if (this.dragger && this.enabled) {
        this.dragger.stop();
        isNaN(a) && (a = 0);
        isNaN(b) && (b = 1);
        var e, g;
        this.rotate ? (e = this.height, b = e - e * b, g = e - e * a) : (e = this.width, g = e * b, b = e * a);
        this.updateScrollbarSize(b, g);
        this.clipDragger(!1);
        this.getPercents();
        c && this.dispatchScrollbarEvent();
      }
    },
    destroy: function destroy() {
      this.clear();
      d.remove(this.set);
      d.remove(this.iconRight);
      d.remove(this.iconLeft);
    },
    clear: function clear() {},
    handleDragStart: function handleDragStart() {
      if (this.enabled) {
        this.fireZoomEvent("zoomStarted");
        var a = this.chart;
        this.dragger.stop();
        this.removeCursors();
        d.isModern && (this.dragger.node.style.cssText = this.dragCursorDown);
        this.dragging = !0;
        var b = this.getDBox();
        this.rotate ? (this.initialCoord = b.y, this.initialMouse = a.mouseY) : (this.initialCoord = b.x, this.initialMouse = a.mouseX);
      }
    },
    handleDragStop: function handleDragStop() {
      this.updateOnReleaseOnly && (this.update(), this.skipEvent = !1, this.dispatchScrollbarEvent());
      this.dragging = !1;
      this.mouseIsOver && this.removeCursors();
      d.isModern && (this.dragger.node.style.cssText = this.dragCursorHover);
      this.update();
      this.fireZoomEvent("zoomEnded");
    },
    handleDraggerOver: function handleDraggerOver() {
      this.handleMouseOver();
      d.isModern && (this.dragger.node.style.cssText = this.dragCursorHover);
    },
    leftDragStart: function leftDragStart() {
      this.fireZoomEvent("zoomStarted");
      this.dragger.stop();
      this.resizingLeft = !0;
      this.updateGripCursor(!0);
    },
    updateGripCursor: function updateGripCursor(a) {
      d.isModern && (a = this.rotate ? a ? this.vResizeCursorDown : this.vResizeCursorHover : a ? this.hResizeCursorDown : this.hResizeCursorHover) && (this.iconRight && (this.iconRight.node.style.cssText = a), this.iconLeft && (this.iconLeft.node.style.cssText = a));
    },
    leftDragStop: function leftDragStop() {
      this.resizingLeft && (this.resizingLeft = !1, this.mouseIsOver || this.removeCursors(), this.updateOnRelease(), this.fireZoomEvent("zoomEnded"));
      this.updateGripCursor(!1);
    },
    rightDragStart: function rightDragStart() {
      this.fireZoomEvent("zoomStarted");
      this.dragger.stop();
      this.resizingRight = !0;
      this.updateGripCursor(!0);
    },
    rightDragStop: function rightDragStop() {
      this.resizingRight && (this.resizingRight = !1, this.mouseIsOver || this.removeCursors(), this.updateOnRelease(), this.fireZoomEvent("zoomEnded"));
      this.updateGripCursor(!1);
    },
    iconRollOut: function iconRollOut() {
      this.removeCursors();
    },
    iconRollOver: function iconRollOver() {
      this.rotate ? this.vResizeCursor && this.chart.setMouseCursor(this.vResizeCursor) : this.hResizeCursor && this.chart.setMouseCursor(this.hResizeCursor);
      this.handleMouseOver();
    },
    getDBox: function getDBox() {
      if (this.dragger) return this.dragger.getBBox();
    },
    handleBgClick: function handleBgClick() {
      var a = this;

      if (!a.resizingRight && !a.resizingLeft) {
        a.zooming = !0;
        var b,
            c,
            e = a.scrollDuration,
            g = a.dragger;
        b = a.getDBox();
        var f = b.height,
            h = b.width;
        c = a.chart;
        var k = a.y,
            l = a.x,
            m = a.rotate;
        m ? (b = "y", c = c.mouseY - f / 2 - k, c = d.fitToBounds(c, 0, a.height - f)) : (b = "x", c = c.mouseX - h / 2 - l, c = d.fitToBounds(c, 0, a.width - h));
        a.updateOnReleaseOnly ? (a.skipEvent = !1, g.setAttr(b, c), a.dispatchScrollbarEvent(), a.clipDragger()) : (a.animating = !0, c = Math.round(c), m ? g.animate({
          y: c
        }, e, ">") : g.animate({
          x: c
        }, e, ">"), a.forceClip = !0, clearTimeout(a.forceTO), a.forceTO = setTimeout(function () {
          a.stopForceClip.call(a);
        }, 5E3 * e));
      }
    },
    updateOnRelease: function updateOnRelease() {
      this.updateOnReleaseOnly && (this.update(), this.skipEvent = !1, this.dispatchScrollbarEvent());
    },
    handleReleaseOutside: function handleReleaseOutside() {
      if (this.set) {
        if (this.resizingLeft || this.resizingRight || this.dragging) this.dragging = this.resizingRight = this.resizingLeft = !1, this.updateOnRelease(), this.removeCursors();
        this.animating = this.mouseIsOver = !1;
        this.hideDragIcons();
        this.update();
      }
    },
    handleMouseOver: function handleMouseOver() {
      this.mouseIsOver = !0;
      this.showDragIcons();
    },
    handleMouseOut: function handleMouseOut() {
      this.mouseIsOver = !1;
      this.hideDragIcons();
      this.removeCursors();
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.ChartScrollbar = d.Class({
    inherits: d.SimpleChartScrollbar,
    construct: function construct(a) {
      this.cname = "ChartScrollbar";
      d.ChartScrollbar.base.construct.call(this, a);
      this.graphLineColor = "#BBBBBB";
      this.graphLineAlpha = 0;
      this.graphFillColor = "#BBBBBB";
      this.graphFillAlpha = 1;
      this.selectedGraphLineColor = "#888888";
      this.selectedGraphLineAlpha = 0;
      this.selectedGraphFillColor = "#888888";
      this.selectedGraphFillAlpha = 1;
      this.gridCount = 0;
      this.gridColor = "#FFFFFF";
      this.gridAlpha = .7;
      this.skipEvent = this.autoGridCount = !1;
      this.color = "#FFFFFF";
      this.scrollbarCreated = !1;
      this.oppositeAxis = !0;
      this.accessibleLabel = "Zoom chart using cursor arrows";
      d.applyTheme(this, a, this.cname);
    },
    init: function init() {
      var a = this.categoryAxis,
          b = this.chart,
          c = this.gridAxis;
      a || ("CategoryAxis" == this.gridAxis.cname ? (this.catScrollbar = !0, a = new d.CategoryAxis(), a.id = "scrollbar") : (a = new d.ValueAxis(), a.data = b.chartData, a.id = c.id, a.type = c.type, a.maximumDate = c.maximumDate, a.minimumDate = c.minimumDate, a.minPeriod = c.minPeriod, a.minMaxField = c.minMaxField), this.categoryAxis = a);
      a.chart = b;
      var e = b.categoryAxis;
      e && (a.firstDayOfWeek = e.firstDayOfWeek);
      a.dateFormats = c.dateFormats;
      a.markPeriodChange = c.markPeriodChange;
      a.boldPeriodBeginning = c.boldPeriodBeginning;
      a.labelFunction = c.labelFunction;
      a.axisItemRenderer = d.RecItem;
      a.axisRenderer = d.RecAxis;
      a.guideFillRenderer = d.RecFill;
      a.inside = !0;
      a.fontSize = this.fontSize;
      a.tickLength = 0;
      a.axisAlpha = 0;
      d.isString(this.graph) && (this.graph = d.getObjById(b.graphs, this.graph));
      (a = this.graph) && this.catScrollbar && (c = this.valueAxis, c || (this.valueAxis = c = new d.ValueAxis(), c.visible = !1, c.scrollbar = !0, c.axisItemRenderer = d.RecItem, c.axisRenderer = d.RecAxis, c.guideFillRenderer = d.RecFill, c.labelsEnabled = !1, c.chart = b), b = this.unselectedGraph, b || (b = new d.AmGraph(), b.scrollbar = !0, this.unselectedGraph = b, b.negativeBase = a.negativeBase, b.noStepRisers = a.noStepRisers), b = this.selectedGraph, b || (b = new d.AmGraph(), b.scrollbar = !0, this.selectedGraph = b, b.negativeBase = a.negativeBase, b.noStepRisers = a.noStepRisers));
      this.scrollbarCreated = !0;
    },
    draw: function draw() {
      var a = this;
      d.ChartScrollbar.base.draw.call(a);

      if (a.enabled) {
        a.scrollbarCreated || a.init();
        var b = a.chart,
            c = b.chartData,
            e = a.categoryAxis,
            g = a.rotate,
            f = a.x,
            h = a.y,
            k = a.width,
            l = a.height,
            m = a.gridAxis,
            n = a.set;
        e.setOrientation(!g);
        e.parseDates = m.parseDates;
        "ValueAxis" == a.categoryAxis.cname && (e.rotate = !g);
        e.equalSpacing = m.equalSpacing;
        e.minPeriod = m.minPeriod;
        e.startOnAxis = m.startOnAxis;
        e.width = k - 1;
        e.height = l;
        e.gridCount = a.gridCount;
        e.gridColor = a.gridColor;
        e.gridAlpha = a.gridAlpha;
        e.color = a.color;
        e.tickLength = 0;
        e.axisAlpha = 0;
        e.autoGridCount = a.autoGridCount;
        e.parseDates && !e.equalSpacing && e.timeZoom(b.firstTime, b.lastTime);
        e.minimum = a.gridAxis.fullMin;
        e.maximum = a.gridAxis.fullMax;
        e.strictMinMax = !0;
        e.zoom(0, c.length - 1);

        if ((m = a.graph) && a.catScrollbar) {
          var q = a.valueAxis,
              p = m.valueAxis;
          q.id = p.id;
          q.rotate = g;
          q.setOrientation(g);
          q.width = k;
          q.height = l;
          q.dataProvider = c;
          q.reversed = p.reversed;
          q.logarithmic = p.logarithmic;
          q.gridAlpha = 0;
          q.axisAlpha = 0;
          n.push(q.set);
          g ? (q.y = h, q.x = 0) : (q.x = f, q.y = 0);
          var f = Infinity,
              h = -Infinity,
              t;

          for (t = 0; t < c.length; t++) {
            var r = c[t].axes[p.id].graphs[m.id].values,
                w;

            for (w in r) {
              if (r.hasOwnProperty(w) && "percents" != w && "total" != w) {
                var z = r[w];
                z < f && (f = z);
                z > h && (h = z);
              }
            }
          }

          Infinity != f && (q.minimum = f);
          -Infinity != h && (q.maximum = h + .1 * (h - f));
          f == h && (--q.minimum, q.maximum += 1);
          void 0 !== a.minimum && (q.minimum = a.minimum);
          void 0 !== a.maximum && (q.maximum = a.maximum);
          q.zoom(0, c.length - 1);
          w = a.unselectedGraph;
          w.id = m.id;
          w.bcn = "scrollbar-graph-";
          w.rotate = g;
          w.chart = b;
          w.data = c;
          w.valueAxis = q;
          w.chart = m.chart;
          w.categoryAxis = a.categoryAxis;
          w.periodSpan = m.periodSpan;
          w.valueField = m.valueField;
          w.openField = m.openField;
          w.closeField = m.closeField;
          w.highField = m.highField;
          w.lowField = m.lowField;
          w.lineAlpha = a.graphLineAlpha;
          w.lineColorR = a.graphLineColor;
          w.fillAlphas = a.graphFillAlpha;
          w.fillColorsR = a.graphFillColor;
          w.connect = m.connect;
          w.hidden = m.hidden;
          w.width = k;
          w.height = l;
          w.pointPosition = m.pointPosition;
          w.stepDirection = m.stepDirection;
          w.periodSpan = m.periodSpan;
          p = a.selectedGraph;
          p.id = m.id;
          p.bcn = w.bcn + "selected-";
          p.rotate = g;
          p.chart = b;
          p.data = c;
          p.valueAxis = q;
          p.chart = m.chart;
          p.categoryAxis = e;
          p.periodSpan = m.periodSpan;
          p.valueField = m.valueField;
          p.openField = m.openField;
          p.closeField = m.closeField;
          p.highField = m.highField;
          p.lowField = m.lowField;
          p.lineAlpha = a.selectedGraphLineAlpha;
          p.lineColorR = a.selectedGraphLineColor;
          p.fillAlphas = a.selectedGraphFillAlpha;
          p.fillColorsR = a.selectedGraphFillColor;
          p.connect = m.connect;
          p.hidden = m.hidden;
          p.width = k;
          p.height = l;
          p.pointPosition = m.pointPosition;
          p.stepDirection = m.stepDirection;
          p.periodSpan = m.periodSpan;
          b = a.graphType;
          b || (b = m.type);
          w.type = b;
          p.type = b;
          c = c.length - 1;
          w.zoom(0, c);
          p.zoom(0, c);
          p.set.click(function () {
            a.handleBackgroundClick();
          }).mouseover(function () {
            a.handleMouseOver();
          }).mouseout(function () {
            a.handleMouseOut();
          });
          w.set.click(function () {
            a.handleBackgroundClick();
          }).mouseover(function () {
            a.handleMouseOver();
          }).mouseout(function () {
            a.handleMouseOut();
          });
          n.push(w.set);
          n.push(p.set);
        }

        n.push(e.set);
        n.push(e.labelsSet);
        a.bg.toBack();
        a.invisibleBg.toFront();
        a.dragger.toFront();
        a.iconLeft.toFront();
        a.iconRight.toFront();
      }
    },
    timeZoom: function timeZoom(a, b, c) {
      this.startTime = a;
      this.endTime = b;
      this.timeDifference = b - a;
      this.skipEvent = !d.toBoolean(c);
      this.zoomScrollbar();
      this.dispatchScrollbarEvent();
    },
    zoom: function zoom(a, b) {
      this.start = a;
      this.end = b;
      this.skipEvent = !0;
      this.zoomScrollbar();
    },
    dispatchScrollbarEvent: function dispatchScrollbarEvent() {
      if (this.categoryAxis && "ValueAxis" == this.categoryAxis.cname) d.ChartScrollbar.base.dispatchScrollbarEvent.call(this);else if (this.skipEvent) this.skipEvent = !1;else {
        var a = this.chart.chartData,
            b,
            c,
            e = this.dragger.getBBox();
        b = e.x;
        var g = e.y,
            f = e.width,
            e = e.height,
            h = this.chart;
        this.rotate ? (b = g, c = e) : c = f;
        f = {
          type: "zoomed",
          target: this
        };
        f.chart = h;
        var k = this.categoryAxis,
            l = this.stepWidth,
            e = h.minSelectedTime,
            m = h.maxSelectedTime,
            g = !1;

        if (k.parseDates && !k.equalSpacing) {
          if (a = h.lastTime, h = h.firstTime, k = Math.round(b / l) + h, b = this.dragging ? k + this.timeDifference : Math.round((b + c) / l) + h, k > b && (k = b), 0 < e && b - k < e && (b = Math.round(k + (b - k) / 2), g = Math.round(e / 2), k = b - g, b += g, g = !0), 0 < m && b - k > m && (b = Math.round(k + (b - k) / 2), g = Math.round(m / 2), k = b - g, b += g, g = !0), b > a && (b = a), b - e < k && (k = b - e), k < h && (k = h), k + e > b && (b = k + e), k != this.startTime || b != this.endTime) this.startTime = k, this.endTime = b, f.start = k, f.end = b, f.startDate = new Date(k), f.endDate = new Date(b), this.fire(f);
        } else {
          m = l / 2;
          k.startOnAxis || (b += m, c -= m);
          e = k.xToIndex(b);
          k.startOnAxis || (b = k.getCoordinate(e) - m);
          b = k.xToIndex(b + c);
          if (e != this.start || this.end != b) k.startOnAxis && (this.resizingRight && e == b && b++, this.resizingLeft && e == b && (0 < e ? e-- : b = 1)), b == a.length && (b = a.length - 1), e == b && b == a.length - 1 && (e = b - 1), this.start = e, this.end = this.dragging ? this.start + this.difference : b, f.start = this.start, f.end = this.end, k.parseDates && (a[this.start] && (f.startDate = new Date(a[this.start].time)), a[this.end] && (f.endDate = new Date(a[this.end].time))), this.fire(f);
          this.percentStart = e;
          this.percentEnd = b;
        }

        g && this.zoomScrollbar(!0);
      }
    },
    zoomScrollbar: function zoomScrollbar(a) {
      if ((!(this.dragging || this.resizingLeft || this.resizingRight || this.animating) || a) && this.dragger && this.enabled) {
        var b,
            c,
            d = this.chart;
        a = d.chartData;
        var g = this.categoryAxis;
        g.parseDates && !g.equalSpacing ? (a = g.stepWidth, c = d.firstTime, b = a * (this.startTime - c), c = a * (this.endTime - c)) : (a[this.start] && (b = a[this.start].x[g.id]), a[this.end] && (c = a[this.end].x[g.id]), a = g.stepWidth, g.startOnAxis || (d = a / 2, b -= d, c += d));
        this.stepWidth = a;
        isNaN(b) || isNaN(c) || this.updateScrollbarSize(b, c);
      }
    },
    maskGraphs: function maskGraphs(a, b, c, d) {
      var g = this.selectedGraph;
      g && g.set.clipRect(a, b, c, d);
    },
    handleDragStart: function handleDragStart() {
      d.ChartScrollbar.base.handleDragStart.call(this);
      this.difference = this.end - this.start;
      this.timeDifference = this.endTime - this.startTime;
      0 > this.timeDifference && (this.timeDifference = 0);
    },
    handleBackgroundClick: function handleBackgroundClick() {
      d.ChartScrollbar.base.handleBackgroundClick.call(this);
      this.dragging || (this.difference = this.end - this.start, this.timeDifference = this.endTime - this.startTime, 0 > this.timeDifference && (this.timeDifference = 0));
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.AmBalloon = d.Class({
    construct: function construct(a) {
      this.cname = "AmBalloon";
      this.enabled = !0;
      this.fillColor = "#FFFFFF";
      this.fillAlpha = .8;
      this.borderThickness = 2;
      this.borderColor = "#FFFFFF";
      this.borderAlpha = 1;
      this.cornerRadius = 0;
      this.maxWidth = 220;
      this.horizontalPadding = 8;
      this.verticalPadding = 4;
      this.pointerWidth = 6;
      this.pointerOrientation = "V";
      this.color = "#000000";
      this.adjustBorderColor = !0;
      this.show = this.follow = this.showBullet = !1;
      this.bulletSize = 3;
      this.shadowAlpha = .4;
      this.shadowColor = "#000000";
      this.fadeOutDuration = this.animationDuration = .3;
      this.fixedPosition = !0;
      this.offsetY = 6;
      this.offsetX = 1;
      this.textAlign = "center";
      this.disableMouseEvents = !0;
      this.deltaSignX = this.deltaSignY = 1;
      d.isModern || (this.offsetY *= 1.5);
      this.sdy = this.sdx = 0;
      d.applyTheme(this, a, this.cname);
    },
    draw: function draw() {
      var a = this.pointToX,
          b = this.pointToY;
      d.isModern || (this.drop = !1);
      var c = this.chart;
      d.VML && (this.fadeOutDuration = 0);
      this.xAnim && c.stopAnim(this.xAnim);
      this.yAnim && c.stopAnim(this.yAnim);
      this.sdy = this.sdx = 0;

      if (!isNaN(a)) {
        var e = this.follow,
            g = c.container,
            f = this.set;
        d.remove(f);
        this.removeDiv();
        f = g.set();
        f.node.style.pointerEvents = "none";
        this.set = f;
        this.mainSet ? (this.mainSet.push(this.set), this.sdx = this.mainSet.x, this.sdy = this.mainSet.y) : c.balloonsSet.push(f);

        if (this.show) {
          var h = this.l,
              k = this.t,
              l = this.r,
              m = this.b,
              n = this.balloonColor,
              q = this.fillColor,
              p = this.borderColor,
              t = q;
          void 0 != n && (this.adjustBorderColor ? t = p = n : q = n);
          var r = this.horizontalPadding,
              w = this.verticalPadding,
              z = this.pointerWidth,
              x = this.pointerOrientation,
              u = this.cornerRadius,
              A = c.fontFamily,
              y = this.fontSize;
          void 0 == y && (y = c.fontSize);
          var n = document.createElement("div"),
              B = c.classNamePrefix;
          n.className = B + "-balloon-div";
          this.className && (n.className = n.className + " " + B + "-balloon-div-" + this.className);
          B = n.style;
          this.disableMouseEvents && (B.pointerEvents = "none");
          B.position = "absolute";
          var D = this.minWidth,
              C = document.createElement("div");
          n.appendChild(C);
          var I = C.style;
          isNaN(D) || (I.minWidth = D - 2 * r + "px");
          I.textAlign = this.textAlign;
          I.maxWidth = this.maxWidth + "px";
          I.fontSize = y + "px";
          I.color = this.color;
          I.fontFamily = A;
          C.innerHTML = this.text;
          c.chartDiv.appendChild(n);
          this.textDiv = n;
          var I = n.offsetWidth,
              H = n.offsetHeight;
          n.clientHeight && (I = n.clientWidth, H = n.clientHeight);
          A = H + 2 * w;
          C = I + 2 * r;
          !isNaN(D) && C < D && (C = D);
          window.opera && (A += 2);
          var Q = !1,
              y = this.offsetY;
          c.handDrawn && (y += c.handDrawScatter + 2);
          "H" != x ? (D = a - C / 2, b < k + A + 10 && "down" != x ? (Q = !0, e && (b += y), y = b + z, this.deltaSignY = -1) : (e && (b -= y), y = b - A - z, this.deltaSignY = 1)) : (2 * z > A && (z = A / 2), y = b - A / 2, a < h + (l - h) / 2 ? (D = a + z, this.deltaSignX = -1) : (D = a - C - z, this.deltaSignX = 1));
          y + A >= m && (y = m - A);
          y < k && (y = k);
          D < h && (D = h);
          D + C > l && (D = l - C);
          var k = y + w,
              m = D + r,
              M = this.shadowAlpha,
              P = this.shadowColor,
              r = this.borderThickness,
              ia = this.bulletSize,
              J,
              w = this.fillAlpha,
              aa = this.borderAlpha;
          this.showBullet && (J = d.circle(g, ia, t, w), f.push(J));
          this.drop ? (h = C / 1.6, l = 0, "V" == x && (x = "down"), "H" == x && (x = "left"), "down" == x && (D = a + 1, y = b - h - h / 3), "up" == x && (l = 180, D = a + 1, y = b + h + h / 3), "left" == x && (l = 270, D = a + h + h / 3 + 2, y = b), "right" == x && (l = 90, D = a - h - h / 3 + 2, y = b), k = y - H / 2 + 1, m = D - I / 2 - 1, q = d.drop(g, h, l, q, w, r, p, aa)) : 0 < u || 0 === z ? (0 < M && (a = d.rect(g, C, A, q, 0, r + 1, P, M, u), d.isModern ? a.translate(1, 1) : a.translate(4, 4), f.push(a)), q = d.rect(g, C, A, q, w, r, p, aa, u)) : (t = [], u = [], "H" != x ? (h = a - D, h > C - z && (h = C - z), h < z && (h = z), t = [0, h - z, a - D, h + z, C, C, 0, 0], u = Q ? [0, 0, b - y, 0, 0, A, A, 0] : [A, A, b - y, A, A, 0, 0, A]) : (x = b - y, x > A - z && (x = A - z), x < z && (x = z), u = [0, x - z, b - y, x + z, A, A, 0, 0], t = a < h + (l - h) / 2 ? [0, 0, D < a ? 0 : a - D, 0, 0, C, C, 0] : [C, C, D + C > a ? C : a - D, C, C, 0, 0, C]), 0 < M && (a = d.polygon(g, t, u, q, 0, r, P, M), a.translate(1, 1), f.push(a)), q = d.polygon(g, t, u, q, w, r, p, aa));
          this.bg = q;
          f.push(q);
          q.toFront();
          d.setCN(c, q, "balloon-bg");
          this.className && d.setCN(c, q, "balloon-bg-" + this.className);
          g = 1 * this.deltaSignX;
          m += this.sdx;
          k += this.sdy;
          B.left = m + "px";
          B.top = k + "px";
          f.translate(D - g, y, 1, !0);
          q = q.getBBox();
          this.bottom = y + A + 1;
          this.yPos = q.y + y;
          J && J.translate(this.pointToX - D + g, b - y);
          b = this.animationDuration;
          0 < this.animationDuration && !e && !isNaN(this.prevX) && (f.translate(this.prevX, this.prevY, NaN, !0), f.animate({
            translate: D - g + "," + y
          }, b, "easeOutSine"), n && (B.left = this.prevTX + "px", B.top = this.prevTY + "px", this.xAnim = c.animate({
            node: n
          }, "left", this.prevTX, m, b, "easeOutSine", "px"), this.yAnim = c.animate({
            node: n
          }, "top", this.prevTY, k, b, "easeOutSine", "px")));
          this.prevX = D - g;
          this.prevY = y;
          this.prevTX = m;
          this.prevTY = k;
        }
      }
    },
    fixPrevious: function fixPrevious() {
      this.rPrevX = this.prevX;
      this.rPrevY = this.prevY;
      this.rPrevTX = this.prevTX;
      this.rPrevTY = this.prevTY;
    },
    restorePrevious: function restorePrevious() {
      this.prevX = this.rPrevX;
      this.prevY = this.rPrevY;
      this.prevTX = this.rPrevTX;
      this.prevTY = this.rPrevTY;
    },
    followMouse: function followMouse() {
      if (this.follow && this.show) {
        var a = this.chart.mouseX - this.offsetX * this.deltaSignX - this.sdx,
            b = this.chart.mouseY - this.sdy;
        this.pointToX = a;
        this.pointToY = b;
        if (a != this.previousX || b != this.previousY) if (this.previousX = a, this.previousY = b, 0 === this.cornerRadius) this.draw();else {
          var c = this.set;

          if (c) {
            var d = c.getBBox(),
                a = a - d.width / 2,
                g = b - d.height - 10;
            a < this.l && (a = this.l);
            a > this.r - d.width && (a = this.r - d.width);
            g < this.t && (g = b + 10);
            c.translate(a, g);
            b = this.textDiv.style;
            b.left = a + this.horizontalPadding + "px";
            b.top = g + this.verticalPadding + "px";
          }
        }
      }
    },
    changeColor: function changeColor(a) {
      this.balloonColor = a;
    },
    setBounds: function setBounds(a, b, c, d) {
      this.l = a;
      this.t = b;
      this.r = c;
      this.b = d;
      this.destroyTO && clearTimeout(this.destroyTO);
    },
    showBalloon: function showBalloon(a) {
      if (this.text != a || this.positionChanged) this.text = a, this.isHiding = !1, this.show = !0, this.destroyTO && clearTimeout(this.destroyTO), a = this.chart, this.fadeAnim1 && a.stopAnim(this.fadeAnim1), this.fadeAnim2 && a.stopAnim(this.fadeAnim2), this.draw(), this.positionChanged = !1;
    },
    hide: function hide(a) {
      var b = this;
      b.text = void 0;
      isNaN(a) && (a = b.fadeOutDuration);
      var c = b.chart;

      if (0 < a && !b.isHiding) {
        b.isHiding = !0;
        b.destroyTO && clearTimeout(b.destroyTO);
        b.destroyTO = setTimeout(function () {
          b.destroy.call(b);
        }, 1E3 * a);
        b.follow = !1;
        b.show = !1;
        var d = b.set;
        d && (d.setAttr("opacity", b.fillAlpha), b.fadeAnim1 = d.animate({
          opacity: 0
        }, a, "easeInSine"));
        b.textDiv && (b.fadeAnim2 = c.animate({
          node: b.textDiv
        }, "opacity", 1, 0, a, "easeInSine", ""));
      } else b.show = !1, b.follow = !1, b.destroy();
    },
    setPosition: function setPosition(a, b) {
      if (a != this.pointToX || b != this.pointToY) this.previousX = this.pointToX, this.previousY = this.pointToY, this.pointToX = a, this.pointToY = b, this.positionChanged = !0;
    },
    followCursor: function followCursor(a) {
      var b = this;
      b.follow = a;
      clearInterval(b.interval);
      var c = b.chart.mouseX - b.sdx,
          d = b.chart.mouseY - b.sdy;
      !isNaN(c) && a && (b.pointToX = c - b.offsetX * b.deltaSignX, b.pointToY = d, b.followMouse(), b.interval = setInterval(function () {
        b.followMouse.call(b);
      }, 40));
    },
    removeDiv: function removeDiv() {
      if (this.textDiv) {
        var a = this.textDiv.parentNode;
        a && a.removeChild(this.textDiv);
      }
    },
    destroy: function destroy() {
      clearInterval(this.interval);
      d.remove(this.set);
      this.removeDiv();
      this.set = null;
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.AmCoordinateChart = d.Class({
    inherits: d.AmChart,
    construct: function construct(a) {
      d.AmCoordinateChart.base.construct.call(this, a);
      this.theme = a;
      this.createEvents("rollOverGraphItem", "rollOutGraphItem", "clickGraphItem", "doubleClickGraphItem", "rightClickGraphItem", "clickGraph", "rollOverGraph", "rollOutGraph");
      this.startAlpha = 1;
      this.startDuration = 0;
      this.startEffect = "elastic";
      this.sequencedAnimation = !0;
      this.colors = "#FF6600 #FCD202 #B0DE09 #0D8ECF #2A0CD0 #CD0D74 #CC0000 #00CC00 #0000CC #DDDDDD #999999 #333333 #990000".split(" ");
      this.balloonDateFormat = "MMM DD, YYYY";
      this.valueAxes = [];
      this.graphs = [];
      this.guides = [];
      this.gridAboveGraphs = !1;
      d.applyTheme(this, a, "AmCoordinateChart");
    },
    initChart: function initChart() {
      d.AmCoordinateChart.base.initChart.call(this);
      this.drawGraphs = !0;
      var a = this.categoryAxis;
      a && (this.categoryAxis = d.processObject(a, d.CategoryAxis, this.theme));
      this.processValueAxes();
      this.createValueAxes();
      this.processGraphs();
      this.processGuides();
      d.VML && (this.startAlpha = 1);
      this.setLegendData(this.graphs);
      this.gridAboveGraphs && (this.gridSet.toFront(), this.bulletSet.toFront(), this.balloonsSet.toFront());
    },
    createValueAxes: function createValueAxes() {
      if (0 === this.valueAxes.length) {
        var a = new d.ValueAxis();
        this.addValueAxis(a);
      }
    },
    parseData: function parseData() {
      this.processValueAxes();
      this.processGraphs();
    },
    parseSerialData: function parseSerialData(a) {
      this.chartData = [];
      if (a) {
        if (0 < this.processTimeout) {
          1 > this.processCount && (this.processCount = 1);
          var b = a.length / this.processCount;
          this.parseCount = Math.ceil(b) - 1;

          for (var c = 0; c < b; c++) {
            this.delayParseSerialData(a, c);
          }
        } else this.parseCount = 0, this.parsePartSerialData(a, 0, a.length, 0);
      } else this.onDataUpdated();
    },
    delayParseSerialData: function delayParseSerialData(a, b) {
      var c = this,
          d = c.processCount;
      setTimeout(function () {
        c.parsePartSerialData.call(c, a, b * d, (b + 1) * d, b);
      }, c.processTimeout);
    },
    parsePartSerialData: function parsePartSerialData(a, b, c, e) {
      c > a.length && (c = a.length);
      var g = this.graphs,
          f = {},
          h = this.seriesIdField;
      h || (h = this.categoryField);
      var k = !1,
          l,
          m = this.categoryAxis,
          n,
          q,
          p;
      m && (k = m.parseDates, n = m.forceShowField, p = m.classNameField, q = m.labelColorField, l = m.categoryFunction);
      var t,
          r,
          w = {},
          z;
      k && (t = d.extractPeriod(m.minPeriod), r = t.period, t = t.count, z = d.getPeriodDuration(r, t));
      var x = {};
      this.lookupTable = x;
      var u,
          A = this.dataDateFormat,
          y = {};

      for (u = b; u < c; u++) {
        var B = {},
            D = a[u];
        b = D[this.categoryField];
        B.dataContext = D;
        B.category = l ? l(b, D, m) : String(b);
        n && (B.forceShow = D[n]);
        p && (B.className = D[p]);
        q && (B.labelColor = D[q]);
        x[D[h]] = B;
        if (k && (m.categoryFunction ? b = m.categoryFunction(b, D, m) : (!A || b instanceof Date || (b = b.toString() + " |"), b = d.getDate(b, A, m.minPeriod)), b = d.resetDateToMin(b, r, t, m.firstDayOfWeek), B.category = b, B.time = b.getTime(), isNaN(B.time))) continue;
        var C = this.valueAxes;
        B.axes = {};
        B.x = {};
        var I;

        for (I = 0; I < C.length; I++) {
          var H = C[I].id;
          B.axes[H] = {};
          B.axes[H].graphs = {};
          var Q;

          for (Q = 0; Q < g.length; Q++) {
            b = g[Q];
            var M = b.id,
                P = 1.1;
            isNaN(b.gapPeriod) || (P = b.gapPeriod);
            var ia = b.periodValue;

            if (b.valueAxis.id == H) {
              B.axes[H].graphs[M] = {};
              var J = {};
              J.index = u;
              var aa = D;
              b.dataProvider && (aa = f);
              J.values = this.processValues(aa, b, ia);
              if (!b.connect || b.forceGap && !isNaN(b.gapPeriod)) if (y && y[M] && 0 < P && B.time - w[M] >= z * P && (y[M].gap = !0), b.forceGap) {
                var P = 0,
                    ma;

                for (ma in J.values) {
                  P++;
                }

                0 < P && (w[M] = B.time, y[M] = J);
              } else w[M] = B.time, y[M] = J;
              this.processFields(b, J, aa);
              J.category = B.category;
              J.serialDataItem = B;
              J.graph = b;
              B.axes[H].graphs[M] = J;
            }
          }
        }

        this.chartData[u] = B;
      }

      if (this.parseCount == e) {
        for (a = 0; a < g.length; a++) {
          b = g[a], b.dataProvider && this.parseGraphData(b);
        }

        this.dataChanged = !1;
        this.dispatchDataUpdated = !0;
        this.onDataUpdated();
      }
    },
    processValues: function processValues(a, b, c) {
      var e = {},
          g,
          f = !1;
      "candlestick" != b.type && "ohlc" != b.type || "" === c || (f = !0);

      for (var h = "value error open close low high".split(" "), k = 0; k < h.length; k++) {
        var l = h[k];
        "value" != l && "error" != l && f && (c = l.charAt(0).toUpperCase() + l.slice(1));
        var m = a[b[l + "Field"] + c];
        null !== m && (g = Number(m), isNaN(g) || (e[l] = g), "date" == b.valueAxis.type && void 0 !== m && (g = d.getDate(m, b.chart.dataDateFormat), e[l] = g.getTime()));
      }

      return e;
    },
    parseGraphData: function parseGraphData(a) {
      var b = a.dataProvider,
          c = a.seriesIdField;
      c || (c = this.seriesIdField);
      c || (c = this.categoryField);
      var d;

      for (d = 0; d < b.length; d++) {
        var g = b[d],
            f = this.lookupTable[String(g[c])],
            h = a.valueAxis.id;
        f && (h = f.axes[h].graphs[a.id], h.serialDataItem = f, h.values = this.processValues(g, a, a.periodValue), this.processFields(a, h, g));
      }
    },
    addValueAxis: function addValueAxis(a) {
      a.chart = this;
      this.valueAxes.push(a);
      this.validateData();
    },
    removeValueAxesAndGraphs: function removeValueAxesAndGraphs() {
      var a = this.valueAxes,
          b;

      for (b = a.length - 1; -1 < b; b--) {
        this.removeValueAxis(a[b]);
      }
    },
    removeValueAxis: function removeValueAxis(a) {
      var b = this.graphs,
          c;

      for (c = b.length - 1; 0 <= c; c--) {
        var d = b[c];
        d && d.valueAxis == a && this.removeGraph(d);
      }

      b = this.valueAxes;

      for (c = b.length - 1; 0 <= c; c--) {
        b[c] == a && b.splice(c, 1);
      }

      this.validateData();
    },
    addGraph: function addGraph(a) {
      this.graphs.push(a);
      this.chooseGraphColor(a, this.graphs.length - 1);
      this.validateData();
    },
    removeGraph: function removeGraph(a) {
      var b = this.graphs,
          c;

      for (c = b.length - 1; 0 <= c; c--) {
        b[c] == a && (b.splice(c, 1), a.destroy());
      }

      this.validateData();
    },
    handleValueAxisZoom: function handleValueAxisZoom() {},
    processValueAxes: function processValueAxes() {
      var a = this.valueAxes,
          b;

      for (b = 0; b < a.length; b++) {
        var c = a[b],
            c = d.processObject(c, d.ValueAxis, this.theme);
        a[b] = c;
        c.chart = this;
        c.init();
        this.listenTo(c, "axisIntZoomed", this.handleValueAxisZoom);
        c.id || (c.id = "valueAxisAuto" + b + "_" + new Date().getTime());
        void 0 === c.usePrefixes && (c.usePrefixes = this.usePrefixes);
      }
    },
    processGuides: function processGuides() {
      var a = this.guides,
          b = this.categoryAxis;
      if (a) for (var c = 0; c < a.length; c++) {
        var e = a[c];
        (void 0 !== e.category || void 0 !== e.date) && b && b.addGuide(e);
        e.id || (e.id = "guideAuto" + c + "_" + new Date().getTime());
        var g = e.valueAxis;
        g ? (d.isString(g) && (g = this.getValueAxisById(g)), g ? g.addGuide(e) : this.valueAxes[0].addGuide(e)) : isNaN(e.value) || this.valueAxes[0].addGuide(e);
      }
    },
    processGraphs: function processGraphs() {
      var a = this.graphs,
          b;
      this.graphsById = {};

      for (b = 0; b < a.length; b++) {
        var c = a[b],
            c = d.processObject(c, d.AmGraph, this.theme);
        a[b] = c;
        this.chooseGraphColor(c, b);
        c.chart = this;
        c.init();
        d.isString(c.valueAxis) && (c.valueAxis = this.getValueAxisById(c.valueAxis));
        c.valueAxis || (c.valueAxis = this.valueAxes[0]);
        c.id || (c.id = "graphAuto" + b + "_" + new Date().getTime());
        this.graphsById[c.id] = c;
      }
    },
    formatString: function formatString(a, b, c) {
      var e = b.graph,
          g = e.valueAxis;

      if (g.duration && g.maxInterval && b.values.value) {
        var f = g.numberFormatter;
        f || (f = chart.nf);
        g = d.formatDuration(b.values.value, g.duration, "", g.durationUnits, g.maxInterval, f);
        a = a.split("[[value]]").join(g);
      }

      a = d.massReplace(a, {
        "[[title]]": e.title,
        "[[description]]": b.description
      });
      a = c ? d.fixNewLines(a) : d.fixBrakes(a);
      return a = d.cleanFromEmpty(a);
    },
    getBalloonColor: function getBalloonColor(a, b, c) {
      var e = a.lineColor,
          g = a.balloonColor;
      c && (g = e);
      c = a.fillColorsR;
      "object" == _typeof(c) ? e = c[0] : void 0 !== c && (e = c);
      b.isNegative && (c = a.negativeLineColor, a = a.negativeFillColors, "object" == _typeof(a) ? c = a[0] : void 0 !== a && (c = a), void 0 !== c && (e = c));
      void 0 !== b.color && (e = b.color);
      void 0 !== b.lineColor && (e = b.lineColor);
      b = b.fillColors;
      void 0 !== b && (e = b, d.ifArray(b) && (e = b[0]));
      void 0 === g && (g = e);
      return g;
    },
    getGraphById: function getGraphById(a) {
      return d.getObjById(this.graphs, a);
    },
    getValueAxisById: function getValueAxisById(a) {
      return d.getObjById(this.valueAxes, a);
    },
    processFields: function processFields(a, b, c) {
      if (a.itemColors) {
        var e = a.itemColors,
            g = b.index;
        b.color = g < e.length ? e[g] : d.randomColor();
      }

      e = "lineColor color alpha fillColors description bullet customBullet bulletSize bulletConfig url labelColor dashLength pattern gap className columnIndex".split(" ");

      for (g = 0; g < e.length; g++) {
        var f = e[g],
            h = a[f + "Field"];
        h && (h = c[h], d.isDefined(h) && (b[f] = h));
      }

      b.dataContext = c;
    },
    chooseGraphColor: function chooseGraphColor(a, b) {
      if (a.lineColor) a.lineColorR = a.lineColor;else {
        var c;
        c = this.colors.length > b ? this.colors[b] : a.lineColorR ? a.lineColorR : d.randomColor();
        a.lineColorR = c;
      }
      a.fillColorsR = a.fillColors ? a.fillColors : a.lineColorR;
      a.bulletBorderColorR = a.bulletBorderColor ? a.bulletBorderColor : a.useLineColorForBulletBorder ? a.lineColorR : a.bulletColor;
      a.bulletColorR = a.bulletColor ? a.bulletColor : a.lineColorR;
      if (c = this.patterns) a.pattern = c[b];
    },
    handleLegendEvent: function handleLegendEvent(a) {
      var b = a.type;

      if (a = a.dataItem) {
        var c = a.hidden,
            d = a.showBalloon;

        switch (b) {
          case "clickMarker":
            this.textClickEnabled && (d ? this.hideGraphsBalloon(a) : this.showGraphsBalloon(a));
            break;

          case "clickLabel":
            d ? this.hideGraphsBalloon(a) : this.showGraphsBalloon(a);
            break;

          case "rollOverItem":
            c || this.highlightGraph(a);
            break;

          case "rollOutItem":
            c || this.unhighlightGraph();
            break;

          case "hideItem":
            this.hideGraph(a);
            break;

          case "showItem":
            this.showGraph(a);
        }
      }
    },
    highlightGraph: function highlightGraph(a) {
      var b = this.graphs;

      if (b) {
        var c,
            d = .2;
        this.legend && (d = this.legend.rollOverGraphAlpha);
        if (1 != d) for (c = 0; c < b.length; c++) {
          var g = b[c];
          g != a && g.changeOpacity(d);
        }
      }
    },
    unhighlightGraph: function unhighlightGraph() {
      var a;
      this.legend && (a = this.legend.rollOverGraphAlpha);

      if (1 != a) {
        a = this.graphs;
        var b;

        for (b = 0; b < a.length; b++) {
          a[b].changeOpacity(1);
        }
      }
    },
    showGraph: function showGraph(a) {
      a.switchable && (a.hidden = !1, this.dataChanged = !0, "xy" != this.type && (this.marginsUpdated = !1), this.chartCreated && this.initChart());
    },
    hideGraph: function hideGraph(a) {
      a.switchable && (this.dataChanged = !0, "xy" != this.type && (this.marginsUpdated = !1), a.hidden = !0, this.chartCreated && this.initChart());
    },
    hideGraphsBalloon: function hideGraphsBalloon(a) {
      a.showBalloon = !1;
      this.updateLegend();
    },
    showGraphsBalloon: function showGraphsBalloon(a) {
      a.showBalloon = !0;
      this.updateLegend();
    },
    updateLegend: function updateLegend() {
      this.legend && this.legend.invalidateSize();
    },
    resetAnimation: function resetAnimation() {
      this.animatable = [];
      var a = this.graphs;

      if (a) {
        var b;

        for (b = 0; b < a.length; b++) {
          a[b].animationPlayed = !1;
        }
      }
    },
    animateAgain: function animateAgain() {
      this.resetAnimation();
      this.validateNow();
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.TrendLine = d.Class({
    construct: function construct(a) {
      this.cname = "TrendLine";
      this.createEvents("click", "rollOver", "rollOut");
      this.isProtected = !1;
      this.dashLength = 0;
      this.lineColor = "#00CC00";
      this.lineThickness = this.lineAlpha = 1;
      d.applyTheme(this, a, this.cname);
    },
    draw: function draw() {
      var a = this;
      a.destroy();
      var b = a.chart,
          c = b.container,
          e,
          g,
          f,
          h,
          k = a.categoryAxis,
          l = a.initialDate,
          m = a.initialCategory,
          n = a.finalDate,
          q = a.finalCategory,
          p = a.valueAxis,
          t = a.valueAxisX,
          r = a.initialXValue,
          w = a.finalXValue,
          z = a.initialValue,
          x = a.finalValue,
          u = p.recalculateToPercents,
          A = b.dataDateFormat;
      k && (l && (l = d.getDate(l, A, "fff"), a.initialDate = l, e = k.dateToCoordinate(l)), m && (e = k.categoryToCoordinate(m)), n && (n = d.getDate(n, A, "fff"), a.finalDate = n, g = k.dateToCoordinate(n)), q && (g = k.categoryToCoordinate(q)));
      t && !u && (isNaN(r) || (e = t.getCoordinate(r)), isNaN(w) || (g = t.getCoordinate(w)));
      p && !u && (isNaN(z) || (f = p.getCoordinate(z)), isNaN(x) || (h = p.getCoordinate(x)));

      if (!(isNaN(e) || isNaN(g) || isNaN(f) || isNaN(f))) {
        b.rotate ? (k = [f, h], h = [e, g]) : (k = [e, g], h = [f, h]);
        f = d.line(c, k, h, a.lineColor, a.lineAlpha, a.lineThickness, a.dashLength);
        e = k;
        g = h;
        n = k[1] - k[0];
        q = h[1] - h[0];
        0 === n && (n = .01);
        0 === q && (q = .01);
        l = n / Math.abs(n);
        m = q / Math.abs(q);
        q = 90 * Math.PI / 180 - Math.asin(n / (n * q / Math.abs(n * q) * Math.sqrt(Math.pow(n, 2) + Math.pow(q, 2))));
        n = Math.abs(5 * Math.cos(q));
        q = Math.abs(5 * Math.sin(q));
        e.push(k[1] - l * q, k[0] - l * q);
        g.push(h[1] + m * n, h[0] + m * n);
        h = d.polygon(c, e, g, "#ffffff", .005, 0);
        c = c.set([h, f]);
        c.translate(b.marginLeftReal, b.marginTopReal);
        b.trendLinesSet.push(c);
        d.setCN(b, f, "trend-line");
        d.setCN(b, f, "trend-line-" + a.id);
        a.line = f;
        a.set = c;
        if (f = a.initialImage) f = d.processObject(f, d.Image, a.theme), f.chart = b, f.draw(), f.translate(e[0] + f.offsetX, g[0] + f.offsetY), c.push(f.set);
        if (f = a.finalImage) f = d.processObject(f, d.Image, a.theme), f.chart = b, f.draw(), f.translate(e[1] + f.offsetX, g[1] + f.offsetY), c.push(f.set);
        c.mouseup(function () {
          a.handleLineClick();
        }).mouseover(function () {
          a.handleLineOver();
        }).mouseout(function () {
          a.handleLineOut();
        });
        c.touchend && c.touchend(function () {
          a.handleLineClick();
        });
        c.clipRect(0, 0, b.plotAreaWidth, b.plotAreaHeight);
      }
    },
    handleLineClick: function handleLineClick() {
      this.fire({
        type: "click",
        trendLine: this,
        chart: this.chart
      });
    },
    handleLineOver: function handleLineOver() {
      var a = this.rollOverColor;
      void 0 !== a && this.line.attr({
        stroke: a
      });
      this.balloonText && (clearTimeout(this.chart.hoverInt), a = this.line.getBBox(), this.chart.showBalloon(this.balloonText, this.lineColor, !0, this.x + a.x + a.width / 2, this.y + a.y + a.height / 2));
      this.fire({
        type: "rollOver",
        trendLine: this,
        chart: this.chart
      });
    },
    handleLineOut: function handleLineOut() {
      this.line.attr({
        stroke: this.lineColor
      });
      this.balloonText && this.chart.hideBalloon();
      this.fire({
        type: "rollOut",
        trendLine: this,
        chart: this.chart
      });
    },
    destroy: function destroy() {
      d.remove(this.set);
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.Image = d.Class({
    construct: function construct(a) {
      this.cname = "Image";
      this.height = this.width = 20;
      this.rotation = this.offsetY = this.offsetX = 0;
      this.balloonColor = this.color = "#000000";
      this.opacity = 1;
      d.applyTheme(this, a, this.cname);
    },
    draw: function draw() {
      var a = this;
      a.set && a.set.remove();
      var b = a.chart.container;
      a.set = b.set();
      var c, d;
      a.url ? (c = b.image(a.url, 0, 0, a.width, a.height), d = 1) : a.svgPath && (c = b.path(a.svgPath), c.setAttr("fill", a.color), c.setAttr("stroke", a.outlineColor), b = c.getBBox(), d = Math.min(a.width / b.width, a.height / b.height));
      c && (c.setAttr("opacity", a.opacity), a.set.rotate(a.rotation), c.translate(-a.width / 2, -a.height / 2, d), a.balloonText && c.mouseover(function () {
        a.chart.showBalloon(a.balloonText, a.balloonColor, !0);
      }).mouseout(function () {
        a.chart.hideBalloon();
      }).touchend(function () {
        a.chart.hideBalloon();
      }).touchstart(function () {
        a.chart.showBalloon(a.balloonText, a.balloonColor, !0);
      }), a.set.push(c));
    },
    translate: function translate(a, b) {
      this.set && this.set.translate(a, b);
    }
  });
})();

(function () {
  var d = window.AmCharts;

  d.circle = function (a, b, c, e, g, f, h, k, l) {
    0 >= b && (b = .001);
    if (void 0 == g || 0 === g) g = .01;
    void 0 === f && (f = "#000000");
    void 0 === h && (h = 0);
    e = {
      fill: c,
      stroke: f,
      "fill-opacity": e,
      "stroke-width": g,
      "stroke-opacity": h
    };
    a = isNaN(l) ? a.circle(0, 0, b).attr(e) : a.ellipse(0, 0, b, l).attr(e);
    k && a.gradient("radialGradient", [c, d.adjustLuminosity(c, -.6)]);
    return a;
  };

  d.text = function (a, b, c, e, g, f, h, k) {
    f || (f = "middle");
    "right" == f && (f = "end");
    "left" == f && (f = "start");
    isNaN(k) && (k = 1);
    void 0 !== b && (b = String(b), d.isIE && !d.isModern && (b = b.replace("&amp;", "&"), b = b.replace("&", "&amp;")));
    c = {
      fill: c,
      "font-family": e,
      "font-size": g + "px",
      opacity: k
    };
    !0 === h && (c["font-weight"] = "bold");
    c["text-anchor"] = f;
    return a.text(b, c);
  };

  d.polygon = function (a, b, c, e, g, f, h, k, l, m, n) {
    isNaN(f) && (f = .01);
    isNaN(k) && (k = g);
    var q = e,
        p = !1;
    "object" == _typeof(q) && 1 < q.length && (p = !0, q = q[0]);
    void 0 === h && (h = q);
    g = {
      fill: q,
      stroke: h,
      "fill-opacity": g,
      "stroke-width": f,
      "stroke-opacity": k
    };
    void 0 !== n && 0 < n && (g["stroke-dasharray"] = n);
    n = d.dx;
    f = d.dy;
    a.handDrawn && (c = d.makeHD(b, c, a.handDrawScatter), b = c[0], c = c[1]);
    h = Math.round;
    m && (h = Number);
    k = "M" + (h(b[0]) + n) + "," + (h(c[0]) + f);

    for (q = 1; q < b.length; q++) {
      m && (b[q] = d.roundTo(b[q], 5), c[q] = d.roundTo(c[q], 5)), k += " L" + (h(b[q]) + n) + "," + (h(c[q]) + f);
    }

    a = a.path(k + " Z").attr(g);
    p && a.gradient("linearGradient", e, l);
    return a;
  };

  d.rect = function (a, b, c, e, g, f, h, k, l, m, n) {
    if (isNaN(b) || isNaN(c)) return a.set();
    isNaN(f) && (f = 0);
    void 0 === l && (l = 0);
    void 0 === m && (m = 270);
    isNaN(g) && (g = 0);
    var q = e,
        p = !1;
    "object" == _typeof(q) && (q = q[0], p = !0);
    void 0 === h && (h = q);
    void 0 === k && (k = g);
    b = Math.round(b);
    c = Math.round(c);
    var t = 0,
        r = 0;
    0 > b && (b = Math.abs(b), t = -b);
    0 > c && (c = Math.abs(c), r = -c);
    t += d.dx;
    r += d.dy;
    g = {
      fill: q,
      stroke: h,
      "fill-opacity": g,
      "stroke-opacity": k
    };
    void 0 !== n && 0 < n && (g["stroke-dasharray"] = n);
    a = a.rect(t, r, b, c, l, f).attr(g);
    p && a.gradient("linearGradient", e, m);
    return a;
  };

  d.bullet = function (a, b, c, e, g, f, h, k, l, m, n, q, p) {
    var t;
    "circle" == b && (b = "round");

    switch (b) {
      case "round":
        t = d.circle(a, c / 2, e, g, f, h, k);
        break;

      case "square":
        t = d.polygon(a, [-c / 2, c / 2, c / 2, -c / 2], [c / 2, c / 2, -c / 2, -c / 2], e, g, f, h, k, m - 180, void 0, p);
        break;

      case "rectangle":
        t = d.polygon(a, [-c, c, c, -c], [c / 2, c / 2, -c / 2, -c / 2], e, g, f, h, k, m - 180, void 0, p);
        break;

      case "diamond":
        t = d.polygon(a, [-c / 2, 0, c / 2, 0], [0, -c / 2, 0, c / 2], e, g, f, h, k);
        break;

      case "triangleUp":
        t = d.triangle(a, c, 0, e, g, f, h, k);
        break;

      case "triangleDown":
        t = d.triangle(a, c, 180, e, g, f, h, k);
        break;

      case "triangleLeft":
        t = d.triangle(a, c, 270, e, g, f, h, k);
        break;

      case "triangleRight":
        t = d.triangle(a, c, 90, e, g, f, h, k);
        break;

      case "bubble":
        t = d.circle(a, c / 2, e, g, f, h, k, !0);
        break;

      case "line":
        t = d.line(a, [-c / 2, c / 2], [0, 0], e, g, f, h, k);
        break;

      case "yError":
        t = a.set();
        t.push(d.line(a, [0, 0], [-c / 2, c / 2], e, g, f));
        t.push(d.line(a, [-l, l], [-c / 2, -c / 2], e, g, f));
        t.push(d.line(a, [-l, l], [c / 2, c / 2], e, g, f));
        break;

      case "xError":
        t = a.set(), t.push(d.line(a, [-c / 2, c / 2], [0, 0], e, g, f)), t.push(d.line(a, [-c / 2, -c / 2], [-l, l], e, g, f)), t.push(d.line(a, [c / 2, c / 2], [-l, l], e, g, f));
    }

    t && t.pattern(n, NaN, q);
    return t;
  };

  d.triangle = function (a, b, c, d, g, f, h, k) {
    if (void 0 === f || 0 === f) f = 1;
    void 0 === h && (h = "#000");
    void 0 === k && (k = 0);
    d = {
      fill: d,
      stroke: h,
      "fill-opacity": g,
      "stroke-width": f,
      "stroke-opacity": k
    };
    b /= 2;
    var l;
    0 === c && (l = " M" + -b + "," + b + " L0," + -b + " L" + b + "," + b + " Z");
    180 == c && (l = " M" + -b + "," + -b + " L0," + b + " L" + b + "," + -b + " Z");
    90 == c && (l = " M" + -b + "," + -b + " L" + b + ",0 L" + -b + "," + b + " Z");
    270 == c && (l = " M" + -b + ",0 L" + b + "," + b + " L" + b + "," + -b + " Z");
    return a.path(l).attr(d);
  };

  d.line = function (a, b, c, e, g, f, h, k, l, m, n) {
    if (a.handDrawn && !n) return d.handDrawnLine(a, b, c, e, g, f, h, k, l, m, n);
    f = {
      fill: "none",
      "stroke-width": f
    };
    void 0 !== h && 0 < h && (f["stroke-dasharray"] = h);
    isNaN(g) || (f["stroke-opacity"] = g);
    e && (f.stroke = e);
    e = Math.round;
    m && (e = Number, b[0] = d.roundTo(b[0], 5), c[0] = d.roundTo(c[0], 5));
    m = d.dx;
    g = d.dy;
    h = "M" + (e(b[0]) + m) + "," + (e(c[0]) + g);

    for (k = 1; k < b.length; k++) {
      b[k] = d.roundTo(b[k], 5), c[k] = d.roundTo(c[k], 5), h += " L" + (e(b[k]) + m) + "," + (e(c[k]) + g);
    }

    if (d.VML) return a.path(h, void 0, !0).attr(f);
    l && (h += " M0,0 L0,0");
    return a.path(h).attr(f);
  };

  d.makeHD = function (a, b, c) {
    for (var d = [], g = [], f = 1; f < a.length; f++) {
      for (var h = Number(a[f - 1]), k = Number(b[f - 1]), l = Number(a[f]), m = Number(b[f]), n = Math.round(Math.sqrt(Math.pow(l - h, 2) + Math.pow(m - k, 2)) / 50) + 1, l = (l - h) / n, m = (m - k) / n, q = 0; q <= n; q++) {
        var p = k + q * m + Math.random() * c;
        d.push(h + q * l + Math.random() * c);
        g.push(p);
      }
    }

    return [d, g];
  };

  d.handDrawnLine = function (a, b, c, e, g, f, h, k, l, m) {
    var n,
        q = a.set();

    for (n = 1; n < b.length; n++) {
      for (var p = [b[n - 1], b[n]], t = [c[n - 1], c[n]], t = d.makeHD(p, t, a.handDrawScatter), p = t[0], t = t[1], r = 1; r < p.length; r++) {
        q.push(d.line(a, [p[r - 1], p[r]], [t[r - 1], t[r]], e, g, f + Math.random() * a.handDrawThickness - a.handDrawThickness / 2, h, k, l, m, !0));
      }
    }

    return q;
  };

  d.doNothing = function (a) {
    return a;
  };

  d.drop = function (a, b, c, d, g, f, h, k) {
    var l = 1 / 180 * Math.PI,
        m = c - 20,
        n = Math.sin(m * l) * b,
        q = Math.cos(m * l) * b,
        p = Math.sin((m + 40) * l) * b,
        t = Math.cos((m + 40) * l) * b,
        r = .8 * b,
        w = -b / 3,
        z = b / 3;
    0 === c && (w = -w, z = 0);
    180 == c && (z = 0);
    90 == c && (w = 0);
    270 == c && (w = 0, z = -z);
    c = {
      fill: d,
      stroke: h,
      "stroke-width": f,
      "stroke-opacity": k,
      "fill-opacity": g
    };
    b = "M" + n + "," + q + " A" + b + "," + b + ",0,1,1," + p + "," + t + (" A" + r + "," + r + ",0,0,0," + (Math.sin((m + 20) * l) * b + z) + "," + (Math.cos((m + 20) * l) * b + w));
    b += " A" + r + "," + r + ",0,0,0," + n + "," + q;
    return a.path(b, void 0, void 0, "1000,1000").attr(c);
  };

  d.wedge = function (a, b, c, e, g, f, h, k, l, m, n, q, p, t) {
    var r = Math.round;
    f = r(f);
    h = r(h);
    k = r(k);
    var w = r(h / f * k),
        z = d.VML,
        x = 359.5 + f / 100;
    359.94 < x && (x = 359.94);
    g >= x && (g = x);
    var u = 1 / 180 * Math.PI,
        x = b + Math.sin(e * u) * k,
        A = c - Math.cos(e * u) * w,
        y = b + Math.sin(e * u) * f,
        B = c - Math.cos(e * u) * h,
        D = b + Math.sin((e + g) * u) * f,
        C = c - Math.cos((e + g) * u) * h,
        I = b + Math.sin((e + g) * u) * k,
        u = c - Math.cos((e + g) * u) * w,
        H = {
      fill: d.adjustLuminosity(m.fill, -.2),
      "stroke-opacity": 0,
      "fill-opacity": m["fill-opacity"]
    },
        Q = 0;
    180 < Math.abs(g) && (Q = 1);
    e = a.set();
    var M;
    z && (x = r(10 * x), y = r(10 * y), D = r(10 * D), I = r(10 * I), A = r(10 * A), B = r(10 * B), C = r(10 * C), u = r(10 * u), b = r(10 * b), l = r(10 * l), c = r(10 * c), f *= 10, h *= 10, k *= 10, w *= 10, 1 > Math.abs(g) && 1 >= Math.abs(D - y) && 1 >= Math.abs(C - B) && (M = !0));
    g = "";
    var P;
    q && (H["fill-opacity"] = 0, H["stroke-opacity"] = m["stroke-opacity"] / 2, H.stroke = m.stroke);

    if (0 < l) {
      P = " M" + x + "," + (A + l) + " L" + y + "," + (B + l);
      z ? (M || (P += " A" + (b - f) + "," + (l + c - h) + "," + (b + f) + "," + (l + c + h) + "," + y + "," + (B + l) + "," + D + "," + (C + l)), P += " L" + I + "," + (u + l), 0 < k && (M || (P += " B" + (b - k) + "," + (l + c - w) + "," + (b + k) + "," + (l + c + w) + "," + I + "," + (l + u) + "," + x + "," + (l + A)))) : (P += " A" + f + "," + h + ",0," + Q + ",1," + D + "," + (C + l) + " L" + I + "," + (u + l), 0 < k && (P += " A" + k + "," + w + ",0," + Q + ",0," + x + "," + (A + l)));
      P += " Z";
      var ia = l;
      z && (ia /= 10);

      for (var J = 0; J < ia; J += 10) {
        var aa = a.path(P, void 0, void 0, "1000,1000").attr(H);
        e.push(aa);
        aa.translate(0, -J);
      }

      P = a.path(" M" + x + "," + A + " L" + x + "," + (A + l) + " L" + y + "," + (B + l) + " L" + y + "," + B + " L" + x + "," + A + " Z", void 0, void 0, "1000,1000").attr(H);
      l = a.path(" M" + D + "," + C + " L" + D + "," + (C + l) + " L" + I + "," + (u + l) + " L" + I + "," + u + " L" + D + "," + C + " Z", void 0, void 0, "1000,1000").attr(H);
      e.push(P);
      e.push(l);
    }

    z ? (M || (g = " A" + r(b - f) + "," + r(c - h) + "," + r(b + f) + "," + r(c + h) + "," + r(y) + "," + r(B) + "," + r(D) + "," + r(C)), h = " M" + r(x) + "," + r(A) + " L" + r(y) + "," + r(B) + g + " L" + r(I) + "," + r(u)) : h = " M" + x + "," + A + " L" + y + "," + B + (" A" + f + "," + h + ",0," + Q + ",1," + D + "," + C) + " L" + I + "," + u;
    0 < k && (z ? M || (h += " B" + (b - k) + "," + (c - w) + "," + (b + k) + "," + (c + w) + "," + I + "," + u + "," + x + "," + A) : h += " A" + k + "," + w + ",0," + Q + ",0," + x + "," + A);
    a.handDrawn && (k = d.line(a, [x, y], [A, B], m.stroke, m.thickness * Math.random() * a.handDrawThickness, m["stroke-opacity"]), e.push(k));
    a = a.path(h + " Z", void 0, void 0, "1000,1000").attr(m);

    if (n) {
      k = [];

      for (w = 0; w < n.length; w++) {
        k.push(d.adjustLuminosity(m.fill, n[w]));
      }

      "radial" != t || d.isModern || (k = []);
      0 < k.length && a.gradient(t + "Gradient", k);
    }

    d.isModern && "radial" == t && a.grad && (a.grad.setAttribute("gradientUnits", "userSpaceOnUse"), a.grad.setAttribute("r", f), a.grad.setAttribute("cx", b), a.grad.setAttribute("cy", c));
    a.pattern(q, NaN, p);
    e.wedge = a;
    e.push(a);
    return e;
  };

  d.rgb2hex = function (a) {
    return (a = a.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i)) && 4 === a.length ? "#" + ("0" + parseInt(a[1], 10).toString(16)).slice(-2) + ("0" + parseInt(a[2], 10).toString(16)).slice(-2) + ("0" + parseInt(a[3], 10).toString(16)).slice(-2) : "";
  };

  d.adjustLuminosity = function (a, b) {
    a && -1 != a.indexOf("rgb") && (a = d.rgb2hex(a));
    a = String(a).replace(/[^0-9a-f]/gi, "");
    6 > a.length && (a = String(a[0]) + String(a[0]) + String(a[1]) + String(a[1]) + String(a[2]) + String(a[2]));
    b = b || 0;
    var c = "#",
        e,
        g;

    for (g = 0; 3 > g; g++) {
      e = parseInt(a.substr(2 * g, 2), 16), e = Math.round(Math.min(Math.max(0, e + e * b), 255)).toString(16), c += ("00" + e).substr(e.length);
    }

    return c;
  };
})();

(function () {
  var d = window.AmCharts;
  d.Bezier = d.Class({
    construct: function construct(a, b, c, e, g, f, h, k, l, m, n) {
      var q = a.chart,
          p = d.bezierX,
          t = d.bezierY;
      isNaN(q.bezierX) || (p = q.bezierX);
      isNaN(q.bezierY) || (t = q.bezierY);
      isNaN(p) && (q.rotate ? (p = 20, t = 4) : (t = 20, p = 4));
      var r, w;
      "object" == _typeof(h) && 1 < h.length && (w = !0, r = h, h = h[0]);
      "object" == _typeof(k) && (k = k[0]);
      0 === k && (h = "none");
      f = {
        fill: h,
        "fill-opacity": k,
        "stroke-width": f
      };
      void 0 !== l && 0 < l && (f["stroke-dasharray"] = l);
      isNaN(g) || (f["stroke-opacity"] = g);
      e && (f.stroke = e);
      e = "M" + Math.round(b[0]) + "," + Math.round(c[0]) + " ";
      g = [];

      for (l = 0; l < b.length; l++) {
        isNaN(b[l]) || isNaN(c[l]) ? (e += this.drawSegment(g, p, t), l < b.length - 1 && (e += "L" + b[l + 1] + "," + c[l + 1] + " "), g = []) : g.push({
          x: Number(b[l]),
          y: Number(c[l])
        });
      }

      e += this.drawSegment(g, p, t);
      m && (e += m);
      this.path = a.path(e).attr(f);
      this.node = this.path.node;
      w && this.path.gradient("linearGradient", r, n);
    },
    drawSegment: function drawSegment(a, b, c) {
      var d = "";
      if (2 < a.length) for (var g = 0; g < a.length - 1; g++) {
        var f = [],
            h = a[g - 1],
            k = a[g],
            l = a[g + 1],
            m = a[g + 2];
        0 === g ? (f.push({
          x: k.x,
          y: k.y
        }), f.push({
          x: k.x,
          y: k.y
        }), f.push({
          x: l.x,
          y: l.y
        }), f.push({
          x: m.x,
          y: m.y
        })) : g >= a.length - 2 ? (f.push({
          x: h.x,
          y: h.y
        }), f.push({
          x: k.x,
          y: k.y
        }), f.push({
          x: l.x,
          y: l.y
        }), f.push({
          x: l.x,
          y: l.y
        })) : (f.push({
          x: h.x,
          y: h.y
        }), f.push({
          x: k.x,
          y: k.y
        }), f.push({
          x: l.x,
          y: l.y
        }), f.push({
          x: m.x,
          y: m.y
        }));
        h = [];
        k = Math.round;
        h.push({
          x: k(f[1].x),
          y: k(f[1].y)
        });
        h.push({
          x: k((-f[0].x + b * f[1].x + f[2].x) / b),
          y: k((-f[0].y + c * f[1].y + f[2].y) / c)
        });
        h.push({
          x: k((f[1].x + b * f[2].x - f[3].x) / b),
          y: k((f[1].y + c * f[2].y - f[3].y) / c)
        });
        h.push({
          x: k(f[2].x),
          y: k(f[2].y)
        });
        d += "C" + h[1].x + "," + h[1].y + "," + h[2].x + "," + h[2].y + "," + h[3].x + "," + h[3].y + " ";
      } else 1 < a.length && (d += "L" + a[1].x + "," + a[1].y);
      return d;
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.AmDraw = d.Class({
    construct: function construct(a, b, c, e) {
      d.SVG_NS = "http://www.w3.org/2000/svg";
      d.SVG_XLINK = "http://www.w3.org/1999/xlink";
      d.hasSVG = !!document.createElementNS && !!document.createElementNS(d.SVG_NS, "svg").createSVGRect;
      1 > b && (b = 10);
      1 > c && (c = 10);
      this.div = a;
      this.width = b;
      this.height = c;
      this.rBin = document.createElement("div");
      d.hasSVG ? (d.SVG = !0, b = this.createSvgElement("svg"), a.appendChild(b), this.container = b, this.addDefs(e), this.R = new d.SVGRenderer(this)) : d.isIE && d.VMLRenderer && (d.VML = !0, d.vmlStyleSheet || (document.namespaces.add("amvml", "urn:schemas-microsoft-com:vml"), 31 > document.styleSheets.length ? (b = document.createStyleSheet(), b.addRule(".amvml", "behavior:url(#default#VML); display:inline-block; antialias:true"), d.vmlStyleSheet = b) : document.styleSheets[0].addRule(".amvml", "behavior:url(#default#VML); display:inline-block; antialias:true")), this.container = a, this.R = new d.VMLRenderer(this, e), this.R.disableSelection(a));
    },
    createSvgElement: function createSvgElement(a) {
      return document.createElementNS(d.SVG_NS, a);
    },
    circle: function circle(a, b, c, e) {
      var g = new d.AmDObject("circle", this);
      g.attr({
        r: c,
        cx: a,
        cy: b
      });
      this.addToContainer(g.node, e);
      return g;
    },
    ellipse: function ellipse(a, b, c, e, g) {
      var f = new d.AmDObject("ellipse", this);
      f.attr({
        rx: c,
        ry: e,
        cx: a,
        cy: b
      });
      this.addToContainer(f.node, g);
      return f;
    },
    setSize: function setSize(a, b) {
      0 < a && 0 < b && (this.container.style.width = a + "px", this.container.style.height = b + "px");
    },
    rect: function rect(a, b, c, e, g, f, h) {
      var k = new d.AmDObject("rect", this);
      d.VML && (g = Math.round(100 * g / Math.min(c, e)), c += 2 * f, e += 2 * f, k.bw = f, k.node.style.marginLeft = -f, k.node.style.marginTop = -f);
      1 > c && (c = 1);
      1 > e && (e = 1);
      k.attr({
        x: a,
        y: b,
        width: c,
        height: e,
        rx: g,
        ry: g,
        "stroke-width": f
      });
      this.addToContainer(k.node, h);
      return k;
    },
    image: function image(a, b, c, e, g, f) {
      var h = new d.AmDObject("image", this);
      h.attr({
        x: b,
        y: c,
        width: e,
        height: g
      });
      this.R.path(h, a);
      this.addToContainer(h.node, f);
      return h;
    },
    addToContainer: function addToContainer(a, b) {
      b || (b = this.container);
      b.appendChild(a);
    },
    text: function text(a, b, c) {
      return this.R.text(a, b, c);
    },
    path: function path(a, b, c, e) {
      var g = new d.AmDObject("path", this);
      e || (e = "100,100");
      g.attr({
        cs: e
      });
      c ? g.attr({
        dd: a
      }) : g.attr({
        d: a
      });
      this.addToContainer(g.node, b);
      return g;
    },
    set: function set(a) {
      return this.R.set(a);
    },
    remove: function remove(a) {
      if (a) {
        var b = this.rBin;
        b.appendChild(a);
        b.innerHTML = "";
      }
    },
    renderFix: function renderFix() {
      var a = this.container,
          b = a.style;
      b.top = "0px";
      b.left = "0px";

      try {
        var c = a.getBoundingClientRect(),
            d = c.left - Math.round(c.left),
            g = c.top - Math.round(c.top);
        d && (b.left = d + "px");
        g && (b.top = g + "px");
      } catch (f) {}
    },
    update: function update() {
      this.R.update();
    },
    addDefs: function addDefs(a) {
      if (d.hasSVG) {
        var b = this.createSvgElement("desc"),
            c = this.container;
        c.setAttribute("version", "1.1");
        c.style.position = "absolute";
        this.setSize(this.width, this.height);

        if (a.accessibleTitle) {
          var e = this.createSvgElement("text");
          c.appendChild(e);
          e.innerHTML = a.accessibleTitle;
          e.style.opacity = 0;
        }

        d.rtl && (c.setAttribute("direction", "rtl"), c.style.left = "auto", c.style.right = "0px");
        a && (a.addCodeCredits && b.appendChild(document.createTextNode("JavaScript chart by amCharts " + a.version)), a.accessibleDescription && (b.innerHTML = "", b.appendChild(document.createTextNode(a.accessibleDescription))), c.appendChild(b), a.defs && (b = this.createSvgElement("defs"), c.appendChild(b), d.parseDefs(a.defs, b), this.defs = b));
      }
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.AmDObject = d.Class({
    construct: function construct(a, b) {
      this.D = b;
      this.R = b.R;
      this.node = this.R.create(this, a);
      this.y = this.x = 0;
      this.scale = 1;
    },
    attr: function attr(a) {
      this.R.attr(this, a);
      return this;
    },
    getAttr: function getAttr(a) {
      return this.node.getAttribute(a);
    },
    setAttr: function setAttr(a, b) {
      this.R.setAttr(this, a, b);
      return this;
    },
    clipRect: function clipRect(a, b, c, d) {
      this.R.clipRect(this, a, b, c, d);
    },
    translate: function translate(a, b, c, d) {
      d || (a = Math.round(a), b = Math.round(b));
      this.R.move(this, a, b, c);
      this.x = a;
      this.y = b;
      this.scale = c;
      this.angle && this.rotate(this.angle);
    },
    rotate: function rotate(a, b) {
      this.R.rotate(this, a, b);
      this.angle = a;
    },
    animate: function animate(a, b, c) {
      for (var e in a) {
        if (a.hasOwnProperty(e)) {
          var g = e,
              f = a[e];
          c = d.getEffect(c);
          this.R.animate(this, g, f, b, c);
        }
      }
    },
    push: function push(a) {
      if (a) {
        var b = this.node;
        b.appendChild(a.node);
        var c = a.clipPath;
        c && b.appendChild(c);
        (a = a.grad) && b.appendChild(a);
      }
    },
    text: function text(a) {
      this.R.setText(this, a);
    },
    remove: function remove() {
      this.stop();
      this.R.remove(this);
    },
    clear: function clear() {
      var a = this.node;
      if (a.hasChildNodes()) for (; 1 <= a.childNodes.length;) {
        a.removeChild(a.firstChild);
      }
    },
    hide: function hide() {
      this.setAttr("visibility", "hidden");
    },
    show: function show() {
      this.setAttr("visibility", "visible");
    },
    getBBox: function getBBox() {
      return this.R.getBBox(this);
    },
    toFront: function toFront() {
      var a = this.node;

      if (a) {
        this.prevNextNode = a.nextSibling;
        var b = a.parentNode;
        b && b.appendChild(a);
      }
    },
    toPrevious: function toPrevious() {
      var a = this.node;
      a && this.prevNextNode && (a = a.parentNode) && a.insertBefore(this.prevNextNode, null);
    },
    toBack: function toBack() {
      var a = this.node;

      if (a) {
        this.prevNextNode = a.nextSibling;
        var b = a.parentNode;

        if (b) {
          var c = b.firstChild;
          c && b.insertBefore(a, c);
        }
      }
    },
    mouseover: function mouseover(a) {
      this.R.addListener(this, "mouseover", a);
      return this;
    },
    mouseout: function mouseout(a) {
      this.R.addListener(this, "mouseout", a);
      return this;
    },
    click: function click(a) {
      this.R.addListener(this, "click", a);
      return this;
    },
    dblclick: function dblclick(a) {
      this.R.addListener(this, "dblclick", a);
      return this;
    },
    mousedown: function mousedown(a) {
      this.R.addListener(this, "mousedown", a);
      return this;
    },
    mouseup: function mouseup(a) {
      this.R.addListener(this, "mouseup", a);
      return this;
    },
    touchmove: function touchmove(a) {
      this.R.addListener(this, "touchmove", a);
      return this;
    },
    touchstart: function touchstart(a) {
      this.R.addListener(this, "touchstart", a);
      return this;
    },
    touchend: function touchend(a) {
      this.R.addListener(this, "touchend", a);
      return this;
    },
    keyup: function keyup(a) {
      this.R.addListener(this, "keyup", a);
      return this;
    },
    focus: function focus(a) {
      this.R.addListener(this, "focus", a);
      return this;
    },
    blur: function blur(a) {
      this.R.addListener(this, "blur", a);
      return this;
    },
    contextmenu: function contextmenu(a) {
      this.node.addEventListener ? this.node.addEventListener("contextmenu", a, !0) : this.R.addListener(this, "contextmenu", a);
      return this;
    },
    stop: function stop() {
      d.removeFromArray(this.R.animations, this.an_translate);
      d.removeFromArray(this.R.animations, this.an_y);
      d.removeFromArray(this.R.animations, this.an_x);
    },
    length: function length() {
      return this.node.childNodes.length;
    },
    gradient: function gradient(a, b, c) {
      this.R.gradient(this, a, b, c);
    },
    pattern: function pattern(a, b, c) {
      a && this.R.pattern(this, a, b, c);
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.VMLRenderer = d.Class({
    construct: function construct(a, b) {
      this.chart = b;
      this.D = a;
      this.cNames = {
        circle: "oval",
        ellipse: "oval",
        rect: "roundrect",
        path: "shape"
      };
      this.styleMap = {
        x: "left",
        y: "top",
        width: "width",
        height: "height",
        "font-family": "fontFamily",
        "font-size": "fontSize",
        visibility: "visibility"
      };
    },
    create: function create(a, b) {
      var c;
      if ("group" == b) c = document.createElement("div"), a.type = "div";else if ("text" == b) c = document.createElement("div"), a.type = "text";else if ("image" == b) c = document.createElement("img"), a.type = "image";else {
        a.type = "shape";
        a.shapeType = this.cNames[b];
        c = document.createElement("amvml:" + this.cNames[b]);
        var d = document.createElement("amvml:stroke");
        c.appendChild(d);
        a.stroke = d;
        var g = document.createElement("amvml:fill");
        c.appendChild(g);
        a.fill = g;
        g.className = "amvml";
        d.className = "amvml";
        c.className = "amvml";
      }
      c.style.position = "absolute";
      c.style.top = 0;
      c.style.left = 0;
      return c;
    },
    path: function path(a, b) {
      a.node.setAttribute("src", b);
    },
    setAttr: function setAttr(a, b, c) {
      if (void 0 !== c) {
        var e;
        8 === document.documentMode && (e = !0);
        var g = a.node,
            f = a.type,
            h = g.style;
        "r" == b && (h.width = 2 * c, h.height = 2 * c);
        "oval" == a.shapeType && ("rx" == b && (h.width = 2 * c), "ry" == b && (h.height = 2 * c));
        "roundrect" == a.shapeType && ("width" != b && "height" != b || --c);
        "cursor" == b && (h.cursor = c);
        "cx" == b && (h.left = c - d.removePx(h.width) / 2);
        "cy" == b && (h.top = c - d.removePx(h.height) / 2);
        var k = this.styleMap[b];
        "width" == k && 0 > c && (c = 0);
        void 0 !== k && (h[k] = c);
        "text" == f && ("text-anchor" == b && (a.anchor = c, k = g.clientWidth, "end" == c && (h.marginLeft = -k + "px"), "middle" == c && (h.marginLeft = -(k / 2) + "px", h.textAlign = "center"), "start" == c && (h.marginLeft = "0px")), "fill" == b && (h.color = c), "font-weight" == b && (h.fontWeight = c));
        if (h = a.children) for (k = 0; k < h.length; k++) {
          h[k].setAttr(b, c);
        }

        if ("shape" == f) {
          "cs" == b && (g.style.width = "100px", g.style.height = "100px", g.setAttribute("coordsize", c));
          "d" == b && g.setAttribute("path", this.svgPathToVml(c));
          "dd" == b && g.setAttribute("path", c);
          f = a.stroke;
          a = a.fill;
          "stroke" == b && (e ? f.color = c : f.setAttribute("color", c));
          "stroke-width" == b && (e ? f.weight = c : f.setAttribute("weight", c));
          "stroke-opacity" == b && (e ? f.opacity = c : f.setAttribute("opacity", c));
          "stroke-dasharray" == b && (h = "solid", 0 < c && 3 > c && (h = "dot"), 3 <= c && 6 >= c && (h = "dash"), 6 < c && (h = "longdash"), e ? f.dashstyle = h : f.setAttribute("dashstyle", h));
          if ("fill-opacity" == b || "opacity" == b) 0 === c ? e ? a.on = !1 : a.setAttribute("on", !1) : e ? a.opacity = c : a.setAttribute("opacity", c);
          "fill" == b && (e ? a.color = c : a.setAttribute("color", c));
          "rx" == b && (e ? g.arcSize = c + "%" : g.setAttribute("arcsize", c + "%"));
        }
      }
    },
    attr: function attr(a, b) {
      for (var c in b) {
        b.hasOwnProperty(c) && this.setAttr(a, c, b[c]);
      }
    },
    text: function text(a, b, c) {
      var e = new d.AmDObject("text", this.D),
          g = e.node;
      g.style.whiteSpace = "pre";
      g.innerHTML = a;
      this.D.addToContainer(g, c);
      this.attr(e, b);
      return e;
    },
    getBBox: function getBBox(a) {
      return this.getBox(a.node);
    },
    getBox: function getBox(a) {
      var b = a.offsetLeft,
          c = a.offsetTop,
          d = a.offsetWidth,
          g = a.offsetHeight,
          f;

      if (a.hasChildNodes()) {
        var h, k, l;

        for (l = 0; l < a.childNodes.length; l++) {
          f = this.getBox(a.childNodes[l]);
          var m = f.x;
          isNaN(m) || (isNaN(h) ? h = m : m < h && (h = m));
          var n = f.y;
          isNaN(n) || (isNaN(k) ? k = n : n < k && (k = n));
          m = f.width + m;
          isNaN(m) || (d = Math.max(d, m));
          f = f.height + n;
          isNaN(f) || (g = Math.max(g, f));
        }

        0 > h && (b += h);
        0 > k && (c += k);
      }

      return {
        x: b,
        y: c,
        width: d,
        height: g
      };
    },
    setText: function setText(a, b) {
      var c = a.node;
      c && (c.innerHTML = b);
      this.setAttr(a, "text-anchor", a.anchor);
    },
    addListener: function addListener(a, b, c) {
      a.node["on" + b] = c;
    },
    move: function move(a, b, c) {
      var e = a.node,
          g = e.style;
      "text" == a.type && (c -= d.removePx(g.fontSize) / 2 - 1);
      "oval" == a.shapeType && (b -= d.removePx(g.width) / 2, c -= d.removePx(g.height) / 2);
      a = a.bw;
      isNaN(a) || (b -= a, c -= a);
      isNaN(b) || isNaN(c) || (e.style.left = b + "px", e.style.top = c + "px");
    },
    svgPathToVml: function svgPathToVml(a) {
      var b = a.split(" ");
      a = "";
      var c,
          d = Math.round,
          g;

      for (g = 0; g < b.length; g++) {
        var f = b[g],
            h = f.substring(0, 1),
            f = f.substring(1),
            k = f.split(","),
            l = d(k[0]) + "," + d(k[1]);
        "M" == h && (a += " m " + l);
        "L" == h && (a += " l " + l);
        "Z" == h && (a += " x e");

        if ("Q" == h) {
          var m = c.length,
              n = c[m - 1],
              q = k[0],
              p = k[1],
              l = k[2],
              t = k[3];
          c = d(c[m - 2] / 3 + 2 / 3 * q);
          n = d(n / 3 + 2 / 3 * p);
          q = d(2 / 3 * q + l / 3);
          p = d(2 / 3 * p + t / 3);
          a += " c " + c + "," + n + "," + q + "," + p + "," + l + "," + t;
        }

        "C" == h && (a += " c " + k[0] + "," + k[1] + "," + k[2] + "," + k[3] + "," + k[4] + "," + k[5]);
        "A" == h && (a += " wa " + f);
        "B" == h && (a += " at " + f);
        c = k;
      }

      return a;
    },
    animate: function animate(a, b, c, d, g) {
      var f = a.node,
          h = this.chart;
      a.animationFinished = !1;

      if ("translate" == b) {
        b = c.split(",");
        c = b[1];
        var k = f.offsetTop;
        h.animate(a, "left", f.offsetLeft, b[0], d, g, "px");
        h.animate(a, "top", k, c, d, g, "px");
      }
    },
    clipRect: function clipRect(a, b, c, d, g) {
      a = a.node;
      0 === b && 0 === c ? (a.style.width = d + "px", a.style.height = g + "px", a.style.overflow = "hidden") : a.style.clip = "rect(" + c + "px " + (b + d) + "px " + (c + g) + "px " + b + "px)";
    },
    rotate: function rotate(a, b, c) {
      if (0 !== Number(b)) {
        var e = a.node;
        a = e.style;
        c || (c = this.getBGColor(e.parentNode));
        a.backgroundColor = c;
        a.paddingLeft = 1;
        c = b * Math.PI / 180;
        var g = Math.cos(c),
            f = Math.sin(c),
            h = d.removePx(a.left),
            k = d.removePx(a.top),
            l = e.offsetWidth,
            e = e.offsetHeight;
        b /= Math.abs(b);
        a.left = h + l / 2 - l / 2 * Math.cos(c) - b * e / 2 * Math.sin(c) + 3;
        a.top = k - b * l / 2 * Math.sin(c) + b * e / 2 * Math.sin(c);
        a.cssText = a.cssText + "; filter:progid:DXImageTransform.Microsoft.Matrix(M11='" + g + "', M12='" + -f + "', M21='" + f + "', M22='" + g + "', sizingmethod='auto expand');";
      }
    },
    getBGColor: function getBGColor(a) {
      var b = "#FFFFFF";

      if (a.style) {
        var c = a.style.backgroundColor;
        "" !== c ? b = c : a.parentNode && (b = this.getBGColor(a.parentNode));
      }

      return b;
    },
    set: function set(a) {
      var b = new d.AmDObject("group", this.D);
      this.D.container.appendChild(b.node);

      if (a) {
        var c;

        for (c = 0; c < a.length; c++) {
          b.push(a[c]);
        }
      }

      return b;
    },
    gradient: function gradient(a, b, c, d) {
      var g = "";
      "radialGradient" == b && (b = "gradientradial", c.reverse());
      "linearGradient" == b && (b = "gradient");
      var f;

      for (f = 0; f < c.length; f++) {
        g += Math.round(100 * f / (c.length - 1)) + "% " + c[f], f < c.length - 1 && (g += ",");
      }

      a = a.fill;
      90 == d ? d = 0 : 270 == d ? d = 180 : 180 == d ? d = 90 : 0 === d && (d = 270);
      8 === document.documentMode ? (a.type = b, a.angle = d) : (a.setAttribute("type", b), a.setAttribute("angle", d));
      g && (a.colors.value = g);
    },
    remove: function remove(a) {
      a.clipPath && this.D.remove(a.clipPath);
      this.D.remove(a.node);
    },
    disableSelection: function disableSelection(a) {
      a.onselectstart = function () {
        return !1;
      };

      a.style.cursor = "default";
    },
    pattern: function pattern(a, b, c, e) {
      c = a.node;
      a = a.fill;
      var g = "none";
      b.color && (g = b.color);
      c.fillColor = g;
      b = b.url;
      d.isAbsolute(b) || (b = e + b);
      8 === document.documentMode ? (a.type = "tile", a.src = b) : (a.setAttribute("type", "tile"), a.setAttribute("src", b));
    },
    update: function update() {}
  });
})();

(function () {
  var d = window.AmCharts;
  d.SVGRenderer = d.Class({
    construct: function construct(a) {
      this.D = a;
      this.animations = [];
    },
    create: function create(a, b) {
      return document.createElementNS(d.SVG_NS, b);
    },
    attr: function attr(a, b) {
      for (var c in b) {
        b.hasOwnProperty(c) && this.setAttr(a, c, b[c]);
      }
    },
    setAttr: function setAttr(a, b, c) {
      void 0 !== c && a.node.setAttribute(b, c);
    },
    animate: function animate(a, b, c, e, g) {
      a.animationFinished = !1;
      var f = a.node;
      a["an_" + b] && d.removeFromArray(this.animations, a["an_" + b]);
      "translate" == b ? (f = (f = f.getAttribute("transform")) ? String(f).substring(10, f.length - 1) : "0,0", f = f.split(", ").join(" "), f = f.split(" ").join(","), 0 === f && (f = "0,0")) : f = Number(f.getAttribute(b));
      c = {
        obj: a,
        frame: 0,
        attribute: b,
        from: f,
        to: c,
        time: e,
        effect: g
      };
      this.animations.push(c);
      a["an_" + b] = c;
    },
    update: function update() {
      var a,
          b = this.animations;

      for (a = b.length - 1; 0 <= a; a--) {
        var c = b[a],
            e = c.time * d.updateRate,
            g = c.frame + 1,
            f = c.obj,
            h = c.attribute,
            k,
            l,
            m;

        if (g <= e) {
          c.frame++;

          if ("translate" == h) {
            k = c.from.split(",");
            h = Number(k[0]);
            k = Number(k[1]);
            isNaN(k) && (k = 0);
            l = c.to.split(",");
            m = Number(l[0]);
            l = Number(l[1]);
            m = 0 === m - h ? m : Math.round(d[c.effect](0, g, h, m - h, e));
            c = 0 === l - k ? l : Math.round(d[c.effect](0, g, k, l - k, e));
            h = "transform";
            if (isNaN(m) || isNaN(c)) continue;
            c = "translate(" + m + "," + c + ")";
          } else l = Number(c.from), k = Number(c.to), m = k - l, c = d[c.effect](0, g, l, m, e), isNaN(c) && (c = k), 0 === m && this.animations.splice(a, 1);

          this.setAttr(f, h, c);
        } else "translate" == h ? (l = c.to.split(","), m = Number(l[0]), l = Number(l[1]), f.translate(m, l)) : (k = Number(c.to), this.setAttr(f, h, k)), f.animationFinished = !0, this.animations.splice(a, 1);
      }
    },
    getBBox: function getBBox(a) {
      if (a = a.node) try {
        return a.getBBox();
      } catch (b) {}
      return {
        width: 0,
        height: 0,
        x: 0,
        y: 0
      };
    },
    path: function path(a, b) {
      a.node.setAttributeNS(d.SVG_XLINK, "xlink:href", b);
    },
    clipRect: function clipRect(a, b, c, e, g) {
      var f = a.node,
          h = a.clipPath;
      h && this.D.remove(h);
      var k = f.parentNode;
      k && (f = document.createElementNS(d.SVG_NS, "clipPath"), h = d.getUniqueId(), f.setAttribute("id", h), this.D.rect(b, c, e, g, 0, 0, f), k.appendChild(f), b = "#", d.baseHref && !d.isIE && (b = this.removeTarget(window.location.href) + b), this.setAttr(a, "clip-path", "url(" + b + h + ")"), this.clipPathC++, a.clipPath = f);
    },
    text: function text(a, b, c) {
      var e = new d.AmDObject("text", this.D);
      a = String(a).split("\n");
      var g = d.removePx(b["font-size"]),
          f;

      for (f = 0; f < a.length; f++) {
        var h = this.create(null, "tspan");
        h.appendChild(document.createTextNode(a[f]));
        h.setAttribute("y", (g + 2) * f + Math.round(g / 2));
        h.setAttribute("x", 0);
        e.node.appendChild(h);
      }

      e.node.setAttribute("y", Math.round(g / 2));
      this.attr(e, b);
      this.D.addToContainer(e.node, c);
      return e;
    },
    setText: function setText(a, b) {
      var c = a.node;
      c && (c.removeChild(c.firstChild), c.appendChild(document.createTextNode(b)));
    },
    move: function move(a, b, c, d) {
      isNaN(b) && (b = 0);
      isNaN(c) && (c = 0);
      b = "translate(" + b + "," + c + ")";
      d && (b = b + " scale(" + d + ")");
      this.setAttr(a, "transform", b);
    },
    rotate: function rotate(a, b) {
      var c = a.node.getAttribute("transform"),
          d = "rotate(" + b + ")";
      c && (d = c + " " + d);
      this.setAttr(a, "transform", d);
    },
    set: function set(a) {
      var b = new d.AmDObject("g", this.D);
      this.D.container.appendChild(b.node);

      if (a) {
        var c;

        for (c = 0; c < a.length; c++) {
          b.push(a[c]);
        }
      }

      return b;
    },
    addListener: function addListener(a, b, c) {
      a.node["on" + b] = c;
    },
    gradient: function gradient(a, b, c, e) {
      var g = a.node,
          f = a.grad;
      f && this.D.remove(f);
      b = document.createElementNS(d.SVG_NS, b);
      f = d.getUniqueId();
      b.setAttribute("id", f);

      if (!isNaN(e)) {
        var h = 0,
            k = 0,
            l = 0,
            m = 0;
        90 == e ? l = 100 : 270 == e ? m = 100 : 180 == e ? h = 100 : 0 === e && (k = 100);
        b.setAttribute("x1", h + "%");
        b.setAttribute("x2", k + "%");
        b.setAttribute("y1", l + "%");
        b.setAttribute("y2", m + "%");
      }

      for (e = 0; e < c.length; e++) {
        h = document.createElementNS(d.SVG_NS, "stop"), k = 100 * e / (c.length - 1), 0 === e && (k = 0), h.setAttribute("offset", k + "%"), h.setAttribute("stop-color", c[e]), b.appendChild(h);
      }

      g.parentNode.appendChild(b);
      c = "#";
      d.baseHref && !d.isIE && (c = this.removeTarget(window.location.href) + c);
      g.setAttribute("fill", "url(" + c + f + ")");
      a.grad = b;
    },
    removeTarget: function removeTarget(a) {
      return a.split("#")[0];
    },
    pattern: function pattern(a, b, c, e) {
      var g = a.node;
      isNaN(c) && (c = 1);
      var f = a.patternNode;
      f && this.D.remove(f);
      var f = document.createElementNS(d.SVG_NS, "pattern"),
          h = d.getUniqueId(),
          k = b;
      b.url && (k = b.url);
      d.isAbsolute(k) || -1 != k.indexOf("data:image") || (k = e + k);
      e = Number(b.width);
      isNaN(e) && (e = 4);
      var l = Number(b.height);
      isNaN(l) && (l = 4);
      e /= c;
      l /= c;
      c = b.x;
      isNaN(c) && (c = 0);
      var m = -Math.random() * Number(b.randomX);
      isNaN(m) || (c = m);
      m = b.y;
      isNaN(m) && (m = 0);
      var n = -Math.random() * Number(b.randomY);
      isNaN(n) || (m = n);
      f.setAttribute("id", h);
      f.setAttribute("width", e);
      f.setAttribute("height", l);
      f.setAttribute("patternUnits", "userSpaceOnUse");
      f.setAttribute("xlink:href", k);
      b.color && (n = document.createElementNS(d.SVG_NS, "rect"), n.setAttributeNS(null, "height", e), n.setAttributeNS(null, "width", l), n.setAttributeNS(null, "fill", b.color), f.appendChild(n));
      this.D.image(k, 0, 0, e, l, f).translate(c, m);
      k = "#";
      d.baseHref && !d.isIE && (k = this.removeTarget(window.location.href) + k);
      g.setAttribute("fill", "url(" + k + h + ")");
      a.patternNode = f;
      g.parentNode.appendChild(f);
    },
    remove: function remove(a) {
      a.clipPath && this.D.remove(a.clipPath);
      a.grad && this.D.remove(a.grad);
      a.patternNode && this.D.remove(a.patternNode);
      this.D.remove(a.node);
    }
  });
})();

(function () {
  var d = window.AmCharts;
  d.AmLegend = d.Class({
    construct: function construct(a) {
      this.enabled = !0;
      this.cname = "AmLegend";
      this.createEvents("rollOverMarker", "rollOverItem", "rollOutMarker", "rollOutItem", "showItem", "hideItem", "clickMarker", "clickLabel");
      this.position = "bottom";
      this.borderColor = this.color = "#000000";
      this.borderAlpha = 0;
      this.markerLabelGap = 5;
      this.verticalGap = 10;
      this.align = "left";
      this.horizontalGap = 0;
      this.spacing = 10;
      this.markerDisabledColor = "#AAB3B3";
      this.markerType = "square";
      this.markerSize = 16;
      this.markerBorderThickness = this.markerBorderAlpha = 1;
      this.marginBottom = this.marginTop = 0;
      this.marginLeft = this.marginRight = 20;
      this.autoMargins = !0;
      this.valueWidth = 50;
      this.switchable = !0;
      this.switchType = "x";
      this.switchColor = "#FFFFFF";
      this.rollOverColor = "#CC0000";
      this.reversedOrder = !1;
      this.labelText = "[[title]]";
      this.valueText = "[[value]]";
      this.accessibleLabel = "[[title]]";
      this.useMarkerColorForLabels = !1;
      this.rollOverGraphAlpha = 1;
      this.textClickEnabled = !1;
      this.equalWidths = !0;
      this.backgroundColor = "#FFFFFF";
      this.backgroundAlpha = 0;
      this.useGraphSettings = !1;
      this.showEntries = !0;
      this.labelDx = 0;
      d.applyTheme(this, a, this.cname);
    },
    setData: function setData(a) {
      this.legendData = a;
      this.invalidateSize();
    },
    invalidateSize: function invalidateSize() {
      this.destroy();
      this.entries = [];
      this.valueLabels = [];
      var a = this.legendData;
      this.enabled && (d.ifArray(a) || d.ifArray(this.data)) && this.drawLegend();
    },
    drawLegend: function drawLegend() {
      var a = this.chart,
          b = this.position,
          c = this.width,
          e = a.divRealWidth,
          g = a.divRealHeight,
          f = this.div,
          h = this.legendData;
      this.data && (h = this.combineLegend ? this.legendData.concat(this.data) : this.data);
      isNaN(this.fontSize) && (this.fontSize = a.fontSize);
      this.maxColumnsReal = this.maxColumns;
      if ("right" == b || "left" == b) this.maxColumnsReal = 1, this.autoMargins && (this.marginLeft = this.marginRight = 10);else if (this.autoMargins) {
        this.marginRight = a.marginRight;
        this.marginLeft = a.marginLeft;
        var k = a.autoMarginOffset;
        "bottom" == b ? (this.marginBottom = k, this.marginTop = 0) : (this.marginTop = k, this.marginBottom = 0);
      }
      c = void 0 !== c ? d.toCoordinate(c, e) : "right" != b && "left" != b ? a.realWidth : 0 < this.ieW ? this.ieW : a.realWidth;
      "outside" == b ? (c = f.offsetWidth, g = f.offsetHeight, f.clientHeight && (c = f.clientWidth, g = f.clientHeight)) : (isNaN(c) || (f.style.width = c + "px"), f.className = "amChartsLegend " + a.classNamePrefix + "-legend-div");
      this.divWidth = c;
      (b = this.container) ? (b.container.innerHTML = "", f.appendChild(b.container), b.width = c, b.height = g, b.setSize(c, g), b.addDefs(a)) : b = new d.AmDraw(f, c, g, a);
      this.container = b;
      this.lx = 0;
      this.ly = 8;
      g = this.markerSize;
      g > this.fontSize && (this.ly = g / 2 - 1);
      0 < g && (this.lx += g + this.markerLabelGap);
      this.titleWidth = 0;
      if (g = this.title) g = d.text(this.container, g, this.color, a.fontFamily, this.fontSize, "start", !0), d.setCN(a, g, "legend-title"), g.translate(this.marginLeft, this.marginTop + this.verticalGap + this.ly + 1), a = g.getBBox(), this.titleWidth = a.width + 15, this.titleHeight = a.height + 6;
      this.index = this.maxLabelWidth = 0;

      if (this.showEntries) {
        for (a = 0; a < h.length; a++) {
          this.createEntry(h[a]);
        }

        for (a = this.index = 0; a < h.length; a++) {
          this.createValue(h[a]);
        }
      }

      this.arrangeEntries();
      this.updateValues();
    },
    arrangeEntries: function arrangeEntries() {
      var a = this.position,
          b = this.marginLeft + this.titleWidth,
          c = this.marginRight,
          e = this.marginTop,
          g = this.marginBottom,
          f = this.horizontalGap,
          h = this.div,
          k = this.divWidth,
          l = this.maxColumnsReal,
          m = this.verticalGap,
          n = this.spacing,
          q = k - c - b,
          p = 0,
          t = 0,
          r = this.container;
      this.set && this.set.remove();
      var w = r.set();
      this.set = w;
      var z = r.set();
      w.push(z);
      var x = this.entries,
          u,
          A;

      for (A = 0; A < x.length; A++) {
        u = x[A].getBBox();
        var y = u.width;
        y > p && (p = y);
        u = u.height;
        u > t && (t = u);
      }

      var y = t = 0,
          B = f,
          D = 0,
          C = 0;

      for (A = 0; A < x.length; A++) {
        var I = x[A];
        this.reversedOrder && (I = x[x.length - A - 1]);
        u = I.getBBox();
        var H;
        this.equalWidths ? H = y * (p + n + this.markerLabelGap) : (H = B, B = B + u.width + f + n);
        H + u.width > q && 0 < A && 0 !== y && (t++, H = y = 0, B = H + u.width + f + n, D = D + C + m, C = 0);
        u.height > C && (C = u.height);
        I.translate(H, D);
        y++;
        !isNaN(l) && y >= l && (y = 0, t++, D = D + C + m, B = f, C = 0);
        z.push(I);
      }

      u = z.getBBox();
      l = u.height + 2 * m - 1;
      "left" == a || "right" == a ? (n = u.width + 2 * f, k = n + b + c, h.style.width = k + "px", this.ieW = k) : n = k - b - c - 1;
      c = d.polygon(this.container, [0, n, n, 0], [0, 0, l, l], this.backgroundColor, this.backgroundAlpha, 1, this.borderColor, this.borderAlpha);
      d.setCN(this.chart, c, "legend-bg");
      w.push(c);
      w.translate(b, e);
      c.toBack();
      b = f;
      if ("top" == a || "bottom" == a || "absolute" == a || "outside" == a) "center" == this.align ? b = f + (n - u.width) / 2 : "right" == this.align && (b = f + n - u.width);
      z.translate(b, m + 1);
      this.titleHeight > l && (l = this.titleHeight);
      e = l + e + g + 1;
      0 > e && (e = 0);
      "absolute" != a && "outside" != a && e > this.chart.divRealHeight && (h.style.top = "0px");
      h.style.height = Math.round(e) + "px";
      r.setSize(this.divWidth, e);
    },
    createEntry: function createEntry(a) {
      if (!1 !== a.visibleInLegend && !a.hideFromLegend) {
        var b = this,
            c = b.chart,
            e = b.useGraphSettings,
            g = a.markerType;
        g && (e = !1);
        a.legendEntryWidth = b.markerSize;
        g || (g = b.markerType);
        var f = a.color,
            h = a.alpha;
        a.legendKeyColor && (f = a.legendKeyColor());
        a.legendKeyAlpha && (h = a.legendKeyAlpha());
        var k;
        !0 === a.hidden && (k = f = b.markerDisabledColor);
        var l = a.pattern,
            m,
            n = a.customMarker;
        n || (n = b.customMarker);
        var q = b.container,
            p = b.markerSize,
            t = 0,
            r = 0,
            w = p / 2;

        if (e) {
          e = a.type;
          b.switchType = void 0;
          if ("line" == e || "step" == e || "smoothedLine" == e || "ohlc" == e) m = q.set(), a.hidden || (f = a.lineColorR, k = a.bulletBorderColorR), t = d.line(q, [0, 2 * p], [p / 2, p / 2], f, a.lineAlpha, a.lineThickness, a.dashLength), d.setCN(c, t, "graph-stroke"), m.push(t), a.bullet && (a.hidden || (f = a.bulletColorR), t = d.bullet(q, a.bullet, a.bulletSize, f, a.bulletAlpha, a.bulletBorderThickness, k, a.bulletBorderAlpha)) && (d.setCN(c, t, "graph-bullet"), t.translate(p + 1, p / 2), m.push(t)), w = 0, t = p, r = p / 3;else {
            a.getGradRotation && (m = a.getGradRotation(), 0 === m && (m = 180));
            t = a.fillColorsR;
            !0 === a.hidden && (t = f);
            if (m = b.createMarker("rectangle", t, a.fillAlphas, a.lineThickness, f, a.lineAlpha, m, l, a.dashLength)) w = p, m.translate(w, p / 2);
            t = p;
          }
          d.setCN(c, m, "graph-" + e);
          d.setCN(c, m, "graph-" + a.id);
        } else if (n) m = q.image(n, 0, 0, p, p);else {
          var z;
          isNaN(b.gradientRotation) || (z = 180 + b.gradientRotation);
          (m = b.createMarker(g, f, h, void 0, void 0, void 0, z, l)) && m.translate(p / 2, p / 2);
        }

        d.setCN(c, m, "legend-marker");
        b.addListeners(m, a);
        q = q.set([m]);
        b.switchable && a.switchable && q.setAttr("cursor", "pointer");
        void 0 !== a.id && d.setCN(c, q, "legend-item-" + a.id);
        d.setCN(c, q, a.className, !0);
        k = b.switchType;
        var x;
        k && "none" != k && 0 < p && ("x" == k ? (x = b.createX(), x.translate(p / 2, p / 2)) : x = b.createV(), x.dItem = a, !0 !== a.hidden ? "x" == k ? x.hide() : x.show() : "x" != k && x.hide(), b.switchable || x.hide(), b.addListeners(x, a), a.legendSwitch = x, q.push(x), d.setCN(c, x, "legend-switch"));
        k = b.color;
        a.showBalloon && b.textClickEnabled && void 0 !== b.selectedColor && (k = b.selectedColor);
        b.useMarkerColorForLabels && !l && (k = f);
        !0 === a.hidden && (k = b.markerDisabledColor);
        f = d.massReplace(b.labelText, {
          "[[title]]": a.title
        });
        void 0 !== b.tabIndex && (q.setAttr("tabindex", b.tabIndex), q.setAttr("role", "menuitem"), q.keyup(function (c) {
          13 == c.keyCode && b.clickMarker(a, c);
        }));
        c.accessible && b.accessibleLabel && (l = d.massReplace(b.accessibleLabel, {
          "[[title]]": a.title
        }), c.makeAccessible(q, l));
        l = b.fontSize;
        m && (p <= l && (p = p / 2 + b.ly - l / 2 + (l + 2 - p) / 2 - r, m.translate(w, p), x && x.translate(x.x, p)), a.legendEntryWidth = m.getBBox().width);
        var u;
        f && (f = d.fixBrakes(f), a.legendTextReal = f, u = b.labelWidth, u = isNaN(u) ? d.text(b.container, f, k, c.fontFamily, l, "start") : d.wrappedText(b.container, f, k, c.fontFamily, l, "start", !1, u, 0), d.setCN(c, u, "legend-label"), u.translate(b.lx + t, b.ly), q.push(u), b.labelDx = t, c = u.getBBox().width, b.maxLabelWidth < c && (b.maxLabelWidth = c));
        b.entries[b.index] = q;
        a.legendEntry = b.entries[b.index];
        a.legendMarker = m;
        a.legendLabel = u;
        b.index++;
      }
    },
    addListeners: function addListeners(a, b) {
      var c = this;
      a && a.mouseover(function (a) {
        c.rollOverMarker(b, a);
      }).mouseout(function (a) {
        c.rollOutMarker(b, a);
      }).click(function (a) {
        c.clickMarker(b, a);
      });
    },
    rollOverMarker: function rollOverMarker(a, b) {
      this.switchable && this.dispatch("rollOverMarker", a, b);
      this.dispatch("rollOverItem", a, b);
    },
    rollOutMarker: function rollOutMarker(a, b) {
      this.switchable && this.dispatch("rollOutMarker", a, b);
      this.dispatch("rollOutItem", a, b);
    },
    clickMarker: function clickMarker(a, b) {
      this.switchable && (!0 === a.hidden ? this.dispatch("showItem", a, b) : this.dispatch("hideItem", a, b));
      this.dispatch("clickMarker", a, b);
    },
    rollOverLabel: function rollOverLabel(a, b) {
      a.hidden || this.textClickEnabled && a.legendLabel && a.legendLabel.attr({
        fill: this.rollOverColor
      });
      this.dispatch("rollOverItem", a, b);
    },
    rollOutLabel: function rollOutLabel(a, b) {
      if (!a.hidden && this.textClickEnabled && a.legendLabel) {
        var c = this.color;
        void 0 !== this.selectedColor && a.showBalloon && (c = this.selectedColor);
        this.useMarkerColorForLabels && (c = a.lineColor, void 0 === c && (c = a.color));
        a.legendLabel.attr({
          fill: c
        });
      }

      this.dispatch("rollOutItem", a, b);
    },
    clickLabel: function clickLabel(a, b) {
      this.textClickEnabled ? a.hidden || this.dispatch("clickLabel", a, b) : this.switchable && (!0 === a.hidden ? this.dispatch("showItem", a, b) : this.dispatch("hideItem", a, b));
    },
    dispatch: function dispatch(a, b, c) {
      a = {
        type: a,
        dataItem: b,
        target: this,
        event: c,
        chart: this.chart
      };
      this.chart && this.chart.handleLegendEvent(a);
      this.fire(a);
    },
    createValue: function createValue(a) {
      var b = this,
          c = b.fontSize,
          e = b.chart;

      if (!1 !== a.visibleInLegend && !a.hideFromLegend) {
        var g = b.maxLabelWidth,
            f = 0;
        b.forceWidth && (g = b.labelWidth);
        b.equalWidths || (b.valueAlign = "left");
        f = {
          x: 0,
          y: 0,
          width: 0,
          height: 0
        };
        a.legendLabel && (f = a.legendLabel.getBBox());
        "left" == b.valueAlign && (g = f.width);
        var f = f.height,
            h = g,
            k = b.markerSize;
        k < c + 7 && (k = c + 7, d.VML && (k += 3));

        if (b.valueText && 0 < b.valueWidth) {
          var l = b.color;
          b.useMarkerColorForValues && (l = a.color, a.legendKeyColor && (l = a.legendKeyColor()));
          !0 === a.hidden && (l = b.markerDisabledColor);
          var m = b.valueText,
              g = g + b.lx + b.labelDx + b.markerLabelGap + b.valueWidth,
              n = "end";
          "left" == b.valueAlign && (g -= b.valueWidth, n = "start");
          c = d.text(b.container, m, l, b.chart.fontFamily, c, n);
          d.setCN(e, c, "legend-value");
          c.translate(g, b.ly);
          b.entries[b.index].push(c);
          h += b.valueWidth + 2 * b.markerLabelGap;
          c.dItem = a;
          b.valueLabels.push(c);
          k < f + 5 && (k = f + 5);
        }

        b.index++;
        e = b.container.rect(a.legendEntryWidth, 0, h, k, 0, 0).attr({
          stroke: "none",
          fill: "#fff",
          "fill-opacity": .005
        });
        e.dItem = a;
        b.entries[b.index - 1].push(e);
        e.mouseover(function (c) {
          b.rollOverLabel(a, c);
        }).mouseout(function (c) {
          b.rollOutLabel(a, c);
        }).click(function (c) {
          b.clickLabel(a, c);
        });
      }
    },
    createV: function createV() {
      var a = this.markerSize;
      return d.polygon(this.container, [a / 5, a / 2, a - a / 5, a / 2], [a / 3, a - a / 5, a / 5, a / 1.7], this.switchColor);
    },
    createX: function createX() {
      var a = (this.markerSize - 4) / 2,
          b = {
        stroke: this.switchColor,
        "stroke-width": 3
      },
          c = this.container,
          e = d.line(c, [-a, a], [-a, a]).attr(b),
          a = d.line(c, [-a, a], [a, -a]).attr(b);
      return this.container.set([e, a]);
    },
    createMarker: function createMarker(a, b, c, e, g, f, h, k, l) {
      var m = this.markerSize,
          n = this.container;
      g || (g = this.markerBorderColor);
      g || (g = b);
      isNaN(e) && (e = this.markerBorderThickness);
      isNaN(f) && (f = this.markerBorderAlpha);
      return d.bullet(n, a, m, b, c, e, g, f, m, h, k, this.chart.path, l);
    },
    validateNow: function validateNow() {
      this.invalidateSize();
    },
    updateValues: function updateValues() {
      var a = this.valueLabels,
          b = this.chart,
          c,
          e = this.data;
      if (a) for (c = 0; c < a.length; c++) {
        var g = a[c],
            f = g.dItem;
        f.periodDataItem = void 0;
        f.periodPercentDataItem = void 0;
        var h = " ";
        if (e) f.value ? g.text(f.value) : g.text("");else {
          var k = null;

          if (void 0 !== f.type) {
            var k = f.currentDataItem,
                l = this.periodValueText;
            f.legendPeriodValueText && (l = f.legendPeriodValueText);
            f.legendPeriodValueTextR && (l = f.legendPeriodValueTextR);
            k ? (h = this.valueText, f.legendValueText && (h = f.legendValueText), f.legendValueTextR && (h = f.legendValueTextR), h = b.formatString(h, k)) : l && b.formatPeriodString && (l = d.massReplace(l, {
              "[[title]]": f.title
            }), h = b.formatPeriodString(l, f));
          } else h = b.formatString(this.valueText, f);

          l = f;
          k && (l = k);
          var m = this.valueFunction;
          m && (h = m(l, h, b.periodDataItem));
          var n;
          this.useMarkerColorForLabels && !k && f.lastDataItem && (k = f.lastDataItem);
          k ? n = b.getBalloonColor(f, k) : f.legendKeyColor && (n = f.legendKeyColor());
          f.legendColorFunction && (n = f.legendColorFunction(l, h, f.periodDataItem, f.periodPercentDataItem));
          g.text(h);

          if (!f.pattern && (this.useMarkerColorForValues && g.setAttr("fill", n), this.useMarkerColorForLabels)) {
            if (g = f.legendMarker) g.setAttr("fill", n), g.setAttr("stroke", n);
            (g = f.legendLabel) && (f.hidden ? g.setAttr("fill", this.markerDisabledColor) : g.setAttr("fill", n));
          }
        }
      }
    },
    renderFix: function renderFix() {
      if (!d.VML && this.enabled) {
        var a = this.container;
        a && a.renderFix();
      }
    },
    destroy: function destroy() {
      this.div.innerHTML = "";
      d.remove(this.set);
    }
  });
})();

(function () {
  var d = window.AmCharts;

  d.formatMilliseconds = function (a, b) {
    if (-1 != a.indexOf("fff")) {
      var c = b.getMilliseconds(),
          d = String(c);
      10 > c && (d = "00" + c);
      10 <= c && 100 > c && (d = "0" + c);
      a = a.replace(/fff/g, d);
    }

    return a;
  };

  d.extractPeriod = function (a) {
    var b = d.stripNumbers(a),
        c = 1;
    b != a && (c = Number(a.slice(0, a.indexOf(b))));
    return {
      period: b,
      count: c
    };
  };

  d.getDate = function (a, b, c) {
    return a instanceof Date ? d.newDate(a, c) : b && isNaN(a) ? d.stringToDate(a, b) : new Date(a);
  };

  d.daysInMonth = function (a) {
    return new Date(a.getYear(), a.getMonth() + 1, 0).getDate();
  };

  d.newDate = function (a, b) {
    return b && -1 == b.indexOf("fff") ? new Date(a) : new Date(a.getFullYear(), a.getMonth(), a.getDate(), a.getHours(), a.getMinutes(), a.getSeconds(), a.getMilliseconds());
  };

  d.resetDateToMin = function (a, b, c, e) {
    void 0 === e && (e = 1);
    var g, f, h, k, l, m, n;
    d.useUTC ? (g = a.getUTCFullYear(), f = a.getUTCMonth(), h = a.getUTCDate(), k = a.getUTCHours(), l = a.getUTCMinutes(), m = a.getUTCSeconds(), n = a.getUTCMilliseconds(), a = a.getUTCDay()) : (g = a.getFullYear(), f = a.getMonth(), h = a.getDate(), k = a.getHours(), l = a.getMinutes(), m = a.getSeconds(), n = a.getMilliseconds(), a = a.getDay());

    switch (b) {
      case "YYYY":
        g = Math.floor(g / c) * c;
        f = 0;
        h = 1;
        n = m = l = k = 0;
        break;

      case "MM":
        f = Math.floor(f / c) * c;
        h = 1;
        n = m = l = k = 0;
        break;

      case "WW":
        h = a >= e ? h - a + e : h - (7 + a) + e;
        n = m = l = k = 0;
        break;

      case "DD":
        n = m = l = k = 0;
        break;

      case "hh":
        k = Math.floor(k / c) * c;
        n = m = l = 0;
        break;

      case "mm":
        l = Math.floor(l / c) * c;
        n = m = 0;
        break;

      case "ss":
        m = Math.floor(m / c) * c;
        n = 0;
        break;

      case "fff":
        n = Math.floor(n / c) * c;
    }

    d.useUTC ? (a = new Date(), a.setUTCFullYear(g, f, h), a.setUTCHours(k, l, m, n)) : a = new Date(g, f, h, k, l, m, n);
    return a;
  };

  d.getPeriodDuration = function (a, b) {
    void 0 === b && (b = 1);
    var c;

    switch (a) {
      case "YYYY":
        c = 316224E5;
        break;

      case "MM":
        c = 26784E5;
        break;

      case "WW":
        c = 6048E5;
        break;

      case "DD":
        c = 864E5;
        break;

      case "hh":
        c = 36E5;
        break;

      case "mm":
        c = 6E4;
        break;

      case "ss":
        c = 1E3;
        break;

      case "fff":
        c = 1;
    }

    return c * b;
  };

  d.intervals = {
    s: {
      nextInterval: "ss",
      contains: 1E3
    },
    ss: {
      nextInterval: "mm",
      contains: 60,
      count: 0
    },
    mm: {
      nextInterval: "hh",
      contains: 60,
      count: 1
    },
    hh: {
      nextInterval: "DD",
      contains: 24,
      count: 2
    },
    DD: {
      nextInterval: "",
      contains: Infinity,
      count: 3
    }
  };

  d.getMaxInterval = function (a, b) {
    var c = d.intervals;
    return a >= c[b].contains ? (a = Math.round(a / c[b].contains), b = c[b].nextInterval, d.getMaxInterval(a, b)) : "ss" == b ? c[b].nextInterval : b;
  };

  d.dayNames = "Sunday Monday Tuesday Wednesday Thursday Friday Saturday".split(" ");
  d.shortDayNames = "Sun Mon Tue Wed Thu Fri Sat".split(" ");
  d.monthNames = "January February March April May June July August September October November December".split(" ");
  d.shortMonthNames = "Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec".split(" ");

  d.getWeekNumber = function (a) {
    a = new Date(a);
    a.setHours(0, 0, 0);
    a.setDate(a.getDate() + 4 - (a.getDay() || 7));
    var b = new Date(a.getFullYear(), 0, 1);
    return Math.ceil(((a - b) / 864E5 + 1) / 7);
  };

  d.stringToDate = function (a, b) {
    var c = {},
        e = [{
      pattern: "YYYY",
      period: "year"
    }, {
      pattern: "YY",
      period: "year"
    }, {
      pattern: "MM",
      period: "month"
    }, {
      pattern: "M",
      period: "month"
    }, {
      pattern: "DD",
      period: "date"
    }, {
      pattern: "D",
      period: "date"
    }, {
      pattern: "JJ",
      period: "hours"
    }, {
      pattern: "J",
      period: "hours"
    }, {
      pattern: "HH",
      period: "hours"
    }, {
      pattern: "H",
      period: "hours"
    }, {
      pattern: "KK",
      period: "hours"
    }, {
      pattern: "K",
      period: "hours"
    }, {
      pattern: "LL",
      period: "hours"
    }, {
      pattern: "L",
      period: "hours"
    }, {
      pattern: "NN",
      period: "minutes"
    }, {
      pattern: "N",
      period: "minutes"
    }, {
      pattern: "SS",
      period: "seconds"
    }, {
      pattern: "S",
      period: "seconds"
    }, {
      pattern: "QQQ",
      period: "milliseconds"
    }, {
      pattern: "QQ",
      period: "milliseconds"
    }, {
      pattern: "Q",
      period: "milliseconds"
    }],
        g = !0,
        f = b.indexOf("AA");
    -1 != f && (a.substr(f, 2), "pm" == a.toLowerCase && (g = !1));
    var f = b,
        h,
        k,
        l;

    for (l = 0; l < e.length; l++) {
      k = e[l].period, c[k] = 0, "date" == k && (c[k] = 1);
    }

    for (l = 0; l < e.length; l++) {
      if (h = e[l].pattern, k = e[l].period, -1 != b.indexOf(h)) {
        var m = d.getFromDateString(h, a, f);
        b = b.replace(h, "");
        if ("KK" == h || "K" == h || "LL" == h || "L" == h) g || (m += 12);
        c[k] = m;
      }
    }

    d.useUTC ? (e = new Date(), e.setUTCFullYear(c.year, c.month, c.date), e.setUTCHours(c.hours, c.minutes, c.seconds, c.milliseconds)) : e = new Date(c.year, c.month, c.date, c.hours, c.minutes, c.seconds, c.milliseconds);
    return e;
  };

  d.getFromDateString = function (a, b, c) {
    if (void 0 !== b) return c = c.indexOf(a), b = String(b), b = b.substr(c, a.length), "0" == b.charAt(0) && (b = b.substr(1, b.length - 1)), b = Number(b), isNaN(b) && (b = 0), -1 != a.indexOf("M") && b--, b;
  };

  d.formatDate = function (a, b, c) {
    c || (c = d);
    var e,
        g,
        f,
        h,
        k,
        l,
        m,
        n,
        q = d.getWeekNumber(a);
    d.useUTC ? (e = a.getUTCFullYear(), g = a.getUTCMonth(), f = a.getUTCDate(), h = a.getUTCDay(), k = a.getUTCHours(), l = a.getUTCMinutes(), m = a.getUTCSeconds(), n = a.getUTCMilliseconds()) : (e = a.getFullYear(), g = a.getMonth(), f = a.getDate(), h = a.getDay(), k = a.getHours(), l = a.getMinutes(), m = a.getSeconds(), n = a.getMilliseconds());
    var p = String(e).substr(2, 2),
        t = "0" + h;
    b = b.replace(/W/g, q);
    q = k;
    24 == q && (q = 0);
    var r = q;
    10 > r && (r = "0" + r);
    b = b.replace(/JJ/g, r);
    b = b.replace(/J/g, q);
    r = k;
    0 === r && (r = 24, -1 != b.indexOf("H") && (f--, 0 === f && (e = new Date(a), e.setDate(e.getDate() - 1), g = e.getMonth(), f = e.getDate(), e = e.getFullYear())));
    a = g + 1;
    9 > g && (a = "0" + a);
    q = f;
    10 > f && (q = "0" + f);
    var w = r;
    10 > w && (w = "0" + w);
    b = b.replace(/HH/g, w);
    b = b.replace(/H/g, r);
    r = k;
    11 < r && (r -= 12);
    w = r;
    10 > w && (w = "0" + w);
    b = b.replace(/KK/g, w);
    b = b.replace(/K/g, r);
    r = k;
    0 === r && (r = 12);
    12 < r && (r -= 12);
    w = r;
    10 > w && (w = "0" + w);
    b = b.replace(/LL/g, w);
    b = b.replace(/L/g, r);
    r = l;
    10 > r && (r = "0" + r);
    b = b.replace(/NN/g, r);
    b = b.replace(/N/g, l);
    l = m;
    10 > l && (l = "0" + l);
    b = b.replace(/SS/g, l);
    b = b.replace(/S/g, m);
    m = n;
    10 > m ? m = "00" + m : 100 > m && (m = "0" + m);
    l = n;
    10 > l && (l = "00" + l);
    b = b.replace(/A/g, "@A@");
    b = b.replace(/QQQ/g, m);
    b = b.replace(/QQ/g, l);
    b = b.replace(/Q/g, n);
    b = b.replace(/YYYY/g, "@IIII@");
    b = b.replace(/YY/g, "@II@");
    b = b.replace(/MMMM/g, "@XXXX@");
    b = b.replace(/MMM/g, "@XXX@");
    b = b.replace(/MM/g, "@XX@");
    b = b.replace(/M/g, "@X@");
    b = b.replace(/DD/g, "@RR@");
    b = b.replace(/D/g, "@R@");
    b = b.replace(/EEEE/g, "@PPPP@");
    b = b.replace(/EEE/g, "@PPP@");
    b = b.replace(/EE/g, "@PP@");
    b = b.replace(/E/g, "@P@");
    b = b.replace(/@IIII@/g, e);
    b = b.replace(/@II@/g, p);
    b = b.replace(/@XXXX@/g, c.monthNames[g]);
    b = b.replace(/@XXX@/g, c.shortMonthNames[g]);
    b = b.replace(/@XX@/g, a);
    b = b.replace(/@X@/g, g + 1);
    b = b.replace(/@RR@/g, q);
    b = b.replace(/@R@/g, f);
    b = b.replace(/@PPPP@/g, c.dayNames[h]);
    b = b.replace(/@PPP@/g, c.shortDayNames[h]);
    b = b.replace(/@PP@/g, t);
    b = b.replace(/@P@/g, h);
    return b = 12 > k ? b.replace(/@A@/g, c.amString) : b.replace(/@A@/g, c.pmString);
  };

  d.changeDate = function (a, b, c, e, g) {
    if (d.useUTC) return d.changeUTCDate(a, b, c, e, g);
    var f = -1;
    void 0 === e && (e = !0);
    void 0 === g && (g = !1);
    !0 === e && (f = 1);

    switch (b) {
      case "YYYY":
        a.setFullYear(a.getFullYear() + c * f);
        e || g || a.setDate(a.getDate() + 1);
        break;

      case "MM":
        b = a.getMonth();
        var h = a.getFullYear();
        a.setMonth(b + c * f);
        var k = a.getMonth();
        if (e && k - b > c) for (; a.getMonth() - b > c;) {
          a.setDate(a.getDate() - 1);
        }
        h == a.getFullYear() && a.getMonth() > b + c * f && a.setDate(a.getDate() - 1);
        e || g || a.setDate(a.getDate() + 1);
        break;

      case "DD":
        a.setDate(a.getDate() + c * f);
        break;

      case "WW":
        a.setDate(a.getDate() + c * f * 7);
        break;

      case "hh":
        a.setHours(a.getHours() + c * f);
        break;

      case "mm":
        a.setMinutes(a.getMinutes() + c * f);
        break;

      case "ss":
        a.setSeconds(a.getSeconds() + c * f);
        break;

      case "fff":
        a.setMilliseconds(a.getMilliseconds() + c * f);
    }

    return a;
  };

  d.changeUTCDate = function (a, b, c, d, g) {
    var f = -1;
    void 0 === d && (d = !0);
    void 0 === g && (g = !1);
    !0 === d && (f = 1);

    switch (b) {
      case "YYYY":
        a.setUTCFullYear(a.getUTCFullYear() + c * f);
        d || g || a.setUTCDate(a.getUTCDate() + 1);
        break;

      case "MM":
        b = a.getUTCMonth();
        a.setUTCMonth(a.getUTCMonth() + c * f);
        a.getUTCMonth() > b + c * f && a.setUTCDate(a.getUTCDate() - 1);
        d || g || a.setUTCDate(a.getUTCDate() + 1);
        break;

      case "DD":
        a.setUTCDate(a.getUTCDate() + c * f);
        break;

      case "WW":
        a.setUTCDate(a.getUTCDate() + c * f * 7);
        break;

      case "hh":
        a.setUTCHours(a.getUTCHours() + c * f);
        break;

      case "mm":
        a.setUTCMinutes(a.getUTCMinutes() + c * f);
        break;

      case "ss":
        a.setUTCSeconds(a.getUTCSeconds() + c * f);
        break;

      case "fff":
        a.setUTCMilliseconds(a.getUTCMilliseconds() + c * f);
    }

    return a;
  };
})();
"use strict";

(function () {
  var k = window.AmCharts;
  k.AmSlicedChart = k.Class({
    inherits: k.AmChart,
    construct: function construct(a) {
      this.createEvents("rollOverSlice", "rollOutSlice", "clickSlice", "pullOutSlice", "pullInSlice", "rightClickSlice");
      k.AmSlicedChart.base.construct.call(this, a);
      this.colors = "#FF0F00 #FF6600 #FF9E01 #FCD202 #F8FF01 #B0DE09 #04D215 #0D8ECF #0D52D1 #2A0CD0 #8A0CCF #CD0D74 #754DEB #DDDDDD #999999 #333333 #000000 #57032A #CA9726 #990000 #4B0C25".split(" ");
      this.alpha = 1;
      this.groupPercent = 0;
      this.groupedTitle = "Other";
      this.groupedPulled = !1;
      this.groupedAlpha = 1;
      this.marginLeft = 0;
      this.marginBottom = this.marginTop = 10;
      this.marginRight = 0;
      this.hoverAlpha = 1;
      this.outlineColor = "#FFFFFF";
      this.outlineAlpha = 0;
      this.outlineThickness = 1;
      this.startAlpha = 0;
      this.startDuration = 1;
      this.startEffect = "bounce";
      this.sequencedAnimation = !0;
      this.pullOutDuration = 1;
      this.pullOutEffect = "bounce";
      this.pullOnHover = this.pullOutOnlyOne = !1;
      this.labelsEnabled = !0;
      this.labelTickColor = "#000000";
      this.labelTickAlpha = .2;
      this.hideLabelsPercent = 0;
      this.urlTarget = "_self";
      this.autoMarginOffset = 10;
      this.gradientRatio = [];
      this.maxLabelWidth = 200;
      this.accessibleLabel = "[[title]]: [[percents]]% [[value]] [[description]]";
      k.applyTheme(this, a, "AmSlicedChart");
    },
    initChart: function initChart() {
      k.AmSlicedChart.base.initChart.call(this);
      this.dataChanged && (this.parseData(), this.dispatchDataUpdated = !0, this.dataChanged = !1, this.setLegendData(this.chartData));
      this.drawChart();
    },
    handleLegendEvent: function handleLegendEvent(a) {
      var b = a.type,
          c = a.dataItem,
          d = this.legend;

      if (c.wedge && c) {
        var g = c.hidden;
        a = a.event;

        switch (b) {
          case "clickMarker":
            g || d.switchable || this.clickSlice(c, a);
            break;

          case "clickLabel":
            g || this.clickSlice(c, a, !1);
            break;

          case "rollOverItem":
            g || this.rollOverSlice(c, !1, a);
            break;

          case "rollOutItem":
            g || this.rollOutSlice(c, a);
            break;

          case "hideItem":
            this.hideSlice(c, a);
            break;

          case "showItem":
            this.showSlice(c, a);
        }
      }
    },
    invalidateVisibility: function invalidateVisibility() {
      this.recalculatePercents();
      this.initChart();
      var a = this.legend;
      a && a.invalidateSize();
    },
    addEventListeners: function addEventListeners(a, b) {
      var c = this;
      a.mouseover(function (a) {
        c.rollOverSlice(b, !0, a);
      }).mouseout(function (a) {
        c.rollOutSlice(b, a);
      }).touchend(function (a) {
        c.rollOverSlice(b, a);
      }).mouseup(function (a) {
        c.clickSlice(b, a);
      }).contextmenu(function (a) {
        c.handleRightClick(b, a);
      });
    },
    formatString: function formatString(a, b, c) {
      a = k.formatValue(a, b, ["value"], this.nf, "", this.usePrefixes, this.prefixesOfSmallNumbers, this.prefixesOfBigNumbers);
      var d = this.pf.precision;
      isNaN(this.tempPrec) || (this.pf.precision = this.tempPrec);
      a = k.formatValue(a, b, ["percents"], this.pf);
      a = k.massReplace(a, {
        "[[title]]": b.title,
        "[[description]]": b.description
      });
      this.pf.precision = d;
      -1 != a.indexOf("[[") && (a = k.formatDataContextValue(a, b.dataContext));
      a = c ? k.fixNewLines(a) : k.fixBrakes(a);
      return a = k.cleanFromEmpty(a);
    },
    startSlices: function startSlices() {
      var a;

      for (a = 0; a < this.chartData.length; a++) {
        0 < this.startDuration && this.sequencedAnimation ? this.setStartTO(a) : this.startSlice(this.chartData[a]);
      }
    },
    setStartTO: function setStartTO(a) {
      var b = this;
      a = setTimeout(function () {
        b.startSequenced.call(b);
      }, b.startDuration / b.chartData.length * 500 * a);
      b.timeOuts.push(a);
    },
    pullSlices: function pullSlices(a) {
      var b = this.chartData,
          c;

      for (c = 0; c < b.length; c++) {
        var d = b[c];
        d.pulled && this.pullSlice(d, 1, a);
      }
    },
    startSequenced: function startSequenced() {
      var a = this.chartData,
          b;

      for (b = 0; b < a.length; b++) {
        if (!a[b].started) {
          this.startSlice(this.chartData[b]);
          break;
        }
      }
    },
    startSlice: function startSlice(a) {
      a.started = !0;
      var b = a.wedge,
          c = this.startDuration,
          d = a.labelSet;
      b && 0 < c && (0 < a.alpha && b.show(), b.translate(a.startX, a.startY), this.animatable.push(b), b.animate({
        opacity: 1,
        translate: "0,0"
      }, c, this.startEffect));
      d && 0 < c && (0 < a.alpha && d.show(), d.translate(a.startX, a.startY), d.animate({
        opacity: 1,
        translate: "0,0"
      }, c, this.startEffect));
    },
    showLabels: function showLabels() {
      var a = this.chartData,
          b;

      for (b = 0; b < a.length; b++) {
        var c = a[b];

        if (0 < c.alpha) {
          var d = c.label;
          d && d.show();
          (c = c.tick) && c.show();
        }
      }
    },
    showSlice: function showSlice(a) {
      isNaN(a) ? a.hidden = !1 : this.chartData[a].hidden = !1;
      this.invalidateVisibility();
    },
    hideSlice: function hideSlice(a) {
      isNaN(a) ? a.hidden = !0 : this.chartData[a].hidden = !0;
      this.hideBalloon();
      this.invalidateVisibility();
    },
    rollOverSlice: function rollOverSlice(a, b, c) {
      isNaN(a) || (a = this.chartData[a]);
      clearTimeout(this.hoverInt);

      if (!a.hidden) {
        this.pullOnHover && this.pullSlice(a, 1);
        1 > this.hoverAlpha && a.wedge && a.wedge.attr({
          opacity: this.hoverAlpha
        });
        var d = a.balloonX,
            g = a.balloonY;
        a.pulled && (d += a.pullX, g += a.pullY);
        var f = this.formatString(this.balloonText, a, !0),
            h = this.balloonFunction;
        h && (f = h(a, f));
        h = k.adjustLuminosity(a.color, -.15);
        f ? this.showBalloon(f, h, b, d, g) : this.hideBalloon();
        0 === a.value && this.hideBalloon();
        this.fire({
          type: "rollOverSlice",
          dataItem: a,
          chart: this,
          event: c
        });
      }
    },
    rollOutSlice: function rollOutSlice(a, b) {
      isNaN(a) || (a = this.chartData[a]);
      a.wedge && a.wedge.attr({
        opacity: 1
      });
      this.hideBalloon();
      this.fire({
        type: "rollOutSlice",
        dataItem: a,
        chart: this,
        event: b
      });
    },
    clickSlice: function clickSlice(a, b, c) {
      this.checkTouchDuration(b) && (isNaN(a) || (a = this.chartData[a]), a.pulled ? this.pullSlice(a, 0) : this.pullSlice(a, 1), k.getURL(a.url, this.urlTarget), c || this.fire({
        type: "clickSlice",
        dataItem: a,
        chart: this,
        event: b
      }));
    },
    handleRightClick: function handleRightClick(a, b) {
      isNaN(a) || (a = this.chartData[a]);
      this.fire({
        type: "rightClickSlice",
        dataItem: a,
        chart: this,
        event: b
      });
    },
    drawTicks: function drawTicks() {
      var a = this.chartData,
          b;

      for (b = 0; b < a.length; b++) {
        var c = a[b];

        if (c.label && !c.skipTick) {
          var d = c.ty,
              d = k.line(this.container, [c.tx0, c.tx, c.tx2], [c.ty0, d, d], this.labelTickColor, this.labelTickAlpha);
          k.setCN(this, d, this.type + "-tick");
          k.setCN(this, d, c.className, !0);
          c.tick = d;
          c.wedge.push(d);
        }
      }
    },
    initialStart: function initialStart() {
      var a = this,
          b = a.startDuration,
          c = setTimeout(function () {
        a.showLabels.call(a);
      }, 1E3 * b);
      a.timeOuts.push(c);
      a.chartCreated ? a.pullSlices(!0) : (a.startSlices(), 0 < b ? (b = setTimeout(function () {
        a.pullSlices.call(a);
      }, 1200 * b), a.timeOuts.push(b)) : a.pullSlices(!0));
    },
    pullSlice: function pullSlice(a, b, c) {
      var d = this.pullOutDuration;
      !0 === c && (d = 0);
      if (c = a.wedge) 0 < d ? (c.animate({
        translate: b * a.pullX + "," + b * a.pullY
      }, d, this.pullOutEffect), a.labelSet && a.labelSet.animate({
        translate: b * a.pullX + "," + b * a.pullY
      }, d, this.pullOutEffect)) : (a.labelSet && a.labelSet.translate(b * a.pullX, b * a.pullY), c.translate(b * a.pullX, b * a.pullY));
      1 == b ? (a.pulled = !0, this.pullOutOnlyOne && this.pullInAll(a.index), a = {
        type: "pullOutSlice",
        dataItem: a,
        chart: this
      }) : (a.pulled = !1, a = {
        type: "pullInSlice",
        dataItem: a,
        chart: this
      });
      this.fire(a);
    },
    pullInAll: function pullInAll(a) {
      var b = this.chartData,
          c;

      for (c = 0; c < this.chartData.length; c++) {
        c != a && b[c].pulled && this.pullSlice(b[c], 0);
      }
    },
    pullOutAll: function pullOutAll() {
      var a = this.chartData,
          b;

      for (b = 0; b < a.length; b++) {
        a[b].pulled || this.pullSlice(a[b], 1);
      }
    },
    parseData: function parseData() {
      var a = [];
      this.chartData = a;
      var b = this.dataProvider;
      isNaN(this.pieAlpha) || (this.alpha = this.pieAlpha);

      if (void 0 !== b) {
        var c = b.length,
            d = 0,
            g,
            f,
            h;

        for (g = 0; g < c; g++) {
          f = {};
          var e = b[g];
          f.dataContext = e;
          null !== e[this.valueField] && (f.value = Number(e[this.valueField]));
          (h = e[this.titleField]) || (h = "");
          f.title = h;
          f.pulled = k.toBoolean(e[this.pulledField], !1);
          (h = e[this.descriptionField]) || (h = "");
          f.description = h;
          f.labelRadius = Number(e[this.labelRadiusField]);
          f.switchable = !0;
          f.className = e[this.classNameField];
          f.url = e[this.urlField];
          h = e[this.patternField];
          !h && this.patterns && (h = this.patterns[g]);
          f.pattern = h;
          f.visibleInLegend = k.toBoolean(e[this.visibleInLegendField], !0);
          h = e[this.alphaField];
          f.alpha = void 0 !== h ? Number(h) : this.alpha;
          h = e[this.colorField];
          void 0 !== h && (f.color = h);
          f.labelColor = k.toColor(e[this.labelColorField]);
          d += f.value;
          f.hidden = !1;
          a[g] = f;
        }

        for (g = b = 0; g < c; g++) {
          f = a[g], f.percents = f.value / d * 100, f.percents < this.groupPercent && b++;
        }

        1 < b && (this.groupValue = 0, this.removeSmallSlices(), a.push({
          title: this.groupedTitle,
          value: this.groupValue,
          percents: this.groupValue / d * 100,
          pulled: this.groupedPulled,
          color: this.groupedColor,
          url: this.groupedUrl,
          description: this.groupedDescription,
          alpha: this.groupedAlpha,
          pattern: this.groupedPattern,
          className: this.groupedClassName,
          dataContext: {}
        }));
        c = this.baseColor;
        c || (c = this.pieBaseColor);
        d = this.brightnessStep;
        d || (d = this.pieBrightnessStep);

        for (g = 0; g < a.length; g++) {
          c ? h = k.adjustLuminosity(c, g * d / 100) : (h = this.colors[g], void 0 === h && (h = k.randomColor())), void 0 === a[g].color && (a[g].color = h);
        }

        this.recalculatePercents();
      }
    },
    recalculatePercents: function recalculatePercents() {
      var a = this.chartData,
          b = 0,
          c,
          d;

      for (c = 0; c < a.length; c++) {
        d = a[c], !d.hidden && 0 < d.value && (b += d.value);
      }

      for (c = 0; c < a.length; c++) {
        d = this.chartData[c], d.percents = !d.hidden && 0 < d.value ? 100 * d.value / b : 0;
      }
    },
    removeSmallSlices: function removeSmallSlices() {
      var a = this.chartData,
          b;

      for (b = a.length - 1; 0 <= b; b--) {
        a[b].percents < this.groupPercent && (this.groupValue += a[b].value, a.splice(b, 1));
      }
    },
    animateAgain: function animateAgain() {
      var a = this;
      a.startSlices();

      for (var b = 0; b < a.chartData.length; b++) {
        var c = a.chartData[b];
        c.started = !1;
        var d = c.wedge;
        d && (d.setAttr("opacity", a.startAlpha), d.translate(c.startX, c.startY));
        if (d = c.labelSet) d.setAttr("opacity", a.startAlpha), d.translate(c.startX, c.startY);
      }

      b = a.startDuration;
      0 < b ? (b = setTimeout(function () {
        a.pullSlices.call(a);
      }, 1200 * b), a.timeOuts.push(b)) : a.pullSlices();
    },
    measureMaxLabel: function measureMaxLabel() {
      var a = this.chartData,
          b = 0,
          c;

      for (c = 0; c < a.length; c++) {
        var d = a[c],
            g = this.formatString(this.labelText, d),
            f = this.labelFunction;
        f && (g = f(d, g));
        d = k.text(this.container, g, this.color, this.fontFamily, this.fontSize);
        g = d.getBBox().width;
        g > b && (b = g);
        d.remove();
      }

      return b;
    }
  });
})();

(function () {
  var k = window.AmCharts;
  k.AmPieChart = k.Class({
    inherits: k.AmSlicedChart,
    construct: function construct(a) {
      this.type = "pie";
      k.AmPieChart.base.construct.call(this, a);
      this.cname = "AmPieChart";
      this.pieBrightnessStep = 30;
      this.minRadius = 10;
      this.depth3D = 0;
      this.startAngle = 90;
      this.angle = this.innerRadius = 0;
      this.startRadius = "500%";
      this.pullOutRadius = "20%";
      this.labelRadius = 20;
      this.labelText = "[[title]]: [[percents]]%";
      this.balloonText = "[[title]]: [[percents]]% ([[value]])\n[[description]]";
      this.previousScale = 1;
      this.adjustPrecision = !1;
      this.gradientType = "radial";
      k.applyTheme(this, a, this.cname);
    },
    drawChart: function drawChart() {
      k.AmPieChart.base.drawChart.call(this);
      var a = this.chartData;

      if (k.ifArray(a)) {
        if (0 < this.realWidth && 0 < this.realHeight) {
          k.VML && (this.startAlpha = 1);
          var b = this.startDuration,
              c = this.container,
              d = this.updateWidth();
          this.realWidth = d;
          var g = this.updateHeight();
          this.realHeight = g;
          var f = k.toCoordinate,
              h = f(this.marginLeft, d),
              e = f(this.marginRight, d),
              z = f(this.marginTop, g) + this.getTitleHeight(),
              n = f(this.marginBottom, g) + this.depth3D,
              A,
              B,
              m,
              w = k.toNumber(this.labelRadius),
              q = this.measureMaxLabel();
          q > this.maxLabelWidth && (q = this.maxLabelWidth);
          this.labelText && this.labelsEnabled || (w = q = 0);
          A = void 0 === this.pieX ? (d - h - e) / 2 + h : f(this.pieX, this.realWidth);
          B = void 0 === this.pieY ? (g - z - n) / 2 + z : f(this.pieY, g);
          m = f(this.radius, d, g);
          m || (d = 0 <= w ? d - h - e - 2 * q : d - h - e, g = g - z - n, m = Math.min(d, g), g < d && (m /= 1 - this.angle / 90, m > d && (m = d)), g = k.toCoordinate(this.pullOutRadius, m), m = (0 <= w ? m - 1.8 * (w + g) : m - 1.8 * g) / 2);
          m < this.minRadius && (m = this.minRadius);
          g = f(this.pullOutRadius, m);
          z = k.toCoordinate(this.startRadius, m);
          f = f(this.innerRadius, m);
          f >= m && (f = m - 1);
          n = k.fitToBounds(this.startAngle, 0, 360);
          0 < this.depth3D && (n = 270 <= n ? 270 : 90);
          n -= 90;
          360 < n && (n -= 360);
          d = m - m * this.angle / 90;

          for (h = q = 0; h < a.length; h++) {
            e = a[h], !0 !== e.hidden && (q += k.roundTo(e.percents, this.pf.precision));
          }

          q = k.roundTo(q, this.pf.precision);
          this.tempPrec = NaN;
          this.adjustPrecision && 100 != q && (this.tempPrec = this.pf.precision + 1);

          for (var E, h = 0; h < a.length; h++) {
            if (e = a[h], !0 !== e.hidden && (this.showZeroSlices || 0 !== e.percents)) {
              var r = 360 * e.percents / 100,
                  q = Math.sin((n + r / 2) / 180 * Math.PI),
                  C = d / m * -Math.cos((n + r / 2) / 180 * Math.PI),
                  p = this.outlineColor;
              p || (p = e.color);
              var u = this.alpha;
              isNaN(e.alpha) || (u = e.alpha);
              p = {
                fill: e.color,
                stroke: p,
                "stroke-width": this.outlineThickness,
                "stroke-opacity": this.outlineAlpha,
                "fill-opacity": u
              };
              e.url && (p.cursor = "pointer");
              p = k.wedge(c, A, B, n, r, m, d, f, this.depth3D, p, this.gradientRatio, e.pattern, this.path, this.gradientType);
              k.setCN(this, p, "pie-item");
              k.setCN(this, p.wedge, "pie-slice");
              k.setCN(this, p, e.className, !0);
              this.addEventListeners(p, e);
              e.startAngle = n;
              a[h].wedge = p;
              0 < b && (this.chartCreated || p.setAttr("opacity", this.startAlpha));
              e.ix = q;
              e.iy = C;
              e.wedge = p;
              e.index = h;
              e.label = null;
              u = c.set();

              if (this.labelsEnabled && this.labelText && e.percents >= this.hideLabelsPercent) {
                var l = n + r / 2;
                0 > l && (l += 360);
                360 < l && (l -= 360);
                var t = w;
                isNaN(e.labelRadius) || (t = e.labelRadius, 0 > t && (e.skipTick = !0));
                var r = A + q * (m + t),
                    D = B + C * (m + t),
                    x,
                    v = 0;
                isNaN(E) && 350 < l && 1 < a.length - h && (E = h - 1 + Math.floor((a.length - h) / 2));

                if (0 <= t) {
                  var y;
                  90 >= l && 0 <= l ? (y = 0, x = "start", v = 8) : 90 <= l && 180 > l ? (y = 1, x = "start", v = 8) : 180 <= l && 270 > l ? (y = 2, x = "end", v = -8) : 270 <= l && 354 >= l ? (y = 3, x = "end", v = -8) : 354 <= l && (h > E ? (y = 0, x = "start", v = 8) : (y = 3, x = "end", v = -8));
                  e.labelQuarter = y;
                } else x = "middle";

                l = this.formatString(this.labelText, e);
                (t = this.labelFunction) && (l = t(e, l));
                t = e.labelColor;
                t || (t = this.color);
                "" !== l && (l = k.wrappedText(c, l, t, this.fontFamily, this.fontSize, x, !1, this.maxLabelWidth), k.setCN(this, l, "pie-label"), k.setCN(this, l, e.className, !0), l.translate(r + 1.5 * v, D), 0 > w && (l.node.style.pointerEvents = "none"), l.node.style.cursor = "default", e.ty = D, e.textX = r + 1.5 * v, u.push(l), this.axesSet.push(u), e.labelSet = u, e.label = l, this.addEventListeners(u, e));
                e.tx = r;
                e.tx2 = r + v;
                e.tx0 = A + q * m;
                e.ty0 = B + C * m;
              }

              r = f + (m - f) / 2;
              e.pulled && (r += g);
              this.accessible && this.accessibleLabel && (D = this.formatString(this.accessibleLabel, e), this.makeAccessible(p, D));
              void 0 !== this.tabIndex && p.setAttr("tabindex", this.tabIndex);
              e.balloonX = q * r + A;
              e.balloonY = C * r + B;
              e.startX = Math.round(q * z);
              e.startY = Math.round(C * z);
              e.pullX = Math.round(q * g);
              e.pullY = Math.round(C * g);
              this.graphsSet.push(p);
              if (0 === e.alpha || 0 < b && !this.chartCreated) p.hide(), u && u.hide();
              n += 360 * e.percents / 100;
              360 < n && (n -= 360);
            }
          }

          0 < w && this.arrangeLabels();
          this.pieXReal = A;
          this.pieYReal = B;
          this.radiusReal = m;
          this.innerRadiusReal = f;
          0 < w && this.drawTicks();
          this.initialStart();
          this.setDepths();
        }

        (a = this.legend) && a.invalidateSize();
      } else this.cleanChart();

      this.dispDUpd();
    },
    setDepths: function setDepths() {
      var a = this.chartData,
          b;

      for (b = 0; b < a.length; b++) {
        var c = a[b],
            d = c.wedge,
            c = c.startAngle;
        0 <= c && 180 > c ? d.toFront() : 180 <= c && d.toBack();
      }
    },
    arrangeLabels: function arrangeLabels() {
      var a = this.chartData,
          b = a.length,
          c,
          d;

      for (d = b - 1; 0 <= d; d--) {
        c = a[d], 0 !== c.labelQuarter || c.hidden || this.checkOverlapping(d, c, 0, !0, 0);
      }

      for (d = 0; d < b; d++) {
        c = a[d], 1 != c.labelQuarter || c.hidden || this.checkOverlapping(d, c, 1, !1, 0);
      }

      for (d = b - 1; 0 <= d; d--) {
        c = a[d], 2 != c.labelQuarter || c.hidden || this.checkOverlapping(d, c, 2, !0, 0);
      }

      for (d = 0; d < b; d++) {
        c = a[d], 3 != c.labelQuarter || c.hidden || this.checkOverlapping(d, c, 3, !1, 0);
      }
    },
    checkOverlapping: function checkOverlapping(a, b, c, d, g) {
      var f,
          h,
          e = this.chartData,
          k = e.length,
          n = b.label;

      if (n) {
        if (!0 === d) for (h = a + 1; h < k; h++) {
          e[h].labelQuarter == c && (f = this.checkOverlappingReal(b, e[h], c)) && (h = k);
        } else for (h = a - 1; 0 <= h; h--) {
          e[h].labelQuarter == c && (f = this.checkOverlappingReal(b, e[h], c)) && (h = 0);
        }
        !0 === f && 200 > g && isNaN(b.labelRadius) && (f = b.ty + 3 * b.iy, b.ty = f, n.translate(b.textX, f), this.checkOverlapping(a, b, c, d, g + 1));
      }
    },
    checkOverlappingReal: function checkOverlappingReal(a, b, c) {
      var d = !1,
          g = a.label,
          f = b.label;
      a.labelQuarter != c || a.hidden || b.hidden || !f || (g = g.getBBox(), c = {}, c.width = g.width, c.height = g.height, c.y = a.ty, c.x = a.tx, a = f.getBBox(), f = {}, f.width = a.width, f.height = a.height, f.y = b.ty, f.x = b.tx, k.hitTest(c, f) && (d = !0));
      return d;
    }
  });
})();
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

(function () {
  var e = window.AmCharts;
  e.AmRectangularChart = e.Class({
    inherits: e.AmCoordinateChart,
    construct: function construct(a) {
      e.AmRectangularChart.base.construct.call(this, a);
      this.theme = a;
      this.createEvents("zoomed", "changed");
      this.marginRight = this.marginBottom = this.marginTop = this.marginLeft = 20;
      this.depth3D = this.angle = 0;
      this.plotAreaFillColors = "#FFFFFF";
      this.plotAreaFillAlphas = 0;
      this.plotAreaBorderColor = "#000000";
      this.plotAreaBorderAlpha = 0;
      this.maxZoomFactor = 20;
      this.zoomOutButtonImageSize = 19;
      this.zoomOutButtonImage = "lens";
      this.zoomOutText = "Show all";
      this.zoomOutButtonColor = "#e5e5e5";
      this.zoomOutButtonAlpha = 0;
      this.zoomOutButtonRollOverAlpha = 1;
      this.zoomOutButtonPadding = 8;
      this.trendLines = [];
      this.autoMargins = !0;
      this.marginsUpdated = !1;
      this.autoMarginOffset = 10;
      e.applyTheme(this, a, "AmRectangularChart");
    },
    initChart: function initChart() {
      e.AmRectangularChart.base.initChart.call(this);
      this.updateDxy();
      !this.marginsUpdated && this.autoMargins && (this.resetMargins(), this.drawGraphs = !1);
      this.processScrollbars();
      this.updateMargins();
      this.updatePlotArea();
      this.updateScrollbars();
      this.updateTrendLines();
      this.updateChartCursor();
      this.updateValueAxes();
      this.scrollbarOnly || this.updateGraphs();
    },
    drawChart: function drawChart() {
      e.AmRectangularChart.base.drawChart.call(this);
      this.drawPlotArea();

      if (e.ifArray(this.chartData)) {
        var a = this.chartCursor;
        a && a.draw();
      }
    },
    resetMargins: function resetMargins() {
      var a = {},
          b;

      if ("xy" == this.type) {
        var c = this.xAxes,
            d = this.yAxes;

        for (b = 0; b < c.length; b++) {
          var g = c[b];
          g.ignoreAxisWidth || (g.setOrientation(!0), g.fixAxisPosition(), a[g.position] = !0);
        }

        for (b = 0; b < d.length; b++) {
          c = d[b], c.ignoreAxisWidth || (c.setOrientation(!1), c.fixAxisPosition(), a[c.position] = !0);
        }
      } else {
        d = this.valueAxes;

        for (b = 0; b < d.length; b++) {
          c = d[b], c.ignoreAxisWidth || (c.setOrientation(this.rotate), c.fixAxisPosition(), a[c.position] = !0);
        }

        (b = this.categoryAxis) && !b.ignoreAxisWidth && (b.setOrientation(!this.rotate), b.fixAxisPosition(), b.fixAxisPosition(), a[b.position] = !0);
      }

      a.left && (this.marginLeft = 0);
      a.right && (this.marginRight = 0);
      a.top && (this.marginTop = 0);
      a.bottom && (this.marginBottom = 0);
      this.fixMargins = a;
    },
    measureMargins: function measureMargins() {
      var a = this.valueAxes,
          b,
          c = this.autoMarginOffset,
          d = this.fixMargins,
          g = this.realWidth,
          h = this.realHeight,
          f = c,
          e = c,
          k = g;
      b = h;
      var m;

      for (m = 0; m < a.length; m++) {
        a[m].handleSynchronization(), b = this.getAxisBounds(a[m], f, k, e, b), f = Math.round(b.l), k = Math.round(b.r), e = Math.round(b.t), b = Math.round(b.b);
      }

      if (a = this.categoryAxis) b = this.getAxisBounds(a, f, k, e, b), f = Math.round(b.l), k = Math.round(b.r), e = Math.round(b.t), b = Math.round(b.b);
      d.left && f < c && (this.marginLeft = Math.round(-f + c), !isNaN(this.minMarginLeft) && this.marginLeft < this.minMarginLeft && (this.marginLeft = this.minMarginLeft));
      d.right && k >= g - c && (this.marginRight = Math.round(k - g + c), !isNaN(this.minMarginRight) && this.marginRight < this.minMarginRight && (this.marginRight = this.minMarginRight));
      d.top && e < c + this.titleHeight && (this.marginTop = Math.round(this.marginTop - e + c + this.titleHeight), !isNaN(this.minMarginTop) && this.marginTop < this.minMarginTop && (this.marginTop = this.minMarginTop));
      d.bottom && b > h - c && (this.marginBottom = Math.round(this.marginBottom + b - h + c), !isNaN(this.minMarginBottom) && this.marginBottom < this.minMarginBottom && (this.marginBottom = this.minMarginBottom));
      this.initChart();
    },
    getAxisBounds: function getAxisBounds(a, b, c, d, g) {
      if (!a.ignoreAxisWidth) {
        var h = a.labelsSet,
            f = a.tickLength;
        a.inside && (f = 0);
        if (h) switch (h = a.getBBox(), a.position) {
          case "top":
            a = h.y;
            d > a && (d = a);
            break;

          case "bottom":
            a = h.y + h.height;
            g < a && (g = a);
            break;

          case "right":
            a = h.x + h.width + f + 3;
            c < a && (c = a);
            break;

          case "left":
            a = h.x - f, b > a && (b = a);
        }
      }

      return {
        l: b,
        t: d,
        r: c,
        b: g
      };
    },
    drawZoomOutButton: function drawZoomOutButton() {
      var a = this;

      if (!a.zbSet) {
        var b = a.container.set();
        a.zoomButtonSet.push(b);
        var c = a.color,
            d = a.fontSize,
            g = a.zoomOutButtonImageSize,
            h = a.zoomOutButtonImage.replace(/\.[a-z]*$/i, ""),
            f = a.langObj.zoomOutText || a.zoomOutText,
            l = a.zoomOutButtonColor,
            k = a.zoomOutButtonAlpha,
            m = a.zoomOutButtonFontSize,
            p = a.zoomOutButtonPadding;
        isNaN(m) || (d = m);
        (m = a.zoomOutButtonFontColor) && (c = m);
        var m = a.zoomOutButton,
            n;
        m && (m.fontSize && (d = m.fontSize), m.color && (c = m.color), m.backgroundColor && (l = m.backgroundColor), isNaN(m.backgroundAlpha) || (a.zoomOutButtonRollOverAlpha = m.backgroundAlpha));
        var u = m = 0,
            u = a.pathToImages;

        if (h) {
          if (e.isAbsolute(h) || void 0 === u) u = "";
          n = a.container.image(u + h + a.extension, 0, 0, g, g);
          e.setCN(a, n, "zoom-out-image");
          b.push(n);
          n = n.getBBox();
          m = n.width + 5;
        }

        void 0 !== f && (c = e.text(a.container, f, c, a.fontFamily, d, "start"), e.setCN(a, c, "zoom-out-label"), d = c.getBBox(), u = n ? n.height / 2 - 3 : d.height / 2, c.translate(m, u), b.push(c));
        n = b.getBBox();
        c = 1;
        e.isModern || (c = 0);
        l = e.rect(a.container, n.width + 2 * p + 5, n.height + 2 * p - 2, l, 1, 1, l, c);
        l.setAttr("opacity", k);
        l.translate(-p, -p);
        e.setCN(a, l, "zoom-out-bg");
        b.push(l);
        l.toBack();
        a.zbBG = l;
        n = l.getBBox();
        b.translate(a.marginLeftReal + a.plotAreaWidth - n.width + p, a.marginTopReal + p);
        b.hide();
        b.mouseover(function () {
          a.rollOverZB();
        }).mouseout(function () {
          a.rollOutZB();
        }).click(function () {
          a.clickZB();
        }).touchstart(function () {
          a.rollOverZB();
        }).touchend(function () {
          a.rollOutZB();
          a.clickZB();
        });

        for (k = 0; k < b.length; k++) {
          b[k].attr({
            cursor: "pointer"
          });
        }

        void 0 !== a.zoomOutButtonTabIndex && (b.setAttr("tabindex", a.zoomOutButtonTabIndex), b.setAttr("role", "menuitem"), b.keyup(function (b) {
          13 == b.keyCode && a.clickZB();
        }));
        a.zbSet = b;
      }
    },
    rollOverZB: function rollOverZB() {
      this.rolledOverZB = !0;
      this.zbBG.setAttr("opacity", this.zoomOutButtonRollOverAlpha);
    },
    rollOutZB: function rollOutZB() {
      this.rolledOverZB = !1;
      this.zbBG.setAttr("opacity", this.zoomOutButtonAlpha);
    },
    clickZB: function clickZB() {
      this.rolledOverZB = !1;
      this.zoomOut();
    },
    zoomOut: function zoomOut() {
      this.zoomOutValueAxes();
    },
    drawPlotArea: function drawPlotArea() {
      var a = this.dx,
          b = this.dy,
          c = this.marginLeftReal,
          d = this.marginTopReal,
          g = this.plotAreaWidth - 1,
          h = this.plotAreaHeight - 1,
          f = this.plotAreaFillColors,
          l = this.plotAreaFillAlphas,
          k = this.plotAreaBorderColor,
          m = this.plotAreaBorderAlpha;
      "object" == _typeof(l) && (l = l[0]);
      f = e.polygon(this.container, [0, g, g, 0, 0], [0, 0, h, h, 0], f, l, 1, k, m, this.plotAreaGradientAngle);
      e.setCN(this, f, "plot-area");
      f.translate(c + a, d + b);
      this.set.push(f);
      0 !== a && 0 !== b && (f = this.plotAreaFillColors, "object" == _typeof(f) && (f = f[0]), f = e.adjustLuminosity(f, -.15), g = e.polygon(this.container, [0, a, g + a, g, 0], [0, b, b, 0, 0], f, l, 1, k, m), e.setCN(this, g, "plot-area-bottom"), g.translate(c, d + h), this.set.push(g), a = e.polygon(this.container, [0, 0, a, a, 0], [0, h, h + b, b, 0], f, l, 1, k, m), e.setCN(this, a, "plot-area-left"), a.translate(c, d), this.set.push(a));
      (c = this.bbset) && this.scrollbarOnly && c.remove();
    },
    updatePlotArea: function updatePlotArea() {
      var a = this.updateWidth(),
          b = this.updateHeight(),
          c = this.container;
      this.realWidth = a;
      this.realWidth = b;
      c && this.container.setSize(a, b);
      var c = this.marginLeftReal,
          d = this.marginTopReal,
          a = a - c - this.marginRightReal - this.dx,
          b = b - d - this.marginBottomReal;
      1 > a && (a = 1);
      1 > b && (b = 1);
      this.plotAreaWidth = Math.round(a);
      this.plotAreaHeight = Math.round(b);
      this.plotBalloonsSet.translate(c, d);
    },
    updateDxy: function updateDxy() {
      this.dx = Math.round(this.depth3D * Math.cos(this.angle * Math.PI / 180));
      this.dy = Math.round(-this.depth3D * Math.sin(this.angle * Math.PI / 180));
      this.d3x = Math.round(this.columnSpacing3D * Math.cos(this.angle * Math.PI / 180));
      this.d3y = Math.round(-this.columnSpacing3D * Math.sin(this.angle * Math.PI / 180));
    },
    updateMargins: function updateMargins() {
      var a = this.getTitleHeight();
      this.titleHeight = a;
      this.marginTopReal = this.marginTop - this.dy;
      this.fixMargins && !this.fixMargins.top && (this.marginTopReal += a);
      this.marginBottomReal = this.marginBottom;
      this.marginLeftReal = this.marginLeft;
      this.marginRightReal = this.marginRight;
    },
    updateValueAxes: function updateValueAxes() {
      var a = this.valueAxes,
          b;

      for (b = 0; b < a.length; b++) {
        var c = a[b];
        this.setAxisRenderers(c);
        this.updateObjectSize(c);
      }
    },
    setAxisRenderers: function setAxisRenderers(a) {
      a.axisRenderer = e.RecAxis;
      a.guideFillRenderer = e.RecFill;
      a.axisItemRenderer = e.RecItem;
      a.marginsChanged = !0;
    },
    updateGraphs: function updateGraphs() {
      var a = this.graphs,
          b;

      for (b = 0; b < a.length; b++) {
        var c = a[b];
        c.index = b;
        c.rotate = this.rotate;
        this.updateObjectSize(c);
      }
    },
    updateObjectSize: function updateObjectSize(a) {
      a.width = this.plotAreaWidth - 1;
      a.height = this.plotAreaHeight - 1;
      a.x = this.marginLeftReal;
      a.y = this.marginTopReal;
      a.dx = this.dx;
      a.dy = this.dy;
    },
    updateChartCursor: function updateChartCursor() {
      var a = this.chartCursor;
      a && (a = e.processObject(a, e.ChartCursor, this.theme), this.updateObjectSize(a), this.addChartCursor(a), a.chart = this);
    },
    processScrollbars: function processScrollbars() {
      var a = this.chartScrollbar;
      a && (a = e.processObject(a, e.ChartScrollbar, this.theme), this.addChartScrollbar(a));
    },
    updateScrollbars: function updateScrollbars() {},
    removeChartCursor: function removeChartCursor() {
      e.callMethod("destroy", [this.chartCursor]);
      this.chartCursor = null;
    },
    zoomTrendLines: function zoomTrendLines() {
      var a = this.trendLines,
          b;

      for (b = 0; b < a.length; b++) {
        var c = a[b];
        c.valueAxis.recalculateToPercents ? c.set && c.set.hide() : (c.x = this.marginLeftReal, c.y = this.marginTopReal, c.draw());
      }
    },
    handleCursorValueZoom: function handleCursorValueZoom() {},
    addTrendLine: function addTrendLine(a) {
      this.trendLines.push(a);
    },
    zoomOutValueAxes: function zoomOutValueAxes() {
      for (var a = this.valueAxes, b = 0; b < a.length; b++) {
        a[b].zoomOut();
      }
    },
    removeTrendLine: function removeTrendLine(a) {
      var b = this.trendLines,
          c;

      for (c = b.length - 1; 0 <= c; c--) {
        b[c] == a && b.splice(c, 1);
      }
    },
    adjustMargins: function adjustMargins(a, b) {
      var c = a.position,
          d = a.scrollbarHeight + a.offset;
      a.enabled && ("top" == c ? b ? this.marginLeftReal += d : this.marginTopReal += d : b ? this.marginRightReal += d : this.marginBottomReal += d);
    },
    getScrollbarPosition: function getScrollbarPosition(a, b, c) {
      var d = "bottom",
          g = "top";
      a.oppositeAxis || (g = d, d = "top");
      a.position = b ? "bottom" == c || "left" == c ? d : g : "top" == c || "right" == c ? d : g;
    },
    updateChartScrollbar: function updateChartScrollbar(a, b) {
      if (a) {
        a.rotate = b;
        var c = this.marginTopReal,
            d = this.marginLeftReal,
            g = a.scrollbarHeight,
            h = this.dx,
            f = this.dy,
            e = a.offset;
        "top" == a.position ? b ? (a.y = c, a.x = d - g - e) : (a.y = c - g + f - e, a.x = d + h) : b ? (a.y = c + f, a.x = d + this.plotAreaWidth + h + e) : (a.y = c + this.plotAreaHeight + e, a.x = this.marginLeftReal);
      }
    },
    showZB: function showZB(a) {
      var b = this.zbSet;
      a && (b = this.zoomOutText, "" !== b && b && this.drawZoomOutButton());
      if (b = this.zbSet) this.zoomButtonSet.push(b), a ? b.show() : b.hide(), this.rollOutZB();
    },
    handleReleaseOutside: function handleReleaseOutside(a) {
      e.AmRectangularChart.base.handleReleaseOutside.call(this, a);
      (a = this.chartCursor) && a.handleReleaseOutside && a.handleReleaseOutside();
    },
    handleMouseDown: function handleMouseDown(a) {
      e.AmRectangularChart.base.handleMouseDown.call(this, a);
      var b = this.chartCursor;
      b && b.handleMouseDown && !this.rolledOverZB && b.handleMouseDown(a);
    },
    update: function update() {
      e.AmRectangularChart.base.update.call(this);
      this.chartCursor && this.chartCursor.update && this.chartCursor.update();
    },
    handleScrollbarValueZoom: function handleScrollbarValueZoom(a) {
      this.relativeZoomValueAxes(a.target.valueAxes, a.relativeStart, a.relativeEnd);
      this.zoomAxesAndGraphs();
    },
    zoomValueScrollbar: function zoomValueScrollbar(a) {
      if (a && a.enabled) {
        var b = a.valueAxes[0],
            c = b.relativeStart,
            d = b.relativeEnd;
        b.reversed && (d = 1 - c, c = 1 - b.relativeEnd);
        a.percentZoom(c, d);
      }
    },
    zoomAxesAndGraphs: function zoomAxesAndGraphs() {
      if (!this.scrollbarOnly) {
        var a = this.valueAxes,
            b;

        for (b = 0; b < a.length; b++) {
          a[b].zoom(this.start, this.end);
        }

        a = this.graphs;

        for (b = 0; b < a.length; b++) {
          a[b].zoom(this.start, this.end);
        }

        (b = this.chartCursor) && b.clearSelection();
        this.zoomTrendLines();
      }
    },
    handleValueAxisZoomReal: function handleValueAxisZoomReal(a, b) {
      var c = a.relativeStart,
          d = a.relativeEnd;
      if (c > d) var g = c,
          c = d,
          d = g;
      this.relativeZoomValueAxes(b, c, d);
      this.updateAfterValueZoom();
    },
    updateAfterValueZoom: function updateAfterValueZoom() {
      this.zoomAxesAndGraphs();
      this.zoomScrollbar();
    },
    relativeZoomValueAxes: function relativeZoomValueAxes(a, b, c) {
      b = e.fitToBounds(b, 0, 1);
      c = e.fitToBounds(c, 0, 1);

      if (b > c) {
        var d = b;
        b = c;
        c = d;
      }

      var d = 1 / this.maxZoomFactor,
          g = e.getDecimals(d) + 4;
      c - b < d && (c = b + (c - b) / 2, b = c - d / 2, c += d / 2);
      b = e.roundTo(b, g);
      c = e.roundTo(c, g);
      d = !1;

      if (a) {
        for (g = 0; g < a.length; g++) {
          var h = a[g].zoomToRelativeValues(b, c, !0);
          h && (d = h);
        }

        this.showZB();
      }

      return d;
    },
    addChartCursor: function addChartCursor(a) {
      e.callMethod("destroy", [this.chartCursor]);
      a && (this.listenTo(a, "moved", this.handleCursorMove), this.listenTo(a, "zoomed", this.handleCursorZoom), this.listenTo(a, "zoomStarted", this.handleCursorZoomStarted), this.listenTo(a, "panning", this.handleCursorPanning), this.listenTo(a, "onHideCursor", this.handleCursorHide));
      this.chartCursor = a;
    },
    handleCursorChange: function handleCursorChange() {},
    handleCursorMove: function handleCursorMove(a) {
      var b,
          c = this.valueAxes;

      for (b = 0; b < c.length; b++) {
        if (!a.panning) {
          var d = c[b];
          d && d.showBalloon && d.showBalloon(a.x, a.y);
        }
      }
    },
    handleCursorZoom: function handleCursorZoom(a) {
      if (this.skipZoomed) this.skipZoomed = !1;else {
        var b = this.startX0,
            c = this.endX0,
            d = this.endY0,
            g = this.startY0,
            h = a.startX,
            f = a.endX,
            e = a.startY,
            k = a.endY;
        this.startX0 = this.endX0 = this.startY0 = this.endY0 = NaN;
        this.handleCursorZoomReal(b + h * (c - b), b + f * (c - b), g + e * (d - g), g + k * (d - g), a);
      }
    },
    handleCursorHide: function handleCursorHide() {
      var a,
          b = this.valueAxes;

      for (a = 0; a < b.length; a++) {
        b[a].hideBalloon();
      }

      b = this.graphs;

      for (a = 0; a < b.length; a++) {
        b[a].hideBalloonReal();
      }
    }
  });
})();

(function () {
  var e = window.AmCharts;
  e.AmSerialChart = e.Class({
    inherits: e.AmRectangularChart,
    construct: function construct(a) {
      this.type = "serial";
      e.AmSerialChart.base.construct.call(this, a);
      this.cname = "AmSerialChart";
      this.theme = a;
      this.columnSpacing = 5;
      this.columnSpacing3D = 0;
      this.columnWidth = .8;
      var b = new e.CategoryAxis(a);
      b.chart = this;
      this.categoryAxis = b;
      this.zoomOutOnDataUpdate = !0;
      this.mouseWheelZoomEnabled = this.mouseWheelScrollEnabled = this.rotate = this.skipZoom = !1;
      this.minSelectedTime = 0;
      e.applyTheme(this, a, this.cname);
    },
    initChart: function initChart() {
      e.AmSerialChart.base.initChart.call(this);
      this.updateCategoryAxis(this.categoryAxis, this.rotate, "categoryAxis");
      if (this.dataChanged) this.parseData();else this.onDataUpdated();
      this.drawGraphs = !0;
    },
    onDataUpdated: function onDataUpdated() {
      var a = this.countColumns(),
          b = this.chartData,
          c = this.graphs,
          d;

      for (d = 0; d < c.length; d++) {
        var g = c[d];
        g.data = b;
        g.columnCount = a;
      }

      0 < b.length && (this.firstTime = this.getStartTime(b[0].time), this.lastTime = this.getEndTime(b[b.length - 1].time));
      this.drawChart();
      this.autoMargins && !this.marginsUpdated ? (this.marginsUpdated = !0, this.measureMargins()) : this.dispDUpd();
    },
    syncGrid: function syncGrid() {
      if (this.synchronizeGrid) {
        var a = this.valueAxes,
            b,
            c;

        if (0 < a.length) {
          var d = 0;

          for (c = 0; c < a.length; c++) {
            b = a[c], d < b.gridCountReal && (d = b.gridCountReal);
          }

          var g = !1;

          for (c = 0; c < a.length; c++) {
            if (b = a[c], b.gridCountReal < d) {
              var h = (d - b.gridCountReal) / 2,
                  f = g = h;
              0 !== h - Math.round(h) && (g -= .5, f += .5);
              0 <= b.min && 0 > b.min - g * b.step && (f += g, g = 0);
              0 >= b.max && 0 < b.max + f * b.step && (g += f, f = 0);
              h = e.getDecimals(b.step);
              b.minimum = e.roundTo(b.min - g * b.step, h);
              b.maximum = e.roundTo(b.max + f * b.step, h);
              b.setStep = b.step;
              g = b.strictMinMax = !0;
            }
          }

          g && this.updateAfterValueZoom();

          for (c = 0; c < a.length; c++) {
            b = a[c], b.minimum = NaN, b.maximum = NaN, b.setStep = NaN, b.strictMinMax = !1;
          }
        }
      }
    },
    handleWheelReal: function handleWheelReal(a, b) {
      if (!this.wheelBusy) {
        var c = this.categoryAxis,
            d = c.parseDates,
            g = c.minDuration(),
            h = 1,
            f = 1;
        this.mouseWheelZoomEnabled ? b || (h = -1) : b && (h = -1);
        var e = this.chartCursor;

        if (e) {
          var k = e.mouseX,
              e = e.mouseY;
          h != f && (k = this.rotate ? e / this.plotAreaHeight : k / this.plotAreaWidth, h *= k, f *= 1 - k);
          k = .05 * (this.end - this.start);
          d && (k = .05 * (this.endTime - this.startTime) / g);
          1 > k && (k = 1);
          h *= k;
          f *= k;
          if (!d || c.equalSpacing) h = Math.round(h), f = Math.round(f);
        }

        e = this.chartData.length;
        c = this.lastTime;
        k = this.firstTime;
        0 > a ? d ? (e = this.endTime - this.startTime, d = this.startTime + h * g, g = this.endTime + f * g, 0 < f && 0 < h && g >= c && (g = c, d = c - e), this.zoomToDates(new Date(d), new Date(g))) : (0 < f && 0 < h && this.end >= e - 1 && (h = f = 0), d = this.start + h, g = this.end + f, this.zoomToIndexes(d, g)) : d ? (e = this.endTime - this.startTime, d = this.startTime - h * g, g = this.endTime - f * g, 0 < f && 0 < h && d <= k && (d = k, g = k + e), this.zoomToDates(new Date(d), new Date(g))) : (0 < f && 0 < h && 1 > this.start && (h = f = 0), d = this.start - h, g = this.end - f, this.zoomToIndexes(d, g));
      }
    },
    validateData: function validateData(a) {
      this.marginsUpdated = !1;
      this.zoomOutOnDataUpdate && !a && (this.endTime = this.end = this.startTime = this.start = NaN);
      e.AmSerialChart.base.validateData.call(this);
    },
    drawChart: function drawChart() {
      if (0 < this.realWidth && 0 < this.realHeight) {
        e.AmSerialChart.base.drawChart.call(this);
        var a = this.chartData;

        if (e.ifArray(a)) {
          var b = this.chartScrollbar;
          !b || !this.marginsUpdated && this.autoMargins || b.draw();
          (b = this.valueScrollbar) && b.draw();
          var b = a.length - 1,
              c,
              d;
          c = this.categoryAxis;

          if (c.parseDates && !c.equalSpacing) {
            if (c = this.startTime, d = this.endTime, isNaN(c) || isNaN(d)) c = this.firstTime, d = this.lastTime;
          } else {
            c = this.start;
            d = this.end;
            if (isNaN(c) || isNaN(d)) d = c = NaN;
            isNaN(c) && (isNaN(this.startTime) || (c = this.getClosestIndex(a, "time", this.startTime, !0, 0, a.length)));
            isNaN(d) && (isNaN(this.endTime) || (d = this.getClosestIndex(a, "time", this.endTime, !1, 0, a.length)));
            if (isNaN(c) || isNaN(d)) c = 0, d = b;
          }

          this.endTime = this.startTime = this.end = this.start = void 0;
          this.zoom(c, d);
        }
      } else this.cleanChart();
    },
    cleanChart: function cleanChart() {
      e.callMethod("destroy", [this.valueAxes, this.graphs, this.categoryAxis, this.chartScrollbar, this.chartCursor, this.valueScrollbar]);
    },
    updateCategoryAxis: function updateCategoryAxis(a, b, c) {
      a.chart = this;
      a.id = c;
      a.rotate = b;
      a.setOrientation(!this.rotate);
      a.init();
      this.setAxisRenderers(a);
      this.updateObjectSize(a);
    },
    updateValueAxes: function updateValueAxes() {
      e.AmSerialChart.base.updateValueAxes.call(this);
      var a = this.valueAxes,
          b;

      for (b = 0; b < a.length; b++) {
        var c = a[b],
            d = this.rotate;
        c.rotate = d;
        c.setOrientation(d);
        d = this.categoryAxis;
        if (!d.startOnAxis || d.parseDates) c.expandMinMax = !0;
      }
    },
    getStartTime: function getStartTime(a) {
      var b = this.categoryAxis;
      return e.resetDateToMin(new Date(a), b.minPeriod, 1, b.firstDayOfWeek).getTime();
    },
    getEndTime: function getEndTime(a) {
      var b = e.extractPeriod(this.categoryAxis.minPeriod);
      return e.changeDate(new Date(a), b.period, b.count, !0).getTime() - 1;
    },
    updateMargins: function updateMargins() {
      e.AmSerialChart.base.updateMargins.call(this);
      var a = this.chartScrollbar;
      a && (this.getScrollbarPosition(a, this.rotate, this.categoryAxis.position), this.adjustMargins(a, this.rotate));
      if (a = this.valueScrollbar) this.getScrollbarPosition(a, !this.rotate, this.valueAxes[0].position), this.adjustMargins(a, !this.rotate);
    },
    updateScrollbars: function updateScrollbars() {
      e.AmSerialChart.base.updateScrollbars.call(this);
      this.updateChartScrollbar(this.chartScrollbar, this.rotate);
      this.updateChartScrollbar(this.valueScrollbar, !this.rotate);
    },
    zoom: function zoom(a, b) {
      var c = this.categoryAxis;
      c.parseDates && !c.equalSpacing ? this.timeZoom(a, b) : this.indexZoom(a, b);
      isNaN(a) && this.zoomOutValueAxes();
      (c = this.chartCursor) && (c.pan || c.hideCursorReal());
      this.updateLegendValues();
    },
    timeZoom: function timeZoom(a, b) {
      var c = this.maxSelectedTime;
      isNaN(c) || (b != this.endTime && b - a > c && (a = b - c), a != this.startTime && b - a > c && (b = a + c));
      var d = this.minSelectedTime;

      if (0 < d && b - a < d) {
        var g = Math.round(a + (b - a) / 2),
            d = Math.round(d / 2);
        a = g - d;
        b = g + d;
      }

      d = this.chartData;
      g = this.categoryAxis;

      if (e.ifArray(d) && (a != this.startTime || b != this.endTime)) {
        var h = g.minDuration(),
            f = this.firstTime,
            l = this.lastTime;
        a || (a = f, isNaN(c) || (a = l - c));
        b || (b = l);
        a > l && (a = l);
        b < f && (b = f);
        a < f && (a = f);
        b > l && (b = l);
        b < a && (b = a + h);
        b - a < h / 5 && (b < l ? b = a + h / 5 : a = b - h / 5);
        this.startTime = a;
        this.endTime = b;
        c = d.length - 1;
        h = this.getClosestIndex(d, "time", a, !0, 0, c);
        d = this.getClosestIndex(d, "time", b, !1, h, c);
        g.timeZoom(a, b);
        g.zoom(h, d);
        this.start = e.fitToBounds(h, 0, c);
        this.end = e.fitToBounds(d, 0, c);
        this.zoomAxesAndGraphs();
        this.zoomScrollbar();
        this.fixCursor();
        this.showZB();
        this.syncGrid();
        this.updateColumnsDepth();
        this.dispatchTimeZoomEvent();
      }
    },
    showZB: function showZB() {
      var a,
          b = this.categoryAxis;
      b && b.parseDates && !b.equalSpacing && (this.startTime > this.firstTime && (a = !0), this.endTime < this.lastTime && (a = !0));
      0 < this.start && (a = !0);
      this.end < this.chartData.length - 1 && (a = !0);
      if (b = this.valueAxes) b = b[0], isNaN(b.relativeStart) || (0 !== e.roundTo(b.relativeStart, 3) && (a = !0), 1 != e.roundTo(b.relativeEnd, 3) && (a = !0));
      e.AmSerialChart.base.showZB.call(this, a);
    },
    updateAfterValueZoom: function updateAfterValueZoom() {
      e.AmSerialChart.base.updateAfterValueZoom.call(this);
      this.updateColumnsDepth();
    },
    indexZoom: function indexZoom(a, b) {
      var c = this.maxSelectedSeries,
          d = !1;
      isNaN(c) || (b != this.end && b - a > c && (a = b - c, d = !0), a != this.start && b - a > c && (b = a + c, d = !0));

      if (d && (d = this.chartScrollbar) && d.dragger) {
        var g = d.dragger.getBBox();
        d.maxWidth = g.width;
        d.maxHeight = g.height;
      }

      if (a != this.start || b != this.end) d = this.chartData.length - 1, isNaN(a) && (a = 0, isNaN(c) || (a = d - c)), isNaN(b) && (b = d), b < a && (b = a), b > d && (b = d), a > d && (a = d - 1), 0 > a && (a = 0), this.start = a, this.end = b, this.categoryAxis.zoom(a, b), this.zoomAxesAndGraphs(), this.zoomScrollbar(), this.fixCursor(), 0 !== a || b != this.chartData.length - 1 ? this.showZB(!0) : this.showZB(!1), this.syncGrid(), this.updateColumnsDepth(), this.dispatchIndexZoomEvent();
    },
    updateGraphs: function updateGraphs() {
      e.AmSerialChart.base.updateGraphs.call(this);
      var a = this.graphs,
          b;

      for (b = 0; b < a.length; b++) {
        var c = a[b];
        c.columnWidthReal = this.columnWidth;
        c.categoryAxis = this.categoryAxis;
        e.isString(c.fillToGraph) && (c.fillToGraph = this.graphsById[c.fillToGraph]);
      }
    },
    zoomAxesAndGraphs: function zoomAxesAndGraphs() {
      e.AmSerialChart.base.zoomAxesAndGraphs.call(this);
      this.updateColumnsDepth();
    },
    updateColumnsDepth: function updateColumnsDepth() {
      if (0 !== this.depth3D || 0 !== this.angle) {
        var a,
            b = this.graphs,
            c;
        this.columnsArray = [];

        for (a = 0; a < b.length; a++) {
          c = b[a];
          var d = c.columnsArray;

          if (d) {
            var g;

            for (g = 0; g < d.length; g++) {
              this.columnsArray.push(d[g]);
            }
          }
        }

        this.columnsArray.sort(this.compareDepth);

        if (0 < this.columnsArray.length) {
          b = this.columnsSet;
          d = this.container.set();
          this.columnSet.push(d);

          for (a = 0; a < this.columnsArray.length; a++) {
            d.push(this.columnsArray[a].column.set);
          }

          c && d.translate(c.x, c.y);
          this.columnsSet = d;
          e.remove(b);
        }
      }
    },
    compareDepth: function compareDepth(a, b) {
      return a.depth > b.depth ? 1 : -1;
    },
    zoomScrollbar: function zoomScrollbar() {
      var a = this.chartScrollbar,
          b = this.categoryAxis;

      if (a) {
        if (!this.zoomedByScrollbar) {
          var c = a.dragger;
          c && c.stop();
        }

        this.zoomedByScrollbar = !1;
        b.parseDates && !b.equalSpacing ? a.timeZoom(this.startTime, this.endTime) : a.zoom(this.start, this.end);
      }

      this.zoomValueScrollbar(this.valueScrollbar);
    },
    updateTrendLines: function updateTrendLines() {
      var a = this.trendLines,
          b;

      for (b = 0; b < a.length; b++) {
        var c = a[b],
            c = e.processObject(c, e.TrendLine, this.theme);
        a[b] = c;
        c.chart = this;
        c.id || (c.id = "trendLineAuto" + b + "_" + new Date().getTime());
        e.isString(c.valueAxis) && (c.valueAxis = this.getValueAxisById(c.valueAxis));
        c.valueAxis || (c.valueAxis = this.valueAxes[0]);
        c.categoryAxis = this.categoryAxis;
      }
    },
    countColumns: function countColumns() {
      var a = 0,
          b = this.valueAxes.length,
          c = this.graphs.length,
          d,
          g,
          e = !1,
          f,
          l;

      for (l = 0; l < b; l++) {
        g = this.valueAxes[l];
        var k = g.stackType,
            m = 0;
        if ("100%" == k || "regular" == k) for (e = !1, f = 0; f < c; f++) {
          d = this.graphs[f], d.tcc = 1, d.valueAxis == g && "column" == d.type && (!e && d.stackable && (a++, e = !0), (!d.stackable && d.clustered || d.newStack && 0 !== m) && a++, d.columnIndex = a - 1, d.clustered || (d.columnIndex = 0), m++);
        }

        if ("none" == k || "3d" == k) {
          e = !1;

          for (f = 0; f < c; f++) {
            d = this.graphs[f], d.valueAxis == g && "column" == d.type && (d.clustered ? (d.tcc = 1, d.newStack && (a = 0), d.hidden || (d.columnIndex = a, a++)) : d.hidden || (e = !0, d.tcc = 1, d.columnIndex = 0));
          }

          e && 0 === a && (a = 1);
        }

        if ("3d" == k) {
          g = 1;

          for (l = 0; l < c; l++) {
            d = this.graphs[l], d.newStack && g++, d.depthCount = g, d.tcc = a;
          }

          a = g;
        }
      }

      return a;
    },
    parseData: function parseData() {
      e.AmSerialChart.base.parseData.call(this);
      this.parseSerialData(this.dataProvider);
    },
    getCategoryIndexByValue: function getCategoryIndexByValue(a) {
      var b = this.chartData,
          c;

      for (c = 0; c < b.length; c++) {
        if (b[c].category == a) return c;
      }
    },
    handleScrollbarZoom: function handleScrollbarZoom(a) {
      this.zoomedByScrollbar = !0;
      this.zoom(a.start, a.end);
    },
    dispatchTimeZoomEvent: function dispatchTimeZoomEvent() {
      if (this.drawGraphs && (this.prevStartTime != this.startTime || this.prevEndTime != this.endTime)) {
        var a = {
          type: "zoomed"
        };
        a.startDate = new Date(this.startTime);
        a.endDate = new Date(this.endTime);
        a.startIndex = this.start;
        a.endIndex = this.end;
        this.startIndex = this.start;
        this.endIndex = this.end;
        this.startDate = a.startDate;
        this.endDate = a.endDate;
        this.prevStartTime = this.startTime;
        this.prevEndTime = this.endTime;
        var b = this.categoryAxis,
            c = e.extractPeriod(b.minPeriod).period,
            b = b.dateFormatsObject[c];
        a.startValue = e.formatDate(a.startDate, b, this);
        a.endValue = e.formatDate(a.endDate, b, this);
        a.chart = this;
        a.target = this;
        this.fire(a);
      }
    },
    dispatchIndexZoomEvent: function dispatchIndexZoomEvent() {
      if (this.drawGraphs && (this.prevStartIndex != this.start || this.prevEndIndex != this.end)) {
        this.startIndex = this.start;
        this.endIndex = this.end;
        var a = this.chartData;

        if (e.ifArray(a) && !isNaN(this.start) && !isNaN(this.end)) {
          var b = {
            chart: this,
            target: this,
            type: "zoomed"
          };
          b.startIndex = this.start;
          b.endIndex = this.end;
          b.startValue = a[this.start].category;
          b.endValue = a[this.end].category;
          this.categoryAxis.parseDates && (this.startTime = a[this.start].time, this.endTime = a[this.end].time, b.startDate = new Date(this.startTime), b.endDate = new Date(this.endTime));
          this.prevStartIndex = this.start;
          this.prevEndIndex = this.end;
          this.fire(b);
        }
      }
    },
    updateLegendValues: function updateLegendValues() {
      this.legend && this.legend.updateValues();
    },
    getClosestIndex: function getClosestIndex(a, b, c, d, g, e) {
      0 > g && (g = 0);
      e > a.length - 1 && (e = a.length - 1);
      var f = g + Math.round((e - g) / 2),
          l = a[f][b];
      return c == l ? f : 1 >= e - g ? d ? g : Math.abs(a[g][b] - c) < Math.abs(a[e][b] - c) ? g : e : c == l ? f : c < l ? this.getClosestIndex(a, b, c, d, g, f) : this.getClosestIndex(a, b, c, d, f, e);
    },
    zoomToIndexes: function zoomToIndexes(a, b) {
      var c = this.chartData;

      if (c) {
        var d = c.length;
        0 < d && (0 > a && (a = 0), b > d - 1 && (b = d - 1), d = this.categoryAxis, d.parseDates && !d.equalSpacing ? this.zoom(c[a].time, this.getEndTime(c[b].time)) : this.zoom(a, b));
      }
    },
    zoomToDates: function zoomToDates(a, b) {
      var c = this.chartData;
      if (c) if (this.categoryAxis.equalSpacing) {
        var d = this.getClosestIndex(c, "time", a.getTime(), !0, 0, c.length);
        b = e.resetDateToMin(b, this.categoryAxis.minPeriod, 1);
        c = this.getClosestIndex(c, "time", b.getTime(), !1, 0, c.length);
        this.zoom(d, c);
      } else this.zoom(a.getTime(), b.getTime());
    },
    zoomToCategoryValues: function zoomToCategoryValues(a, b) {
      this.chartData && this.zoom(this.getCategoryIndexByValue(a), this.getCategoryIndexByValue(b));
    },
    formatPeriodString: function formatPeriodString(a, b) {
      if (b) {
        b.periodDataItem = {};
        b.periodPercentDataItem = {};
        var c = ["value", "open", "low", "high", "close"],
            d = "value open low high close average sum count".split(" "),
            g = b.valueAxis,
            h = this.chartData,
            f = b.numberFormatter;
        f || (f = this.nf);

        for (var l = 0; l < c.length; l++) {
          for (var k = c[l], m = 0, p = 0, n = 0, u = 0, v, x, E, t, r, B, q, w, y, C, F = this.start; F <= this.end; F++) {
            var D = h[F];

            if (D) {
              var A = D.axes[g.id].graphs[b.id];

              if (A) {
                if (A.values) {
                  var z = A.values[k],
                      D = D.x.categoryAxis;

                  if (this.rotate) {
                    if (0 > D || D > A.graph.height) z = NaN;
                  } else if (0 > D || D > A.graph.width) z = NaN;

                  if (!isNaN(z)) {
                    isNaN(v) && (v = z);
                    x = z;
                    if (isNaN(E) || E > z) E = z;
                    if (isNaN(t) || t < z) t = z;
                    r = e.getDecimals(m);
                    D = e.getDecimals(z);
                    m += z;
                    m = e.roundTo(m, Math.max(r, D));
                    p++;
                    r = m / p;
                  }
                }

                if (A.percents && (A = A.percents[k], !isNaN(A))) {
                  isNaN(B) && (B = A);
                  q = A;
                  if (isNaN(w) || w > A) w = A;
                  if (isNaN(y) || y < A) y = A;
                  C = e.getDecimals(n);
                  z = e.getDecimals(A);
                  n += A;
                  n = e.roundTo(n, Math.max(C, z));
                  u++;
                  C = n / u;
                }
              }
            }
          }

          m = {
            open: v,
            close: x,
            high: t,
            low: E,
            average: r,
            sum: m,
            count: p
          };
          n = {
            open: B,
            close: q,
            high: y,
            low: w,
            average: C,
            sum: n,
            count: u
          };
          a = e.formatValue(a, m, d, f, k + "\\.", this.usePrefixes, this.prefixesOfSmallNumbers, this.prefixesOfBigNumbers);
          a = e.formatValue(a, n, d, this.pf, "percents\\." + k + "\\.");
          b.periodDataItem[k] = m;
          b.periodPercentDataItem[k] = n;
        }
      }

      return a = e.cleanFromEmpty(a);
    },
    formatString: function formatString(a, b, c) {
      if (b) {
        var d = b.graph;

        if (void 0 !== a) {
          if (-1 != a.indexOf("[[category]]")) {
            var g = b.serialDataItem.category;

            if (this.categoryAxis.parseDates) {
              var h = this.balloonDateFormat,
                  f = this.chartCursor;
              f && f.categoryBalloonDateFormat && (h = f.categoryBalloonDateFormat);
              h = e.formatDate(g, h, this);
              -1 != h.indexOf("fff") && (h = e.formatMilliseconds(h, g));
              g = h;
            }

            a = a.replace(/\[\[category\]\]/g, String(g.replace("$", "$$$")));
          }

          g = d.numberFormatter;
          g || (g = this.nf);
          h = b.graph.valueAxis;
          (f = h.duration) && !isNaN(b.values.value) && (f = e.formatDuration(b.values.value, f, "", h.durationUnits, h.maxInterval, g), a = a.replace(RegExp("\\[\\[value\\]\\]", "g"), f));
          "date" == h.type && (h = e.formatDate(new Date(b.values.value), d.dateFormat, this), f = RegExp("\\[\\[value\\]\\]", "g"), a = a.replace(f, h), h = e.formatDate(new Date(b.values.open), d.dateFormat, this), f = RegExp("\\[\\[open\\]\\]", "g"), a = a.replace(f, h));
          d = "value open low high close total".split(" ");
          h = this.pf;
          a = e.formatValue(a, b.percents, d, h, "percents\\.");
          a = e.formatValue(a, b.values, d, g, "", this.usePrefixes, this.prefixesOfSmallNumbers, this.prefixesOfBigNumbers);
          a = e.formatValue(a, b.values, ["percents"], h);
          -1 != a.indexOf("[[") && (a = e.formatDataContextValue(a, b.dataContext));
          -1 != a.indexOf("[[") && b.graph.customData && (a = e.formatDataContextValue(a, b.graph.customData));
          a = e.AmSerialChart.base.formatString.call(this, a, b, c);
        }

        return a;
      }
    },
    updateChartCursor: function updateChartCursor() {
      e.AmSerialChart.base.updateChartCursor.call(this);
      var a = this.chartCursor,
          b = this.categoryAxis;

      if (a) {
        var c = a.categoryBalloonAlpha,
            d = a.categoryBalloonColor,
            g = a.color;
        void 0 === d && (d = a.cursorColor);
        var h = a.valueZoomable,
            f = a.zoomable,
            l = a.valueLineEnabled;
        this.rotate ? (a.vLineEnabled = l, a.hZoomEnabled = h, a.vZoomEnabled = f) : (a.hLineEnabled = l, a.vZoomEnabled = h, a.hZoomEnabled = f);
        if (a.valueLineBalloonEnabled) for (l = 0; l < this.valueAxes.length; l++) {
          h = this.valueAxes[l], (f = h.balloon) || (f = {}), f = e.extend(f, this.balloon, !0), f.fillColor = d, f.balloonColor = d, f.fillAlpha = c, f.borderColor = d, f.color = g, h.balloon = f;
        } else for (f = 0; f < this.valueAxes.length; f++) {
          h = this.valueAxes[f], h.balloon && (h.balloon = null);
        }
        b && (b.balloonTextFunction = a.categoryBalloonFunction, a.categoryLineAxis = b, b.balloonText = a.categoryBalloonText, a.categoryBalloonEnabled && ((f = b.balloon) || (f = {}), f = e.extend(f, this.balloon, !0), f.fillColor = d, f.balloonColor = d, f.fillAlpha = c, f.borderColor = d, f.color = g, b.balloon = f), b.balloon && (b.balloon.enabled = a.categoryBalloonEnabled));
      }
    },
    addChartScrollbar: function addChartScrollbar(a) {
      e.callMethod("destroy", [this.chartScrollbar]);
      a && (a.chart = this, this.listenTo(a, "zoomed", this.handleScrollbarZoom));
      this.rotate ? void 0 === a.width && (a.width = a.scrollbarHeight) : void 0 === a.height && (a.height = a.scrollbarHeight);
      a.gridAxis = this.categoryAxis;
      this.chartScrollbar = a;
    },
    addValueScrollbar: function addValueScrollbar(a) {
      e.callMethod("destroy", [this.valueScrollbar]);
      a && (a.chart = this, this.listenTo(a, "zoomed", this.handleScrollbarValueZoom), this.listenTo(a, "zoomStarted", this.handleCursorZoomStarted));
      var b = a.scrollbarHeight;
      this.rotate ? void 0 === a.height && (a.height = b) : void 0 === a.width && (a.width = b);
      a.gridAxis || (a.gridAxis = this.valueAxes[0]);
      a.valueAxes = this.valueAxes;
      this.valueScrollbar = a;
    },
    removeChartScrollbar: function removeChartScrollbar() {
      e.callMethod("destroy", [this.chartScrollbar]);
      this.chartScrollbar = null;
    },
    removeValueScrollbar: function removeValueScrollbar() {
      e.callMethod("destroy", [this.valueScrollbar]);
      this.valueScrollbar = null;
    },
    handleReleaseOutside: function handleReleaseOutside(a) {
      e.AmSerialChart.base.handleReleaseOutside.call(this, a);
      e.callMethod("handleReleaseOutside", [this.chartScrollbar, this.valueScrollbar]);
    },
    update: function update() {
      e.AmSerialChart.base.update.call(this);
      this.chartScrollbar && this.chartScrollbar.update && this.chartScrollbar.update();
      this.valueScrollbar && this.valueScrollbar.update && this.valueScrollbar.update();
    },
    processScrollbars: function processScrollbars() {
      e.AmSerialChart.base.processScrollbars.call(this);
      var a = this.valueScrollbar;
      a && (a = e.processObject(a, e.ChartScrollbar, this.theme), a.id = "valueScrollbar", this.addValueScrollbar(a));
    },
    handleValueAxisZoom: function handleValueAxisZoom(a) {
      this.handleValueAxisZoomReal(a, this.valueAxes);
    },
    zoomOut: function zoomOut() {
      e.AmSerialChart.base.zoomOut.call(this);
      this.zoom();
      this.syncGrid();
    },
    getNextItem: function getNextItem(a) {
      var b = a.index,
          c = this.chartData,
          d = a.graph;
      if (b + 1 < c.length) for (b += 1; b < c.length; b++) {
        if (a = c[b]) if (a = a.axes[d.valueAxis.id].graphs[d.id], !isNaN(a.y)) return a;
      }
    },
    handleCursorZoomReal: function handleCursorZoomReal(a, b, c, d, g) {
      var e = g.target,
          f,
          l;
      this.rotate ? (isNaN(a) || isNaN(b) || this.relativeZoomValueAxes(this.valueAxes, a, b) && this.updateAfterValueZoom(), e.vZoomEnabled && (f = g.start, l = g.end)) : (isNaN(c) || isNaN(d) || this.relativeZoomValueAxes(this.valueAxes, c, d) && this.updateAfterValueZoom(), e.hZoomEnabled && (f = g.start, l = g.end));
      isNaN(f) || isNaN(l) || (a = this.categoryAxis, a.parseDates && !a.equalSpacing ? this.zoomToDates(new Date(f), new Date(l)) : this.zoomToIndexes(f, l));
    },
    handleCursorZoomStarted: function handleCursorZoomStarted() {
      var a = this.valueAxes;

      if (a) {
        var a = a[0],
            b = a.relativeStart,
            c = a.relativeEnd;
        a.reversed && (b = 1 - a.relativeEnd, c = 1 - a.relativeStart);
        this.rotate ? (this.startX0 = b, this.endX0 = c) : (this.startY0 = b, this.endY0 = c);
      }

      this.categoryAxis && (this.start0 = this.start, this.end0 = this.end, this.startTime0 = this.startTime, this.endTime0 = this.endTime);
    },
    fixCursor: function fixCursor() {
      this.chartCursor && this.chartCursor.fixPosition();
      this.prevCursorItem = null;
    },
    handleCursorMove: function handleCursorMove(a) {
      e.AmSerialChart.base.handleCursorMove.call(this, a);
      var b = a.target,
          c = this.categoryAxis;
      if (a.panning) this.handleCursorHide(a);else if (this.chartData && !b.isHidden) {
        var d = this.graphs;

        if (d) {
          var g;
          g = c.xToIndex(this.rotate ? a.y : a.x);

          if (g = this.chartData[g]) {
            var h, f, l, k;

            if (b.oneBalloonOnly && b.valueBalloonsEnabled) {
              var m = Infinity;

              for (h = 0; h < d.length; h++) {
                if (f = d[h], f.balloon.enabled && f.showBalloon && !f.hidden) {
                  l = f.valueAxis.id;
                  l = g.axes[l].graphs[f.id];
                  if (b.showNextAvailable && isNaN(l.y) && (l = this.getNextItem(l), !l)) continue;
                  l = l.y;
                  "top" == f.showBalloonAt && (l = 0);
                  "bottom" == f.showBalloonAt && (l = this.height);
                  var p = b.mouseX,
                      n = b.mouseY;
                  l = this.rotate ? Math.abs(p - l) : Math.abs(n - l);
                  l < m && (m = l, k = f);
                }
              }

              b.mostCloseGraph = k;
            }

            if (this.prevCursorItem != g || k != this.prevMostCloseGraph) {
              m = [];

              for (h = 0; h < d.length; h++) {
                f = d[h], l = f.valueAxis.id, l = g.axes[l].graphs[f.id], b.showNextAvailable && isNaN(l.y) && (l = this.getNextItem(l)), k && f != k ? (f.showGraphBalloon(l, b.pointer, !1, b.graphBulletSize, b.graphBulletAlpha), f.balloon.hide(0)) : b.valueBalloonsEnabled ? (f.balloon.showBullet = b.bulletsEnabled, f.balloon.bulletSize = b.bulletSize / 2, a.hideBalloons || (f.showGraphBalloon(l, b.pointer, !1, b.graphBulletSize, b.graphBulletAlpha), f.balloon.set && m.push({
                  balloon: f.balloon,
                  y: f.balloon.pointToY
                }))) : (f.currentDataItem = l, f.resizeBullet(l, b.graphBulletSize, b.graphBulletAlpha));
              }

              b.avoidBalloonOverlapping && this.arrangeBalloons(m);
              this.prevCursorItem = g;
            }

            this.prevMostCloseGraph = k;
          }
        }

        c.showBalloon(a.x, a.y, b.categoryBalloonDateFormat, a.skip);
        this.updateLegendValues();
      }
    },
    handleCursorHide: function handleCursorHide(a) {
      e.AmSerialChart.base.handleCursorHide.call(this, a);
      a = this.categoryAxis;
      this.prevCursorItem = null;
      this.updateLegendValues();
      a && a.hideBalloon();
      a = this.graphs;
      var b;

      for (b = 0; b < a.length; b++) {
        a[b].currentDataItem = null;
      }
    },
    handleCursorPanning: function handleCursorPanning(a) {
      var b = a.target,
          c,
          d = a.deltaX,
          g = a.deltaY,
          h = a.delta2X,
          f = a.delta2Y;
      a = !1;

      if (this.rotate) {
        isNaN(h) && (h = d, a = !0);
        var l = this.endX0;
        c = this.startX0;
        var k = l - c,
            l = l - k * h,
            m = k;
        a || (m = 0);
        a = e.fitToBounds(c - k * d, 0, 1 - m);
      } else isNaN(f) && (f = g, a = !0), l = this.endY0, c = this.startY0, k = l - c, l += k * g, m = k, a || (m = 0), a = e.fitToBounds(c + k * f, 0, 1 - m);

      c = e.fitToBounds(l, m, 1);
      var p;
      b.valueZoomable && (p = this.relativeZoomValueAxes(this.valueAxes, a, c));
      var n;
      c = this.categoryAxis;
      this.rotate && (d = g, h = f);
      a = !1;
      isNaN(h) && (h = d, a = !0);
      if (b.zoomable && (0 < Math.abs(d) || 0 < Math.abs(h))) if (c.parseDates && !c.equalSpacing) {
        if (f = this.startTime0, g = this.endTime0, c = g - f, h *= c, k = this.firstTime, l = this.lastTime, m = c, a || (m = 0), a = Math.round(e.fitToBounds(f - c * d, k, l - m)), h = Math.round(e.fitToBounds(g - h, k + m, l)), this.startTime != a || this.endTime != h) n = {
          chart: this,
          target: b,
          type: "zoomed",
          start: a,
          end: h
        }, this.skipZoomed = !0, b.fire(n), this.zoom(a, h), n = !0;
      } else if (f = this.start0, g = this.end0, c = g - f, d = Math.round(c * d), h = Math.round(c * h), k = this.chartData.length - 1, a || (c = 0), a = e.fitToBounds(f - d, 0, k - c), c = e.fitToBounds(g - h, c, k), this.start != a || this.end != c) this.skipZoomed = !0, b.fire({
        chart: this,
        target: b,
        type: "zoomed",
        start: a,
        end: c
      }), this.zoom(a, c), n = !0;
      !n && p && this.updateAfterValueZoom();
    },
    arrangeBalloons: function arrangeBalloons(a) {
      var b = this.plotAreaHeight;
      a.sort(this.compareY);
      var c,
          d,
          e,
          h = this.plotAreaWidth,
          f = a.length;

      for (c = 0; c < f; c++) {
        d = a[c].balloon, d.setBounds(0, 0, h, b), d.restorePrevious(), d.draw(), b = d.yPos - 3;
      }

      a.reverse();

      for (c = 0; c < f; c++) {
        d = a[c].balloon;
        var b = d.bottom,
            l = d.bottom - d.yPos;
        0 < c && b - l < e + 3 && (d.setBounds(0, e + 3, h, e + l + 3), d.restorePrevious(), d.draw());
        d.set && d.set.show();
        e = d.bottom;
      }
    },
    compareY: function compareY(a, b) {
      return a.y < b.y ? 1 : -1;
    }
  });
})();

(function () {
  var e = window.AmCharts;
  e.Cuboid = e.Class({
    construct: function construct(a, b, c, d, e, h, f, l, k, m, p, n, u, v, x, E, t) {
      this.set = a.set();
      this.container = a;
      this.h = Math.round(c);
      this.w = Math.round(b);
      this.dx = d;
      this.dy = e;
      this.colors = h;
      this.alpha = f;
      this.bwidth = l;
      this.bcolor = k;
      this.balpha = m;
      this.dashLength = v;
      this.topRadius = E;
      this.pattern = x;
      this.rotate = u;
      this.bcn = t;
      u ? 0 > b && 0 === p && (p = 180) : 0 > c && 270 == p && (p = 90);
      this.gradientRotation = p;
      0 === d && 0 === e && (this.cornerRadius = n);
      this.draw();
    },
    draw: function draw() {
      var a = this.set;
      a.clear();
      var b = this.container,
          c = b.chart,
          d = this.w,
          g = this.h,
          h = this.dx,
          f = this.dy,
          l = this.colors,
          k = this.alpha,
          m = this.bwidth,
          p = this.bcolor,
          n = this.balpha,
          u = this.gradientRotation,
          v = this.cornerRadius,
          x = this.dashLength,
          E = this.pattern,
          t = this.topRadius,
          r = this.bcn,
          B = l,
          q = l;
      "object" == _typeof(l) && (B = l[0], q = l[l.length - 1]);
      var w,
          y,
          C,
          F,
          D,
          A,
          z,
          L,
          M,
          Q = k;
      E && (k = 0);
      var G,
          H,
          I,
          J,
          K = this.rotate;
      if (0 < Math.abs(h) || 0 < Math.abs(f)) if (isNaN(t)) z = q, q = e.adjustLuminosity(B, -.2), q = e.adjustLuminosity(B, -.2), w = e.polygon(b, [0, h, d + h, d, 0], [0, f, f, 0, 0], q, k, 1, p, 0, u), 0 < n && (M = e.line(b, [0, h, d + h], [0, f, f], p, n, m, x)), y = e.polygon(b, [0, 0, d, d, 0], [0, g, g, 0, 0], q, k, 1, p, 0, u), y.translate(h, f), 0 < n && (C = e.line(b, [h, h], [f, f + g], p, n, m, x)), F = e.polygon(b, [0, 0, h, h, 0], [0, g, g + f, f, 0], q, k, 1, p, 0, u), D = e.polygon(b, [d, d, d + h, d + h, d], [0, g, g + f, f, 0], q, k, 1, p, 0, u), 0 < n && (A = e.line(b, [d, d + h, d + h, d], [0, f, g + f, g], p, n, m, x)), q = e.adjustLuminosity(z, .2), z = e.polygon(b, [0, h, d + h, d, 0], [g, g + f, g + f, g, g], q, k, 1, p, 0, u), 0 < n && (L = e.line(b, [0, h, d + h], [g, g + f, g + f], p, n, m, x));else {
        var N, O, P;
        K ? (N = g / 2, q = h / 2, P = g / 2, O = d + h / 2, H = Math.abs(g / 2), G = Math.abs(h / 2)) : (q = d / 2, N = f / 2, O = d / 2, P = g + f / 2 + 1, G = Math.abs(d / 2), H = Math.abs(f / 2));
        I = G * t;
        J = H * t;
        .1 < G && .1 < G && (w = e.circle(b, G, B, k, m, p, n, !1, H), w.translate(q, N));
        .1 < I && .1 < I && (z = e.circle(b, I, e.adjustLuminosity(B, .5), k, m, p, n, !1, J), z.translate(O, P));
      }
      k = Q;
      1 > Math.abs(g) && (g = 0);
      1 > Math.abs(d) && (d = 0);
      !isNaN(t) && (0 < Math.abs(h) || 0 < Math.abs(f)) ? (l = [B], l = {
        fill: l,
        stroke: p,
        "stroke-width": m,
        "stroke-opacity": n,
        "fill-opacity": k
      }, K ? (k = "M0,0 L" + d + "," + (g / 2 - g / 2 * t), m = " B", 0 < d && (m = " A"), e.VML ? (k += m + Math.round(d - I) + "," + Math.round(g / 2 - J) + "," + Math.round(d + I) + "," + Math.round(g / 2 + J) + "," + d + ",0," + d + "," + g, k = k + (" L0," + g) + (m + Math.round(-G) + "," + Math.round(g / 2 - H) + "," + Math.round(G) + "," + Math.round(g / 2 + H) + ",0," + g + ",0,0")) : (k += "A" + I + "," + J + ",0,0,0," + d + "," + (g - g / 2 * (1 - t)) + "L0," + g, k += "A" + G + "," + H + ",0,0,1,0,0"), G = 90) : (m = d / 2 - d / 2 * t, k = "M0,0 L" + m + "," + g, e.VML ? (k = "M0,0 L" + m + "," + g, m = " B", 0 > g && (m = " A"), k += m + Math.round(d / 2 - I) + "," + Math.round(g - J) + "," + Math.round(d / 2 + I) + "," + Math.round(g + J) + ",0," + g + "," + d + "," + g, k += " L" + d + ",0", k += m + Math.round(d / 2 + G) + "," + Math.round(H) + "," + Math.round(d / 2 - G) + "," + Math.round(-H) + "," + d + ",0,0,0") : (k += "A" + I + "," + J + ",0,0,0," + (d - d / 2 * (1 - t)) + "," + g + "L" + d + ",0", k += "A" + G + "," + H + ",0,0,1,0,0"), G = 180), b = b.path(k).attr(l), b.gradient("linearGradient", [B, e.adjustLuminosity(B, -.3), e.adjustLuminosity(B, -.3), B], G), K ? b.translate(h / 2, 0) : b.translate(0, f / 2)) : b = 0 === g ? e.line(b, [0, d], [0, 0], p, n, m, x) : 0 === d ? e.line(b, [0, 0], [0, g], p, n, m, x) : 0 < v ? e.rect(b, d, g, l, k, m, p, n, v, u, x) : e.polygon(b, [0, 0, d, d, 0], [0, g, g, 0, 0], l, k, m, p, n, u, !1, x);
      d = isNaN(t) ? 0 > g ? [w, M, y, C, F, D, A, z, L, b] : [z, L, y, C, F, D, w, M, A, b] : K ? 0 < d ? [w, b, z] : [z, b, w] : 0 > g ? [w, b, z] : [z, b, w];
      e.setCN(c, b, r + "front");
      e.setCN(c, y, r + "back");
      e.setCN(c, z, r + "top");
      e.setCN(c, w, r + "bottom");
      e.setCN(c, F, r + "left");
      e.setCN(c, D, r + "right");

      for (w = 0; w < d.length; w++) {
        if (y = d[w]) a.push(y), e.setCN(c, y, r + "element");
      }

      E && b.pattern(E, NaN, c.path);
    },
    width: function width(a) {
      isNaN(a) && (a = 0);
      this.w = Math.round(a);
      this.draw();
    },
    height: function height(a) {
      isNaN(a) && (a = 0);
      this.h = Math.round(a);
      this.draw();
    },
    animateHeight: function animateHeight(a, b) {
      var c = this;
      c.animationFinished = !1;
      c.easing = b;
      c.totalFrames = a * e.updateRate;
      c.rh = c.h;
      c.frame = 0;
      c.height(1);
      setTimeout(function () {
        c.updateHeight.call(c);
      }, 1E3 / e.updateRate);
    },
    updateHeight: function updateHeight() {
      var a = this;
      a.frame++;
      var b = a.totalFrames;
      a.frame <= b ? (b = a.easing(0, a.frame, 1, a.rh - 1, b), a.height(b), window.requestAnimationFrame ? window.requestAnimationFrame(function () {
        a.updateHeight.call(a);
      }) : setTimeout(function () {
        a.updateHeight.call(a);
      }, 1E3 / e.updateRate)) : (a.height(a.rh), a.animationFinished = !0);
    },
    animateWidth: function animateWidth(a, b) {
      var c = this;
      c.animationFinished = !1;
      c.easing = b;
      c.totalFrames = a * e.updateRate;
      c.rw = c.w;
      c.frame = 0;
      c.width(1);
      setTimeout(function () {
        c.updateWidth.call(c);
      }, 1E3 / e.updateRate);
    },
    updateWidth: function updateWidth() {
      var a = this;
      a.frame++;
      var b = a.totalFrames;
      a.frame <= b ? (b = a.easing(0, a.frame, 1, a.rw - 1, b), a.width(b), window.requestAnimationFrame ? window.requestAnimationFrame(function () {
        a.updateWidth.call(a);
      }) : setTimeout(function () {
        a.updateWidth.call(a);
      }, 1E3 / e.updateRate)) : (a.width(a.rw), a.animationFinished = !0);
    }
  });
})();

(function () {
  var e = window.AmCharts;
  e.CategoryAxis = e.Class({
    inherits: e.AxisBase,
    construct: function construct(a) {
      this.cname = "CategoryAxis";
      e.CategoryAxis.base.construct.call(this, a);
      this.minPeriod = "DD";
      this.equalSpacing = this.parseDates = !1;
      this.position = "bottom";
      this.startOnAxis = !1;
      this.gridPosition = "middle";
      this.safeDistance = 30;
      this.stickBalloonToCategory = !1;
      e.applyTheme(this, a, this.cname);
    },
    draw: function draw() {
      e.CategoryAxis.base.draw.call(this);
      this.generateDFObject();
      var a = this.chart.chartData;
      this.data = a;
      this.labelRotationR = this.labelRotation;
      this.type = null;

      if (e.ifArray(a)) {
        var b,
            c = this.chart;
        "scrollbar" != this.id ? (e.setCN(c, this.set, "category-axis"), e.setCN(c, this.labelsSet, "category-axis"), e.setCN(c, this.axisLine.axisSet, "category-axis")) : this.bcn = this.id + "-";
        var d = this.start,
            g = this.labelFrequency,
            h = 0,
            f = this.end - d + 1,
            l = this.gridCountR,
            k = this.showFirstLabel,
            m = this.showLastLabel,
            p,
            n = "",
            n = e.extractPeriod(this.minPeriod),
            u = e.getPeriodDuration(n.period, n.count),
            v,
            x,
            E,
            t,
            r,
            B = this.rotate,
            q = this.firstDayOfWeek,
            w = this.boldPeriodBeginning;
        b = e.resetDateToMin(new Date(a[a.length - 1].time + 1.05 * u), this.minPeriod, 1, q).getTime();
        this.firstTime = c.firstTime;
        this.endTime > b && (this.endTime = b);
        r = this.minorGridEnabled;
        x = this.gridAlpha;
        var y = 0,
            C = 0;
        if (this.widthField) for (b = this.start; b <= this.end; b++) {
          if (t = this.data[b]) {
            var F = Number(this.data[b].dataContext[this.widthField]);
            isNaN(F) || (y += F, t.widthValue = F);
          }
        }
        if (this.parseDates && !this.equalSpacing) this.lastTime = a[a.length - 1].time, this.maxTime = e.resetDateToMin(new Date(this.lastTime + 1.05 * u), this.minPeriod, 1, q).getTime(), this.timeDifference = this.endTime - this.startTime, this.parseDatesDraw();else if (!this.parseDates) {
          if (this.cellWidth = this.getStepWidth(f), f < l && (l = f), h += this.start, this.stepWidth = this.getStepWidth(f), 0 < l) for (q = Math.floor(f / l), t = this.chooseMinorFrequency(q), f = h, f / 2 == Math.round(f / 2) && f--, 0 > f && (f = 0), w = 0, this.widthField && (f = this.start), this.end - f + 1 >= this.autoRotateCount && (this.labelRotationR = this.autoRotateAngle), b = f; b <= this.end + 2; b++) {
            l = !1;
            0 <= b && b < this.data.length ? (v = this.data[b], n = v.category, l = v.forceShow) : n = "";
            if (r && !isNaN(t)) {
              if (b / t == Math.round(b / t) || l) b / q == Math.round(b / q) || l || (this.gridAlpha = this.minorGridAlpha, n = void 0);else continue;
            } else if (b / q != Math.round(b / q) && !l) continue;
            f = this.getCoordinate(b - h);
            l = 0;
            "start" == this.gridPosition && (f -= this.cellWidth / 2, l = this.cellWidth / 2);
            p = !0;
            E = l;
            "start" == this.tickPosition && (E = 0, p = !1, l = 0);
            if (b == d && !k || b == this.end && !m) n = void 0;
            Math.round(w / g) != w / g && (n = void 0);
            w++;
            a = this.cellWidth;
            B && (a = NaN, this.ignoreAxisWidth || !c.autoMargins) && (a = "right" == this.position ? c.marginRight : c.marginLeft, a -= this.tickLength + 10);
            this.labelFunction && v && (n = this.labelFunction(n, v, this));
            n = e.fixBrakes(n);
            u = !1;
            this.boldLabels && (u = !0);
            b > this.end && "start" == this.tickPosition && (n = " ");
            this.rotate && this.inside && (l -= 2);
            isNaN(v.widthValue) || (v.percentWidthValue = v.widthValue / y * 100, a = this.rotate ? this.height * v.widthValue / y : this.width * v.widthValue / y, f = C, C += a, l = a / 2);
            p = new this.axisItemRenderer(this, f, n, p, a, l, void 0, u, E, !1, v.labelColor, v.className);
            p.serialDataItem = v;
            this.pushAxisItem(p);
            this.gridAlpha = x;
          }
        } else if (this.parseDates && this.equalSpacing) {
          h = this.start;
          this.startTime = this.data[this.start].time;
          this.endTime = this.data[this.end].time;
          this.timeDifference = this.endTime - this.startTime;
          b = this.choosePeriod(0);
          g = b.period;
          v = b.count;
          b = e.getPeriodDuration(g, v);
          b < u && (g = n.period, v = n.count, b = u);
          x = g;
          "WW" == x && (x = "DD");
          this.currentDateFormat = this.dateFormatsObject[x];
          this.stepWidth = this.getStepWidth(f);
          l = Math.ceil(this.timeDifference / b) + 1;
          n = e.resetDateToMin(new Date(this.startTime - b), g, v, q).getTime();
          this.cellWidth = this.getStepWidth(f);
          f = Math.round(n / b);
          d = -1;
          f / 2 == Math.round(f / 2) && (d = -2, n -= b);
          f = this.start;
          f / 2 == Math.round(f / 2) && f--;
          0 > f && (f = 0);
          C = this.end + 2;
          C >= this.data.length && (C = this.data.length);
          a = !1;
          a = !k;
          this.previousPos = -1E3;
          20 < this.labelRotationR && (this.safeDistance = 5);
          F = f;

          if (this.data[f].time != e.resetDateToMin(new Date(this.data[f].time), g, v, q).getTime()) {
            var u = 0,
                D = n;

            for (b = f; b < C; b++) {
              t = this.data[b].time, this.checkPeriodChange(g, v, t, D) && (u++, 2 <= u && (F = b, b = C), D = t);
            }
          }

          r && 1 < v && (t = this.chooseMinorFrequency(v), e.getPeriodDuration(g, t));
          if (0 < this.gridCountR) for (b = f; b < C; b++) {
            if (t = this.data[b].time, this.checkPeriodChange(g, v, t, n) && b >= F) {
              f = this.getCoordinate(b - this.start);
              r = !1;
              this.nextPeriod[x] && (r = this.checkPeriodChange(this.nextPeriod[x], 1, t, n, x)) && e.resetDateToMin(new Date(t), this.nextPeriod[x], 1, q).getTime() != t && (r = !1);
              u = !1;
              r && this.markPeriodChange ? (r = this.dateFormatsObject[this.nextPeriod[x]], u = !0) : r = this.dateFormatsObject[x];
              n = e.formatDate(new Date(t), r, c);
              if (b == d && !k || b == l && !m) n = " ";
              a ? a = !1 : (w || (u = !1), f - this.previousPos > this.safeDistance * Math.cos(this.labelRotationR * Math.PI / 180) && (this.labelFunction && (n = this.labelFunction(n, new Date(t), this, g, v, E)), this.boldLabels && (u = !0), p = new this.axisItemRenderer(this, f, n, void 0, void 0, void 0, void 0, u), r = p.graphics(), this.pushAxisItem(p), r = r.getBBox().width, e.isModern || (r -= f), this.previousPos = f + r));
              E = n = t;
            }
          }
        }

        for (b = k = 0; b < this.data.length; b++) {
          if (t = this.data[b]) this.parseDates && !this.equalSpacing ? (m = t.time, d = this.cellWidth, "MM" == this.minPeriod && (d = 864E5 * e.daysInMonth(new Date(m)) * this.stepWidth, t.cellWidth = d), m = Math.round((m - this.startTime) * this.stepWidth + d / 2)) : m = this.getCoordinate(b - h), t.x[this.id] = m;
        }

        if (this.widthField) for (b = this.start; b <= this.end; b++) {
          t = this.data[b], d = t.widthValue, t.percentWidthValue = d / y * 100, this.rotate ? (m = this.height * d / y / 2 + k, k = this.height * d / y + k) : (m = this.width * d / y / 2 + k, k = this.width * d / y + k), t.x[this.id] = m;
        }
        y = this.guides.length;

        for (b = 0; b < y; b++) {
          if (k = this.guides[b], q = q = q = r = d = NaN, m = k.above, k.toCategory && (q = c.getCategoryIndexByValue(k.toCategory), isNaN(q) || (d = this.getCoordinate(q - h), k.expand && (d += this.cellWidth / 2), p = new this.axisItemRenderer(this, d, "", !0, NaN, NaN, k), this.pushAxisItem(p, m))), k.category && (q = c.getCategoryIndexByValue(k.category), isNaN(q) || (r = this.getCoordinate(q - h), k.expand && (r -= this.cellWidth / 2), q = (d - r) / 2, p = new this.axisItemRenderer(this, r, k.label, !0, NaN, q, k), this.pushAxisItem(p, m))), w = c.dataDateFormat, k.toDate && (!w || k.toDate instanceof Date || (k.toDate = k.toDate.toString() + " |"), k.toDate = e.getDate(k.toDate, w), this.equalSpacing ? (q = c.getClosestIndex(this.data, "time", k.toDate.getTime(), !1, 0, this.data.length - 1), isNaN(q) || (d = this.getCoordinate(q - h))) : d = (k.toDate.getTime() - this.startTime) * this.stepWidth, p = new this.axisItemRenderer(this, d, "", !0, NaN, NaN, k), this.pushAxisItem(p, m)), k.date && (!w || k.date instanceof Date || (k.date = k.date.toString() + " |"), k.date = e.getDate(k.date, w), this.equalSpacing ? (q = c.getClosestIndex(this.data, "time", k.date.getTime(), !1, 0, this.data.length - 1), isNaN(q) || (r = this.getCoordinate(q - h))) : r = (k.date.getTime() - this.startTime) * this.stepWidth, q = (d - r) / 2, p = !0, k.toDate && (p = !1), p = "H" == this.orientation ? new this.axisItemRenderer(this, r, k.label, p, 2 * q, NaN, k) : new this.axisItemRenderer(this, r, k.label, !1, NaN, q, k), this.pushAxisItem(p, m)), k.balloonText && p && (q = p.label) && this.addEventListeners(q, k), 0 < d || 0 < r) {
            q = !1;

            if (this.rotate) {
              if (d < this.height || r < this.height) q = !0;
            } else if (d < this.width || r < this.width) q = !0;

            q && (d = new this.guideFillRenderer(this, r, d, k), r = d.graphics(), this.pushAxisItem(d, m), k.graphics = r, r.index = b, k.balloonText && this.addEventListeners(r, k));
          }
        }

        if (c = c.chartCursor) B ? c.fixHeight(this.cellWidth) : (c.fixWidth(this.cellWidth), c.fullWidth && this.balloon && (this.balloon.minWidth = this.cellWidth));
        this.previousHeight = A;
      }

      this.axisCreated = !0;
      this.set.translate(this.x, this.y);
      this.labelsSet.translate(this.x, this.y);
      this.labelsSet.show();
      this.positionTitle();
      (B = this.axisLine.set) && B.toFront();
      var A = this.getBBox().height;
      2 < A - this.previousHeight && this.autoWrap && !this.parseDates && (this.axisCreated = this.chart.marginsUpdated = !1);
    },
    xToIndex: function xToIndex(a) {
      var b = this.data,
          c = this.chart,
          d = c.rotate,
          g = this.stepWidth,
          h;
      if (this.parseDates && !this.equalSpacing) a = this.startTime + Math.round(a / g) - this.minDuration() / 2, h = c.getClosestIndex(b, "time", a, !1, this.start, this.end + 1);else if (this.widthField) for (c = Infinity, g = this.start; g <= this.end; g++) {
        var f = this.data[g];
        f && (f = Math.abs(f.x[this.id] - a), f < c && (c = f, h = g));
      } else this.startOnAxis || (a -= g / 2), h = this.start + Math.round(a / g);
      h = e.fitToBounds(h, 0, b.length - 1);
      var l;
      b[h] && (l = b[h].x[this.id]);
      d ? l > this.height + 1 && h-- : l > this.width + 1 && h--;
      0 > l && h++;
      return h = e.fitToBounds(h, 0, b.length - 1);
    },
    dateToCoordinate: function dateToCoordinate(a) {
      return this.parseDates && !this.equalSpacing ? (a.getTime() - this.startTime) * this.stepWidth : this.parseDates && this.equalSpacing ? (a = this.chart.getClosestIndex(this.data, "time", a.getTime(), !1, 0, this.data.length - 1), this.getCoordinate(a - this.start)) : NaN;
    },
    categoryToCoordinate: function categoryToCoordinate(a) {
      if (this.chart) {
        if (this.parseDates) return this.dateToCoordinate(new Date(a));
        a = this.chart.getCategoryIndexByValue(a);
        if (!isNaN(a)) return this.getCoordinate(a - this.start);
      } else return NaN;
    },
    coordinateToDate: function coordinateToDate(a) {
      return this.equalSpacing ? (a = this.xToIndex(a), new Date(this.data[a].time)) : new Date(this.startTime + a / this.stepWidth);
    },
    coordinateToValue: function coordinateToValue(a) {
      a = this.xToIndex(a);
      if (a = this.data[a]) return this.parseDates ? a.time : a.category;
    },
    getCoordinate: function getCoordinate(a) {
      a *= this.stepWidth;
      this.startOnAxis || (a += this.stepWidth / 2);
      return Math.round(a);
    },
    formatValue: function formatValue(a, b) {
      b || (b = this.currentDateFormat);
      this.parseDates && (a = e.formatDate(new Date(a), b, this.chart));
      return a;
    },
    showBalloonAt: function showBalloonAt(a, b) {
      void 0 === b && (b = this.parseDates ? this.dateToCoordinate(new Date(a)) : this.categoryToCoordinate(a));
      return this.adjustBalloonCoordinate(b);
    },
    formatBalloonText: function formatBalloonText(a, b, c) {
      var d = "",
          g = "",
          h = this.chart,
          f = this.data[b];
      if (f) if (this.parseDates) d = e.formatDate(f.category, c, h), b = e.changeDate(new Date(f.category), this.minPeriod, 1), g = e.formatDate(b, c, h), -1 != d.indexOf("fff") && (d = e.formatMilliseconds(d, f.category), g = e.formatMilliseconds(g, b));else {
        var l;
        this.data[b + 1] && (l = this.data[b + 1]);
        d = e.fixNewLines(f.category);
        l && (g = e.fixNewLines(l.category));
      }
      a = a.replace(/\[\[category\]\]/g, String(d));
      return a = a.replace(/\[\[toCategory\]\]/g, String(g));
    },
    adjustBalloonCoordinate: function adjustBalloonCoordinate(a, b) {
      var c = this.xToIndex(a),
          d = this.chart.chartCursor;

      if (this.stickBalloonToCategory) {
        var e = this.data[c];
        e && (a = e.x[this.id]);
        this.stickBalloonToStart && (a -= this.cellWidth / 2);
        var h = 0;

        if (d) {
          var f = d.limitToGraph;

          if (f) {
            var l = f.valueAxis.id;
            f.hidden || (h = e.axes[l].graphs[f.id].y);
          }

          this.rotate ? ("left" == this.position ? (f && (h -= d.width), 0 < h && (h = 0)) : 0 > h && (h = 0), d.fixHLine(a, h)) : ("top" == this.position ? (f && (h -= d.height), 0 < h && (h = 0)) : 0 > h && (h = 0), d.fixVLine(a, h));
        }
      }

      d && !b && (d.setIndex(c), this.parseDates && d.setTimestamp(this.coordinateToDate(a).getTime()));
      return a;
    }
  });
})();
"use strict";

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var tinCannyDashboard = null;
jQuery(document).ready(function ($) {
  tinCannyDashboard = {
    init: function init() {
      this.insertHTML();
      this.getData();
      this.groupSelector();
    },
    insertHTML: function insertHTML() {
      // Get elements
      var $dashboard = $(this.html); // Get containerdataProvider: completionAndTinCanDates

      var $container;

      if ($('#dashboard-widgets-wrap').length > 0) {
        $container = $('#dashboard-widgets-wrap').prev();
      } else if ($('#uo-welcome-panel').length > 0) {
        $container = $('#uo-welcome-panel').parent();
      } else {
        $container = $('#coursesOverviewGraphHeading');
      } // Get mainContainer


      var $mainContainer = $('.tclr.wrap'); // Get context
      // Values:
      // - dashboard:  WP Admin main page
      // - plugin:     The Tin Canny Dashboard page
      // - frontend:   Frontend

      var context = reportingApiSetup.page;
      context = context == 'reporting' && $('body').hasClass('wp-admin') ? 'plugin' : context;
      context = context == 'reporting' ? 'frontend' : context; // Filter HTML and add context class

      if (context == 'plugin') {
        $mainContainer.addClass('uo-reporting--plugin');
        $dashboard.find('.reporting-dashboard-col-3').remove();
      }

      if (context == 'frontend') {
        $mainContainer.addClass('uo-reporting--frontend'); // $dashboard.find( '.reporting-dashboard-col-1' ).remove();

        $dashboard.find('.reporting-dashboard-col-3').remove();
      }

      if (context == 'dashboard') {
        $mainContainer.addClass('uo-reporting--dashboard'); // Add class to the dashboard container

        $dashboard.addClass('uo-reporting--dashboard');
      } // Check if we have to remove the Tin Can report button


      if (reportingApiSetup.showTinCanTab == '0') {
        $dashboard.find('.reporting-dashboard-quick-links__item[data-id="tin-can"]').remove();
      } // Save context


      this.context = context; // Insert HTML

      $dashboard.insertAfter($container);
    },
    getData: function getData() {
      var _this = this;

      // Create variable to save AJAX
      var coursesOverviewApiCall;

      if (typeof reportingApiSetup.isolated_group_id == 'string' && parseInt(reportingApiSetup.isolated_group_id)) {
        if (this.context == 'dashboard') {
          coursesOverviewApiCall = this.restCall('/?group_id=' + reportingApiSetup.isolated_group_id);
        }
      } else {
        if (this.context == 'dashboard') {
          coursesOverviewApiCall = this.restCall();
        }
      }

      if (this.context == 'dashboard') {
        // On success
        coursesOverviewApiCall.done(function (response) {
          _this.dataObject = response;

          _this.renderData(); // Trigger tab selection to render the tables
          // Get current tab


          var $tabs = $('.uo-admin-reporting-tabs'); // Check if the element exists

          if ($tabs.length > 0) {
            // Get id of current tab
            var activeTab = $tabs.data('tab_on_load'); // Trigger click on tab

            $(".uo-admin-reporting-tabs .nav-tab[data-tab_id=\"".concat(activeTab, "\"]")).trigger('click');
          }
        }); // On error

        coursesOverviewApiCall.fail(function (response) {
          // Append error message
          $('#wpbody-content').append($('<div/>', {
            id: 'api-error',
            text: response.responseTex
          }));
        });
      }
    },
    restCall: function restCall() {
      var parameters = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      return $.ajax({
        url: "".concat(reportingApiSetup.root, "dashboard_data").concat(parameters),
        beforeSend: function beforeSend(xhr) {
          xhr.setRequestHeader('X-WP-Nonce', reportingApiSetup.nonce);
        }
      });
    },
    renderData: function renderData() {
      // Create metaboxes
      this.metaboxReports();
      this.metaboxRecentActivity();
      this.metaboxMostAndLeastCompletedCourses();
    },
    metaboxReports: function metaboxReports() {
      var _this2 = this;

      // Bind links
      $('.reporting-dashboard-quick-links__item').on('click', function () {
        window.location.href = "".concat(_this2.dataObject.report_link).concat($(event.currentTarget).data('append'));
      }); // Fill quick data numbers

      $('.reporting-dashboard-quick-stats__item').each(function (index, element) {
        // Get elements
        var $item = $(element),
            $number = $item.find('.reporting-dashboard-quick-stats__number'),
            // Get id
        id = $item.data('id'); // Get number
        // First, define default value

        var number = 0; // Get number depending of the id

        switch (id) {
          case 'users':
            number = _this2.dataObject.total_users;
            break;

          case 'courses':
            number = _this2.dataObject.total_courses;
            break;
        } // Add number to the container


        $number.text(number); // Add ready class

        $item.addClass('reporting-dashboard-quick-stats__item--ready');
      });
    },
    metaboxRecentActivity: function metaboxRecentActivity() {
      // Create pattern to find parts of a date ( YYYY-MM-DD )
      var datePattern = /^(\d{4})-(\d{2})-(\d{2})$/; // Add missing days between entries in the graph data.

      var addMissingDaysToGraphData = function addMissingDaysToGraphData(graphData) {
        // Get number of entries
        var numberOfEntries = graphData.length; // New dates

        var newGraphData = []; // Iterate each entry

        graphData.forEach(function (element, index) {
          // Add this day to the graph data array
          newGraphData.push(element); // if it isn't the last one then search and add missing days

          if (index != numberOfEntries - 1) {
            // Get current and next elements
            var currentEntry = element,
                nextEntry = graphData[index + 1]; // Get date

            var _datePattern$exec = datePattern.exec(currentEntry.date),
                _datePattern$exec2 = _slicedToArray(_datePattern$exec, 4),
                currentEntryYear = _datePattern$exec2[1],
                currentEntryMonth = _datePattern$exec2[2],
                currentEntryDay = _datePattern$exec2[3]; //let currentEntryDate = new Date( `${currentEntryYear}-${currentEntryMonth}-${currentEntryDay}T00:00:00Z` );


            var currentEntryDate = new Date("".concat(currentEntryYear, "-").concat(currentEntryMonth, "-").concat(currentEntryDay, "T02:00:00Z")); // Get date of the next entry

            var _datePattern$exec3 = datePattern.exec(nextEntry.date),
                _datePattern$exec4 = _slicedToArray(_datePattern$exec3, 4),
                nextEntryYear = _datePattern$exec4[1],
                nextEntryMonth = _datePattern$exec4[2],
                nextEntryDay = _datePattern$exec4[3]; //let nextEntryDate = new Date( `${nextEntryYear}-${nextEntryMonth}-${nextEntryDay}T00:00:00Z` );


            var nextEntryDate = new Date("".concat(nextEntryYear, "-").concat(nextEntryMonth, "-").concat(nextEntryDay, "T02:00:00Z")); // Days difference between those two

            var differenceInDays = Math.ceil((nextEntryDate - currentEntryDate) / 1000 / 60 / 60 / 24); // Clone currentEntryDate

            var newDate = new Date(+currentEntryDate); // Add missing days (don't count the last one)

            for (var i = 1; i <= differenceInDays - 1; i++) {
              // Get new date
              newDate.setDate(newDate.getDate() + 1);
              var newDateParts = {
                year: newDate.getUTCFullYear(),
                month: newDate.getUTCMonth() + 1,
                day: newDate.getUTCDate()
              }; // Add zeros

              newDateParts.month = newDateParts.month < 10 ? "0".concat(newDateParts.month) : newDateParts.month;
              newDateParts.day = newDateParts.day < 10 ? "0".concat(newDateParts.day) : newDateParts.day; // Add date

              newGraphData.push({
                date: "".concat(newDateParts.year, "-").concat(newDateParts.month, "-").concat(newDateParts.day)
              });
            }
          }
        }); // Return data

        return newGraphData;
      }; // Get data


      var completionAndTinCanDates = this.dataObject.courses_tincan_completed; // Merge data if they have the same day
      // So, for example, if we have an array with two elements: { date: '2019-01-01', completions: 1 }
      // and { data: '2019-01-01', tinCan: 5 } we will merge them to create a new array
      // with only one element: { data: '2019-01-01', completions: 1, tinCan: 5 }

      var dataGroupedByDate = [],
          dateAndIndexPair = {};
      completionAndTinCanDates.forEach(function (element, index) {
        // Check if we already added that date
        if (dateAndIndexPair[element.date] !== undefined) {
          // Then merge this object with the stored one
          dataGroupedByDate[dateAndIndexPair[element.date]] = Object.assign({}, dataGroupedByDate[dateAndIndexPair[element.date]], element);
        } else {
          // Create element and store object, and
          // add the pair index
          dateAndIndexPair[element.date] = dataGroupedByDate.push(element) - 1;
        }
      });
      completionAndTinCanDates = dataGroupedByDate; // Check if it has data

      var hasData = completionAndTinCanDates.length > 0; // If there is no data then add some
      // We only have to add two days separated by 7 days. The following
      // function will add all the missing dates (the ones between those two)

      if (!hasData) {
        var _completionAndTinCanD;

        // Get dates
        var dateNow = new Date(),
            date10DaysAgo = new Date();
        date10DaysAgo.setDate(date10DaysAgo.getDate() - 7); // Get parts of both dates

        var dateParts = {
          now: {
            year: dateNow.getUTCFullYear(),
            month: dateNow.getUTCMonth() + 1,
            day: dateNow.getUTCDate()
          },
          daysAgo: {
            year: date10DaysAgo.getUTCFullYear(),
            month: date10DaysAgo.getUTCMonth() + 1,
            day: date10DaysAgo.getUTCDate()
          }
        }; // Add zeros

        dateParts.now.month = dateParts.now.month < 10 ? "0".concat(dateParts.now.month) : dateParts.now.month;
        dateParts.now.day = dateParts.now.day < 10 ? "0".concat(dateParts.now.day) : dateParts.now.day;
        dateParts.daysAgo.month = dateParts.daysAgo.month < 10 ? "0".concat(dateParts.daysAgo.month) : dateParts.daysAgo.month;
        dateParts.daysAgo.day = dateParts.daysAgo.day < 10 ? "0".concat(dateParts.daysAgo.day) : dateParts.daysAgo.day;

        (_completionAndTinCanD = completionAndTinCanDates).push.apply(_completionAndTinCanD, [{
          date: "".concat(dateParts.daysAgo.year, "-").concat(dateParts.daysAgo.month, "-").concat(dateParts.daysAgo.day)
        }, {
          date: "".concat(dateParts.now.year, "-").concat(dateParts.now.month, "-").concat(dateParts.now.day)
        }]);
      } else {
        // Check that we have information from the date of the last entry until today
        // So, if we the last completion or Tin Can statement was 10 days ago we have to add those 10 missing days
        // Actually, we have to add only the last one, the script has other function that will add the days in the middle
        // First, get the date
        var _datePattern$exec5 = datePattern.exec(completionAndTinCanDates[completionAndTinCanDates.length - 1].date),
            _datePattern$exec6 = _slicedToArray(_datePattern$exec5, 4),
            lastElementYear = _datePattern$exec6[1],
            lastElementMonth = _datePattern$exec6[2],
            lastElementDay = _datePattern$exec6[3];

        var lastElementDate = new Date("".concat(lastElementYear, "-").concat(lastElementMonth, "-").concat(lastElementDay, "T02:00:00Z")); // Compare that day with today

        var todayDate = new Date(); // Days difference between those two

        var differenceInDays = Math.ceil((todayDate - lastElementDate) / 1000 / 60 / 60 / 24) - 1; // Add the days missing

        lastElementDate.setDate(lastElementDate.getDate() + differenceInDays); // If there is day difference then we have to add the date

        if (differenceInDays > 0) {
          // Get date parts
          var _dateParts = {
            year: lastElementDate.getUTCFullYear(),
            month: lastElementDate.getUTCMonth() + 1,
            day: lastElementDate.getUTCDate()
          }; // Add zeros

          _dateParts.month = _dateParts.month < 10 ? "0".concat(_dateParts.month) : _dateParts.month;
          _dateParts.day = _dateParts.day < 10 ? "0".concat(_dateParts.day) : _dateParts.day; // Add element

          completionAndTinCanDates.push({
            date: "".concat(_dateParts.year, "-").concat(_dateParts.month, "-").concat(_dateParts.day)
          });
        }
      } // Add missing days


      completionAndTinCanDates = addMissingDaysToGraphData(completionAndTinCanDates); // If there isn't enough data to fill 7 days then add some
      // days to complete the week

      if (completionAndTinCanDates.length < 7) {
        // Calculate days to add
        var daysToAdd = 7 - completionAndTinCanDates.length; // Get date of the first element

        var _datePattern$exec7 = datePattern.exec(completionAndTinCanDates[0].date),
            _datePattern$exec8 = _slicedToArray(_datePattern$exec7, 4),
            firstElementYear = _datePattern$exec8[1],
            firstElementMonth = _datePattern$exec8[2],
            firstElementDay = _datePattern$exec8[3];

        var firstElementDate = new Date("".concat(firstElementYear, "-").concat(firstElementMonth, "-").concat(firstElementDay, "T00:00:00Z")); // Get new date (so we have 7 days)

        firstElementDate.setDate(firstElementDate.getDate() - daysToAdd); // Get date parts

        var _dateParts2 = {
          year: firstElementDate.getUTCFullYear(),
          month: firstElementDate.getUTCMonth() + 1,
          day: firstElementDate.getUTCDate()
        }; // Add zeros

        _dateParts2.month = _dateParts2.month < 10 ? "0".concat(_dateParts2.month) : _dateParts2.month;
        _dateParts2.day = _dateParts2.day < 10 ? "0".concat(_dateParts2.day) : _dateParts2.day; // Prepend new elements

        completionAndTinCanDates = [{
          date: "".concat(_dateParts2.year, "-").concat(_dateParts2.month, "-").concat(_dateParts2.day)
        }].concat(_toConsumableArray(completionAndTinCanDates)); // Add missing days

        completionAndTinCanDates = addMissingDaysToGraphData(completionAndTinCanDates);
      } // If it has data, then


      if (hasData) {
        // Add missing properties
        completionAndTinCanDates = completionAndTinCanDates.map(function (entry) {
          // Create new object
          var day = {
            date: entry.date,
            completions: entry.completions === undefined ? 0 : entry.completions
          }; // Check if we have to show the Tin Can data,
          // otherwise, don't add it

          if (TincannyUI.show.tinCanData) {
            // Add tin can data
            day.tinCan = entry.tinCan === undefined ? 0 : entry.tinCan;
          } // Return day data


          return day;
        });
      } // Create chart data


      var chart = {
        type: 'serial',
        listeners: [{
          event: 'init',
          method: function method(e) {
            // Define the number of days to show
            var numberOfDaysToShow = 30; // Zoom automatically to the last X days
            // First, check if there are more than X days of data

            if (completionAndTinCanDates.length > numberOfDaysToShow) {
              var dates = {
                end: completionAndTinCanDates[completionAndTinCanDates.length - 1].date
              };

              if (0 >= completionAndTinCanDates.length - 1 - numberOfDaysToShow) {
                dates.start = completionAndTinCanDates[0].date;
              } else {
                dates.start = completionAndTinCanDates[completionAndTinCanDates.length - 1 - numberOfDaysToShow].date;
              } // Those dates are strings, convert them to Date objects


              var _datePattern$exec9 = datePattern.exec(dates.start),
                  _datePattern$exec10 = _slicedToArray(_datePattern$exec9, 4),
                  startYear = _datePattern$exec10[1],
                  startMonth = _datePattern$exec10[2],
                  startDay = _datePattern$exec10[3];

              var startDate = new Date("".concat(startYear, "-").concat(startMonth, "-").concat(startDay, "T00:00:00Z"));

              var _datePattern$exec11 = datePattern.exec(dates.end),
                  _datePattern$exec12 = _slicedToArray(_datePattern$exec11, 4),
                  endYear = _datePattern$exec12[1],
                  endMonth = _datePattern$exec12[2],
                  endDay = _datePattern$exec12[3];

              var endDate = new Date("".concat(endYear, "-").concat(endMonth, "-").concat(endDay, "T00:00:00Z")); // Set zoom

              e.chart.zoomToDates(startDate, endDate);
            }
          }
        }, {
          event: 'drawn',
          method: function method() {
            // If it doesn't have data then render a notice
            if (!hasData) {
              // Get container
              var $chartContainer = $('#reporting-recent-activities .amcharts-chart-div'); // Create notice

              var $notice = $("\n                                <div class=\"reporting-dashboard-status reporting-dashboard-status--warning\">\n                                    <div class=\"reporting-dashboard-status__icon\"></div>\n                                    <div class=\"reporting-dashboard-status__text\">\n                                        ".concat(reportingApiSetup.localizedStrings.graphNoActivity, "\n                                    </div>\n                                </div>\n                            ")); // Append notice

              $chartContainer.append($notice);
            }
          }
        }],
        pathToImages: 'https://cdn.amcharts.com/lib/3/images/',
        categoryField: 'date',
        columnWidth: 0,
        dataDateFormat: 'YYYY-MM-DD',
        angle: 34,
        marginBottom: 14,
        marginLeft: 19,
        plotAreaBorderAlpha: 0.04,
        plotAreaFillAlphas: 0.03,
        colors: [TincannyUI.colors.primary, TincannyUI.colors.secondary, '#84b761', '#cc4748', '#cd82ad', '#2f4074', '#448e4d', '#b7b83f', '#b9783f', '#b93e3d', '#913167'],
        startDuration: 0,
        fontFamily: TincannyUI.mainFont,
        fontSize: 13,
        handDrawScatter: 1,
        theme: 'light',
        categoryAxis: {
          autoRotateAngle: 0,
          gridPosition: 'start',
          parseDates: true,
          offset: 9
        },
        chartCursor: {
          enabled: true,
          bulletSize: 6,
          cursorColor: '#000000'
        },
        chartScrollbar: {
          enabled: true,
          autoGridCount: true,
          backgroundAlpha: 0.46,
          backgroundColor: '#F3F3F3',
          color: '#666766',
          dragIconHeight: 30,
          dragIconWidth: 30,
          // Check if we should show Tin Can data. If we should, then use
          // the Tin Can graph to render the scroll graph, otherwise use
          // the Completions graph
          graph: TincannyUI.show.tinCanData ? 'tin-can' : 'completion',
          graphFillAlpha: 0.15,
          graphLineAlpha: 1,
          graphType: 'smoothedLine',
          gridAlpha: 0.02,
          gridColor: 'F3F3F3',
          offset: 20,
          scrollbarHeight: 35,
          scrollDuration: 7,
          selectedBackgroundAlpha: 1,
          selectedGraphFillAlpha: 0,
          selectedGraphFillColor: 'A4A4A4',
          selectedGraphLineAlpha: 1,
          selectedGraphLineColor: '979797'
        },
        trendLines: [],
        graphs: [{
          balloonColor: TincannyUI.colors.primary,
          balloonText: sprintf(reportingApiSetup.localizedStrings.graphTooltipCompletions, '[[value]]'),
          bulletAlpha: 0,
          color: '',
          columnWidth: 0,
          cornerRadiusTop: 50,
          fontSize: 12,
          id: 'completion',
          lineThickness: 2,
          minDistance: 0,
          negativeFillAlphas: 0,
          title: sprintf(reportingApiSetup.localizedStrings.graphCourseCompletions, reportingApiSetup.learnDashLabels.course),
          type: 'smoothedLine',
          valueAxis: 'completion',
          valueField: 'completions',
          yAxis: 'completion'
        } // Below we will check if we have to add or not the Tin Can graph
        ],
        guides: [],
        valueAxes: [{
          gridType: 'circles',
          id: 'completion',
          stackType: 'regular',
          axisAlpha: 1,
          axisColor: TincannyUI.colors.primary,
          color: TincannyUI.colors.primary,
          labelOffset: 6,
          tickLength: 0,
          title: '',
          titleBold: false,
          titleFontSize: 13,
          titleRotation: -90
        } // Below we will check if we have to add or not the Tin Can graph
        ],
        allLabels: [],
        balloon: {},
        legend: {
          enabled: true,
          useGraphSettings: true
        },
        titles: [],
        dataProvider: completionAndTinCanDates
      }; // Check if we have to add the Tin Can data

      if (TincannyUI.show.tinCanData) {
        // Add Graph
        chart.graphs.push({
          balloonColor: TincannyUI.colors.secondary,
          balloonText: sprintf(reportingApiSetup.localizedStrings.graphTooltipStatements, '[[value]]'),
          columnWidth: 0,
          fillColors: TincannyUI.colors.secondary,
          id: 'tin-can',
          lineThickness: 2,
          minDistance: 0,
          negativeBase: 2,
          showBulletsAt: 'open',
          title: reportingApiSetup.localizedStrings.graphTinCanStatements,
          topRadius: 0,
          type: 'smoothedLine',
          valueAxis: 'tin-can',
          valueField: 'tinCan',
          yAxis: 'tin-can'
        }); // Add value axes

        chart.valueAxes.push({
          id: 'tin-can',
          pointPosition: 'middle',
          position: 'right',
          stackType: 'regular',
          axisAlpha: 1,
          axisColor: TincannyUI.colors.secondary,
          color: TincannyUI.colors.secondary,
          title: '',
          titleBold: false,
          titleFontSize: 13,
          titleRotation: 90
        });
      }

      if (isDefined(wp.hooks)) {
        chart = wp.hooks.applyFilters('tc_chartObject', chart, completionAndTinCanDates, this.dataObject);
      } // Create Recent Activities chart


      AmCharts.makeChart('reporting-recent-activities', chart);
    },
    metaboxMostAndLeastCompletedCourses: function metaboxMostAndLeastCompletedCourses() {
      // Get list
      var courses = this.dataObject.top_course_completions; // Clean data

      courses = courses.map(function (element) {
        // Define default percentage
        var percentage = 0; // Now get the real percentage
        // Get the number of enrolled users

        var numberOfEnrolledUsers = isDefined(element.course_user_access_list) ? Object.keys(element.course_user_access_list).length : 0; // Check if it has at least one enrolled user

        if (numberOfEnrolledUsers >= 1) {
          percentage = Math.floor(element.completions / Object.keys(element.course_user_access_list).length * 100);
        } // Return new object


        return {
          percentage: percentage,
          title: element.post_title
        };
      }); // Sort data

      courses = courses.sort(function (a, b) {
        return b.percentage - a.percentage;
      }); // Add element number

      courses = courses.map(function (element, index) {
        return Object.assign({}, element, {
          order: index + 1
        });
      }); // Get parts (top, middle, bottom)

      var coursesRanking = {
        top: [],
        middle: [],
        bottom: []
      },
          coursesCopy = courses.slice(0); // Get best ones

      coursesRanking.top = coursesCopy.slice(0, 3);
      coursesCopy = coursesCopy.slice(3); // Get the ones at the bottom

      if (coursesCopy.length <= 3) {
        coursesRanking.bottom = coursesCopy;
        coursesCopy = [];
      } else {
        coursesRanking.bottom = coursesCopy.slice(coursesCopy.length - 3, coursesCopy.length);
        coursesCopy = coursesCopy.slice(0, coursesCopy.length - 3);
      } // Get the ones at the middle


      coursesRanking.middle = coursesCopy; // Get container where we're going to insert the list of courses

      var $container = $('#reporting-completed-ranking'); // Remove loading data

      $container.html(''); // Create function to create elements

      var createRow = function createRow(course) {
        return "\n                    <div class=\"reporting-completed-ranking__item\">\n                        <div class=\"reporting-completed-ranking__order\">".concat(course.order, "</div>\n                        <div class=\"reporting-completed-ranking__title\">").concat(course.title, "</div>\n                        <div class=\"reporting-completed-ranking__percentage\">").concat(course.percentage, "%</div>\n                    </div>\n                ");
      }; // Check if we have items to add


      if (courses.length > 0) {
        // Add elements to the container
        if (coursesRanking.top.length > 0) {
          $container.append($("\n                        <div class=\"reporting-completed-ranking__top\">\n                            ".concat(coursesRanking.top.map(function (element) {
            return createRow(element);
          }).join(''), "\n                        </div>")));
        }

        if (coursesRanking.middle.length > 0) {
          $container.append($("\n                        <div class=\"reporting-completed-ranking__middle\">\n                            <div id=\"reporting-completed-ranking-middle__points\" class=\"reporting-completed-ranking-middle__points\" tclr-tooltip=\"".concat(sprintf(reportingApiSetup.localizedStrings.overviewBoxesReportsCoursesCompletionSeeAll, reportingApiSetup.learnDashLabels.courses.toLowerCase()), "\">\n                                \xB7\xB7\xB7\n                            </div>\n                            <div id=\"reporting-completed-ranking-middle__items\" class=\"reporting-completed-ranking-middle__items\">\n                                ").concat(coursesRanking.middle.map(function (element) {
            return createRow(element);
          }).join(''), "\n                            </div>\n                        </div>")));
        }

        if (coursesRanking.bottom.length > 0) {
          $container.append($("\n                        <div class=\"reporting-completed-ranking__bottom\">\n                            ".concat(coursesRanking.bottom.map(function (element) {
            return createRow(element);
          }).join(''), "\n                        </div>")));
        }
      } else {
        // Add "No course completions" notice
        // Create notice
        var $notice = $("\n                    <div class=\"reporting-dashboard-status reporting-dashboard-status--warning\">\n                        <div class=\"reporting-dashboard-status__icon\"></div>\n                        <div class=\"reporting-dashboard-status__text\">\n                            ".concat(reportingApiSetup.localizedStrings.overviewBoxesReportsCoursesCompletionNoData, "\n                        </div>\n                    </div>\n                ")); // Append notice

        $container.append($notice);
      } // Bind button to show all courses


      var $showAllElements = {
        btn: $('#reporting-completed-ranking-middle__points'),
        items: $('#reporting-completed-ranking-middle__items')
      }; // Listen click

      $showAllElements.btn.on('click', function () {
        // Hide button
        $showAllElements.btn.hide(); // Show items

        $showAllElements.items.slideToggle(300);
      }); // Define width of .reporting-completed-ranking__order and .reporting-completed-ranking__percentage
      // Context: Normally we would do this with CSS using a table structure, but in this case
      // we have each row separated in three different containers; in the first container
      // .reporting-completed-ranking__order can have 1 digit, while in the third container
      // that element can have 2 digits (or even more in big sites). So to fix this we have to use JS

      var fixedWidth = {
        order: 0,
        percentage: 0
      };
      $('.reporting-completed-ranking__order').each(function (index, element) {
        // Get cell width
        var cellWidth = $(element).width(); // Check if it's the biggest one

        fixedWidth.order = fixedWidth.order < cellWidth ? cellWidth : fixedWidth.order;
      });
      $('.reporting-completed-ranking__percentage').each(function (index, element) {
        // Get cell width
        var cellWidth = $(element).width(); // Check if it's the biggest one

        fixedWidth.percentage = fixedWidth.percentage < cellWidth ? cellWidth : fixedWidth.percentage;
      });
      $('.reporting-completed-ranking__order').css({
        width: "".concat(fixedWidth.order, "px")
      });
      $('.reporting-completed-ranking__percentage').css({
        width: "".concat(fixedWidth.percentage, "px")
      });
    },
    groupSelector: function groupSelector() {
      // Get group selector
      var $groupSelector = $('#reporting-group-selector'); // Check if it exists

      if ($groupSelector.length == 1) {
        // Init select2
        $groupSelector.select2({
          theme: 'default tclr-select2',
          language: {
            errorLoading: function errorLoading() {
              return TincannyData.i18n.dropdown.errorLoading;
            },
            inputTooLong: function inputTooLong(e) {
              var n = e.input.length - e.maximum;

              if (n == 1) {
                return TincannyData.i18n.dropdown.inputTooLong.singular;
              } else {
                return TincannyData.i18n.dropdown.inputTooLong.plural.replace('%s', n);
              }
            },
            inputTooShort: function inputTooShort(e) {
              return TincannyData.i18n.dropdown.inputTooShort.replace('%s', e.minimum - e.input.length);
            },
            loadingMore: function loadingMore() {
              return TincannyData.i18n.dropdown.loadingMore;
            },
            maximumSelected: function maximumSelected(e) {
              if (e.maximum == 1) {
                return TincannyData.i18n.dropdown.maximumSelected.singular;
              } else {
                return TincannyData.i18n.dropdown.maximumSelected.plural.replace('%s', e.maximum);
              }
            },
            noResults: function noResults() {
              return TincannyData.i18n.dropdown.noResults;
            },
            searching: function searching() {
              return TincannyData.i18n.dropdown.searching;
            },
            removeAllItems: function removeAllItems() {
              return TincannyData.i18n.dropdown.removeAllItems;
            }
          }
        });
      }
    },
    html: "\n            <div class=\"tclr uo-reporting-dashboard-container\">\n                <div class=\"reporting-dashboard-col-container reporting-dashboard-col-1\">\n                    <div class=\"reporting-dashboard-col-inner-container\">\n                        <div class=\"reporting-dashboard-col-heading\">".concat(reportingApiSetup.localizedStrings.overviewBoxesTitleReports, "</div>\n                        <div class=\"reporting-dashboard-col-content\">\n                            <div class=\"reporting-dashboard-quick-links\">\n                                <div class=\"reporting-dashboard-quick-links__item\" data-id=\"courseReportTab\" data-append=\"\">\n                                    <div class=\"reporting-dashboard-quick-links__icon\">\n                                        <span class=\"tincanny-icon tincanny-icon-book\"></span>\n                                    </div>\n                                    <div class=\"reporting-dashboard-quick-links__content\">\n                                        <div class=\"reporting-dashboard-quick-links__title\">\n                                            ").concat(sprintf(reportingApiSetup.localizedStrings.overviewBoxesReportsCourseReportTitle, reportingApiSetup.learnDashLabels.course), "\n                                        </div>\n                                        <div class=\"reporting-dashboard-quick-links__description\">\n                                            ").concat(reportingApiSetup.localizedStrings.overviewBoxesReportsCourseReportDescription, "\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"reporting-dashboard-quick-links__item\" data-id=\"userReportTab\" data-append=\"&tab=userReportTab\">\n                                    <div class=\"reporting-dashboard-quick-links__icon\">\n                                        <span class=\"tincanny-icon tincanny-icon-user\"></span>\n                                    </div>\n                                    <div class=\"reporting-dashboard-quick-links__content\">\n                                        <div class=\"reporting-dashboard-quick-links__title\">\n                                            ").concat(reportingApiSetup.localizedStrings.overviewBoxesReportsUserReportTitle, "\n                                        </div>\n                                        <div class=\"reporting-dashboard-quick-links__description\">\n                                            ").concat(reportingApiSetup.localizedStrings.overviewBoxesReportsUserReportDescription, "\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"reporting-dashboard-quick-links__item\" data-id=\"tin-can\" data-append=\"&tab=tin-can\">\n                                    <div class=\"reporting-dashboard-quick-links__icon\">\n                                        <span class=\"tincanny-icon tincanny-icon-chart-bar\"></span>\n                                    </div>\n                                    <div class=\"reporting-dashboard-quick-links__content\">\n                                        <div class=\"reporting-dashboard-quick-links__title\">\n                                            ").concat(reportingApiSetup.localizedStrings.overviewBoxesReportsTinCanReportTitle, "\n                                        </div>\n                                        <div class=\"reporting-dashboard-quick-links__description\">\n                                            ").concat(reportingApiSetup.localizedStrings.overviewBoxesReportsTinCanReportDescription, "\n                                        </div>\n                                    </div>\n                                </div>\n                            </div>\n                            <div class=\"reporting-dashboard-quick-stats\">\n                                <div class=\"reporting-dashboard-quick-stats__item\" data-id=\"users\">\n                                    <div class=\"reporting-dashboard-quick-stats__number\">-</div>\n                                    <div class=\"reporting-dashboard-quick-stats__description\">").concat(reportingApiSetup.localizedStrings.overviewUsers, "</div>\n                                </div>\n                                <div class=\"reporting-dashboard-quick-stats__item\" data-id=\"courses\">\n                                    <div class=\"reporting-dashboard-quick-stats__number\">-</div>\n                                    <div class=\"reporting-dashboard-quick-stats__description\">").concat(reportingApiSetup.learnDashLabels.courses, "</div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"reporting-dashboard-col-container reporting-dashboard-col-2\">\n                    <div class=\"reporting-dashboard-col-inner-container\">\n                        <div class=\"reporting-dashboard-col-heading\">").concat(reportingApiSetup.localizedStrings.overviewBoxesTitleRecentActivities, "</div>\n                        <div class=\"reporting-dashboard-col-content reporting-dashboard-col-content--small-padding\">\n                            <div id=\"reporting-recent-activities\" class=\"reporting-recent-activities\">\n                                <div class=\"reporting-dashboard-status reporting-dashboard-status--loading\">\n                                    <div class=\"reporting-dashboard-status__icon\"></div>\n                                    <div class=\"reporting-dashboard-status__text\">\n                                        ").concat(reportingApiSetup.localizedStrings.overviewLoading, "\n                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"reporting-dashboard-col-container reporting-dashboard-col-3\">\n                    <div class=\"reporting-dashboard-col-inner-container\">\n                        <div class=\"reporting-dashboard-col-heading\">\n                            ").concat(sprintf(reportingApiSetup.localizedStrings.overviewBoxesTitleCompletedCourses, reportingApiSetup.learnDashLabels.courses), "\n                        </div>\n                        <div class=\"reporting-dashboard-col-content\">\n                            <div class=\"reporting-completed-ranking\" id=\"reporting-completed-ranking\">\n                                <div class=\"reporting-dashboard-status reporting-dashboard-status--loading\">\n                                    <div class=\"reporting-dashboard-status__icon\"></div>\n                                    <div class=\"reporting-dashboard-status__text\">\n                                        ").concat(reportingApiSetup.localizedStrings.overviewLoading, "\n                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>")
  };
  tinCannyDashboard.init();
});