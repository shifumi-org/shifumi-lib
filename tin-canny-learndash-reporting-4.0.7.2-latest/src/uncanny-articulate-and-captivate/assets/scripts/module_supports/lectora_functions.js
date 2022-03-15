/*******************************************************************************
 * * * Filename: tincanfunctions.js * * File Description: This file contains
 * several JavaScript functions that are * used to communicate with a Tin Can
 * LRS * * References: Tin Can API *
 ******************************************************************************/

var tcFinishCalled = false;
var ERR_NONE = 0;
var ERR_FROMLMS = 1;
var ERR_INVNUM = 2;
var ERR_INVID = 3;
var ERR_NOLMS = 4;
var ERR_INVINTERACT = 5;
var DEBUG_LMS = 16;

var TinCanVersion = '1.0.0';
var TinCanStmtVersion = '1.0.0';
var TinCanStatus = '';
var TinCanScore = '';
var TinCanCompStatus = '';
var TinCanSatStatus = '';
var TinCanUpdPending = false;
var TinCanProcessing = false;
var TinCanBookmark = '';
var TinCanSuspend = '';
var bDelayTCSuspend = false;
var tc_driver = null;
var tcapi_cache = null;

var bTCCalledFinish = false;
var bTCLoaded = false;
var bTCStatusWasSet = false;
var dTCStart = null;
var dTCEnd = null;
var intAccumulatedMS = 0;
var blnOverrodeTime = false;
var intTimeOverrideMS = null;
var intError = ERR_NONE;
var strErrorDesc = "";

function convertTotalMills(ts)
{
  var Sec = 0;
  var Min = 0;
  var Hour = 0;
  while (ts >= 3600000)
  {
    Hour += 1;
    ts -= 3600000;
  }
  while (ts >= 60000)
  {
    Min += 1;
    ts -= 60000;
  }
  while (ts >= 1000)
  {
    Sec += 1;
    ts -= 1000;
  }
  if (Hour < 10)
    Hour = "0" + Hour;
  if (Min < 10)
    Min = "0" + Min;
  if (Sec < 10)
    Sec = "0" + Sec;
  var rtnVal = Hour + ":" + Min + ":" + Sec;
  return rtnVal;
}

function LMSGetValue_(name)
{
  if (tcFinishCalled == true)
    return "";
  else
  {
    var valString = '';

    if (name == 'cmi.core.lesson_status')
      valString = TinCanStatus;
    else if (name == 'cmi.core.student_name')
      valString = getStudentName(tc_driver.actor);
    else if (name == 'cmi.core.lesson_location')
      valString = LMSGetBookmark();
    else if (name == 'cmi.suspend_data' && TinCanSuspend != null && TinCanSuspend.length > 0)
      valString = TinCanSuspend;
    else if (name == 'cmi.core.session_time')
      valString = convertTotalMills(GetSessionAccumulatedTime());
    else
    {
      ResetErrorStatus();
      if (bTCLoaded)
        valString = TinCanGetInfo(tc_driver, name);
      else
        valString = "";
    }

    if (valString == null)
    {
      if (name == 'cmi.core.score.raw')
        valString = TinCanScore;
      else if (name == 'cmi.suspend_data')
        valString = '';
    }

    if (valString != null)
    {
      if (name == 'cmi.core.score.raw')
        TinCanScore = valString;
      else if (name == 'cmi.suspend_data')
        TinCanSuspend = valString;
    }

    trivLogMsg('LMSGetValue for ' + name + ' = [' + valString + ']', DEBUG_LMS);
    return valString;
  }
}

function LMSGetValue(name)
{
  return LMSGetValue_(name);
}

function LMSSetValue_(name, value)
{
  if (tcFinishCalled != true)
  {
    if (name == 'cmi.core.lesson_status')
      TCSetStatus(value);
    else
    {
      ResetErrorStatus();
      if (bTCLoaded)
      {
        if (name == 'cmi.suspend_data' && value.length >= TinCanSuspend.length - 1
          && value.length <= TinCanSuspend.length + 1)
          bDelayTCSuspend = true;
        else
        {
          if ( name.length > 0 )
            TinCanSetInfo(tc_driver, name, value);
          if (bDelayTCSuspend)
          {
            TinCanSetInfo(tc_driver, 'cmi.suspend_data', TinCanSuspend);
            bDelayTCSuspend = false;
          }
        }
      }
    }

    if (name == 'cmi.core.score.raw')
    {
      TinCanScore = value;
      TinCanUpdPending = true;
    }
    else if (name == 'cmi.suspend_data')
      TinCanSuspend = value;

    trivLogMsg('LMSSetValue for ' + name + ' to [' + value + ']', DEBUG_LMS);
  }

  return;
}

function LMSSetValue(name, value)
{
  return LMSSetValue_(name, value);
}

function CheckValidInt(intNum)
{
  var str = new String(intNum);
  if (str.indexOf("-", 0) == 0)
    str = str.substring(1, str.length - 1);

  var regValidChars = new RegExp("[^0-9]");
  if (str.search(regValidChars) == -1)
    return true;
  return false;
}

