/*******************************************************************************
**
** Filename: tincanwrapper.js
**
** File Description: This file contains the wrapper functions that allows the content
**                   to access the Tin Can functions in the titlemgr frameset.
**
** References: Tin Can API
**
*******************************************************************************/

var finishCalled = false;
var autoCommit = false;

function MySetValue( lmsVar, lmsVal ) {
 var titleMgr = getTitleMgrHandle();
 if( titleMgr ) titleMgr.setVariable(lmsVar,lmsVal,0);
  getDisplayWindow().LMSSetValue( lmsVar, lmsVal );
}

function loadPage( activityName, activityDesc ) {
 var startDate = readVariable( 'TrivantisSCORMTimer', 0 );
 //LD-5418
 //saveVariable( 'TrivantisEPS', 'F' ); 
 if( startDate == 0 ) {
	getDisplayWindow().LMSInitialize( activityName, activityDesc );
	var status = new String( getDisplayWindow().LMSGetValue( "cmi.core.lesson_status" ) );
	status = status.toLowerCase();
	if (status == "not attempted")
		MySetValue( "cmi.core.lesson_status", "attempted" );

	startTimer();
	return true;
 }
 else return false;
}

function startTimer() {
 var startDate = new Date().getTime();
 saveVariable('TrivantisSCORMTimer',startDate);
}

function doBack() {
 MySetValue( "cmi.core.exit", "suspend" );
 saveVariable( 'TrivantisEPS', 'T' );
 finishCalled = true;
 getDisplayWindow().LMSFinish();
 saveVariable( 'TrivantisSCORMTimer', 0 );
}

function doContinue( status ) {
 MySetValue( "cmi.core.exit", "" );
 var mode = new String( getDisplayWindow().LMSGetValue( "cmi.core.lesson_mode" ) );
 mode = mode.toLowerCase();
 if ( mode != "review"  &&  mode != "browse" ) MySetValue( "cmi.core.lesson_status", status );
 saveVariable( 'TrivantisEPS', 'T' );
 finishCalled = true;
 getDisplayWindow().LMSFinish();
 saveVariable( 'TrivantisSCORMTimer', 0 );
}

function doQuit(bForce){
 saveVariable( 'TrivantisEPS', 'T' );
 finishCalled = true;
 getDisplayWindow().LMSSetValue('',0);
 getDisplayWindow().LMSFinish();
 saveVariable( 'TrivantisSCORMTimer', 0 );
 if( bForce && getDisplayWindow().myTop ) getDisplayWindow().myTop.close();
}

function unloadPage(bForce, titleName)
{
	var exitPageStatus = readVariable('TrivantisEPS', 'F');
	if (exitPageStatus != 'T')
	{
	    if (window.name.length > 0 && window.name.indexOf('Trivantis_') == -1)
	        trivScormQuit(bForce, titleName, false);
	}
	//LD-5418
	saveVariable( 'TrivantisEPS', 'F' );
}

function trivTop() {
    var win = getDisplayWindow(), top = win;
    while(win && win.parent != null && win.parent != win)
    {
        try
        {
            // Will throw when the parent window is from a different domain
            if(win.parent.document)
                top = win.parent;
        } catch (e) {}
        win = win.parent;
    }
    return top;
}
function findxAPI(win) 
{
   // Search the window hierarchy for the TitleMgr Frame.

   if (win.length > 0)  // does the window have frames?
   {
      if (win.frames['titlemgrframe'] != null)
      {
         return win.frames['titlemgrframe'];
      }

      for (var i=0;i<win.length;i++)
      {
         var theAPI = findxAPI(win.frames[i]);
         if (theAPI != null)
         {
            return theAPI;
         }
      }
   }
   if (parent.frames['titlemgrframe'] != null)
   {
	 return parent.frames['titlemgrframe'];
   }
   return null;   
}
function getDisplayWindow(defWind){
    if(!isSinglePagePlayerAvail() || defWind)
        return window;
    else
        return trivPlayer.window;
}

function getDisplayDocument(defDoc){
    if(!isSinglePagePlayerAvail() || defDoc)
        return document;
    else
        return trivPlayer.document;
}

function setPlayerIniFrame(pgPlayer){
    if(!isSinglePagePlayerAvail())
        trivPlayer = pgPlayer;
}
function isSinglePagePlayerAvail(){
    if(!window.trivPlayer || typeof(window.trivPlayer) == 'undefined')
        return false;
    else
        return true;
}
var tcAPI = window.parent;

