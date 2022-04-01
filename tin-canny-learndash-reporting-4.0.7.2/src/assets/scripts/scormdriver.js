/*! xAPIWrapper v 1.11.0 | Built on 2020-01-29 13:51:08-0500 */

var CryptoJS = (CryptoJS = CryptoJS || function (l) {
	var t = {}, e = t.lib = {}, i = e.Base = {
		extend: function (t) {
			n.prototype = this;
			var e = new n;
			return t && e.mixIn(t), e.hasOwnProperty("init") || (e.init = function () {
				e.$super.init.apply(this, arguments)
			}), (e.init.prototype = e).$super = this, e
		}, create: function () {
			var t = this.extend();
			return t.init.apply(t, arguments), t
		}, init: function () {
		}, mixIn: function (t) {
			for (var e in t) t.hasOwnProperty(e) && (this[e] = t[e]);
			t.hasOwnProperty("toString") && (this.toString = t.toString)
		}, clone: function () {
			return this.init.prototype.extend(this)
		}
	};

	function n() {
	}

	var u = e.WordArray = i.extend({
		init: function (t, e) {
			t = this.words = t || [], this.sigBytes = null != e ? e : 4 * t.length
		}, toString: function (t) {
			return (t || o).stringify(this)
		}, concat: function (t) {
			var e = this.words, i = t.words, n = this.sigBytes, r = t.sigBytes;
			if (this.clamp(), n % 4) for (var o = 0; o < r; o++) {
				var s = i[o >>> 2] >>> 24 - o % 4 * 8 & 255;
				e[n + o >>> 2] |= s << 24 - (n + o) % 4 * 8
			} else if (65535 < i.length) for (o = 0; o < r; o += 4) e[n + o >>> 2] = i[o >>> 2]; else e.push.apply(e, i);
			return this.sigBytes += r, this
		}, clamp: function () {
			var t = this.words, e = this.sigBytes;
			t[e >>> 2] &= 4294967295 << 32 - e % 4 * 8, t.length = l.ceil(e / 4)
		}, clone: function () {
			var t = i.clone.call(this);
			return t.words = this.words.slice(0), t
		}, random: function (t) {
			for (var e = [], i = 0; i < t; i += 4) e.push(4294967296 * l.random() | 0);
			return new u.init(e, t)
		}
	}), r = t.enc = {}, o = r.Hex = {
		stringify: function (t) {
			for (var e = t.words, i = t.sigBytes, n = [], r = 0; r < i; r++) {
				var o = e[r >>> 2] >>> 24 - r % 4 * 8 & 255;
				n.push((o >>> 4).toString(16)), n.push((15 & o).toString(16))
			}
			return n.join("")
		}, parse: function (t) {
			for (var e = t.length, i = [], n = 0; n < e; n += 2) i[n >>> 3] |= parseInt(t.substr(n, 2), 16) << 24 - n % 8 * 4;
			return new u.init(i, e / 2)
		}
	}, s = r.Latin1 = {
		stringify: function (t) {
			for (var e = t.words, i = t.sigBytes, n = [], r = 0; r < i; r++) {
				var o = e[r >>> 2] >>> 24 - r % 4 * 8 & 255;
				n.push(String.fromCharCode(o))
			}
			return n.join("")
		}, parse: function (t) {
			for (var e = t.length, i = [], n = 0; n < e; n++) i[n >>> 2] |= (255 & t.charCodeAt(n)) << 24 - n % 4 * 8;
			return new u.init(i, e)
		}
	}, a = r.Utf8 = {
		stringify: function (t) {
			try {
				return decodeURIComponent(escape(s.stringify(t)))
			} catch (t) {
				throw new Error("Malformed UTF-8 data")
			}
		}, parse: function (t) {
			return s.parse(unescape(encodeURIComponent(t)))
		}
	}, p = (r.Base64 = {
		stringify: function (t) {
			var e = t.words, i = t.sigBytes, n = this._map;
			t.clamp();
			for (var r = [], o = 0; o < i; o += 3) for (var s = (e[o >>> 2] >>> 24 - o % 4 * 8 & 255) << 16 | (e[o + 1 >>> 2] >>> 24 - (o + 1) % 4 * 8 & 255) << 8 | e[o + 2 >>> 2] >>> 24 - (o + 2) % 4 * 8 & 255, a = 0; a < 4 && o + .75 * a < i; a++) r.push(n.charAt(s >>> 6 * (3 - a) & 63));
			var p = n.charAt(64);
			if (p) for (; r.length % 4;) r.push(p);
			return r.join("")
		}, parse: function (t) {
			var e = t.length, i = this._map, n = i.charAt(64);
			if (n) {
				var r = t.indexOf(n);
				-1 != r && (e = r)
			}
			for (var o = [], s = 0, a = 0; a < e; a++) if (a % 4) {
				var p = i.indexOf(t.charAt(a - 1)) << a % 4 * 2, c = i.indexOf(t.charAt(a)) >>> 6 - a % 4 * 2;
				o[s >>> 2] |= (p | c) << 24 - s % 4 * 8, s++
			}
			return u.create(o, s)
		}, _map: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/="
	}, e.BufferedBlockAlgorithm = i.extend({
		reset: function () {
			this._data = new u.init, this._nDataBytes = 0
		}, _append: function (t) {
			"string" == typeof t && (t = a.parse(t)), this._data.concat(t), this._nDataBytes += t.sigBytes
		}, _process: function (t) {
			var e = this._data, i = e.words, n = e.sigBytes, r = this.blockSize, o = n / (4 * r),
				s = (o = t ? l.ceil(o) : l.max((0 | o) - this._minBufferSize, 0)) * r, a = l.min(4 * s, n);
			if (s) {
				for (var p = 0; p < s; p += r) this._doProcessBlock(i, p);
				var c = i.splice(0, s);
				e.sigBytes -= a
			}
			return new u.init(c, a)
		}, clone: function () {
			var t = i.clone.call(this);
			return t._data = this._data.clone(), t
		}, _minBufferSize: 0
	})), c = e.Hasher = p.extend({
		cfg: i.extend(), init: function (t) {
			this.cfg = this.cfg.extend(t), this.reset()
		}, reset: function () {
			p.reset.call(this), this._doReset()
		}, update: function (t) {
			return this._append(t), this._process(), this
		}, finalize: function (t) {
			return t && this._append(t), this._doFinalize()
		}, blockSize: 16, _createHelper: function (i) {
			return function (t, e) {
				return new i.init(e).finalize(t)
			}
		}, _createHmacHelper: function (i) {
			return function (t, e) {
				return new d.HMAC.init(i, e).finalize(t)
			}
		}
	}), d = t.algo = {}, h = [], f = d.SHA1 = c.extend({
		_doReset: function () {
			this._hash = new u.init([1732584193, 4023233417, 2562383102, 271733878, 3285377520])
		}, _doProcessBlock: function (t, e) {
			for (var i = this._hash.words, n = i[0], r = i[1], o = i[2], s = i[3], a = i[4], p = 0; p < 80; p++) {
				if (p < 16) h[p] = 0 | t[e + p]; else {
					var c = h[p - 3] ^ h[p - 8] ^ h[p - 14] ^ h[p - 16];
					h[p] = c << 1 | c >>> 31
				}
				var l = (n << 5 | n >>> 27) + a + h[p];
				l += p < 20 ? 1518500249 + (r & o | ~r & s) : p < 40 ? 1859775393 + (r ^ o ^ s) : p < 60 ? (r & o | r & s | o & s) - 1894007588 : (r ^ o ^ s) - 899497514, a = s, s = o, o = r << 30 | r >>> 2, r = n, n = l
			}
			i[0] = i[0] + n | 0, i[1] = i[1] + r | 0, i[2] = i[2] + o | 0, i[3] = i[3] + s | 0, i[4] = i[4] + a | 0
		}, _doFinalize: function () {
			var t = this._data, e = t.words, i = 8 * this._nDataBytes, n = 8 * t.sigBytes;
			return e[n >>> 5] |= 128 << 24 - n % 32, e[14 + (64 + n >>> 9 << 4)] = l.floor(i / 4294967296), e[15 + (64 + n >>> 9 << 4)] = i, t.sigBytes = 4 * e.length, this._process(), this._hash
		}, clone: function () {
			var t = c.clone.call(this);
			return t._hash = this._hash.clone(), t
		}
	});
	return t.SHA1 = c._createHelper(f), t.HmacSHA1 = c._createHmacHelper(f), t
}(Math)) || function (a) {
	function i() {
	}

	var t = {}, e = t.lib = {}, n = e.Base = {
		extend: function (t) {
			i.prototype = this;
			var e = new i;
			return t && e.mixIn(t), e.hasOwnProperty("init") || (e.init = function () {
				e.$super.init.apply(this, arguments)
			}), (e.init.prototype = e).$super = this, e
		}, create: function () {
			var t = this.extend();
			return t.init.apply(t, arguments), t
		}, init: function () {
		}, mixIn: function (t) {
			for (var e in t) t.hasOwnProperty(e) && (this[e] = t[e]);
			t.hasOwnProperty("toString") && (this.toString = t.toString)
		}, clone: function () {
			return this.init.prototype.extend(this)
		}
	}, p = e.WordArray = n.extend({
		init: function (t, e) {
			t = this.words = t || [], this.sigBytes = null != e ? e : 4 * t.length
		}, toString: function (t) {
			return (t || o).stringify(this)
		}, concat: function (t) {
			var e = this.words, i = t.words, n = this.sigBytes;
			if (t = t.sigBytes, this.clamp(), n % 4) for (var r = 0; r < t; r++) e[n + r >>> 2] |= (i[r >>> 2] >>> 24 - r % 4 * 8 & 255) << 24 - (n + r) % 4 * 8; else if (65535 < i.length) for (r = 0; r < t; r += 4) e[n + r >>> 2] = i[r >>> 2]; else e.push.apply(e, i);
			return this.sigBytes += t, this
		}, clamp: function () {
			var t = this.words, e = this.sigBytes;
			t[e >>> 2] &= 4294967295 << 32 - e % 4 * 8, t.length = a.ceil(e / 4)
		}, clone: function () {
			var t = n.clone.call(this);
			return t.words = this.words.slice(0), t
		}, random: function (t) {
			for (var e = [], i = 0; i < t; i += 4) e.push(4294967296 * a.random() | 0);
			return new p.init(e, t)
		}
	}), r = t.enc = {}, o = r.Hex = {
		stringify: function (t) {
			var e = t.words;
			t = t.sigBytes;
			for (var i = [], n = 0; n < t; n++) {
				var r = e[n >>> 2] >>> 24 - n % 4 * 8 & 255;
				i.push((r >>> 4).toString(16)), i.push((15 & r).toString(16))
			}
			return i.join("")
		}, parse: function (t) {
			for (var e = t.length, i = [], n = 0; n < e; n += 2) i[n >>> 3] |= parseInt(t.substr(n, 2), 16) << 24 - n % 8 * 4;
			return new p.init(i, e / 2)
		}
	}, s = r.Latin1 = {
		stringify: function (t) {
			var e = t.words;
			t = t.sigBytes;
			for (var i = [], n = 0; n < t; n++) i.push(String.fromCharCode(e[n >>> 2] >>> 24 - n % 4 * 8 & 255));
			return i.join("")
		}, parse: function (t) {
			for (var e = t.length, i = [], n = 0; n < e; n++) i[n >>> 2] |= (255 & t.charCodeAt(n)) << 24 - n % 4 * 8;
			return new p.init(i, e)
		}
	}, c = r.Utf8 = {
		stringify: function (t) {
			try {
				return decodeURIComponent(escape(s.stringify(t)))
			} catch (t) {
				throw Error("Malformed UTF-8 data")
			}
		}, parse: function (t) {
			return s.parse(unescape(encodeURIComponent(t)))
		}
	}, l = e.BufferedBlockAlgorithm = n.extend({
		reset: function () {
			this._data = new p.init, this._nDataBytes = 0
		}, _append: function (t) {
			"string" == typeof t && (t = c.parse(t)), this._data.concat(t), this._nDataBytes += t.sigBytes
		}, _process: function (t) {
			var e = this._data, i = e.words, n = e.sigBytes, r = this.blockSize, o = n / (4 * r);
			if (t = (o = t ? a.ceil(o) : a.max((0 | o) - this._minBufferSize, 0)) * r, n = a.min(4 * t, n), t) {
				for (var s = 0; s < t; s += r) this._doProcessBlock(i, s);
				s = i.splice(0, t), e.sigBytes -= n
			}
			return new p.init(s, n)
		}, clone: function () {
			var t = n.clone.call(this);
			return t._data = this._data.clone(), t
		}, _minBufferSize: 0
	});
	e.Hasher = l.extend({
		cfg: n.extend(), init: function (t) {
			this.cfg = this.cfg.extend(t), this.reset()
		}, reset: function () {
			l.reset.call(this), this._doReset()
		}, update: function (t) {
			return this._append(t), this._process(), this
		}, finalize: function (t) {
			return t && this._append(t), this._doFinalize()
		}, blockSize: 16, _createHelper: function (i) {
			return function (t, e) {
				return new i.init(e).finalize(t)
			}
		}, _createHmacHelper: function (i) {
			return function (t, e) {
				return new u.HMAC.init(i, e).finalize(t)
			}
		}
	});
	var u = t.algo = {};
	return t
}(Math);