function ConvertToTime(ms)
{
  trivLogMsg("In ConvertToTime ms=" + ms, DEBUG_LMS);
  var retTime = "";
  var hoas;
  var secs;
  var mins;
  var hours;
  var days;
  var mns;
  var yrs;
  var hpm = 100 * 60 * 60 * 24 * (((365 * 4) + 1) / 48);
  hoas = Math.floor(ms / 10);
  yrs = Math.floor(hoas / (hpm * 12));
  hoas -= (yrs * hpm * 12);
  mns = Math.floor(hoas / hpm);
  hoas -= (mns * hpm);
  days = Math.floor(hoas / (100 * 60 * 60 * 24));
  hoas -= (days * 100 * 60 * 60 * 24);
  hours = Math.floor(hoas / (100 * 60 * 60));
  hoas -= (hours * 100 * 60 * 60);
  mins = Math.floor(hoas / (100 * 60));
  hoas -= (mins * 100 * 60);
  secs = Math.floor(hoas / 100);
  hoas -= (secs * 100);
  if (yrs > 0)
  {
    retTime += yrs + "Y";
  }
  if (mns > 0)
  {
    retTime += mns + "M";
  }
  if (days > 0)
  {
    retTime += days + "D";
  }
  if ((hoas + secs + mins + hours) > 0)
  {
    retTime += "T";
    if (hours > 0)
    {
      retTime += hours + "H";
    }
    if (mins > 0)
    {
      retTime += mins + "M";
    }
    if ((hoas + secs) > 0)
    {
      retTime += secs;
      if (hoas > 0)
      {
        retTime += "." + hoas;
      }
      retTime += "S";
    }
  }
  if (retTime == "")
  {
    retTime = "0S";
  }
  retTime = "P" + retTime;
  trivLogMsg("Returning-" + retTime, DEBUG_LMS);
  return retTime;
}

function IsValidDec(strValue)
{
  strValue = new String(strValue);
  if (strValue.search(/[^.\d-]/) > -1)
    return false;
  if (strValue.search("-") > -1)
  {
    if (strValue.indexOf("-", 1) > -1)
      return false;
  }
  if (strValue.indexOf(".") != strValue.lastIndexOf("."))
    return false;
  if (strValue.search(/\d/) < 0)
    return false;
  return true;
}

function isalnum(strValue)
{
  return strValue.search(/\w/) >= 0;
}

function trimleft(str)
{
  str = new String(str);
  return (str.replace(/^\s+/, ''));
}

function trimright(str)
{
  str = new String(str);
  return (str.replace(/\s+$/, ''));
}

// defined again below.
//function Trim(strToTrim)
//{
//	var str = trimleft(trimright(strToTrim));
//	return (str.replace(/\s{2,}/g, " "));
//}

function isurl(urlStr)
{
  return urlStr != null && (urlStr.indexOf("http://") == 0 || urlStr.indexOf("https://") == 0);
}

function TinCanConfigObject( activityName, activityDesc )
{
  trivLogMsg("TinCanConfigObject", DEBUG_LMS);
  var lrsProps = [ "endpoint", "auth" ];
  var singleProps = [ "activity_id", "grouping", "activity_platform", "registration" ];
  var singlePropMap = {
    activity_id : "activityId",
    grouping : "grouping",
    activity_platform : "activityPlatform",
    registration : "registration"
  };
  var lrs = {};
  var qsVars = parseSearch();
  var prop;
  var i;

  var result = {
    _isIE : (typeof XDomainRequest !== "undefined"),
    recordStores : [],
    actor : null,
    agentJSON : null,
    activityId : null,
    activityName : {},
    activityDesc : {},
    activityPlatform : null,
    grouping : null,
    registration : null
  };

  if (qsVars.hasOwnProperty("actor"))
  {
    try
    {
      var actor = JSON.parse(qsVars.actor);
      if (actor.mbox !== undefined)
      {
        if (actor.name !== undefined)
          result.actor = {
            name : actor.name,
            mbox : actor.mbox
          };
        else
          result.actor = {
            mbox : actor.mbox
          };
      }
      else
        result.actor = {
          objectType : actor.objectType,
          name : actor.name[0],
          account : {
            name : actor.account[0].accountName,
            homePage : actor.account[0].accountServiceHomePage
          }
        };
      result.agentJSON = JSON.stringify(result.actor);
      delete qsVars.actor;
    } catch (ex)
    {
      trivLogMsg("TinCanConfigObject - failed to parse actor: " + ex, DEBUG_LMS);
    }
  }
  for (i = 0; i < singleProps.length; i += 1)
  {
    prop = singleProps[i];
    if (qsVars.hasOwnProperty(prop))
    {
      result[singlePropMap[prop]] = qsVars[prop];
      delete qsVars[prop];
    }
  }
  if (qsVars.hasOwnProperty("endpoint"))
  {
    for (i = 0; i < lrsProps.length; i += 1)
    {
      prop = lrsProps[i];
      if (qsVars.hasOwnProperty(prop))
      {
        lrs[prop] = qsVars[prop];
        delete qsVars[prop];
      }
    }
    lrs.extended = qsVars;
    lrs.allowFail = false;
    TinCanAddRecordStore(result, lrs);
  }

  result.activityName = activityName;
  result.activityDesc = activityDesc;
  result.courseActivityObject =
    {
      "id" : result.activityId,
      "definition" :
        {
          "name" :
            {
              "en-US" : result.activityName
            },
          "description" :
            {
              "en-US" : result.activityDesc
            }
        }
    };

  /*
     * if(result.recordStores.length===0) { trivLogMsg("TinCanConfigObject -
     * resulted in no LRS: DATA CANNOT BE STORED", DEBUG_LMS); alert("[error] No
     * LRS: DATA CANNOT BE STORED"); throw{code:1,mesg:"No LRS: DATA CANNOT BE
     * STORED"}; }
     */
  return result;
}

