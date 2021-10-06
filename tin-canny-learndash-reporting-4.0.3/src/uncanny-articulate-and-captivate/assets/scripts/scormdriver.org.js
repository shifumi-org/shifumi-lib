/*
https://raw.githubusercontent.com/lifaon74/url-polyfill/master/url-polyfill.js
 */
var g = (typeof global !== 'undefined') ? global :
  ((typeof window !== 'undefined') ? window :
    ((typeof self !== 'undefined') ? self : this));


(function(global) {
  /**
   * Polyfill URLSearchParams
   *
   * Inspired from : https://github.com/WebReflection/url-search-params/blob/master/src/url-search-params.js
   */

  var checkIfIteratorIsSupported = function() {
    try {
      return !!Symbol.iterator;
    } catch (error) {
      return false;
    }
  };


  var iteratorSupported = checkIfIteratorIsSupported();

  var createIterator = function(items) {
    var iterator = {
      next: function() {
        var value = items.shift();
        return {
          done: value === void 0,
          value: value
        };
      }
    };

    if (iteratorSupported) {
      iterator[Symbol.iterator] = function() {
        return iterator;
      };
    }

    return iterator;
  };

  var polyfillURLSearchParams = function() {

    var URLSearchParams = function(searchString) {
      Object.defineProperty(this, '_entries', {
        value: {}
      });

      if (typeof searchString === 'string') {
        if (searchString !== '') {
          searchString = searchString.replace(/^\?/, '');
          var attributes = searchString.split('&');
          var attribute;
          for (var i = 0; i < attributes.length; i++) {
            attribute = attributes[i].split('=');
            this.append(
              decodeURIComponent(attribute[0]),
              (attribute.length > 1) ? decodeURIComponent(attribute[1]) : ''
            );
          }
        }
      } else if (searchString instanceof URLSearchParams) {
        var _this = this;
        searchString.forEach(function(value, name) {
          _this.append(value, name);
        });
      }
    };

    var proto = URLSearchParams.prototype;

    proto.append = function(name, value) {
      if (name in this._entries) {
        this._entries[name].push(value.toString());
      } else {
        this._entries[name] = [value.toString()];
      }
    };

    proto.delete = function(name) {
      delete this._entries[name];
    };

    proto.get = function(name) {
      return (name in this._entries) ? this._entries[name][0] : null;
    };

    proto.getAll = function(name) {
      return (name in this._entries) ? this._entries[name].slice(0) : [];
    };

    proto.has = function(name) {
      return (name in this._entries);
    };

    proto.set = function(name, value) {
      this._entries[name] = [value.toString()];
    };

    proto.forEach = function(callback, thisArg) {
      var entries;
      for (var name in this._entries) {
        if (this._entries.hasOwnProperty(name)) {
          entries = this._entries[name];
          for (var i = 0; i < entries.length; i++) {
            callback.call(thisArg, entries[i], name, this);
          }
        }
      }
    };

    proto.keys = function() {
      var items = [];
      this.forEach(function(value, name) {
        items.push(name);
      });
      return createIterator(items);
    };

    proto.values = function() {
      var items = [];
      this.forEach(function(value) {
        items.push(value);
      });
      return createIterator(items);
    };

    proto.entries = function() {
      var items = [];
      this.forEach(function(value, name) {
        items.push([name, value]);
      });
      return createIterator(items);
    };

    if (iteratorSupported) {
      proto[Symbol.iterator] = proto.entries;
    }

    proto.toString = function() {
      var searchString = '';
      this.forEach(function(value, name) {
        if (searchString.length > 0) searchString += '&';
        searchString += encodeURIComponent(name) + '=' + encodeURIComponent(value);
      });
      return searchString;
    };

    global.URLSearchParams = URLSearchParams;
  };

  if (!('URLSearchParams' in global)) {
    polyfillURLSearchParams();
  }

  // HTMLAnchorElement

})(g);

(function(global) {
  /**
   * Polyfill URL
   *
   * Inspired from : https://github.com/arv/DOM-URL-Polyfill/blob/master/src/url.js
   */

  var checkIfURLIsSupported = function() {
    try {
      var u = new URL('b', 'http://a');
      u.pathname = 'c%20d';
      return (u.href === 'http://a/c%20d') && u.searchParams;
    } catch (e) {
      return false;
    }
  };


  var polyfillURL = function() {
    var _URL = global.URL;

    var URL = function(url, base) {
      if (typeof url !== 'string') throw new TypeError('Failed to construct \'URL\': Invalid URL');

      var doc = document.implementation.createHTMLDocument('');
      window.doc = doc;
      if (base) {
        var baseElement = doc.createElement('base');
        baseElement.href = base;
        doc.head.appendChild(baseElement);
      }

      var anchorElement = doc.createElement('a');
      anchorElement.href = url;
      doc.body.appendChild(anchorElement);
      anchorElement.href = anchorElement.href; // force href to refresh

      if (anchorElement.protocol === ':' || !/:/.test(anchorElement.href)) {
        throw new TypeError('Invalid URL');
      }

      Object.defineProperty(this, '_anchorElement', {
        value: anchorElement
      });
    };

    var proto = URL.prototype;

    var linkURLWithAnchorAttribute = function(attributeName) {
      Object.defineProperty(proto, attributeName, {
        get: function() {
          return this._anchorElement[attributeName];
        },
        set: function(value) {
          this._anchorElement[attributeName] = value;
        },
        enumerable: true
      });
    };

    ['hash', 'host', 'hostname', 'port', 'protocol', 'search']
      .forEach(function(attributeName) {
        linkURLWithAnchorAttribute(attributeName);
      });

    Object.defineProperties(proto, {

      'toString': {
        get: function() {
          var _this = this;
          return function() {
            return _this.href;
          };
        }
      },

      'href': {
        get: function() {
          return this._anchorElement.href.replace(/\?$/, '');
        },
        set: function(value) {
          this._anchorElement.href = value;
        },
        enumerable: true
      },

      'pathname': {
        get: function() {
          return this._anchorElement.pathname.replace(/(^\/?)/, '/');
        },
        set: function(value) {
          this._anchorElement.pathname = value;
        },
        enumerable: true
      },

      'origin': {
        get: function() {
          return this._anchorElement.protocol + '//' + this._anchorElement.hostname + (this._anchorElement.port ? (':' + this._anchorElement.port) : '');
        },
        enumerable: true
      },

      'password': { // TODO
        get: function() {
          return '';
        },
        set: function(value) {},
        enumerable: true
      },

      'username': { // TODO
        get: function() {
          return '';
        },
        set: function(value) {},
        enumerable: true
      },

      'searchParams': {
        get: function() {
          var searchParams = new URLSearchParams(this.search);
          var _this = this;
          ['append', 'delete', 'set'].forEach(function(methodName) {
            var method = searchParams[methodName];
            searchParams[methodName] = function() {
              method.apply(searchParams, arguments);
              _this.search = searchParams.toString();
            };
          });
          return searchParams;
        },
        enumerable: true
      }
    });

    URL.createObjectURL = function(blob) {
      return _URL.createObjectURL.apply(_URL, arguments);
    };

    URL.revokeObjectURL = function(url) {
      return _URL.revokeObjectURL.apply(_URL, arguments);
    };

    global.URL = URL;

  };

  if (!checkIfURLIsSupported()) {
    polyfillURL();
  }

  if ((global.location !== void 0) && !('origin' in global.location)) {
    var getOrigin = function() {
      return global.location.protocol + '//' + global.location.hostname + (global.location.port ? (':' + global.location.port) : '');
    };

    try {
      Object.defineProperty(global.location, 'origin', {
        get: getOrigin,
        enumerable: true
      });
    } catch (e) {
      setInterval(function() {
        global.location.origin = getOrigin();
      }, 100);
    }
  }

})(g);


/* Copyright © 2003-2013 Rustici Software, LLC  All Rights Reserved. www.scorm.com */
var VERSION = "6.0.0";
var PREFERENCE_DEFAULT = 0;
var PREFERENCE_OFF = -1;
var PREFERENCE_ON = 1;
var LESSON_STATUS_PASSED = 1;
var LESSON_STATUS_COMPLETED = 2;
var LESSON_STATUS_FAILED = 3;
var LESSON_STATUS_INCOMPLETE = 4;
var LESSON_STATUS_BROWSED = 5;
var LESSON_STATUS_NOT_ATTEMPTED = 6;
var ENTRY_REVIEW = 1;
var ENTRY_FIRST_TIME = 2;
var ENTRY_RESUME = 3;
var MODE_NORMAL = 1;
var MODE_BROWSE = 2;
var MODE_REVIEW = 3;
var MAX_CMI_TIME = 36002439990;
var NO_ERROR = 0;
var ERROR_LMS = 1;
var ERROR_INVALID_PREFERENCE = 2;
var ERROR_INVALID_NUMBER = 3;
var ERROR_INVALID_ID = 4;
var ERROR_INVALID_STATUS = 5;
var ERROR_INVALID_RESPONSE = 6;
var ERROR_NOT_LOADED = 7;
var ERROR_INVALID_INTERACTION_RESPONSE = 8;
var EXIT_TYPE_SUSPEND = "SUSPEND";
var EXIT_TYPE_FINISH = "FINISH";
var EXIT_TYPE_TIMEOUT = "TIMEOUT";
var EXIT_TYPE_UNLOAD = "UNLOAD";
var INTERACTION_RESULT_CORRECT = "CORRECT";
var INTERACTION_RESULT_WRONG = "WRONG";
var INTERACTION_RESULT_UNANTICIPATED = "UNANTICIPATED";
var INTERACTION_RESULT_NEUTRAL = "NEUTRAL";
var INTERACTION_TYPE_TRUE_FALSE = "true-false";
var INTERACTION_TYPE_CHOICE = "choice";
var INTERACTION_TYPE_FILL_IN = "fill-in";
var INTERACTION_TYPE_LONG_FILL_IN = "long-fill-in";
var INTERACTION_TYPE_MATCHING = "matching";
var INTERACTION_TYPE_PERFORMANCE = "performance";
var INTERACTION_TYPE_SEQUENCING = "sequencing";
var INTERACTION_TYPE_LIKERT = "likert";
var INTERACTION_TYPE_NUMERIC = "numeric";
var DATA_CHUNK_PAIR_SEPARATOR = '###';
var DATA_CHUNK_VALUE_SEPARATOR = '$$';
var APPID = "__APPID__";
var CLOUDURL = "__CLOUDURL__";
var blnDebug = true;
var strLMSStandard = 'AUTO';
var DEFAULT_EXIT_TYPE = EXIT_TYPE_SUSPEND;
var AICC_LESSON_ID = "1";
var EXIT_BEHAVIOR = "SCORM_RECOMMENDED";
var EXIT_TARGET = "goodbye.html";
var LMS_SPECIFIED_REDIRECT_EVAL_STATEMENT = "";
var AICC_COMM_DISABLE_XMLHTTP = false;
var AICC_COMM_DISABLE_IFRAME = false;
var AICC_COMM_PREPEND_HTTP_IF_MISSING = true;
var AICC_REPORT_MIN_MAX_SCORE = true;
var SHOW_DEBUG_ON_LAUNCH = false;
var DO_NOT_REPORT_INTERACTIONS = false;
var SCORE_CAN_ONLY_IMPROVE = false;
var REVIEW_MODE_IS_READ_ONLY = false;
var AICC_RE_CHECK_LOADED_INTERVAL = 250;
var AICC_RE_CHECK_ATTEMPTS_BEFORE_TIMEOUT = 240;
var USE_AICC_KILL_TIME = true;
var AICC_ENTRY_FLAG_DEFAULT = ENTRY_REVIEW;
var AICC_USE_CUSTOM_COMMS = false;
var FORCED_COMMIT_TIME = "0";
var ALLOW_NONE_STANDARD = true;
var USE_2004_SUSPENDALL_NAVREQ = false;
var USE_STRICT_SUSPEND_DATA_LIMITS = false;
var EXIT_SUSPEND_IF_COMPLETED = false;
var EXIT_NORMAL_IF_PASSED = false;
var AICC_ENCODE_PARAMETER_VALUES = true;

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
    WriteToDebug("GetQueryStringValue Element '" + strElement + "' Not Found, Returning: empty string");
    return "";
  } else {
    WriteToDebug("GetQueryStringValue for '" + strElement + "' Returning: " + foundValue);
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

function ConvertStringToBoolean(str) {
  var intTemp;
  if (EqualsIgnoreCase(str, "true") || EqualsIgnoreCase(str, "t") || str.toLowerCase().indexOf("t") == 0) {
    return true;
  } else {
    intTemp = parseInt(str, 10);
    if (intTemp == 1 || intTemp == -1) {
      return true;
    } else {
      return false;
    }
  }
}

function EqualsIgnoreCase(str1, str2) {
  var blnReturn;
  str1 = new String(str1);
  str2 = new String(str2);
  blnReturn = (str1.toLowerCase() == str2.toLowerCase())
  return blnReturn;
}

function ValidInteger(intNum) {
  WriteToDebug("In ValidInteger intNum=" + intNum);
  var str = new String(intNum);
  if (str.indexOf("-", 0) == 0) {
    str = str.substring(1, str.length - 1);
  }
  var regValidChars = new RegExp("[^0-9]");
  if (str.search(regValidChars) == -1) {
    WriteToDebug("Returning true");
    return true;
  }
  WriteToDebug("Returning false");
  return false;
}

function ConvertDateToIso8601TimeStamp(dtm) {
  var strTimeStamp;
  dtm = new Date(dtm);
  var Year = dtm.getFullYear();
  var Month = dtm.getMonth() + 1;
  var Day = dtm.getDate();
  var Hour = dtm.getHours();
  var Minute = dtm.getMinutes();
  var Second = dtm.getSeconds();
  Month = ZeroPad(Month, 2);
  Day = ZeroPad(Day, 2);
  Hour = ZeroPad(Hour, 2);
  Minute = ZeroPad(Minute, 2);
  Second = ZeroPad(Second, 2);
  strTimeStamp = Year + "-" + Month + "-" + Day + "T" + Hour + ":" + Minute + ":" + Second;
  var tzoffset = -(dtm.getTimezoneOffset() / 60);
  if (tzoffset != 0) {
    strTimeStamp += '.0';
    if (tzoffset > 0) {
      if (('' + tzoffset).indexOf('.') != -1) {
        var fraction = '0' + ('' + tzoffset).substr(('' + tzoffset).indexOf('.'), ('' + tzoffset).length);
        var base = ('' + tzoffset).substr(0, ('' + tzoffset).indexOf('.'));
        fraction = (fraction * 60);
        strTimeStamp += '+' + ZeroPad(base + '.' + fraction, 2);
      } else {
        strTimeStamp += '+' + ZeroPad(tzoffset, 2);
      }
    } else {
      strTimeStamp += ZeroPad(tzoffset, 2);
    }
  }
  return strTimeStamp;
}

function ConvertIso8601TimeStampToDate(strTimeStamp) {
  strTimeStamp = new String(strTimeStamp);
  var ary = new Array();
  ary = strTimeStamp.split(/[\:T+-]/);
  var Year = ary[0];
  var Month = ary[1] - 1;
  var Day = ary[2];
  var Hour = ary[3];
  var Minute = ary[4];
  var Second = ary[5];
  return new Date(Year, Month, Day, Hour, Minute, Second, 0);
}

function ConvertDateToCMIDate(dtmDate) {
  WriteToDebug("In ConvertDateToCMIDate");
  var strYear;
  var strMonth;
  var strDay;
  var strReturn;
  dtmDate = new Date(dtmDate);
  strYear = dtmDate.getFullYear()
  strMonth = (dtmDate.getMonth() + 1);
  strDay = dtmDate.getDate();
  strReturn = ZeroPad(strYear, 4) + "/" + ZeroPad(strMonth, 2) + "/" + ZeroPad(strDay, 2);
  return strReturn;
}

function ConvertDateToCMITime(dtmDate) {
  var strHours;
  var strMinutes;
  var strSeconds;
  var strReturn;
  dtmDate = new Date(dtmDate);
  strHours = dtmDate.getHours();
  strMinutes = dtmDate.getMinutes();
  strSeconds = dtmDate.getSeconds();
  strReturn = ZeroPad(strHours, 2) + ":" + ZeroPad(strMinutes, 2) + ":" + ZeroPad(strSeconds, 2);
  return strReturn;
}

function ConvertCMITimeSpanToMS(strTime) {
  WriteToDebug("In ConvertCMITimeSpanToMS, strTime=" + strTime);
  var aryParts;
  var intHours;
  var intMinutes;
  var intSeconds;
  var intTotalMilliSeconds;
  aryParts = strTime.split(":");
  if (!IsValidCMITimeSpan(strTime)) {
    WriteToDebug("ERROR - Invalid TimeSpan");
    SetErrorInfo(SCORM_ERROR_GENERAL, "LMS ERROR - Invalid time span passed to ConvertCMITimeSpanToMS, please contact technical support");
    return 0;
  }
  intHours = aryParts[0];
  intMinutes = aryParts[1];
  intSeconds = aryParts[2];
  WriteToDebug("intHours=" + intHours + " intMinutes=" + intMinutes + " intSeconds=" + intSeconds);
  intTotalMilliSeconds = (intHours * 3600000) + (intMinutes * 60000) + (intSeconds * 1000);
  intTotalMilliSeconds = Math.round(intTotalMilliSeconds);
  WriteToDebug("Returning " + intTotalMilliSeconds);
  return intTotalMilliSeconds;
}

function ConvertScorm2004TimeToMS(strIso8601Time) {
  WriteToDebug("In ConvertScorm2004TimeToMS, strIso8601Time=" + strIso8601Time);
  var intTotalMs = 0;
  var strNumberBuilder;
  var strCurrentCharacter;
  var blnInTimeSection;
  var Seconds = 0;
  var Minutes = 0;
  var Hours = 0;
  var Days = 0;
  var Months = 0;
  var Years = 0;
  var MILLISECONDS_PER_SECOND = 1000;
  var MILLISECONDS_PER_MINUTE = MILLISECONDS_PER_SECOND * 60;
  var MILLISECONDS_PER_HOUR = MILLISECONDS_PER_MINUTE * 60;
  var MILLISECONDS_PER_DAY = MILLISECONDS_PER_HOUR * 24;
  var MILLISECONDS_PER_MONTH = MILLISECONDS_PER_DAY * (((365 * 4) + 1) / 48);
  var MILLISECONDS_PER_YEAR = MILLISECONDS_PER_MONTH * 12;
  strIso8601Time = new String(strIso8601Time);
  strNumberBuilder = "";
  strCurrentCharacter = "";
  blnInTimeSection = false;
  for (var i = 1; i < strIso8601Time.length; i++) {
    strCurrentCharacter = strIso8601Time.charAt(i);
    if (IsIso8601SectionDelimiter(strCurrentCharacter)) {
      switch (strCurrentCharacter.toUpperCase()) {
        case "Y":
          Years = parseInt(strNumberBuilder, 10);
          break;
        case "M":
          if (blnInTimeSection) {
            Minutes = parseInt(strNumberBuilder, 10);
          } else {
            Months = parseInt(strNumberBuilder, 10);
          }
          break;
        case "D":
          Days = parseInt(strNumberBuilder, 10);
          break;
        case "H":
          Hours = parseInt(strNumberBuilder, 10);
          break;
        case "S":
          Seconds = parseFloat(strNumberBuilder);
          break;
        case "T":
          blnInTimeSection = true;
          break;
      }
      strNumberBuilder = "";
    } else {
      strNumberBuilder += "" + strCurrentCharacter;
    }
  }
  WriteToDebug("Years=" + Years + "\n" + "Months=" + Months + "\n" + "Days=" + Days + "\n" + "Hours=" + Hours + "\n" + "Minutes=" + Minutes + "\n" + "Seconds=" + Seconds + "\n");
  intTotalMs = (Years * MILLISECONDS_PER_YEAR) +
    (Months * MILLISECONDS_PER_MONTH) +
    (Days * MILLISECONDS_PER_DAY) +
    (Hours * MILLISECONDS_PER_HOUR) +
    (Minutes * MILLISECONDS_PER_MINUTE) +
    (Seconds * MILLISECONDS_PER_SECOND);
  intTotalMs = Math.round(intTotalMs);
  WriteToDebug("returning-" + intTotalMs);
  return intTotalMs;
}

function IsIso8601SectionDelimiter(str) {
  if (str.search(/[PYMDTHS]/) >= 0) {
    return true;
  } else {
    return false;
  }
}

function IsValidCMITimeSpan(strValue) {
  WriteToDebug("In IsValidCMITimeSpan strValue=" + strValue);
  var regValid = /^\d?\d?\d?\d:\d?\d:\d?\d(.\d\d?)?$/;
  if (strValue.search(regValid) > -1) {
    WriteToDebug("Returning True");
    return true;
  } else {
    WriteToDebug("Returning False");
    return false;
  }
}

function IsValidIso8601TimeSpan(strValue) {
  WriteToDebug("In IsValidIso8601TimeSpan strValue=" + strValue);
  var regValid = /^P(\d+Y)?(\d+M)?(\d+D)?(T(\d+H)?(\d+M)?(\d+(.\d\d?)?S)?)?$/;
  if (strValue.search(regValid) > -1) {
    WriteToDebug("Returning True");
    return true;
  } else {
    WriteToDebug("Returning False");
    return false;
  }
}

function ConvertMilliSecondsToTCAPITime(intTotalMilliseconds, blnIncludeFraction) {
  var intHours;
  var intMinutes;
  var intSeconds;
  var intMilliseconds;
  var intHundredths;
  var strCMITimeSpan;
  WriteToDebug("In ConvertMilliSecondsToTCAPITime, intTotalMilliseconds = " + intTotalMilliseconds + ", blnIncludeFraction = " + blnIncludeFraction);
  if (blnIncludeFraction == null || blnIncludeFraction == undefined) {
    blnIncludeFraction = true;
  }
  intMilliseconds = intTotalMilliseconds % 1000;
  intSeconds = ((intTotalMilliseconds - intMilliseconds) / 1000) % 60;
  intMinutes = ((intTotalMilliseconds - intMilliseconds - (intSeconds * 1000)) / 60000) % 60;
  intHours = (intTotalMilliseconds - intMilliseconds - (intSeconds * 1000) - (intMinutes * 60000)) / 3600000;
  WriteToDebug("Separated Parts, intHours=" + intHours + ", intMinutes=" + intMinutes + ", intSeconds=" + intSeconds + ", intMilliseconds=" + intMilliseconds);
  if (intHours == 10000) {
    WriteToDebug("Max intHours detected");
    intHours = 9999;
    intMinutes = (intTotalMilliseconds - (intHours * 3600000)) / 60000;
    if (intMinutes == 100) {
      intMinutes = 99;
    }
    intMinutes = Math.floor(intMinutes);
    intSeconds = (intTotalMilliseconds - (intHours * 3600000) - (intMinutes * 60000)) / 1000;
    if (intSeconds == 100) {
      intSeconds = 99;
    }
    intSeconds = Math.floor(intSeconds);
    intMilliseconds = (intTotalMilliseconds - (intHours * 3600000) - (intMinutes * 60000) - (intSeconds * 1000));
    WriteToDebug("Separated Parts, intHours=" + intHours + ", intMinutes=" + intMinutes + ", intSeconds=" + intSeconds + ", intMilliseconds=" + intMilliseconds);
  }
  intHundredths = Math.floor(intMilliseconds / 10);
  strCMITimeSpan = ZeroPad(intHours, 4) + ":" + ZeroPad(intMinutes, 2) + ":" + ZeroPad(intSeconds, 2);
  if (blnIncludeFraction) {
    strCMITimeSpan += "." + intHundredths;
  }
  WriteToDebug("strCMITimeSpan=" + strCMITimeSpan);
  if (intHours > 9999) {
    strCMITimeSpan = "9999:99:99";
    if (blnIncludeFraction) {
      strCMITimeSpan += ".99";
    }
  }
  WriteToDebug("returning " + strCMITimeSpan);
  return strCMITimeSpan;
}

function ConvertMilliSecondsToSCORMTime(intTotalMilliseconds, blnIncludeFraction) {
  var intHours;
  var intMinutes;
  var intSeconds;
  var intMilliseconds;
  var intHundredths;
  var strCMITimeSpan;
  WriteToDebug("In ConvertMilliSecondsToSCORMTime, intTotalMilliseconds = " + intTotalMilliseconds + ", blnIncludeFraction = " + blnIncludeFraction);
  if (blnIncludeFraction == null || blnIncludeFraction == undefined) {
    blnIncludeFraction = true;
  }
  intMilliseconds = intTotalMilliseconds % 1000;
  intSeconds = ((intTotalMilliseconds - intMilliseconds) / 1000) % 60;
  intMinutes = ((intTotalMilliseconds - intMilliseconds - (intSeconds * 1000)) / 60000) % 60;
  intHours = (intTotalMilliseconds - intMilliseconds - (intSeconds * 1000) - (intMinutes * 60000)) / 3600000;
  WriteToDebug("Separated Parts, intHours=" + intHours + ", intMinutes=" + intMinutes + ", intSeconds=" + intSeconds + ", intMilliseconds=" + intMilliseconds);
  if (intHours == 10000) {
    WriteToDebug("Max intHours detected");
    intHours = 9999;
    intMinutes = (intTotalMilliseconds - (intHours * 3600000)) / 60000;
    if (intMinutes == 100) {
      intMinutes = 99;
    }
    intMinutes = Math.floor(intMinutes);
    intSeconds = (intTotalMilliseconds - (intHours * 3600000) - (intMinutes * 60000)) / 1000;
    if (intSeconds == 100) {
      intSeconds = 99;
    }
    intSeconds = Math.floor(intSeconds);
    intMilliseconds = (intTotalMilliseconds - (intHours * 3600000) - (intMinutes * 60000) - (intSeconds * 1000));
    WriteToDebug("Separated Parts, intHours=" + intHours + ", intMinutes=" + intMinutes + ", intSeconds=" + intSeconds + ", intMilliseconds=" + intMilliseconds);
  }
  intHundredths = Math.floor(intMilliseconds / 10);
  strCMITimeSpan = ZeroPad(intHours, 4) + ":" + ZeroPad(intMinutes, 2) + ":" + ZeroPad(intSeconds, 2);
  if (blnIncludeFraction) {
    strCMITimeSpan += "." + intHundredths;
  }
  WriteToDebug("strCMITimeSpan=" + strCMITimeSpan);
  if (intHours > 9999) {
    strCMITimeSpan = "9999:99:99";
    if (blnIncludeFraction) {
      strCMITimeSpan += ".99";
    }
  }
  WriteToDebug("returning " + strCMITimeSpan);
  return strCMITimeSpan;
}

function ConvertMilliSecondsIntoSCORM2004Time(intTotalMilliseconds) {
  WriteToDebug("In ConvertMilliSecondsIntoSCORM2004Time intTotalMilliseconds=" + intTotalMilliseconds);
  var ScormTime = "";
  var HundredthsOfASecond;
  var Seconds;
  var Minutes;
  var Hours;
  var Days;
  var Months;
  var Years;
  var HUNDREDTHS_PER_SECOND = 100;
  var HUNDREDTHS_PER_MINUTE = HUNDREDTHS_PER_SECOND * 60;
  var HUNDREDTHS_PER_HOUR = HUNDREDTHS_PER_MINUTE * 60;
  var HUNDREDTHS_PER_DAY = HUNDREDTHS_PER_HOUR * 24;
  var HUNDREDTHS_PER_MONTH = HUNDREDTHS_PER_DAY * (((365 * 4) + 1) / 48);
  var HUNDREDTHS_PER_YEAR = HUNDREDTHS_PER_MONTH * 12;
  HundredthsOfASecond = Math.floor(intTotalMilliseconds / 10);
  Years = Math.floor(HundredthsOfASecond / HUNDREDTHS_PER_YEAR);
  HundredthsOfASecond -= (Years * HUNDREDTHS_PER_YEAR);
  Months = Math.floor(HundredthsOfASecond / HUNDREDTHS_PER_MONTH);
  HundredthsOfASecond -= (Months * HUNDREDTHS_PER_MONTH);
  Days = Math.floor(HundredthsOfASecond / HUNDREDTHS_PER_DAY);
  HundredthsOfASecond -= (Days * HUNDREDTHS_PER_DAY);
  Hours = Math.floor(HundredthsOfASecond / HUNDREDTHS_PER_HOUR);
  HundredthsOfASecond -= (Hours * HUNDREDTHS_PER_HOUR);
  Minutes = Math.floor(HundredthsOfASecond / HUNDREDTHS_PER_MINUTE);
  HundredthsOfASecond -= (Minutes * HUNDREDTHS_PER_MINUTE);
  Seconds = Math.floor(HundredthsOfASecond / HUNDREDTHS_PER_SECOND);
  HundredthsOfASecond -= (Seconds * HUNDREDTHS_PER_SECOND);
  if (Years > 0) {
    ScormTime += Years + "Y";
  }
  if (Months > 0) {
    ScormTime += Months + "M";
  }
  if (Days > 0) {
    ScormTime += Days + "D";
  }
  if ((HundredthsOfASecond + Seconds + Minutes + Hours) > 0) {
    ScormTime += "T";
    if (Hours > 0) {
      ScormTime += Hours + "H";
    }
    if (Minutes > 0) {
      ScormTime += Minutes + "M";
    }
    if ((HundredthsOfASecond + Seconds) > 0) {
      ScormTime += Seconds;
      if (HundredthsOfASecond > 0) {
        ScormTime += "." + HundredthsOfASecond;
      }
      ScormTime += "S";
    }
  }
  if (ScormTime == "") {
    ScormTime = "T0S";
  }
  ScormTime = "P" + ScormTime;
  WriteToDebug("Returning-" + ScormTime);
  return ScormTime;
}

function ZeroPad(intNum, intNumDigits) {
  WriteToDebug("In ZeroPad intNum=" + intNum + " intNumDigits=" + intNumDigits);
  var strTemp;
  var intLen;
  var decimalToPad;
  var i;
  var isNeg = false;
  strTemp = new String(intNum);
  if (strTemp.indexOf('-') != -1) {
    isNeg = true;
    strTemp = strTemp.substr(1, strTemp.length);
  }
  if (strTemp.indexOf('.') != -1) {
    strTemp.replace('.', '');
    decimalToPad = strTemp.substr(strTemp.indexOf('.') + 1, strTemp.length);
    strTemp = strTemp.substr(0, strTemp.indexOf('.'));
  }
  intLen = strTemp.length;
  if (intLen > intNumDigits) {
    WriteToDebug("Length of string is greater than num digits, trimming string");
    strTemp = strTemp.substr(0, intNumDigits);
  } else {
    for (i = intLen; i < intNumDigits; i++) {
      strTemp = "0" + strTemp;
    }
  }
  if (isNeg == true) {
    strTemp = '-' + strTemp;
  }
  if (decimalToPad != null && decimalToPad != '') {
    if (decimalToPad.length == 1) {
      strTemp += ':' + decimalToPad + '0';
    } else {
      strTemp += ':' + decimalToPad;
    }
  }
  WriteToDebug("Returning - " + strTemp);
  return strTemp;
}

function IsValidDecimal(strValue) {
  WriteToDebug("In IsValidDecimal, strValue=" + strValue);
  strValue = new String(strValue);
  if (strValue.search(/[^.\d-]/) > -1) {
    WriteToDebug("Returning False - character other than a digit, dash or period found");
    return false;
  }
  if (strValue.search("-") > -1) {
    if (strValue.indexOf("-", 1) > -1) {
      WriteToDebug("Returning False - dash found in the middle of the string");
      return false;
    }
  }
  if (strValue.indexOf(".") != strValue.lastIndexOf(".")) {
    WriteToDebug("Returning False - more than one decimal point found");
    return false;
  }
  if (strValue.search(/\d/) < 0) {
    WriteToDebug("Returning False - no digits found");
    return false;
  }
  WriteToDebug("Returning True");
  return true;
}

function IsAlphaNumeric(strValue) {
  WriteToDebug("In IsAlphaNumeric");
  if (strValue.search(/\w/) < 0) {
    WriteToDebug("Returning false");
    return false;
  } else {
    WriteToDebug("Returning true");
    return true;
  }
}

function ReverseNameSequence(strName) {
  var strFirstName;
  var strLastName;
  var intCommaLoc;
  if (strName == "") strName = "Not Found, Learner Name";
  intCommaLoc = strName.indexOf(",");
  strFirstName = strName.slice(intCommaLoc + 1);
  strLastName = strName.slice(0, intCommaLoc);
  strFirstName = Trim(strFirstName);
  strLastName = Trim(strLastName);
  return strFirstName + ' ' + strLastName;
}

function LTrim(str) {
  str = new String(str);
  return (str.replace(/^\s+/, ''));
}

function RTrim(str) {
  str = new String(str);
  return (str.replace(/\s+$/, ''));
}

function Trim(strToTrim) {
  var str = LTrim(RTrim(strToTrim));
  return (str.replace(/\s{2,}/g, " "));
}

function GetValueFromDataChunk(strID) {
  var strChunk = new String(GetDataChunk());
  var aryPairs = new Array();
  var aryValues = new Array();
  var i;
  aryPairs = strChunk.split(parent.DATA_CHUNK_PAIR_SEPARATOR);
  for (i = 0; i < aryPairs.length; i++) {
    aryValues = aryPairs[i].split(parent.DATA_CHUNK_VALUE_SEPARATOR);
    if (aryValues[0] == strID) return aryValues[1];
  }
  return '';
}

function SetDataChunkValue(strID, strValue) {
  var strChunk = new String(GetDataChunk());
  var aryPairs = new Array();
  var aryValues = new Array();
  var i;
  var blnFound = new Boolean(false);
  aryPairs = strChunk.split(parent.DATA_CHUNK_PAIR_SEPARATOR);
  for (i = 0; i < aryPairs.length; i++) {
    aryValues = aryPairs[i].split(parent.DATA_CHUNK_VALUE_SEPARATOR);
    if (aryValues[0] == strID) {
      aryValues[1] = strValue;
      blnFound = true;
      aryPairs[i] = aryValues[0] + parent.DATA_CHUNK_VALUE_SEPARATOR + aryValues[1];
    }
  }
  if (blnFound == true) {
    strChunk = aryPairs.join(parent.DATA_CHUNK_PAIR_SEPARATOR);
  } else {
    if (strChunk == '') {
      strChunk = strID + parent.DATA_CHUNK_VALUE_SEPARATOR + strValue;
    } else {
      strChunk += parent.DATA_CHUNK_PAIR_SEPARATOR + strID + parent.DATA_CHUNK_VALUE_SEPARATOR + strValue;
    }
  }
  SetDataChunk(strChunk);
  return true;
}

function GetLastDirAndPageName(str) {
  var page = new String(str);
  var LastSlashLocation = page.lastIndexOf("/");
  var SecondLastSlashLocation = page.lastIndexOf("/", LastSlashLocation - 1);
  return page.substr(SecondLastSlashLocation + 1);
}

function RoundToPrecision(number, significantDigits) {
  number = parseFloat(number);
  return (Math.round(number * Math.pow(10, significantDigits)) / Math.pow(10, significantDigits))
}

function IsAbsoluteUrl(urlStr) {
  return urlStr != null && (urlStr.indexOf("http://") == 0 || urlStr.indexOf("https://") == 0)
}

function TouchCloud() {
  if (APPID != null && APPID != "" && APPID != "__APPID__" && CLOUDURL !== null && CLOUDURL.indexOf("http") === 0) {
    var cloudForm = document.createElement("form");
    cloudForm.name = "cloudform";
    cloudForm.id = "cloudform";
    cloudForm.style = "display:none;";
    document.body.appendChild(cloudForm);
    var elAppId = document.createElement("input");
    elAppId.name = "appId";
    elAppId.value = APPID;
    elAppId.type = "hidden";
    cloudForm.appendChild(elAppId);
    var elUrl = document.createElement("input");
    elUrl.name = "servingUrl";
    elUrl.type = "hidden";
    elUrl.value = document.location.href;
    cloudForm.appendChild(elUrl);
    var elVersion = document.createElement("input");
    elVersion.name = "version";
    elVersion.type = "hidden";
    elVersion.value = VERSION;
    cloudForm.appendChild(elVersion);
    cloudForm.target = "rusticisoftware_aicc_results";
    cloudForm.action = CLOUDURL;
    document.getElementById('cloudform').submit();
    return true;
  } else {
    return false;
  }
}

function IsNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

function loadScript(url, callback) {
  var head = document.getElementsByTagName('head')[0],
    script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = url;
  if (!script.addEventListener || (document.documentMode && document.documentMode < 9)) {
    script.onreadystatechange = function() {
      if (/loaded|complete/.test(script.readyState)) {
        script.onreadystatechange = null;
        callback();
      }
    };
  } else {
    script.addEventListener("load", callback, false);
  }
  head.appendChild(script);
};

"0.31.0";
var CryptoJS = CryptoJS || function(i, m) {
  var p = {},
    h = p.lib = {},
    n = h.Base = function() {
      function a() {}
      return {
        extend: function(b) {
          a.prototype = this;
          var c = new a;
          b && c.mixIn(b);
          c.$super = this;
          return c
        },
        create: function() {
          var a = this.extend();
          a.init.apply(a, arguments);
          return a
        },
        init: function() {},
        mixIn: function(a) {
          for (var c in a) a.hasOwnProperty(c) && (this[c] = a[c]);
          a.hasOwnProperty("toString") && (this.toString = a.toString)
        },
        clone: function() {
          return this.$super.extend(this)
        }
      }
    }(),
    o = h.WordArray = n.extend({
      init: function(a, b) {
        a = this.words = a || [];
        this.sigBytes = b != m ? b : 4 * a.length
      },
      toString: function(a) {
        return (a || e).stringify(this)
      },
      concat: function(a) {
        var b = this.words,
          c = a.words,
          d = this.sigBytes,
          a = a.sigBytes;
        this.clamp();
        if (d % 4)
          for (var f = 0; f < a; f++) b[d + f >>> 2] |= (c[f >>> 2] >>> 24 - 8 * (f % 4) & 255) << 24 - 8 * ((d + f) % 4);
        else if (65535 < c.length)
          for (f = 0; f < a; f += 4) b[d + f >>> 2] = c[f >>> 2];
        else b.push.apply(b, c);
        this.sigBytes += a;
        return this
      },
      clamp: function() {
        var a = this.words,
          b = this.sigBytes;
        a[b >>> 2] &= 4294967295 << 32 - 8 * (b % 4);
        a.length = i.ceil(b / 4)
      },
      clone: function() {
        var a = n.clone.call(this);
        a.words = this.words.slice(0);
        return a
      },
      random: function(a) {
        for (var b = [], c = 0; c < a; c += 4) b.push(4294967296 * i.random() | 0);
        return o.create(b, a)
      }
    }),
    q = p.enc = {},
    e = q.Hex = {
      stringify: function(a) {
        for (var b = a.words, a = a.sigBytes, c = [], d = 0; d < a; d++) {
          var f = b[d >>> 2] >>> 24 - 8 * (d % 4) & 255;
          c.push((f >>> 4).toString(16));
          c.push((f & 15).toString(16))
        }
        return c.join("")
      },
      parse: function(a) {
        for (var b = a.length, c = [], d = 0; d < b; d += 2) c[d >>> 3] |= parseInt(a.substr(d, 2), 16) << 24 - 4 * (d % 8);
        return o.create(c, b / 2)
      }
    },
    g = q.Latin1 = {
      stringify: function(a) {
        for (var b = a.words, a = a.sigBytes, c = [], d = 0; d < a; d++) c.push(String.fromCharCode(b[d >>> 2] >>> 24 - 8 * (d % 4) & 255));
        return c.join("")
      },
      parse: function(a) {
        for (var b = a.length, c = [], d = 0; d < b; d++) c[d >>> 2] |= (a.charCodeAt(d) & 255) << 24 - 8 * (d % 4);
        return o.create(c, b)
      }
    },
    j = q.Utf8 = {
      stringify: function(a) {
        try {
          return decodeURIComponent(escape(g.stringify(a)))
        } catch (b) {
          throw Error("Malformed UTF-8 data");
        }
      },
      parse: function(a) {
        return g.parse(unescape(encodeURIComponent(a)))
      }
    },
    k = h.BufferedBlockAlgorithm = n.extend({
      reset: function() {
        this._data = o.create();
        this._nDataBytes = 0
      },
      _append: function(a) {
        "string" == typeof a && (a = j.parse(a));
        this._data.concat(a);
        this._nDataBytes += a.sigBytes
      },
      _process: function(a) {
        var b = this._data,
          c = b.words,
          d = b.sigBytes,
          f = this.blockSize,
          e = d / (4 * f),
          e = a ? i.ceil(e) : i.max((e | 0) - this._minBufferSize, 0),
          a = e * f,
          d = i.min(4 * a, d);
        if (a) {
          for (var g = 0; g < a; g += f) this._doProcessBlock(c, g);
          g = c.splice(0, a);
          b.sigBytes -= d
        }
        return o.create(g, d)
      },
      clone: function() {
        var a = n.clone.call(this);
        a._data = this._data.clone();
        return a
      },
      _minBufferSize: 0
    });
  h.Hasher = k.extend({
    init: function() {
      this.reset()
    },
    reset: function() {
      k.reset.call(this);
      this._doReset()
    },
    update: function(a) {
      this._append(a);
      this._process();
      return this
    },
    finalize: function(a) {
      a && this._append(a);
      this._doFinalize();
      return this._hash
    },
    clone: function() {
      var a = k.clone.call(this);
      a._hash = this._hash.clone();
      return a
    },
    blockSize: 16,
    _createHelper: function(a) {
      return function(b, c) {
        return a.create(c).finalize(b)
      }
    },
    _createHmacHelper: function(a) {
      return function(b, c) {
        return l.HMAC.create(a, c).finalize(b)
      }
    }
  });
  var l = p.algo = {};
  return p
}(Math);
(function() {
  var i = CryptoJS,
    m = i.lib,
    p = m.WordArray,
    m = m.Hasher,
    h = [],
    n = i.algo.SHA1 = m.extend({
      _doReset: function() {
        this._hash = p.create([1732584193, 4023233417, 2562383102, 271733878, 3285377520])
      },
      _doProcessBlock: function(o, i) {
        for (var e = this._hash.words, g = e[0], j = e[1], k = e[2], l = e[3], a = e[4], b = 0; 80 > b; b++) {
          if (16 > b) h[b] = o[i + b] | 0;
          else {
            var c = h[b - 3] ^ h[b - 8] ^ h[b - 14] ^ h[b - 16];
            h[b] = c << 1 | c >>> 31
          }
          c = (g << 5 | g >>> 27) + a + h[b];
          c = 20 > b ? c + ((j & k | ~j & l) + 1518500249) : 40 > b ? c + ((j ^ k ^ l) + 1859775393) : 60 > b ? c + ((j & k | j & l | k & l) - 1894007588) : c + ((j ^ k ^ l) -
            899497514);
          a = l;
          l = k;
          k = j << 30 | j >>> 2;
          j = g;
          g = c
        }
        e[0] = e[0] + g | 0;
        e[1] = e[1] + j | 0;
        e[2] = e[2] + k | 0;
        e[3] = e[3] + l | 0;
        e[4] = e[4] + a | 0
      },
      _doFinalize: function() {
        var i = this._data,
          h = i.words,
          e = 8 * this._nDataBytes,
          g = 8 * i.sigBytes;
        h[g >>> 5] |= 128 << 24 - g % 32;
        h[(g + 64 >>> 9 << 4) + 15] = e;
        i.sigBytes = 4 * h.length;
        this._process()
      }
    });
  i.SHA1 = m._createHelper(n);
  i.HmacSHA1 = m._createHmacHelper(n)
})();
(function() {
  var C = CryptoJS;
  var C_lib = C.lib;
  var WordArray = C_lib.WordArray;
  var C_enc = C.enc;
  var Base64 = C_enc.Base64 = {
    stringify: function(wordArray) {
      var words = wordArray.words;
      var sigBytes = wordArray.sigBytes;
      var map = this._map;
      wordArray.clamp();
      var base64Chars = [];
      for (var i = 0; i < sigBytes; i += 3) {
        var byte1 = (words[i >>> 2] >>> (24 - (i % 4) * 8)) & 0xff;
        var byte2 = (words[(i + 1) >>> 2] >>> (24 - ((i + 1) % 4) * 8)) & 0xff;
        var byte3 = (words[(i + 2) >>> 2] >>> (24 - ((i + 2) % 4) * 8)) & 0xff;
        var triplet = (byte1 << 16) | (byte2 << 8) | byte3;
        for (var j = 0;
             (j < 4) && (i + j * 0.75 < sigBytes); j++) {
          base64Chars.push(map.charAt((triplet >>> (6 * (3 - j))) & 0x3f));
        }
      }
      var paddingChar = map.charAt(64);
      if (paddingChar) {
        while (base64Chars.length % 4) {
          base64Chars.push(paddingChar);
        }
      }
      return base64Chars.join('');
    },
    parse: function(base64Str) {
      base64Str = base64Str.replace(/\s/g, '');
      var base64StrLength = base64Str.length;
      var map = this._map;
      var paddingChar = map.charAt(64);
      if (paddingChar) {
        var paddingIndex = base64Str.indexOf(paddingChar);
        if (paddingIndex != -1) {
          base64StrLength = paddingIndex;
        }
      }
      var words = [];
      var nBytes = 0;
      for (var i = 0; i < base64StrLength; i++) {
        if (i % 4) {
          var bitsHigh = map.indexOf(base64Str.charAt(i - 1)) << ((i % 4) * 2);
          var bitsLow = map.indexOf(base64Str.charAt(i)) >>> (6 - (i % 4) * 2);
          words[nBytes >>> 2] |= (bitsHigh | bitsLow) << (24 - (nBytes % 4) * 8);
          nBytes++;
        }
      }
      return WordArray.create(words, nBytes);
    },
    _map: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/='
  };
}());
var TinCan;
(function() {
  "use strict";
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
    "Accept-Language": true
  };
  TinCan = function(cfg) {
    this.log("constructor");
    this.recordStores = [];
    this.actor = null;
    this.activity = null;
    this.registration = null;
    this.context = null;
    this.init(cfg);
  };
  TinCan.prototype = {
    LOG_SRC: "TinCan",
    log: function(msg, src) {
      if (TinCan.DEBUG && typeof console !== "undefined" && console.log) {
        src = src || this.LOG_SRC || "TinCan";
        console.log("TinCan." + src + ": " + msg);
      }
    },
    init: function(cfg) {
      this.log("init");
      var i;
      cfg = cfg || {};
      if (cfg.hasOwnProperty("url") && cfg.url !== "") {
        this._initFromQueryString(cfg.url);
      }
      if (cfg.hasOwnProperty("recordStores") && cfg.recordStores !== undefined) {
        for (i = 0; i < cfg.recordStores.length; i += 1) {
          this.addRecordStore(cfg.recordStores[i]);
        }
      }
      if (cfg.hasOwnProperty("activity")) {
        if (cfg.activity instanceof TinCan.Activity) {
          this.activity = cfg.activity;
        } else {
          this.activity = new TinCan.Activity(cfg.activity);
        }
      }
      if (cfg.hasOwnProperty("actor")) {
        if (cfg.actor instanceof TinCan.Agent) {
          this.actor = cfg.actor;
        } else {
          this.actor = new TinCan.Agent(cfg.actor);
        }
      }
      if (cfg.hasOwnProperty("context")) {
        if (cfg.context instanceof TinCan.Context) {
          this.context = cfg.context;
        } else {
          this.context = new TinCan.Context(cfg.context);
        }
      }
      if (cfg.hasOwnProperty("registration")) {
        this.registration = cfg.registration;
      }
    },
    _initFromQueryString: function(url) {
      this.log("_initFromQueryString");
      var i, prop, qsParams = TinCan.Utils.parseURL(url).params,
        lrsProps = ["endpoint", "auth"],
        lrsCfg = {},
        contextCfg, extended = null;
      if (qsParams.hasOwnProperty("actor")) {
        this.log("_initFromQueryString - found actor: " + qsParams.actor);
        try {
          this.actor = TinCan.Agent.fromJSON(qsParams.actor);
          delete qsParams.actor;
        } catch (ex) {
          this.log("_initFromQueryString - failed to set actor: " + ex);
        }
      }
      if (qsParams.hasOwnProperty("activity_id")) {
        this.activity = new TinCan.Activity({
          id: qsParams.activity_id
        });
        delete qsParams.activity_id;
      }
      if (qsParams.hasOwnProperty("activity_platform") || qsParams.hasOwnProperty("registration") || qsParams.hasOwnProperty("grouping")) {
        contextCfg = {};
        if (qsParams.hasOwnProperty("activity_platform")) {
          contextCfg.platform = qsParams.activity_platform;
          delete qsParams.activity_platform;
        }
        if (qsParams.hasOwnProperty("registration")) {
          contextCfg.registration = this.registration = qsParams.registration;
          delete qsParams.registration;
        }
        if (qsParams.hasOwnProperty("grouping")) {
          contextCfg.contextActivities = {};
          contextCfg.contextActivities.grouping = qsParams.grouping;
          delete qsParams.grouping;
        }
        this.context = new TinCan.Context(contextCfg);
      }
      if (qsParams.hasOwnProperty("endpoint")) {
        for (i = 0; i < lrsProps.length; i += 1) {
          prop = lrsProps[i];
          if (qsParams.hasOwnProperty(prop)) {
            lrsCfg[prop] = qsParams[prop];
            delete qsParams[prop];
          }
        }
        for (i in qsParams) {
          if (qsParams.hasOwnProperty(i)) {
            if (_reservedQSParams.hasOwnProperty(i)) {
              delete qsParams[i];
            } else {
              extended = extended || {};
              extended[i] = qsParams[i];
            }
          }
        }
        if (extended !== null) {
          lrsCfg.extended = extended;
        }
        lrsCfg.allowFail = false;
        this.addRecordStore(lrsCfg);
      }
    },
    addRecordStore: function(cfg) {
      this.log("addRecordStore");
      var lrs;
      if (cfg instanceof TinCan.LRS) {
        lrs = cfg;
      } else {
        lrs = new TinCan.LRS(cfg);
      }
      this.recordStores.push(lrs);
    },
    prepareStatement: function(stmt) {
      this.log("prepareStatement");
      if (!(stmt instanceof TinCan.Statement)) {
        stmt = new TinCan.Statement(stmt);
      }
      if (stmt.actor === null && this.actor !== null) {
        stmt.actor = this.actor;
      }
      if (stmt.target === null && this.activity !== null) {
        stmt.target = this.activity;
      }
      if (this.context !== null) {
        if (stmt.context === null) {
          stmt.context = this.context;
        } else {
          if (stmt.context.registration === null) {
            stmt.context.registration = this.context.registration;
          }
          if (stmt.context.platform === null) {
            stmt.context.platform = this.context.platform;
          }
          if (this.context.contextActivities !== null) {
            if (stmt.context.contextActivities === null) {
              stmt.context.contextActivities = this.context.contextActivities;
            } else {
              if (this.context.contextActivities.grouping !== null && stmt.context.contextActivities.grouping === null) {
                stmt.context.contextActivities.grouping = this.context.contextActivities.grouping;
              }
              if (this.context.contextActivities.parent !== null && stmt.context.contextActivities.parent === null) {
                stmt.context.contextActivities.parent = this.context.contextActivities.parent;
              }
              if (this.context.contextActivities.other !== null && stmt.context.contextActivities.other === null) {
                stmt.context.contextActivities.other = this.context.contextActivities.other;
              }
            }
          }
        }
      }
      return stmt;
    },
    sendStatement: function(stmt, callback) {
      this.log("sendStatement");
      var self = this,
        lrs, statement = this.prepareStatement(stmt),
        rsCount = this.recordStores.length,
        i, results = [],
        callbackWrapper, callbackResults = [];
      if (rsCount > 0) {
        if (typeof callback === "function") {
          callbackWrapper = function(err, xhr) {
            var args;
            self.log("sendStatement - callbackWrapper: " + rsCount);
            if (rsCount > 1) {
              rsCount -= 1;
              callbackResults.push({
                err: err,
                xhr: xhr
              });
            } else if (rsCount === 1) {
              callbackResults.push({
                err: err,
                xhr: xhr
              });
              args = [callbackResults, statement];
              callback.apply(this, args);
            } else {
              self.log("sendStatement - unexpected record store count: " + rsCount);
            }
          };
        }
        for (i = 0; i < rsCount; i += 1) {
          lrs = this.recordStores[i];
          results.push(lrs.saveStatement(statement, {
            callback: callbackWrapper
          }));
        }
      } else {
        this.log("[warning] sendStatement: No LRSs added yet (statement not sent)");
        if (typeof callback === "function") {
          callback.apply(this, [null, statement]);
        }
      }
      return {
        statement: statement,
        results: results
      };
    },
    getStatement: function(stmtId, callback) {
      this.log("getStatement");
      var lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        return lrs.retrieveStatement(stmtId, {
          callback: callback
        });
      }
      this.log("[warning] getStatement: No LRSs added yet (statement not retrieved)");
    },
    voidStatement: function(stmt, callback, options) {
      this.log("voidStatement");
      var self = this,
        lrs, actor, voidingStatement, rsCount = this.recordStores.length,
        i, results = [],
        callbackWrapper, callbackResults = [];
      if (stmt instanceof TinCan.Statement) {
        stmt = stmt.id;
      }
      if (typeof options.actor !== "undefined") {
        actor = options.actor;
      } else if (this.actor !== null) {
        actor = this.actor;
      }
      voidingStatement = new TinCan.Statement({
        actor: actor,
        verb: {
          id: "http://adlnet.gov/expapi/verbs/voided"
        },
        target: {
          objectType: "StatementRef",
          id: stmt
        }
      });
      if (rsCount > 0) {
        if (typeof callback === "function") {
          callbackWrapper = function(err, xhr) {
            var args;
            self.log("voidStatement - callbackWrapper: " + rsCount);
            if (rsCount > 1) {
              rsCount -= 1;
              callbackResults.push({
                err: err,
                xhr: xhr
              });
            } else if (rsCount === 1) {
              callbackResults.push({
                err: err,
                xhr: xhr
              });
              args = [callbackResults, voidingStatement];
              callback.apply(this, args);
            } else {
              self.log("voidStatement - unexpected record store count: " + rsCount);
            }
          };
        }
        for (i = 0; i < rsCount; i += 1) {
          lrs = this.recordStores[i];
          results.push(lrs.saveStatement(voidingStatement, {
            callback: callbackWrapper
          }));
        }
      } else {
        this.log("[warning] voidStatement: No LRSs added yet (statement not sent)");
        if (typeof callback === "function") {
          callback.apply(this, [null, voidingStatement]);
        }
      }
      return {
        statement: voidingStatement,
        results: results
      };
    },
    getVoidedStatement: function(stmtId, callback) {
      this.log("getVoidedStatement");
      var lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        return lrs.retrieveVoidedStatement(stmtId, {
          callback: callback
        });
      }
      this.log("[warning] getVoidedStatement: No LRSs added yet (statement not retrieved)");
    },
    sendStatements: function(stmts, callback) {
      this.log("sendStatements");
      var self = this,
        lrs, statements = [],
        rsCount = this.recordStores.length,
        i, results = [],
        callbackWrapper, callbackResults = [];
      if (stmts.length === 0) {
        if (typeof callback === "function") {
          callback.apply(this, [null, statements]);
        }
      } else {
        for (i = 0; i < stmts.length; i += 1) {
          statements.push(this.prepareStatement(stmts[i]));
        }
        if (rsCount > 0) {
          if (typeof callback === "function") {
            callbackWrapper = function(err, xhr) {
              var args;
              self.log("sendStatements - callbackWrapper: " + rsCount);
              if (rsCount > 1) {
                rsCount -= 1;
                callbackResults.push({
                  err: err,
                  xhr: xhr
                });
              } else if (rsCount === 1) {
                callbackResults.push({
                  err: err,
                  xhr: xhr
                });
                args = [callbackResults, statements];
                callback.apply(this, args);
              } else {
                self.log("sendStatements - unexpected record store count: " + rsCount);
              }
            };
          }
          for (i = 0; i < rsCount; i += 1) {
            lrs = this.recordStores[i];
            results.push(lrs.saveStatements(statements, {
              callback: callbackWrapper
            }));
          }
        } else {
          this.log("[warning] sendStatements: No LRSs added yet (statements not sent)");
          if (typeof callback === "function") {
            callback.apply(this, [null, statements]);
          }
        }
      }
      return {
        statements: statements,
        results: results
      };
    },
    getStatements: function(cfg) {
      this.log("getStatements");
      var queryCfg = {},
        lrs, params;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        cfg = cfg || {};
        params = cfg.params || {};
        if (cfg.sendActor && this.actor !== null) {
          if (lrs.version === "0.9" || lrs.version === "0.95") {
            params.actor = this.actor;
          } else {
            params.agent = this.actor;
          }
        }
        if (cfg.sendActivity && this.activity !== null) {
          if (lrs.version === "0.9" || lrs.version === "0.95") {
            params.target = this.activity;
          } else {
            params.activity = this.activity;
          }
        }
        if (typeof params.registration === "undefined" && this.registration !== null) {
          params.registration = this.registration;
        }
        queryCfg = {
          params: params
        };
        if (typeof cfg.callback !== "undefined") {
          queryCfg.callback = cfg.callback;
        }
        return lrs.queryStatements(queryCfg);
      }
      this.log("[warning] getStatements: No LRSs added yet (statements not read)");
    },
    getState: function(key, cfg) {
      this.log("getState");
      var queryCfg, lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        cfg = cfg || {};
        queryCfg = {
          agent: (typeof cfg.agent !== "undefined" ? cfg.agent : this.actor),
          activity: (typeof cfg.activity !== "undefined" ? cfg.activity : this.activity)
        };
        if (typeof cfg.registration !== "undefined") {
          queryCfg.registration = cfg.registration;
        } else if (this.registration !== null) {
          queryCfg.registration = this.registration;
        }
        if (typeof cfg.callback !== "undefined") {
          queryCfg.callback = cfg.callback;
        }
        return lrs.retrieveState(key, queryCfg);
      }
      this.log("[warning] getState: No LRSs added yet (state not retrieved)");
    },
    setState: function(key, val, cfg) {
      this.log("setState");
      var queryCfg, lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        cfg = cfg || {};
        queryCfg = {
          agent: (typeof cfg.agent !== "undefined" ? cfg.agent : this.actor),
          activity: (typeof cfg.activity !== "undefined" ? cfg.activity : this.activity)
        };
        if (typeof cfg.registration !== "undefined") {
          queryCfg.registration = cfg.registration;
        } else if (this.registration !== null) {
          queryCfg.registration = this.registration;
        }
        if (typeof cfg.lastSHA1 !== "undefined") {
          queryCfg.lastSHA1 = cfg.lastSHA1;
        }
        if (typeof cfg.contentType !== "undefined") {
          queryCfg.contentType = cfg.contentType;
        }
        if (typeof cfg.callback !== "undefined") {
          queryCfg.callback = cfg.callback;
        }
        return lrs.saveState(key, val, queryCfg);
      }
      this.log("[warning] setState: No LRSs added yet (state not saved)");
    },
    deleteState: function(key, cfg) {
      this.log("deleteState");
      var queryCfg, lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        cfg = cfg || {};
        queryCfg = {
          agent: (typeof cfg.agent !== "undefined" ? cfg.agent : this.actor),
          activity: (typeof cfg.activity !== "undefined" ? cfg.activity : this.activity)
        };
        if (typeof cfg.registration !== "undefined") {
          queryCfg.registration = cfg.registration;
        } else if (this.registration !== null) {
          queryCfg.registration = this.registration;
        }
        if (typeof cfg.callback !== "undefined") {
          queryCfg.callback = cfg.callback;
        }
        return lrs.dropState(key, queryCfg);
      }
      this.log("[warning] deleteState: No LRSs added yet (state not deleted)");
    },
    getActivityProfile: function(key, cfg) {
      this.log("getActivityProfile");
      var queryCfg, lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        cfg = cfg || {};
        queryCfg = {
          activity: (typeof cfg.activity !== "undefined" ? cfg.activity : this.activity)
        };
        if (typeof cfg.callback !== "undefined") {
          queryCfg.callback = cfg.callback;
        }
        return lrs.retrieveActivityProfile(key, queryCfg);
      }
      this.log("[warning] getActivityProfile: No LRSs added yet (activity profile not retrieved)");
    },
    setActivityProfile: function(key, val, cfg) {
      this.log("setActivityProfile");
      var queryCfg, lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        cfg = cfg || {};
        queryCfg = {
          activity: (typeof cfg.activity !== "undefined" ? cfg.activity : this.activity)
        };
        if (typeof cfg.callback !== "undefined") {
          queryCfg.callback = cfg.callback;
        }
        if (typeof cfg.lastSHA1 !== "undefined") {
          queryCfg.lastSHA1 = cfg.lastSHA1;
        }
        if (typeof cfg.contentType !== "undefined") {
          queryCfg.contentType = cfg.contentType;
        }
        return lrs.saveActivityProfile(key, val, queryCfg);
      }
      this.log("[warning] setActivityProfile: No LRSs added yet (activity profile not saved)");
    },
    deleteActivityProfile: function(key, cfg) {
      this.log("deleteActivityProfile");
      var queryCfg, lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        cfg = cfg || {};
        queryCfg = {
          activity: (typeof cfg.activity !== "undefined" ? cfg.activity : this.activity)
        };
        if (typeof cfg.callback !== "undefined") {
          queryCfg.callback = cfg.callback;
        }
        return lrs.dropActivityProfile(key, queryCfg);
      }
      this.log("[warning] deleteActivityProfile: No LRSs added yet (activity profile not deleted)");
    },
    getAgentProfile: function(key, cfg) {
      this.log("getAgentProfile");
      var queryCfg, lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        cfg = cfg || {};
        queryCfg = {
          agent: (typeof cfg.agent !== "undefined" ? cfg.agent : this.actor)
        };
        if (typeof cfg.callback !== "undefined") {
          queryCfg.callback = cfg.callback;
        }
        return lrs.retrieveAgentProfile(key, queryCfg);
      }
      this.log("[warning] getAgentProfile: No LRSs added yet (agent profile not retrieved)");
    },
    setAgentProfile: function(key, val, cfg) {
      this.log("setAgentProfile");
      var queryCfg, lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        cfg = cfg || {};
        queryCfg = {
          agent: (typeof cfg.agent !== "undefined" ? cfg.agent : this.actor)
        };
        if (typeof cfg.callback !== "undefined") {
          queryCfg.callback = cfg.callback;
        }
        if (typeof cfg.lastSHA1 !== "undefined") {
          queryCfg.lastSHA1 = cfg.lastSHA1;
        }
        if (typeof cfg.contentType !== "undefined") {
          queryCfg.contentType = cfg.contentType;
        }
        return lrs.saveAgentProfile(key, val, queryCfg);
      }
      this.log("[warning] setAgentProfile: No LRSs added yet (agent profile not saved)");
    },
    deleteAgentProfile: function(key, cfg) {
      this.log("deleteAgentProfile");
      var queryCfg, lrs;
      if (this.recordStores.length > 0) {
        lrs = this.recordStores[0];
        cfg = cfg || {};
        queryCfg = {
          agent: (typeof cfg.agent !== "undefined" ? cfg.agent : this.actor)
        };
        if (typeof cfg.callback !== "undefined") {
          queryCfg.callback = cfg.callback;
        }
        return lrs.dropAgentProfile(key, queryCfg);
      }
      this.log("[warning] deleteAgentProfile: No LRSs added yet (agent profile not deleted)");
    }
  };
  TinCan.DEBUG = false;
  TinCan.enableDebug = function() {
    TinCan.DEBUG = true;
  };
  TinCan.disableDebug = function() {
    TinCan.DEBUG = false;
  };
  TinCan.versions = function() {
    return ["1.0.1", "1.0.0", "0.95", "0.9"];
  };
  if (typeof module === "object") {
    module.exports = TinCan;
  }
}());
(function() {
  "use strict";
  TinCan.Utils = {
    getUUID: function() {
      return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0,
          v = c == "x" ? r : (r & 0x3 | 0x8);
        return v.toString(16);
      });
    },
    getISODateString: function(d) {
      function pad(val, n) {
        var padder, tempVal;
        if (typeof val === "undefined" || val === null) {
          val = 0;
        }
        if (typeof n === "undefined" || n === null) {
          n = 2;
        }
        padder = Math.pow(10, n - 1);
        tempVal = val.toString();
        while (val < padder && padder > 1) {
          tempVal = "0" + tempVal;
          padder = padder / 10;
        }
        return tempVal;
      }
      return d.getUTCFullYear() + "-" +
        pad(d.getUTCMonth() + 1) + "-" +
        pad(d.getUTCDate()) + "T" +
        pad(d.getUTCHours()) + ":" +
        pad(d.getUTCMinutes()) + ":" +
        pad(d.getUTCSeconds()) + "." +
        pad(d.getUTCMilliseconds(), 3) + "Z";
    },
    getSHA1String: function(str) {
      return CryptoJS.SHA1(str).toString(CryptoJS.enc.Hex);
    },
    getBase64String: function(str) {
      return CryptoJS.enc.Base64.stringify(CryptoJS.enc.Latin1.parse(str));
    },
    getLangDictionaryValue: function(prop, lang) {
      var langDict = this[prop],
        key;
      if (typeof lang !== "undefined" && typeof langDict[lang] !== "undefined") {
        return langDict[lang];
      }
      if (typeof langDict.und !== "undefined") {
        return langDict.und;
      }
      if (typeof langDict["en-US"] !== "undefined") {
        return langDict["en-US"];
      }
      for (key in langDict) {
        if (langDict.hasOwnProperty(key)) {
          return langDict[key];
        }
      }
      return "";
    },
    parseURL: function(url) {
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
    },
    getServerRoot: function(absoluteUrl) {
      var urlParts = absoluteUrl.split("/");
      return urlParts[0] + "//" + urlParts[2];
    },
    getContentTypeFromHeader: function(header) {
      return (String(header).split(";"))[0];
    },
    isApplicationJSON: function(header) {
      return TinCan.Utils.getContentTypeFromHeader(header).toLowerCase().indexOf("application/json") === 0;
    }
  };
}());
(function() {
  "use strict";
  var LRS = TinCan.LRS = function(cfg) {
    this.log("constructor");
    this.endpoint = null;
    this.version = null;
    this.auth = null;
    this.allowFail = true;
    this.extended = null;
    this.init(cfg);
  };
  LRS.prototype = {
    LOG_SRC: "LRS",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var versions = TinCan.versions(),
        versionMatch = false,
        i;
      cfg = cfg || {};
      if (cfg.hasOwnProperty("alertOnRequestFailure")) {
        this.log("'alertOnRequestFailure' is deprecated (alerts have been removed) no need to set it now");
      }
      if (!cfg.hasOwnProperty("endpoint") || cfg.endpoint === null || cfg.endpoint === "") {
        this.log("[error] LRS invalid: no endpoint");
        throw {
          code: 3,
          mesg: "LRS invalid: no endpoint"
        };
      }
      this.endpoint = String(cfg.endpoint);
      if (this.endpoint.slice(-1) !== "/") {
        this.log("adding trailing slash to endpoint");
        this.endpoint += "/";
      }
      if (cfg.hasOwnProperty("allowFail")) {
        this.allowFail = cfg.allowFail;
      }
      if (cfg.hasOwnProperty("auth")) {
        this.auth = cfg.auth;
      } else if (cfg.hasOwnProperty("username") && cfg.hasOwnProperty("password")) {
        this.auth = "Basic " + TinCan.Utils.getBase64String(cfg.username + ":" + cfg.password);
      }
      if (cfg.hasOwnProperty("extended")) {
        this.extended = cfg.extended;
      }
      this._initByEnvironment(cfg);
      if (typeof cfg.version !== "undefined") {
        this.log("version: " + cfg.version);
        for (i = 0; i < versions.length; i += 1) {
          if (versions[i] === cfg.version) {
            versionMatch = true;
            break;
          }
        }
        if (!versionMatch) {
          this.log("[error] LRS invalid: version not supported (" + cfg.version + ")");
          throw {
            code: 5,
            mesg: "LRS invalid: version not supported (" + cfg.version + ")"
          };
        }
        this.version = cfg.version;
      } else {
        this.version = versions[0];
      }
    },
    _initByEnvironment: function() {
      this.log("_initByEnvironment not overloaded - no environment loaded?");
    },
    _makeRequest: function() {
      this.log("_makeRequest not overloaded - no environment loaded?");
    },
    _IEModeConversion: function() {
      this.log("_IEModeConversion not overloaded - browser environment not loaded.");
    },
    sendRequest: function(cfg) {
      this.log("sendRequest");
      var fullUrl = this.endpoint + cfg.url,
        headers = {},
        prop;
      if (cfg.url.indexOf("http") === 0) {
        fullUrl = cfg.url;
      }
      if (this.extended !== null) {
        cfg.params = cfg.params || {};
        for (prop in this.extended) {
          if (this.extended.hasOwnProperty(prop)) {
            if (!cfg.params.hasOwnProperty(prop)) {
              if (this.extended[prop] !== null) {
                cfg.params[prop] = this.extended[prop];
              }
            }
          }
        }
      }
      headers.Authorization = this.auth;
      if (this.version !== "0.9") {
        headers["X-Experience-API-Version"] = this.version;
      }
      for (prop in cfg.headers) {
        if (cfg.headers.hasOwnProperty(prop)) {
          headers[prop] = cfg.headers[prop];
        }
      }
      return this._makeRequest(fullUrl, headers, cfg);
    },
    about: function(cfg) {
      this.log("about");
      var requestCfg, requestResult, callbackWrapper;
      cfg = cfg || {};
      requestCfg = {
        url: "about",
        method: "GET",
        params: {}
      };
      if (typeof cfg.callback !== "undefined") {
        callbackWrapper = function(err, xhr) {
          var result = xhr;
          if (err === null) {
            result = TinCan.About.fromJSON(xhr.responseText);
          }
          cfg.callback(err, result);
        };
        requestCfg.callback = callbackWrapper;
      }
      requestResult = this.sendRequest(requestCfg);
      if (callbackWrapper) {
        return;
      }
      if (requestResult.err === null) {
        requestResult.xhr = TinCan.About.fromJSON(requestResult.xhr.responseText);
      }
      return requestResult;
    },
    saveStatement: function(stmt, cfg) {
      this.log("saveStatement");
      var requestCfg, versionedStatement;
      cfg = cfg || {};
      try {
        versionedStatement = stmt.asVersion(this.version);
      } catch (ex) {
        if (this.allowFail) {
          this.log("[warning] statement could not be serialized in version (" + this.version + "): " + ex);
          if (typeof cfg.callback !== "undefined") {
            cfg.callback(null, null);
            return;
          }
          return {
            err: null,
            xhr: null
          };
        }
        this.log("[error] statement could not be serialized in version (" + this.version + "): " + ex);
        if (typeof cfg.callback !== "undefined") {
          cfg.callback(ex, null);
          return;
        }
        return {
          err: ex,
          xhr: null
        };
      }
      requestCfg = {
        url: "statements",
        data: JSON.stringify(versionedStatement),
        headers: {
          "Content-Type": "application/json"
        }
      };
      if (stmt.id !== null) {
        requestCfg.method = "PUT";
        requestCfg.params = {
          statementId: stmt.id
        };
      } else {
        requestCfg.method = "POST";
      }
      if (typeof cfg.callback !== "undefined") {
        requestCfg.callback = cfg.callback;
      }
      return this.sendRequest(requestCfg);
    },
    retrieveStatement: function(stmtId, cfg) {
      this.log("retrieveStatement");
      var requestCfg, requestResult, callbackWrapper;
      cfg = cfg || {};
      requestCfg = {
        url: "statements",
        method: "GET",
        params: {
          statementId: stmtId
        }
      };
      if (typeof cfg.callback !== "undefined") {
        callbackWrapper = function(err, xhr) {
          var result = xhr;
          if (err === null) {
            result = TinCan.Statement.fromJSON(xhr.responseText);
          }
          cfg.callback(err, result);
        };
        requestCfg.callback = callbackWrapper;
      }
      requestResult = this.sendRequest(requestCfg);
      if (!callbackWrapper) {
        requestResult.statement = null;
        if (requestResult.err === null) {
          requestResult.statement = TinCan.Statement.fromJSON(requestResult.xhr.responseText);
        }
      }
      return requestResult;
    },
    retrieveVoidedStatement: function(stmtId, cfg) {
      this.log("retrieveVoidedStatement");
      var requestCfg, requestResult, callbackWrapper;
      cfg = cfg || {};
      requestCfg = {
        url: "statements",
        method: "GET",
        params: {}
      };
      if (this.version === "0.9" || this.version === "0.95") {
        requestCfg.params.statementId = stmtId;
      } else {
        requestCfg.params.voidedStatementId = stmtId;
      }
      if (typeof cfg.callback !== "undefined") {
        callbackWrapper = function(err, xhr) {
          var result = xhr;
          if (err === null) {
            result = TinCan.Statement.fromJSON(xhr.responseText);
          }
          cfg.callback(err, result);
        };
        requestCfg.callback = callbackWrapper;
      }
      requestResult = this.sendRequest(requestCfg);
      if (!callbackWrapper) {
        requestResult.statement = null;
        if (requestResult.err === null) {
          requestResult.statement = TinCan.Statement.fromJSON(requestResult.xhr.responseText);
        }
      }
      return requestResult;
    },
    saveStatements: function(stmts, cfg) {
      this.log("saveStatements");
      var requestCfg, versionedStatement, versionedStatements = [],
        i;
      cfg = cfg || {};
      if (stmts.length === 0) {
        if (typeof cfg.callback !== "undefined") {
          cfg.callback(new Error("no statements"), null);
          return;
        }
        return {
          err: new Error("no statements"),
          xhr: null
        };
      }
      for (i = 0; i < stmts.length; i += 1) {
        try {
          versionedStatement = stmts[i].asVersion(this.version);
        } catch (ex) {
          if (this.allowFail) {
            this.log("[warning] statement could not be serialized in version (" + this.version + "): " + ex);
            if (typeof cfg.callback !== "undefined") {
              cfg.callback(null, null);
              return;
            }
            return {
              err: null,
              xhr: null
            };
          }
          this.log("[error] statement could not be serialized in version (" + this.version + "): " + ex);
          if (typeof cfg.callback !== "undefined") {
            cfg.callback(ex, null);
            return;
          }
          return {
            err: ex,
            xhr: null
          };
        }
        versionedStatements.push(versionedStatement);
      }
      requestCfg = {
        url: "statements",
        method: "POST",
        data: JSON.stringify(versionedStatements),
        headers: {
          "Content-Type": "application/json"
        }
      };
      if (typeof cfg.callback !== "undefined") {
        requestCfg.callback = cfg.callback;
      }
      return this.sendRequest(requestCfg);
    },
    queryStatements: function(cfg) {
      this.log("queryStatements");
      var requestCfg, requestResult, callbackWrapper;
      cfg = cfg || {};
      cfg.params = cfg.params || {};
      try {
        requestCfg = this._queryStatementsRequestCfg(cfg);
      } catch (ex) {
        this.log("[error] Query statements failed - " + ex);
        if (typeof cfg.callback !== "undefined") {
          cfg.callback(ex, {});
        }
        return {
          err: ex,
          statementsResult: null
        };
      }
      if (typeof cfg.callback !== "undefined") {
        callbackWrapper = function(err, xhr) {
          var result = xhr;
          if (err === null) {
            result = TinCan.StatementsResult.fromJSON(xhr.responseText);
          }
          cfg.callback(err, result);
        };
        requestCfg.callback = callbackWrapper;
      }
      requestResult = this.sendRequest(requestCfg);
      requestResult.config = requestCfg;
      if (!callbackWrapper) {
        requestResult.statementsResult = null;
        if (requestResult.err === null) {
          requestResult.statementsResult = TinCan.StatementsResult.fromJSON(requestResult.xhr.responseText);
        }
      }
      return requestResult;
    },
    _queryStatementsRequestCfg: function(cfg) {
      this.log("_queryStatementsRequestCfg");
      var params = {},
        returnCfg = {
          url: "statements",
          method: "GET",
          params: params
        },
        jsonProps = ["agent", "actor", "object", "instructor"],
        idProps = ["verb", "activity"],
        valProps = ["registration", "context", "since", "until", "limit", "authoritative", "sparse", "ascending", "related_activities", "related_agents", "format", "attachments"],
        i, prop, universal = {
          verb: true,
          registration: true,
          since: true,
          until: true,
          limit: true,
          ascending: true
        },
        compatibility = {
          "0.9": {
            supported: {
              actor: true,
              instructor: true,
              target: true,
              object: true,
              context: true,
              authoritative: true,
              sparse: true
            }
          },
          "1.0.0": {
            supported: {
              agent: true,
              activity: true,
              related_activities: true,
              related_agents: true,
              format: true,
              attachments: true
            }
          }
        };
      compatibility["0.95"] = compatibility["0.9"];
      compatibility["1.0.1"] = compatibility["1.0.0"];
      if (cfg.params.hasOwnProperty("target")) {
        cfg.params.object = cfg.params.target;
      }
      for (prop in cfg.params) {
        if (cfg.params.hasOwnProperty(prop)) {
          if (typeof universal[prop] === "undefined" && typeof compatibility[this.version].supported[prop] === "undefined") {
            throw "Unrecognized query parameter configured: " + prop;
          }
        }
      }
      for (i = 0; i < jsonProps.length; i += 1) {
        if (typeof cfg.params[jsonProps[i]] !== "undefined") {
          params[jsonProps[i]] = JSON.stringify(cfg.params[jsonProps[i]].asVersion(this.version));
        }
      }
      for (i = 0; i < idProps.length; i += 1) {
        if (typeof cfg.params[idProps[i]] !== "undefined") {
          params[idProps[i]] = cfg.params[idProps[i]].id;
        }
      }
      for (i = 0; i < valProps.length; i += 1) {
        if (typeof cfg.params[valProps[i]] !== "undefined") {
          params[valProps[i]] = cfg.params[valProps[i]];
        }
      }
      return returnCfg;
    },
    moreStatements: function(cfg) {
      this.log("moreStatements: " + cfg.url);
      var requestCfg, requestResult, callbackWrapper, parsedURL, serverRoot;
      cfg = cfg || {};
      parsedURL = TinCan.Utils.parseURL(cfg.url);
      serverRoot = TinCan.Utils.getServerRoot(this.endpoint);
      if (parsedURL.path.indexOf("/statements") === 0) {
        parsedURL.path = this.endpoint.replace(serverRoot, "") + parsedURL.path;
        this.log("converting non-standard more URL to " + parsedURL.path);
      }
      if (parsedURL.path.indexOf("/") !== 0) {
        parsedURL.path = "/" + parsedURL.path;
      }
      requestCfg = {
        method: "GET",
        url: serverRoot + parsedURL.path,
        params: parsedURL.params
      };
      if (typeof cfg.callback !== "undefined") {
        callbackWrapper = function(err, xhr) {
          var result = xhr;
          if (err === null) {
            result = TinCan.StatementsResult.fromJSON(xhr.responseText);
          }
          cfg.callback(err, result);
        };
        requestCfg.callback = callbackWrapper;
      }
      requestResult = this.sendRequest(requestCfg);
      requestResult.config = requestCfg;
      if (!callbackWrapper) {
        requestResult.statementsResult = null;
        if (requestResult.err === null) {
          requestResult.statementsResult = TinCan.StatementsResult.fromJSON(requestResult.xhr.responseText);
        }
      }
      return requestResult;
    },
    retrieveState: function(key, cfg) {
      this.log("retrieveState");
      var requestParams = {},
        requestCfg = {},
        requestResult, callbackWrapper;
      requestParams = {
        stateId: key,
        activityId: cfg.activity.id
      };
      if (this.version === "0.9") {
        requestParams.actor = JSON.stringify(cfg.agent.asVersion(this.version));
      } else {
        requestParams.agent = JSON.stringify(cfg.agent.asVersion(this.version));
      }
      if (typeof cfg.registration !== "undefined") {
        if (this.version === "0.9") {
          requestParams.registrationId = cfg.registration;
        } else {
          requestParams.registration = cfg.registration;
        }
      }
      requestCfg = {
        url: "activities/state",
        method: "GET",
        params: requestParams,
        ignore404: true
      };
      if (typeof cfg.callback !== "undefined") {
        callbackWrapper = function(err, xhr) {
          var result = xhr;
          if (err === null) {
            if (xhr.status === 404) {
              result = null;
            } else {
              result = new TinCan.State({
                id: key,
                contents: xhr.responseText
              });
              if (typeof xhr.getResponseHeader !== "undefined" && xhr.getResponseHeader("ETag") !== null && xhr.getResponseHeader("ETag") !== "") {
                result.etag = xhr.getResponseHeader("ETag");
              } else {
                result.etag = TinCan.Utils.getSHA1String(xhr.responseText);
              }
              if (typeof xhr.contentType !== "undefined") {
                result.contentType = xhr.contentType;
              } else if (typeof xhr.getResponseHeader !== "undefined" && xhr.getResponseHeader("Content-Type") !== null && xhr.getResponseHeader("Content-Type") !== "") {
                result.contentType = xhr.getResponseHeader("Content-Type");
              }
              if (TinCan.Utils.isApplicationJSON(result.contentType)) {
                try {
                  result.contents = JSON.parse(result.contents);
                } catch (ex) {
                  this.log("retrieveState - failed to deserialize JSON: " + ex);
                }
              }
            }
          }
          cfg.callback(err, result);
        };
        requestCfg.callback = callbackWrapper;
      }
      requestResult = this.sendRequest(requestCfg);
      if (!callbackWrapper) {
        requestResult.state = null;
        if (requestResult.err === null && requestResult.xhr.status !== 404) {
          requestResult.state = new TinCan.State({
            id: key,
            contents: requestResult.xhr.responseText
          });
          if (typeof requestResult.xhr.getResponseHeader !== "undefined" && requestResult.xhr.getResponseHeader("ETag") !== null && requestResult.xhr.getResponseHeader("ETag") !== "") {
            requestResult.state.etag = requestResult.xhr.getResponseHeader("ETag");
          } else {
            requestResult.state.etag = TinCan.Utils.getSHA1String(requestResult.xhr.responseText);
          }
          if (typeof requestResult.xhr.contentType !== "undefined") {
            requestResult.state.contentType = requestResult.xhr.contentType;
          } else if (typeof requestResult.xhr.getResponseHeader !== "undefined" && requestResult.xhr.getResponseHeader("Content-Type") !== null && requestResult.xhr.getResponseHeader("Content-Type") !== "") {
            requestResult.state.contentType = requestResult.xhr.getResponseHeader("Content-Type");
          }
          if (TinCan.Utils.isApplicationJSON(requestResult.state.contentType)) {
            try {
              requestResult.state.contents = JSON.parse(requestResult.state.contents);
            } catch (ex) {
              this.log("retrieveState - failed to deserialize JSON: " + ex);
            }
          }
        }
      }
      return requestResult;
    },
    saveState: function(key, val, cfg) {
      this.log("saveState");
      var requestParams, requestCfg;
      if (typeof cfg.contentType === "undefined") {
        cfg.contentType = "application/octet-stream";
      }
      if (typeof val === "object" && TinCan.Utils.isApplicationJSON(cfg.contentType)) {
        val = JSON.stringify(val);
      }
      requestParams = {
        stateId: key,
        activityId: cfg.activity.id
      };
      if (this.version === "0.9") {
        requestParams.actor = JSON.stringify(cfg.agent.asVersion(this.version));
      } else {
        requestParams.agent = JSON.stringify(cfg.agent.asVersion(this.version));
      }
      if (typeof cfg.registration !== "undefined") {
        if (this.version === "0.9") {
          requestParams.registrationId = cfg.registration;
        } else {
          requestParams.registration = cfg.registration;
        }
      }
      requestCfg = {
        url: "activities/state",
        method: "PUT",
        params: requestParams,
        data: val,
        headers: {
          "Content-Type": cfg.contentType
        }
      };
      if (typeof cfg.callback !== "undefined") {
        requestCfg.callback = cfg.callback;
      }
      if (typeof cfg.lastSHA1 !== "undefined" && cfg.lastSHA1 !== null) {
        requestCfg.headers["If-Match"] = cfg.lastSHA1;
      }
      return this.sendRequest(requestCfg);
    },
    dropState: function(key, cfg) {
      this.log("dropState");
      var requestParams, requestCfg;
      requestParams = {
        activityId: cfg.activity.id
      };
      if (this.version === "0.9") {
        requestParams.actor = JSON.stringify(cfg.agent.asVersion(this.version));
      } else {
        requestParams.agent = JSON.stringify(cfg.agent.asVersion(this.version));
      }
      if (key !== null) {
        requestParams.stateId = key;
      }
      if (typeof cfg.registration !== "undefined") {
        if (this.version === "0.9") {
          requestParams.registrationId = cfg.registration;
        } else {
          requestParams.registration = cfg.registration;
        }
      }
      requestCfg = {
        url: "activities/state",
        method: "DELETE",
        params: requestParams
      };
      if (typeof cfg.callback !== "undefined") {
        requestCfg.callback = cfg.callback;
      }
      return this.sendRequest(requestCfg);
    },
    retrieveActivityProfile: function(key, cfg) {
      this.log("retrieveActivityProfile");
      var requestCfg = {},
        requestResult, callbackWrapper;
      requestCfg = {
        url: "activities/profile",
        method: "GET",
        params: {
          profileId: key,
          activityId: cfg.activity.id
        },
        ignore404: true
      };
      if (typeof cfg.callback !== "undefined") {
        callbackWrapper = function(err, xhr) {
          var result = xhr;
          if (err === null) {
            if (xhr.status === 404) {
              result = null;
            } else {
              result = new TinCan.ActivityProfile({
                id: key,
                activity: cfg.activity,
                contents: xhr.responseText
              });
              if (typeof xhr.getResponseHeader !== "undefined" && xhr.getResponseHeader("ETag") !== null && xhr.getResponseHeader("ETag") !== "") {
                result.etag = xhr.getResponseHeader("ETag");
              } else {
                result.etag = TinCan.Utils.getSHA1String(xhr.responseText);
              }
              if (typeof xhr.contentType !== "undefined") {
                result.contentType = xhr.contentType;
              } else if (typeof xhr.getResponseHeader !== "undefined" && xhr.getResponseHeader("Content-Type") !== null && xhr.getResponseHeader("Content-Type") !== "") {
                result.contentType = xhr.getResponseHeader("Content-Type");
              }
              if (TinCan.Utils.isApplicationJSON(result.contentType)) {
                try {
                  result.contents = JSON.parse(result.contents);
                } catch (ex) {
                  this.log("retrieveActivityProfile - failed to deserialize JSON: " + ex);
                }
              }
            }
          }
          cfg.callback(err, result);
        };
        requestCfg.callback = callbackWrapper;
      }
      requestResult = this.sendRequest(requestCfg);
      if (!callbackWrapper) {
        requestResult.profile = null;
        if (requestResult.err === null && requestResult.xhr.status !== 404) {
          requestResult.profile = new TinCan.ActivityProfile({
            id: key,
            activity: cfg.activity,
            contents: requestResult.xhr.responseText
          });
          if (typeof requestResult.xhr.getResponseHeader !== "undefined" && requestResult.xhr.getResponseHeader("ETag") !== null && requestResult.xhr.getResponseHeader("ETag") !== "") {
            requestResult.profile.etag = requestResult.xhr.getResponseHeader("ETag");
          } else {
            requestResult.profile.etag = TinCan.Utils.getSHA1String(requestResult.xhr.responseText);
          }
          if (typeof requestResult.xhr.contentType !== "undefined") {
            requestResult.profile.contentType = requestResult.xhr.contentType;
          } else if (typeof requestResult.xhr.getResponseHeader !== "undefined" && requestResult.xhr.getResponseHeader("Content-Type") !== null && requestResult.xhr.getResponseHeader("Content-Type") !== "") {
            requestResult.profile.contentType = requestResult.xhr.getResponseHeader("Content-Type");
          }
          if (TinCan.Utils.isApplicationJSON(requestResult.profile.contentType)) {
            try {
              requestResult.profile.contents = JSON.parse(requestResult.profile.contents);
            } catch (ex) {
              this.log("retrieveActivityProfile - failed to deserialize JSON: " + ex);
            }
          }
        }
      }
      return requestResult;
    },
    saveActivityProfile: function(key, val, cfg) {
      this.log("saveActivityProfile");
      var requestCfg;
      if (typeof cfg.contentType === "undefined") {
        cfg.contentType = "application/octet-stream";
      }
      if (typeof val === "object" && TinCan.Utils.isApplicationJSON(cfg.contentType)) {
        val = JSON.stringify(val);
      }
      requestCfg = {
        url: "activities/profile",
        method: "PUT",
        params: {
          profileId: key,
          activityId: cfg.activity.id
        },
        data: val,
        headers: {
          "Content-Type": cfg.contentType
        }
      };
      if (typeof cfg.callback !== "undefined") {
        requestCfg.callback = cfg.callback;
      }
      if (typeof cfg.lastSHA1 !== "undefined" && cfg.lastSHA1 !== null) {
        requestCfg.headers["If-Match"] = cfg.lastSHA1;
      } else {
        requestCfg.headers["If-None-Match"] = "*";
      }
      return this.sendRequest(requestCfg);
    },
    dropActivityProfile: function(key, cfg) {
      this.log("dropActivityProfile");
      var requestParams, requestCfg;
      requestParams = {
        profileId: key,
        activityId: cfg.activity.id
      };
      requestCfg = {
        url: "activities/profile",
        method: "DELETE",
        params: requestParams
      };
      if (typeof cfg.callback !== "undefined") {
        requestCfg.callback = cfg.callback;
      }
      return this.sendRequest(requestCfg);
    },
    retrieveAgentProfile: function(key, cfg) {
      this.log("retrieveAgentProfile");
      var requestCfg = {},
        requestResult, callbackWrapper;
      requestCfg = {
        method: "GET",
        params: {
          profileId: key
        },
        ignore404: true
      };
      if (this.version === "0.9") {
        requestCfg.url = "actors/profile";
        requestCfg.params.actor = JSON.stringify(cfg.agent.asVersion(this.version));
      } else {
        requestCfg.url = "agents/profile";
        requestCfg.params.agent = JSON.stringify(cfg.agent.asVersion(this.version));
      }
      if (typeof cfg.callback !== "undefined") {
        callbackWrapper = function(err, xhr) {
          var result = xhr;
          if (err === null) {
            if (xhr.status === 404) {
              result = null;
            } else {
              result = new TinCan.AgentProfile({
                id: key,
                agent: cfg.agent,
                contents: xhr.responseText
              });
              if (typeof xhr.getResponseHeader !== "undefined" && xhr.getResponseHeader("ETag") !== null && xhr.getResponseHeader("ETag") !== "") {
                result.etag = xhr.getResponseHeader("ETag");
              } else {
                result.etag = TinCan.Utils.getSHA1String(xhr.responseText);
              }
              if (typeof xhr.contentType !== "undefined") {
                result.contentType = xhr.contentType;
              } else if (typeof xhr.getResponseHeader !== "undefined" && xhr.getResponseHeader("Content-Type") !== null && xhr.getResponseHeader("Content-Type") !== "") {
                result.contentType = xhr.getResponseHeader("Content-Type");
              }
              if (TinCan.Utils.isApplicationJSON(result.contentType)) {
                try {
                  result.contents = JSON.parse(result.contents);
                } catch (ex) {
                  this.log("retrieveAgentProfile - failed to deserialize JSON: " + ex);
                }
              }
            }
          }
          cfg.callback(err, result);
        };
        requestCfg.callback = callbackWrapper;
      }
      requestResult = this.sendRequest(requestCfg);
      if (!callbackWrapper) {
        requestResult.profile = null;
        if (requestResult.err === null && requestResult.xhr.status !== 404) {
          requestResult.profile = new TinCan.AgentProfile({
            id: key,
            agent: cfg.agent,
            contents: requestResult.xhr.responseText
          });
          if (typeof requestResult.xhr.getResponseHeader !== "undefined" && requestResult.xhr.getResponseHeader("ETag") !== null && requestResult.xhr.getResponseHeader("ETag") !== "") {
            requestResult.profile.etag = requestResult.xhr.getResponseHeader("ETag");
          } else {
            requestResult.profile.etag = TinCan.Utils.getSHA1String(requestResult.xhr.responseText);
          }
          if (typeof requestResult.xhr.contentType !== "undefined") {
            requestResult.profile.contentType = requestResult.xhr.contentType;
          } else if (typeof requestResult.xhr.getResponseHeader !== "undefined" && requestResult.xhr.getResponseHeader("Content-Type") !== null && requestResult.xhr.getResponseHeader("Content-Type") !== "") {
            requestResult.profile.contentType = requestResult.xhr.getResponseHeader("Content-Type");
          }
          if (TinCan.Utils.isApplicationJSON(requestResult.profile.contentType)) {
            try {
              requestResult.profile.contents = JSON.parse(requestResult.profile.contents);
            } catch (ex) {
              this.log("retrieveAgentProfile - failed to deserialize JSON: " + ex);
            }
          }
        }
      }
      return requestResult;
    },
    saveAgentProfile: function(key, val, cfg) {
      this.log("saveAgentProfile");
      var requestCfg;
      if (typeof cfg.contentType === "undefined") {
        cfg.contentType = "application/octet-stream";
      }
      if (typeof val === "object" && TinCan.Utils.isApplicationJSON(cfg.contentType)) {
        val = JSON.stringify(val);
      }
      requestCfg = {
        method: "PUT",
        params: {
          profileId: key
        },
        data: val,
        headers: {
          "Content-Type": cfg.contentType
        }
      };
      if (this.version === "0.9") {
        requestCfg.url = "actors/profile";
        requestCfg.params.actor = JSON.stringify(cfg.agent.asVersion(this.version));
      } else {
        requestCfg.url = "agents/profile";
        requestCfg.params.agent = JSON.stringify(cfg.agent.asVersion(this.version));
      }
      if (typeof cfg.callback !== "undefined") {
        requestCfg.callback = cfg.callback;
      }
      if (typeof cfg.lastSHA1 !== "undefined" && cfg.lastSHA1 !== null) {
        requestCfg.headers["If-Match"] = cfg.lastSHA1;
      } else {
        requestCfg.headers["If-None-Match"] = "*";
      }
      return this.sendRequest(requestCfg);
    },
    dropAgentProfile: function(key, cfg) {
      this.log("dropAgentProfile");
      var requestParams, requestCfg;
      requestParams = {
        profileId: key
      };
      requestCfg = {
        method: "DELETE",
        params: requestParams
      };
      if (this.version === "0.9") {
        requestCfg.url = "actors/profile";
        requestParams.actor = JSON.stringify(cfg.agent.asVersion(this.version));
      } else {
        requestCfg.url = "agents/profile";
        requestParams.agent = JSON.stringify(cfg.agent.asVersion(this.version));
      }
      if (typeof cfg.callback !== "undefined") {
        requestCfg.callback = cfg.callback;
      }
      return this.sendRequest(requestCfg);
    }
  };
  LRS.syncEnabled = null;
}());
(function() {
  "use strict";
  var AgentAccount = TinCan.AgentAccount = function(cfg) {
    this.log("constructor");
    this.homePage = null;
    this.name = null;
    this.init(cfg);
  };
  AgentAccount.prototype = {
    LOG_SRC: "AgentAccount",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["name", "homePage"];
      cfg = cfg || {};
      if (typeof cfg.accountServiceHomePage !== "undefined") {
        cfg.homePage = cfg.accountServiceHomePage;
      }
      if (typeof cfg.accountName !== "undefined") {
        cfg.name = cfg.accountName;
      }
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
    },
    toString: function() {
      this.log("toString");
      var result = "";
      if (this.name !== null || this.homePage !== null) {
        result += this.name !== null ? this.name : "-";
        result += ":";
        result += this.homePage !== null ? this.homePage : "-";
      } else {
        result = "AgentAccount: unidentified";
      }
      return result;
    },
    asVersion: function(version) {
      this.log("asVersion: " + version);
      var result = {};
      version = version || TinCan.versions()[0];
      if (version === "0.9") {
        result.accountName = this.name;
        result.accountServiceHomePage = this.homePage;
      } else {
        result.name = this.name;
        result.homePage = this.homePage;
      }
      return result;
    }
  };
  AgentAccount.fromJSON = function(acctJSON) {
    AgentAccount.prototype.log("fromJSON");
    var _acct = JSON.parse(acctJSON);
    return new AgentAccount(_acct);
  };
}());
(function() {
  "use strict";
  var Agent = TinCan.Agent = function(cfg) {
    this.log("constructor");
    this.name = null;
    this.mbox = null;
    this.mbox_sha1sum = null;
    this.openid = null;
    this.account = null;
    this.degraded = false;
    this.init(cfg);
  };
  Agent.prototype = {
    objectType: "Agent",
    LOG_SRC: "Agent",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["name", "mbox", "mbox_sha1sum", "openid"],
        val;
      cfg = cfg || {};
      if (typeof cfg.lastName !== "undefined" || typeof cfg.firstName !== "undefined") {
        cfg.name = "";
        if (typeof cfg.firstName !== "undefined" && cfg.firstName.length > 0) {
          cfg.name = cfg.firstName[0];
          if (cfg.firstName.length > 1) {
            this.degraded = true;
          }
        }
        if (cfg.name !== "") {
          cfg.name += " ";
        }
        if (typeof cfg.lastName !== "undefined" && cfg.lastName.length > 0) {
          cfg.name += cfg.lastName[0];
          if (cfg.lastName.length > 1) {
            this.degraded = true;
          }
        }
      } else if (typeof cfg.familyName !== "undefined" || typeof cfg.givenName !== "undefined") {
        cfg.name = "";
        if (typeof cfg.givenName !== "undefined" && cfg.givenName.length > 0) {
          cfg.name = cfg.givenName[0];
          if (cfg.givenName.length > 1) {
            this.degraded = true;
          }
        }
        if (cfg.name !== "") {
          cfg.name += " ";
        }
        if (typeof cfg.familyName !== "undefined" && cfg.familyName.length > 0) {
          cfg.name += cfg.familyName[0];
          if (cfg.familyName.length > 1) {
            this.degraded = true;
          }
        }
      }
      if (typeof cfg.name === "object" && cfg.name !== null) {
        if (cfg.name.length > 1) {
          this.degraded = true;
        }
        cfg.name = cfg.name[0];
      }
      if (typeof cfg.mbox === "object" && cfg.mbox !== null) {
        if (cfg.mbox.length > 1) {
          this.degraded = true;
        }
        cfg.mbox = cfg.mbox[0];
      }
      if (typeof cfg.mbox_sha1sum === "object" && cfg.mbox_sha1sum !== null) {
        if (cfg.mbox_sha1sum.length > 1) {
          this.degraded = true;
        }
        cfg.mbox_sha1sum = cfg.mbox_sha1sum[0];
      }
      if (typeof cfg.openid === "object" && cfg.openid !== null) {
        if (cfg.openid.length > 1) {
          this.degraded = true;
        }
        cfg.openid = cfg.openid[0];
      }
      if (typeof cfg.account === "object" && cfg.account !== null && typeof cfg.account.homePage === "undefined" && typeof cfg.account.name === "undefined") {
        if (cfg.account.length === 0) {
          delete cfg.account;
        } else {
          if (cfg.account.length > 1) {
            this.degraded = true;
          }
          cfg.account = cfg.account[0];
        }
      }
      if (cfg.hasOwnProperty("account")) {
        if (cfg.account instanceof TinCan.AgentAccount) {
          this.account = cfg.account;
        } else {
          this.account = new TinCan.AgentAccount(cfg.account);
        }
      }
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          val = cfg[directProps[i]];
          if (directProps[i] === "mbox" && val.indexOf("mailto:") === -1) {
            val = "mailto:" + val;
          }
          this[directProps[i]] = val;
        }
      }
    },
    toString: function() {
      this.log("toString");
      if (this.name !== null) {
        return this.name;
      }
      if (this.mbox !== null) {
        return this.mbox.replace("mailto:", "");
      }
      if (this.mbox_sha1sum !== null) {
        return this.mbox_sha1sum;
      }
      if (this.openid !== null) {
        return this.openid;
      }
      if (this.account !== null) {
        return this.account.toString();
      }
      return this.objectType + ": unidentified";
    },
    asVersion: function(version) {
      this.log("asVersion: " + version);
      var result = {
        objectType: this.objectType
      };
      version = version || TinCan.versions()[0];
      if (version === "0.9") {
        if (this.mbox !== null) {
          result.mbox = [this.mbox];
        } else if (this.mbox_sha1sum !== null) {
          result.mbox_sha1sum = [this.mbox_sha1sum];
        } else if (this.openid !== null) {
          result.openid = [this.openid];
        } else if (this.account !== null) {
          result.account = [this.account.asVersion(version)];
        }
        if (this.name !== null) {
          result.name = [this.name];
        }
      } else {
        if (this.mbox !== null) {
          result.mbox = this.mbox;
        } else if (this.mbox_sha1sum !== null) {
          result.mbox_sha1sum = this.mbox_sha1sum;
        } else if (this.openid !== null) {
          result.openid = this.openid;
        } else if (this.account !== null) {
          result.account = this.account.asVersion(version);
        }
        if (this.name !== null) {
          result.name = this.name;
        }
      }
      return result;
    }
  };
  Agent.fromJSON = function(agentJSON) {
    Agent.prototype.log("fromJSON");
    var _agent = JSON.parse(agentJSON);
    return new Agent(_agent);
  };
}());
(function() {
  "use strict";
  var Group = TinCan.Group = function(cfg) {
    this.log("constructor");
    this.name = null;
    this.mbox = null;
    this.mbox_sha1sum = null;
    this.openid = null;
    this.account = null;
    this.member = [];
    this.init(cfg);
  };
  Group.prototype = {
    objectType: "Group",
    LOG_SRC: "Group",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i;
      cfg = cfg || {};
      TinCan.Agent.prototype.init.call(this, cfg);
      if (typeof cfg.member !== "undefined") {
        for (i = 0; i < cfg.member.length; i += 1) {
          if (cfg.member[i] instanceof TinCan.Agent) {
            this.member.push(cfg.member[i]);
          } else {
            this.member.push(new TinCan.Agent(cfg.member[i]));
          }
        }
      }
    },
    toString: function(lang) {
      this.log("toString");
      var result = TinCan.Agent.prototype.toString.call(this, lang);
      if (result !== this.objectType + ": unidentified") {
        result = this.objectType + ": " + result;
      }
      return result;
    },
    asVersion: function(version) {
      this.log("asVersion: " + version);
      var result, i;
      version = version || TinCan.versions()[0];
      result = TinCan.Agent.prototype.asVersion.call(this, version);
      if (this.member.length > 0) {
        result.member = [];
        for (i = 0; i < this.member.length; i += 1) {
          result.member.push(this.member[i].asVersion(version));
        }
      }
      return result;
    }
  };
  Group.fromJSON = function(groupJSON) {
    Group.prototype.log("fromJSON");
    var _group = JSON.parse(groupJSON);
    return new Group(_group);
  };
}());
(function() {
  "use strict";
  var _downConvertMap = {
      "http://adlnet.gov/expapi/verbs/experienced": "experienced",
      "http://adlnet.gov/expapi/verbs/attended": "attended",
      "http://adlnet.gov/expapi/verbs/attempted": "attempted",
      "http://adlnet.gov/expapi/verbs/completed": "completed",
      "http://adlnet.gov/expapi/verbs/passed": "passed",
      "http://adlnet.gov/expapi/verbs/failed": "failed",
      "http://adlnet.gov/expapi/verbs/answered": "answered",
      "http://adlnet.gov/expapi/verbs/interacted": "interacted",
      "http://adlnet.gov/expapi/verbs/imported": "imported",
      "http://adlnet.gov/expapi/verbs/created": "created",
      "http://adlnet.gov/expapi/verbs/shared": "shared",
      "http://adlnet.gov/expapi/verbs/voided": "voided"
    },
    Verb = TinCan.Verb = function(cfg) {
      this.log("constructor");
      this.id = null;
      this.display = null;
      this.init(cfg);
    };
  Verb.prototype = {
    LOG_SRC: "Verb",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["id", "display"],
        prop;
      if (typeof cfg === "string") {
        this.id = cfg;
        this.display = {
          und: this.id
        };
        for (prop in _downConvertMap) {
          if (_downConvertMap.hasOwnProperty(prop) && _downConvertMap[prop] === cfg) {
            this.id = prop;
            break;
          }
        }
      } else {
        cfg = cfg || {};
        for (i = 0; i < directProps.length; i += 1) {
          if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
            this[directProps[i]] = cfg[directProps[i]];
          }
        }
        if (this.display === null && typeof _downConvertMap[this.id] !== "undefined") {
          this.display = {
            "und": _downConvertMap[this.id]
          };
        }
      }
    },
    toString: function(lang) {
      this.log("toString");
      if (this.display !== null) {
        return this.getLangDictionaryValue("display", lang);
      }
      return this.id;
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result;
      version = version || TinCan.versions()[0];
      if (version === "0.9") {
        result = _downConvertMap[this.id];
      } else {
        result = {
          id: this.id
        };
        if (this.display !== null) {
          result.display = this.display;
        }
      }
      return result;
    },
    getLangDictionaryValue: TinCan.Utils.getLangDictionaryValue
  };
  Verb.fromJSON = function(verbJSON) {
    Verb.prototype.log("fromJSON");
    var _verb = JSON.parse(verbJSON);
    return new Verb(_verb);
  };
}());
(function() {
  "use strict";
  var Result = TinCan.Result = function(cfg) {
    this.log("constructor");
    this.score = null;
    this.success = null;
    this.completion = null;
    this.duration = null;
    this.response = null;
    this.extensions = null;
    this.init(cfg);
  };
  Result.prototype = {
    LOG_SRC: "Result",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["completion", "duration", "extensions", "response", "success"];
      cfg = cfg || {};
      if (cfg.hasOwnProperty("score") && cfg.score !== null) {
        if (cfg.score instanceof TinCan.Score) {
          this.score = cfg.score;
        } else {
          this.score = new TinCan.Score(cfg.score);
        }
      }
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
      if (this.completion === "Completed") {
        this.completion = true;
      }
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result = {},
        optionalDirectProps = ["success", "duration", "response", "extensions"],
        optionalObjProps = ["score"],
        i;
      version = version || TinCan.versions()[0];
      for (i = 0; i < optionalDirectProps.length; i += 1) {
        if (this[optionalDirectProps[i]] !== null) {
          result[optionalDirectProps[i]] = this[optionalDirectProps[i]];
        }
      }
      for (i = 0; i < optionalObjProps.length; i += 1) {
        if (this[optionalObjProps[i]] !== null) {
          result[optionalObjProps[i]] = this[optionalObjProps[i]].asVersion(version);
        }
      }
      if (this.completion !== null) {
        if (version === "0.9") {
          if (this.completion) {
            result.completion = "Completed";
          }
        } else {
          result.completion = this.completion;
        }
      }
      return result;
    }
  };
  Result.fromJSON = function(resultJSON) {
    Result.prototype.log("fromJSON");
    var _result = JSON.parse(resultJSON);
    return new Result(_result);
  };
}());
(function() {
  "use strict";
  var Score = TinCan.Score = function(cfg) {
    this.log("constructor");
    this.scaled = null;
    this.raw = null;
    this.min = null;
    this.max = null;
    this.init(cfg);
  };
  Score.prototype = {
    LOG_SRC: "Score",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["scaled", "raw", "min", "max"];
      cfg = cfg || {};
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result = {},
        optionalDirectProps = ["scaled", "raw", "min", "max"],
        i;
      version = version || TinCan.versions()[0];
      for (i = 0; i < optionalDirectProps.length; i += 1) {
        if (this[optionalDirectProps[i]] !== null) {
          result[optionalDirectProps[i]] = this[optionalDirectProps[i]];
        }
      }
      return result;
    }
  };
  Score.fromJSON = function(scoreJSON) {
    Score.prototype.log("fromJSON");
    var _score = JSON.parse(scoreJSON);
    return new Score(_score);
  };
}());
(function() {
  "use strict";
  var InteractionComponent = TinCan.InteractionComponent = function(cfg) {
    this.log("constructor");
    this.id = null;
    this.description = null;
    this.init(cfg);
  };
  InteractionComponent.prototype = {
    LOG_SRC: "InteractionComponent",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["id", "description"];
      cfg = cfg || {};
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result = {
          id: this.id
        },
        optionalDirectProps = ["description"],
        i, prop;
      version = version || TinCan.versions()[0];
      for (i = 0; i < optionalDirectProps.length; i += 1) {
        prop = optionalDirectProps[i];
        if (this[prop] !== null) {
          result[prop] = this[prop];
        }
      }
      return result;
    },
    getLangDictionaryValue: TinCan.Utils.getLangDictionaryValue
  };
  InteractionComponent.fromJSON = function(icJSON) {
    InteractionComponent.prototype.log("fromJSON");
    var _ic = JSON.parse(icJSON);
    return new InteractionComponent(_ic);
  };
}());
(function() {
  "use strict";
  var _downConvertMap = {
      "http://adlnet.gov/expapi/activities/course": "course",
      "http://adlnet.gov/expapi/activities/module": "module",
      "http://adlnet.gov/expapi/activities/meeting": "meeting",
      "http://adlnet.gov/expapi/activities/media": "media",
      "http://adlnet.gov/expapi/activities/performance": "performance",
      "http://adlnet.gov/expapi/activities/simulation": "simulation",
      "http://adlnet.gov/expapi/activities/assessment": "assessment",
      "http://adlnet.gov/expapi/activities/interaction": "interaction",
      "http://adlnet.gov/expapi/activities/cmi.interaction": "cmi.interaction",
      "http://adlnet.gov/expapi/activities/question": "question",
      "http://adlnet.gov/expapi/activities/objective": "objective",
      "http://adlnet.gov/expapi/activities/link": "link"
    },
    ActivityDefinition = TinCan.ActivityDefinition = function(cfg) {
      this.log("constructor");
      this.name = null;
      this.description = null;
      this.type = null;
      this.moreInfo = null;
      this.extensions = null;
      this.interactionType = null;
      this.correctResponsesPattern = null;
      this.choices = null;
      this.scale = null;
      this.source = null;
      this.target = null;
      this.steps = null;
      this.init(cfg);
    };
  ActivityDefinition.prototype = {
    LOG_SRC: "ActivityDefinition",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, j, prop, directProps = ["name", "description", "moreInfo", "extensions", "correctResponsesPattern"],
        interactionComponentProps = [];
      cfg = cfg || {};
      if (cfg.hasOwnProperty("type") && cfg.type !== null) {
        for (prop in _downConvertMap) {
          if (_downConvertMap.hasOwnProperty(prop) && _downConvertMap[prop] === cfg.type) {
            cfg.type = _downConvertMap[prop];
          }
        }
        this.type = cfg.type;
      }
      if (cfg.hasOwnProperty("interactionType") && cfg.interactionType !== null) {
        this.interactionType = cfg.interactionType;
        if (cfg.interactionType === "choice" || cfg.interactionType === "sequencing") {
          interactionComponentProps.push("choices");
        } else if (cfg.interactionType === "likert") {
          interactionComponentProps.push("scale");
        } else if (cfg.interactionType === "matching") {
          interactionComponentProps.push("source");
          interactionComponentProps.push("target");
        } else if (cfg.interactionType === "performance") {
          interactionComponentProps.push("steps");
        }
        if (interactionComponentProps.length > 0) {
          for (i = 0; i < interactionComponentProps.length; i += 1) {
            prop = interactionComponentProps[i];
            if (cfg.hasOwnProperty(prop) && cfg[prop] !== null) {
              this[prop] = [];
              for (j = 0; j < cfg[prop].length; j += 1) {
                if (cfg[prop][j] instanceof TinCan.InteractionComponent) {
                  this[prop].push(cfg[prop][j]);
                } else {
                  this[prop].push(new TinCan.InteractionComponent(cfg[prop][j]));
                }
              }
            }
          }
        }
      }
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
    },
    toString: function(lang) {
      this.log("toString");
      if (this.name !== null) {
        return this.getLangDictionaryValue("name", lang);
      }
      if (this.description !== null) {
        return this.getLangDictionaryValue("description", lang);
      }
      return "";
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result = {},
        directProps = ["name", "description", "interactionType", "correctResponsesPattern", "extensions"],
        interactionComponentProps = ["choices", "scale", "source", "target", "steps"],
        i, j, prop;
      version = version || TinCan.versions()[0];
      if (this.type !== null) {
        if (version === "0.9") {
          result.type = _downConvertMap[this.type];
        } else {
          result.type = this.type;
        }
      }
      for (i = 0; i < directProps.length; i += 1) {
        prop = directProps[i];
        if (this[prop] !== null) {
          result[prop] = this[prop];
        }
      }
      for (i = 0; i < interactionComponentProps.length; i += 1) {
        prop = interactionComponentProps[i];
        if (this[prop] !== null) {
          result[prop] = [];
          for (j = 0; j < this[prop].length; j += 1) {
            result[prop].push(this[prop][j].asVersion(version));
          }
        }
      }
      if (version.indexOf("0.9") !== 0) {
        if (this.moreInfo !== null) {
          result.moreInfo = this.moreInfo;
        }
      }
      return result;
    },
    getLangDictionaryValue: TinCan.Utils.getLangDictionaryValue
  };
  ActivityDefinition.fromJSON = function(definitionJSON) {
    ActivityDefinition.prototype.log("fromJSON");
    var _definition = JSON.parse(definitionJSON);
    return new ActivityDefinition(_definition);
  };
}());
(function() {
  "use strict";
  var Activity = TinCan.Activity = function(cfg) {
    this.log("constructor");
    this.objectType = "Activity";
    this.id = null;
    this.definition = null;
    this.init(cfg);
  };
  Activity.prototype = {
    LOG_SRC: "Activity",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["id"];
      cfg = cfg || {};
      if (cfg.hasOwnProperty("definition")) {
        if (cfg.definition instanceof TinCan.ActivityDefinition) {
          this.definition = cfg.definition;
        } else {
          this.definition = new TinCan.ActivityDefinition(cfg.definition);
        }
      }
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
    },
    toString: function(lang) {
      this.log("toString");
      var defString = "";
      if (this.definition !== null) {
        defString = this.definition.toString(lang);
        if (defString !== "") {
          return defString;
        }
      }
      if (this.id !== null) {
        return this.id;
      }
      return "Activity: unidentified";
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result = {
        id: this.id,
        objectType: this.objectType
      };
      version = version || TinCan.versions()[0];
      if (this.definition !== null) {
        result.definition = this.definition.asVersion(version);
      }
      return result;
    }
  };
  Activity.fromJSON = function(activityJSON) {
    Activity.prototype.log("fromJSON");
    var _activity = JSON.parse(activityJSON);
    return new Activity(_activity);
  };
}());
(function() {
  "use strict";
  var ContextActivities = TinCan.ContextActivities = function(cfg) {
    this.log("constructor");
    this.category = null;
    this.parent = null;
    this.grouping = null;
    this.other = null;
    this.init(cfg);
  };
  ContextActivities.prototype = {
    LOG_SRC: "ContextActivities",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, j, objProps = ["category", "parent", "grouping", "other"],
        prop, val;
      cfg = cfg || {};
      for (i = 0; i < objProps.length; i += 1) {
        prop = objProps[i];
        if (cfg.hasOwnProperty(prop) && cfg[prop] !== null) {
          if (Object.prototype.toString.call(cfg[prop]) === "[object Array]") {
            for (j = 0; j < cfg[prop].length; j += 1) {
              this.add(prop, cfg[prop][j]);
            }
          } else {
            val = cfg[prop];
            this.add(prop, val);
          }
        }
      }
    },
    add: function(key, val) {
      if (key !== "category" && key !== "parent" && key !== "grouping" && key !== "other") {
        return;
      }
      if (this[key] === null) {
        this[key] = [];
      }
      if (!(val instanceof TinCan.Activity)) {
        val = typeof val === "string" ? {
          id: val
        } : val;
        val = new TinCan.Activity(val);
      }
      this[key].push(val);
      return this[key].length - 1;
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result = {},
        optionalObjProps = ["parent", "grouping", "other"],
        i, j;
      version = version || TinCan.versions()[0];
      for (i = 0; i < optionalObjProps.length; i += 1) {
        if (this[optionalObjProps[i]] !== null && this[optionalObjProps[i]].length > 0) {
          if (version === "0.9" || version === "0.95") {
            if (this[optionalObjProps[i]].length > 1) {
              this.log("[warning] version does not support multiple values in: " + optionalObjProps[i]);
            }
            result[optionalObjProps[i]] = this[optionalObjProps[i]][0].asVersion(version);
          } else {
            result[optionalObjProps[i]] = [];
            for (j = 0; j < this[optionalObjProps[i]].length; j += 1) {
              result[optionalObjProps[i]].push(this[optionalObjProps[i]][j].asVersion(version));
            }
          }
        }
      }
      if (this.category !== null && this.category.length > 0) {
        if (version === "0.9" || version === "0.95") {
          this.log("[error] version does not support the 'category' property: " + version);
          throw new Error(version + " does not support the 'category' property");
        } else {
          result.category = [];
          for (i = 0; i < this.category.length; i += 1) {
            result.category.push(this.category[i].asVersion(version));
          }
        }
      }
      return result;
    }
  };
  ContextActivities.fromJSON = function(contextActivitiesJSON) {
    ContextActivities.prototype.log("fromJSON");
    var _contextActivities = JSON.parse(contextActivitiesJSON);
    return new ContextActivities(_contextActivities);
  };
}());
(function() {
  "use strict";
  var Context = TinCan.Context = function(cfg) {
    this.log("constructor");
    this.registration = null;
    this.instructor = null;
    this.team = null;
    this.contextActivities = null;
    this.revision = null;
    this.platform = null;
    this.language = null;
    this.statement = null;
    this.extensions = null;
    this.init(cfg);
  };
  Context.prototype = {
    LOG_SRC: "Context",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["registration", "revision", "platform", "language", "statement", "extensions"],
        agentGroupProps = ["instructor", "team"],
        prop, val;
      cfg = cfg || {};
      for (i = 0; i < directProps.length; i += 1) {
        prop = directProps[i];
        if (cfg.hasOwnProperty(prop) && cfg[prop] !== null) {
          this[prop] = cfg[prop];
        }
      }
      for (i = 0; i < agentGroupProps.length; i += 1) {
        prop = agentGroupProps[i];
        if (cfg.hasOwnProperty(prop) && cfg[prop] !== null) {
          val = cfg[prop];
          if (typeof val.objectType === "undefined" || val.objectType === "Person") {
            val.objectType = "Agent";
          }
          if (val.objectType === "Agent" && !(val instanceof TinCan.Agent)) {
            val = new TinCan.Agent(val);
          } else if (val.objectType === "Group" && !(val instanceof TinCan.Group)) {
            val = new TinCan.Group(val);
          }
          this[prop] = val;
        }
      }
      if (cfg.hasOwnProperty("contextActivities") && cfg.contextActivities !== null) {
        if (cfg.contextActivities instanceof TinCan.ContextActivities) {
          this.contextActivities = cfg.contextActivities;
        } else {
          this.contextActivities = new TinCan.ContextActivities(cfg.contextActivities);
        }
      }
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result = {},
        optionalDirectProps = ["registration", "revision", "platform", "language", "extensions"],
        optionalObjProps = ["instructor", "team", "contextActivities", "statement"],
        i;
      version = version || TinCan.versions()[0];
      for (i = 0; i < optionalDirectProps.length; i += 1) {
        if (this[optionalDirectProps[i]] !== null) {
          result[optionalDirectProps[i]] = this[optionalDirectProps[i]];
        }
      }
      for (i = 0; i < optionalObjProps.length; i += 1) {
        if (this[optionalObjProps[i]] !== null) {
          result[optionalObjProps[i]] = this[optionalObjProps[i]].asVersion(version);
        }
      }
      return result;
    }
  };
  Context.fromJSON = function(contextJSON) {
    Context.prototype.log("fromJSON");
    var _context = JSON.parse(contextJSON);
    return new Context(_context);
  };
}());
(function() {
  "use strict";
  var StatementRef = TinCan.StatementRef = function(cfg) {
    this.log("constructor");
    this.id = null;
    this.init(cfg);
  };
  StatementRef.prototype = {
    objectType: "StatementRef",
    LOG_SRC: "StatementRef",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["id"];
      cfg = cfg || {};
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
    },
    toString: function() {
      this.log("toString");
      return this.id;
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result = {
        objectType: this.objectType,
        id: this.id
      };
      if (version === "0.9") {
        result.objectType = "Statement";
      }
      return result;
    }
  };
  StatementRef.fromJSON = function(stRefJSON) {
    StatementRef.prototype.log("fromJSON");
    var _stRef = JSON.parse(stRefJSON);
    return new StatementRef(_stRef);
  };
}());
(function() {
  "use strict";
  var SubStatement = TinCan.SubStatement = function(cfg) {
    this.log("constructor");
    this.actor = null;
    this.verb = null;
    this.target = null;
    this.result = null;
    this.context = null;
    this.timestamp = null;
    this.init(cfg);
  };
  SubStatement.prototype = {
    objectType: "SubStatement",
    LOG_SRC: "SubStatement",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["timestamp"];
      cfg = cfg || {};
      if (cfg.hasOwnProperty("object")) {
        cfg.target = cfg.object;
      }
      if (cfg.hasOwnProperty("actor")) {
        if (typeof cfg.actor.objectType === "undefined" || cfg.actor.objectType === "Person") {
          cfg.actor.objectType = "Agent";
        }
        if (cfg.actor.objectType === "Agent") {
          if (cfg.actor instanceof TinCan.Agent) {
            this.actor = cfg.actor;
          } else {
            this.actor = new TinCan.Agent(cfg.actor);
          }
        } else if (cfg.actor.objectType === "Group") {
          if (cfg.actor instanceof TinCan.Group) {
            this.actor = cfg.actor;
          } else {
            this.actor = new TinCan.Group(cfg.actor);
          }
        }
      }
      if (cfg.hasOwnProperty("verb")) {
        if (cfg.verb instanceof TinCan.Verb) {
          this.verb = cfg.verb;
        } else {
          this.verb = new TinCan.Verb(cfg.verb);
        }
      }
      if (cfg.hasOwnProperty("target")) {
        if (cfg.target instanceof TinCan.Activity || cfg.target instanceof TinCan.Agent || cfg.target instanceof TinCan.Group || cfg.target instanceof TinCan.SubStatement || cfg.target instanceof TinCan.StatementRef) {
          this.target = cfg.target;
        } else {
          if (typeof cfg.target.objectType === "undefined") {
            cfg.target.objectType = "Activity";
          }
          if (cfg.target.objectType === "Activity") {
            this.target = new TinCan.Activity(cfg.target);
          } else if (cfg.target.objectType === "Agent") {
            this.target = new TinCan.Agent(cfg.target);
          } else if (cfg.target.objectType === "Group") {
            this.target = new TinCan.Group(cfg.target);
          } else if (cfg.target.objectType === "SubStatement") {
            this.target = new TinCan.SubStatement(cfg.target);
          } else if (cfg.target.objectType === "StatementRef") {
            this.target = new TinCan.StatementRef(cfg.target);
          } else {
            this.log("Unrecognized target type: " + cfg.target.objectType);
          }
        }
      }
      if (cfg.hasOwnProperty("result")) {
        if (cfg.result instanceof TinCan.Result) {
          this.result = cfg.result;
        } else {
          this.result = new TinCan.Result(cfg.result);
        }
      }
      if (cfg.hasOwnProperty("context")) {
        if (cfg.context instanceof TinCan.Context) {
          this.context = cfg.context;
        } else {
          this.context = new TinCan.Context(cfg.context);
        }
      }
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
    },
    toString: function(lang) {
      this.log("toString");
      return (this.actor !== null ? this.actor.toString(lang) : "") + " " +
        (this.verb !== null ? this.verb.toString(lang) : "") + " " +
        (this.target !== null ? this.target.toString(lang) : "");
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result, optionalDirectProps = ["timestamp"],
        optionalObjProps = ["actor", "verb", "result", "context"],
        i;
      result = {
        objectType: this.objectType
      };
      version = version || TinCan.versions()[0];
      for (i = 0; i < optionalDirectProps.length; i += 1) {
        if (this[optionalDirectProps[i]] !== null) {
          result[optionalDirectProps[i]] = this[optionalDirectProps[i]];
        }
      }
      for (i = 0; i < optionalObjProps.length; i += 1) {
        if (this[optionalObjProps[i]] !== null) {
          result[optionalObjProps[i]] = this[optionalObjProps[i]].asVersion(version);
        }
      }
      if (this.target !== null) {
        result.object = this.target.asVersion(version);
      }
      return result;
    }
  };
  SubStatement.fromJSON = function(subStJSON) {
    SubStatement.prototype.log("fromJSON");
    var _subSt = JSON.parse(subStJSON);
    return new SubStatement(_subSt);
  };
}());
(function() {
  "use strict";
  var Statement = TinCan.Statement = function(cfg, initCfg) {
    this.log("constructor");
    if (typeof initCfg === "number") {
      initCfg = {
        storeOriginal: initCfg
      };
    } else {
      initCfg = initCfg || {};
    }
    if (typeof initCfg.storeOriginal === "undefined") {
      initCfg.storeOriginal = null;
    }
    if (typeof initCfg.doStamp === "undefined") {
      initCfg.doStamp = true;
    }
    this.id = null;
    this.actor = null;
    this.verb = null;
    this.target = null;
    this.result = null;
    this.context = null;
    this.timestamp = null;
    this.stored = null;
    this.authority = null;
    this.version = null;
    this.degraded = false;
    this.voided = null;
    this.inProgress = null;
    this.originalJSON = null;
    this.init(cfg, initCfg);
  };
  Statement.prototype = {
    LOG_SRC: "Statement",
    log: TinCan.prototype.log,
    init: function(cfg, initCfg) {
      this.log("init");
      var i, directProps = ["id", "stored", "timestamp", "version", "inProgress", "voided"];
      cfg = cfg || {};
      if (initCfg.storeOriginal) {
        this.originalJSON = JSON.stringify(cfg, null, initCfg.storeOriginal);
      }
      if (cfg.hasOwnProperty("object")) {
        cfg.target = cfg.object;
      }
      if (cfg.hasOwnProperty("actor")) {
        if (typeof cfg.actor.objectType === "undefined" || cfg.actor.objectType === "Person") {
          cfg.actor.objectType = "Agent";
        }
        if (cfg.actor.objectType === "Agent") {
          if (cfg.actor instanceof TinCan.Agent) {
            this.actor = cfg.actor;
          } else {
            this.actor = new TinCan.Agent(cfg.actor);
          }
        } else if (cfg.actor.objectType === "Group") {
          if (cfg.actor instanceof TinCan.Group) {
            this.actor = cfg.actor;
          } else {
            this.actor = new TinCan.Group(cfg.actor);
          }
        }
      }
      if (cfg.hasOwnProperty("authority")) {
        if (typeof cfg.authority.objectType === "undefined" || cfg.authority.objectType === "Person") {
          cfg.authority.objectType = "Agent";
        }
        if (cfg.authority.objectType === "Agent") {
          if (cfg.authority instanceof TinCan.Agent) {
            this.authority = cfg.authority;
          } else {
            this.authority = new TinCan.Agent(cfg.authority);
          }
        } else if (cfg.authority.objectType === "Group") {
          if (cfg.actor instanceof TinCan.Group) {
            this.authority = cfg.authority;
          } else {
            this.authority = new TinCan.Group(cfg.authority);
          }
        }
      }
      if (cfg.hasOwnProperty("verb")) {
        if (cfg.verb instanceof TinCan.Verb) {
          this.verb = cfg.verb;
        } else {
          this.verb = new TinCan.Verb(cfg.verb);
        }
      }
      if (cfg.hasOwnProperty("target")) {
        if (cfg.target instanceof TinCan.Activity || cfg.target instanceof TinCan.Agent || cfg.target instanceof TinCan.Group || cfg.target instanceof TinCan.SubStatement || cfg.target instanceof TinCan.StatementRef) {
          this.target = cfg.target;
        } else {
          if (typeof cfg.target.objectType === "undefined") {
            cfg.target.objectType = "Activity";
          }
          if (cfg.target.objectType === "Activity") {
            this.target = new TinCan.Activity(cfg.target);
          } else if (cfg.target.objectType === "Agent") {
            this.target = new TinCan.Agent(cfg.target);
          } else if (cfg.target.objectType === "Group") {
            this.target = new TinCan.Group(cfg.target);
          } else if (cfg.target.objectType === "SubStatement") {
            this.target = new TinCan.SubStatement(cfg.target);
          } else if (cfg.target.objectType === "StatementRef") {
            this.target = new TinCan.StatementRef(cfg.target);
          } else {
            this.log("Unrecognized target type: " + cfg.target.objectType);
          }
        }
      }
      if (cfg.hasOwnProperty("result")) {
        if (cfg.result instanceof TinCan.Result) {
          this.result = cfg.result;
        } else {
          this.result = new TinCan.Result(cfg.result);
        }
      }
      if (cfg.hasOwnProperty("context")) {
        if (cfg.context instanceof TinCan.Context) {
          this.context = cfg.context;
        } else {
          this.context = new TinCan.Context(cfg.context);
        }
      }
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
      if (initCfg.doStamp) {
        this.stamp();
      }
    },
    toString: function(lang) {
      this.log("toString");
      return (this.actor !== null ? this.actor.toString(lang) : "") + " " +
        (this.verb !== null ? this.verb.toString(lang) : "") + " " +
        (this.target !== null ? this.target.toString(lang) : "");
    },
    asVersion: function(version) {
      this.log("asVersion");
      var result = {},
        optionalDirectProps = ["id", "timestamp"],
        optionalObjProps = ["actor", "verb", "result", "context", "authority"],
        i;
      version = version || TinCan.versions()[0];
      for (i = 0; i < optionalDirectProps.length; i += 1) {
        if (this[optionalDirectProps[i]] !== null) {
          result[optionalDirectProps[i]] = this[optionalDirectProps[i]];
        }
      }
      for (i = 0; i < optionalObjProps.length; i += 1) {
        if (this[optionalObjProps[i]] !== null) {
          result[optionalObjProps[i]] = this[optionalObjProps[i]].asVersion(version);
        }
      }
      if (this.target !== null) {
        result.object = this.target.asVersion(version);
      }
      if (version === "0.9" || version === "0.95") {
        if (this.voided !== null) {
          result.voided = this.voided;
        }
      }
      if (version === "0.9" && this.inProgress !== null) {
        result.inProgress = this.inProgress;
      }
      return result;
    },
    stamp: function() {
      this.log("stamp");
      if (this.id === null) {
        this.id = TinCan.Utils.getUUID();
      }
      if (this.timestamp === null) {
        this.timestamp = TinCan.Utils.getISODateString(new Date());
      }
    }
  };
  Statement.fromJSON = function(stJSON) {
    Statement.prototype.log("fromJSON");
    var _st = JSON.parse(stJSON);
    return new Statement(_st);
  };
}());
(function() {
  "use strict";
  var StatementsResult = TinCan.StatementsResult = function(cfg) {
    this.log("constructor");
    this.statements = null;
    this.more = null;
    this.init(cfg);
  };
  StatementsResult.prototype = {
    LOG_SRC: "StatementsResult",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      cfg = cfg || {};
      if (cfg.hasOwnProperty("statements")) {
        this.statements = cfg.statements;
      }
      if (cfg.hasOwnProperty("more")) {
        this.more = cfg.more;
      }
    }
  };
  StatementsResult.fromJSON = function(resultJSON) {
    StatementsResult.prototype.log("fromJSON");
    var _result, stmts = [],
      stmt, i;
    try {
      _result = JSON.parse(resultJSON);
    } catch (parseError) {
      StatementsResult.prototype.log("fromJSON - JSON.parse error: " + parseError);
    }
    if (_result) {
      for (i = 0; i < _result.statements.length; i += 1) {
        try {
          stmt = new TinCan.Statement(_result.statements[i], 4);
        } catch (error) {
          StatementsResult.prototype.log("fromJSON - statement instantiation failed: " + error + " (" + JSON.stringify(_result.statements[i]) + ")");
          stmt = new TinCan.Statement({
            id: _result.statements[i].id
          }, 4);
        }
        stmts.push(stmt);
      }
      _result.statements = stmts;
    }
    return new StatementsResult(_result);
  };
}());
(function() {
  "use strict";
  var State = TinCan.State = function(cfg) {
    this.log("constructor");
    this.id = null;
    this.updated = null;
    this.contents = null;
    this.etag = null;
    this.contentType = null;
    this.init(cfg);
  };
  State.prototype = {
    LOG_SRC: "State",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["id", "contents", "etag", "contentType"];
      cfg = cfg || {};
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
      this.updated = false;
    }
  };
  State.fromJSON = function(stateJSON) {
    State.prototype.log("fromJSON");
    var _state = JSON.parse(stateJSON);
    return new State(_state);
  };
}());
(function() {
  "use strict";
  var ActivityProfile = TinCan.ActivityProfile = function(cfg) {
    this.log("constructor");
    this.id = null;
    this.activity = null;
    this.updated = null;
    this.contents = null;
    this.etag = null;
    this.contentType = null;
    this.init(cfg);
  };
  ActivityProfile.prototype = {
    LOG_SRC: "ActivityProfile",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["id", "contents", "etag", "contentType"];
      cfg = cfg || {};
      if (cfg.hasOwnProperty("activity")) {
        if (cfg.activity instanceof TinCan.Activity) {
          this.activity = cfg.activity;
        } else {
          this.activity = new TinCan.Activity(cfg.activity);
        }
      }
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
      this.updated = false;
    }
  };
  ActivityProfile.fromJSON = function(stateJSON) {
    ActivityProfile.prototype.log("fromJSON");
    var _state = JSON.parse(stateJSON);
    return new ActivityProfile(_state);
  };
}());
(function() {
  "use strict";
  var AgentProfile = TinCan.AgentProfile = function(cfg) {
    this.log("constructor");
    this.id = null;
    this.agent = null;
    this.updated = null;
    this.contents = null;
    this.etag = null;
    this.contentType = null;
    this.init(cfg);
  };
  AgentProfile.prototype = {
    LOG_SRC: "AgentProfile",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["id", "contents", "etag", "contentType"];
      cfg = cfg || {};
      if (cfg.hasOwnProperty("agent")) {
        if (cfg.agent instanceof TinCan.Agent) {
          this.agent = cfg.agent;
        } else {
          this.agent = new TinCan.Agent(cfg.agent);
        }
      }
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
      this.updated = false;
    }
  };
  AgentProfile.fromJSON = function(stateJSON) {
    AgentProfile.prototype.log("fromJSON");
    var _state = JSON.parse(stateJSON);
    return new AgentProfile(_state);
  };
}());
(function() {
  "use strict";
  var About = TinCan.About = function(cfg) {
    this.log("constructor");
    this.version = null;
    this.init(cfg);
  };
  About.prototype = {
    LOG_SRC: "About",
    log: TinCan.prototype.log,
    init: function(cfg) {
      this.log("init");
      var i, directProps = ["version"];
      cfg = cfg || {};
      for (i = 0; i < directProps.length; i += 1) {
        if (cfg.hasOwnProperty(directProps[i]) && cfg[directProps[i]] !== null) {
          this[directProps[i]] = cfg[directProps[i]];
        }
      }
    }
  };
  About.fromJSON = function(aboutJSON) {
    About.prototype.log("fromJSON");
    var _about = JSON.parse(aboutJSON);
    return new About(_about);
  };
}());
(function() {
  "use strict";
  var LOG_SRC = "Environment.Browser",
    nativeRequest, xdrRequest, requestComplete, __delay, __IEModeConversion, env = {},
    log = TinCan.prototype.log;
  if (typeof window === "undefined") {
    log("'window' not defined", LOG_SRC);
    return;
  }
  if (!window.JSON) {
    window.JSON = {
      parse: function(sJSON) {
        return eval("(" + sJSON + ")");
      },
      stringify: function(vContent) {
        var sOutput = "",
          nId, sProp;
        if (vContent instanceof Object) {
          if (vContent.constructor === Array) {
            for (nId = 0; nId < vContent.length; nId += 1) {
              sOutput += this.stringify(vContent[nId]) + ",";
            }
            return "[" + sOutput.substr(0, sOutput.length - 1) + "]";
          }
          if (vContent.toString !== Object.prototype.toString) {
            return "\"" + vContent.toString().replace(/"/g, "\\$&") + "\"";
          }
          for (sProp in vContent) {
            if (vContent.hasOwnProperty(sProp)) {
              sOutput += "\"" + sProp.replace(/"/g, "\\$&") + "\":" + this.stringify(vContent[sProp]) + ",";
            }
          }
          return "{" + sOutput.substr(0, sOutput.length - 1) + "}";
        }
        return typeof vContent === "string" ? "\"" + vContent.replace(/"/g, "\\$&") + "\"" : String(vContent);
      }
    };
  }
  if (!Date.now) {
    Date.now = function() {
      return +(new Date());
    };
  }
  env.hasCORS = false;
  env.useXDR = false;
  if (typeof XMLHttpRequest !== "undefined" && typeof(new XMLHttpRequest()).withCredentials !== "undefined") {
    env.hasCORS = true;
  } else if (typeof XDomainRequest !== "undefined") {
    env.hasCORS = true;
    env.useXDR = true;
  }
  requestComplete = function(xhr, cfg, control) {
    log("requestComplete: " + control.finished + ", xhr.status: " + xhr.status, LOG_SRC);
    var requestCompleteResult, notFoundOk, httpStatus;
    if (typeof xhr.status === "undefined") {
      httpStatus = control.fakeStatus;
    } else {
      httpStatus = (xhr.status === 1223) ? 204 : xhr.status;
    }
    if (!control.finished) {
      control.finished = true;
      notFoundOk = (cfg.ignore404 && httpStatus === 404);
      if ((httpStatus >= 200 && httpStatus < 400) || notFoundOk) {
        if (cfg.callback) {
          cfg.callback(null, xhr);
        } else {
          requestCompleteResult = {
            err: null,
            xhr: xhr
          };
          return requestCompleteResult;
        }
      } else {
        requestCompleteResult = {
          err: httpStatus,
          xhr: xhr
        };
        if (httpStatus === 0) {
          log("[warning] There was a problem communicating with the Learning Record Store. Aborted, offline, or invalid CORS endpoint (" + httpStatus + ")", LOG_SRC);
        } else {
          log("[warning] There was a problem communicating with the Learning Record Store. (" + httpStatus + " | " + xhr.responseText + ")", LOG_SRC);
        }
        if (cfg.callback) {
          cfg.callback(httpStatus, xhr);
        }
        return requestCompleteResult;
      }
    } else {
      return requestCompleteResult;
    }
  };
  __IEModeConversion = function(fullUrl, headers, pairs, cfg) {
    var prop;
    for (prop in headers) {
      if (headers.hasOwnProperty(prop)) {
        pairs.push(prop + "=" + encodeURIComponent(headers[prop]));
      }
    }
    if (typeof cfg.data !== "undefined") {
      pairs.push("content=" + encodeURIComponent(cfg.data));
    }
    headers["Content-Type"] = "application/x-www-form-urlencoded";
    fullUrl += "?method=" + cfg.method;
    cfg.method = "POST";
    cfg.params = {};
    if (pairs.length > 0) {
      cfg.data = pairs.join("&");
    }
    return fullUrl;
  };
  nativeRequest = function(fullUrl, headers, cfg) {
    log("sendRequest using XMLHttpRequest", LOG_SRC);
    var self = this,
      xhr, prop, pairs = [],
      data, control = {
        finished: false,
        fakeStatus: null
      },
      async = typeof cfg.callback !== "undefined", fullRequest = fullUrl, err, MAX_REQUEST_LENGTH = 2048;
    log("sendRequest using XMLHttpRequest - async: " + async, LOG_SRC);
    for (prop in cfg.params) {
      if (cfg.params.hasOwnProperty(prop)) {
        pairs.push(prop + "=" + encodeURIComponent(cfg.params[prop]));
      }
    }
    if (pairs.length > 0) {
      fullRequest += "?" + pairs.join("&");
    }
    if (fullRequest.length >= MAX_REQUEST_LENGTH) {
      if (typeof headers["Content-Type"] !== "undefined" && headers["Content-Type"] !== "application/json") {
        err = new Error("Unsupported content type for IE Mode request");
        if (typeof cfg.callback !== "undefined") {
          cfg.callback(err, null);
        }
        return {
          err: err,
          xhr: null
        };
      }
      if (typeof cfg.method === "undefined") {
        err = new Error("method must not be undefined for an IE Mode Request conversion");
        if (typeof cfg.callback !== "undefined") {
          cfg.callback(err, null);
        }
        return {
          err: err,
          xhr: null
        };
      }
      fullUrl = __IEModeConversion(fullUrl, headers, pairs, cfg);
    } else {
      fullUrl = fullRequest;
    }
    if (typeof XMLHttpRequest !== "undefined") {
      xhr = new XMLHttpRequest();
    } else {
      xhr = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xhr.open(cfg.method, fullUrl, async);
    for (prop in headers) {
      if (headers.hasOwnProperty(prop)) {
        xhr.setRequestHeader(prop, headers[prop]);
      }
    }
    if (typeof cfg.data !== "undefined") {
      cfg.data += "";
    }
    data = cfg.data;
    if (async) {
      xhr.onreadystatechange = function() {
        log("xhr.onreadystatechange - xhr.readyState: " + xhr.readyState, LOG_SRC);
        if (xhr.readyState === 4) {
          requestComplete.call(self, xhr, cfg, control);
        }
      };
    }
    try {
      xhr.send(data);
    } catch (ex) {
      log("sendRequest caught send exception: " + ex, LOG_SRC);
    }
    if (async) {
      return xhr;
    }
    return requestComplete.call(this, xhr, cfg, control);
  };
  xdrRequest = function(fullUrl, headers, cfg) {
    log("sendRequest using XDomainRequest", LOG_SRC);
    var self = this,
      xhr, pairs = [],
      data, prop, until, control = {
        finished: false,
        fakeStatus: null
      },
      err;
    if (typeof headers["Content-Type"] !== "undefined" && headers["Content-Type"] !== "application/json") {
      err = new Error("Unsupported content type for IE Mode request");
      if (cfg.callback) {
        cfg.callback(err, null);
        return null;
      }
      return {
        err: err,
        xhr: null
      };
    }
    fullUrl += "?method=" + cfg.method;
    for (prop in cfg.params) {
      if (cfg.params.hasOwnProperty(prop)) {
        pairs.push(prop + "=" + encodeURIComponent(cfg.params[prop]));
      }
    }
    for (prop in headers) {
      if (headers.hasOwnProperty(prop)) {
        pairs.push(prop + "=" + encodeURIComponent(headers[prop]));
      }
    }
    if (cfg.data !== null) {
      pairs.push("content=" + encodeURIComponent(cfg.data));
    }
    data = pairs.join("&");
    xhr = new XDomainRequest();
    xhr.open("POST", fullUrl);
    if (!cfg.callback) {
      xhr.onload = function() {
        control.fakeStatus = 200;
      };
      xhr.onerror = function() {
        control.fakeStatus = 400;
      };
      xhr.ontimeout = function() {
        control.fakeStatus = 0;
      };
    } else {
      xhr.onload = function() {
        control.fakeStatus = 200;
        requestComplete.call(self, xhr, cfg, control);
      };
      xhr.onerror = function() {
        control.fakeStatus = 400;
        requestComplete.call(self, xhr, cfg, control);
      };
      xhr.ontimeout = function() {
        control.fakeStatus = 0;
        requestComplete.call(self, xhr, cfg, control);
      };
    }
    xhr.onprogress = function() {};
    xhr.timeout = 0;
    try {
      xhr.send(data);
    } catch (ex) {
      log("sendRequest caught send exception: " + ex, LOG_SRC);
    }
    if (!cfg.callback) {
      until = 10000 + Date.now();
      log("sendRequest - until: " + until + ", finished: " + control.finished, LOG_SRC);
      while (Date.now() < until && control.fakeStatus === null) {
        __delay();
      }
      return requestComplete.call(self, xhr, cfg, control);
    }
    return xhr;
  };
  TinCan.LRS.prototype._initByEnvironment = function(cfg) {
    log("_initByEnvironment", LOG_SRC);
    var urlParts, schemeMatches, locationPort, isXD;
    cfg = cfg || {};
    this._makeRequest = nativeRequest;
    this._IEModeConversion = __IEModeConversion;
    urlParts = this.endpoint.toLowerCase().match(/([A-Za-z]+:)\/\/([^:\/]+):?(\d+)?(\/.*)?$/);
    if (urlParts === null) {
      log("[error] LRS invalid: failed to divide URL parts", LOG_SRC);
      throw {
        code: 4,
        mesg: "LRS invalid: failed to divide URL parts"
      };
    }
    locationPort = location.port;
    schemeMatches = location.protocol.toLowerCase() === urlParts[1];
    if (locationPort === "") {
      locationPort = (location.protocol.toLowerCase() === "http:" ? "80" : (location.protocol.toLowerCase() === "https:" ? "443" : ""));
    }
    isXD = (!schemeMatches || location.hostname.toLowerCase() !== urlParts[2] || locationPort !== ((urlParts[3] !== null && typeof urlParts[3] !== "undefined" && urlParts[3] !== "") ? urlParts[3] : (urlParts[1] === "http:" ? "80" : (urlParts[1] === "https:" ? "443" : ""))));
    if (isXD) {
      if (env.hasCORS) {
        if (env.useXDR && schemeMatches) {
          this._makeRequest = xdrRequest;
        } else if (env.useXDR && !schemeMatches) {
          if (cfg.allowFail) {
            log("[warning] LRS invalid: cross domain request for differing scheme in IE with XDR (allowed to fail)", LOG_SRC);
          } else {
            log("[error] LRS invalid: cross domain request for differing scheme in IE with XDR", LOG_SRC);
            throw {
              code: 2,
              mesg: "LRS invalid: cross domain request for differing scheme in IE with XDR"
            };
          }
        }
      } else {
        if (cfg.allowFail) {
          log("[warning] LRS invalid: cross domain requests not supported in this browser (allowed to fail)", LOG_SRC);
        } else {
          log("[error] LRS invalid: cross domain requests not supported in this browser", LOG_SRC);
          throw {
            code: 1,
            mesg: "LRS invalid: cross domain requests not supported in this browser"
          };
        }
      }
    }
  };
  __delay = function() {
    var xhr = new XMLHttpRequest(),
      url = window.location + "?forcenocache=" + TinCan.Utils.getUUID();
    xhr.open("GET", url, false);
    xhr.send(null);
  };
  TinCan.LRS.syncEnabled = true;
}());;
(function(root) {
  var freeExports = typeof exports == 'object' && exports;
  var freeModule = typeof module == 'object' && module && module.exports == freeExports && module;
  var freeGlobal = typeof global == 'object' && global;
  if (freeGlobal.global === freeGlobal || freeGlobal.window === freeGlobal) {
    root = freeGlobal;
  }
  var punycode, maxInt = 2147483647,
    base = 36,
    tMin = 1,
    tMax = 26,
    skew = 38,
    damp = 700,
    initialBias = 72,
    initialN = 128,
    delimiter = '-',
    regexPunycode = /^xn--/,
    regexNonASCII = /[^ -~]/,
    regexSeparators = /\x2E|\u3002|\uFF0E|\uFF61/g,
    errors = {
      'overflow': 'Overflow: input needs wider integers to process',
      'not-basic': 'Illegal input >= 0x80 (not a basic code point)',
      'invalid-input': 'Invalid input'
    },
    baseMinusTMin = base - tMin,
    floor = Math.floor,
    stringFromCharCode = String.fromCharCode,
    key;

  function error(type) {
    throw RangeError(errors[type]);
  }

  function map(array, fn) {
    var length = array.length;
    while (length--) {
      array[length] = fn(array[length]);
    }
    return array;
  }

  function mapDomain(string, fn) {
    return map(string.split(regexSeparators), fn).join('.');
  }

  function ucs2decode(string) {
    var output = [],
      counter = 0,
      length = string.length,
      value, extra;
    while (counter < length) {
      value = string.charCodeAt(counter++);
      if (value >= 0xD800 && value <= 0xDBFF && counter < length) {
        extra = string.charCodeAt(counter++);
        if ((extra & 0xFC00) == 0xDC00) {
          output.push(((value & 0x3FF) << 10) + (extra & 0x3FF) + 0x10000);
        } else {
          output.push(value);
          counter--;
        }
      } else {
        output.push(value);
      }
    }
    return output;
  }

  function ucs2encode(array) {
    return map(array, function(value) {
      var output = '';
      if (value > 0xFFFF) {
        value -= 0x10000;
        output += stringFromCharCode(value >>> 10 & 0x3FF | 0xD800);
        value = 0xDC00 | value & 0x3FF;
      }
      output += stringFromCharCode(value);
      return output;
    }).join('');
  }

  function basicToDigit(codePoint) {
    if (codePoint - 48 < 10) {
      return codePoint - 22;
    }
    if (codePoint - 65 < 26) {
      return codePoint - 65;
    }
    if (codePoint - 97 < 26) {
      return codePoint - 97;
    }
    return base;
  }

  function digitToBasic(digit, flag) {
    return digit + 22 + 75 * (digit < 26) - ((flag != 0) << 5);
  }

  function adapt(delta, numPoints, firstTime) {
    var k = 0;
    delta = firstTime ? floor(delta / damp) : delta >> 1;
    delta += floor(delta / numPoints);
    for (; delta > baseMinusTMin * tMax >> 1; k += base) {
      delta = floor(delta / baseMinusTMin);
    }
    return floor(k + (baseMinusTMin + 1) * delta / (delta + skew));
  }

  function decode(input) {
    var output = [],
      inputLength = input.length,
      out, i = 0,
      n = initialN,
      bias = initialBias,
      basic, j, index, oldi, w, k, digit, t, length, baseMinusT;
    basic = input.lastIndexOf(delimiter);
    if (basic < 0) {
      basic = 0;
    }
    for (j = 0; j < basic; ++j) {
      if (input.charCodeAt(j) >= 0x80) {
        error('not-basic');
      }
      output.push(input.charCodeAt(j));
    }
    for (index = basic > 0 ? basic + 1 : 0; index < inputLength;) {
      for (oldi = i, w = 1, k = base;; k += base) {
        if (index >= inputLength) {
          error('invalid-input');
        }
        digit = basicToDigit(input.charCodeAt(index++));
        if (digit >= base || digit > floor((maxInt - i) / w)) {
          error('overflow');
        }
        i += digit * w;
        t = k <= bias ? tMin : (k >= bias + tMax ? tMax : k - bias);
        if (digit < t) {
          break;
        }
        baseMinusT = base - t;
        if (w > floor(maxInt / baseMinusT)) {
          error('overflow');
        }
        w *= baseMinusT;
      }
      out = output.length + 1;
      bias = adapt(i - oldi, out, oldi == 0);
      if (floor(i / out) > maxInt - n) {
        error('overflow');
      }
      n += floor(i / out);
      i %= out;
      output.splice(i++, 0, n);
    }
    return ucs2encode(output);
  }

  function encode(input) {
    var n, delta, handledCPCount, basicLength, bias, j, m, q, k, t, currentValue, output = [],
      inputLength, handledCPCountPlusOne, baseMinusT, qMinusT;
    input = ucs2decode(input);
    inputLength = input.length;
    n = initialN;
    delta = 0;
    bias = initialBias;
    for (j = 0; j < inputLength; ++j) {
      currentValue = input[j];
      if (currentValue < 0x80) {
        output.push(stringFromCharCode(currentValue));
      }
    }
    handledCPCount = basicLength = output.length;
    if (basicLength) {
      output.push(delimiter);
    }
    while (handledCPCount < inputLength) {
      for (m = maxInt, j = 0; j < inputLength; ++j) {
        currentValue = input[j];
        if (currentValue >= n && currentValue < m) {
          m = currentValue;
        }
      }
      handledCPCountPlusOne = handledCPCount + 1;
      if (m - n > floor((maxInt - delta) / handledCPCountPlusOne)) {
        error('overflow');
      }
      delta += (m - n) * handledCPCountPlusOne;
      n = m;
      for (j = 0; j < inputLength; ++j) {
        currentValue = input[j];
        if (currentValue < n && ++delta > maxInt) {
          error('overflow');
        }
        if (currentValue == n) {
          for (q = delta, k = base;; k += base) {
            t = k <= bias ? tMin : (k >= bias + tMax ? tMax : k - bias);
            if (q < t) {
              break;
            }
            qMinusT = q - t;
            baseMinusT = base - t;
            output.push(stringFromCharCode(digitToBasic(t + qMinusT % baseMinusT, 0)));
            q = floor(qMinusT / baseMinusT);
          }
          output.push(stringFromCharCode(digitToBasic(q, 0)));
          bias = adapt(delta, handledCPCountPlusOne, handledCPCount == basicLength);
          delta = 0;
          ++handledCPCount;
        }
      }
      ++delta;
      ++n;
    }
    return output.join('');
  }

  function toUnicode(domain) {
    return mapDomain(domain, function(string) {
      return regexPunycode.test(string) ? decode(string.slice(4).toLowerCase()) : string;
    });
  }

  function toASCII(domain) {
    return mapDomain(domain, function(string) {
      return regexNonASCII.test(string) ? 'xn--' + encode(string) : string;
    });
  }
  punycode = {
    'version': '1.2.3',
    'ucs2': {
      'decode': ucs2decode,
      'encode': ucs2encode
    },
    'decode': decode,
    'encode': encode,
    'toASCII': toASCII,
    'toUnicode': toUnicode
  };
  if (typeof define == 'function' && typeof define.amd == 'object' && define.amd) {
    define(function() {
      return punycode;
    });
  } else if (freeExports && !freeExports.nodeType) {
    if (freeModule) {
      freeModule.exports = punycode;
    } else {
      for (key in punycode) {
        punycode.hasOwnProperty(key) && (freeExports[key] = punycode[key]);
      }
    }
  } else {
    root.punycode = punycode;
  }
}(this));
(function(root, factory) {
  'use strict';
  if (typeof exports === 'object') {
    module.exports = factory(require('./punycode'), require('./IPv6'), require('./SecondLevelDomains'));
  } else if (typeof define === 'function' && define.amd) {
    define(['./punycode', './IPv6', './SecondLevelDomains'], factory);
  } else {
    root.URI = factory(root.punycode, root.IPv6, root.SecondLevelDomains, root);
  }
}(this,
  function(punycode, IPv6, SLD, root) {
  'use strict';
  var _URI = root && root.URI;

  function URI(url, base) {
    if (!(this instanceof URI)) {
      return new URI(url, base);
    }
    if (url === undefined) {
      if (arguments.length) {
        throw new TypeError('undefined is not a valid argument for URI');
      }
      if (typeof location !== 'undefined') {
        url = location.href + '';
      } else {
        url = '';
      }
    }
    this.href(url);
    if (base !== undefined) {
      return this.absoluteTo(base);
    }
    return this;
  }
  URI.version = '1.14.2';
  var p = URI.prototype;
  var hasOwn = Object.prototype.hasOwnProperty;

  function escapeRegEx(string) {
    return string.replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
  }

  function getType(value) {
    if (value === undefined) {
      return 'Undefined';
    }
    return String(Object.prototype.toString.call(value)).slice(8, -1);
  }

  function isArray(obj) {
    return getType(obj) === 'Array';
  }

  function filterArrayValues(data, value) {
    var lookup = {};
    var i, length;
    if (isArray(value)) {
      for (i = 0, length = value.length; i < length; i++) {
        lookup[value[i]] = true;
      }
    } else {
      lookup[value] = true;
    }
    for (i = 0, length = data.length; i < length; i++) {
      if (lookup[data[i]] !== undefined) {
        data.splice(i, 1);
        length--;
        i--;
      }
    }
    return data;
  }

  function arrayContains(list, value) {
    var i, length;
    if (isArray(value)) {
      for (i = 0, length = value.length; i < length; i++) {
        if (!arrayContains(list, value[i])) {
          return false;
        }
      }
      return true;
    }
    var _type = getType(value);
    for (i = 0, length = list.length; i < length; i++) {
      if (_type === 'RegExp') {
        if (typeof list[i] === 'string' && list[i].match(value)) {
          return true;
        }
      } else if (list[i] === value) {
        return true;
      }
    }
    return false;
  }

  function arraysEqual(one, two) {
    if (!isArray(one) || !isArray(two)) {
      return false;
    }
    if (one.length !== two.length) {
      return false;
    }
    one.sort();
    two.sort();
    for (var i = 0, l = one.length; i < l; i++) {
      if (one[i] !== two[i]) {
        return false;
      }
    }
    return true;
  }
  URI._parts = function() {
    return {
      protocol: null,
      username: null,
      password: null,
      hostname: null,
      urn: null,
      port: null,
      path: null,
      query: null,
      fragment: null,
      duplicateQueryParameters: URI.duplicateQueryParameters,
      escapeQuerySpace: URI.escapeQuerySpace
    };
  };
  URI.duplicateQueryParameters = false;
  URI.escapeQuerySpace = true;
  URI.protocol_expression = /^[a-z][a-z0-9.+-]*$/i;
  URI.idn_expression = /[^a-z0-9\.-]/i;
  URI.punycode_expression = /(xn--)/i;
  URI.ip4_expression = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/;
  URI.ip6_expression = /^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/;
  URI.find_uri_expression = /\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>?«»“”‘’]))/ig;
  URI.findUri = {
    start: /\b(?:([a-z][a-z0-9.+-]*:\/\/)|www\.)/gi,
    end: /[\s\r\n]|$/,
    trim: /[`!()\[\]{};:'".,<>?«»“”„‘’]+$/
  };
  URI.defaultPorts = {
    http: '80',
    https: '443',
    ftp: '21',
    gopher: '70',
    ws: '80',
    wss: '443'
  };
  URI.invalid_hostname_characters = /[^a-zA-Z0-9\.-]/;
  URI.domAttributes = {
    'a': 'href',
    'blockquote': 'cite',
    'link': 'href',
    'base': 'href',
    'script': 'src',
    'form': 'action',
    'img': 'src',
    'area': 'href',
    'iframe': 'src',
    'embed': 'src',
    'source': 'src',
    'track': 'src',
    'input': 'src',
    'audio': 'src',
    'video': 'src'
  };
  URI.getDomAttribute = function(node) {
    if (!node || !node.nodeName) {
      return undefined;
    }
    var nodeName = node.nodeName.toLowerCase();
    if (nodeName === 'input' && node.type !== 'image') {
      return undefined;
    }
    return URI.domAttributes[nodeName];
  };

  function escapeForDumbFirefox36(value) {
    return escape(value);
  }

  function strictEncodeURIComponent(string) {
    return encodeURIComponent(string).replace(/[!'()*]/g, escapeForDumbFirefox36).replace(/\*/g, '%2A');
  }

  function _isIriCodePoint(point) {
    return (point === 0x00002d || point === 0x0002E || point === 0x00005F || point === 0x0007E || (point >= 0x000030 && point < 0x000040) || (point >= 0x000041 && point < 0x00005B) || (point >= 0x000061 && point < 0x00007B) || (point >= 0x0000A0 && point < 0x00D800) || (point >= 0x00E000 && point < 0x00F8FF) || (point >= 0x00F900 && point < 0x00FDD0) || (point >= 0x00FDF0 && point < 0x00FFF0) || (point >= 0x010000 && point < 0x01FFFE) || (point >= 0x020000 && point < 0x02FFFE) || (point >= 0x030000 && point < 0x03FFFE) || (point >= 0x040000 && point < 0x04FFFE) || (point >= 0x050000 && point < 0x05FFFE) || (point >= 0x060000 && point < 0x06FFFE) || (point >= 0x070000 && point < 0x07FFFE) || (point >= 0x080000 && point < 0x08FFFE) || (point >= 0x090000 && point < 0x09FFFE) || (point >= 0x0A0000 && point < 0x0AFFFE) || (point >= 0x0B0000 && point < 0x0BFFFE) || (point >= 0x0C0000 && point < 0x0CFFFE) || (point >= 0x0D0000 && point < 0x0DFFFE) || (point >= 0x0E0000 && point < 0x0EFFFE) || (point >= 0x0F0000 && point < 0x0FFFFE) || (point >= 0x100000 && point < 0x10FFFE));
  }

  function encodeIRIComponent(string) {
    var inputCodePoints = punycode.ucs2.decode(string);
    var output = '';
    for (var i = 0; i < inputCodePoints.length; i++) {
      var codePoint = inputCodePoints[i];
      if (_isIriCodePoint(codePoint)) {
        output += punycode.ucs2.encode([codePoint]);
      } else {
        var asString = punycode.ucs2.encode([codePoint]);
        output += strictEncodeURIComponent(asString);
      }
    }
    return output;
  }

  function recodeIRIHostname(string) {
    if (URI.punycode_expression.test(string)) {
      string = punycode.toUnicode(string);
    }
    return encodeIRIComponent(string);
  }
  URI._defaultRecodeHostname = punycode ? punycode.toASCII : function(string) {
    return string;
  };
  URI.iso8859 = function() {
    URI.recodeHostname = URI._defaultRecodeHostname;
    URI.encode = escape;
    URI.decode = unescape;
  };
  URI.unicode = function() {
    URI.recodeHostname = URI._defaultRecodeHostname;
    URI.encode = strictEncodeURIComponent;
    URI.decode = decodeURIComponent;
  };
  URI.iri = function() {
    URI.recodeHostname = recodeIRIHostname;
    URI.encode = encodeIRIComponent;
    URI.decode = decodeURIComponent;
  };
  URI.unicode();
  URI.characters = {
    pathname: {
      encode: {
        expression: /%(24|26|2B|2C|3B|3D|3A|40)/ig,
        map: {
          '%24': '$',
          '%26': '&',
          '%2B': '+',
          '%2C': ',',
          '%3B': ';',
          '%3D': '=',
          '%3A': ':',
          '%40': '@'
        }
      },
      decode: {
        expression: /[\/\?#]/g,
        map: {
          '/': '%2F',
          '?': '%3F',
          '#': '%23'
        }
      }
    },
    reserved: {
      encode: {
        expression: /%(21|23|24|26|27|28|29|2A|2B|2C|2F|3A|3B|3D|3F|40|5B|5D)/ig,
        map: {
          '%3A': ':',
          '%2F': '/',
          '%3F': '?',
          '%23': '#',
          '%5B': '[',
          '%5D': ']',
          '%40': '@',
          '%21': '!',
          '%24': '$',
          '%26': '&',
          '%27': '\'',
          '%28': '(',
          '%29': ')',
          '%2A': '*',
          '%2B': '+',
          '%2C': ',',
          '%3B': ';',
          '%3D': '='
        }
      }
    },
    urnpath: {
      encode: {
        expression: /%(21|24|27|28|29|2A|2B|2C|3B|3D|40)/ig,
        map: {
          '%21': '!',
          '%24': '$',
          '%27': '\'',
          '%28': '(',
          '%29': ')',
          '%2A': '*',
          '%2B': '+',
          '%2C': ',',
          '%3B': ';',
          '%3D': '=',
          '%40': '@'
        }
      },
      decode: {
        expression: /[\/\?#:]/g,
        map: {
          '/': '%2F',
          '?': '%3F',
          '#': '%23',
          ':': '%3A'
        }
      }
    }
  };
  URI.encodeQuery = function(string, escapeQuerySpace) {
    var escaped = URI.encode(string + '');
    if (escapeQuerySpace === undefined) {
      escapeQuerySpace = URI.escapeQuerySpace;
    }
    return escapeQuerySpace ? escaped.replace(/%20/g, '+') : escaped;
  };
  URI.decodeQuery = function(string, escapeQuerySpace) {
    string += '';
    if (escapeQuerySpace === undefined) {
      escapeQuerySpace = URI.escapeQuerySpace;
    }
    try {
      return URI.decode(escapeQuerySpace ? string.replace(/\+/g, '%20') : string);
    } catch (e) {
      return string;
    }
  };
  var _parts = {
    'encode': 'encode',
    'decode': 'decode'
  };
  var _part;
  var generateAccessor = function(_group, _part) {
    return function(string) {
      try {
        return URI[_part](string + '').replace(URI.characters[_group][_part].expression, function(c) {
          return URI.characters[_group][_part].map[c];
        });
      } catch (e) {
        return string;
      }
    };
  };
  for (_part in _parts) {
    URI[_part + 'PathSegment'] = generateAccessor('pathname', _parts[_part]);
    URI[_part + 'UrnPathSegment'] = generateAccessor('urnpath', _parts[_part]);
  }
  var generateSegmentedPathFunction = function(_sep, _codingFuncName, _innerCodingFuncName) {
    return function(string) {
      var actualCodingFunc;
      if (!_innerCodingFuncName) {
        actualCodingFunc = URI[_codingFuncName];
      } else {
        actualCodingFunc = function(string) {
          return URI[_codingFuncName](URI[_innerCodingFuncName](string));
        };
      }
      var segments = (string + '').split(_sep);
      for (var i = 0, length = segments.length; i < length; i++) {
        segments[i] = actualCodingFunc(segments[i]);
      }
      return segments.join(_sep);
    };
  };
  URI.decodePath = generateSegmentedPathFunction('/', 'decodePathSegment');
  URI.decodeUrnPath = generateSegmentedPathFunction(':', 'decodeUrnPathSegment');
  URI.recodePath = generateSegmentedPathFunction('/', 'encodePathSegment', 'decode');
  URI.recodeUrnPath = generateSegmentedPathFunction(':', 'encodeUrnPathSegment', 'decode');
  URI.encodeReserved = generateAccessor('reserved', 'encode');
  URI.parse = function(string, parts) {
    var pos;
    if (!parts) {
      parts = {};
    }
    pos = string.indexOf('#');
    if (pos > -1) {
      parts.fragment = string.substring(pos + 1) || null;
      string = string.substring(0, pos);
    }
    pos = string.indexOf('?');
    if (pos > -1) {
      parts.query = string.substring(pos + 1) || null;
      string = string.substring(0, pos);
    }
    if (string.substring(0, 2) === '//') {
      parts.protocol = null;
      string = string.substring(2);
      string = URI.parseAuthority(string, parts);
    } else {
      pos = string.indexOf(':');
      if (pos > -1) {
        parts.protocol = string.substring(0, pos) || null;
        if (parts.protocol && !parts.protocol.match(URI.protocol_expression)) {
          parts.protocol = undefined;
        } else if (string.substring(pos + 1, pos + 3) === '//') {
          string = string.substring(pos + 3);
          string = URI.parseAuthority(string, parts);
        } else {
          string = string.substring(pos + 1);
          parts.urn = true;
        }
      }
    }
    parts.path = string;
    return parts;
  };
  URI.parseHost = function(string, parts) {
    var pos = string.indexOf('/');
    var bracketPos;
    var t;
    if (pos === -1) {
      pos = string.length;
    }
    if (string.charAt(0) === '[') {
      bracketPos = string.indexOf(']');
      parts.hostname = string.substring(1, bracketPos) || null;
      parts.port = string.substring(bracketPos + 2, pos) || null;
      if (parts.port === '/') {
        parts.port = null;
      }
    } else {
      var firstColon = string.indexOf(':');
      var firstSlash = string.indexOf('/');
      var nextColon = string.indexOf(':', firstColon + 1);
      if (nextColon !== -1 && (firstSlash === -1 || nextColon < firstSlash)) {
        parts.hostname = string.substring(0, pos) || null;
        parts.port = null;
      } else {
        t = string.substring(0, pos).split(':');
        parts.hostname = t[0] || null;
        parts.port = t[1] || null;
      }
    }
    if (parts.hostname && string.substring(pos).charAt(0) !== '/') {
      pos++;
      string = '/' + string;
    }
    return string.substring(pos) || '/';
  };
  URI.parseAuthority = function(string, parts) {
    string = URI.parseUserinfo(string, parts);
    return URI.parseHost(string, parts);
  };
  URI.parseUserinfo = function(string, parts) {
    var firstSlash = string.indexOf('/');
    var pos = string.lastIndexOf('@', firstSlash > -1 ? firstSlash : string.length - 1);
    var t;
    if (pos > -1 && (firstSlash === -1 || pos < firstSlash)) {
      t = string.substring(0, pos).split(':');
      parts.username = t[0] ? URI.decode(t[0]) : null;
      t.shift();
      parts.password = t[0] ? URI.decode(t.join(':')) : null;
      string = string.substring(pos + 1);
    } else {
      parts.username = null;
      parts.password = null;
    }
    return string;
  };
  URI.parseQuery = function(string, escapeQuerySpace) {
    if (!string) {
      return {};
    }
    string = string.replace(/&+/g, '&').replace(/^\?*&*|&+$/g, '');
    if (!string) {
      return {};
    }
    var items = {};
    var splits = string.split('&');
    var length = splits.length;
    var v, name, value;
    for (var i = 0; i < length; i++) {
      v = splits[i].split('=');
      name = URI.decodeQuery(v.shift(), escapeQuerySpace);
      value = v.length ? URI.decodeQuery(v.join('='), escapeQuerySpace) : null;
      if (hasOwn.call(items, name)) {
        if (typeof items[name] === 'string') {
          items[name] = [items[name]];
        }
        items[name].push(value);
      } else {
        items[name] = value;
      }
    }
    return items;
  };
  URI.build = function(parts) {
    var t = '';
    if (parts.protocol) {
      t += parts.protocol + ':';
    }
    if (!parts.urn && (t || parts.hostname)) {
      t += '//';
    }
    t += (URI.buildAuthority(parts) || '');
    if (typeof parts.path === 'string') {
      if (parts.path.charAt(0) !== '/' && typeof parts.hostname === 'string') {
        t += '/';
      }
      t += parts.path;
    }
    if (typeof parts.query === 'string' && parts.query) {
      t += '?' + parts.query;
    }
    if (typeof parts.fragment === 'string' && parts.fragment) {
      t += '#' + parts.fragment;
    }
    return t;
  };
  URI.buildHost = function(parts) {
    var t = '';
    if (!parts.hostname) {
      return '';
    } else if (URI.ip6_expression.test(parts.hostname)) {
      t += '[' + parts.hostname + ']';
    } else {
      t += parts.hostname;
    }
    if (parts.port) {
      t += ':' + parts.port;
    }
    return t;
  };
  URI.buildAuthority = function(parts) {
    return URI.buildUserinfo(parts) + URI.buildHost(parts);
  };
  URI.buildUserinfo = function(parts) {
    var t = '';
    if (parts.username) {
      t += URI.encode(parts.username);
      if (parts.password) {
        t += ':' + URI.encode(parts.password);
      }
      t += '@';
    }
    return t;
  };
  URI.buildQuery = function(data, duplicateQueryParameters, escapeQuerySpace) {
    var t = '';
    var unique, key, i, length;
    for (key in data) {
      if (hasOwn.call(data, key) && key) {
        if (isArray(data[key])) {
          unique = {};
          for (i = 0, length = data[key].length; i < length; i++) {
            if (data[key][i] !== undefined && unique[data[key][i] + ''] === undefined) {
              t += '&' + URI.buildQueryParameter(key, data[key][i], escapeQuerySpace);
              if (duplicateQueryParameters !== true) {
                unique[data[key][i] + ''] = true;
              }
            }
          }
        } else if (data[key] !== undefined) {
          t += '&' + URI.buildQueryParameter(key, data[key], escapeQuerySpace);
        }
      }
    }
    return t.substring(1);
  };
  URI.buildQueryParameter = function(name, value, escapeQuerySpace) {
    return URI.encodeQuery(name, escapeQuerySpace) + (value !== null ? '=' + URI.encodeQuery(value, escapeQuerySpace) : '');
  };
  URI.addQuery = function(data, name, value) {
    if (typeof name === 'object') {
      for (var key in name) {
        if (hasOwn.call(name, key)) {
          URI.addQuery(data, key, name[key]);
        }
      }
    } else if (typeof name === 'string') {
      if (data[name] === undefined) {
        data[name] = value;
        return;
      } else if (typeof data[name] === 'string') {
        data[name] = [data[name]];
      }
      if (!isArray(value)) {
        value = [value];
      }
      data[name] = (data[name] || []).concat(value);
    } else {
      throw new TypeError('URI.addQuery() accepts an object, string as the name parameter');
    }
  };
  URI.removeQuery = function(data, name, value) {
    var i, length, key;
    if (isArray(name)) {
      for (i = 0, length = name.length; i < length; i++) {
        data[name[i]] = undefined;
      }
    } else if (typeof name === 'object') {
      for (key in name) {
        if (hasOwn.call(name, key)) {
          URI.removeQuery(data, key, name[key]);
        }
      }
    } else if (typeof name === 'string') {
      if (value !== undefined) {
        if (data[name] === value) {
          data[name] = undefined;
        } else if (isArray(data[name])) {
          data[name] = filterArrayValues(data[name], value);
        }
      } else {
        data[name] = undefined;
      }
    } else {
      throw new TypeError('URI.removeQuery() accepts an object, string as the first parameter');
    }
  };
  URI.hasQuery = function(data, name, value, withinArray) {
    if (typeof name === 'object') {
      for (var key in name) {
        if (hasOwn.call(name, key)) {
          if (!URI.hasQuery(data, key, name[key])) {
            return false;
          }
        }
      }
      return true;
    } else if (typeof name !== 'string') {
      throw new TypeError('URI.hasQuery() accepts an object, string as the name parameter');
    }
    switch (getType(value)) {
      case 'Undefined':
        return name in data;
      case 'Boolean':
        var _booly = Boolean(isArray(data[name]) ? data[name].length : data[name]);
        return value === _booly;
      case 'Function':
        return !!value(data[name], name, data);
      case 'Array':
        if (!isArray(data[name])) {
          return false;
        }
        var op = withinArray ? arrayContains : arraysEqual;
        return op(data[name], value);
      case 'RegExp':
        if (!isArray(data[name])) {
          return Boolean(data[name] && data[name].match(value));
        }
        if (!withinArray) {
          return false;
        }
        return arrayContains(data[name], value);
      case 'Number':
        value = String(value);
      case 'String':
        if (!isArray(data[name])) {
          return data[name] === value;
        }
        if (!withinArray) {
          return false;
        }
        return arrayContains(data[name], value);
      default:
        throw new TypeError('URI.hasQuery() accepts undefined, boolean, string, number, RegExp, Function as the value parameter');
    }
  };
  URI.commonPath = function(one, two) {
    var length = Math.min(one.length, two.length);
    var pos;
    for (pos = 0; pos < length; pos++) {
      if (one.charAt(pos) !== two.charAt(pos)) {
        pos--;
        break;
      }
    }
    if (pos < 1) {
      return one.charAt(0) === two.charAt(0) && one.charAt(0) === '/' ? '/' : '';
    }
    if (one.charAt(pos) !== '/' || two.charAt(pos) !== '/') {
      pos = one.substring(0, pos).lastIndexOf('/');
    }
    return one.substring(0, pos + 1);
  };
  URI.withinString = function(string, callback, options) {
    options || (options = {});
    var _start = options.start || URI.findUri.start;
    var _end = options.end || URI.findUri.end;
    var _trim = options.trim || URI.findUri.trim;
    var _attributeOpen = /[a-z0-9-]=["']?$/i;
    _start.lastIndex = 0;
    while (true) {
      var match = _start.exec(string);
      if (!match) {
        break;
      }
      var start = match.index;
      if (options.ignoreHtml) {
        var attributeOpen = string.slice(Math.max(start - 3, 0), start);
        if (attributeOpen && _attributeOpen.test(attributeOpen)) {
          continue;
        }
      }
      var end = start + string.slice(start).search(_end);
      var slice = string.slice(start, end).replace(_trim, '');
      if (options.ignore && options.ignore.test(slice)) {
        continue;
      }
      end = start + slice.length;
      var result = callback(slice, start, end, string);
      string = string.slice(0, start) + result + string.slice(end);
      _start.lastIndex = start + result.length;
    }
    _start.lastIndex = 0;
    return string;
  };
  URI.ensureValidHostname = function(v) {
    if (v.match(URI.invalid_hostname_characters)) {
      if (!punycode) {
        throw new TypeError('Hostname "' + v + '" contains characters other than [A-Z0-9.-] and Punycode.js is not available');
      }
      if (punycode.toASCII(v).match(URI.invalid_hostname_characters)) {
        throw new TypeError('Hostname "' + v + '" contains characters other than [A-Z0-9.-]');
      }
    }
  };
  URI.noConflict = function(removeAll) {
    if (removeAll) {
      var unconflicted = {
        URI: this.noConflict()
      };
      if (root.URITemplate && typeof root.URITemplate.noConflict === 'function') {
        unconflicted.URITemplate = root.URITemplate.noConflict();
      }
      if (root.IPv6 && typeof root.IPv6.noConflict === 'function') {
        unconflicted.IPv6 = root.IPv6.noConflict();
      }
      if (root.SecondLevelDomains && typeof root.SecondLevelDomains.noConflict === 'function') {
        unconflicted.SecondLevelDomains = root.SecondLevelDomains.noConflict();
      }
      return unconflicted;
    } else if (root.URI === this) {
      root.URI = _URI;
    }
    return this;
  };
  p.build = function(deferBuild) {
    if (deferBuild === true) {
      this._deferred_build = true;
    } else if (deferBuild === undefined || this._deferred_build) {
      this._string = URI.build(this._parts);
      this._deferred_build = false;
    }
    return this;
  };
  p.clone = function() {
    return new URI(this);
  };
  p.valueOf = p.toString = function() {
    return this.build(false)._string;
  };

  function generateSimpleAccessor(_part) {
    return function(v, build) {
      if (v === undefined) {
        return this._parts[_part] || '';
      } else {
        this._parts[_part] = v || null;
        this.build(!build);
        return this;
      }
    };
  }

  function generatePrefixAccessor(_part, _key) {
    return function(v, build) {
      if (v === undefined) {
        return this._parts[_part] || '';
      } else {
        if (v !== null) {
          v = v + '';
          if (v.charAt(0) === _key) {
            v = v.substring(1);
          }
        }
        this._parts[_part] = v;
        this.build(!build);
        return this;
      }
    };
  }
  p.protocol = generateSimpleAccessor('protocol');
  p.username = generateSimpleAccessor('username');
  p.password = generateSimpleAccessor('password');
  p.hostname = generateSimpleAccessor('hostname');
  p.port = generateSimpleAccessor('port');
  p.query = generatePrefixAccessor('query', '?');
  p.fragment = generatePrefixAccessor('fragment', '#');
  p.search = function(v, build) {
    var t = this.query(v, build);
    return typeof t === 'string' && t.length ? ('?' + t) : t;
  };
  p.hash = function(v, build) {
    var t = this.fragment(v, build);
    return typeof t === 'string' && t.length ? ('#' + t) : t;
  };
  p.pathname = function(v, build) {
    if (v === undefined || v === true) {
      var res = this._parts.path || (this._parts.hostname ? '/' : '');
      return v ? (this._parts.urn ? URI.decodeUrnPath : URI.decodePath)(res) : res;
    } else {
      if (this._parts.urn) {
        this._parts.path = v ? URI.recodeUrnPath(v) : '';
      } else {
        this._parts.path = v ? URI.recodePath(v) : '/';
      }
      this.build(!build);
      return this;
    }
  };
  p.path = p.pathname;
  p.href = function(href, build) {
    var key;
    if (href === undefined) {
      return this.toString();
    }
    this._string = '';
    this._parts = URI._parts();
    var _URI = href instanceof URI;
    var _object = typeof href === 'object' && (href.hostname || href.path || href.pathname);
    if (href.nodeName) {
      var attribute = URI.getDomAttribute(href);
      href = href[attribute] || '';
      _object = false;
    }
    if (!_URI && _object && href.pathname !== undefined) {
      href = href.toString();
    }
    if (typeof href === 'string' || href instanceof String) {
      this._parts = URI.parse(String(href), this._parts);
    } else if (_URI || _object) {
      var src = _URI ? href._parts : href;
      for (key in src) {
        if (hasOwn.call(this._parts, key)) {
          this._parts[key] = src[key];
        }
      }
    } else {
      throw new TypeError('invalid input');
    }
    this.build(!build);
    return this;
  };
  p.is = function(what) {
    var ip = false;
    var ip4 = false;
    var ip6 = false;
    var name = false;
    var sld = false;
    var idn = false;
    var punycode = false;
    var relative = !this._parts.urn;
    if (this._parts.hostname) {
      relative = false;
      ip4 = URI.ip4_expression.test(this._parts.hostname);
      ip6 = URI.ip6_expression.test(this._parts.hostname);
      ip = ip4 || ip6;
      name = !ip;
      sld = name && SLD && SLD.has(this._parts.hostname);
      idn = name && URI.idn_expression.test(this._parts.hostname);
      punycode = name && URI.punycode_expression.test(this._parts.hostname);
    }
    switch (what.toLowerCase()) {
      case 'relative':
        return relative;
      case 'absolute':
        return !relative;
      case 'domain':
      case 'name':
        return name;
      case 'sld':
        return sld;
      case 'ip':
        return ip;
      case 'ip4':
      case 'ipv4':
      case 'inet4':
        return ip4;
      case 'ip6':
      case 'ipv6':
      case 'inet6':
        return ip6;
      case 'idn':
        return idn;
      case 'url':
        return !this._parts.urn;
      case 'urn':
        return !!this._parts.urn;
      case 'punycode':
        return punycode;
    }
    return null;
  };
  var _protocol = p.protocol;
  var _port = p.port;
  var _hostname = p.hostname;
  p.protocol = function(v, build) {
    if (v !== undefined) {
      if (v) {
        v = v.replace(/:(\/\/)?$/, '');
        if (!v.match(URI.protocol_expression)) {
          throw new TypeError('Protocol "' + v + '" contains characters other than [A-Z0-9.+-] or doesn\'t start with [A-Z]');
        }
      }
    }
    return _protocol.call(this, v, build);
  };
  p.scheme = p.protocol;
  p.port = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (v !== undefined) {
      if (v === 0) {
        v = null;
      }
      if (v) {
        v += '';
        if (v.charAt(0) === ':') {
          v = v.substring(1);
        }
        if (v.match(/[^0-9]/)) {
          throw new TypeError('Port "' + v + '" contains characters other than [0-9]');
        }
      }
    }
    return _port.call(this, v, build);
  };
  p.hostname = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (v !== undefined) {
      var x = {};
      URI.parseHost(v, x);
      v = x.hostname;
    }
    return _hostname.call(this, v, build);
  };
  p.host = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (v === undefined) {
      return this._parts.hostname ? URI.buildHost(this._parts) : '';
    } else {
      URI.parseHost(v, this._parts);
      this.build(!build);
      return this;
    }
  };
  p.authority = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (v === undefined) {
      return this._parts.hostname ? URI.buildAuthority(this._parts) : '';
    } else {
      URI.parseAuthority(v, this._parts);
      this.build(!build);
      return this;
    }
  };
  p.userinfo = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (v === undefined) {
      if (!this._parts.username) {
        return '';
      }
      var t = URI.buildUserinfo(this._parts);
      return t.substring(0, t.length - 1);
    } else {
      if (v[v.length - 1] !== '@') {
        v += '@';
      }
      URI.parseUserinfo(v, this._parts);
      this.build(!build);
      return this;
    }
  };
  p.resource = function(v, build) {
    var parts;
    if (v === undefined) {
      return this.path() + this.search() + this.hash();
    }
    parts = URI.parse(v);
    this._parts.path = parts.path;
    this._parts.query = parts.query;
    this._parts.fragment = parts.fragment;
    this.build(!build);
    return this;
  };
  p.subdomain = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (v === undefined) {
      if (!this._parts.hostname || this.is('IP')) {
        return '';
      }
      var end = this._parts.hostname.length - this.domain().length - 1;
      return this._parts.hostname.substring(0, end) || '';
    } else {
      var e = this._parts.hostname.length - this.domain().length;
      var sub = this._parts.hostname.substring(0, e);
      var replace = new RegExp('^' + escapeRegEx(sub));
      if (v && v.charAt(v.length - 1) !== '.') {
        v += '.';
      }
      if (v) {
        URI.ensureValidHostname(v);
      }
      this._parts.hostname = this._parts.hostname.replace(replace, v);
      this.build(!build);
      return this;
    }
  };
  p.domain = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (typeof v === 'boolean') {
      build = v;
      v = undefined;
    }
    if (v === undefined) {
      if (!this._parts.hostname || this.is('IP')) {
        return '';
      }
      var t = this._parts.hostname.match(/\./g);
      if (t && t.length < 2) {
        return this._parts.hostname;
      }
      var end = this._parts.hostname.length - this.tld(build).length - 1;
      end = this._parts.hostname.lastIndexOf('.', end - 1) + 1;
      return this._parts.hostname.substring(end) || '';
    } else {
      if (!v) {
        throw new TypeError('cannot set domain empty');
      }
      URI.ensureValidHostname(v);
      if (!this._parts.hostname || this.is('IP')) {
        this._parts.hostname = v;
      } else {
        var replace = new RegExp(escapeRegEx(this.domain()) + '$');
        this._parts.hostname = this._parts.hostname.replace(replace, v);
      }
      this.build(!build);
      return this;
    }
  };
  p.tld = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (typeof v === 'boolean') {
      build = v;
      v = undefined;
    }
    if (v === undefined) {
      if (!this._parts.hostname || this.is('IP')) {
        return '';
      }
      var pos = this._parts.hostname.lastIndexOf('.');
      var tld = this._parts.hostname.substring(pos + 1);
      if (build !== true && SLD && SLD.list[tld.toLowerCase()]) {
        return SLD.get(this._parts.hostname) || tld;
      }
      return tld;
    } else {
      var replace;
      if (!v) {
        throw new TypeError('cannot set TLD empty');
      } else if (v.match(/[^a-zA-Z0-9-]/)) {
        if (SLD && SLD.is(v)) {
          replace = new RegExp(escapeRegEx(this.tld()) + '$');
          this._parts.hostname = this._parts.hostname.replace(replace, v);
        } else {
          throw new TypeError('TLD "' + v + '" contains characters other than [A-Z0-9]');
        }
      } else if (!this._parts.hostname || this.is('IP')) {
        throw new ReferenceError('cannot set TLD on non-domain host');
      } else {
        replace = new RegExp(escapeRegEx(this.tld()) + '$');
        this._parts.hostname = this._parts.hostname.replace(replace, v);
      }
      this.build(!build);
      return this;
    }
  };
  p.directory = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (v === undefined || v === true) {
      if (!this._parts.path && !this._parts.hostname) {
        return '';
      }
      if (this._parts.path === '/') {
        return '/';
      }
      var end = this._parts.path.length - this.filename().length - 1;
      var res = this._parts.path.substring(0, end) || (this._parts.hostname ? '/' : '');
      return v ? URI.decodePath(res) : res;
    } else {
      var e = this._parts.path.length - this.filename().length;
      var directory = this._parts.path.substring(0, e);
      var replace = new RegExp('^' + escapeRegEx(directory));
      if (!this.is('relative')) {
        if (!v) {
          v = '/';
        }
        if (v.charAt(0) !== '/') {
          v = '/' + v;
        }
      }
      if (v && v.charAt(v.length - 1) !== '/') {
        v += '/';
      }
      v = URI.recodePath(v);
      this._parts.path = this._parts.path.replace(replace, v);
      this.build(!build);
      return this;
    }
  };
  p.filename = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (v === undefined || v === true) {
      if (!this._parts.path || this._parts.path === '/') {
        return '';
      }
      var pos = this._parts.path.lastIndexOf('/');
      var res = this._parts.path.substring(pos + 1);
      return v ? URI.decodePathSegment(res) : res;
    } else {
      var mutatedDirectory = false;
      if (v.charAt(0) === '/') {
        v = v.substring(1);
      }
      if (v.match(/\.?\//)) {
        mutatedDirectory = true;
      }
      var replace = new RegExp(escapeRegEx(this.filename()) + '$');
      v = URI.recodePath(v);
      this._parts.path = this._parts.path.replace(replace, v);
      if (mutatedDirectory) {
        this.normalizePath(build);
      } else {
        this.build(!build);
      }
      return this;
    }
  };
  p.suffix = function(v, build) {
    if (this._parts.urn) {
      return v === undefined ? '' : this;
    }
    if (v === undefined || v === true) {
      if (!this._parts.path || this._parts.path === '/') {
        return '';
      }
      var filename = this.filename();
      var pos = filename.lastIndexOf('.');
      var s, res;
      if (pos === -1) {
        return '';
      }
      s = filename.substring(pos + 1);
      res = (/^[a-z0-9%]+$/i).test(s) ? s : '';
      return v ? URI.decodePathSegment(res) : res;
    } else {
      if (v.charAt(0) === '.') {
        v = v.substring(1);
      }
      var suffix = this.suffix();
      var replace;
      if (!suffix) {
        if (!v) {
          return this;
        }
        this._parts.path += '.' + URI.recodePath(v);
      } else if (!v) {
        replace = new RegExp(escapeRegEx('.' + suffix) + '$');
      } else {
        replace = new RegExp(escapeRegEx(suffix) + '$');
      }
      if (replace) {
        v = URI.recodePath(v);
        this._parts.path = this._parts.path.replace(replace, v);
      }
      this.build(!build);
      return this;
    }
  };
  p.segment = function(segment, v, build) {
    var separator = this._parts.urn ? ':' : '/';
    var path = this.path();
    var absolute = path.substring(0, 1) === '/';
    var segments = path.split(separator);
    if (segment !== undefined && typeof segment !== 'number') {
      build = v;
      v = segment;
      segment = undefined;
    }
    if (segment !== undefined && typeof segment !== 'number') {
      throw new Error('Bad segment "' + segment + '", must be 0-based integer');
    }
    if (absolute) {
      segments.shift();
    }
    if (segment < 0) {
      segment = Math.max(segments.length + segment, 0);
    }
    if (v === undefined) {
      return segment === undefined ? segments : segments[segment];
    } else if (segment === null || segments[segment] === undefined) {
      if (isArray(v)) {
        segments = [];
        for (var i = 0, l = v.length; i < l; i++) {
          if (!v[i].length && (!segments.length || !segments[segments.length - 1].length)) {
            continue;
          }
          if (segments.length && !segments[segments.length - 1].length) {
            segments.pop();
          }
          segments.push(v[i]);
        }
      } else if (v || typeof v === 'string') {
        if (segments[segments.length - 1] === '') {
          segments[segments.length - 1] = v;
        } else {
          segments.push(v);
        }
      }
    } else {
      if (v) {
        segments[segment] = v;
      } else {
        segments.splice(segment, 1);
      }
    }
    if (absolute) {
      segments.unshift('');
    }
    return this.path(segments.join(separator), build);
  };
  p.segmentCoded = function(segment, v, build) {
    var segments, i, l;
    if (typeof segment !== 'number') {
      build = v;
      v = segment;
      segment = undefined;
    }
    if (v === undefined) {
      segments = this.segment(segment, v, build);
      if (!isArray(segments)) {
        segments = segments !== undefined ? URI.decode(segments) : undefined;
      } else {
        for (i = 0, l = segments.length; i < l; i++) {
          segments[i] = URI.decode(segments[i]);
        }
      }
      return segments;
    }
    if (!isArray(v)) {
      v = (typeof v === 'string' || v instanceof String) ? URI.encode(v) : v;
    } else {
      for (i = 0, l = v.length; i < l; i++) {
        v[i] = URI.decode(v[i]);
      }
    }
    return this.segment(segment, v, build);
  };
  var q = p.query;
  p.query = function(v, build) {
    if (v === true) {
      return URI.parseQuery(this._parts.query, this._parts.escapeQuerySpace);
    } else if (typeof v === 'function') {
      var data = URI.parseQuery(this._parts.query, this._parts.escapeQuerySpace);
      var result = v.call(this, data);
      this._parts.query = URI.buildQuery(result || data, this._parts.duplicateQueryParameters, this._parts.escapeQuerySpace);
      this.build(!build);
      return this;
    } else if (v !== undefined && typeof v !== 'string') {
      this._parts.query = URI.buildQuery(v, this._parts.duplicateQueryParameters, this._parts.escapeQuerySpace);
      this.build(!build);
      return this;
    } else {
      return q.call(this, v, build);
    }
  };
  p.setQuery = function(name, value, build) {
    var data = URI.parseQuery(this._parts.query, this._parts.escapeQuerySpace);
    if (typeof name === 'string' || name instanceof String) {
      data[name] = value !== undefined ? value : null;
    } else if (typeof name === 'object') {
      for (var key in name) {
        if (hasOwn.call(name, key)) {
          data[key] = name[key];
        }
      }
    } else {
      throw new TypeError('URI.addQuery() accepts an object, string as the name parameter');
    }
    this._parts.query = URI.buildQuery(data, this._parts.duplicateQueryParameters, this._parts.escapeQuerySpace);
    if (typeof name !== 'string') {
      build = value;
    }
    this.build(!build);
    return this;
  };
  p.addQuery = function(name, value, build) {
    var data = URI.parseQuery(this._parts.query, this._parts.escapeQuerySpace);
    URI.addQuery(data, name, value === undefined ? null : value);
    this._parts.query = URI.buildQuery(data, this._parts.duplicateQueryParameters, this._parts.escapeQuerySpace);
    if (typeof name !== 'string') {
      build = value;
    }
    this.build(!build);
    return this;
  };
  p.removeQuery = function(name, value, build) {
    var data = URI.parseQuery(this._parts.query, this._parts.escapeQuerySpace);
    URI.removeQuery(data, name, value);
    this._parts.query = URI.buildQuery(data, this._parts.duplicateQueryParameters, this._parts.escapeQuerySpace);
    if (typeof name !== 'string') {
      build = value;
    }
    this.build(!build);
    return this;
  };
  p.hasQuery = function(name, value, withinArray) {
    var data = URI.parseQuery(this._parts.query, this._parts.escapeQuerySpace);
    return URI.hasQuery(data, name, value, withinArray);
  };
  p.setSearch = p.setQuery;
  p.addSearch = p.addQuery;
  p.removeSearch = p.removeQuery;
  p.hasSearch = p.hasQuery;
  p.normalize = function() {
    if (this._parts.urn) {
      return this.normalizeProtocol(false).normalizePath(false).normalizeQuery(false).normalizeFragment(false).build();
    }
    return this.normalizeProtocol(false).normalizeHostname(false).normalizePort(false).normalizePath(false).normalizeQuery(false).normalizeFragment(false).build();
  };
  p.normalizeProtocol = function(build) {
    if (typeof this._parts.protocol === 'string') {
      this._parts.protocol = this._parts.protocol.toLowerCase();
      this.build(!build);
    }
    return this;
  };
  p.normalizeHostname = function(build) {
    if (this._parts.hostname) {
      if (this.is('IDN') || this.is('punycode')) {
        this._parts.hostname = URI.recodeHostname(this._parts.hostname);
      } else if (this.is('IPv6') && IPv6) {
        this._parts.hostname = IPv6.best(this._parts.hostname);
      }
      this._parts.hostname = this._parts.hostname.toLowerCase();
      this.build(!build);
    }
    return this;
  };
  p.normalizePort = function(build) {
    if (typeof this._parts.protocol === 'string' && this._parts.port === URI.defaultPorts[this._parts.protocol]) {
      this._parts.port = null;
      this.build(!build);
    }
    return this;
  };
  p.normalizePath = function(build) {
    var _path = this._parts.path;
    if (!_path) {
      return this;
    }
    if (this._parts.urn) {
      this._parts.path = URI.recodeUrnPath(this._parts.path);
      this.build(!build);
      return this;
    }
    if (this._parts.path === '/') {
      return this;
    }
    var _was_relative;
    var _leadingParents = '';
    var _parent, _pos;
    if (_path.charAt(0) !== '/') {
      _was_relative = true;
      _path = '/' + _path;
    }
    _path = _path.replace(/(\/(\.\/)+)|(\/\.$)/g, '/').replace(/\/{2,}/g, '/');
    if (_was_relative) {
      _leadingParents = _path.substring(1).match(/^(\.\.\/)+/) || '';
      if (_leadingParents) {
        _leadingParents = _leadingParents[0];
      }
    }
    while (true) {
      _parent = _path.indexOf('/..');
      if (_parent === -1) {
        break;
      } else if (_parent === 0) {
        _path = _path.substring(3);
        continue;
      }
      _pos = _path.substring(0, _parent).lastIndexOf('/');
      if (_pos === -1) {
        _pos = _parent;
      }
      _path = _path.substring(0, _pos) + _path.substring(_parent + 3);
    }
    if (_was_relative && this.is('relative')) {
      _path = _leadingParents + _path.substring(1);
    }
    _path = URI.recodePath(_path);
    this._parts.path = _path;
    this.build(!build);
    return this;
  };
  p.normalizePathname = p.normalizePath;
  p.normalizeQuery = function(build) {
    if (typeof this._parts.query === 'string') {
      if (!this._parts.query.length) {
        this._parts.query = null;
      } else {
        this.query(URI.parseQuery(this._parts.query, this._parts.escapeQuerySpace));
      }
      this.build(!build);
    }
    return this;
  };
  p.normalizeFragment = function(build) {
    if (!this._parts.fragment) {
      this._parts.fragment = null;
      this.build(!build);
    }
    return this;
  };
  p.normalizeSearch = p.normalizeQuery;
  p.normalizeHash = p.normalizeFragment;

  function _generateNormalizer(hostnameRecoder, encoder, decoder) {
    return function() {
      var r = URI.recodeHostname
      var e = URI.encode;
      var d = URI.decode;
      URI.encode = encoder;
      URI.decode = decoder;
      try {
        this.normalize();
      } finally {
        URI.recodeHostname = r;
        URI.encode = e;
        URI.decode = d;
      }
      return this;
    };
  }
  p.iso8859 = _generateNormalizer(URI._defaultRecodeHostname, escape, decodeURIComponent);
  p.unicode = _generateNormalizer(URI._defaultRecodeHostname, strictEncodeURIComponent, unescape);
  p.iri = _generateNormalizer(recodeIRIHostname, encodeIRIComponent, decodeURIComponent);
  p.readable = function() {
    var uri = this.clone();
    uri.username('').password('').normalize();
    var t = '';
    if (uri._parts.protocol) {
      t += uri._parts.protocol + (uri._parts.urn ? ':' : '://');
    }
    if (uri._parts.hostname) {
      if (uri.is('punycode') && punycode) {
        t += punycode.toUnicode(uri._parts.hostname);
        if (uri._parts.port) {
          t += ':' + uri._parts.port;
        }
      } else {
        t += uri.host();
      }
    }
    if (uri._parts.hostname && uri._parts.path && uri._parts.path.charAt(0) !== '/') {
      t += '/';
    }
    t += uri.path(true);
    if (uri._parts.query) {
      var q = '';
      for (var i = 0, qp = uri._parts.query.split('&'), l = qp.length; i < l; i++) {
        var kv = (qp[i] || '').split('=');
        q += '&' + URI.decodeQuery(kv[0], this._parts.escapeQuerySpace).replace(/&/g, '%26');
        if (kv[1] !== undefined) {
          q += '=' + URI.decodeQuery(kv[1], this._parts.escapeQuerySpace).replace(/&/g, '%26');
        }
      }
      t += '?' + q.substring(1);
    }
    t += URI.decodeQuery(uri.hash(), true);
    return t;
  };
  p.absoluteTo = function(base) {
    var resolved = this.clone();
    var properties = ['protocol', 'username', 'password', 'hostname', 'port'];
    var basedir, i, p;
    if (this._parts.urn) {
      throw new Error('URNs do not have any generally defined hierarchical components');
    }
    if (!(base instanceof URI)) {
      base = new URI(base);
    }
    if (!resolved._parts.protocol) {
      resolved._parts.protocol = base._parts.protocol;
    }
    if (this._parts.hostname) {
      return resolved;
    }
    for (i = 0;
         (p = properties[i]); i++) {
      resolved._parts[p] = base._parts[p];
    }
    if (!resolved._parts.path) {
      resolved._parts.path = base._parts.path;
      if (!resolved._parts.query) {
        resolved._parts.query = base._parts.query;
      }
    } else if (resolved._parts.path.substring(-2) === '..') {
      resolved._parts.path += '/';
    }
    if (resolved.path().charAt(0) !== '/') {
      basedir = base.directory();
      resolved._parts.path = (basedir ? (basedir + '/') : '') + resolved._parts.path;
      resolved.normalizePath();
    }
    resolved.build();
    return resolved;
  };
  p.relativeTo = function(base) {
    var relative = this.clone().normalize();
    var relativeParts, baseParts, common, relativePath, basePath;
    if (relative._parts.urn) {
      throw new Error('URNs do not have any generally defined hierarchical components');
    }
    base = new URI(base).normalize();
    relativeParts = relative._parts;
    baseParts = base._parts;
    relativePath = relative.path();
    basePath = base.path();
    if (relativePath.charAt(0) !== '/') {
      throw new Error('URI is already relative');
    }
    if (basePath.charAt(0) !== '/') {
      throw new Error('Cannot calculate a URI relative to another relative URI');
    }
    if (relativeParts.protocol === baseParts.protocol) {
      relativeParts.protocol = null;
    }
    if (relativeParts.username !== baseParts.username || relativeParts.password !== baseParts.password) {
      return relative.build();
    }
    if (relativeParts.protocol !== null || relativeParts.username !== null || relativeParts.password !== null) {
      return relative.build();
    }
    if (relativeParts.hostname === baseParts.hostname && relativeParts.port === baseParts.port) {
      relativeParts.hostname = null;
      relativeParts.port = null;
    } else {
      return relative.build();
    }
    if (relativePath === basePath) {
      relativeParts.path = '';
      return relative.build();
    }
    common = URI.commonPath(relative.path(), base.path());
    if (!common) {
      return relative.build();
    }
    var parents = baseParts.path.substring(common.length).replace(/[^\/]*$/, '').replace(/.*?\//g, '../');
    relativeParts.path = parents + relativeParts.path.substring(common.length);
    return relative.build();
  };
  p.equals = function(uri) {
    var one = this.clone();
    var two = new URI(uri);
    var one_map = {};
    var two_map = {};
    var checked = {};
    var one_query, two_query, key;
    one.normalize();
    two.normalize();
    if (one.toString() === two.toString()) {
      return true;
    }
    one_query = one.query();
    two_query = two.query();
    one.query('');
    two.query('');
    if (one.toString() !== two.toString()) {
      return false;
    }
    if (one_query.length !== two_query.length) {
      return false;
    }
    one_map = URI.parseQuery(one_query, this._parts.escapeQuerySpace);
    two_map = URI.parseQuery(two_query, this._parts.escapeQuerySpace);
    for (key in one_map) {
      if (hasOwn.call(one_map, key)) {
        if (!isArray(one_map[key])) {
          if (one_map[key] !== two_map[key]) {
            return false;
          }
        } else if (!arraysEqual(one_map[key], two_map[key])) {
          return false;
        }
        checked[key] = true;
      }
    }
    for (key in two_map) {
      if (hasOwn.call(two_map, key)) {
        if (!checked[key]) {
          return false;
        }
      }
    }
    return true;
  };
  p.duplicateQueryParameters = function(v) {
    this._parts.duplicateQueryParameters = !!v;
    return this;
  };
  p.escapeQuerySpace = function(v) {
    this._parts.escapeQuerySpace = !!v;
    return this;
  };
  return URI;
}));
var TC_COURSE_ID, TC_COURSE_NAME, TC_COURSE_DESC, TC_RECORD_STORES;
var TCAPI_STATUS = "",
  TCAPI_STATUS_CHANGED = false,
  TCAPI_SCORE = {},
  TCAPI_COMPLETION_STATUS = "",
  TCAPI_SATISFACTION_STATUS = null,
  TCAPI_UPDATES_PENDING = false,
  TCAPI_IN_PROGRESS = false,
  TCAPI_NO_ERROR = "",
  TCAPI_VERB_COMPLETED = "completed",
  TCAPI_VERB_EXPERIENCED = "experienced",
  TCAPI_VERB_ATTEMPTED = "attempted",
  TCAPI_VERB_ANSWERED = "answered",
  TCAPI_VERB_PASSED = "passed",
  TCAPI_VERB_FAILED = "failed",
  TCAPI_INIT_VERB = TCAPI_VERB_ATTEMPTED,
  TCAPI_INTERACTION = "http://adlnet.gov/expapi/activities/cmi.interaction",
  TCAPI_INTERACTION_TYPE_TRUE_FALSE = "true-false",
  TCAPI_INTERACTION_TYPE_CHOICE = "choice",
  TCAPI_INTERACTION_TYPE_FILL_IN = "fill-in",
  TCAPI_INTERACTION_TYPE_MATCHING = "matching",
  TCAPI_INTERACTION_TYPE_PERFORMANCE = "performance",
  TCAPI_INTERACTION_TYPE_SEQUENCING = "sequencing",
  TCAPI_INTERACTION_TYPE_LIKERT = "likert",
  TCAPI_INTERACTION_TYPE_NUMERIC = "numeric",
  TCAPI_STATE_BOOKMARK = "bookmark",
  TCAPI_STATE_TOTAL_TIME = "cumulative_time",
  TCAPI_STATE_SUSPEND_DATA = "suspend_data",
  TCAPI_ERROR_INVALID_PREFERENCE = 0,
  TCAPI_ERROR_INVALID_TIMESPAN = 1,
  TCAPI_FUNC_NOOP = function() {},
  intTCAPIError, strTCAPIErrorString, strTCAPIErrorDiagnostic;
var tincan;
var tcapi_cache;

function TCAPI_Initialize() {
  WriteToDebug("In TCAPI_Initialize");
  tcapi_cache = {
    totalPrevDuration: null,
    statementQueue: []
  };
  TinCan.prototype.log = TinCan.LRS.prototype.log = function(msg, src) {
    src = src || this.LOG_SRC || "TinCan";
    WriteToDebug("TinCan." + src + ": " + msg);
  };
  try {
    tincan = new TinCan({
      url: location.href,
      recordStores: TC_RECORD_STORES,
      activity: {
        id: TC_COURSE_ID,
        definition: {
          name: TC_COURSE_NAME,
          description: TC_COURSE_DESC
        }
      }
    });
  } catch (ex) {
    WriteToDebug("TCAPI_Initialize - TinCan construction failed: " + JSON.stringify(ex));
    return;
  }
  if (tincan.recordStores.length === 0) {
    WriteToDebug("TCAPI_Initialize - resulted in no LRS: DATA CANNOT BE STORED");
    return;
  }
  WriteToDebug("TCAPI_Initialize - fetching cumulative time from state: " + TCAPI_STATE_TOTAL_TIME);
  tincan.getState(TCAPI_STATE_TOTAL_TIME, {
    callback: function(err, state) {
      WriteToDebug("TCAPI_Initialize - getState callback");
      var contents;
      if (err !== null) {
        WriteToDebug("TCAPI_Initialize - getState callback: " + err.responseText + " (" + err.status + ")");
        return;
      }
      WriteToDebug("TCAPI_Initialize - getState callback - state: " + state);
      if (state !== null && state.contents !== null && typeof(state.contents) == "string" && state.contents.match(/^\d+$/)) {
        tcapi_cache.totalPrevDuration = Number(state.contents);
      } else if (state !== null && state.contents !== null && typeof(state.contents) == "number") {
        tcapi_cache.totalPrevDuration = state.contents;
      } else {
        tcapi_cache.totalPrevDuration = 0;
      }
    }
  });
  TCAPI_STATUS = TCAPI_INIT_VERB;
  TCAPI_IN_PROGRESS = true;
  WriteToDebug("TCAPI_Initialize - record initial launch statement");
  tincan.sendStatement({
    verb: TCAPI_INIT_VERB,
    inProgress: TCAPI_IN_PROGRESS
  }, function(results, statement) {
    if (results[0].err !== null) {
      WriteToDebug("TCAPI_Initialize - record initial launch statement - err: " + results[0].err.responseText + " (" + results[0].err.status + ")");
      return;
    }
    WriteToDebug("TCAPI_Initialize - record initial launch statement success: " + statement.id);
  });
  InitializeExecuted(true, "");
  return true;
}

function _TCAPI_SetStateSafe(key, value) {
  var result;
  try {
    result = tincan.setState(key, value);
  } catch (ex) {
    WriteToDebug("In _TCAPI_SetStateSafe - caught exception from setState: " + ex.message);
  }
  return result;
}

function TCAPI_GetStudentID() {
  WriteToDebug("In TCAPI_GetStudentID");
  if (tincan.actor.mbox !== null) {
    return tincan.actor.mbox;
  }
  if (tincan.actor.mbox_sha1sum !== null) {
    return tincan.actor.mbox_sha1sum;
  }
  if (tincan.actor.openid !== null) {
    return tincan.actor.openid;
  }
  if (tincan.actor.account !== null) {
    return tincan.actor.account.name;
  }
  return null;
}

function TCAPI_GetStudentName() {
  WriteToDebug("In TCAPI_GetStudentName");
  return tincan.actor !== null ? tincan.actor.toString() : "";
}

function TCAPI_GetBookmark() {
  WriteToDebug("In TCAPI_GetBookmark");
  var bookmark = "",
    getStateResult = tincan.getState(TCAPI_STATE_BOOKMARK);
  if (getStateResult.state !== null) {
    bookmark = getStateResult.state.contents;
  }
  return bookmark;
}

function TCAPI_SetBookmark(value, name) {
  WriteToDebug("In TCAPI_SetBookmark - value: " + value + ", name: " + name);
  _TCAPI_SetStateSafe(TCAPI_STATE_BOOKMARK, value);
  WriteToDebug("In TCAPI_SetBookmark - sending statement: " + value);
  tincan.sendStatement({
    verb: TCAPI_VERB_EXPERIENCED,
    object: {
      id: tincan.activity.id + "/" + value,
      definition: {
        name: {
          "en-US": ((name !== undefined && name !== "") ? name : value)
        }
      }
    },
    context: {
      contextActivities: {
        parent: tincan.activity
      }
    }
  }, function(results, statement) {
    if (results[0].err !== null) {
      WriteToDebug("TCAPI_SetBookmark - sending statement: " + value + " - err: " + results[0].err.responseText + " (" + results[0].err.status + ")");
      return;
    }
    WriteToDebug("TCAPI_SetBookmark - sending statement success: " + value + " - id: " + statement.id);
  });
  return true;
}

function TCAPI_GetDataChunk() {
  WriteToDebug("In TCAPI_GetDataChunk");
  var data = "",
    getStateResult = tincan.getState(TCAPI_STATE_SUSPEND_DATA);
  if (getStateResult.state !== null) {
    data = getStateResult.state.contents;
  }
  return data;
}

function TCAPI_SetDataChunk(value) {
  WriteToDebug("In TCAPI_SetDataChunk");
  _TCAPI_SetStateSafe(TCAPI_STATE_SUSPEND_DATA, value);
  return true;
}

function TCAPI_CommitData() {
  WriteToDebug("In TCAPI_CommitData - TCAPI_STATUS:" + TCAPI_STATUS);
  WriteToDebug("In TCAPI_CommitData - TCAPI_UPDATES_PENDING: " + TCAPI_UPDATES_PENDING);
  var stmt;
  if (TCAPI_UPDATES_PENDING) {
    stmt = {
      verb: TCAPI_STATUS,
      inProgress: TCAPI_IN_PROGRESS,
      result: {}
    };
    if (TCAPI_COMPLETION_STATUS !== '' || !TCAPI_IN_PROGRESS) {
      stmt.result.duration = ConvertMilliSecondsIntoSCORM2004Time(GetSessionAccumulatedTime() + TCAPI_GetPreviouslyAccumulatedTime());
    }
    if (TCAPI_COMPLETION_STATUS !== '') {
      stmt.result.completion = true;
    }
    if (TCAPI_SATISFACTION_STATUS !== null) {
      stmt.result.success = TCAPI_SATISFACTION_STATUS;
    }
    if (typeof TCAPI_SCORE.raw !== "undefined") {
      stmt.result.score = TCAPI_SCORE;
    }
    tcapi_cache.statementQueue.push(stmt);
    TCAPI_UPDATES_PENDING = false;
  }
  if (tcapi_cache.statementQueue.length > 0) {
    tincan.sendStatements(tcapi_cache.statementQueue);
    tcapi_cache.statementQueue = [];
  }
  return true;
}

function TCAPI_Finish(exitType, statusWasSet) {
  WriteToDebug("In TCAPI_Finish - exitType: " + exitType);
  if (exitType === EXIT_TYPE_SUSPEND) {
    _TCAPI_SetStateSafe(TCAPI_STATE_TOTAL_TIME, TCAPI_GetPreviouslyAccumulatedTime() + GetSessionAccumulatedTime());
    TCAPI_SetSuspended();
  }
  TCAPI_CommitData();
  return true;
}

function TCAPI_GetAudioPlayPreference() {
  WriteToDebug("In TCAPI_GetAudioPlayPreference");
  var intTempPreference = 0,
    getStateResult;
  TCAPI_ClearErrorInfo();
  getStateResult = tincan.getState("cmi.student_preference.audio");
  if (getStateResult.state !== null) {
    intTempPreference = getStateResult.state.contents;
  }
  intTempPreference = parseInt(intTempPreference, 10);
  WriteToDebug("intTempPreference=" + intTempPreference);
  if (intTempPreference > 0) {
    WriteToDebug("Returning On");
    return PREFERENCE_ON;
  } else if (intTempPreference == 0) {
    WriteToDebug("Returning Default");
    return PREFERENCE_DEFAULT;
  } else if (intTempPreference < 0) {
    WriteToDebug("returning Off");
    return PREFERENCE_OFF;
  }
  WriteToDebug("Error: Invalid preference");
  TCAPI_SetErrorInfoManually(TCAPI_ERROR_INVALID_PREFERENCE, "Invalid audio preference received from LMS", "intTempPreference=" + intTempPreference);
  return null;
}

function TCAPI_GetAudioVolumePreference() {
  WriteToDebug("In TCAPI_GetAudioVollumePreference");
  var intTempPreference = 100,
    getStateResult;
  TCAPI_ClearErrorInfo();
  getStateResult = tincan.getState("cmi.student_preference.audio");
  if (getStateResult.state !== null) {
    intTempPreference = getStateResult.state.contents;
  }
  WriteToDebug("intTempPreference=" + intTempPreference);
  intTempPreference = parseInt(intTempPreference, 10);
  if (intTempPreference <= 0) {
    WriteToDebug("Setting to 100");
    intTempPreference = 100;
  }
  if (intTempPreference > 100) {
    WriteToDebug("ERROR: invalid preference");
    TCAPI_SetErrorInfoManually(TCAPI_ERROR_INVALID_PREFERENCE, "Invalid audio preference received from LMS", "intTempPreference=" + intTempPreference);
    return null;
  }
  WriteToDebug("Returning " + intTempPreference);
  return intTempPreference;
}

function TCAPI_SetAudioPreference(PlayPreference, intPercentOfMaxVolume) {
  WriteToDebug("In TCAPI_SetAudioPreference PlayPreference=" + PlayPreference + ", intPercentOfMaxVolume=" + intPercentOfMaxVolume);
  TCAPI_ClearErrorInfo();
  if (PlayPreference == PREFERENCE_OFF) {
    WriteToDebug("Setting percent to -1 - OFF");
    intPercentOfMaxVolume = -1;
  }
  _TCAPI_SetStateSafe("cmi.student_preference.audio", intPercentOfMaxVolume);
}

function TCAPI_SetLanguagePreference(strLanguage) {
  WriteToDebug("In TCAPI_SetLanguagePreference strLanguage=" + strLanguage);
  TCAPI_ClearErrorInfo();
  _TCAPI_SetStateSafe("cmi.student_preference.language", strLanguage);
}

function TCAPI_GetLanguagePreference() {
  WriteToDebug("In TCAPI_GetLanguagePreference");
  var pref, getStateResult;
  TCAPI_ClearErrorInfo();
  getStateResult = tincan.getState("cmi.student_preference.language");
  if (getStateResult.state !== null) {
    pref = getStateResult.state.contents;
  }
  return pref;
}

function TCAPI_SetSpeedPreference(intPercentOfMax) {
  WriteToDebug("In TCAPI_SetSpeedPreference intPercentOfMax=" + intPercentOfMax);
  var intTCAPISpeed;
  TCAPI_ClearErrorInfo();
  intTCAPISpeed = (intPercentOfMax * 2) - 100;
  WriteToDebug("intTCAPISpeed=" + intTCAPISpeed);
  _TCAPI_SetStateSafe("cmi.student_preference.speed", intTCAPISpeed);
}

function TCAPI_GetSpeedPreference() {
  WriteToDebug("In TCAPI_GetSpeedPreference");
  var intTCAPISpeed = 100,
    intPercentOfMax, getStateResult;
  TCAPI_ClearErrorInfo();
  getStateResult = tincan.getState("cmi.student_preference.speed");
  if (getStateResult.state !== null) {
    intTCAPISpeed = getStateResult.state.contents;
  }
  WriteToDebug("intTCAPISpeed=" + intTCAPISpeed);
  if (!ValidInteger(intTCAPISpeed)) {
    WriteToDebug("ERROR - invalid integer");
    TCAPI_SetErrorInfoManually(TCAPI_ERROR_INVALID_SPEED, "Invalid speed preference received from LMS - not an integer", "intTCAPISpeed=" + intTCAPISpeed);
    return null;
  }
  intTCAPISpeed = parseInt(intTCAPISpeed, 10);
  if (intTCAPISpeed < -100 || intTCAPISpeed > 100) {
    WriteToDebug("ERROR - out of range");
    TCAPI_SetErrorInfoManually(TCAPI_ERROR_INVALID_SPEED, "Invalid speed preference received from LMS - out of range", "intTCAPISpeed=" + intTCAPISpeed);
    return null;
  }
  intPercentOfMax = (intTCAPISpeed + 100) / 2;
  intPercentOfMax = parseInt(intPercentOfMax, 10);
  WriteToDebug("Returning " + intPercentOfMax);
  return intPercentOfMax;
}

function TCAPI_SetTextPreference(intPreference) {
  WriteToDebug("In TCAPI_SetTextPreference intPreference=" + intPreference);
  TCAPI_ClearErrorInfo();
  _TCAPI_SetStateSafe("cmi.student_preference.text", intPreference);
}

function TCAPI_GetTextPreference() {
  WriteToDebug("In TCAPI_GetTextPreference");
  var intTempPreference = 0,
    getStateResult;
  TCAPI_ClearErrorInfo();
  getStateResult = tincan.getState("cmi.student_preference.text");
  if (getStateResult.state !== null) {
    intTempPreference = getStateResult.state.contents;
  }
  intTempPreference = parseInt(intTempPreference, 10);
  WriteToDebug("intTempPreference=" + intTempPreference);
  if (intTempPreference > 0) {
    WriteToDebug("Returning On");
    return PREFERENCE_ON;
  } else if (intTempPreference == 0 || intTempPreference == "") {
    WriteToDebug("Returning Default");
    return PREFERENCE_DEFAULT;
  } else if (intTempPreference < 0) {
    WriteToDebug("returning Off");
    return PREFERENCE_OFF;
  }
  WriteToDebug("Error: Invalid preference");
  TCAPI_SetErrorInfoManually(TCAPI_ERROR_INVALID_PREFERENCE, "Invalid text preference received from LMS", "intTempPreference=" + intTempPreference);
  return null;
}

function TCAPI_GetPreviouslyAccumulatedTime() {
  WriteToDebug("In TCAPI_GetPreviouslyAccumulatedTime");
  var data = 0,
    getStateResult;
  WriteToDebug("In TCAPI_GetPreviouslyAccumulatedTime - cached: " + tcapi_cache.totalPrevDuration);
  if (tcapi_cache.totalPrevDuration === null) {
    getStateResult = tincan.getState(TCAPI_STATE_TOTAL_TIME);
    if (getStateResult.state !== null) {
      data = Number(getStateResult.state.contents);
    }
    tcapi_cache.totalPrevDuration = (data === NaN) ? 0 : data;
  }
  return tcapi_cache.totalPrevDuration;
}

function TCAPI_SaveTime(intMilliSeconds) {
  WriteToDebug("In TCAPI_SaveTime");
  return true;
}

function TCAPI_GetMaxTimeAllowed() {
  WriteToDebug("In TCAPI_GetMaxTimeAllowed");
  return null;
}

function TCAPI_SetScore(intScore, intMaxScore, intMinScore) {
  WriteToDebug("In TCAPI_SetScore intScore=" + intScore + ", intMaxScore=" + intMaxScore + ", intMinScore=" + intMinScore);
  TCAPI_ClearErrorInfo();
  TCAPI_SCORE["raw"] = intScore;
  TCAPI_SCORE["max"] = intMaxScore;
  TCAPI_SCORE["min"] = intMinScore;
  WriteToDebug("Returning " + TCAPI_SCORE);
  TCAPI_UPDATES_PENDING = true;
  return true;
}

function TCAPI_GetScore() {
  WriteToDebug("In TCAPI_GetScore");
  TCAPI_ClearErrorInfo();
  WriteToDebug("Returning " + TCAPI_SCORE['raw']);
  return TCAPI_SCORE['raw'];
}

function TCAPI_SetPointBasedScore(intScore, intMaxScore, intMinScore) {
  WriteToDebug("TCAPI_SetPointBasedScore - TCAPI does not support SetPointBasedScore, falling back to SetScore");
  return TCAPI_SetScore(intScore, intMaxScore, intMinScore);
}

function TCAPI_GetScaledScore(intScore, intMaxScore, intMinScore) {
  WriteToDebug("TCAPI_GetScaledScore - TCAPI does not support GetScaledScore, returning false");
  return false;
}

function TCAPI_RecordInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, TCAPIInteractionType, strAlternateResponse, strAlternateCorrectResponse, intScoreRaw, intScoreMin) {
  var blnTempResult, intInteractionIndex, strResult, actObj = {},
    stmt, interactionActivityId = tincan.activity.id + "-" + strID,
    interactionActivityType = TCAPI_INTERACTION;
  TCAPI_ClearErrorInfo();
  switch (TCAPIInteractionType) {
    case "true-false":
      actObj = {
        id: interactionActivityId,
        definition: {
          description: {
            'en-US': strDescription
          },
          type: interactionActivityType,
          interactionType: "true-false",
          correctResponsesPattern: [strCorrectResponse]
        }
      };
      break;
    case "choice":
      actObj = {
        id: interactionActivityId,
        definition: {
          description: {
            'en-US': strDescription
          },
          type: interactionActivityType,
          interactionType: "choice",
          correctResponsesPattern: [strCorrectResponse]
        }
      };
      break;
    case "fill-in":
      actObj = {
        id: interactionActivityId,
        definition: {
          description: {
            'en-US': strDescription
          },
          type: interactionActivityType,
          interactionType: "fill-in",
          correctResponsesPattern: [strCorrectResponse]
        }
      };
      break;
    case "matching":
      actObj = {
        id: interactionActivityId,
        definition: {
          description: {
            'en-US': strDescription
          },
          type: interactionActivityType,
          interactionType: "matching",
          correctResponsesPattern: [strCorrectResponse]
        }
      };
      break;
    case "performance":
      actObj = {
        id: interactionActivityId,
        definition: {
          description: {
            'en-US': strDescription
          },
          type: interactionActivityType,
          interactionType: "performance",
          correctResponsesPattern: [strCorrectResponse]
        }
      };
      break;
    case "sequencing":
      actObj = {
        id: interactionActivityId,
        definition: {
          description: {
            'en-US': strDescription
          },
          type: interactionActivityType,
          interactionType: "sequencing",
          correctResponsesPattern: [strCorrectResponse]
        }
      };
      break;
    case "likert":
      actObj = {
        id: interactionActivityId,
        definition: {
          description: {
            'en-US': strDescription
          },
          type: interactionActivityType,
          interactionType: "likert",
          correctResponsesPattern: [strCorrectResponse]
        }
      };
      break;
    case "numeric":
      actObj = {
        id: interactionActivityId,
        definition: {
          description: {
            'en-US': strDescription
          },
          type: interactionActivityType,
          interactionType: "numeric",
          correctResponsesPattern: [strCorrectResponse]
        }
      };
      break;
    case "other":
      actObj = {
        id: interactionActivityId,
        definition: {
          description: {
            'en-US': strDescription
          },
          type: interactionActivityType,
          interactionType: "other",
          correctResponsesPattern: [strCorrectResponse]
        }
      };
      break;
    default:
      WriteToDebug("TCAPI_RecordInteraction received an invalid TCPAIInteractionType of " + TCAPIInteractionType);
      return false;
  }
  if (actObj.id !== null) {
    stmt = {
      verb: TCAPI_VERB_ANSWERED,
      object: actObj,
      context: {
        contextActivities: {
          parent: tincan.activity,
          grouping: {
            id: tincan.activity.id + '-' + strLearningObjectiveID
          }
        }
      }
    };
    if (strResponse !== null) {
      if (intScoreRaw != undefined && intScoreMin != undefined) {
        var scaledScore = 0;
        if (intWeighting != 0) scaledScore = intScoreRaw / intWeighting;
        if (scaledScore < -1) scaledScore = -1;
        if (scaledScore > 1) scaledScore = 1;
        stmt.result = {
          score: {
            scaled: scaledScore,
            raw: intScoreRaw,
            min: intScoreMin,
            max: intWeighting
          },
          response: strResponse,
          success: blnCorrect
        };
      } else stmt.result = {
        response: strResponse,
        success: blnCorrect
      };
    }
    tcapi_cache.statementQueue.push(stmt);
  }
  return true;
}

function TCAPI_RecordTrueFalseInteraction(strID, blnResponse, blnCorrect, blnCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin) {
  WriteToDebug("In TCAPI_RecordTrueFalseInteraction strID=" + strID + ", strResponse=" + strResponse + ", blnCorrect=" + blnCorrect + ", strCorrectResponse=" + strCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID + ", dtmTime=" + dtmTime);
  var strResponse = "",
    strCorrectResponse = null;
  if (blnResponse === true) {
    strResponse = "true";
  } else {
    strResponse = "false";
  }
  if (blnCorrectResponse === true) {
    strCorrectResponse = "true";
  } else if (blnCorrectResponse === false) {
    strCorrectResponse = "false";
  }
  return TCAPI_RecordInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, TCAPI_INTERACTION_TYPE_TRUE_FALSE, strResponse, strCorrectResponse, intScoreRaw, intScoreMin);
}

function TCAPI_RecordMultipleChoiceInteraction(strID, aryResponse, blnCorrect, aryCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin) {
  WriteToDebug("In TCAPI_RecordMultipleChoiceInteraction strID=" + strID + ", aryResponse=" + aryResponse + ", blnCorrect=" + blnCorrect + ", aryCorrectResponse=" + aryCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID + ", dtmTime=" + dtmTime);
  var strResponse = "",
    strResponseLong = "",
    strCorrectResponse = "",
    strCorrectResponseLong = "";
  for (var i = 0; i < aryResponse.length; i++) {
    if (strResponse.length > 0) {
      strResponse += "[,]";
    }
    if (strResponseLong.length > 0) {
      strResponseLong += "[,]";
    }
    strResponse += aryResponse[i].Short;
    strResponseLong += aryResponse[i].Long;
  }
  for (var i = 0; i < aryCorrectResponse.length; i++) {
    if (strCorrectResponse.length > 0) {
      strCorrectResponse += "[,]";
    }
    if (strCorrectResponseLong.length > 0) {
      strCorrectResponseLong += "[,]";
    }
    strCorrectResponse += aryCorrectResponse[i].Short;
    strCorrectResponseLong += aryCorrectResponse[i].Long;
  }
  return TCAPI_RecordInteraction(strID, strResponseLong, blnCorrect, strCorrectResponseLong, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, TCAPI_INTERACTION_TYPE_CHOICE, strResponse, strCorrectResponse, intScoreRaw, intScoreMin);
}

function TCAPI_RecordFillInInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin) {
  WriteToDebug("In TCAPI_RecordFillInInteraction strID=" + strID + ", strResponse=" + strResponse + ", blnCorrect=" + blnCorrect + ", strCorrectResponse=" + strCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID + ", dtmTime=" + dtmTime);
  strResponse = new String(strResponse);
  if (strResponse.length > 255) {
    strResponse = strResponse.substr(0, 255);
  }
  if (strCorrectResponse === null) {
    strCorrectResponse = "";
  }
  strCorrectResponse = new String(strCorrectResponse);
  if (strCorrectResponse.length > 255) {
    strCorrectResponse = strCorrectResponse.substr(0, 255);
  }
  return TCAPI_RecordInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, TCAPI_INTERACTION_TYPE_FILL_IN, strResponse, strCorrectResponse, intScoreRaw, intScoreMin);
}

function TCAPI_RecordMatchingInteraction(strID, aryResponse, blnCorrect, aryCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin) {
  WriteToDebug("In TCAPI_RecordMatchingInteraction strID=" + strID + ", aryResponse=" + aryResponse + ", blnCorrect=" + blnCorrect + ", aryCorrectResponse=" + aryCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID + ", dtmTime=" + dtmTime);
  var strResponse = "";
  strResponseLong = "", strCorrectResponse = "", strCorrectResponseLong = "";
  for (var i = 0; i < aryResponse.length; i++) {
    if (strResponse.length > 0) {
      strResponse += "[,]";
    }
    if (strResponseLong.length > 0) {
      strResponseLong += "[,]";
    }
    strResponse += aryResponse[i].Source.Short + "[.]" + aryResponse[i].Target.Short;
    strResponseLong += aryResponse[i].Source.Long + "[.]" + aryResponse[i].Target.Long;
  }
  for (var i = 0; i < aryCorrectResponse.length; i++) {
    if (strCorrectResponse.length > 0) {
      strCorrectResponse += "[,]";
    }
    if (strCorrectResponseLong.length > 0) {
      strCorrectResponseLong += "[,]";
    }
    strCorrectResponse += aryCorrectResponse[i].Source.Short + "[.]" + aryCorrectResponse[i].Target.Short;
    strCorrectResponseLong += aryCorrectResponse[i].Source.Long + "[.]" + aryCorrectResponse[i].Target.Long;
  }
  return TCAPI_RecordInteraction(strID, strResponseLong, blnCorrect, strCorrectResponseLong, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, TCAPI_INTERACTION_TYPE_MATCHING, strResponse, strCorrectResponse, intScoreRaw, intScoreMin);
}

function TCAPI_RecordPerformanceInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin) {
  WriteToDebug("In TCAPI_RecordPerformanceInteraction strID=" + strID + ", strResponse=" + strResponse + ", blnCorrect=" + blnCorrect + ", strCorrectResponse=" + strCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID + ", dtmTime=" + dtmTime);
  strResponse = new String(strResponse);
  if (strResponse.length > 255) {
    strResponse = strResponse.substr(0, 255);
  }
  if (strCorrectResponse == null) {
    strCorrectResponse = "";
  }
  strCorrectResponse = new String(strCorrectResponse);
  if (strCorrectResponse.length > 255) {
    strCorrectResponse = strCorrectResponse.substr(0, 255);
  }
  return TCAPI_RecordInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, TCAPI_INTERACTION_TYPE_PERFORMANCE, strResponse, strCorrectResponse, intScoreRaw, intScoreMin);
}

function TCAPI_RecordSequencingInteraction(strID, aryResponse, blnCorrect, aryCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin) {
  WriteToDebug("In TCAPI_RecordSequencingInteraction strID=" + strID + ", aryResponse=" + aryResponse + ", blnCorrect=" + blnCorrect + ", aryCorrectResponse=" + aryCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID + ", dtmTime=" + dtmTime);
  var strResponse = "",
    strResponseLong = "",
    strCorrectResponse = "",
    strCorrectResponseLong = "";
  for (var i = 0; i < aryResponse.length; i++) {
    if (strResponse.length > 0) {
      strResponse += "[,]";
    }
    if (strResponseLong.length > 0) {
      strResponseLong += "[,]";
    }
    strResponse += aryResponse[i].Short;
    strResponseLong += aryResponse[i].Long;
  }
  for (var i = 0; i < aryCorrectResponse.length; i++) {
    if (strCorrectResponse.length > 0) {
      strCorrectResponse += "[,]";
    }
    if (strCorrectResponseLong.length > 0) {
      strCorrectResponseLong += "[,]";
    }
    strCorrectResponse += aryCorrectResponse[i].Short;
    strCorrectResponseLong += aryCorrectResponse[i].Long;
  }
  return TCAPI_RecordInteraction(strID, strResponseLong, blnCorrect, strCorrectResponseLong, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, TCAPI_INTERACTION_TYPE_SEQUENCING, strResponse, strCorrectResponse, intScoreRaw, intScoreMin);
}

function TCAPI_RecordLikertInteraction(strID, response, blnCorrect, correctResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin) {
  WriteToDebug("In TCAPI_RecordLikertInteraction strID=" + strID + ", response=" + response + ", blnCorrect=" + blnCorrect + ", correctResponse=" + correctResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID + ", dtmTime=" + dtmTime);
  var strResponse = response.Short,
    strResponseLong = response.Long,
    strCorrectResponse = "",
    strCorrectResponseLong = "";
  if (correctResponse !== null) {
    strCorrectResponse = correctResponse.Short;
    strCorrectResponseLong = correctResponse.Long;
  }
  return TCAPI_RecordInteraction(strID, strResponseLong, blnCorrect, strCorrectResponseLong, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, TCAPI_INTERACTION_TYPE_LIKERT, strResponse, strCorrectResponse, intScoreRaw, intScoreMin);
}

function TCAPI_RecordNumericInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin) {
  WriteToDebug("In TCAPI_RecordNumericInteraction strID=" + strID + ", strResponse=" + strResponse + ", blnCorrect=" + blnCorrect + ", strCorrectResponse=" + strCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID + ", dtmTime=" + dtmTime);
  return TCAPI_RecordInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, TCAPI_INTERACTION_TYPE_NUMERIC, strResponse, strCorrectResponse);
}

function TCAPI_GetEntryMode() {
  WriteToDebug("In TCAPI_GetEntryMode");
  return null;
}

function TCAPI_GetLessonMode() {
  WriteToDebug("In TCAPI_GetLessonMode");
  return null;
}

function TCAPI_GetTakingForCredit() {
  WriteToDebug("In TCAPI_GetTakingForCredit");
  return null;
}

function TCAPI_SetObjectiveScore(strObjectiveID, intScore, intMaxScore, intMinScore) {
  WriteToDebug("In TCAPI_SetObjectiveScore, strObejctiveID=" + strObjectiveID + ", intScore=" + intScore + ", intMaxScore=" + intMaxScore + ", intMinScore=" + intMinScore);
}

function TCAPI_SetObjectiveDescription(strObjectiveID, strObjectiveDescription) {
  WriteToDebug("In TCAPI_SetObjectiveDescription, strObjectiveDescription=" + strObjectiveDescription);
  TCAPI_ClearErrorInfo();
  return TCAPI_TRUE;
}

function TCAPI_SetObjectiveStatus(strObjectiveID, Lesson_Status) {
  WriteToDebug("In TCAPI_SetObjectiveStatus strObjectiveID=" + strObjectiveID + ", Lesson_Status=" + Lesson_Status);
}

function TCAPI_GetObjectiveScore(strObjectiveID) {
  WriteToDebug("In TCAPI_GetObjectiveScore, strObejctiveID=" + strObjectiveID);
}

function TCAPI_GetObjectiveDescription(strObjectiveID) {
  WriteToDebug("In TCAPI_GetObjectiveDescription, strObejctiveID=" + strObjectiveID);
  return "";
}

function TCAPI_GetObjectiveStatus(strObjectiveID) {
  WriteToDebug("In TCAPI_GetObjectiveStatus, strObejctiveID=" + strObjectiveID);
}

function TCAPI_FindObjectiveIndexFromID(strObjectiveID) {
  WriteToDebug("In TCAPI_FindObjectiveIndexFromID");
}

function TCAPI_SetSuspended() {
  WriteToDebug("In TCAPI_SetSuspended");
  if (TCAPI_IN_PROGRESS) {
    TCAPI_IN_PROGRESS = false;
    TCAPI_UPDATES_PENDING = true;
  }
  return true;
}

function TCAPI_SetFailed() {
  WriteToDebug("In TCAPI_SetFailed");
  TCAPI_STATUS = TCAPI_VERB_FAILED;
  TCAPI_STATUS_CHANGED = true;
  TCAPI_SATISFACTION_STATUS = false;
  TCAPI_IN_PROGRESS = false;
  TCAPI_UPDATES_PENDING = true;
  return true;
}

function TCAPI_SetPassed() {
  WriteToDebug("In TCAPI_SetPassed");
  TCAPI_STATUS = TCAPI_VERB_PASSED;
  TCAPI_STATUS_CHANGED = true;
  TCAPI_SATISFACTION_STATUS = true;
  TCAPI_IN_PROGRESS = false;
  TCAPI_UPDATES_PENDING = true;
  return true;
}

function TCAPI_SetCompleted() {
  WriteToDebug("In TCAPI_SetCompleted");
  TCAPI_ClearErrorInfo();
  if (TCAPI_STATUS === TCAPI_INIT_VERB) {
    TCAPI_STATUS = TCAPI_VERB_COMPLETED;
    TCAPI_STATUS_CHANGED = true;
  }
  TCAPI_COMPLETION_STATUS = TCAPI_VERB_COMPLETED;
  TCAPI_IN_PROGRESS = false;
  TCAPI_UPDATES_PENDING = true;
  return true;
}

function TCAPI_ResetStatus() {
  WriteToDebug("In TCAPI_ResetStatus");
  TCAPI_ClearErrorInfo();
  TCAPI_STATUS = TCAPI_INIT_VERB;
  TCAPI_STATUS_CHANGED = true;
  TCAPI_COMPLETION_STATUS = '';
  TCAPI_SATISFACTION_STATUS = null;
  TCAPI_IN_PROGRESS = true;
  TCAPI_UPDATES_PENDING = true;
  return true;
}

function TCAPI_GetStatus() {
  WriteToDebug("In TCAPI_GetStatus");
  var strStatus = "";
  TCAPI_ClearErrorInfo();
  if (TCAPI_STATUS === TCAPI_VERB_COMPLETED) {
    strStatus = "completed";
  } else if (TCAPI_STATUS === TCAPI_VERB_ATTEMPTED) {
    strStatus = "attempted";
  } else if (TCAPI_STATUS === TCAPI_VERB_PASSED) {
    strStatus = "passed";
  } else if (TCAPI_STATUS === TCAPI_VERB_FAILED) {
    strStatus = "failed";
  } else {
    strStatus = TCAPI_STATUS;
  }
  WriteToDebug("In TCAPI_GetStatus - strStatus=" + strStatus);
  return strStatus;
}

function TCAPI_GetCompletionStatus() {
  WriteToDebug("In TCAPI_GetCompletionStatus");
  WriteToDebug("In TCAPI_GetCompletionStatus: returning TCAPI_COMPLETION_STAUS: " + TCAPI_COMPLETION_STATUS);
  return TCAPI_COMPLETION_STATUS;
}

function TCAPI_SetNavigationRequest(strNavRequest) {
  WriteToDebug("TCAPI_GetNavigationRequest - TCAPI does not support navigation requests, returning false");
  return false;
}

function TCAPI_GetNavigationRequest() {
  WriteToDebug("TCAPI_GetNavigationRequest - TCAPI does not support navigation requests, returning false");
  return false;
}

function TCAPI_CreateDataBucket(strBucketId, intMinSize, intMaxSize) {
  WriteToDebug("TCAPI_CreateDataBucket - TCAPI does not support SSP, returning false");
  return false;
}

function TCAPI_GetDataFromBucket(strBucketId) {
  WriteToDebug("TCAPI_GetDataFromBucket - TCAPI does not support SSP, returning empty string");
  return "";
}

function TCAPI_PutDataInBucket(strBucketId, strData, blnAppendToEnd) {
  WriteToDebug("TCAPI_PutDataInBucket - TCAPI does not support SSP, returning false");
  return false;
}

function TCAPI_DetectSSPSupport() {
  WriteToDebug("TCAPI_DetectSSPSupport - TCAPI does not support SSP, returning false");
  return false;
}

function TCAPI_GetBucketInfo(strBucketId) {
  WriteToDebug("AICC_DetectSSPSupport - TCAPI does not support SSP, returning empty SSPBucketSize");
  return new SSPBucketSize(0, 0);
}

function TCAPI_WriteComment(strComment) {
  WriteToDebug("In TCAPI_WriteComment - TCAPI does not support LMS comments");
  return false;
}

function TCAPI_GetLMSComments() {
  WriteToDebug("In TCAPI_GetLMSComments - TCAPI does not support LMS comments");
  return false;
}

function TCAPI_GetLaunchData() {
  WriteToDebug("In TCAPI_GetLaunchData - TCAPI does not support launch data");
  return false;
}

function TCAPI_SetLaunchData() {
  WriteToDebug("In TCAPI_SetLaunchData - TCAPI does not support launch data");
  return false;
}

function TCAPI_GetComments() {
  WriteToDebug("In TCAPI_GetComments - TCAPI does not support comments");
  return false;
}

function TCAPI_SetComments() {
  WriteToDebug("In TCAPI_SetComments - TCAPI does not support comments");
  return false;
}

function TCAPI_DisplayMessageOnTimeout() {
  TCAPI_ClearErrorInfo();
  WriteToDebug("In TCAPI_DisplayMessageOnTimeout - TCAPI does not support MessageOnTimeout");
  return false;
}

function TCAPI_ExitOnTimeout() {
  WriteToDebug("In TCAPI_ExitOnTimeout - TCAPI does not support ExitOnTimeout");
  return false;
}

function TCAPI_GetPassingScore() {
  WriteToDebug("In TCAPI_GetPassingScore - TCAPI does not support GetPassingScore");
  return false;
}

function TCAPI_GetProgressMeasure() {
  WriteToDebug("TCAPI_GetProgressMeasure - TCAPI does not support progress_measure, returning false");
  return false;
}

function TCAPI_SetProgressMeasure() {
  WriteToDebug("TCAPI_SetProgressMeasure - TCAPI does not support progress_measure, returning false");
  return false;
}

function TCAPI_GetObjectiveProgressMeasure() {
  WriteToDebug("TCAPI_GetObjectiveProgressMeasure - TCAPI does not support progress_measure, returning false");
  return false;
}

function TCAPI_SetObjectiveProgressMeasure() {
  WriteToDebug("TCAPI_SetObjectiveProgressMeasure - TCAPI does not support progress_measure, returning false");
  return false;
}

function TCAPI_IsContentInBrowseMode() {
  WriteToDebug("In TCAPI_IsContentInBrowseMode - TCAPI does not support BrowseMode");
  return false;
}

function TCAPI_FindInteractionIndexFromID(strInteractionID) {
  WriteToDebug("TCAPI_FindInteractionIndexFromID - TCAPI does not support interaction retrieval, returning null");
  return null;
}

function TCAPI_GetInteractionType(strInteractionID) {
  WriteToDebug("TCAPI_GetInteractionType - TCAPI does not support interaction retrieval, returning empty string");
  return '';
}

function TCAPI_GetInteractionTimestamp(strInteractionID) {
  WriteToDebug("TCAPI_GetInteractionTimestamp - TCAPI does not support interaction retrieval, returning empty string");
  return '';
}

function TCAPI_GetInteractionCorrectResponses(strInteractionID) {
  WriteToDebug("TCAPI_GetInteractionCorrectResponses - TCAPI does not support interaction retrieval, returning empty array");
  return [];
}

function TCAPI_GetInteractionWeighting(strInteractionID) {
  WriteToDebug("TCAPI_GetInteractionWeighting - TCAPI does not support interaction retrieval, returning empty string");
  return '';
}

function TCAPI_GetInteractionLearnerResponses(strInteractionID) {
  WriteToDebug("TCAPI_GetInteractionLearnerResponses - TCAPI does not support interaction retrieval, returning empty array");
  return [];
}

function TCAPI_GetInteractionResult(strInteractionID) {
  WriteToDebug("TCAPI_GetInteractionResult - TCAPI does not support interaction retrieval, returning empty string");
  return '';
}

function TCAPI_GetInteractionLatency(strInteractionID) {
  WriteToDebug("TCAPI_GetInteractionDescription - TCAPI does not support interaction retrieval, returning empty string");
  return '';
}

function TCAPI_GetInteractionDescription(strInteractionID) {
  WriteToDebug("TCAPI_GetInteractionDescription - TCAPI does not support interaction retrieval, returning empty string");
  return '';
}

function TCAPI_ClearErrorInfo() {
  WriteToDebug("In TCAPI_ClearErrorInfo");
  intTCAPIError = TCAPI_NO_ERROR;
  strTCAPIErrorString = "";
  strTCAPIErrorDiagnostic = "";
}

function TCAPI_SetErrorInfo() {
  WriteToDebug("In TCAPI_SetErrorInfo");
  intTCAPIError = TCAPI_objAPI.LMSGetLastError();
  strTCAPIErrorString = TCAPI_objAPI.LMSGetErrorString(intTCAPIError);
  strTCAPIErrorDiagnostic = TCAPI_objAPI.LMSGetDiagnostic("");
  intTCAPIError = intTCAPIError + "";
  strTCAPIErrorString = strTCAPIErrorString + "";
  strTCAPIErrorDiagnostic = strTCAPIErrorDiagnostic + "";
  WriteToDebug("intTCAPIError=" + intTCAPIError);
  WriteToDebug("strTCAPIErrorString=" + strTCAPIErrorString);
  WriteToDebug("strTCAPIErrorDiagnostic=" + strTCAPIErrorDiagnostic);
}

function TCAPI_SetErrorInfoManually(intNum, strString, strDiagnostic) {
  WriteToDebug("In TCAPI_SetErrorInfoManually");
  WriteToDebug("ERROR-Num=" + intNum);
  WriteToDebug("      String=" + strString);
  WriteToDebug("      Diag=" + strDiagnostic);
  intTCAPIError = intNum;
  strTCAPIErrorString = strString;
  strTCAPIErrorDiagnostic = strDiagnostic;
}

function TCAPI_GetLastError() {
  WriteToDebug("In TCAPI_GetLastError");
  if (intTCAPIError === TCAPI_NO_ERROR) {
    WriteToDebug("Returning No Error");
    return NO_ERROR;
  } else {
    WriteToDebug("Returning " + intTCAPIError);
    return intTCAPIError;
  }
}

function TCAPI_GetLastErrorDesc() {
  WriteToDebug("In TCAPI_GetLastErrorDesc, " + strTCAPIErrorString + "\n" + strTCAPIErrorDiagnostic);
  return strTCAPIErrorString + "\n" + strTCAPIErrorDiagnostic;
}

function LMSStandardAPI(strStandard) {
  //WriteToDebug("In LMSStandardAPI strStandard=" + strStandard);
  if (strStandard == "") {
    //WriteToDebug("No standard specified, using NONE");
    strStandard = "NONE";
  }
  this.Initialize = TCAPI_Initialize;
  this.Finish = TCAPI_Finish;
  this.Terminate = TCAPI_Finish;
  this.CommitData = TCAPI_CommitData;
  this.Commit = CommitData;
  this.GetStudentID = TCAPI_GetStudentID;
  this.GetStudentName = TCAPI_GetStudentName;
  this.GetBookmark = TCAPI_GetBookmark;
  this.SetBookmark = TCAPI_SetBookmark;
  this.GetDataChunk = TCAPI_GetDataChunk;
  this.SetDataChunk = TCAPI_SetDataChunk;
  this.GetLaunchData = TCAPI_GetLaunchData;
  this.GetComments = TCAPI_GetComments;
  this.WriteComment = TCAPI_WriteComment;
  this.GetLMSComments = TCAPI_GetLMSComments;
  this.GetAudioPlayPreference = TCAPI_GetAudioPlayPreference;
  this.GetAudioVolumePreference = TCAPI_GetAudioVolumePreference;
  this.SetAudioPreference = TCAPI_SetAudioPreference;
  this.SetLanguagePreference = TCAPI_SetLanguagePreference;
  this.GetLanguagePreference = TCAPI_GetLanguagePreference;
  this.SetSpeedPreference = TCAPI_SetSpeedPreference;
  this.GetSpeedPreference = TCAPI_GetSpeedPreference;
  this.SetTextPreference = TCAPI_SetTextPreference;
  this.GetTextPreference = TCAPI_GetTextPreference;
  this.GetPreviouslyAccumulatedTime = TCAPI_GetPreviouslyAccumulatedTime;
  this.SaveTime = TCAPI_SaveTime;
  this.GetMaxTimeAllowed = TCAPI_GetMaxTimeAllowed;
  this.DisplayMessageOnTimeout = TCAPI_DisplayMessageOnTimeout;
  this.ExitOnTimeout = TCAPI_ExitOnTimeout;
  this.GetPassingScore = TCAPI_GetPassingScore;
  this.SetScore = TCAPI_SetScore;
  this.GetScore = TCAPI_GetScore;
  this.GetScaledScore = TCAPI_GetScaledScore;
  this.RecordTrueFalseInteraction = TCAPI_RecordTrueFalseInteraction;
  this.RecordMultipleChoiceInteraction = TCAPI_RecordMultipleChoiceInteraction;
  this.RecordFillInInteraction = TCAPI_RecordFillInInteraction;
  this.RecordMatchingInteraction = TCAPI_RecordMatchingInteraction;
  this.RecordPerformanceInteraction = TCAPI_RecordPerformanceInteraction;
  this.RecordSequencingInteraction = TCAPI_RecordSequencingInteraction;
  this.RecordLikertInteraction = TCAPI_RecordLikertInteraction;
  this.RecordNumericInteraction = TCAPI_RecordNumericInteraction;
  this.GetEntryMode = TCAPI_GetEntryMode;
  this.GetLessonMode = TCAPI_GetLessonMode;
  this.GetTakingForCredit = TCAPI_GetTakingForCredit;
  this.SetObjectiveScore = TCAPI_SetObjectiveScore;
  this.SetObjectiveStatus = TCAPI_SetObjectiveStatus;
  this.GetObjectiveScore = TCAPI_GetObjectiveScore;
  this.GetObjectiveStatus = TCAPI_GetObjectiveStatus;
  this.SetObjectiveDescription = TCAPI_SetObjectiveDescription;
  this.GetObjectiveDescription = TCAPI_GetObjectiveDescription;
  this.SetFailed = TCAPI_SetFailed;
  this.SetPassed = TCAPI_SetPassed;
  this.SetCompleted = TCAPI_SetCompleted;
  this.ResetStatus = TCAPI_ResetStatus;
  this.GetStatus = TCAPI_GetStatus;
  this.GetLastError = TCAPI_GetLastError;
  this.GetLastErrorDesc = TCAPI_GetLastErrorDesc;
  this.GetInteractionType = TCAPI_GetInteractionType;
  this.GetInteractionTimestamp = TCAPI_GetInteractionTimestamp;
  this.GetInteractionCorrectResponses = TCAPI_GetInteractionCorrectResponses;
  this.GetInteractionWeighting = TCAPI_GetInteractionWeighting;
  this.GetInteractionLearnerResponses = TCAPI_GetInteractionLearnerResponses;
  this.GetInteractionResult = TCAPI_GetInteractionResult;
  this.GetInteractionLatency = TCAPI_GetInteractionLatency;
  this.GetInteractionDescription = TCAPI_GetInteractionDescription;
  this.CreateDataBucket = TCAPI_CreateDataBucket;
  this.GetDataFromBucket = TCAPI_GetDataFromBucket;
  this.PutDataInBucket = TCAPI_PutDataInBucket;
  this.DetectSSPSupport = TCAPI_DetectSSPSupport;
  this.GetBucketInfo = TCAPI_GetBucketInfo;
  this.GetProgressMeasure = TCAPI_GetProgressMeasure;
  this.SetProgressMeasure = TCAPI_SetProgressMeasure;
  this.SetPointBasedScore = TCAPI_SetPointBasedScore;
  this.SetNavigationRequest = TCAPI_SetNavigationRequest;
  this.GetNavigationRequest = TCAPI_GetNavigationRequest;
  this.SetObjectiveProgressMeasure = TCAPI_SetObjectiveProgressMeasure;
  this.GetObjectiveProgressMeasure = TCAPI_GetObjectiveProgressMeasure;
  this.Standard = strStandard;
}
function LMSStandardAPIEval(strStandard) {
  WriteToDebug("In LMSStandardAPI strStandard=" + strStandard);
  if (strStandard == "") {
    WriteToDebug("No standard specified, using NONE");
    strStandard = "NONE";
  }
  eval("this.Initialize = " + strStandard + "_Initialize");
  eval("this.Finish = " + strStandard + "_Finish");
  eval("this.Terminate = " + strStandard + "_Finish");
  eval("this.CommitData = " + strStandard + "_CommitData");
  eval("this.GetStudentID = " + strStandard + "_GetStudentID");
  eval("this.GetStudentName = " + strStandard + "_GetStudentName");
  eval("this.GetBookmark = " + strStandard + "_GetBookmark");
  eval("this.SetBookmark = " + strStandard + "_SetBookmark");
  eval("this.GetDataChunk = " + strStandard + "_GetDataChunk");
  eval("this.SetDataChunk = " + strStandard + "_SetDataChunk");
  eval("this.GetLaunchData = " + strStandard + "_GetLaunchData");
  eval("this.GetComments = " + strStandard + "_GetComments");
  eval("this.WriteComment = " + strStandard + "_WriteComment");
  eval("this.GetLMSComments = " + strStandard + "_GetLMSComments");
  eval("this.GetAudioPlayPreference = " + strStandard + "_GetAudioPlayPreference");
  eval("this.GetAudioVolumePreference = " + strStandard + "_GetAudioVolumePreference");
  eval("this.SetAudioPreference = " + strStandard + "_SetAudioPreference");
  eval("this.SetLanguagePreference = " + strStandard + "_SetLanguagePreference");
  eval("this.GetLanguagePreference = " + strStandard + "_GetLanguagePreference");
  eval("this.SetSpeedPreference = " + strStandard + "_SetSpeedPreference");
  eval("this.GetSpeedPreference = " + strStandard + "_GetSpeedPreference");
  eval("this.SetTextPreference = " + strStandard + "_SetTextPreference");
  eval("this.GetTextPreference = " + strStandard + "_GetTextPreference");
  eval("this.GetPreviouslyAccumulatedTime = " + strStandard + "_GetPreviouslyAccumulatedTime");
  eval("this.SaveTime = " + strStandard + "_SaveTime");
  eval("this.GetMaxTimeAllowed = " + strStandard + "_GetMaxTimeAllowed");
  eval("this.DisplayMessageOnTimeout = " + strStandard + "_DisplayMessageOnTimeout");
  eval("this.ExitOnTimeout = " + strStandard + "_ExitOnTimeout");
  eval("this.GetPassingScore = " + strStandard + "_GetPassingScore");
  eval("this.SetScore = " + strStandard + "_SetScore");
  eval("this.GetScore = " + strStandard + "_GetScore");
  eval("this.GetScaledScore = " + strStandard + "_GetScaledScore");
  eval("this.RecordTrueFalseInteraction = " + strStandard + "_RecordTrueFalseInteraction");
  eval("this.RecordMultipleChoiceInteraction = " + strStandard + "_RecordMultipleChoiceInteraction");
  eval("this.RecordFillInInteraction = " + strStandard + "_RecordFillInInteraction");
  eval("this.RecordMatchingInteraction = " + strStandard + "_RecordMatchingInteraction");
  eval("this.RecordPerformanceInteraction = " + strStandard + "_RecordPerformanceInteraction");
  eval("this.RecordSequencingInteraction = " + strStandard + "_RecordSequencingInteraction");
  eval("this.RecordLikertInteraction = " + strStandard + "_RecordLikertInteraction");
  eval("this.RecordNumericInteraction = " + strStandard + "_RecordNumericInteraction");
  eval("this.GetEntryMode = " + strStandard + "_GetEntryMode");
  eval("this.GetLessonMode = " + strStandard + "_GetLessonMode");
  eval("this.GetTakingForCredit = " + strStandard + "_GetTakingForCredit");
  eval("this.SetObjectiveScore = " + strStandard + "_SetObjectiveScore");
  eval("this.SetObjectiveStatus = " + strStandard + "_SetObjectiveStatus");
  eval("this.GetObjectiveScore = " + strStandard + "_GetObjectiveScore");
  eval("this.GetObjectiveStatus = " + strStandard + "_GetObjectiveStatus");
  eval("this.SetObjectiveDescription = " + strStandard + "_SetObjectiveDescription");
  eval("this.GetObjectiveDescription = " + strStandard + "_GetObjectiveDescription");
  eval("this.SetFailed = " + strStandard + "_SetFailed");
  eval("this.SetPassed = " + strStandard + "_SetPassed");
  eval("this.SetCompleted = " + strStandard + "_SetCompleted");
  eval("this.ResetStatus = " + strStandard + "_ResetStatus");
  eval("this.GetStatus = " + strStandard + "_GetStatus");
  eval("this.GetLastError = " + strStandard + "_GetLastError");
  eval("this.GetLastErrorDesc = " + strStandard + "_GetLastErrorDesc");
  eval("this.GetInteractionType = " + strStandard + "_GetInteractionType");
  eval("this.GetInteractionTimestamp = " + strStandard + "_GetInteractionTimestamp");
  eval("this.GetInteractionCorrectResponses = " + strStandard + "_GetInteractionCorrectResponses");
  eval("this.GetInteractionWeighting = " + strStandard + "_GetInteractionWeighting");
  eval("this.GetInteractionLearnerResponses = " + strStandard + "_GetInteractionLearnerResponses");
  eval("this.GetInteractionResult = " + strStandard + "_GetInteractionResult");
  eval("this.GetInteractionLatency = " + strStandard + "_GetInteractionLatency");
  eval("this.GetInteractionDescription = " + strStandard + "_GetInteractionDescription");
  eval("this.CreateDataBucket = " + strStandard + "_CreateDataBucket");
  eval("this.GetDataFromBucket = " + strStandard + "_GetDataFromBucket");
  eval("this.PutDataInBucket = " + strStandard + "_PutDataInBucket");
  eval("this.DetectSSPSupport = " + strStandard + "_DetectSSPSupport");
  eval("this.GetBucketInfo = " + strStandard + "_GetBucketInfo");
  eval("this.GetProgressMeasure = " + strStandard + "_GetProgressMeasure");
  eval("this.SetProgressMeasure = " + strStandard + "_SetProgressMeasure");
  eval("this.SetPointBasedScore = " + strStandard + "_SetPointBasedScore");
  eval("this.SetNavigationRequest = " + strStandard + "_SetNavigationRequest");
  eval("this.GetNavigationRequest = " + strStandard + "_GetNavigationRequest");
  eval("this.SetObjectiveProgressMeasure = " + strStandard + "_SetObjectiveProgressMeasure");
  eval("this.GetObjectiveProgressMeasure = " + strStandard + "_GetObjectiveProgressMeasure");
  this.Standard = strStandard;
}
var blnCalledFinish = false;
var blnStandAlone = false;
var blnLoaded = false;
var blnReachedEnd = false;
var blnStatusWasSet = false;
var blnLmsPresent = false;
var dtmStart = null;
var dtmEnd = null;
var intAccumulatedMS = 0;
var blnOverrodeTime = false;
var intTimeOverrideMS = null;
var aryDebug = new Array();
var strDebug = "";
var winDebug;
var intError = NO_ERROR;
var strErrorDesc = "";
var objLMS = null;

function Start() {

  var strStandAlone;
  var strShowInteractiveDebug;
  var objTempAPI = null;
  var strTemp = "";
  WriteToDebug("<h1>SCORM Driver starting up</h1>");
  WriteToDebug("----------------------------------------");
  WriteToDebug("----------------------------------------");
  WriteToDebug("In Start - Version: " + VERSION + "  Last Modified=" + window.document.lastModified);
  WriteToDebug("Browser Info (" + navigator.appName + " " + navigator.appVersion + ")");
  WriteToDebug("URL: " + window.document.location.href);
  WriteToDebug("----------------------------------------");
  WriteToDebug("----------------------------------------");
  ClearErrorInfo();
  strStandAlone = GetQueryStringValue("StandAlone", window.location.search);
  strShowInteractiveDebug = GetQueryStringValue("ShowDebug", window.location.search);
  WriteToDebug("strStandAlone=" + strStandAlone + "  strShowInteractiveDebug=" + strShowInteractiveDebug);
  if (ConvertStringToBoolean(strStandAlone)) {
    WriteToDebug("Entering Stand Alone Mode");
    blnStandAlone = true;
  }
  if (blnStandAlone) {
    WriteToDebug("Using NONE Standard");
    objLMS = new LMSStandardAPI("NONE");
  } else {
    WriteToDebug("Standard From Configuration File - " + strLMSStandard);
    if (strLMSStandard.toUpperCase() == "TCAPI") {
      WriteToDebug("Using TCAPI as set in the configuration");
      objLMS = new LMSStandardAPI("TCAPI");
      blnLmsPresent = true;
    } else if (strLMSStandard.toUpperCase() == "AUTO") {
      WriteToDebug("Searching for AICC querystring parameters");
      strTemp = GetQueryStringValue("AICC_URL", document.location.search);
      if (strTemp != null && strTemp != "") {
        WriteToDebug("Found AICC querystring parameters, using AICC");
        objLMS = new LMSStandardAPI("AICC");
        blnLmsPresent = true;
      } else {
        WriteToDebug("Auto-detecting standard - Searching for SCORM 2004 API");
        try {
          objTempAPI = SCORM2004_GrabAPI();
        } catch (e) {
          WriteToDebug("Error grabbing 2004 API-" + e.name + ":" + e.message);
        }
        if (!(typeof(objTempAPI) == "undefined" || objTempAPI == null)) {
          WriteToDebug("Found SCORM 2004 API, using SCORM 2004");
          objLMS = new LMSStandardAPI("SCORM2004");
          blnLmsPresent = true;
        } else {
          WriteToDebug("Searching for SCORM 1.2 API");
          try {
            objTempAPI = SCORM_GrabAPI();
          } catch (e) {
            WriteToDebug("Error grabbing 1.2 API-" + e.name + ":" + e.message);
          }
          if (!(typeof(objTempAPI) == "undefined" || objTempAPI == null)) {
            WriteToDebug("Found SCORM API, using SCORM");
            objLMS = new LMSStandardAPI("SCORM");
            blnLmsPresent = true;
          } else {
            WriteToDebug("Searching for TCAPI endpoint");
            strTemp = GetQueryStringValue("endpoint", document.location.search);
            if (strTemp !== null && strTemp !== "") {
              WriteToDebug("Found TCAPI via 'endpoint' query string param, using TCAPI");
              objLMS = new LMSStandardAPI("TCAPI");
              blnLmsPresent = true;
              strLMSStandard = "TCAPI";
            } else {
              if (ALLOW_NONE_STANDARD === true) {
                WriteToDebug("Could not determine standard, defaulting to Stand Alone");
                objLMS = new LMSStandardAPI("NONE");
              } else {
                WriteToDebug("Could not determine standard, Stand Alone is disabled in configuration");
                DisplayError("Could not determine standard. Neither SCORM nor AICC APIs could be found");
                return;
              }
            }
          }
        }
      }
    } else {
      WriteToDebug("Using Standard From Configuration File - " + strLMSStandard);
      objLMS = new LMSStandardAPI(strLMSStandard);
      blnLmsPresent = true;
    }
  }
  if (ConvertStringToBoolean(strShowInteractiveDebug) || (!(typeof(SHOW_DEBUG_ON_LAUNCH) == "undefined") && SHOW_DEBUG_ON_LAUNCH === true)) {
    WriteToDebug("Showing Interactive Debug Windows");
    ShowDebugWindow();
  }
  WriteToDebug("Calling Standard Initialize");
  if (strLMSStandard.toUpperCase() == "TCAPI") {
    loadScript("tc-config.js", objLMS.Initialize);
  } else {
    objLMS.Initialize();
  }
  TouchCloud();
  return;
}

function InitializeExecuted(blnSuccess, strErrorMessage) {
  WriteToDebug("In InitializeExecuted, blnSuccess=" + blnSuccess + ", strErrorMessage=" + strErrorMessage);
  if (!blnSuccess) {
    WriteToDebug("ERROR - LMS Initialize Failed");
    if (strErrorMessage == "") {
      strErrorMessage = "An Error Has Occurred";
    }
    blnLmsPresent = false;
    DisplayError(strErrorMessage);
    return;
  }
  blnLoaded = true;
  dtmStart = new Date();
  //LoadContent();
  return;
}

function ExecFinish(ExitType) {
  WriteToDebug("In ExecFinish, ExiType=" + ExitType);
  ClearErrorInfo();
  if (blnLoaded && !blnCalledFinish) {
    WriteToDebug("Haven't called finish before, finishing");
    blnCalledFinish = true;
    if (blnReachedEnd && (!EXIT_SUSPEND_IF_COMPLETED)) {
      WriteToDebug("Reached End, overiding exit type to FINISH");
      ExitType = EXIT_TYPE_FINISH;
    }
    if (objLMS.GetStatus() == LESSON_STATUS_PASSED && EXIT_NORMAL_IF_PASSED == true) {
      WriteToDebug("Passed status and config value set, overiding exit type to FINISH");
      ExitType = EXIT_TYPE_FINISH;
    }
    if (!blnOverrodeTime) {
      WriteToDebug("Did not override time");
      dtmEnd = new Date();
      AccumulateTime();
      objLMS.SaveTime(intAccumulatedMS);
    }
    blnLoaded = false;
    WriteToDebug("Calling LMS Finish");
    return objLMS.Finish(ExitType, blnStatusWasSet);
  }
  return true;
}

function IsLoaded() {
  WriteToDebug("In IsLoaded, returning -" + blnLoaded);
  return blnLoaded;
}

function WriteToDebug(strInfo) {
  console.log(strInfo);
  if (blnDebug) {
    var dtm = new Date();
    var strLine;
    strLine = aryDebug.length + ":" + dtm.toString() + " - " + strInfo;
    aryDebug[aryDebug.length] = strLine;
    if (winDebug && !winDebug.closed) {
      if (!window.firstMsg) {
        window.firstMsg = true;
      } else {
        winDebug.document.write("<div class='debugLog' style='background-color:" + getBackgroundColorForLogs() + ";padding-bottom: 10px;font-family:monospace;word-wrap: break-word;'>" + strLine + "</div>\n");
      }
    }
    return;
  }
}

function ShowDebugWindow(lmsPreview) {
  if (!lmsPreview) {
    if (winDebug && !winDebug.closed) {
      winDebug.close();
    }
    winDebug = window.parent.window.open("", "Debug", "width=600,height=300,resizable,scrollbars");
  } else {
    winDebug = getDebugLogsIFrame()
  }
  if (winDebug) {
    winDebug.document.write(aryDebug.join("<br>\n"));
    winDebug.document.close();
    winDebug.focus();
  }
  return;
}

function DisplayError(strMessage) {
  var blnShowDebug;
  WriteToDebug("In DisplayError, strMessage=" + strMessage);
  blnShowDebug = confirm("An error has occurred:\n\n" + strMessage + "\n\nPress 'OK' to view debug information to send to technical support.");
  if (blnShowDebug) {
    ShowDebugWindow();
  }
}

function GetLastError() {
  WriteToDebug("In GetLastError, intError=" + intError);
  if (intError != NO_ERROR) {
    WriteToDebug("Returning API Error");
    return intError;
  } else if (IsLoaded() && objLMS.GetLastError() != NO_ERROR) {
    WriteToDebug("Returning LMS Error");
    return ERROR_LMS;
  }
  WriteToDebug("Returning No Error");
  return NO_ERROR;
}

function GetLastLMSErrorCode() {
  WriteToDebug("In GetLastLMSErrorCode, intError=" + intError);
  var LMSError = objLMS.GetLastError();
  if (IsLoaded() && LMSError != NO_ERROR) {
    WriteToDebug("Returning LMS Error: " + LMSError);
    return LMSError;
  }
  WriteToDebug("Returning No Error");
  return NO_ERROR;
}

function GetLastErrorDesc() {
  WriteToDebug("In GetLastErrorDesc");
  if (intError != NO_ERROR) {
    WriteToDebug("Returning API Error - " + strErrorDesc);
    return strErrorDesc;
  } else if (IsLoaded() && objLMS.GetLastError() != NO_ERROR) {
    WriteToDebug("returning LMS Error");
    return objLMS.GetLastErrorDesc;
  }
  WriteToDebug("Returning No Error");
  return "";
}

function SetErrorInfo(intErrorNumToSet, strErrorDescToSet) {
  WriteToDebug("In SetErrorInfo - Num=" + intErrorNumToSet + " Desc=" + strErrorDescToSet);
  intError = intErrorNumToSet;
  strErrorDesc = strErrorDescToSet;
}

function ClearErrorInfo() {
  WriteToDebug("In ClearErrorInfo");
  var intError = NO_ERROR;
  var strErrorDesc = "";
}

function CommitData() {
  alert('asdasd');
  WriteToDebug("In CommitData");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  if (!blnOverrodeTime) {
    WriteToDebug("Did not override time, saving incremental time");
    dtmEnd = new Date();
    AccumulateTime();
    dtmStart = new Date();
    this.SaveTime(intAccumulatedMS);
    TCAPI_UPDATES_PENDING = true;
  }
  return TCAPI_CommitData();
}

function Suspend() {
  WriteToDebug("In Suspend");
  ClearErrorInfo();
  return ExecFinish(EXIT_TYPE_SUSPEND);
}

function Finish() {
  WriteToDebug("In Finish");
  ClearErrorInfo();
  return ExecFinish(EXIT_TYPE_FINISH);
}

function TimeOut() {
  WriteToDebug("In TimeOut");
  ClearErrorInfo();
  return ExecFinish(EXIT_TYPE_TIMEOUT);
}

function Unload() {
  WriteToDebug("In Unload");
  ClearErrorInfo();
  return ExecFinish(DEFAULT_EXIT_TYPE);
}

function SetReachedEnd() {
  WriteToDebug("In SetReachedEnd");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  if (blnStatusWasSet == false) {
    objLMS.SetCompleted();
  }
  blnReachedEnd = true;
  return true;
}

function ConcedeControl() {
  WriteToDebug("Conceding control with type: " + EXIT_BEHAVIOR);
  ClearErrorInfo();
  var contentRoot = null;
  var urlBase = null;
  switch (EXIT_BEHAVIOR) {
    case "SCORM_RECOMMENDED":
      contentRoot = SearchParentsForContentRoot();
      if (contentRoot == window.top) {
        Suspend();
        contentRoot.window.close();
      } else {
        Suspend();
        if (contentRoot != null) {
          if (IsAbsoluteUrl(EXIT_TARGET)) {
            contentRoot.scormdriver_content.location.href = EXIT_TARGET;
          } else {
            urlBase = GetContentRootUrlBase(contentRoot);
            contentRoot.scormdriver_content.location.href = urlBase + EXIT_TARGET;
          }
        }
      }
      break;
    case "ALWAYS_CLOSE":
      Suspend();
      window.close();
      break;
    case "ALWAYS_CLOSE_TOP":
      Suspend();
      window.top.close();
      break;
    case "ALWAYS_CLOSE_PARENT":
      Suspend();
      window.parent.close();
      break;
    case "NOTHING":
      Suspend();
      break;
    case "REDIR_CONTENT_FRAME":
      Suspend();
      contentRoot = SearchParentsForContentRoot();
      if (contentRoot != null) {
        if (IsAbsoluteUrl(EXIT_TARGET)) {
          contentRoot.scormdriver_content.location.href = EXIT_TARGET;
        } else {
          urlBase = GetContentRootUrlBase(contentRoot);
          contentRoot.scormdriver_content.location.href = urlBase + EXIT_TARGET;
        }
      }
      break;
  }
  return true;
}

function GetContentRootUrlBase(contentRoot) {
  var urlParts = contentRoot.location.href.split("/");
  delete urlParts[urlParts.length - 1];
  contentRoot = urlParts.join("/");
  return contentRoot;
}

function SearchParentsForContentRoot() {
  var contentRoot = null;
  var wnd = window;
  var i = 0;
  if (wnd.scormdriver_content) {
    contentRoot = wnd;
    return contentRoot;
  }
  while (contentRoot == null && wnd != window.top && (i++ < 100)) {
    if (wnd.scormdriver_content) {
      contentRoot = wnd;
      return contentRoot;
    } else {
      wnd = wnd.parent;
    }
  }
  WriteToDebug("Unable to locate content root");
  return null;
}

function GetStudentID() {
  WriteToDebug("In GetStudentID");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return "";
  }
  return objLMS.GetStudentID();
}

function GetStudentName() {
  WriteToDebug("In GetStudentName");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return "";
  }
  return objLMS.GetStudentName();
}

function GetBookmark() {
  WriteToDebug("In GetBookmark");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return "";
  }
  return objLMS.GetBookmark();
}

function SetBookmark(strBookmark, strDesc) {
  WriteToDebug("In SetBookmark - strBookmark=" + strBookmark + ", strDesc=" + strDesc);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.SetBookmark(strBookmark, strDesc);
}

function GetDataChunk() {
  WriteToDebug("In GetDataChunk");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return "";
  }
  return objLMS.GetDataChunk();
}

function SetDataChunk(strData) {
  WriteToDebug("In SetDataChunk strData=" + strData);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.SetDataChunk(strData);
}

function GetLaunchData() {
  WriteToDebug("In GetLaunchData");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return "";
  }
  return objLMS.GetLaunchData();
}

function GetComments() {
  var strCommentString;
  var aryComments;
  var i;
  WriteToDebug("In GetComments");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return null;
  }
  strCommentString = objLMS.GetComments();
  WriteToDebug("strCommentString=" + strCommentString);
  strCommentString = new String(strCommentString);
  if (strCommentString != "") {
    aryComments = strCommentString.split(" | ");
    for (i = 0; i < aryComments.length; i++) {
      WriteToDebug("Returning Comment #" + i);
      aryComments[i] = new String(aryComments[i]);
      aryComments[i] = aryComments[i].replace(/\|\|/g, "|");
      WriteToDebug("Comment #" + i + "=" + aryComments[i]);
    }
  } else {
    aryComments = new Array(0);
  }
  return aryComments;
}

function WriteComment(strComment) {
  var strExistingCommentString;
  WriteToDebug("In WriteComment strComment=" + strComment);
  ClearErrorInfo();
  strComment = new String(strComment);
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  strComment = strComment.replace(/\|/g, "||");
  strExistingCommentString = objLMS.GetComments();
  if (strExistingCommentString != "" && strExistingCommentString != 'undefined') {
    strComment = " | " + strComment;
  }
  strComment = strComment;
  return objLMS.WriteComment(strComment);
}

function GetLMSComments() {
  WriteToDebug("In GetLMSComments");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return "";
  }
  return objLMS.GetLMSComments();
}

function GetAudioPlayPreference() {
  WriteToDebug("In GetAudioPlayPreference");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return PREFERENCE_DEFAULT;
  }
  return objLMS.GetAudioPlayPreference();
}

function GetAudioVolumePreference() {
  WriteToDebug("GetAudioVolumePreference");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return 100;
  }
  return objLMS.GetAudioVolumePreference();
}

function SetAudioPreference(PlayPreference, intPercentOfMaxVolume) {
  WriteToDebug("In SetAudioPreference PlayPreference=" + PlayPreference + " intPercentOfMaxVolume=" + intPercentOfMaxVolume);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  if (PlayPreference != PREFERENCE_OFF && PlayPreference != PREFERENCE_ON) {
    WriteToDebug("Error Invalid PlayPreference");
    SetErrorInfo(ERROR_INVALID_PREFERENCE, "Invalid PlayPreference passed to SetAudioPreference, PlayPreference=" + PlayPreference);
    return false;
  }
  if (!ValidInteger(intPercentOfMaxVolume)) {
    WriteToDebug("Error Invalid PercentOfMaxVolume - not an integer");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid PercentOfMaxVolume passed to SetAudioPreference (not an integer), intPercentOfMaxVolume=" + intPercentOfMaxVolume);
    return false;
  }
  intPercentOfMaxVolume = parseInt(intPercentOfMaxVolume, 10);
  if (intPercentOfMaxVolume < 1 || intPercentOfMaxVolume > 100) {
    WriteToDebug("Error Invalid PercentOfMaxVolume - out of range");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid PercentOfMaxVolume passed to SetAudioPreference (must be between 1 and 100), intPercentOfMaxVolume=" + intPercentOfMaxVolume);
    return false;
  }
  WriteToDebug("Calling to LMS");
  return objLMS.SetAudioPreference(PlayPreference, intPercentOfMaxVolume);
}

function GetLanguagePreference() {
  WriteToDebug("In GetLanguagePreference");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return "";
  }
  return objLMS.GetLanguagePreference();
}

function SetLanguagePreference(strLanguage) {
  WriteToDebug("In SetLanguagePreference strLanguage=" + strLanguage);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.SetLanguagePreference(strLanguage);
}

function GetSpeedPreference() {
  WriteToDebug("In GetSpeedPreference");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return 100;
  }
  return objLMS.GetSpeedPreference();
}

function SetSpeedPreference(intPercentOfMax) {
  WriteToDebug("In SetSpeedPreference intPercentOfMax=" + intPercentOfMax);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  if (!ValidInteger(intPercentOfMax)) {
    WriteToDebug("ERROR Invalid Percent of MaxSpeed, not an integer");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid PercentOfMaxSpeed passed to SetSpeedPreference (not an integer), intPercentOfMax=" + intPercentOfMax);
    return false;
  }
  intPercentOfMax = parseInt(intPercentOfMax, 10);
  if (intPercentOfMax < 0 || intPercentOfMax > 100) {
    WriteToDebug("ERROR Invalid Percent of MaxSpeed, out of range");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid PercentOfMaxSpeed passed to SetSpeedPreference (must be between 1 and 100), intPercentOfMax=" + intPercentOfMax);
    return false;
  }
  WriteToDebug("Calling to LMS");
  return objLMS.SetSpeedPreference(intPercentOfMax);
}

function GetTextPreference() {
  WriteToDebug("In GetTextPreference");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetTextPreference();
}

function SetTextPreference(intPreference) {
  WriteToDebug("In SetTextPreference intPreference=" + intPreference);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  if (intPreference != PREFERENCE_DEFAULT && intPreference != PREFERENCE_OFF && intPreference != PREFERENCE_ON) {
    WriteToDebug("Error - Invalid Preference");
    SetErrorInfo(ERROR_INVALID_PREFERENCE, "Invalid Preference passed to SetTextPreference, intPreference=" + intPreference);
    return false;
  }
  return objLMS.SetTextPreference(intPreference);
}

function GetPreviouslyAccumulatedTime() {
  WriteToDebug("In GetPreviouslyAccumulatedTime");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return 0;
  }
  return objLMS.GetPreviouslyAccumulatedTime();
}

function AccumulateTime() {
  WriteToDebug("In AccumulateTime dtmStart=" + dtmStart + " dtmEnd=" + dtmEnd + " intAccumulatedMS=" + intAccumulatedMS);
  if (dtmEnd != null && dtmStart != null) {
    WriteToDebug("Accumulating Time");
    intAccumulatedMS += (dtmEnd.getTime() - dtmStart.getTime());
    WriteToDebug("intAccumulatedMS=" + intAccumulatedMS);
  }
}

function GetSessionAccumulatedTime() {
  WriteToDebug("In GetSessionAccumulatedTime");
  ClearErrorInfo();
  WriteToDebug("Setting dtmEnd to now");
  dtmEnd = new Date();
  WriteToDebug("Accumulating Time");
  AccumulateTime();
  if (dtmStart != null) {
    WriteToDebug("Resetting dtmStart");
    dtmStart = new Date();
  }
  WriteToDebug("Setting dtmEnd to null");
  dtmEnd = null;
  WriteToDebug("Returning " + intAccumulatedMS);
  return intAccumulatedMS;
}

function SetSessionTime(intMilliseconds) {
  WriteToDebug("In SetSessionTime");
  ClearErrorInfo();
  if (!ValidInteger(intMilliseconds)) {
    WriteToDebug("ERROR parameter is not an integer");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid intMilliseconds passed to SetSessionTime (not an integer), intMilliseconds=" + intMilliseconds);
    return false;
  }
  intMilliseconds = parseInt(intMilliseconds, 10);
  if (intMilliseconds < 0) {
    WriteToDebug("Error, parameter is less than 0");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid intMilliseconds passed to SetSessionTime (must be greater than 0), intMilliseconds=" + intMilliseconds);
    return false;
  }
  blnOverrodeTime = true;
  intTimeOverrideMS = intMilliseconds;
  objLMS.SaveTime(intTimeOverrideMS);
  return true;
}

function PauseTimeTracking() {
  WriteToDebug("In PauseTimeTracking");
  ClearErrorInfo();
  WriteToDebug("Setting dtmEnd to now");
  dtmEnd = new Date();
  WriteToDebug("Accumulating Time");
  AccumulateTime();
  WriteToDebug("Setting Start and End times to null");
  dtmStart = null;
  dtmEnd = null;
  return true;
}

function ResumeTimeTracking() {
  WriteToDebug("In ResumeTimeTracking");
  ClearErrorInfo();
  WriteToDebug("Setting dtmStart to now");
  dtmStart = new Date();
  return true;
}

function GetMaxTimeAllowed() {
  WriteToDebug("In GetMaxTimeAllowed");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return MAX_CMI_TIME;
  }
  return objLMS.GetMaxTimeAllowed();
}

function DisplayMessageOnTimeout() {
  WriteToDebug("In DisplayMessageOnTimeOut");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.DisplayMessageOnTimeout();
}

function ExitOnTimeout() {
  WriteToDebug("In ExitOnTimeOut");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.ExitOnTimeout();
}

function GetPassingScore() {
  WriteToDebug("In GetPassingScore");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return 0;
  }
  return objLMS.GetPassingScore();
}

function GetScore() {
  WriteToDebug("In GetScore");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return 0;
  }
  return objLMS.GetScore();
}

function GetScaledScore() {
  WriteToDebug("In GetScaledScore");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return 0;
  }
  return objLMS.GetScaledScore();
}

function SetScore(intScore, intMaxScore, intMinScore) {
  WriteToDebug("In SetScore, intScore=" + intScore + ", intMaxScore=" + intMaxScore + ", intMinScore=" + intMinScore);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  if (!IsValidDecimal(intScore)) {
    WriteToDebug("ERROR - intScore not a valid decimal");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Score passed to SetScore (not a valid decimal), intScore=" + intScore);
    return false;
  }
  if (!IsValidDecimal(intMaxScore)) {
    WriteToDebug("ERROR - intMaxScore not a valid decimal");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Max Score passed to SetScore (not a valid decimal), intMaxScore=" + intMaxScore);
    return false;
  }
  if (!IsValidDecimal(intMinScore)) {
    WriteToDebug("ERROR - intMinScore not a valid decimal");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Min Score passed to SetScore (not a valid decimal), intMinScore=" + intMinScore);
    return false;
  }
  WriteToDebug("Converting SCORES to floats");
  intScore = parseFloat(intScore);
  intMaxScore = parseFloat(intMaxScore);
  intMinScore = parseFloat(intMinScore);
  if (strLMSStandard == 'SCORM') {
    WriteToDebug("DEBUG - SCORM 1.2 so checking max score length");
    if (intScore < 0 || intScore > 100) {
      WriteToDebug("ERROR - intScore out of range");
      SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Score passed to SetScore (must be between 0-100), intScore=" + intScore);
      return false;
    }
    if (intMaxScore < 0 || intMaxScore > 100) {
      WriteToDebug("ERROR - intMaxScore out of range");
      SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Max Score passed to SetScore (must be between 0-100), intMaxScore=" + intMaxScore);
      return false;
    }
    if (intMinScore < 0 || intMinScore > 100) {
      WriteToDebug("ERROR - intMinScore out of range");
      SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Min Score passed to SetScore (must be between 0-100), intMinScore=" + intMinScore);
      return false;
    }
  }
  if (SCORE_CAN_ONLY_IMPROVE === true) {
    var previousScore = GetScore();
    if (previousScore != null && previousScore != "" && previousScore > intScore) {
      WriteToDebug("Previous score was greater than new score, configuration only allows scores to improve, returning.");
      return true;
    }
  }
  WriteToDebug("Calling to LMS");
  return objLMS.SetScore(intScore, intMaxScore, intMinScore);
}

function SetPointBasedScore(intScore, intMaxScore, intMinScore) {
  WriteToDebug("In SetPointBasedScore, intScore=" + intScore + ", intMaxScore=" + intMaxScore + ", intMinScore=" + intMinScore);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  if (!IsValidDecimal(intScore)) {
    WriteToDebug("ERROR - intScore not a valid decimal");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Score passed to SetScore (not a valid decimal), intScore=" + intScore);
    return false;
  }
  if (!IsValidDecimal(intMaxScore)) {
    WriteToDebug("ERROR - intMaxScore not a valid decimal");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Max Score passed to SetScore (not a valid decimal), intMaxScore=" + intMaxScore);
    return false;
  }
  if (!IsValidDecimal(intMinScore)) {
    WriteToDebug("ERROR - intMinScore not a valid decimal");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Min Score passed to SetScore (not a valid decimal), intMinScore=" + intMinScore);
    return false;
  }
  WriteToDebug("Converting SCORES to floats");
  intScore = parseFloat(intScore);
  intMaxScore = parseFloat(intMaxScore);
  intMinScore = parseFloat(intMinScore);
  if (strLMSStandard == 'SCORM') {
    if (intScore < 0 || intScore > 100) {
      WriteToDebug("ERROR - intScore out of range");
      SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Score passed to SetScore (must be between 0-100), intScore=" + intScore);
      return false;
    }
    if (intMaxScore < 0 || intMaxScore > 100) {
      WriteToDebug("ERROR - intMaxScore out of range");
      SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Max Score passed to SetScore (must be between 0-100), intMaxScore=" + intMaxScore);
      return false;
    }
    if (intMinScore < 0 || intMinScore > 100) {
      WriteToDebug("ERROR - intMinScore out of range");
      SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Min Score passed to SetScore (must be between 0-100), intMinScore=" + intMinScore);
      return false;
    }
  }
  if (SCORE_CAN_ONLY_IMPROVE === true) {
    var previousScore = GetScore();
    if (previousScore != null && previousScore != "" && previousScore > intScore) {
      WriteToDebug("Previous score was greater than new score, configuration only allows scores to improve, returning.");
      return true;
    }
  }
  WriteToDebug("Calling to LMS");
  return objLMS.SetPointBasedScore(intScore, intMaxScore, intMinScore);
}

function CreateResponseIdentifier(strShort, strLong) {
  if (strShort.replace(" ", "") == "") {
    WriteToDebug("Short Identifier is empty");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid short identifier, strShort=" + strShort);
    return false;
  }
  if (strShort.length != 1) {
    WriteToDebug("ERROR - Short Identifier  not 1 character");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid short identifier, strShort=" + strShort);
    return false;
  }
  if (!IsAlphaNumeric(strShort)) {
    WriteToDebug("ERROR - Short Identifier  not alpha numeric");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid short identifier, strShort=" + strShort);
    return false;
  }
  strShort = strShort.toLowerCase();
  strLong = CreateValidIdentifier(strLong);
  return new ResponseIdentifier(strShort, strLong);
}

function ResponseIdentifier(strShort, strLong) {
  this.Short = new String(strShort);
  this.Long = new String(strLong);
  this.toString = function() {
    return "[Response Identifier " + this.Short + ", " + this.Long + "]";
  };
}

function MatchingResponse(source, target) {
  if (source.constructor == String) {
    source = CreateResponseIdentifier(source, source);
  }
  if (target.constructor == String) {
    target = CreateResponseIdentifier(target, target);
  }
  this.Source = source;
  this.Target = target;
  this.toString = function() {
    return "[Matching Response " + this.Source + ", " + this.Target + "]";
  };
}

function CreateMatchingResponse(pattern) {
  var aryPairs = new Array();
  var aryEachPair = new Array();
  pattern = new String(pattern);
  aryPairs = pattern.split("[,]");
  for (var i = 0; i < aryPairs.length; i++) {
    var thisPair = new String(aryPairs[i]);
    aryEachPair = thisPair.split("[.]");
    WriteToDebug("Matching Response [" + i + "]  source: " + aryEachPair[0] + "  target: " + aryEachPair[1]);
    aryPairs[i] = new MatchingResponse(aryEachPair[0], aryEachPair[1]);
  }
  WriteToDebug("pattern: " + pattern + " becomes " + aryPairs[0]);
  if (aryPairs.length == 0) return aryPairs[0];
  else return aryPairs;
}

function CreateValidIdentifier(str) {
  if (objLMS.Standard === "SCORM" || objLMS.Standard === "AICC") {
    return CreateValidIdentifierLegacy(str);
  } else {
    return CreateUriIdentifier(str, objLMS.Standard === "TCAPI");
  }
}

function CreateUriIdentifier(str, iri) {
  if (str === undefined || str === null || str === "") {
    return "";
  }
  str = Trim(str);
  var uri = new URI(str);
  if (!uri.is('absolute')) {
    str = 'urn:scormdriver:' + encodeURIComponent(str);
    uri = new URI(str);
  }
  uri.normalize();
  if (iri) {
    uri.iri();
  }
  return uri.toString();
}

function CreateValidIdentifierLegacy(str) {
  if (str != null || str != "") {
    str = new String(str);
    str = Trim(str);
    if (str.toLowerCase().indexOf("urn:") == 0) {
      str = str.substr(4);
    }
    str = str.replace(/[^\w\-\(\)\+\.\:\=\@\;\$\_\!\*\'\%]/g, "_");
    return str;
  } else {
    return "";
  }
}

function Trim(str) {
  str = str + '';
  str = str.replace(/^\s*/, "");
  str = str.replace(/\s*$/, "");
  return str;
}

function RecordTrueFalseInteraction(strID, blnResponse, blnCorrect, blnCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, intScoreRaw, intScoreMin) {
  strID = CreateValidIdentifier(strID);
  strLearningObjectiveID = CreateValidIdentifier(strLearningObjectiveID);
  WriteToDebug("In RecordTrueFalseInteraction strID=" + strID + ", blnResponse=" + blnResponse + ", blnCorrect=" + blnCorrect + ", blnCorrectResponse=" + blnCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID);
  if (!(typeof(DO_NOT_REPORT_INTERACTIONS) == "undefined") && DO_NOT_REPORT_INTERACTIONS === true) {
    WriteToDebug("Configuration specifies interactions should not be reported, exiting.");
    return true;
  }
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  if (blnResponse != true && blnResponse != false) {
    SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The Response parameter must be a valid boolean value.");
    return false;
  }
  if (blnCorrectResponse != null && blnCorrectResponse != true && blnCorrectResponse != false) {
    SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The Correct Response parameter must be a valid boolean value or null.");
    return false;
  }
  var dtmTime = new Date();
  WriteToDebug("Calling to LMS");
  if (objLMS.Standard != undefined && objLMS.Standard == "TCAPI" && intScoreRaw != undefined && intScoreMin != undefined) return objLMS.RecordTrueFalseInteraction(strID, blnResponse, blnCorrect, blnCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin);
  else return objLMS.RecordTrueFalseInteraction(strID, blnResponse, blnCorrect, blnCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime);
}

function RecordMultipleChoiceInteraction(strID, response, blnCorrect, correctResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, intScoreRaw, intScoreMin) {
  strID = CreateValidIdentifier(strID);
  strLearningObjectiveID = CreateValidIdentifier(strLearningObjectiveID);
  WriteToDebug("In RecordMultipleChoiceInteraction strID=" + strID + ", response=" + response + ", blnCorrect=" + blnCorrect + ", correctResponse=" + correctResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID);
  if (!(typeof(DO_NOT_REPORT_INTERACTIONS) == "undefined") && DO_NOT_REPORT_INTERACTIONS === true) {
    WriteToDebug("Configuration specifies interactions should not be reported, exiting.");
    return true;
  }
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  strID = new String(strID);
  var aryResponse;
  var aryCorrectResponse;
  if (response.constructor == String) {
    aryResponse = new Array();
    var responseIdentifier = CreateResponseIdentifier(response, response);
    if (responseIdentifier == false) {
      SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The response is not in the correct format");
      return false;
    }
    aryResponse[0] = responseIdentifier;
  } else if (response.constructor == ResponseIdentifier) {
    aryResponse = new Array();
    aryResponse[0] = response;
  } else if (response.constructor == Array || response.constructor.toString().search("Array") > 0) {
    aryResponse = response;
  } else if (window.console && response.constructor.toString() == '(Internal Function)' && response.length > 0) {
    aryResponse = response;
  } else {
    if (window.console) {
      window.console.log("ERROR_INVALID_INTERACTION_RESPONSE :: The response is not in the correct format.");
    }
    SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The response is not in the correct format");
    return false;
  }
  if (correctResponse != null && correctResponse != undefined && correctResponse != "") {
    if (correctResponse.constructor == String) {
      aryCorrectResponse = new Array();
      responseIdentifier = CreateResponseIdentifier(correctResponse, correctResponse);
      if (responseIdentifier == false) {
        SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The correct response is not in the correct format");
        return false;
      }
      aryCorrectResponse[0] = responseIdentifier;
    } else if (correctResponse.constructor == ResponseIdentifier) {
      aryCorrectResponse = new Array();
      aryCorrectResponse[0] = correctResponse;
    } else if (correctResponse.constructor == Array || correctResponse.constructor.toString().search("Array") > 0) {
      aryCorrectResponse = correctResponse;
    } else if (window.console && correctResponse.constructor.toString() == '(Internal Function)' && correctResponse.length > 0) {
      aryCorrectResponse = correctResponse;
    } else {
      SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The correct response is not in the correct format");
      return false;
    }
  } else {
    aryCorrectResponse = new Array();
  }
  var dtmTime = new Date();
  WriteToDebug("Calling to LMS");
  if (objLMS.Standard != undefined && objLMS.Standard == "TCAPI" && intScoreRaw != undefined && intScoreMin != undefined) return objLMS.RecordMultipleChoiceInteraction(strID, aryResponse, blnCorrect, aryCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin);
  else return objLMS.RecordMultipleChoiceInteraction(strID, aryResponse, blnCorrect, aryCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime);
}

function RecordFillInInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, intScoreRaw, intScoreMin) {
  strID = CreateValidIdentifier(strID);
  strLearningObjectiveID = CreateValidIdentifier(strLearningObjectiveID);
  WriteToDebug("In RecordFillInInteraction strID=" + strID + ", strResponse=" + strResponse + ", blnCorrect=" + blnCorrect + ", strCorrectResponse=" + strCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID);
  if (!(typeof(DO_NOT_REPORT_INTERACTIONS) == "undefined") && DO_NOT_REPORT_INTERACTIONS === true) {
    WriteToDebug("Configuration specifies interactions should not be reported, exiting.");
    return true;
  }
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  var dtmTime = new Date();
  WriteToDebug("Calling to LMS");
  if (objLMS.Standard != undefined && objLMS.Standard == "TCAPI" && intScoreRaw != undefined && intScoreMin != undefined) return objLMS.RecordFillInInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin);
  else return objLMS.RecordFillInInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime);
}

function RecordMatchingInteraction(strID, response, blnCorrect, correctResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, intScoreRaw, intScoreMin) {
  strID = CreateValidIdentifier(strID);
  strLearningObjectiveID = CreateValidIdentifier(strLearningObjectiveID);
  WriteToDebug("In RecordMatchingInteraction strID=" + strID + ", response=" + response + ", blnCorrect=" + blnCorrect + ", correctResponse=" + correctResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID);
  if (!(typeof(DO_NOT_REPORT_INTERACTIONS) == "undefined") && DO_NOT_REPORT_INTERACTIONS === true) {
    WriteToDebug("Configuration specifies interactions should not be reported, exiting.");
    return true;
  }
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  var aryResponse;
  var aryCorrectResponse;
  if (response.constructor == MatchingResponse) {
    aryResponse = new Array();
    aryResponse[0] = response;
  } else if (response.constructor == Array || response.constructor.toString().search("Array") > 0) {
    aryResponse = response;
  } else if (window.console && response.constructor.toString() == '(Internal Function)' && response.length > 0) {
    aryResponse = response;
  } else {
    SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The response is not in the correct format");
    return false;
  }
  if (correctResponse != null && correctResponse != undefined) {
    if (correctResponse.constructor == MatchingResponse) {
      aryCorrectResponse = new Array();
      aryCorrectResponse[0] = correctResponse;
    } else if (correctResponse.constructor == Array || correctResponse.constructor.toString().search("Array") > 0) {
      aryCorrectResponse = correctResponse;
    } else if (window.console && correctResponse.constructor.toString() == '(Internal Function)' && correctResponse.length > 0) {
      aryCorrectResponse = correctResponse;
    } else {
      SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The response is not in the correct format");
      return false;
    }
  } else {
    aryCorrectResponse = new Array();
  }
  var dtmTime = new Date();
  WriteToDebug("Calling to LMS");
  if (objLMS.Standard != undefined && objLMS.Standard == "TCAPI" && intScoreRaw != undefined && intScoreMin != undefined) return objLMS.RecordMatchingInteraction(strID, aryResponse, blnCorrect, aryCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin);
  else return objLMS.RecordMatchingInteraction(strID, aryResponse, blnCorrect, aryCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime);
}

function RecordPerformanceInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, intScoreRaw, intScoreMin) {
  strID = CreateValidIdentifier(strID);
  strLearningObjectiveID = CreateValidIdentifier(strLearningObjectiveID);
  WriteToDebug("In RecordPerformanceInteraction strID=" + strID + ", strResponse=" + strResponse + ", blnCorrect=" + blnCorrect + ", strCorrectResponse=" + strCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID);
  if (!(typeof(DO_NOT_REPORT_INTERACTIONS) == "undefined") && DO_NOT_REPORT_INTERACTIONS === true) {
    WriteToDebug("Configuration specifies interactions should not be reported, exiting.");
    return true;
  }
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  var dtmTime = new Date();
  WriteToDebug("Calling to LMS");
  if (objLMS.Standard != undefined && objLMS.Standard == "TCAPI" && intScoreRaw != undefined && intScoreMin != undefined) return objLMS.RecordPerformanceInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin);
  else return objLMS.RecordPerformanceInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime);
}

function RecordSequencingInteraction(strID, response, blnCorrect, correctResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, intScoreRaw, intScoreMin) {
  strID = CreateValidIdentifier(strID);
  strLearningObjectiveID = CreateValidIdentifier(strLearningObjectiveID);
  WriteToDebug("In RecordSequencingInteraction strID=" + strID + ", response=" + response + ", blnCorrect=" + blnCorrect + ", correctResponse=" + correctResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID);
  if (!(typeof(DO_NOT_REPORT_INTERACTIONS) == "undefined") && DO_NOT_REPORT_INTERACTIONS === true) {
    WriteToDebug("Configuration specifies interactions should not be reported, exiting.");
    return true;
  }
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  var aryResponse;
  var aryCorrectResponse;
  if (response.constructor == String) {
    aryResponse = new Array();
    var responseIdentifier = CreateResponseIdentifier(response, response);
    if (responseIdentifier == false) {
      SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The response is not in the correct format");
      return false;
    }
    aryResponse[0] = responseIdentifier;
  } else if (response.constructor == ResponseIdentifier) {
    aryResponse = new Array();
    aryResponse[0] = response;
  } else if (response.constructor == Array || response.constructor.toString().search("Array") > 0) {
    aryResponse = response;
  } else if (window.console && response.constructor.toString() == '(Internal Function)' && response.length > 0) {
    aryResponse = response;
  } else {
    SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The response is not in the correct format");
    return false;
  }
  if (correctResponse != null && correctResponse != undefined && correctResponse != "") {
    if (correctResponse.constructor == String) {
      aryCorrectResponse = new Array();
      responseIdentifier = CreateResponseIdentifier(correctResponse, correctResponse);
      if (responseIdentifier == false) {
        SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The correct response is not in the correct format");
        return false;
      }
      aryCorrectResponse[0] = responseIdentifier;
    } else if (correctResponse.constructor == ResponseIdentifier) {
      aryCorrectResponse = new Array();
      aryCorrectResponse[0] = correctResponse;
    } else if (correctResponse.constructor == Array || correctResponse.constructor.toString().search("Array") > 0) {
      aryCorrectResponse = correctResponse;
    } else if (window.console && correctResponse.constructor.toString() == '(Internal Function)' && correctResponse.length > 0) {
      aryCorrectResponse = correctResponse;
    } else {
      SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The correct response is not in the correct format");
      return false;
    }
  } else {
    aryCorrectResponse = new Array();
  }
  var dtmTime = new Date();
  WriteToDebug("Calling to LMS");
  if (objLMS.Standard != undefined && objLMS.Standard == "TCAPI" && intScoreRaw != undefined && intScoreMin != undefined) return objLMS.RecordSequencingInteraction(strID, aryResponse, blnCorrect, aryCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin);
  else return objLMS.RecordSequencingInteraction(strID, aryResponse, blnCorrect, aryCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime);
}

function RecordLikertInteraction(strID, response, blnCorrect, correctResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, intScoreRaw, intScoreMin) {
  strID = CreateValidIdentifier(strID);
  strLearningObjectiveID = CreateValidIdentifier(strLearningObjectiveID);
  WriteToDebug("In RecordLikertInteraction strID=" + strID + ", response=" + response + ", blnCorrect=" + blnCorrect + ", correctResponse=" + correctResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID);
  if (!(typeof(DO_NOT_REPORT_INTERACTIONS) == "undefined") && DO_NOT_REPORT_INTERACTIONS === true) {
    WriteToDebug("Configuration specifies interactions should not be reported, exiting.");
    return true;
  }
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  var riResponse;
  var riCorrectResponse;
  if (response.constructor == String) {
    riResponse = CreateResponseIdentifier(response, response);
  } else if (response.constructor == ResponseIdentifier) {
    riResponse = response;
  } else {
    SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The response is not in the correct format");
    return false;
  }
  if (correctResponse == null || correctResponse == undefined) {
    riCorrectResponse = null;
  } else if (correctResponse.constructor == ResponseIdentifier) {
    riCorrectResponse = correctResponse;
  } else if (correctResponse.constructor == String) {
    riCorrectResponse = CreateResponseIdentifier(correctResponse, correctResponse);
  } else {
    SetErrorInfo(ERROR_INVALID_INTERACTION_RESPONSE, "The response is not in the correct format");
    return false;
  }
  var dtmTime = new Date();
  WriteToDebug("Calling to LMS");
  if (objLMS.Standard != undefined && objLMS.Standard == "TCAPI" && intScoreRaw != undefined && intScoreMin != undefined) return objLMS.RecordLikertInteraction(strID, riResponse, blnCorrect, riCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin);
  else return objLMS.RecordLikertInteraction(strID, riResponse, blnCorrect, riCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime);
}

function RecordNumericInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, intScoreRaw, intScoreMin) {
  strID = CreateValidIdentifier(strID);
  strLearningObjectiveID = CreateValidIdentifier(strLearningObjectiveID);
  WriteToDebug("In RecordNumericInteraction strID=" + strID + ", strResponse=" + strResponse + ", blnCorrect=" + blnCorrect + ", strCorrectResponse=" + strCorrectResponse + ", strDescription=" + strDescription + ", intWeighting=" + intWeighting + ", intLatency=" + intLatency + ", strLearningObjectiveID=" + strLearningObjectiveID);
  if (!(typeof(DO_NOT_REPORT_INTERACTIONS) == "undefined") && DO_NOT_REPORT_INTERACTIONS === true) {
    WriteToDebug("Configuration specifies interactions should not be reported, exiting.");
    return true;
  }
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  if (!IsValidDecimal(strResponse)) {
    WriteToDebug("ERROR - Invalid Response, not a valid decmial");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Response passed to RecordNumericInteraction (not a valid decimal), strResponse=" + strResponse);
    return false;
  }
  if (strCorrectResponse != undefined && strCorrectResponse != null && IsValidDecimal(strCorrectResponse) == false) {
    WriteToDebug("ERROR - Invalid Correct Response, not a valid decmial");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Correct Response passed to RecordNumericInteraction (not a valid decimal), strCorrectResponse=" + strCorrectResponse);
    return false;
  }
  var dtmTime = new Date();
  WriteToDebug("Calling to LMS");
  if (objLMS.Standard != undefined && objLMS.Standard == "TCAPI" && intScoreRaw != undefined && intScoreMin != undefined) return objLMS.RecordNumericInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime, intScoreRaw, intScoreMin);
  else return objLMS.RecordNumericInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting, intLatency, strLearningObjectiveID, dtmTime);
}

function GetStatus() {
  WriteToDebug("In GetStatus");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return LESSON_STATUS_INCOMPLETE;
  }
  return objLMS.GetStatus();
}

function ResetStatus() {
  WriteToDebug("In ResetStatus");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  WriteToDebug("Setting blnStatusWasSet to false");
  blnStatusWasSet = false;
  return objLMS.ResetStatus();
}

function GetProgressMeasure() {
  WriteToDebug("In GetProgressMeasure");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return LESSON_STATUS_INCOMPLETE;
  }
  return objLMS.GetProgressMeasure();
}

function SetProgressMeasure(numMeasure) {
  WriteToDebug("In SetProgressMeasure, passing in: " + numMeasure);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return LESSON_STATUS_INCOMPLETE;
  }
  return objLMS.SetProgressMeasure(numMeasure);
}

function SetPassed() {
  WriteToDebug("In SetPassed");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  WriteToDebug("Setting blnStatusWasSet to true");
  blnStatusWasSet = true;
  return objLMS.SetPassed();
}

function SetFailed() {
  WriteToDebug("In SetFailed");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  WriteToDebug("Setting blnStatusWasSet to true");
  blnStatusWasSet = true;
  return objLMS.SetFailed();
}

function GetEntryMode() {
  WriteToDebug("In GetEntryMode");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return ENTRY_FIRST_TIME;
  }
  return objLMS.GetEntryMode();
}

function GetLessonMode() {
  WriteToDebug("In GetLessonMode");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return MODE_NORMAL;
  }
  return objLMS.GetLessonMode();
}

function GetTakingForCredit() {
  WriteToDebug("In GetTakingForCredit");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetTakingForCredit();
}

function SetObjectiveScore(strObjectiveID, intScore, intMaxScore, intMinScore) {
  WriteToDebug("In SetObjectiveScore, intObjectiveID=" + strObjectiveID + ", intScore=" + intScore + ", intMaxScore=" + intMaxScore + ", intMinScore=" + intMinScore);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  strObjectiveID = new String(strObjectiveID);
  if (strObjectiveID.replace(" ", "") == "") {
    WriteToDebug("ERROR - Invalid ObjectiveID, empty string");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid ObjectiveID passed to SetObjectiveScore (must have a value), strObjectiveID=" + strObjectiveID);
    return false;
  }
  if (!IsValidDecimal(intScore)) {
    WriteToDebug("ERROR - Invalid Score, not a valid decmial");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Score passed to SetObjectiveScore (not a valid decimal), intScore=" + intScore);
    return false;
  }
  if (!IsValidDecimal(intMaxScore)) {
    WriteToDebug("ERROR - Invalid Max Score, not a valid decmial");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Max Score passed to SetObjectiveScore (not a valid decimal), intMaxScore=" + intMaxScore);
    return false;
  }
  if (!IsValidDecimal(intMinScore)) {
    WriteToDebug("ERROR - Invalid Min Score, not a valid decmial");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Min Score passed to SetObjectiveScore (not a valid decimal), intMinScore=" + intMinScore);
    return false;
  }
  WriteToDebug("Converting Scores to floats");
  intScore = parseFloat(intScore);
  intMaxScore = parseFloat(intMaxScore);
  intMinScore = parseFloat(intMinScore);
  if (intScore < 0 || intScore > 100) {
    WriteToDebug("ERROR - Invalid Score, out of range");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Score passed to SetObjectiveScore (must be between 0-100), intScore=" + intScore);
    return false;
  }
  if (intMaxScore < 0 || intMaxScore > 100) {
    WriteToDebug("ERROR - Invalid Max Score, out of range");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Max Score passed to SetObjectiveScore (must be between 0-100), intMaxScore=" + intMaxScore);
    return false;
  }
  if (intMinScore < 0 || intMinScore > 100) {
    WriteToDebug("ERROR - Invalid Min Score, out of range");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Min Score passed to SetObjectiveScore (must be between 0-100), intMinScore=" + intMinScore);
    return false;
  }
  WriteToDebug("Calling To LMS");
  return objLMS.SetObjectiveScore(strObjectiveID, intScore, intMaxScore, intMinScore);
}

function SetObjectiveStatus(strObjectiveID, Lesson_Status) {
  WriteToDebug("In SetObjectiveStatus strObjectiveID=" + strObjectiveID + ", Lesson_Status=" + Lesson_Status);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  strObjectiveID = new String(strObjectiveID);
  if (strObjectiveID.replace(" ", "") == "") {
    WriteToDebug("ERROR - Invalid ObjectiveID, empty string");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid ObjectiveID passed to SetObjectiveStatus (must have a value), strObjectiveID=" + strObjectiveID);
    return false;
  }
  if ((Lesson_Status != LESSON_STATUS_PASSED) && (Lesson_Status != LESSON_STATUS_COMPLETED) && (Lesson_Status != LESSON_STATUS_FAILED) && (Lesson_Status != LESSON_STATUS_INCOMPLETE) && (Lesson_Status != LESSON_STATUS_BROWSED) && (Lesson_Status != LESSON_STATUS_NOT_ATTEMPTED)) {
    WriteToDebug("ERROR - Invalid Status");
    SetErrorInfo(ERROR_INVALID_STATUS, "Invalid status passed to SetObjectiveStatus, Lesson_Status=" + Lesson_Status);
    return false;
  }
  WriteToDebug("Calling To LMS");
  return objLMS.SetObjectiveStatus(strObjectiveID, Lesson_Status);
}

function GetObjectiveStatus(strObjectiveID) {
  WriteToDebug("In GetObjectiveStatus, strObjectiveID=" + strObjectiveID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetObjectiveStatus(strObjectiveID);
}

function SetObjectiveDescription(strObjectiveID, strObjectiveDescription) {
  WriteToDebug("In SetObjectiveDescription strObjectiveID=" + strObjectiveID + ", strObjectiveDescription=" + strObjectiveDescription);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  strObjectiveID = new String(strObjectiveID);
  if (strObjectiveID.replace(" ", "") == "") {
    WriteToDebug("ERROR - Invalid ObjectiveID, empty string");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid ObjectiveID passed to SetObjectiveStatus (must have a value), strObjectiveID=" + strObjectiveID);
    return false;
  }
  WriteToDebug("Calling To LMS");
  return objLMS.SetObjectiveDescription(strObjectiveID, strObjectiveDescription);
}

function GetObjectiveDescription(strObjectiveID) {
  WriteToDebug("In GetObjectiveDescription, strObjectiveID=" + strObjectiveID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetObjectiveDescription(strObjectiveID);
}

function GetObjectiveScore(strObjectiveID) {
  WriteToDebug("In GetObjectiveScore, strObjectiveID=" + strObjectiveID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetObjectiveScore(strObjectiveID);
}

function IsLmsPresent() {
  return blnLmsPresent;
}

function SetObjectiveProgressMeasure(strObjectiveID, strObjectiveProgressMeasure) {
  WriteToDebug("In SetObjectiveProgressMeasure strObjectiveID=" + strObjectiveID + ", strObjectiveProgressMeasure=" + strObjectiveProgressMeasure);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  strObjectiveID = new String(strObjectiveID);
  if (strObjectiveID.replace(" ", "") == "") {
    WriteToDebug("ERROR - Invalid ObjectiveID, empty string");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid ObjectiveID passed to SetObjectiveProgressMeasure (must have a value), strObjectiveID=" + strObjectiveID);
    return false;
  }
  WriteToDebug("Calling To LMS");
  return objLMS.SetObjectiveProgressMeasure(strObjectiveID, strObjectiveProgressMeasure);
}

function GetObjectiveProgressMeasure(strObjectiveID) {
  WriteToDebug("In GetObjectiveProgressMeasure, strObjectiveID=" + strObjectiveID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetObjectiveProgressMeasure(strObjectiveID);
}

function SetNavigationRequest(strNavRequest) {
  WriteToDebug("In SetNavigationRequest");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.SetNavigationRequest(strNavRequest);
}

function GetNavigationRequest() {
  WriteToDebug("In GetNavigationRequest");
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetNavigationRequest();
}

function GetInteractionType(strInteractionID) {
  strInteractionID = CreateValidIdentifier(strInteractionID);
  WriteToDebug("In GetInteractionType, strInteractionID=" + strInteractionID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetInteractionType(strInteractionID);
}

function GetInteractionTimestamp(strInteractionID) {
  strInteractionID = CreateValidIdentifier(strInteractionID);
  WriteToDebug("In GetInteractionTimestamp, strInteractionID=" + strInteractionID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetInteractionTimestamp(strInteractionID);
}

function GetInteractionCorrectResponses(strInteractionID) {
  strInteractionID = CreateValidIdentifier(strInteractionID);
  WriteToDebug("In GetInteractionCorrectResponses, strInteractionID=" + strInteractionID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetInteractionCorrectResponses(strInteractionID);
}

function GetInteractionWeighting(strInteractionID) {
  strInteractionID = CreateValidIdentifier(strInteractionID);
  WriteToDebug("In GetInteractionWeighting, strInteractionID=" + strInteractionID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetInteractionWeighting(strInteractionID);
}

function GetInteractionLearnerResponses(strInteractionID) {
  strInteractionID = CreateValidIdentifier(strInteractionID);
  WriteToDebug("In GetInteractionLearnerResponses, strInteractionID=" + strInteractionID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetInteractionLearnerResponses(strInteractionID);
}

function GetInteractionResult(strInteractionID) {
  strInteractionID = CreateValidIdentifier(strInteractionID);
  WriteToDebug("In GetInteractionResult, strInteractionID=" + strInteractionID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetInteractionResult(strInteractionID);
}

function GetInteractionLatency(strInteractionID) {
  strInteractionID = CreateValidIdentifier(strInteractionID);
  WriteToDebug("In GetInteractionLatency, strInteractionID=" + strInteractionID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetInteractionLatency(strInteractionID);
}

function GetInteractionDescription(strInteractionID) {
  strInteractionID = CreateValidIdentifier(strInteractionID);
  WriteToDebug("In GetInteractionDescription, strInteractionID=" + strInteractionID);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  return objLMS.GetInteractionDescription(strInteractionID);
}

function CreateDataBucket(strBucketId, intMinSize, intMaxSize) {
  WriteToDebug("In CreateDataBucket, strBucketId=" + strBucketId + ", intMinSize=" + intMinSize + ", intMaxSize=" + intMaxSize);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  strBucketId = new String(strBucketId);
  if (strBucketId.replace(" ", "") == "") {
    WriteToDebug("ERROR - Invalid BucketId, empty string");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid strBucketId passed to CreateDataBucket (must have a value), strBucketId=" + strBucketId);
    return false;
  }
  if (!ValidInteger(intMinSize)) {
    WriteToDebug("ERROR Invalid Min Size, not an integer");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid intMinSize passed to CreateDataBucket (not an integer), intMinSize=" + intMinSize);
    return false;
  }
  if (!ValidInteger(intMaxSize)) {
    WriteToDebug("ERROR Invalid Max Size, not an integer");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid intMaxSize passed to CreateDataBucket (not an integer), intMaxSize=" + intMaxSize);
    return false;
  }
  intMinSize = parseInt(intMinSize, 10);
  intMaxSize = parseInt(intMaxSize, 10);
  if (intMinSize < 0) {
    WriteToDebug("ERROR Invalid Min Size, must be greater than or equal to 0");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Min Size passed to CreateDataBucket (must be greater than or equal to 0), intMinSize=" + intMinSize);
    return false;
  }
  if (intMaxSize <= 0) {
    WriteToDebug("ERROR Invalid Max Size, must be greater than 0");
    SetErrorInfo(ERROR_INVALID_NUMBER, "Invalid Max Size passed to CreateDataBucket (must be greater than 0), intMaxSize=" + intMaxSize);
    return false;
  }
  intMinSize = (intMinSize * 2);
  intMaxSize = (intMaxSize * 2);
  return objLMS.CreateDataBucket(strBucketId, intMinSize, intMaxSize);
}

function GetDataFromBucket(strBucketId) {
  WriteToDebug("In GetDataFromBucket, strBucketId=" + strBucketId);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  strBucketId = new String(strBucketId);
  if (strBucketId.replace(" ", "") == "") {
    WriteToDebug("ERROR - Invalid BucketId, empty string");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid strBucketId passed to GetDataFromBucket (must have a value), strBucketId=" + strBucketId);
    return false;
  }
  return objLMS.GetDataFromBucket(strBucketId);
}

function PutDataInBucket(strBucketId, strData, blnAppendToEnd) {
  WriteToDebug("In PutDataInBucket, strBucketId=" + strBucketId + ", blnAppendToEnd=" + blnAppendToEnd + ", strData=" + strData);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  strBucketId = new String(strBucketId);
  if (strBucketId.replace(" ", "") == "") {
    WriteToDebug("ERROR - Invalid BucketId, empty string");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid strBucketId passed to PutDataInBucket (must have a value), strBucketId=" + strBucketId);
    return false;
  }
  if (blnAppendToEnd != true) {
    WriteToDebug("blnAppendToEnd was not explicitly true so setting it to false, blnAppendToEnd=" + blnAppendToEnd);
    blnAppendToEnd = false;
  }
  return objLMS.PutDataInBucket(strBucketId, strData, blnAppendToEnd);
}

function DetectSSPSupport() {
  return objLMS.DetectSSPSupport();
}

function GetBucketInfo(strBucketId) {
  WriteToDebug("In GetBucketInfo, strBucketId=" + strBucketId);
  ClearErrorInfo();
  if (!IsLoaded()) {
    SetErrorInfo(ERROR_NOT_LOADED, "Cannot make calls to the LMS before calling Start");
    return false;
  }
  strBucketId = new String(strBucketId);
  if (strBucketId.replace(" ", "") == "") {
    WriteToDebug("ERROR - Invalid BucketId, empty string");
    SetErrorInfo(ERROR_INVALID_ID, "Invalid strBucketId passed to GetBucketInfo (must have a value), strBucketId=" + strBucketId);
    return false;
  }
  var bucketInfo = objLMS.GetBucketInfo(strBucketId);
  bucketInfo.TotalSpace = (bucketInfo.TotalSpace / 2);
  bucketInfo.UsedSpace = (bucketInfo.UsedSpace / 2);
  WriteToDebug("GetBucketInfo returning " + bucketInfo);
  return bucketInfo;
}

function SSPBucketSize(totalSpace, usedSpace) {
  this.TotalSpace = totalSpace;
  this.UsedSpace = usedSpace;
  this.toString = function() {
    return "[SSPBucketSize " + this.TotalSpace + ", " + this.UsedSpace + "]";
  };
}
var textFile = null,
  makeTextFile = function(text) {
    var data = new Blob([text], {
      type: 'text/plain'
    });
    // If we are replacing a previously generated file we need to
    // manually revoke the object URL to avoid memory leaks.
    if (textFile !== null) {
      window.URL.revokeObjectURL(textFile);
    }
    textFile = window.URL.createObjectURL(data);
    // returns a URL you can use as a href
    return textFile;
  };

function downloadDebugLogs() {
  var iframeTag = getDebugLogsIFrame();
  var debugDoc = iframeTag.document;
  var logTags = debugDoc.getElementsByClassName("debugLog");
  var textString;
  var i;
  for (i = 0; i < logTags.length; i++) {
    var text = logTags[i].innerHTML;
    textString += text;
    textString += "\n";
  }
  var save = document.createElement('a');
  save.href = makeTextFile(textString);;
  save.target = '_blank';
  save.download = "DebugLogs.log";

  var event = document.createEvent('Event');
  event.initEvent('click', true, true);
  save.dispatchEvent(event);
  (window.URL || window.webkitURL).revokeObjectURL(save.href);
}

function getDebugLogsIFrame() {
  var lCurrentWindow = window;
  var lNoOfLoops = 0;
  if (lCurrentWindow) {
    while (1) {
      if (lCurrentWindow.document.debugWin) {
        return lCurrentWindow.document.debugWin;
      }
      var lParentWindow = lCurrentWindow.parent;
      if (lParentWindow == lCurrentWindow)
        return "";
      if (lParentWindow == undefined)
        return "";
      lCurrentWindow = lParentWindow;
      lNoOfLoops += 1;
      if (lNoOfLoops > 10)
        return "";
    }
  }
}
var g_backgroundColor = "#FFFFFF";

function setBackgroundColorForLogs(aColor) {
  g_backgroundColor = aColor;
}

function getBackgroundColorForLogs() {
  return g_backgroundColor;
}
//initializeCommunication = Start;
var API_1484_11 = new LMSStandardAPI('TCAPI');

//terminateCommunication=Unload;
//window.onbeforeunload 	= Unload;
//window.onunload 		= Unload;