function toBase64(t) {
	return CryptoJS && CryptoJS.enc.Base64 ? CryptoJS.enc.Base64.stringify(CryptoJS.enc.Latin1.parse(t)) : Base64.encode(t)
}

function toSHA1(t) {
	return CryptoJS && CryptoJS.SHA1 ? CryptoJS.SHA1(t).toString() : Crypto.util.bytesToHex(Crypto.SHA1(t, {asBytes: !0}))
}

function toSHA256(t) {
	if ("[object ArrayBuffer]" !== Object.prototype.toString.call(t)) return CryptoJS.SHA256(t).toString(CryptoJS.enc.Hex);
	for (var e = new Uint8Array(t), i = [], n = 0; n < e.length; n += 4) i.push(e[n] << 24 | e[n + 1] << 16 | e[n + 2] << 8 | e[n + 3]);
	return CryptoJS.SHA256(CryptoJS.lib.WordArray.create(i, e.length)).toString(CryptoJS.enc.Hex)
}

function isDate(t) {
	if ("[object Date]" === Object.prototype.toString.call(t)) var e = t; else e = new Date(t);
	return "[object Date]" === Object.prototype.toString.call(e) ? isNaN(e.valueOf()) ? (ADL.XAPIWrapper.log("Invalid date String passed"), null) : e : (ADL.XAPIWrapper.log("Invalid date object"), null)
}