function TinCanAddRecordStore(driver, cfg)
{
  var urlParts;
  var schemeMatches;
  var isXD;

  trivLogMsg("TinCanAddRecordStore", DEBUG_LMS);
  if (!cfg.hasOwnProperty("endpoint"))
  {
    alert("[error] LRS invalid: no endpoint");
    throw {
      code : 3,
      mesg : "LRS invalid: no endpoint"
    };
  }
  if (!cfg.hasOwnProperty("allowFail"))
    cfg.allowFail = true;

  urlParts = cfg.endpoint.toLowerCase().match(/^(.+:)\/\/([^:\/]+):?(\d+)?(\/.*)?$/);
  schemeMatches = location.protocol.toLowerCase() === urlParts[1];
  isXD = (!schemeMatches || location.hostname.toLowerCase() !== urlParts[2] || location.port !== (urlParts[3] !== null ? urlParts[3]
    : (urlParts[1] === 'http:' ? '80' : '443')));
  if (isXD && driver._isIE)
  {
    if (schemeMatches)
    {
      cfg._requestMode = "ie";
      driver.recordStores.push(cfg);
    }
    else
    {
      if (cfg.allowFail)
        alert("[warning] LRS invalid: cross domain request for differing scheme in IE");
      else
      {
        alert("[error] LRS invalid: cross domain request for differing scheme in IE");
        throw {
          code : 2,
          mesg : "LRS invalid: cross domain request for differing scheme in IE"
        };
      }
    }
  }
  else
  {
    cfg._requestMode = "native";
    trivLogMsg("  " + JSON.stringify(cfg, null, 4), DEBUG_LMS);
    driver.recordStores.push(cfg);
  }
}

function TinCanSendStatement(driver, stmt, cbfn)
{
  var lrs, statementId = GenerateRandomID(), cbW, rsCount = driver.recordStores.length, i;

  _TinCanPrepareStatement(driver, stmt);
  var jsonStmt = JSON.stringify(stmt);
  trivLogMsg("TinCanSendStatement:\n" + jsonStmt + "\n", DEBUG_LMS);

  if (rsCount > 0)
  {
    trivLogMsg("  sending...", DEBUG_LMS);
    if (rsCount === 1)
      cbW = cbfn;
    else
    {
      if (typeof cbfn === "function")
      {
        cbW = function()
        {
          trivLogMsg("TinCanSendStatement - cbW: " + rsCount, DEBUG_LMS);
          if (rsCount > 1)
            rsCount -= 1;
          else if (rsCount === 1)
            cbfn.apply(this, arguments);
        };
      }
    }
    for (i = 0; i < rsCount; i += 1)
    {
      lrs = driver.recordStores[i];
      _TinCanXHR_request(lrs, "statements?statementId=" + statementId, "PUT", jsonStmt, cbW);
    }
  }
}

function TinCanSendMultiStatements(driver, stmts, cbfn)
{
  var lrs, cbW, rsCount = driver.recordStores.length, i;

  if (!stmts || !stmts.length || stmts.length < 1)
    return;

  for (i = 0; i < stmts.length; i++)
    _TinCanPrepareStatement(driver, stmts[i]);
  var statementsJson = JSON.stringify(stmts);
  trivLogMsg("  TinCanSendMultiStatements:\n" + statementsJson + "\n", DEBUG_LMS);

  if (rsCount > 0)
  {
    trivLogMsg("  TinCanSendMultiStatements: sending ...", DEBUG_LMS);

    if (rsCount === 1)
      cbW = cbfn;
    else
    {
      if (typeof cbfn === "function")
      {
        cbW = function()
        {
          trivLogMsg("TinCanSendMultiStatements - cbW: " + rsCount, DEBUG_LMS);
          if (rsCount > 1)
            rsCount -= 1;
          else if (rsCount === 1)
            cbfn.apply(this, arguments);
        };
      }
    }
    for (i = 0; i < rsCount; i += 1)
    {
      lrs = driver.recordStores[i];
      _TinCanXHR_request(lrs, "statements", "POST", statementsJson, cbW);
    }
  }
}

function TinCanGetInfo(driver, getKey, cbfn)
{
  trivLogMsg("TinCanGetInfo: " + getKey, DEBUG_LMS);
  if (driver.recordStores.length > 0)
  {
    if( getKey == 'cmi.core.lesson_location' ){
      getKey = 'bookmark';
    }
    var lrs = driver.recordStores[0];
    var url = "activities/state?" + "activityId=" + encodeURIComponent(driver.activityId) + "&agent="
      + encodeURIComponent(driver.agentJSON) + "&stateId=" + encodeURIComponent(getKey);
    if (driver.registration !== null)
      url += "&registration=" + encodeURIComponent(driver.registration);

    var result = _TinCanXHR_request(lrs, url, "GET", null, cbfn, true);
    return (typeof result === "undefined" || result.status === 404) ? null : result.responseText;
  }
}

function TinCanSetInfo(driver, setKey, setVal, cbfn)
{
  trivLogMsg("TinCanSetInfo: " + setKey, DEBUG_LMS);
  if (driver.recordStores.length > 0)
  {
    if( setKey == 'cmi.core.lesson_location' ){
      setKey = 'bookmark';
    }
    var lrs = driver.recordStores[0];
    var url = "activities/state?" + "activityId=" + encodeURIComponent(driver.activityId) + "&agent="
      + encodeURIComponent(driver.agentJSON) + "&stateId=" + encodeURIComponent(setKey);
    if (driver.registration !== null)
      url += "&registration=" + encodeURIComponent(driver.registration);

    _TinCanXHR_request(lrs, url, "PUT", setVal, cbfn);
  }
}

function TinCanISODateString(d)
{
  function pad(val, n)
  {
    if (val == null)
      val = 0;
    if (n == null)
      n = 2;

    var padder = Math.pow(10, n - 1);
    var tempVal = val.toString();
    while (val < padder && padder > 1)
    {
      tempVal = '0' + tempVal;
      padder = padder / 10;
    }
    return tempVal;
  }
  return d.getUTCFullYear() + '-' + pad(d.getUTCMonth() + 1) + '-' + pad(d.getUTCDate()) + 'T' + pad(d.getUTCHours())
    + ':' + pad(d.getUTCMinutes()) + ':' + pad(d.getUTCSeconds()) + '.' + pad(d.getUTCMilliseconds(), 3) + 'Z';
}