function LMSInitialize( activityName, activityDesc )
{
  if ( tcAPI )
    return tcAPI.LMSInitialize_( activityName, activityDesc );
  else if ( typeof getDisplayWindow().LMSInitialize_ == 'function' )
    return getDisplayWindow().LMSInitialize_( activityName, activityDesc );
}

function LMSFinish()
{
  if ( tcAPI )
    return tcAPI.LMSFinish_();
  else if ( typeof getDisplayWindow().LMSFinish_ == 'function' )
    return getDisplayWindow().LMSFinish_();
}

function LMSGetValue(name)
{
  if ( tcAPI )
    return tcAPI.LMSGetValue_(name);
  else if ( typeof getDisplayWindow().LMSGetValue_ == 'function' )
    return getDisplayWindow().LMSGetValue_(name);
}

function LMSSetValue(name, value)
{
  if ( tcAPI )
    return tcAPI.LMSSetValue_(name, value);
  else if ( typeof getDisplayWindow().LMSSetValue_ == 'function' )
    return getDisplayWindow().LMSSetValue_(name, value);
}

function LMSCommit()
{
  if ( tcAPI )
    return tcAPI.LMSCommit_();
  else if ( typeof getDisplayWindow().LMSCommit_ == 'function' )
    return getDisplayWindow().LMSCommit_();
}

function LMSGetLastError()
{
  if ( tcAPI )
    return tcAPI.LMSGetLastError();
}

function LMSGetErrorString(errorCode)
{
  if ( tcAPI )
    return tcAPI.LMSGetErrorString(errorCode);
}

function LMSGetDiagnostic(errorCode)
{
  if ( tcAPI )
    return tcAPI.LMSGetDiagnostic(errorCode);
}

function LMSGetBookmark()
{
  if ( tcAPI )
    return tcAPI.LMSGetBookmark_();
  else if ( typeof getDisplayWindow().LMSGetBookmark_ == 'function' )
    return getDisplayWindow().LMSGetBookmark_();
}

function LMSSetBookmark(strHtml,strName)
{
  if ( tcAPI )
    return tcAPI.LMSSetBookmark_(strHtml,strName);
  else if ( typeof getDisplayWindow().LMSSetBookmark_ == 'function' )
    return getDisplayWindow().LMSSetBookmark_(strHtml,strName);
}

function putSCORMInteractions(id,obj,tim,typ,crsp,wgt,srsp,res,lat,txt,chc,answ)
{
  if ( tcAPI )
    return tcAPI.putSCORMInteractions_(id,obj,tim,typ,crsp,wgt,srsp,res,lat,txt,chc,answ);
  else if ( typeof getDisplayWindow().putSCORMInteractions_ == 'function' )
    return getDisplayWindow().putSCORMInteractions_(id,obj,tim,typ,crsp,wgt,srsp,res,lat,txt,chc,answ);
}

function LMSTinCanStatement(strVerb,strObj,strScore)
{
  if ( tcAPI )
    return tcAPI.LMSTinCanStatement_(strVerb,strObj,strScore);
  else if ( typeof getDisplayWindow().LMSTinCanStatement_ == 'function' )
    return getDisplayWindow().LMSTinCanStatement_(strVerb,strObj,strScore);
}

function LMSTinCanSetStatus(strVerb)
{
  if ( tcAPI )
    return tcAPI.LMSTinCanSetStatus_(strVerb);
  else if ( typeof getDisplayWindow().LMSTinCanSetStatus_ == 'function' )
    return getDisplayWindow().LMSTinCanSetStatus_(strVerb);
}
function LMSIsInitialized()
{
  // there is no direct method for determining if the LMS API is initialized
  // for example an LMSIsInitialized function defined on the API so we'll try
  // a simple LMSGetValue and trap for the LMS Not Initialized Error

  tcAPI = window.parent;
  if (tcAPI == null)
  {
    alert("Unable to locate the LMS's API Implementation.\nLMSIsInitialized() failed.");
    return false;
  }
  else
  {
    LMSInitialize();
    var value = tcAPI.LMSGetValue("cmi.core.student_name");
    if( value.toString().length == 0 )
    {
      var errCode = parseInt( tcAPI.LMSGetLastError().toString(), 10 );
      if (errCode == _NotInitialized)
        return false;
    }
    return true;
  }
}