!function (r) {
	function t(t) {
		return 4294967296 * (t - (0 | t)) | 0
	}

	for (var e = CryptoJS, i = (o = e.lib).WordArray, n = o.Hasher, o = e.algo, s = [], f = [], a = 2, p = 0; p < 64;) {
		var c;
		t:{
			c = a;
			for (var l = r.sqrt(c), u = 2; u <= l; u++) if (!(c % u)) {
				c = !1;
				break t
			}
			c = !0
		}
		c && (p < 8 && (s[p] = t(r.pow(a, .5))), f[p] = t(r.pow(a, 1 / 3)), p++), a++
	}
	var g = [];
	o = o.SHA256 = n.extend({
		_doReset: function () {
			this._hash = new i.init(s.slice(0))
		}, _doProcessBlock: function (t, e) {
			for (var i = this._hash.words, n = i[0], r = i[1], o = i[2], s = i[3], a = i[4], p = i[5], c = i[6], l = i[7], u = 0; u < 64; u++) {
				if (u < 16) g[u] = 0 | t[e + u]; else {
					var d = g[u - 15], h = g[u - 2];
					g[u] = ((d << 25 | d >>> 7) ^ (d << 14 | d >>> 18) ^ d >>> 3) + g[u - 7] + ((h << 15 | h >>> 17) ^ (h << 13 | h >>> 19) ^ h >>> 10) + g[u - 16]
				}
				d = l + ((a << 26 | a >>> 6) ^ (a << 21 | a >>> 11) ^ (a << 7 | a >>> 25)) + (a & p ^ ~a & c) + f[u] + g[u], h = ((n << 30 | n >>> 2) ^ (n << 19 | n >>> 13) ^ (n << 10 | n >>> 22)) + (n & r ^ n & o ^ r & o), l = c, c = p, p = a, a = s + d | 0, s = o, o = r, r = n, n = d + h | 0
			}
			i[0] = i[0] + n | 0, i[1] = i[1] + r | 0, i[2] = i[2] + o | 0, i[3] = i[3] + s | 0, i[4] = i[4] + a | 0, i[5] = i[5] + p | 0, i[6] = i[6] + c | 0, i[7] = i[7] + l | 0
		}, _doFinalize: function () {
			var t = this._data, e = t.words, i = 8 * this._nDataBytes, n = 8 * t.sigBytes;
			return e[n >>> 5] |= 128 << 24 - n % 32, e[14 + (64 + n >>> 9 << 4)] = r.floor(i / 4294967296), e[15 + (64 + n >>> 9 << 4)] = i, t.sigBytes = 4 * e.length, this._process(), this._hash
		}, clone: function () {
			var t = n.clone.call(this);
			return t._hash = this._hash.clone(), t
		}
	});
	e.SHA256 = n._createHelper(o), e.HmacSHA256 = n._createHmacHelper(o)
}(Math), function () {
	var c = CryptoJS.enc.Utf8;
	CryptoJS.algo.HMAC = CryptoJS.lib.Base.extend({
		init: function (t, e) {
			t = this._hasher = new t.init, "string" == typeof e && (e = c.parse(e));
			var i = t.blockSize, n = 4 * i;
			e.sigBytes > n && (e = t.finalize(e)), e.clamp();
			for (var r = this._oKey = e.clone(), o = this._iKey = e.clone(), s = r.words, a = o.words, p = 0; p < i; p++) s[p] ^= 1549556828, a[p] ^= 909522486;
			r.sigBytes = o.sigBytes = n, this.reset()
		}, reset: function () {
			var t = this._hasher;
			t.reset(), t.update(this._iKey)
		}, update: function (t) {
			return this._hasher.update(t), this
		}, finalize: function (t) {
			var e = this._hasher;
			return t = e.finalize(t), e.reset(), e.finalize(this._oKey.clone().concat(t))
		}
	})
}(), function (t) {
	if (void 0 === t.TextEncoder) {
		function p(t, e) {
			if (String.prototype.codePointAt) return t.codePointAt(e);
			var i = (t = String(t)).length, n = e ? Number(e) : 0;
			if (n != n && (n = 0), !(n < 0 || i <= n)) {
				var r, o = t.charCodeAt(n);
				return 55296 <= o && o <= 56319 && n + 1 < i && 56320 <= (r = t.charCodeAt(n + 1)) && r <= 57343 ? 1024 * (o - 55296) + r - 56320 + 65536 : o
			}
		}

		function e(t) {
			switch (t = t || "utf8") {
				case"utf-8":
				case"utf8":
					break;
				default:
					throw"TextEncoder only supports utf8"
			}
		}

		e.prototype.encode = function (t) {
			for (var e = new Uint8Array(3 * t.length), i = -1, n = t.length, r = 0; r < n;) {
				var o = p(t, r), s = 0, a = 0;
				for (o <= 127 ? a = s = 0 : o <= 2047 ? (s = 6, a = 192) : o <= 65535 ? (s = 12, a = 224) : o <= 2097151 && (s = 18, a = 240), e[i += 1] = a | o >> s, s -= 6; 0 <= s;) e[i += 1] = 128 | o >> s & 63, s -= 6;
				r += 65536 <= o ? 2 : 1
			}
			return e.subarray(0, i + 1)
		}, t.TextEncoder = e
	}
	if (void 0 === t.TextDecoder) {
		function a(t) {
			if (String.fromCodePoint) return String.fromCodePoint(t);
			for (var e = [], i = "", n = 0, r = arguments.length; n !== r; ++n) {
				var o = +arguments[n];
				if (!(o < 1114111 && o >>> 0 === o)) throw RangeError("Invalid code point: " + o);
				16383 <= (o <= 65535 ? e.push(o) : (o -= 65536, e.push(55296 + (o >> 10), o % 1024 + 56320))) && (i += String.fromCharCode.apply(null, e), e.length = 0)
			}
			return i + String.fromCharCode.apply(null, e)
		}

		function i(t) {
			switch (t = t || "utf8") {
				case"utf-8":
				case"utf8":
					break;
				default:
					throw"TextDecoder only supports utf8"
			}
		}

		i.prototype.decode = function (t) {
			for (var e = "", i = 0; i < t.length;) {
				var n = t[i], r = 0, o = 0;
				if (n <= 127 ? (r = 0, o = 255 & n) : n <= 223 ? (r = 1, o = 31 & n) : n <= 239 ? (r = 2, o = 15 & n) : n <= 244 && (r = 3, o = 7 & n), 0 < t.length - i - r) for (var s = 0; s < r;) o = o << 6 | 63 & (n = t[i + s + 1]), s += 1; else o = 65533, r = t.length - i;
				e += a(o), i += r + 1
			}
			return e
		}, t.TextDecoder = i
	}
}(this), (window.ADL = window.ADL || {}).activityTypes = {
	assessment: "http://adlnet.gov/expapi/activities/assessment",
	attempt: "http://adlnet.gov/expapi/activities/attempt",
	course: "http://adlnet.gov/expapi/activities/course",
	file: "http://adlnet.gov/expapi/activities/file",
	cmiInteraction: "http://adlnet.gov/expapi/activities/cmi.interaction",
	interaction: "http://adlnet.gov/expapi/activities/interaction",
	lesson: "http://adlnet.gov/expapi/activities/lesson",
	link: "http://adlnet.gov/expapi/activities/link",
	media: "http://adlnet.gov/expapi/activities/media",
	meeting: "http://adlnet.gov/expapi/activities/meeting",
	module: "http://adlnet.gov/expapi/activities/module",
	objective: "http://adlnet.gov/expapi/activities/objective",
	performance: "http://adlnet.gov/expapi/activities/performance",
	profile: "http://adlnet.gov/expapi/activities/profile",
	question: "http://adlnet.gov/expapi/activities/question",
	simulation: "http://adlnet.gov/expapi/activities/simulation"
}, (window.ADL = window.ADL || {}).verbs = {
	abandoned: {id: "https://w3id.org/xapi/adl/verbs/abandoned", display: {"en-US": "abandoned"}},
	answered: {
		id: "http://adlnet.gov/expapi/verbs/answered",
		display: {
			"de-DE": "beantwortete",
			"en-US": "answered",
			"fr-FR": "a répondu",
			"es-ES": "contestó",
			"ar-AR": "أجاب"
		}
	},
	asked: {
		id: "http://adlnet.gov/expapi/verbs/asked",
		display: {"de-DE": "fragte", "en-US": "asked", "fr-FR": "a demandé", "es-ES": "preguntó", "ar-AR": "طلب"}
	},
	attempted: {
		id: "http://adlnet.gov/expapi/verbs/attempted",
		display: {"de-DE": "versuchte", "en-US": "attempted", "fr-FR": "a essayé", "es-ES": "intentó", "ar-AR": "حاول"}
	},
	attended: {
		id: "http://adlnet.gov/expapi/verbs/attended",
		display: {"de-DE": "nahm teil an", "en-US": "attended", "fr-FR": "a suivi", "es-ES": "asistió", "ar-AR": "حضر"}
	},
	commented: {
		id: "http://adlnet.gov/expapi/verbs/commented",
		display: {
			"de-DE": "kommentierte",
			"en-US": "commented",
			"fr-FR": "a commenté",
			"es-ES": "comentó",
			"ar-AR": "علق"
		}
	},
	completed: {
		id: "http://adlnet.gov/expapi/verbs/completed",
		display: {"de-DE": "beendete", "en-US": "completed", "fr-FR": "a terminé", "es-ES": "completó", "ar-AR": "أكمل"}
	},
	exited: {
		id: "http://adlnet.gov/expapi/verbs/exited",
		display: {"de-DE": "verließ", "en-US": "exited", "fr-FR": "a quitté", "es-ES": "salió", "ar-AR": "خرج"}
	},
	experienced: {
		id: "http://adlnet.gov/expapi/verbs/experienced",
		display: {
			"de-DE": "erlebte",
			"en-US": "experienced",
			"fr-FR": "a éprouvé",
			"es-ES": "experimentó",
			"ar-AR": "شاهد"
		}
	},
	failed: {
		id: "http://adlnet.gov/expapi/verbs/failed",
		display: {"de-DE": "verfehlte", "en-US": "failed", "fr-FR": "a échoué", "es-ES": "fracasó", "ar-AR": "فشل"}
	},
	imported: {
		id: "http://adlnet.gov/expapi/verbs/imported",
		display: {
			"de-DE": "importierte",
			"en-US": "imported",
			"fr-FR": "a importé",
			"es-ES": "importó",
			"ar-AR": "مستورد"
		}
	},
	initialized: {
		id: "http://adlnet.gov/expapi/verbs/initialized",
		display: {
			"de-DE": "initialisierte",
			"en-US": "initialized",
			"fr-FR": "a initialisé",
			"es-ES": "inicializó",
			"ar-AR": "بدأ"
		}
	},
	interacted: {
		id: "http://adlnet.gov/expapi/verbs/interacted",
		display: {
			"de-DE": "interagierte",
			"en-US": "interacted",
			"fr-FR": "a interagi",
			"es-ES": "interactuó",
			"ar-AR": "تفاعل"
		}
	},
	launched: {
		id: "http://adlnet.gov/expapi/verbs/launched",
		display: {"de-DE": "startete", "en-US": "launched", "fr-FR": "a lancé", "es-ES": "lanzó", "ar-AR": "أطلق"}
	},
	mastered: {
		id: "http://adlnet.gov/expapi/verbs/mastered",
		display: {"de-DE": "meisterte", "en-US": "mastered", "fr-FR": "a maîtrisé", "es-ES": "dominó", "ar-AR": "أتقن"}
	},
	passed: {
		id: "http://adlnet.gov/expapi/verbs/passed",
		display: {"de-DE": "bestand", "en-US": "passed", "fr-FR": "a réussi", "es-ES": "aprobó", "ar-AR": "نجح"}
	},
	preferred: {
		id: "http://adlnet.gov/expapi/verbs/preferred",
		display: {
			"de-DE": "bevorzugte",
			"en-US": "preferred",
			"fr-FR": "a préféré",
			"es-ES": "prefirió",
			"ar-AR": "فضل"
		}
	},
	progressed: {
		id: "http://adlnet.gov/expapi/verbs/progressed",
		display: {
			"de-DE": "machte Fortschritt mit",
			"en-US": "progressed",
			"fr-FR": "a progressé",
			"es-ES": "progresó",
			"ar-AR": "تقدم"
		}
	},
	registered: {
		id: "http://adlnet.gov/expapi/verbs/registered",
		display: {
			"de-DE": "registrierte",
			"en-US": "registered",
			"fr-FR": "a enregistré",
			"es-ES": "registró",
			"ar-AR": "سجل"
		}
	},
	responded: {
		id: "http://adlnet.gov/expapi/verbs/responded",
		display: {
			"de-DE": "reagierte",
			"en-US": "responded",
			"fr-FR": "a répondu",
			"es-ES": "respondió",
			"ar-AR": "استجاب"
		}
	},
	resumed: {
		id: "http://adlnet.gov/expapi/verbs/resumed",
		display: {
			"de-DE": "setzte fort",
			"en-US": "resumed",
			"fr-FR": "a repris",
			"es-ES": "continuó",
			"ar-AR": "استأنف"
		}
	},
	satisfied: {
		id: "https://w3id.org/xapi/adl/verbs/satisfied",
		display: {
			"de-DE": "befriedigt",
			"en-US": "satisfied",
			"fr-FR": "satisfaite",
			"es-ES": "satisfecho",
			"ar-AR": "راض"
		}
	},
	scored: {
		id: "http://adlnet.gov/expapi/verbs/scored",
		display: {"de-DE": "erreichte", "en-US": "scored", "fr-FR": "a marqué", "es-ES": "anotó", "ar-AR": "سحل النقاط"}
	},
	shared: {
		id: "http://adlnet.gov/expapi/verbs/shared",
		display: {"de-DE": "teilte", "en-US": "shared", "fr-FR": "a partagé", "es-ES": "compartió", "ar-AR": "شارك"}
	},
	suspended: {
		id: "http://adlnet.gov/expapi/verbs/suspended",
		display: {"de-DE": "pausierte", "en-US": "suspended", "fr-FR": "a suspendu", "es-ES": "aplazó", "ar-AR": "علق"}
	},
	terminated: {
		id: "http://adlnet.gov/expapi/verbs/terminated",
		display: {"de-DE": "beendete", "en-US": "terminated", "fr-FR": "a terminé", "es-ES": "terminó", "ar-AR": "أنهى"}
	},
	voided: {
		id: "http://adlnet.gov/expapi/verbs/voided",
		display: {"de-DE": "entwertete", "en-US": "voided", "fr-FR": "a annulé", "es-ES": "anuló", "ar-AR": "فرغ"}
	},
	waived: {
		id: "https://w3id.org/xapi/adl/verbs/waived",
		display: {"de-DE": "verzichtet", "en-US": "waived", "fr-FR": "renoncé", "es-ES": "renunciado", "ar-AR": "تخلى"}
	}
}, Date.prototype.toISOString || function () {
	function t(t) {
		var e = String(t);
		return 1 === e.length && (e = "0" + e), e
	}

	Date.prototype.toISOString = function () {
		return this.getUTCFullYear() + "-" + t(this.getUTCMonth() + 1) + "-" + t(this.getUTCDate()) + "T" + t(this.getUTCHours()) + ":" + t(this.getUTCMinutes()) + ":" + t(this.getUTCSeconds()) + "." + String((this.getUTCMilliseconds() / 1e3).toFixed(3)).slice(2, 5) + "Z"
	}
}(), function (_) {
	u.debug = !1;

	function t(t, e) {
		function i(t) {
			var e = document.createElement("a");
			if (e.href = t, e.protocol && e.host) return e.protocol + "//" + e.host;
			if (e.href) {
				var i = e.href.split("//");
				return i[0] + "//" + i[1].substr(0, i[1].indexOf("/"))
			}
			_.XAPIWrapper.log("Couldn't create base url from endpoint: " + t)
		}

		function n(t, e, i) {
			t.auth = "Basic " + toBase64(e + ":" + i)
		}

		this.lrs = function (t) {
			var e, i, n = ["endpoint", "auth", "actor", "registration", "activity_id", "grouping", "activity_platform"],
				r = new Object;
			if (void 0 !== (e = function () {
				var t, e, i, n, r;
				for (t = window.location.search.substr(1), e = t.split("&"), r = {}, n = 0; n < e.length; n++) 2 === (i = e[n].split("=")).length && i[0] && (r[i[0]] = decodeURIComponent(i[1]));
				return r
			}()) && 0 !== Object.keys(e).length) {
				for (var o = 0; o < n.length; o++) e[i = n[o]] && (r[i] = e[i], delete e[i]);
				r = a(t, r)
			} else r = t;
			return r
		}(t || {}), this.lrs.user && this.lrs.password && n(this.lrs, this.lrs.user, this.lrs.password), this.base = i(this.lrs.endpoint), this.withCredentials = !1, t && void 0 !== t.withCredentials && (this.withCredentials = t.withCredentials), this.strictCallbacks = !1, this.strictCallbacks = t && t.strictCallbacks, e && r.call(this) && window.ADL.XHR_request(this.lrs, this.lrs.endpoint + "about", "GET", null, null, function (t) {
			if (200 == t.status) try {
				var e = JSON.parse(t.response), i = !1;
				for (var n in e.version) if (e.version.hasOwnProperty(n) && e.version[n] == _.XAPIWrapper.xapiVersion) {
					i = !0;
					break
				}
				i || _.XAPIWrapper.log("The lrs version [" + e.version + "] does not match this wrapper's XAPI version [" + _.XAPIWrapper.xapiVersion + "]")
			} catch (t) {
				_.XAPIWrapper.log("The response was not an about object")
			} else _.XAPIWrapper.log("The request to get information about the LRS failed: " + t)
		}, null, !1, null, this.withCredentials, !1), this.searchParams = function () {
			return {format: "exact"}
		}, this.hash = function (t) {
			if (!t) return null;
			try {
				return toSHA1(t)
			} catch (t) {
				return _.XAPIWrapper.log("Error trying to hash -- " + t), null
			}
		}, this.changeConfig = function (t) {
			try {
				_.XAPIWrapper.log("updating lrs object with new configuration"), this.lrs = a(this.lrs, t), t.user && t.password && this.updateAuth(this.lrs, t.user, t.password), this.base = i(this.lrs.endpoint), this.withCredentials = t.withCredentials, this.strictCallbacks = t.strictCallbacks
			} catch (t) {
				_.XAPIWrapper.log("error while changing configuration -- " + t)
			}
		}, this.updateAuth = n
	}

	var e, i = ((e = {endpoint: "http://localhost:8000/xapi/"}).auth = "Basic " + toBase64("tom:1234"), e);

	function r() {
		try {
			return null != this.lrs.endpoint && "" != this.lrs.endpoint
		} catch (t) {
			return !1
		}
	}

	function u(t) {
		if (!u.debug) return !1;
		try {
			return console.log(t), !0
		} catch (t) {
			return !1
		}
	}

	function a(e, i) {
		for (var n in i) if (0 != i.hasOwnProperty(n)) {
			u(n + " : " + i[n]);
			try {
				i[n].constructor == Object ? e[n] = a(e[n], i[n]) : (null == e && (e = new Object), e[n] = i[n])
			} catch (t) {
				null == e && (e = new Object), e[n] = i[n]
			}
		}
		return e
	}

	t.prototype.xapiVersion = "1.0.1", t.prototype.prepareStatement = function (t) {
		void 0 === t.actor ? t.actor = JSON.parse(this.lrs.actor) : "string" == typeof t.actor && (t.actor = JSON.parse(t.actor)), (this.lrs.grouping || this.lrs.registration || this.lrs.activity_platform) && (t.context || (t.context = {})), this.lrs.grouping && (t.context.contextActivities || (t.context.contextActivities = {}), Array.isArray(t.context.contextActivities.grouping) ? t.context.contextActivities.grouping.splice(0, 0, {id: this.lrs.grouping}) : t.context.contextActivities.grouping = [{id: this.lrs.grouping}]), this.lrs.registration && (t.context.registration = this.lrs.registration), this.lrs.activity_platform && (t.context.platform = this.lrs.activity_platform)
	}, t.prototype.testConfig = r, t.prototype.log = u, t.prototype.defaultEncoding = "utf-8", t.prototype.sendStatement = function (t, e, i) {
		if (this.testConfig()) {
			var n;
			this.prepareStatement(t), t.id ? n = t.id : (n = _.ruuid(), t.id = n);
			var r = JSON.stringify(t), o = null;
			i && 0 < i.length && (o = {}, r = this.buildMultipartPost(t, i, o));
			var s = _.XHR_request(this.lrs, this.lrs.endpoint + "statements", "POST", r, this.lrs.auth, e, {id: n}, null, o, this.withCredentials, this.strictCallbacks);
			if (!e) return {xhr: s, id: n}
		}
	}, t.prototype.stringToArrayBuffer = function (t, e) {
		return e = e || _.XAPIWrapper.defaultEncoding, new TextEncoder(e).encode(t).buffer
	}, t.prototype.stringFromArrayBuffer = function (t, e) {
		return e = e || _.XAPIWrapper.defaultEncoding, new TextDecoder(e).decode(t)
	}, t.prototype.buildMultipartPost = function (t, e, i) {
		t.attachments = [];
		for (var n = 0; n < e.length; n++) "signature" == e[n].type && (e[n].type = {
			usageType: "http://adlnet.gov/expapi/attachments/signature",
			display: {"en-US": "A JWT signature"},
			description: {"en-US": "A signature proving the statement was not modified"},
			contentType: "application/octet-stream"
		}), "string" == typeof e[n].value && (e[n].value = this.stringToArrayBuffer(e[n].value)), e[n].type.length = e[n].value.byteLength, e[n].type.sha2 = toSHA256(e[n].value), t.attachments.push(e[n].type);
		var r = [], o = (Math.random() + " ").substring(2, 10) + (Math.random() + " ").substring(2, 10);
		i["Content-Type"] = "multipart/mixed; boundary=" + o;
		var s = "\r\n",
			a = ["--" + o, "Content-Type: application/json", 'Content-Disposition: form-data; name="statement"', "", JSON.stringify(t)].join(s) + s;
		for (var n in r.push(a), e) if (e.hasOwnProperty(n)) {
			var p = ["--" + o, "Content-Type: " + e[n].type.contentType, "Content-Transfer-Encoding: binary", "X-Experience-API-Hash: " + e[n].type.sha2].join(s) + s + s;
			r.push(p), r.push(e[n].value)
		}
		return r.push(s + "--" + o + "--" + s), new Blob(r)
	}, t.prototype.sendStatements = function (t, e) {
		if (this.testConfig()) {
			for (var i in t) t.hasOwnProperty(i) && this.prepareStatement(t[i]);
			var n = _.XHR_request(this.lrs, this.lrs.endpoint + "statements", "POST", JSON.stringify(t), this.lrs.auth, e, null, !1, null, this.withCredentials, this.strictCallbacks);
			if (!e) return n
		}
	}, t.prototype.getStatements = function (t, e, i) {
		if (this.testConfig()) {
			var n = this.lrs.endpoint + "statements";
			if (e) n = this.base + e; else {
				var r = new Array;
				for (s in t) if (t.hasOwnProperty(s)) if ("until" == s || "since" == s) {
					var o = new Date(t[s]);
					r.push(s + "=" + encodeURIComponent(o.toISOString()))
				} else r.push(s + "=" + encodeURIComponent(t[s]));
				0 < r.length && (n = n + "?" + r.join("&"))
			}
			var a = _.XHR_request(this.lrs, n, "GET", null, this.lrs.auth, i, null, !1, null, this.withCredentials, this.strictCallbacks);
			if (void 0 === a || 404 == a.status) return null;
			try {
				return JSON.parse(a.response)
			} catch (t) {
				return a.response
			}
		}
	}, t.prototype.getActivities = function (t, e) {
		if (this.testConfig()) {
			var i = this.lrs.endpoint + "activities?activityId=<activityid>";
			i = i.replace("<activityid>", encodeURIComponent(t));
			var n = _.XHR_request(this.lrs, i, "GET", null, this.lrs.auth, e, null, !0, null, this.withCredentials, this.strictCallbacks);
			if (void 0 === n || 404 == n.status) return null;
			try {
				return JSON.parse(n.response)
			} catch (t) {
				return n.response
			}
		}
	}, t.prototype.sendState = function (t, e, i, n, r, o, s, a) {
		if (this.testConfig()) {
			var p = this.lrs.endpoint + "activities/state?activityId=<activity ID>&agent=<agent>&stateId=<stateid>";
			p = (p = (p = p.replace("<activity ID>", encodeURIComponent(t))).replace("<agent>", encodeURIComponent(JSON.stringify(e)))).replace("<stateid>", encodeURIComponent(i)), n && (p += "&registration=" + encodeURIComponent(n));
			var c = null;
			o && s ? u("Can't have both If-Match and If-None-Match") : o ? c = {"If-Match": _.formatHash(o)} : s && (c = {"If-None-Match": _.formatHash(s)});
			var l = "PUT";
			if (!r) return this.log("No activity state was included."), !1;
			r instanceof Array ? (r = JSON.stringify(r), (c = c || {})["Content-Type"] = "application/json") : r instanceof Object ? (r = JSON.stringify(r), (c = c || {})["Content-Type"] = "application/json", l = "POST") : (c = c || {})["Content-Type"] = "application/octet-stream", _.XHR_request(this.lrs, p, l, r, this.lrs.auth, a, null, null, c, this.withCredentials, this.strictCallbacks)
		}
	}, t.prototype.getState = function (t, e, i, n, r, o) {
		if (this.testConfig()) {
			var s = this.lrs.endpoint + "activities/state?activityId=<activity ID>&agent=<agent>";
			s = (s = s.replace("<activity ID>", encodeURIComponent(t))).replace("<agent>", encodeURIComponent(JSON.stringify(e))), i && (s += "&stateId=" + encodeURIComponent(i)), n && (s += "&registration=" + encodeURIComponent(n)), r && null != (r = isDate(r)) && (s += "&since=" + encodeURIComponent(r.toISOString()));
			var a = _.XHR_request(this.lrs, s, "GET", null, this.lrs.auth, o, null, !0, null, this.withCredentials, this.strictCallbacks);
			if (void 0 === a || 404 == a.status) return null;
			try {
				return JSON.parse(a.response)
			} catch (t) {
				return a.response
			}
		}
	}, t.prototype.deleteState = function (t, e, i, n, r, o, s) {
		if (this.testConfig()) {
			var a = this.lrs.endpoint + "activities/state?activityId=<activity ID>&agent=<agent>&stateId=<stateid>";
			a = (a = (a = a.replace("<activity ID>", encodeURIComponent(t))).replace("<agent>", encodeURIComponent(JSON.stringify(e)))).replace("<stateid>", encodeURIComponent(i)), n && (a += "&registration=" + encodeURIComponent(n));
			var p = null;
			r && o ? u("Can't have both If-Match and If-None-Match") : r ? p = {"If-Match": _.formatHash(r)} : o && (p = {"If-None-Match": _.formatHash(o)});
			var c = _.XHR_request(this.lrs, a, "DELETE", null, this.lrs.auth, s, null, !1, p, this.withCredentials, this.strictCallbacks);
			if (void 0 === c || 404 == c.status) return null;
			try {
				return JSON.parse(c.response)
			} catch (t) {
				return c
			}
		}
	}, t.prototype.sendActivityProfile = function (t, e, i, n, r, o) {
		if (this.testConfig()) {
			var s = this.lrs.endpoint + "activities/profile?activityId=<activity ID>&profileId=<profileid>";
			s = (s = s.replace("<activity ID>", encodeURIComponent(t))).replace("<profileid>", encodeURIComponent(e));
			var a = null;
			n && r ? u("Can't have both If-Match and If-None-Match") : n ? a = {"If-Match": _.formatHash(n)} : r && (a = {"If-None-Match": _.formatHash(r)});
			var p = "PUT";
			if (!i) return this.log("No activity profile was included."), !1;
			i instanceof Array ? (i = JSON.stringify(i), (a = a || {})["Content-Type"] = "application/json") : i instanceof Object ? (i = JSON.stringify(i), (a = a || {})["Content-Type"] = "application/json", p = "POST") : (a = a || {})["Content-Type"] = "application/octet-stream", _.XHR_request(this.lrs, s, p, i, this.lrs.auth, o, null, !1, a, this.withCredentials, this.strictCallbacks)
		}
	}, t.prototype.getActivityProfile = function (t, e, i, n) {
		if (this.testConfig()) {
			var r = this.lrs.endpoint + "activities/profile?activityId=<activity ID>";
			r = r.replace("<activity ID>", encodeURIComponent(t)), e && (r += "&profileId=" + encodeURIComponent(e)), i && null != (i = isDate(i)) && (r += "&since=" + encodeURIComponent(i.toISOString()));
			var o = _.XHR_request(this.lrs, r, "GET", null, this.lrs.auth, n, null, !0, null, this.withCredentials, this.strictCallbacks);
			if (void 0 === o || 404 == o.status) return null;
			try {
				return JSON.parse(o.response)
			} catch (t) {
				return o.response
			}
		}
	}, t.prototype.deleteActivityProfile = function (t, e, i, n, r) {
		if (this.testConfig()) {
			var o = this.lrs.endpoint + "activities/profile?activityId=<activity ID>&profileId=<profileid>";
			o = (o = o.replace("<activity ID>", encodeURIComponent(t))).replace("<profileid>", encodeURIComponent(e));
			var s = null;
			i && n ? u("Can't have both If-Match and If-None-Match") : i ? s = {"If-Match": _.formatHash(i)} : n && (s = {"If-None-Match": _.formatHash(n)});
			var a = _.XHR_request(this.lrs, o, "DELETE", null, this.lrs.auth, r, null, !1, s, this.withCredentials, this.strictCallbacks);
			if (void 0 === a || 404 == a.status) return null;
			try {
				return JSON.parse(a.response)
			} catch (t) {
				return a
			}
		}
	}, t.prototype.getAgents = function (t, e) {
		if (this.testConfig()) {
			var i = this.lrs.endpoint + "agents?agent=<agent>";
			i = i.replace("<agent>", encodeURIComponent(JSON.stringify(t)));
			var n = _.XHR_request(this.lrs, i, "GET", null, this.lrs.auth, e, null, !0, null, this.withCredentials, this.strictCallbacks);
			if (void 0 === n || 404 == n.status) return null;
			try {
				return JSON.parse(n.response)
			} catch (t) {
				return n.response
			}
		}
	}, t.prototype.sendAgentProfile = function (t, e, i, n, r, o) {
		if (this.testConfig()) {
			var s = this.lrs.endpoint + "agents/profile?agent=<agent>&profileId=<profileid>";
			s = (s = s.replace("<agent>", encodeURIComponent(JSON.stringify(t)))).replace("<profileid>", encodeURIComponent(e));
			var a = null;
			n && r ? u("Can't have both If-Match and If-None-Match") : n ? a = {"If-Match": _.formatHash(n)} : r && (a = {"If-None-Match": _.formatHash(r)});
			var p = "PUT";
			if (!i) return this.log("No agent profile was included."), !1;
			i instanceof Array ? (i = JSON.stringify(i), (a = a || {})["Content-Type"] = "application/json") : i instanceof Object ? (i = JSON.stringify(i), (a = a || {})["Content-Type"] = "application/json", p = "POST") : (a = a || {})["Content-Type"] = "application/octet-stream", _.XHR_request(this.lrs, s, p, i, this.lrs.auth, o, null, !1, a, this.withCredentials, this.strictCallbacks)
		}
	}, t.prototype.getAgentProfile = function (t, e, i, n) {
		if (this.testConfig()) {
			var r = this.lrs.endpoint + "agents/profile?agent=<agent>";
			r = (r = r.replace("<agent>", encodeURIComponent(JSON.stringify(t)))).replace("<profileid>", encodeURIComponent(e)), e && (r += "&profileId=" + encodeURIComponent(e)), i && null != (i = isDate(i)) && (r += "&since=" + encodeURIComponent(i.toISOString()));
			var o = _.XHR_request(this.lrs, r, "GET", null, this.lrs.auth, n, null, !0, null, this.withCredentials, this.strictCallbacks);
			if (void 0 === o || 404 == o.status) return null;
			try {
				return JSON.parse(o.response)
			} catch (t) {
				return o.response
			}
		}
	}, t.prototype.deleteAgentProfile = function (t, e, i, n, r) {
		if (this.testConfig()) {
			var o = this.lrs.endpoint + "agents/profile?agent=<agent>&profileId=<profileid>";
			o = (o = o.replace("<agent>", encodeURIComponent(JSON.stringify(t)))).replace("<profileid>", encodeURIComponent(e));
			var s = null;
			i && n ? u("Can't have both If-Match and If-None-Match") : i ? s = {"If-Match": _.formatHash(i)} : n && (s = {"If-None-Match": _.formatHash(n)});
			var a = _.XHR_request(this.lrs, o, "DELETE", null, this.lrs.auth, r, null, !1, s, this.withCredentials, this.strictCallbacks);
			if (void 0 === a || 404 == a.status) return null;
			try {
				return JSON.parse(a.response)
			} catch (t) {
				return a
			}
		}
	}, _.ruuid = function () {
		return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (t) {
			var e = 16 * Math.random() | 0;
			return ("x" == t ? e : 3 & e | 8).toString(16)
		})
	}, _.dateFromISOString = function (t) {
		var e = t.match(new RegExp("([0-9]{4})(-([0-9]{2})(-([0-9]{2})([T| ]([0-9]{2}):([0-9]{2})(:([0-9]{2})(.([0-9]+))?)?(Z|(([-+])([0-9]{2}):([0-9]{2})))?)?)?)?")),
			i = 0, n = new Date(e[1], 0, 1);
		e[3] && n.setMonth(e[3] - 1), e[5] && n.setDate(e[5]), e[7] && n.setHours(e[7]), e[8] && n.setMinutes(e[8]), e[10] && n.setSeconds(e[10]), e[12] && n.setMilliseconds(1e3 * Number("0." + e[12])), e[14] && (i = 60 * Number(e[16]) + Number(e[17]), i *= "-" == e[15] ? 1 : -1), i -= n.getTimezoneOffset(), time = Number(n) + 60 * i * 1e3;
		var r = new Date;
		return r.setTime(Number(time)), r
	}, _.XHR_request = function (t, n, r, e, i, o, s, a, p, c, l) {
		"use strict";
		var u, d, h, f, g, v, y = !1, m = !1, S = !1, b = n.toLowerCase().match(/^(.+):\/\/([^:\/]*):?(\d+)?(\/.*)?$/),
			w = window.location, x = {"Content-Type": "application/json"};
		if (x.Authorization = i, x["X-Experience-API-Version"] = _.XAPIWrapper.xapiVersion, null !== p) for (var C in p) p.hasOwnProperty(C) && (x[C] = p[C]);
		if (m = (m = w.protocol.toLowerCase() !== b[1] || w.hostname.toLowerCase() !== b[2]) || (null === b[3] ? "http" === b[1] ? "80" : "443" : b[3]) === w.port, null !== t && void 0 !== t.extended) {
			for (g in f = new Array, t.extended) f.push(g + "=" + t.extended[g]);
			0 < f.length && (n += (-1 < n.indexOf("?") ? "&" : "?") + f.join("&"))
		}
		var A, I, R = window.XDomainRequest && window.XMLHttpRequest && void 0 === (new XMLHttpRequest).responseType;
		if (m && void 0 !== R && !1 !== R) S = !0, d = function (t, e, i, n) {
			var r = e, o = new Array, s = r.indexOf("?");
			if (0 < s && (o.push(r.substr(s + 1)), r = r.substr(0, s)), r = r + "?method=" + t, null !== i) for (var a in i) i.hasOwnProperty(a) && o.push(a + "=" + encodeURIComponent(i[a]));
			return null !== n && o.push("content=" + encodeURIComponent(n)), {
				method: "POST",
				url: r,
				headers: {},
				data: o.join("&")
			}
		}(r, n, x, e), (u = new XDomainRequest).open(d.method, d.url); else for (var C in (u = new XMLHttpRequest).withCredentials = c, u.open(r, n, true), x) u.setRequestHeader(C, x[C]);

		function T() {
			if (y) return h;
			y = !0;
			var t = a && 404 === u.status;
			if (!(void 0 === u.status || 200 <= u.status && u.status < 400 || t)) {
				var e;
				try {
					e = "There was a problem communicating with the Learning Record Store. ( " + u.status + " | " + u.response + " )" + n
				} catch (t) {
					e = t.toString()
				}
				return _.XAPIWrapper.log(e), _.xhrRequestOnError(u, r, n, o, s, l), h = u
			}
			if (!o) return h = u;
			if (s) l ? o(null, u, s) : o(u, s); else {
				var i;
				try {
					i = JSON.parse(u.responseText)
				} catch (t) {
					i = u.responseText
				}
				l ? o(null, u, i) : o(u, i)
			}
		}

		if (u.onreadystatechange = function () {
			if (4 === u.readyState) return T()
		}, u.onload = T, u.onerror = T, u.send(S ? d.data : e), !o) {
			if (S) for (v = 1e3 + new Date; new Date < v && 4 !== u.readyState && !y;) A = void 0, A = new XMLHttpRequest, I = window.location + "?forcenocache=" + _.ruuid(), A.open("GET", I, true), A.send(null);
			return T()
		}
	}, _.xhrRequestOnError = function (e, t, i, n, r, o) {
		if (n && o) {
			var s, a = e ? e.status : void 0;
			if (s = a ? new Error("Request error: " + e.status) : 0 === a || null === a ? new Error("Request error: aborted") : new Error("Reqeust error: unknown"), r) n(s, e, r); else {
				var p;
				try {
					p = JSON.parse(e.responseText)
				} catch (t) {
					p = e.responseText
				}
				n(s, e, p)
			}
		}
	}, _.formatHash = function (t) {
		return "*" === t ? t : '"' + t + '"'
	}, _.XAPIWrapper = new t(i, !1)
}(window.ADL = window.ADL || {}), function (s) {
	function r(t, e) {
		var i = e.split("."), n = i[0];
		return e = i.slice(1).join("."), t[n] || (/\[\]$/.test(n) ? (n = n.slice(0, -2), Array.isArray(t[n]) || (t[n] = [])) : t[n] = {}), e ? r(t[n], e) : t[n]
	}

	function n(t, e, i, n) {
		if (t && t.actor && t.verb && t.object) {
			var r = t;
			for (var o in r) "actor" != o && "verb" != o && "object" != o && (this[o] = r[o]);
			t = r.actor, e = r.verb, i = r.object
		}
		t ? t instanceof a ? this.actor = t : "Agent" !== t.objectType && t.objectType ? "Group" === t.objectType && (this.actor = new p(t)) : this.actor = new a(t) : this.actor = null, this.verb = e ? e instanceof c ? e : new c(e) : null, i ? "Activity" !== i.objectType && i.objectType ? "Agent" === i.objectType ? this.object = i instanceof a ? i : new a(i) : "Group" === i.objectType ? this.object = i instanceof p ? i : new p(i) : "StatementRef" === i.objectType ? this.object = i instanceof u ? i : new u(i) : "SubStatement" === i.objectType ? this.object = i instanceof d ? i : new d(i) : this.object = null : this.object = i instanceof l ? i : new l(i) : this.object = null, n && (this.result = n), this.generateId = function () {
			this.id = s.ruuid()
		}
	}

	n.prototype.toString = function () {
		return this.actor.toString() + " " + this.verb.toString() + " " + this.object.toString() + " " + this.result.toString()
	}, n.prototype.isValid = function () {
		return this.actor && this.actor.isValid() && this.verb && this.verb.isValid() && this.object && this.object.isValid() && this.result && this.result.isValid()
	}, n.prototype.generateRegistration = function () {
		r(this, "context").registration = s.ruuid()
	}, n.prototype.addParentActivity = function (t) {
		r(this, "context.contextActivities.parent[]").push(new l(t))
	}, n.prototype.addGroupingActivity = function (t) {
		r(this, "context.contextActivities.grouping[]").push(new l(t))
	}, n.prototype.addOtherContextActivity = function (t) {
		r(this, "context.contextActivities.other[]").push(new l(t))
	};
	var a = function (t, e) {
		if (this.objectType = "Agent", this.name = e, t && (t.mbox || t.mbox_sha1sum || t.openid || t.account)) for (var i in t) this[i] = t[i]; else /^mailto:/.test(t) ? this.mbox = t : /^[0-9a-f]{40}$/i.test(t) ? this.mbox_sha1sum = t : /^http[s]?:/.test(t) ? this.openid = t : t && t.homePage && t.name && (this.account = t)
	};
	a.prototype.toString = function () {
		return this.name || this.mbox || this.openid || this.mbox_sha1sum || this.account.name
	}, a.prototype.isValid = function () {
		return this.mbox || this.mbox_sha1sum || this.openid || this.account.homePage && this.account.name || "Group" === this.objectType && this.member
	};
	var p = function (t, e, i) {
		a.call(this, t, i), this.member = e, this.objectType = "Group"
	};
	p.prototype = new a;
	var c = function (t, e) {
		if (t && t.id) for (var i in t) this[i] = t[i]; else this.id = t, e && ("string" == typeof e || e instanceof String ? this.display = {"en-US": e} : this.display = e)
	};
	c.prototype.toString = function () {
		return this.display && (this.display["en-US"] || this.display.en) ? this.display["en-US"] || this.display.en : this.id
	}, c.prototype.isValid = function () {
		return this.id
	};
	var l = function (t, e, i) {
		if (t && t.id) {
			var n = t;
			for (var r in n) this[r] = n[r]
		} else this.objectType = "Activity", this.id = t, (e || i) && (this.definition = {}, "string" == typeof e || e instanceof String ? this.definition.name = {"en-US": e} : e && (this.definition.name = e), "string" == typeof i || i instanceof String ? this.definition.description = {"en-US": i} : i && (this.definition.description = i))
	};
	l.prototype.toString = function () {
		return this.definition && this.definition.name && (this.definition.name["en-US"] || this.definition.name.en) ? this.definition.name["en-US"] || this.definition.name.en : this.id
	}, l.prototype.isValid = function () {
		return this.id && (!this.objectType || "Activity" === this.objectType)
	};
	var u = function (t) {
		if (t && t.id) for (var e in t) this[e] = t[e]; else this.objectType = "StatementRef", this.id = t
	};
	u.prototype.toString = function () {
		return "statement(" + this.id + ")"
	}, u.prototype.isValid = function () {
		return this.id && this.objectType && "StatementRef" === this.objectType
	};
	var d = function (t, e, i) {
		n.call(this, t, e, i), this.objectType = "SubStatement", delete this.id, delete this.stored, delete this.version, delete this.authority
	};
	d.prototype = new n, d.prototype.toString = function () {
		return '"' + d.prototype.prototype.toString.call(this) + '"'
	}, n.Agent = a, n.Group = p, n.Verb = c, n.Activity = l, n.StatementRef = u, n.SubStatement = d, s.XAPIStatement = n
}(window.ADL = window.ADL || {}), function (t) {
	var a = t, e = !1;
	"undefined" != typeof window && (a = window.ADL = t.ADL || {}, e = !0);

	function n(t) {
		if ("SubStatement" === t.objectType && "SubStatement" !== t.object.objectType && !(t.id || t.stored || t.version || t.authority)) return a.xapiutil.getActorId(t.actor) + ":" + a.xapiutil.getVerbDisplay(t.verb) + ":" + a.xapiutil.getObjectId(t.object)
	}

	a.xapiutil = {}, a.xapiutil.getLang = function () {
		var t;
		if ("undefined" != typeof navigator) t = navigator.language || navigator.browserLanguage || navigator.systemLanguage || navigator.userLanguage; else if ("undefined" != typeof process && void 0 !== process.env && void 0 !== process.env.LANG) {
			var e = process.env.LANG;
			t = (t = e.slice(0, e.indexOf("."))).replace(/_/, "-")
		}
		return t || "en-US"
	}, a.xapiutil.getLangVal = function (t) {
		if (t && 0 != Object.keys(t).length) {
			for (var e, i = a.xapiutil.getLang(), n = !1; t[i] ? (e = t[i], n = !0) : i.indexOf("-") && (i = i.substring(0, i.lastIndexOf("-"))), !n && "" !== i;) ;
			return e
		}
	}, a.xapiutil.getMoreStatements = function (n, r, o) {
		if (!e) throw new Error("Node not supported.");
		var s = [];
		a.XAPIWrapper.getStatements(o, null, function t(e) {
			if (e && e.response) {
				var i = JSON.parse(e.response);
				i.statements && (s = s.concat(i.statements), n-- <= 0 ? r(s) : i.more && "" !== i.more ? a.XAPIWrapper.getStatements(o, i.more, t) : "" === i.more && r(s))
			}
		})
	}, a.xapiutil.getActorId = function (t) {
		return t.mbox || t.openid || t.mbox_sha1sum || t.account
	}, a.xapiutil.getActorIdString = function (t) {
		var e = t.mbox || t.openid || t.mbox_sha1sum;
		return e = e || (t.account ? t.account.homePage + ":" + t.account.name : t.member ? "Anon Group " + t.member : "unknown")
	}, a.xapiutil.getActorDisplay = function (t) {
		return t.name || a.xapiutil.getActorIdString(t)
	}, a.xapiutil.getVerbDisplay = function (t) {
		if (t) return t.display && a.xapiutil.getLangVal(t.display) || t.id
	}, a.xapiutil.getObjectType = function (t) {
		return t.objectType || (t.id ? "Activity" : "Agent")
	}, a.xapiutil.getObjectId = function (t) {
		if (t.id) return t.id;
		var e = a.xapiutil.getObjectType(t);
		return "Agent" === e || "Group" === e ? a.xapiutil.getActorId(t) : void 0
	}, a.xapiutil.getObjectIdString = function (t) {
		if (!t) return "unknown";
		if (t.id) return t.id;
		var e = a.xapiutil.getObjectType(t);
		return "Agent" === e || "Group" === e ? a.xapiutil.getActorIdString(t) : "SubStatement" == e ? n(t) : "unknown"
	}, a.xapiutil.getObjectDisplay = function (t) {
		if (!t) return "unknown";
		var e = function (t) {
			if (t.definition && t.definition.name) return a.xapiutil.getLangVal(t.definition.name)
		}(t) || t.name || t.id;
		if (!e) {
			var i = a.xapiutil.getObjectType(t);
			"Agent" === i || "Group" == i ? e = a.xapiutil.getActorDisplay(t) : "SubStatement" == i && (e = n(t))
		}
		return e
	}
}(this), function () {
	var u = window.ADL = window.ADL || {};

	function d(t) {
		for (var e = window.location.search.substring(1).split("&"), i = 0; i < e.length; i++) {
			var n = e[i].split("=");
			if (decodeURIComponent(n[0]) == t) return decodeURIComponent(n[1])
		}
	}

	function h(t) {
		var e = "xAPILaunchKey=" + d("xAPILaunchKey") + "&xAPILaunchService=" + d("xAPILaunchService");
		d("encrypted") && (e += "&encrypted=true");
		for (var i = 0; i < t.length; i++) {
			var n = t[i], r = n.href, o = n.attributes.getNamedItem("courselink");
			o && "true" == o.value && (r = -1 < r.indexOf("?") ? r + "&" + e : r + "?" + e, n.href = r)
		}
	}

	u.launch = function s(a, p, c) {
		var n;
		n = a, a = function () {
			var i = arguments;
			window.setTimeout(function () {
				for (var t = [], e = 0; e < i.length; e++) t.push(i[e]);
				n.apply(window, t)
			}, 0)
		};
		try {
			var r = d("xAPILaunchKey"), o = d("xAPILaunchService");
			if (d("encrypted"), s.terminate = function (t) {
				var e = new URL(o);
				e.pathname += "launch/" + r + "/terminate";
				var i = new XMLHttpRequest;
				i.withCredentials = !0, i.crossDomain = !0, i.open("POST", e.toString(), true), i.setRequestHeader("Content-type", "application/json"), i.send(JSON.stringify({
					code: 0,
					description: t || "User closed content"
				}))
			}, !r || !o) return a("invalid launch parameters");
			var t = new URL(o);
			t.pathname += "launch/" + r;
			var l = new XMLHttpRequest;
			l.withCredentials = !0, l.crossDomain = !0, l.onerror = function (t) {
				window.setTimeout(function () {
					return a(t)
				})
			}, l.onload = function (t) {
				if (200 !== l.status) return l.onerror(l.responseText);
				var e = JSON.parse(l.responseText), i = e, n = {};
				n.endpoint = i.endpoint, n.actor = i.actor, n.withCredentials = !0, n.strictCallbacks = c || !1, window.onunload = function () {
					p && s.terminate("User closed content")
				};
				var r, o = new u.XAPIWrapper.constructor;
				return o.changeConfig(n), h(document.body.querySelectorAll("a")), r = document.body, new MutationObserver(function (t) {
					t.forEach(function (t) {
						for (var e in t.addedNodes) t.addedNodes.hasOwnProperty(e) && t.addedNodes[e].constructor == HTMLAnchorElement && h([t.addedNodes[e]])
					})
				}).observe(r, {attributes: !0, childList: !0, subtree: !0}), a(null, e, o)
			}, l.open("POST", t.toString(), true), l.send()
		} catch (t) {
			a(t)
		}
	}
}();