function _TinCanPrepareStatement(driver, stmt)
{
  if (stmt.actor === undefined)
    stmt.actor = driver.actor;
  if (driver.grouping || driver.registration || driver.activityPlatform)
  {
    if (!stmt.context)
      stmt.context = {};
  }
  // *** I don't see where this would ever be set and it should be revisited if ever used ***
  // if (driver.grouping)
  // {
  // if (!stmt.context.contextActivities)
  // stmt.context.contextActivities = {};
  // stmt.context.contextActivities.grouping = {
  // "id" : driver.grouping,
  // "definition" : {
  // "name" : {
  // "en-US" : driver.activityName
  // },
  // "description" : {
  // "en-US" : driver.activityDesc
  // }
  // },
  // "type" : "Activity"
  // };
  // stmt.context.contextActivities.parent = {
  // "id" : driver.grouping,
  // "definition" : {
  // "name" : {
  // "en-US" : driver.activityName
  // },
  // "description" : {
  // "en-US" : driver.activityDesc
  // }
  // },
  // "type" : "Activity"
  // };
  // }
  if (driver.registration)
    stmt.context.registration = driver.registration;
  if (driver.activity_platform)
    stmt.context.platform = driver.activityPlatform;
}

function _TinCanXHR_request(lrs, url, method, data, cbfn, ignore404, extraHeaders)
{
  var xhr;
  var finished = false;
  var ieXDomain = (lrs._requestMode === "ie");
  var ieModeRequest;
  var result;
  var extended;
  var until;
  var fullUrl = lrs.endpoint + url;

  trivLogMsg("_TinCanXHR_request: " + url, DEBUG_LMS);
  if (lrs.extended !== undefined)
  {
    extended = [];
    for ( var prop in lrs.extended)
    {
      if (lrs.extended[prop] != null )
        extended.push(prop + "=" + encodeURIComponent(lrs.extended[prop]));
    }
    if (extended.length > 0)
      fullUrl += (fullUrl.indexOf("?") > -1 ? "&" : "?") + extended.join("&");
  }
  var headers = {};
  headers["Content-Type"] = "application/json";
  headers["Authorization"] = lrs.auth;
  headers["X-Experience-API-Version"] = TinCanVersion;
  if (extraHeaders !== null)
  {
    for ( var headerName in extraHeaders) {
      headers[headerName] = extraHeaders[headerName];
    }
  }
  if (lrs._requestMode === "native")
  {
    trivLogMsg("_TinCanXHR_request using XMLHttpRequest", DEBUG_LMS);
    xhr = new XMLHttpRequest();
    xhr.open(method, fullUrl, cbfn !== undefined || method === 'PUT'); // force async PUT
    for ( var headerName in headers)
      xhr.setRequestHeader(headerName, headers[headerName]);
  }
  else if (ieXDomain)
  {
    trivLogMsg("_TinCanXHR_request using XDomainRequest", DEBUG_LMS);
    ieModeRequest = _TinCanGetIEModeRequest(method, fullUrl, headers, data);
    xhr = new XDomainRequest();
    xhr.open(ieModeRequest.method, ieModeRequest.url, method === 'PUT'); // force async PUT
  }

  function _TinCanXHR_requestComplete()
  {
    trivLogMsg("_TinCanXHR_requestComplete: " + finished + ", xhr.status: " + xhr.status, DEBUG_LMS);
    var notFoundOk;
    if (!finished)
    {
      finished = true;
      notFoundOk = (ignore404 && xhr.status === 404);
      if (xhr.status === undefined || (xhr.status >= 200 && xhr.status < 400) || notFoundOk)
      {
        if (cbfn)
          cbfn(xhr);
        else
        {
          result = xhr;
          return xhr;
        }
      }
      else
      {
        if (xhr.status > 0)
          alert("[warning] There was a problem communicating with the Learning Record Store. (" + xhr.status
            + " | " + xhr.responseText + ")");
        return xhr;
      }
    }
    else
      return result;
  }
  xhr.onreadystatechange = function()
  {
    if (xhr.readyState === 4)
    {
      _TinCanXHR_requestComplete();
    }
  };
  xhr.onload = _TinCanXHR_requestComplete;
  xhr.onerror = _TinCanXHR_requestComplete;
  xhr.send(ieXDomain ? ieModeRequest.data : data);
  if (!cbfn)
  {
    if (ieXDomain)
    {
      until = 1000 + Date.now();
      trivLogMsg("_TinCanXHR_request: until: " + until + ", finished: " + finished, DEBUG_LMS);
      while (Date.now() < until && !finished)
        __delay();
    }
    return _TinCanXHR_requestComplete();
  }
}

function _TinCanGetIEModeRequest(method, url, headers, data)
{
  var newUrl = url;
  var formData = [];
  var qsIndex = newUrl.indexOf('?');
  var result;

  trivLogMsg("_TinCanGetIEModeRequest", DEBUG_LMS);
  if (qsIndex > 0)
  {
    formData.push(newUrl.substr(qsIndex + 1));
    newUrl = newUrl.substr(0, qsIndex);
  }
  newUrl = newUrl + '?method=' + method;
  if (headers !== null)
  {
    for ( var headerName in headers)
      formData.push(headerName + "=" + encodeURIComponent(headers[headerName]));
  }
  if (data !== null)
    formData.push('content=' + encodeURIComponent(data));

  result = {
    method : "POST",
    url : newUrl,
    headers : {},
    data : formData.join("&")
  };
  return result;
}

function __delay()
{
  var xhr = new XMLHttpRequest();
  var url = window.location + '?forcenocache=' + GenerateRandomID();

  xhr.open('GET', url, false);
  xhr.send(null);
}