function GetQueryStringValue(strElement, strQueryString) {
	var aryPairs;
	var foundValue;
	strQueryString = strQueryString.substring(1);
	aryPairs = strQueryString.split("&");
	foundValue = SearchQueryStringPairs(aryPairs, strElement);
	if (foundValue === null) {
		aryPairs = strQueryString.split(/[\?\&]/);
		foundValue = SearchQueryStringPairs(aryPairs, strElement);
	}
	if (foundValue === null) {
		return "";
	} else {
		return foundValue;
	}
}

function SearchQueryStringPairs(aryPairs, strElement) {
	var i;
	var intEqualPos;
	var strArg = "";
	var strValue = "";
	strElement = strElement.toLowerCase();
	for (i = 0; i < aryPairs.length; i++) {
		intEqualPos = aryPairs[i].indexOf('=');
		if (intEqualPos != -1) {
			strArg = aryPairs[i].substring(0, intEqualPos);
			if (EqualsIgnoreCase(strArg, strElement)) {
				strValue = aryPairs[i].substring(intEqualPos + 1);
				strValue = new String(strValue)
				strValue = strValue.replace(/\+/g, "%20")
				strValue = unescape(strValue);
				return new String(strValue);
			}
		}
	}
	return null;
}

function EqualsIgnoreCase(str1, str2) {
	var blnReturn;
	str1 = new String(str1);
	str2 = new String(str2);
	blnReturn = (str1.toLowerCase() == str2.toLowerCase())
	return blnReturn;
}

function ValidInteger(intNum) {

	var str = new String(intNum);
	if (str.indexOf("-", 0) == 0) {
		str = str.substring(1, str.length - 1);
	}
	var regValidChars = new RegExp("[^0-9]");
	if (str.search(regValidChars) == -1) {

		return true;
	}

	return false;
}

var scv = GetQueryStringValue("sv", document.currentScript.src);
// Check if the method includes is defined
if ( ! String.prototype.includes ){
	// Otherwise, define it
	String.prototype.includes = function( search, start ){
		if ( typeof start !== 'number' ){
			start = 0;
		}

		if ( start + search.length > this.length ){
			return false;
		} else {
			return this.indexOf(search, start) !== -1;
		}
	};
}
function parseURL(url) {
	var parts = String(url).split("?"),
		pairs, pair, i, params = {};
	if (parts.length === 2) {
		pairs = parts[1].split("&");
		for (i = 0; i < pairs.length; i += 1) {
			pair = pairs[i].split("=");
			if (pair.length === 2 && pair[0]) {
				params[pair[0]] = decodeURIComponent(pair[1]);
			}
		}
	}
	return {
		path: parts[0],
		params: params
	};
}
// Define function to get parameters from the URL
function getParameterByName(name, url) {
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, "\$&");
	var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
		results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, " "));
}