function parseSearch()
{
  var oGetVars = {};
  function buildValue(sValue)
  {
    if (/^\s*$/.test(sValue))
      return null;

    if (/^(true|false)$/i.test(sValue))
      return sValue.toLowerCase() === "true";

    if (isFinite(sValue))
      return parseFloat(sValue);

    return sValue;
  }
  var search = '';
  try { search = window.location.search; } catch (e) { }
  if (search.length <= 1)
    search = window.parent.location.search;

  if (search.length > 1)
  {
    var aItKey;
    var nKeyId;
    var aCouples = search.substr(1).split("&");

    for (nKeyId = 0; nKeyId < aCouples.length; nKeyId++)
    {
      aItKey = aCouples[nKeyId].split("=");
      oGetVars[unescape(aItKey[0])] = aItKey.length > 1 ? buildValue(unescape(aItKey[1])) : null;
    }
  }
  trivLogMsg("parseSearch: " + JSON.stringify(oGetVars, null, 4), DEBUG_LMS);
  return oGetVars;
}

function GenerateRandomID()
{
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c)
  {
    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

if (!window.JSON)
{
  window.JSON = {
    parse : function(sJSON)
    {
      return eval("(" + sJSON + ")");
    },
    stringify : function(vContent)
    {
      if (vContent instanceof Object)
      {
        var sOutput = "";
        if (vContent.constructor === Array)
        {
          for (var nId = 0; nId < vContent.length; sOutput += this.stringify(vContent[nId]) + ",", nId++)
          {
          }
          return "[" + sOutput.substr(0, sOutput.length - 1) + "]";
        }
        if (vContent.toString !== Object.prototype.toString)
          return "\"" + vContent.toString().replace(/"/g, "\\$&") + "\"";

        for ( var sProp in vContent)
          sOutput += "\"" + sProp.replace(/"/g, "\\$&") + "\":" + this.stringify(vContent[sProp]) + ",";

        return "{" + sOutput.substr(0, sOutput.length - 1) + "}";
      }
      return typeof vContent === "string" ? "\"" + vContent.replace(/"/g, "\\$&") + "\"" : String(vContent);
    }
  };
}

function LMSInitialize_( activityName, activityDesc )
{
  trivLogMsg("In LMSInitialize", DEBUG_LMS);
  tcapi_cache = {
    totalPrevDuration : null,
    statementQueue : []
  };
  tc_driver = TinCanConfigObject( activityName, activityDesc );
  TinCanGetInfo(tc_driver, 'cumulative_time', function(xhr)
  {
    if (xhr.status < 400)
    {
      if (xhr.responseText.match(/^\d+$/))
        tcapi_cache.totalPrevDuration = Number(xhr.responseText);
      else
        tcapi_cache.totalPrevDuration = 0;
      trivLogMsg('tcapi_cache.totalPrevDuration=' + tcapi_cache.totalPrevDuration);
    }
  });
  TinCanStatus = 'attempted';
  TinCanProcessing = true;
  var stmt = {
    "verb" : {
      "id" : "http://adlnet.gov/expapi/verbs/attempted",
      "display" : {
        "en-US" : "attempted"
      }
    },
    "object" : tc_driver.courseActivityObject,
    "timestamp" : TinCanISODateString(new Date()),
    "version" : TinCanVersion
  };
  TinCanSendStatement(tc_driver, stmt, function(xhr)
  {
  });
  InitializeExecuted(true, "");
  return true;
}

function LMSInitialize( activityName, activityDesc )
{
  LMSInitialize_( activityName, activityDesc );
}

function LMSFinish_()
{
  trivLogMsg("In LMSFinish", DEBUG_LMS);
  tcFinishCalled = true;
  TinCanFinish("SUSPEND");
  if (window.myTop) window.myTop.close();
  return true;
}

function LMSFinish()
{
  return LMSFinish_();
}

function getStudentName(actor)
{
  if (actor === undefined)
    return "";
  if (actor.name !== undefined)
    return actor.name;
  if (actor.lastName != undefined && actor.firstName != undefined)
    return actor.firstName + " " + actor.lastName;
  if (actor.familyName != undefined && actor.givenName != undefined)
    return actor.givenName + " " + actor.familyName;
  if (actor.mbox !== undefined)
    return actor.mbox.replace('mailto:', '');
  if (actor.account !== undefined)
    return actor.account.accountName;

  return truncateString(JSON.stringify(actor), 20);
}

function LMSRecordInteraction(strID, strResponse, blnCorrect, strCorrectResponse, strDescription, intWeighting,
                              intLatency, strLearningObjectiveID, dtmTime, interactType, arChoices, arAnswers)
{

  var validLearningObjectiveID = createValidIdentifier(window.strTestName), // set in TMPr.processTest
    stmt,
    bNeutral = false,
    actObj = {
      "id" : tc_driver.activityId + "/" + createValidIdentifier(strID),
      "definition" : {
        "name" : {
          'en-US' : strDescription
        },
        "description" : {
          'en-US' : strDescription
        },
        "type" : "http://adlnet.gov/expapi/activities/cmi.interaction",
        "interactionType" : interactType,
        "correctResponsesPattern" : [ strCorrectResponse ]
      }
    },
    definition = actObj.definition;
  ResetErrorStatus();
  switch (interactType) {
    case "matching":
      definition.source = [];
      for (var i = 0; i < arChoices.length; i++)
      {
        definition.source.push({
          "id": arChoices[i],
          "description": {
            "en-US": arChoices[i]
          }
        });
      }
      definition.target = [];
      for (var i = 0; i < arAnswers.length; i++)
      {
        var answer = arAnswers[i];

        for( var hyphenLoc = answer.indexOf('-') ; hyphenLoc >= 0 ; hyphenLoc = answer.indexOf('-') )
        {
          var target;
          var orLoc = answer.indexOf('|');

          if(orLoc >= 0)
            target = answer.substring(hyphenLoc+1, orLoc);
          else
            target = answer.substring(hyphenLoc+1);

          if( !ArrContainsTarget(definition.target, target) )
          {
            definition.target.push({
              "id": target,
              "description": {
                "en-US": target
              }
            });
          }

          if(orLoc >= 0)
            answer = answer.substring(orLoc+1);
          else
            break;
        }
      }
      break;
    case "performance":
      definition.steps = [];
      break;
    case "sequencing":
      definition.choices = [];
      for (var i = 0; i < arChoices.length; i++)
      {
        definition.choices.push({
          "id": arChoices[i],
          "description": {
            "en-US": arChoices[i]
          }
        });
      }
      break;
    case "likert":
      definition.scale = [];
      for (var i = 0; i < arChoices.length; i++)
      {
        definition.scale.push({
          "id": arChoices[i],
          "description": {
            "en-US": arChoices[i]
          }
        });
      }
      bNeutral = true;
      break;
    case "choice":
      definition.choices = [];
      for (var i = 0; i < arChoices.length; i++)
      {
        definition.choices.push({
          "id": arChoices[i],
          "description": {
            "en-US": arChoices[i]
          }
        });
      }

      break;
    case "true-false":
    case "fill-in":
    case "numeric":
    case "other":
      break;
    default:
      trivLogMsg("LMSRecordInteraction received an invalid interactType of " + interactType, DEBUG_LMS);
      return false;
  }

  if (actObj.id !== null)
  {
    if (dtmTime == null)
      dtmTime = TinCanISODateString(new Date());
    stmt = {
      verb : {
        "id" : "http://adlnet.gov/expapi/verbs/answered",
        "display" : {
          "en-US" : "answered"
        }
      },
      object : actObj,
      timestamp: dtmTime,
      version : TinCanVersion,
      context : {
        contextActivities : {
          parent : {
            "id" : tc_driver.activityId + '/' + validLearningObjectiveID,
            "definition" : {
              "name" : {
                "en-US" : window.strTestName
              },
              "description" : {
                "en-US" : window.strTestName
              }
            }
          },
          grouping : tc_driver.courseActivityObject
        }
      }
    };
    if (strResponse != null)
    {
      if (bNeutral)
        stmt.result = {
          response : strResponse
        };
      else
        stmt.result = {
          response : strResponse,
          success : blnCorrect
        };
    }
    tcapi_cache.statementQueue.push(stmt);
  }
  return true;
}

function ArrContainsTarget(arr, id)
{
  for(var i = 0 ; i < arr.length ; i++)
  {
    if(arr[i].id == id)
      return true;
  }

  return false;
}

function TCAPI_SetSuspended()
{
  if (TinCanProcessing)
  {
    TinCanProcessing = false;
    TinCanUpdPending = true;
  }
  return true;
}

function TCAPI_GetLastError()
{
  if (intTCAPIError === '')
    return ERR_NONE;
  else
    return intTCAPIError;
}

function InitializeExecuted(blnSuccess, strErrorMessage)
{
  trivLogMsg("In InitializeExecuted, blnSuccess=" + blnSuccess + ", strErrorMessage=" + strErrorMessage, DEBUG_LMS);
  if (!blnSuccess)
  {
    trivLogMsg("ERROR - LMS Initialize Failed", DEBUG_LMS);
    if (strErrorMessage == "")
      strErrorMessage = "An Error Has Occurred";

    DisplayError(strErrorMessage);
    return;
  }
  bTCLoaded = true;
  dTCStart = new Date();
  return;
}

function TinCanFinish(ExitType)
{
  trivLogMsg("In TinCanFinish, ExitType=" + ExitType, DEBUG_LMS);
  ResetErrorStatus();
  if (bTCLoaded && !bTCCalledFinish)
  {
    bTCCalledFinish = true;

    //
    // if the status was set it would have already been recorded.
    //
    // if(TinCanStatus==='completed'||TinCanStatus==='passed')
    // {
    // ExitType="FINISH";
    // TinCanCompStatus='true';
    // TinCanProcessing=false;
    // TinCanUpdPending=true;
    // }

    if (ExitType === "SUSPEND")
    {
      TinCanSetInfo(tc_driver, 'cumulative_time', GetPreviouslyAccumulatedTime() + GetSessionAccumulatedTime());
      TCAPI_SetSuspended();
    }
    LMSCommit();
    bTCLoaded = false;
  }
  return true;
}

function DisplayError(strMessage)
{
  trivLogMsg("In DisplayError, strMessage=" + strMessage, DEBUG_LMS);
  alert("An error has occured:\n\n" + strMessage);
}

function GetLastError()
{
  trivLogMsg("In GetLastError, intError=" + intError, DEBUG_LMS);
  if (intError != ERR_NONE)
  {
    trivLogMsg("Returning API Error", DEBUG_LMS);
    return intError;
  }
  else if (bTCLoaded && TCAPI_GetLastError() != ERR_NONE)
  {
    trivLogMsg("Returning LMS Error", DEBUG_LMS);
    return ERR_FROMLMS;
  }
  trivLogMsg("Returning No Error", DEBUG_LMS);
  return ERR_NONE;
}

function GetLastLMSErrorCode()
{
  var LMSError = TCAPI_GetLastError();

  trivLogMsg("In GetLastLMSErrorCode, intError=" + intError, DEBUG_LMS);
  if (bTCLoaded && LMSError != ERR_NONE)
  {
    trivLogMsg("Returning LMS Error: " + LMSError, DEBUG_LMS);
    return LMSError;
  }
  trivLogMsg("Returning No Error", DEBUG_LMS);
  return ERR_NONE;
}

function GetLastErrorDesc()
{
  trivLogMsg("In GetLastErrorDesc", DEBUG_LMS);
  if (intError != ERR_NONE)
  {
    trivLogMsg("Returning API Error - " + strErrorDesc, DEBUG_LMS);
    return strErrorDesc;
  }
  else if (bTCLoaded && TCAPI_GetLastError() != ERR_NONE)
  {
    trivLogMsg("returning LMS Error " + strTCAPIErrorString + "\n" + strTCAPIErrorDiagnostic, DEBUG_LMS);
    return strTCAPIErrorString + "\n" + strTCAPIErrorDiagnostic;
  }
  trivLogMsg("Returning No Error", DEBUG_LMS);
  return "";
}

function SetErrorInfo(intErrorNumToSet, strErrorDescToSet)
{
  trivLogMsg("In SetErrorInfo - Num=" + intErrorNumToSet + " Desc=" + strErrorDescToSet, DEBUG_LMS);
  intError = intErrorNumToSet;
  strErrorDesc = strErrorDescToSet;
}

function ResetErrorStatus()
{
  intTCAPIError = '';
  strTCAPIErrorString = "";
  strTCAPIErrorDiagnostic = "";
}

function LMSCommit_()
{
  trivLogMsg("In LMSCommit", DEBUG_LMS);
  ResetErrorStatus();
  if (!bTCLoaded)
    return false;
  if (!blnOverrodeTime)
  {
    dTCEnd = new Date();
    AccumulateTime();
    dTCStart = new Date();
  }

  var stmt;
  if (TinCanUpdPending && !(bTCLoaded && TinCanStatus == 'attempted')) // don't send attempted after we are loaded
  {
    stmt = {
      "verb" : {
        "id" : "http://adlnet.gov/expapi/verbs/" + TinCanStatus,
        "display" : {
          "en-US" : TinCanStatus
        }
      },
      "timestamp" : TinCanISODateString(new Date()),
      "version" : TinCanVersion,
      "object": {
        "id" : tc_driver.activityId,
        "definition" : {
          "name" : {
            "en-US" : tc_driver.activityName
          },
          "description" : {
            "en-US" : tc_driver.activityDesc
          }
        }
      },
      "result" : {}
    };

    if (TinCanCompStatus !== '' || !TinCanProcessing)
      stmt.result.duration = ConvertToTime(GetSessionAccumulatedTime());
    if (TinCanCompStatus !== '')
      stmt.result.completion = (TinCanCompStatus == 'true');
    if (TinCanSatStatus !== '')
      stmt.result.success = (TinCanSatStatus == 'true');
    if (TinCanScore !== '')
      stmt.result.score = {
        "raw" : parseInt(TinCanScore, 10),
        "min" : 0,
        "max" : 100
      };
    tcapi_cache.statementQueue.push(stmt);
    TinCanUpdPending = false;
  }
  if (tcapi_cache.statementQueue.length > 0)
  {
    TinCanSendMultiStatements(tc_driver, tcapi_cache.statementQueue);
    tcapi_cache.statementQueue = [];
  }
  return true;
}

function LMSCommit()
{
  return LMSCommit_();
}

function GetPreviouslyAccumulatedTime()
{
  trivLogMsg("In GetPreviouslyAccumulatedTime", DEBUG_LMS);
  ResetErrorStatus();
  if (!bTCLoaded)
    return 0;

  if (tcapi_cache.totalPrevDuration === null)
  {
    var result = TinCanGetInfo(tc_driver, 'cumulative_time');
    if (result === null)
    {
      result = 0;
    }
    tcapi_cache.totalPrevDuration = Number(result);
  }
  trivLogMsg("PreviouslyAccumulatedTime=" + tcapi_cache.totalPrevDuration, DEBUG_LMS);
  return tcapi_cache.totalPrevDuration;

}

function AccumulateTime()
{
  trivLogMsg(
    "In AccumulateTime dTCStart=" + dTCStart + " dTCEnd=" + dTCEnd + " intAccumulatedMS=" + intAccumulatedMS,
    DEBUG_LMS);
  if (dTCEnd != null && dTCStart != null)
  {
    intAccumulatedMS += (dTCEnd.getTime() - dTCStart.getTime());
    trivLogMsg("intAccumulatedMS=" + intAccumulatedMS, DEBUG_LMS);
  }
}

function GetSessionAccumulatedTime()
{
  trivLogMsg("In GetSessionAccumulatedTime", DEBUG_LMS);
  ResetErrorStatus();
  dTCEnd = new Date();
  AccumulateTime();
  if (dTCStart != null)
    dTCStart = new Date();

  dTCEnd = null;
  trivLogMsg("Returning " + intAccumulatedMS, DEBUG_LMS);
  return intAccumulatedMS;
}

function SetSessionTime(intMilliseconds)
{
  trivLogMsg("In SetSessionTime", DEBUG_LMS);
  ResetErrorStatus();
  if (!CheckValidInt(intMilliseconds))
  {
    SetErrorInfo(ERR_INVNUM, "Invalid intMilliseconds passed to SetSessionTime (not an integer), intMilliseconds="
      + intMilliseconds);
    return false;
  }
  intMilliseconds = parseInt(intMilliseconds, 10);
  if (intMilliseconds < 0)
  {
    SetErrorInfo(ERR_INVNUM,
      "Invalid intMilliseconds passed to SetSessionTime (must be greater than 0), intMilliseconds="
      + intMilliseconds);
    return false;
  }
  blnOverrodeTime = true;
  intTimeOverrideMS = intMilliseconds;
  return true;
}

function createValidIdentifier(str)
{
  return encodeURIComponent( stringToSlug(str) );
}

function Trim(str)
{
  return str.replace(/^\s*/, "").replace(/\s*$/, "");
}

function TCSetStatus(stat)
{
  trivLogMsg("In TCSetStatus " + stat, DEBUG_LMS);
  if (!bTCLoaded)
    return false;
  bTCStatusWasSet = true;

  TinCanStatus = stat;
  TinCanUpdPending = true;
  if (stat == 'attempted')
  {
    TinCanSatStatus = 'true';
    TinCanCompStatus = '';
    TinCanProcessing = true;
  }
  else if (stat == 'passed' || stat == 'completed')
  {
    TinCanSatStatus = 'true';
    TinCanCompStatus = 'true';
    TinCanProcessing = false;
  }
  else if (stat == 'failed')
  {
    TinCanSatStatus = 'false';
    TinCanCompStatus = 'true';
    TinCanProcessing = false;
  }
  return true;
}

function LMSGetBookmark_()
{
  trivLogMsg("In LMSGetBookmark");
  ResetErrorStatus();
  if (!bTCLoaded)
    return TinCanBookmark;

  if (TinCanBookmark == null || TinCanBookmark.length == 0)
  {
    TinCanBookmark = TinCanGetInfo(tc_driver, 'bookmark');
    if (TinCanBookmark === null)
      TinCanBookmark = "";
  }
  trivLogMsg("Bookmark=" + TinCanBookmark);
  return TinCanBookmark;
}

function LMSGetBookmark()
{
  return LMSGetBookmark_();
}

function LMSSetBookmark_(strHtml, strName)
{
  trivLogMsg("In LMSSetBookmark - strHtml=" + strHtml + ", strName=" + strName);
  ResetErrorStatus();
  if (!bTCLoaded)
    return false;

  TinCanSetInfo(tc_driver, 'bookmark', strHtml);
  TinCanSendStatement(tc_driver, {
    verb : {
      "id" : "http://adlnet.gov/expapi/verbs/experienced",
      "display" : {
        "en-US" : "experienced"
      }
    },
    timestamp : TinCanISODateString(new Date()),
    version: TinCanVersion,
    object: {
      id : tc_driver.activityId + "/" +  strHtml,
      definition : {
        name : {
          "en-US" : strName
        }
      }
    },
    context : {
      contextActivities : {
        parent : tc_driver.courseActivityObject,
        grouping : tc_driver.courseActivityObject
      }
    }
  }, function()
  {
  });
  TinCanBookmark = strHtml;
  return true;
}

function LMSSetBookmark(strHtml, strName)
{
  return LMSSetBookmark_(strHtml, strName);
}

function putSCORMInteractions_(id, obj, tim, typ, crsp, wgt, srsp, res, lat, txt, chc,answ)
{
  if (obj == 'null')
    obj = null;
  trivLogMsg("putSCORMInteractions [" + id + "][" + obj + "][" + tim + "][" + typ + "][" + crsp + "][" + wgt + "]["
    + srsp + "][" + res + "][" + lat + "][" + txt + "][" + chc + "][" + answ + "]");
  return LMSRecordInteraction(id, srsp, (res === 'correct' ? true : false), crsp, txt, wgt, lat, obj, tim, typ, chc,answ);
}

function putSCORMInteractions(id, obj, tim, typ, crsp, wgt, srsp, res, lat, txt, chc,answ)
{
  return putSCORMInteractions_(id, obj, tim, typ, crsp, wgt, srsp, res, lat, txt, chc,answ);
}

function LMSTinCanStatement_(strVerb, strObj, strScore)
{
  trivLogMsg("In LMSTinCanStatement - strVerb=" + strVerb + ", strObj=" + strObj);

  // this looks like incomplete logic, if we are going to check for an object we should also treat it differently
  // if it comes in as an object, so that probably never happens...
  var strObj2 = strObj.indexOf('{') < 0 ?  createValidIdentifier(strObj) : strObj;

  var stmt = {
    verb : {
      "id" : "http://adlnet.gov/expapi/verbs/" + strVerb,
      "display" : {
        "en-US" : strVerb
      }
    },
    timestamp : TinCanISODateString(new Date()),
    version: TinCanVersion,
    object: {
      id : tc_driver.activityId + "/" + strObj2,
      definition : {
        name : {
          "en-US" : strObj
        }
      }
    },
    context : {
      contextActivities : {
        parent : tc_driver.courseActivityObject,
        grouping : tc_driver.courseActivityObject
      }
    },
    result : {}
  };

  if (strScore !=null)
  {
    stmt.result.completion = true;
    stmt.result.success = (strVerb == 'passed' || strVerb == 'completed' ? true : false);
    stmt.result.score = {
      "raw" : parseInt(strScore, 10),
      "min" : 0,
      "max" : 100
    };
  }
  TinCanSendStatement(tc_driver, stmt, function()
  {
  });
  return true;
}

function LMSTinCanStatement(strVerb, strObj, strScore)
{
  return LMSTinCanStatement_(strVerb, strObj, strScore);
}

function LMSTinCanSetStatus_(strVerb)
{
  trivLogMsg("In LMSTinCanSetStatus - strVerb=" + strVerb);

  TCSetStatus(strVerb);
  return;
}

function LMSTinCanSetStatus(strVerb)
{
  return LMSTinCanSetStatus_(strVerb);
}

function stringToSlug(str)
{
  str = str.replace(/^\s+|\s+$/g, ''); // trim
  str = str.toLowerCase();

  // remove accents, swap ñ for n, etc
  var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
  var to = "aaaaeeeeiiiioooouuuunc------";
  for (var i = 0, l = from.length; i < l; i++)
    str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));

  str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
    .replace(/\s+/g, '-') // collapse whitespace and replace by -
    .replace(/-+/g, '-'); // collapse dashes

  return str;
}