var actor = JSON.parse(getParameterByName('actor'));
var baseUrl = getParameterByName('base_url');
var nonce = getParameterByName('nonce');
var email = actor.mbox[0].replace('mailto:', '');
var postId = getParameterByName('auth').replace('LearnDashId', '');
// User defined code goes here
/*******************************************************************************
 **
 ** xapi object to be used in SCORM wrapper
 **
 ** Version 1.1
 **
 ** Converts many SCORM 2004 data model elements to associated xAPI data
 **
 *******************************************************************************/
xapi = function () {

	var _debug = true;

	var _reservedQSParams = {
		statementId: true,
		voidedStatementId: true,
		verb: true,
		object: true,
		registration: true,
		context: true,
		actor: true,
		since: true,
		until: true,
		limit: true,
		authoritative: true,
		sparse: true,
		instructor: true,
		ascending: true,
		continueToken: true,
		agent: true,
		activityId: true,
		stateId: true,
		profileId: true,
		activity_platform: true,
		grouping: true,
		"Accept-Language": true,
		endpoint:true,
		auth:true
	};
	/*******************************************************************************
	 **
	 ** Configuration object for a specific instance of the wrapper
	 **
	 ** The following configuration values must be set in order for this
	 ** wrapper to function correctly:
	 **
	 ** LRS Data
	 ** -----------
	 ** endpoint - Points at the LRS endpoint
	 ** user -  Username for the LRS
	 ** password - Password for the LRS
	 **
	 ** Other Configuration Values
	 ** ----------------------------
	 ** courseId - IRI for the course this wrapper is used in
	 ** lmsHomePage - LMS home page where the course is/will be imported
	 ** isSCORM2004 - Whether the original course is SCORM 2004 (or SCORM Version 1.2)
	 ** activityId - The ID that will identify the SCO/Activity in the LRS
	 ** groupingContextActivity - Context activity for a synchronous workshop (if applicable)
	 **
	 ** Note: DO NOT UPDATE THE "constants" below.  These are used to indentify
	 **       SCORM profile information and should not be changed
	 **
	 *******************************************************************************/
	var config = {
		lrs: {
			endpoint: "https://lrs.adlnet.gov/xapi/",
			user: "",
			password: ""
		},
		courseId: "",
		lmsHomePage: "",
		isScorm2004: true,
		activityId: "",
		groupingContextActivity: {}
	};

	// xAPI SCORM Profile IRI contstants
	// https://github.com/adlnet/xAPI-SCORM-Profile/blob/master/xapi-scorm-profile.md
	var constants = {
		activityProfileIri: "https://w3id.org/xapi/scorm/activity-profile",
		activityStateIri: "https://w3id.org/xapi/scorm/activity-state",
		actorProfileIri: "https://w3id.org/xapi/scorm/agent-profile",
		attemptStateIri: "https://w3id.org/xapi/scorm/attempt-state"
	};

	// used to hold the data model elements to be used based on SCORM Version
	var scormVersionConfig = {};

	// used to identify if a suspend occurred
	var exitSetToSuspend = false;

	/*******************************************************************************
	 **
	 ** Base statement
	 **
	 ** Must update verb, attempt and result (if applicable) to execute
	 **
	 *******************************************************************************/
	var getBaseStatement = function () {
		return {
			actor: {
				objectType: "Agent",
				mbox:actor.mbox[0].replace('mailto:', ''),
				name:actor.name[0],
			},
			verb: {},
			object: {
				id: config.activityId,
				definition: {
					type: "http://adlnet.gov/expapi/activities/lesson"
				}
			},
			context: {
				contextActivities: {
					grouping: [
						{
							id: "",
							objectType: "Activity",
							definition: {
								type: "http://adlnet.gov/expapi/activities/attempt"
							}
						},
						{
							id: config.courseId,
							objectType: "Activity",
							definition: {
								type: "http://adlnet.gov/expapi/activities/course"
							}
						}
					],
					category: [
						{
							id: "https://w3id.org/xapi/scorm"
						}
					]
				}
			}
		};
	}

	/*******************************************************************************
	 **
	 ** Interactions base statement
	 **
	 ** Must update object iri, attempt, result and interaction
	 ** type/description to execute
	 **
	 *******************************************************************************/
	var getInteractionsBaseStatement = function () {

		return {
			actor: {
				objectType: "Agent",
				mbox:actor.mbox[0].replace('mailto:', ''),
				name:actor.name[0],
			},
			verb: ADL.verbs.responded,
			object: {
				objectType: "Activity",
				id: "",
				definition: {
					type: "http://adlnet.gov/expapi/activities/cmi.interaction",
					interactionType: "",
					correctResponsesPattern: []
				}
			},
			context: {
				contextActivities: {
					parent: [
						{
							id: config.activityId,
							objectType: "Activity",
							definition: {
								type: "http://adlnet.gov/expapi/activities/lesson"
							}
						}
					],
					grouping: [
						{
							id: "",
							objectType: "Activity",
							definition: {
								type: "http://adlnet.gov/expapi/activities/attempt"
							}
						},
						{
							id: config.courseId,
							objectType: "Activity",
							definition: {
								type: "http://adlnet.gov/expapi/activities/course"
							}
						}
					],
					category: [
						{
							id: "https://w3id.org/xapi/scorm"
						}
					]
				}
			},
			result: {
				response: ""
			}
		};
	}

	/*******************************************************************************
	 **
	 ** Voided base statement
	 **
	 ** Must set verb and object to execute
	 **
	 *******************************************************************************/
	var getVoidedBaseStatement = function () {
		return {
			actor: {
				objectType: "Agent",
				mbox:actor.mbox[0].replace('mailto:', ''),
				name:actor.name[0],
			},
			verb: {},
			object: {
				objectType: "StatementRef",
				id: ""
			}
		};
	}

	/*******************************************************************************
	 **
	 ** Gets agent - account corresponding to LMS user registration
	 **
	 ** Used when accessing state objects
	 **
	 *******************************************************************************/
	var getAgent = function () {
		var agent = {
			actor: {
				objectType: "Agent",
				mbox:actor.mbox[0].replace('mailto:', ''),
				name:actor.name[0],
			}
		};

		return agent;
	}

	/*******************************************************************************
	 **
	 ** This function is used to initiate an xAPI attempt
	 **
	 *******************************************************************************/
	var initializeAttempt = function () {
		// configure SCORM version and data elements, get launch data from lms, etc
		configureXAPIData();

		// configure lrs
		configureLRS();

		// deprecated - set the agent profile information based on LMS learner_prefernces
		//setAgentProfile();

		// todo: add error handling to SCORM call
		// Determine whether this is a new or resumed attempt (based on cmi.entry)
		var entry = retrieveDataValue(scormVersionConfig.entryElement);

		var isResumed = (entry == "resume");

		// if "resume", determine if the user issued a suspend sequencing nav
		// request and a terminate was called instead of a suspend and if so, fix
		if (isResumed) {
			adjustFinishStatementForResume();
		}

		// set the attempt context activity based on the SCOs state
		//configureAttemptContextActivityID(entry);

		// Set activity profile info and attempt state every initialize
		// todo: these cause acceptable errors.  ensure they are not written to console
		//setActivityProfile();
		//setAttemptState();

		// Set the appropriate verb based on resumed or new attempt
		var startVerb = isResumed ? ADL.verbs.resumed : ADL.verbs.attempted;

		// Execute the statement
		sendSimpleStatement(startVerb);
	}

	/*******************************************************************************
	 **
	 ** This function looks at the last terminate or statement for a given attempt.
	 ** If "terminated", the terminated stmt is voided and a suspend is issued
	 **
	 *******************************************************************************/
	var adjustFinishStatementForResume = function () {
		var search = ADL.XAPIWrapper.searchParams();
		search['verb'] = ADL.verbs.terminated.id;
		search['activity'] = window.localStorage[config.activityId];
		search['related_activities'] = true;

		var res = ADL.XAPIWrapper.getStatements(search);

		if (res.statements.length == 1) {
			// there is a terminate verb, so must void it and replace with suspended
			// Note: if there is length == 0, no issue.
			//       if length > 1, things are very messed up. Do nothing.

			var terminateStmt = res.statements[0];

			// send the voided statement
			var voidedStmt = getVoidedBaseStatement();
			voidedStmt.verb = ADL.verbs.voided;
			voidedStmt.object.id = terminateStmt.id;

			var response = ADL.XAPIWrapper.sendStatement(voidedStmt);

			// send a suspended statement to replace the (voided) terminated statement
			suspendAttempt(terminateStmt.timestamp);


		}

	}


	/*******************************************************************************
	 **
	 ** This function is used to resume an attempt
	 **
	 *******************************************************************************/
	var resumeAttempt = function () {
		sendSimpleStatement(ADL.verbs.resumed);
	}

	/*******************************************************************************
	 **
	 ** This function is used to suspent an attempt
	 **
	 *******************************************************************************/
	var suspendAttempt = function (timestamp) {
		//sendSimpleStatement(ADL.verbs.suspended);
		var stmt = getBaseStatement();
		stmt.verb = ADL.verbs.suspended;

		if (timestamp != undefined && timestamp != null) {
			stmt.timestamp = timestamp;
		}

		// window.localStorage[activity] uses activity id to return the most recent
		// attempt
		stmt.context.contextActivities.grouping[0].id = window.localStorage[config.activityId];

		// set the context activity from the manifest/launch_data to group together
		// for an event
		stmt.context.contextActivities.grouping.push(config.groupingContextActivity);

		var stmtWithResult = getStmtWithResult(stmt);
		var response = ADL.XAPIWrapper.sendStatement(stmtWithResult);
	}

	/*******************************************************************************
	 **
	 ** This function is used to terminate an xAPI attempt
	 **
	 *******************************************************************************/
	var terminateAttempt = function () {
		//sendSimpleStatement(ADL.verbs.terminated);
		var stmt = getBaseStatement();

		// get the exit and use appropriate verb
		var stopVerb = (exitSetToSuspend) ? ADL.verbs.suspended : ADL.verbs.terminated;

		stmt.verb = stopVerb;

		// window.localStorage[activity] uses activity id to return the most recent
		// attempt
		stmt.context.contextActivities.grouping[0].id = window.localStorage[config.activityId];

		// set the context activity from the manifest/launch_data to group together
		// for an event
		stmt.context.contextActivities.grouping.push(config.groupingContextActivity);

		var stmtWithResult = getStmtWithResult(stmt);
		var response = ADL.XAPIWrapper.sendStatement(stmtWithResult);

		window.localStorage.removeItem("learnerId");
	}

	/*******************************************************************************
	 **
	 ** This function is used to complete the stmt result for terminate and suspend
	 **
	 *******************************************************************************/
	var getStmtWithResult = function (baseStatement) {
		var success = retrieveDataValue(scormVersionConfig.successElement);
		var completion = retrieveDataValue(scormVersionConfig.completionElement);
		var scoreScaled = retrieveDataValue(scormVersionConfig.scoreScaledElement);
		var scoreRaw = retrieveDataValue(scormVersionConfig.scoreRawElement);
		var scoreMin = retrieveDataValue(scormVersionConfig.scoreMinElement);
		var scoreMax = retrieveDataValue(scormVersionConfig.scoreMaxElement);

		var resultSet = false;
		var resultJson = {};
		var scoreSet = false;
		var scoreJson = {};

		// create all of the statement json

		// set success if known
		if (success == "passed") {
			resultSet = true;
			resultJson.success = true;
		} else if (success == "failed") {
			resultSet = true;
			resultJson.success = false;
		}

		// set completion if known
		if (completion == "completed") {
			resultSet = true;
			resultJson.completion = true;
		} else if (completion == "incomplete") {
			resultSet = true;
			resultJson.completion = false;
		}

		// set scaled score if set by sco
		if (scoreScaled != undefined && scoreScaled != "") {
			scoreSet = true;
			resultSet = true;
			scoreJson.scaled = parseFloat(scoreScaled);
		}

		// set raw score if set by sco
		if (scoreRaw != undefined && scoreRaw != "") {
			scoreSet = true;
			resultSet = true;
			scoreJson.raw = parseFloat(scoreRaw);

			// if SCORM 1.2, use raw score / 100 for scaled score
			if (!config.isScorm2004) {
				scoreJson.scaled = parseFloat(scoreRaw) / 100;
			}
		}

		// set min score if set by sco
		if (scoreMin != undefined && scoreMin != "") {
			scoreSet = true;
			resultSet = true;
			scoreJson.min = parseFloat(scoreMin);
		}

		// set max score if set by sco
		if (scoreMax != undefined && scoreMax != "") {
			scoreSet = true;
			resultSet = true;
			scoreJson.max = parseFloat(scoreMax);
		}

		// set the score object in with the rest of the result object
		if (scoreSet) {
			resultJson.score = scoreJson;
		}

		// add result to the base statement
		if (resultSet) {
			baseStatement.result = resultJson;
		}

		return baseStatement;
	}

	/*******************************************************************************
	 **
	 ** This function is used to set agent data based on SCORM learner prefs
	 **
	 ** Deprecated
	 **
	 *******************************************************************************/
	var setAgentProfile = function () {

		var lang = retrieveDataValue(scormVersionConfig.languageElement);
		var audioLevel = retrieveDataValue(scormVersionConfig.audioLevelElement);
		var deliverySpeed = retrieveDataValue(scormVersionConfig.deliverySpeedElement);
		var audioCaptioning = retrieveDataValue(scormVersionConfig.audioCaptioningElement);

		var profile = {
			language: lang,
			audio_level: audioLevel,
			delivery_speed: deliverySpeed,
			audio_captioning: audioCaptioning
		};

		ADL.XAPIWrapper.sendAgentProfile({
				actor: {
					objectType: "Agent",
					mbox:actor.mbox[0].replace('mailto:', ''),
					name:actor.name[0],
				}
			},
			config.activityId,
			profile,
			null,
			"*"
		);
	}

	/*******************************************************************************
	 **
	 ** This function is used to set activity profile information
	 **
	 ** Note: this data is scoped to an activity and does not (normally) change
	 **
	 *******************************************************************************/
	var setActivityProfile = function () {
		// see if the profile is already set
		var ap = ADL.XAPIWrapper.getActivityProfile(config.activityId, constants.activityProfileIri);

		if (ap == null) {
			// get comments from lms (if any)
			//var cmi_num_comments_from_lms_count = retrieveDataValue("cmi.comments_from_lms._count");
			// todo: get the comments, if any and add to array

			// get completion threshold (if supplied in manifest)
			var cmi_completion_threshold = retrieveDataValue(scormVersionConfig.completionThresholdElement);
			var cmi_launch_data = retrieveDataValue(scormVersionConfig.launchDataElement);
			var cmi_max_time_allowed = retrieveDataValue(scormVersionConfig.maxTimeAllowedElement);
			var cmi_scaled_passing_score = retrieveDataValue(scormVersionConfig.scaledPassingScoreElement);
			var cmi_time_limit_action = retrieveDataValue(scormVersionConfig.timeLimitActionElement);

			var activityProfile = {};

			if (config.isScorm2004 && cmi_completion_threshold != "")
				activityProfile.completion_threshold = cmi_completion_threshold;

			if (cmi_launch_data != "")
				activityProfile.launch_data = cmi_launch_data;

			if (cmi_max_time_allowed != "")
				activityProfile.max_time_allowed = cmi_max_time_allowed;

			if (cmi_scaled_passing_score != "")
				activityProfile.scaled_passing_score = cmi_scaled_passing_score;

			if (cmi_time_limit_action != "")
				activityProfile.time_limit_action = cmi_time_limit_action;

			ADL.XAPIWrapper.sendActivityProfile(config.activityId, constants.activityProfileIri, activityProfile, null, "*");
		}
	}

	/*******************************************************************************
	 **
	 ** This function is used to set activity state
	 **
	 ** Note: State data about an activity that is different for each user
	 **
	 **       This is used to also update attempt iri array associated with
	 **       the user and activity
	 **
	 *******************************************************************************/
	var setActivityState = function () {
		// window.localStorage[activity] uses activity id to return the most recent
		// attempt
		var attemptIri = window.localStorage[config.activityId];

		var agent = getAgent();

		// see if the profile is already set
		var as = ADL.XAPIWrapper.getState(config.activityId, agent, constants.activityStateIri);

		// First time, create a new one
		if (as == null || as == '') {
			ADL.XAPIWrapper.sendState(config.activityId, agent, constants.activityStateIri, null, {
				attempts: [attemptIri]
			});
		} else {
			// update state
			var asStr = JSON.stringify(as)
			var newAs = JSON.parse(asStr);

			newAs.attempts.push(attemptIri);

			ADL.XAPIWrapper.sendState(config.activityId, agent, constants.activityStateIri, null, newAs, ADL.XAPIWrapper.hash(asStr));
		}
	}
	var setBookmark = function ( name, value) {

		var attemptIri = window.localStorage[config.activityId];

		var agent = getAgent();
		// update state
		ADL.XAPIWrapper.sendState(config.activityId, agent, name, null, value, ADL.XAPIWrapper.hash(value));

	}

	/*******************************************************************************
	 **
	 ** This function is used to set activity state
	 **
	 ** Note: State data about an activity that is different for each user
	 **
	 **       This is used to also update attempt iri array associated with
	 **       the user and activity
	 **
	 *******************************************************************************/
	var getActivityState = function ( key ) {
		// window.localStorage[activity] uses activity id to return the most recent
		// attempt
		var attemptIri = window.localStorage[config.activityId];

		var agent = getAgent();

		// see if the profile is already set
		var as = ADL.XAPIWrapper.getState(config.activityId, agent, key );

		// First time, create a new one
		if (as == null ) {
			return '';
		} else {
			return as;
		}
	}

	/*******************************************************************************
	 **
	 ** This function is used to set attempt (activity) state
	 **
	 ** Note: State data about an activity that is different for each user, for each
	 **       attempt.
	 **
	 *******************************************************************************/
	var setAttemptState = function () {
		// window.localStorage[activity] uses activity id to return the most recent
		// attempt
		var attemptIri = window.localStorage[config.activityId];
		var agent = getAgent();

		// location, preferences object, credit, lesson_mode, suspend_data,
		// total_time, adl_data
		var cmi_location = retrieveDataValue(scormVersionConfig.locationElement);

		var cmi_language = retrieveDataValue(scormVersionConfig.languageElement);
		var cmi_audio_level = retrieveDataValue(scormVersionConfig.audioLevelElement);
		var cmi_delivery_speed = retrieveDataValue(scormVersionConfig.deliverySpeedElement);
		var cmi_audio_captioning = retrieveDataValue(scormVersionConfig.audioCaptioningElement);

		var preferences = {
			language: cmi_language,
			audio_level: cmi_audio_level,
			delivery_speed: cmi_delivery_speed,
			audio_captioning: cmi_audio_captioning
		};

		var cmi_credit = retrieveDataValue(scormVersionConfig.creditElement);
		var cmi_mode = retrieveDataValue(scormVersionConfig.modeElement);
		var cmi_suspend_data = retrieveDataValue(scormVersionConfig.suspendDataElement);
		var cmi_total_time = retrieveDataValue(scormVersionConfig.totalTimeElement);

		// todo: implement adl.data buckets and store in attempt state

		// create the state object
		var state = {};

		if (cmi_location != "")
			state.location = cmi_location;

		state.preferences = preferences;

		if (cmi_credit != "")
			state.credit = cmi_credit;

		if (cmi_mode != "")
			state.mode = cmi_mode;

		if (cmi_suspend_data != "")
			state.suspend_data = cmi_suspend_data;

		if (cmi_total_time != "")
			state.total_time = cmi_total_time;


		// see if the profile is already set
		var as = ADL.XAPIWrapper.getState(attemptIri, agent, constants.attemptStateIri);

		if (as == null) {
			// first set on this attempt
			ADL.XAPIWrapper.sendState(attemptIri, agent, constants.attemptStateIri, null, state);
		} else {
			var asStr = JSON.stringify(as);

			// updating existing attempt
			ADL.XAPIWrapper.sendState(attemptIri, agent, constants.attemptStateIri, null, state, ADL.XAPIWrapper.hash(asStr));
		}
	}

	/*******************************************************************************
	 **
	 ** This function is used to route set values to the appropriate functions
	 **
	 *******************************************************************************/
	var saveDataValue = function (name, value) {
		var isInteraction = name.indexOf("cmi.interactions") > -1;

		if (isInteraction) {
			setInteraction(name, value);
		} else {

			// Handle only non-array scorm data model elements
			switch (name) {
				case scormVersionConfig.scoreScaledElement:
					setScore(value);
					break;
				case scormVersionConfig.completionElement:
					setComplete(value);
					break;
				case scormVersionConfig.successElement:
					setSuccess(value);
					break;
				case scormVersionConfig.locationElement:
					setBookmark(name, value);
					break;
				case scormVersionConfig.suspendDataElement:
					setBookmark(name, value);
					break;
				case scormVersionConfig.exitElement:
					exitSetToSuspend = (value == "suspend");
					break;
				default:
					break;
			}
		}
	}
	/*******************************************************************************
	 **
	 ** This function/vars is used to handle the interaction type
	 **
	 *******************************************************************************/
	var setInteraction = function (name, value) {
		// key for interactions in local storage is scoped to an attempt
		var interactionsKey = window.localStorage[config.activityId] + "_interactions";

		// get the interactions from local storage
		var cachedInteractionsStr = window.localStorage.getItem(interactionsKey);
		var cachedInteractions = [];
		if (cachedInteractions != null) {
			// get as JSON object array
			var cachedInteractions = JSON.parse(cachedInteractionsStr);
		}

		// figure out what the set value was in the SCORM call
		elementArray = name.split(".");
		var intIndex = elementArray[2];
		var subElement = elementArray[3];

		if (subElement == "id") {
			// its a new interaction.  Set it in local storage
			var newInteraction = {
				index: intIndex,
				id: value,
				type: "",
				learner_response: "",
				result: "",
				description: ""
			};

			if (cachedInteractions != null) {
				// this is not the first interaction set
				cachedInteractions.push(newInteraction);

				// push to local storage
				window.localStorage.setItem(interactionsKey, JSON.stringify(cachedInteractions));
			} else {
				// this is the first interaction set
				window.localStorage.setItem(interactionsKey, JSON.stringify([newInteraction]));
			}
		} else if (subElement == "type") {
			// find interaction with the same index and set type in JSON array
			for (var i = 0; i < cachedInteractions.length; i++) {
				if (cachedInteractions[i].index == intIndex) {
					// found matching index so update this object's type
					cachedInteractions[i].type = value;

					// update local storage
					window.localStorage.setItem(interactionsKey, JSON.stringify(cachedInteractions));

					break;
				}
			}
		} else if (subElement == "description") {
			// find interaction with the same index and set type in JSON array
			for (var i = 0; i < cachedInteractions.length; i++) {
				if (cachedInteractions[i].index == intIndex) {
					// found matching index so update this object's type
					cachedInteractions[i].description = value;

					// update local storage
					window.localStorage.setItem(interactionsKey, JSON.stringify(cachedInteractions));

					break;
				}
			}
		} else if (subElement == "learner_response" || subElement == "student_response") {
			// find interaction with the same index and set type in JSON array
			for (var i = 0; i < cachedInteractions.length; i++) {
				if (cachedInteractions[i].index == intIndex) {
					// found matching index so update this object's type
					cachedInteractions[i].learner_response = value;

					// update local storage
					window.localStorage.setItem(interactionsKey, JSON.stringify(cachedInteractions));

					// Send xAPI Statement
					// Note: this implementation
					var stmt = getInteractionsBaseStatement();
					stmt.object.id = getInteractionIri(cachedInteractions[i].id);
					stmt.context.contextActivities.grouping[0].id = window.localStorage[config.activityId];

					// set the context activity from the manifest/launch_data to group together
					// for an event
					stmt.context.contextActivities.grouping.push(config.groupingContextActivity);

					// set the learner's response
					stmt.result.response = cachedInteractions[i].learner_response;

					// todo: shouldn't assume en-US - implement with default if not specified, or use what was sent
					if (config.isScorm2004) {
						stmt.object.definition.description = {
							"en-US": cachedInteractions[i].description
						};
					}

					// set the specific interaction type
					stmt.object.definition.interactionType = cachedInteractions[i].type;

					// get any type specific JSON that an LRS *may* require
					switch (cachedInteractions[i].type) {
						case "choice":
							stmt.object.definition.choices = [];
							break;
						case "likert":
							stmt.object.definition.scale = [];
							break;
						case "matching":
							stmt.object.definition.source = [];
							stmt.object.definition.target = [];
							break;
						case "performance":
							stmt.object.definition.steps = [];
							break;
						case "sequencing":
							stmt.object.definition.choices = [];
							break;
						default:
							break;
					}

					// todo: make the subelement that you send stmt on configurable
					// send statement
					var response = ADL.XAPIWrapper.sendStatement(stmt);

					// remove interaction from local storage array so its not processed again
					cachedInteractions.splice(i, 1);
				}
			}
		}

	}

	/*******************************************************************************
	 **
	 ** This function is used to get an interaction iri
	 **
	 *******************************************************************************/
	var getInteractionIri = function (interactionId) {
		return config.activityId + "/interactions/" + encodeURIComponent(interactionId);
	}

	/*******************************************************************************
	 **
	 ** This function is used to set a scaled score
	 **
	 *******************************************************************************/
	var setScore = function (value) {
		// For scorm 1.2, must divide raw by 100
		var score = (config.isScorm2004) ? parseFloat(value) : parseFloat(value) / 100;

		var stmt = getBaseStatement();
		stmt.verb = ADL.verbs.scored;
		stmt.context.contextActivities.grouping[0].id = window.localStorage[config.activityId];

		// set the context activity from the manifest/launch_data to group together
		// for an event
		stmt.context.contextActivities.grouping.push(config.groupingContextActivity);

		// todo: add error handling if value is not a valid scaled score
		stmt.result = {
			score: {
				scaled: score
			}
		};

		var response = ADL.XAPIWrapper.sendStatement(stmt);
	}

	/*******************************************************************************
	 **
	 ** This function is used to complete an activity
	 **
	 *******************************************************************************/
	var setComplete = function (value) {
		if (value == "completed") {
			sendSimpleStatement(ADL.verbs.completed);
		}
	}

	/*******************************************************************************
	 **
	 ** This function is used to set pass/failed on an activity
	 **
	 *******************************************************************************/
	var setSuccess = function (value) {
		// if SCORM 1.2, these could be complete/incomplete
		if (value == "passed" || value == "failed")
			sendSimpleStatement(ADL.verbs[value]);
	}

	/*******************************************************************************
	 **
	 ** This function is used to configure LRS endpoint and other values
	 **
	 *******************************************************************************/
	var configureXAPIData = function () {

		var isScorm2004 = scv;


		// get configuration information from the LMS
		//scormLaunchData = retrieveDataValue("cmi.launch_data");
		//scormLaunchDataJSON = JSON.parse(scormLaunchData);

		// todo: confirm launch data exists, if not default values

		// set local config object with launch data information
		config.lrs.endpoint = GetQueryStringValue("endpoint", location.href);
		config.lrs.auth = GetQueryStringValue("auth", location.href);
		//config.lrs.password = scormLaunchDataJSON.lrs.password;
		config.courseId = TC_COURSE_ID;
		config.lmsHomePage = GetQueryStringValue("base_url", location.href);
		if (isScorm2004 == '2004') {
			config.isScorm2004 = true;
		} else {
			config.isScorm2004 = false;
		}
		//config.isScorm2004 = scormLaunchDataJSON.isScorm2004;
		config.activityId = TC_COURSE_ID;
		//config.groupingContextActivity = scormLaunchDataJSON.groupingContextActivity;

		// setup SCORM object based on configuration
		scormVersionConfig = {
			learnerIdElement: (config.isScorm2004) ? "cmi.learner_id" : "cmi.core.student_id",
			entryElement: ((config.isScorm2004 == true) ? "cmi.entry" : "cmi.core.entry"),
			exitElement: (config.isScorm2004) ? "cmi.exit" : "cmi.core.exit",
			successElement: (config.isScorm2004) ? "cmi.success_status" : "cmi.core.lesson_status",
			completionElement: (config.isScorm2004) ? "cmi.completion_status" : "cmi.core.lesson_status",
			scoreRawElement: (config.isScorm2004) ? "cmi.score.raw" : "cmi.core.score.raw",
			scoreMinElement: (config.isScorm2004) ? "cmi.score.min" : "cmi.core.score.min",
			scoreMaxElement: (config.isScorm2004) ? "cmi.score.max" : "cmi.core.score.max",
			scoreScaledElement: (config.isScorm2004) ? "cmi.score.scaled" : "cmi.core.score.raw",
			languageElement: (config.isScorm2004) ? "cmi.learner_preference.language" : "cmi.student_preference.language",
			audioLevelElement: (config.isScorm2004) ? "cmi.learner_preference.audio_level" : "cmi.student_preference.audio",
			deliverySpeedElement: (config.isScorm2004) ? "cmi.learner_preference.delivery_speed" : "cmi.student_preference.speed",
			audioCaptioningElement: (config.isScorm2004) ? "cmi.learner_preference.audio_captioning" : "cmi.student_preference.text",
			completionThresholdElement: (config.isScorm2004) ? "cmi.completion_threshold" : "",
			launchDataElement: "cmi.launch_data",
			maxTimeAllowedElement: (config.isScorm2004) ? "cmi.max_time_allowed" : "cmi.student_data.max_time_allowed",
			scaledPassingScoreElement: (config.isScorm2004) ? "cmi.scaled_passing_score" : "cmi.student_data.mastery_score",
			timeLimitActionElement: (config.isScorm2004) ? "cmi.time_limit_action" : "cmi.student_data.time_limit_action",
			locationElement: (config.isScorm2004) ? "cmi.location" : "cmi.core.lesson_location",
			creditElement: (config.isScorm2004) ? "cmi.credit" : "cmi.core.credit",
			modeElement: (config.isScorm2004) ? "cmi.mode" : "cmi.core.lesson_mode",
			suspendDataElement: "cmi.suspend_data",
			totalTimeElement: (config.isScorm2004) ? "cmi.total_time" : "cmi.core.total_time"
		}

	}

	/*******************************************************************************
	 **
	 ** This function is used to configure LRS endpoint and basic auth values
	 **
	 *******************************************************************************/
	var configureLRS = function () {
		var urlParams = parseURL(window.location.href).params;
		var extended = {};
		var conf = {
			endpoint: config.lrs.endpoint,
		};

		for (i in urlParams) {
			if (urlParams.hasOwnProperty(i)) {
				if (_reservedQSParams.hasOwnProperty(i)) {
					delete urlParams[i];
				} else {
					extended = extended || {};
					extended[i] = urlParams[i];
				}
			}
		}
		if (extended !== null) {
			conf.extended = extended;
		}
		conf.allowFail = false;
		ADL.XAPIWrapper.changeConfig(conf);
	}

	/*******************************************************************************
	 **
	 ** This function is used to get the attempt context activity (grouping) id
	 **
	 *******************************************************************************/
	var configureAttemptContextActivityID = function (cmiEntryValue) {
		// window.localStorage[config.activityId] uses activity id to return the most recent
		// attempt
		if (cmiEntryValue == "resume") {
			if (window.localStorage[config.activityId] == null) {
				window.localStorage[config.activityId] = config.activityId + "?attemptId=" + generateUUID();
			}

			// send a resume statement
			//resumeAttempt();

		} else {
			window.localStorage[config.activityId] = config.activityId + "?attemptId=" + generateUUID();

			// update the activity state with the new attempt IRI
			setActivityState();
		}
	}

	/*******************************************************************************
	 **
	 ** Sends same basic statement with varying verbs
	 **
	 *******************************************************************************/
	var sendSimpleStatement = function (verb) {
		var stmt = getBaseStatement();
		stmt.verb = verb;
		stmt.context.contextActivities.grouping[0].id = window.localStorage[config.activityId];

		// set the context activity from the manifest/launch_data to group together
		// for an event
		stmt.context.contextActivities.grouping.push(config.groupingContextActivity);

		var response = ADL.XAPIWrapper.sendStatement(stmt);
	}


	/*******************************************************************************
	 **
	 ** This function is used to (most likely) get a unique guid to identify
	 ** an attempt
	 **
	 *******************************************************************************/
	var generateUUID = function () {
		var d = new Date().getTime();

		var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
			var r = (d + Math.random() * 16) % 16 | 0;
			d = Math.floor(d / 16);
			return (c == 'x' ? r : (r & 0x7 | 0x8)).toString(16);
		});

		return uuid;
	}

	function message(str) {
		if (_debug) {
			output.log(str);
		}
	}

	return {
		//newSetConfig: newSetConfig,
		//setConfig:setConfig,
		initializeAttempt: initializeAttempt,
		resumeAttempt: resumeAttempt,
		suspendAttempt: suspendAttempt,
		terminateAttempt: terminateAttempt,
		saveDataValue: saveDataValue,
		getSavedData: getActivityState,
		setScore: setScore,
		setComplete: setComplete,
		setSuccess: setSuccess,
		configureLRS: configureLRS,
		getStudent: getAgent
	}

	//
}(); // end xapi object
function console_log(arguments) {
	console.log(arguments)
}
if( scv == "2004" ) {
	var API_1484_11 = function (r) {
		var a = false;
		var i = false;
		var localcache = [];
		var interactions = 0;
		var objectives = 0;
		var e = this;
		return {
			getCache: function () {
				return o
			}, Initialize: function (e) {
				if (i || a) {
					return "false"
				}
				i = true;
				if (r.scorm_version != "1.2") {
					ADL.XAPIWrapper.changeConfig({isScorm2004: true});
				}
				xapi.initializeAttempt();
				return "true"
			}, GetValue: function (key) {
				var r = new Date;
				var t = r.toLocaleString();
				if (!i || a) {
					return "false"
				}
				var r = new Date;
				var t = r.toLocaleString();
				if (key == "cmi.interactions._count") {
					varvalue_count = parseInt(++interactions);
					varvalue = '"' + varvalue_count + '"';
					return varvalue_count
				}
				if ( key == "cmi.objectives._count") {
					varvalue_count = parseInt(++objectives);
					varvalue = '"' + varvalue_count + '"';
					return varvalue_count
				}
				if( key === 'cmi.learner_id' ) {
					return xapi.getStudent().actor.mbox;
				}
				if( key === 'cmi.learner_name' ) {
					return xapi.getStudent().actor.name;
				}
				if( key === 'cmi.mode' ) {
					return 'normal';
				}
				if( key === 'cmi.completion_status' ) {
					return 'incomplete';
				}

				if( localcache[key] === undefined && ( key == "cmi.core.lesson_location" || key == "cmi.suspend_data" ) ) {
					localcache[key] = xapi.getSavedData(key);
				}
				varvalue = localcache[key] === undefined ? "" : localcache[key];
				return varvalue;
			}, SetValue: function (name, value) {
				var t = new Date;
				var n = t.toLocaleString();
				if (!i || a) {
					return "false"
				}
				localcache["cmi.interactions._count"] = interactions;
				localcache["cmi.objectives._count"] = objectives;
				localcache[name] = value;
				xapi.saveDataValue(name, value);
				return "true"
			}, Commit: function (e) {
				if (!i || a) {
					return "false"
				}
				return "true"
			}, Terminate: function (e) {
				if (!i || a) {
					return "false"
				}
				xapi.terminateAttempt();
				return "true"
			}, GetLastError: function () {
				return 0
			}, GetDiagnostic: function (e) {
				return "diagnostic string"
			}, GetErrorString: function (e) {
				return "error string"
			}
		}
	}(window);
	initializeCommunication = API_1484_11.Initialize;
	terminateCommunication = API_1484_11.Terminate;
	storeDataValue = API_1484_11.SetValue;
	retrieveDataValue = API_1484_11.GetValue;
}else{
	var API = function (e) {
		var i = false;
		var a = false;
		var localcache = [];
		var interactions = 0;
		var objectives = 0;
		var t = this;
		return {
			getCache: function () {
				return localcache
			}, LMSInitialize: function (e) {
				if (a || i) {
					return "false"
				}
				a = true;
				xapi.initializeAttempt();
				return "true"
			}, LMSGetValue: function (key) {
				var t = new Date;
				var n = t.toLocaleString();
				if (!a || i) {
					return "false"
				}
				if ( key == "cmi.interactions._count") {
					varvalue_count = parseInt(++interactions);
					varvalue = '"' + varvalue_count + '"';
					return varvalue_count
				}
				if ( key == "cmi.objectives._count") {
					varvalue_count = parseInt(++objectives);
					varvalue = '"' + varvalue_count + '"';
					return varvalue_count
				}
				if( key === 'cmi.core.student_id' ) {
					return xapi.getStudent().actor.mbox;
				}
				if( key === 'cmi.core.student_name' ) {
					return xapi.getStudent().actor.name;
				}
				if( key === 'cmi.core.lesson_mode' ) {
					return 'normal';
				}
				if( key === 'cmi.core.lesson_status' ) {
					return 'incomplete';
				}

				if( localcache[key] === undefined && ( key == "cmi.core.lesson_location" || key == "cmi.suspend_data" ) ) {
					localcache[key] = xapi.getSavedData(key);
				}
				varvalue = localcache[key] === undefined ? "" : localcache[key];

				return varvalue;
			}, LMSSetValue: function (key, value) {
				var n = new Date;
				var r = n.toLocaleString();
				if (!a || i) {
					return "false"
				}
				localcache["cmi.interactions._count"] = interactions;
				localcache["cmi.objectives._count"] = objectives;
				localcache[key] = value;
				xapi.saveDataValue(key, value);
				return "true"
			}, LMSCommit: function (e) {
				if (!a || i) {
					return "false"
				}

				return "true"
			}, LMSFinish: function (e) {
				if (!a || i) {
					return "false"
				}
				xapi.terminateAttempt();
				return "true"
			}, LMSGetLastError: function () {
				return 0
			}, LMSGetDiagnostic: function (e) {
				return "diagnostic string"
			}, LMSGetErrorString: function (e) {
				return "error string"
			}
		}
	}(window);
	initializeCommunication = API.LMSInitialize;
	terminateCommunication = API.LMSFinish;
	storeDataValue = API.LMSSetValue;
	retrieveDataValue = API.LMSGetValue;
}
