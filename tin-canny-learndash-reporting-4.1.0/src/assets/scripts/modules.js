/**
 * @package Uncanny TinCan API
 * @author Uncanny Owl
 * @version 1.0.0
 */

var tincannyModuleController = function() {
	var $this = this;
	this.count = {};
	this.completion = {};
	this.bound = false;
	this.hasCompletionSetting = false;
	this.markCompleteSettings = '';
	this.markCompleteLesson = 0;
	this.markCompleteCourse = 0;
	this.markCompleteLabel  = '';
	this.currentLessonLink  = '';
	this.methodMarkCompleteForTincan  = 'new';
    $this.iframe_interval = false;

	var moduleFunctions = new tincannyModuleFunctions(this);

	this.ready = function() {
		this.iFrameReady();
		this.lightboxReady();
		this.newWindowReady();
		this.setButton(false);

		moduleFunctions.checkH5PCompletiton();

		jQuery(document).on('learndash-time-finished','.learndash_mark_complete_button', function(){
			if( moduleController ){
		        if( moduleController.markCompleteSettings == 'yes' ){
	            	jQuery('.learndash_mark_complete_button').attr('disabled', 'disabled' );
	            	jQuery('.learndash_mark_complete_button').addClass('disabled');
		        }
		    }
		});
	};

	this.setButton = function(edgeReload) {

	    // We are setting display none with styles initial in tin-canny-learndash-reporting\src\uncanny-articulate-and-captivate\classes\Shortcode.php
        // Lets reinstate display none before we run checks
        jQuery( 'form[id=sfwd-mark-complete]' ).css( 'display', 'block' );
        jQuery( '.sfwd-mark-complete' ).css( 'display', 'block' );
        jQuery( '.tclr-mark-complete-button' ).css( 'display', 'block' ).addClass( 'tclr-mark-complete-button--visible' );

		// Check if no button needed
		if( this.markCompleteSettings === 'remove' || this.markCompleteSettings === 'autoadvance' ){
            jQuery( 'form[id=sfwd-mark-complete]' ).remove();
            jQuery( '.sfwd-mark-complete' ).remove();
            return;
		}

		// Condition : No Module
		var is_empty    = true;
		var is_complete = false;

		Object.keys( this.count ).forEach( function( key ) {
			if ( $this.count[ key ] !== undefined )
				is_empty = false;
		});

		// Condition : Any Completion
		Object.keys( $this.count ).forEach( function( key ) {
			if ( $this.count[ key ] > 0 && Object.keys( $this.completion[ key ] ).length > 0 )
				is_complete = true;
		});

        if ( is_empty ) {
            // Legacy
            jQuery( '#sfwd-mark-complete input' ).prop( 'disabled', false );
            jQuery( '#sfwd-mark-complete input' ).removeClass( 'disabled' );

            // 3.0
            jQuery( '.sfwd-mark-complete input' ).removeAttr( 'disabled' );
            jQuery( '.sfwd-mark-complete input' ).removeClass( 'disabled' );

            // Hide option
            if( this.markCompleteSettings == 'hide' ){
                jQuery( '.tclr-mark-complete-button' ).css( 'display', 'block' ).addClass( 'tclr-mark-complete-button--visible' );
            }
        } else if ( is_complete ) {
            // Legacy
            jQuery( '#sfwd-mark-complete input' ).prop( 'disabled', false );
            jQuery( '#sfwd-mark-complete input' ).removeClass( 'disabled' );

            // 3.0
            jQuery( '.sfwd-mark-complete input' ).prop( 'disabled', false );
            jQuery( '.sfwd-mark-complete input' ).removeClass( 'disabled' );

            // Hide option
            if( this.markCompleteSettings == 'hide' ){
                jQuery( '.tclr-mark-complete-button' ).css( 'display', 'block' ).addClass( 'tclr-mark-complete-button--visible' );
            }

            tinCanny.doAction( 'after_tincanny_insert_list' );

        } else {
            // Legacy
            jQuery( '#sfwd-mark-complete input' ).prop( 'disabled', true );
            jQuery( '#sfwd-mark-complete input' ).addClass( 'disabled' );

            // 3.0
            jQuery( '.sfwd-mark-complete input' ).prop( 'disabled', true );
            jQuery( '.sfwd-mark-complete input' ).addClass( 'disabled' );

            // Hide option
            if( this.markCompleteSettings == 'hide' ){
            	jQuery( '.tclr-mark-complete-button' ).css( 'display', 'none' ).removeClass( 'tclr-mark-complete-button--visible' );
			}
        }
	};

	this.iFrameReady = function() {
		jQuery('.AnC-iFrame').bind('ready load', function() {
			moduleFunctions.checkStatues(jQuery(this)[0].contentWindow, 'iFrame');
		});
	};

	this.lightboxReady = function() {
		jQuery('body').bind('DOMSubtreeModified', function() {
			jQuery('.nivo-lightbox-item').bind('load', function() {
				var targetURL = jQuery(this)[0].src;

				if (!jQuery(this).attr('data-tcloaded')) {
					moduleFunctions.checkStatues(jQuery(this)[0].contentWindow, 'lightbox');
					jQuery(this).attr('data-tcloaded', true);
				}

                jQuery(jQuery(this)[0].contentWindow).on( "unload", function() {
                    $this.checkCompletion(targetURL, 'Authorization=LearnDashIdstateId=');
                    /* clearInterval( $this.iframe_interval ); */
                });
                /*$this.iframe_interval = setInterval(function () {
                    $this.checkCompletion(targetURL, 'Authorization=LearnDashIdstateId=');
                }, 5000);*/
			});
		});
	};

	this.newWindowReady = function() {
		var bindNewWindow = function(targetWin) {
			// jQuery( "body" ).append( document.createTextNode( ", Ready" ) );
			if ($this.bound)
				return;

			if (
				!targetWin.document.getElementsByTagName('meta') ||
				!targetWin.document.URL ||
				targetWin.document.URL.indexOf('uncanny-snc') == -1
			) {
				// jQuery( "body" ).append( document.createTextNode( ", Not Ready" ) );
				setTimeout(function() {
					bindNewWindow(targetWin);
				}, 500);

				return;
			}

			moduleFunctions.checkStatues( targetWin, 'blank' );
		};

		jQuery('.AnC-Link').click(function(e) {
			// jQuery( "body" ).append( document.createTextNode( ", Click" ) );

			if (jQuery(this).attr('target') == '_blank' ) {
				e.preventDefault();

				var url = jQuery(this).attr('href');

				if (window.popup) {
					// jQuery( "body" ).append( document.createTextNode( ", Close" ) );
					window.popup.close();
				}

				window.popup = open(url, 'AnC');
				// jQuery( "body" ).append( document.createTextNode( ", Open" ) );

                jQuery(window.popup.document).ready(function() {
                    // jQuery( "body" ).append( document.createTextNode( ", Doc Ready" ) );
                    $this.bound = false;
                    // Removed immediate call and wait for a redirect completion.
					// bindNewWindow(window.popup);
                    setTimeout(function() {
                        bindNewWindow(window.popup);
                    }, 5000);
                });

                /* This will not work in some browsers so removed.
                window.popup.onbeforeunload = function(e) {
                    // check condition
                    setTimeout(function() {
                        bindNewWindow(window.popup);
                    }, 5000);
                    return null;
                };*/

                window.onbeforeunload = function(e){
                    if (window.popup) {
                        window.popup.close();
                    }
                };

				if (
					window.navigator.userAgent.indexOf("Edge") >= 0 ||
					window.navigator.userAgent.indexOf("MSIE") >= 0 ||
					window.navigator.userAgent.indexOf("Trident") >= 0
				) {
					// jQuery( "body" ).append( document.createTextNode( ", Hello" ) );

					setTimeout(function() {
						window.popup.onbeforeunload = function() {
							// jQuery( "body" ).append( document.createTextNode( ", Bye" ) );
							location.reload();
						};
					}, 1500);
				}
			}
		});
	};

    this.replaceRequest = function( targetWin, targetURL, contentType ) {

    };

	this.replaceRequest = function( targetWin, targetURL, contentType ) {

		if( this.methodMarkCompleteForTincan == 'old' ) {
            targetWin.XMLHttpRequest.prototype.oldSend = targetWin.XMLHttpRequest.prototype.send;

            var newSend = function( statement ) {
                this.oldSend(statement);

                try {
                    $this.checkCompletion(targetURL, statement);
                } catch(err) {}
            };

            targetWin.XMLHttpRequest.prototype.send = newSend;
		} else {

            targetWin.XMLHttpRequest.prototype.oldSend = targetWin.XMLHttpRequest.prototype.send;

            var newSend = function (statement) {
                this.oldSend(statement);

                try {

                    //$this.checkCompletion(targetURL, statement);
                    var oldOnReadyStateChange = this.onreadystatechange;
                    var self = this;

                    function onReadyStateChange() {

                        // however run actual callback first.
                        if (oldOnReadyStateChange) {
                            oldOnReadyStateChange();
                        }
                        // Now check if its a completed call
                        if (self.readyState == 4) {
                            // Check if this is a statement call
                            var contentType = moduleFunctions.getContentType(targetURL);
                            var stop = true;
                            var regEx = /\/uncanny-snc\/([0-9]+)\//;
                            var idResult = regEx.exec(targetURL);
                            var id = parseInt(idResult[1]);
                            // if response is not what we expected on completion
                            // for this I am changing in recording statement function

                            if (!self.response) {
                                return false;
                            }
                            if (self.response.toString().indexOf('completion_matched') == -1 && self.response.toString().indexOf('189c3d30-COMP-491a-85ab-1f00c84e651f') == -1) {
                                return false;
                            }

                            if (statement.indexOf('Authorization=LearnDashId') >= -1 && statement.indexOf('stateId=') >= -1) {
                                stop = false;
                            } else {
                                var json = JSON.parse(statement);

                                if (typeof json === 'object')
                                    stop = false;
                            }

                            if ($this.completion[contentType][id])
                                stop = true;

                            if (stop)
                                return;

                            if ($this.markCompleteSettings === 'autoadvance') {
                                response = JSON.parse(self.response);
                                if (typeof response.redirect_to !== 'undefined')
                                    window.location.href = response.redirect_to;
                                else
                                    window.location.reload();
                            }
                            $this.markComplete(contentType, targetURL);
                        }
                    }

                    if (!this.noIntercept) {
                        if (this.addEventListener) {
                            oldOnReadyStateChange = null;
                            this.addEventListener("readystatechange", onReadyStateChange, false);
                        } else {
                            oldOnReadyStateChange = this.onreadystatechange;
                            this.onreadystatechange = onReadyStateChange;
                        }
                    }

                } catch (err) {
                }
            };

            targetWin.XMLHttpRequest.prototype.send = newSend;
        }
	};

	this.checkCompletion = function (targetURL, statement) {
		var contentType = moduleFunctions.getContentType(targetURL);

		var stop = true;
		var regEx = /\/uncanny-snc\/([0-9]+)\//;
		var idResult = regEx.exec(targetURL);
		var id = parseInt(idResult[1]);


		if (statement.indexOf('Authorization=LearnDashId') >= -1 && statement.indexOf('stateId=') >= -1) {
			stop = false;
		} else {
			var json = JSON.parse(statement);

			if (typeof json === 'object')
				stop = false;
		}

		if ($this.completion[contentType][id])
			stop = true;

		if (stop)
			return;

		var data = {
			'action': 'Check ' + contentType + ' Completion',
			'URL': targetURL,
            'course_id': this.markCompleteCourse,
            'lesson_id': this.markCompleteLesson,
            'setting_option': this.markCompleteSettings,
            'lesson_link': this.currentLessonLink,
            'contentType': contentType
		};

		// Check Completion
		jQuery.post( wp_ajax_url, data, function( response ) {
			if ( response ) {
                if ( $this.markCompleteSettings === 'autoadvance') {
                    response = JSON.parse(response);
                	window.location.href = response.redirect_to;
				}
				$this.markComplete( contentType, targetURL );
			}
		});
	};

	this.markComplete = function( contentType, targetURL ) {
		var patternModuleId = /uncanny-snc\/([0-9]+)\//i;
		var resultModuleId = patternModuleId.exec(targetURL);

		if ( resultModuleId[1] ) {
			this.completion[ contentType ][ resultModuleId[1] ] = true;
			this.setButton(true);
            $this.markCompleted( contentType );
		}
	};

    this.markCompleted = function (contentType) {
        if ( this.markCompleteSettings === 'remove' || this.markCompleteSettings === 'autoadvance') {
            var data = {
                'action': 'uncanny-snc-mark-completed',
                'course_id': this.markCompleteCourse,
                'lesson_id': this.markCompleteLesson,
                'setting_option': this.markCompleteSettings,
				'lesson_link': this.currentLessonLink,
                'contentType': contentType
            };

            // Check Completion
            jQuery.post(wp_ajax_url, data, function (response) {
                if (response) {
                	if( typeof( response.redirect_to ) !== 'undefined' ) {
                		window.location.href = response.redirect_to;
                	}
                }
            }, 'json' );
        }
    };
};

var tincannyModuleFunctions = function(moduleController) {
	var $this = this;
	var captivateFunctions = new tincannyCaptivateFunctions();
	var storylineFunctions = new tincannyStorylineFunctions(moduleController);

	this.checkStatues = function( targetWin, openMethod ) {
		var targetURL = targetWin.document.URL;
		var contentsType = this.getContentType(targetURL);

		// Captivate
		if ( contentsType === 'Captivate' ) {
			this.processCaptivate( targetWin, targetURL, openMethod );
			return;

		// iSpring
		} else if ( contentsType === 'iSpring' ) {
			moduleController.replaceRequest( targetWin, targetURL, 'iSpring' );
			return;

		// Articulate Rise
		} else if ( contentsType === 'ArticulateRise' ) {
			moduleController.replaceRequest( targetWin, targetURL, 'ArticulateRise' );
			return;
            /* add Presenter360 tin can format */
        } else if ( contentsType === 'Presenter360' ) {
            moduleController.replaceRequest( targetWin, targetURL, 'Presenter360' );
            return;
            /* END Presenter360 */
            /* add Lectora tin can format */
		} else if ( contentsType === 'Lectora' ) {
            moduleController.replaceRequest( targetWin, targetURL, 'Lectora' );
            return;
            /* END Lectora */
		} else if ( contentsType === 'Scorm' ) {
			moduleController.replaceRequest( targetWin, targetURL, 'Scorm' );
			return;
			/* END Scorm */
		} else if ( contentsType === 'Tincan' ) {
			moduleController.replaceRequest( targetWin, targetURL, 'Tincan' );
			return;
			/* END Scorm */
		}

		// Storyline : Replace Function
		if ( targetWin.OnSendComplete ) {
			targetWin.OnSendComplete = function( commObj ) {
				storylineFunctions.OnSendComplete(targetWin, commObj);
			};

		} else {
			moduleController.replaceRequest(targetWin, targetURL, 'Storyline');
		}
	};

	this.getContentType = function(targetURL) {
		if ( targetURL.indexOf('Captivate') >= 0 ) {
			return 'Captivate';
		} else if ( targetURL.indexOf('/res/') >= 0 ) {
			return 'iSpring';
		} else if ( targetURL.indexOf('ArticulateRise') >= 0 ) {
			return 'ArticulateRise';
		} else if ( targetURL.indexOf('ArticulateRise2017') >= 0 || targetURL.indexOf('AR2017') >= 0 ) {
			return 'ArticulateRise2017';
            /* add Presenter360 tin can format */
        } else if ( targetURL.indexOf('Presenter360') >= 0 ) {
            return 'Presenter360';
            /* END Presenter360 */
			/* add Lectora tin can format */
    	} else if ( targetURL.indexOf('Lectora') >= 0 ) {
        	return 'Lectora';
        	/* END Lectora */
		} else if ( targetURL.indexOf('Scorm') >= 0 ) {
			return 'Scorm';
			/* END Scorm */
		} else if ( targetURL.indexOf('Tincan') >= 0 ) {
			return 'Tincan';
			/* END Scorm */
		}

		return 'Storyline';
	};

	this.processCaptivate = function( targetWin, targetURL, openMethod ) {
		var frame;

		if ( targetURL.indexOf('multiscreen.html') >= 0 ) {
			frame = jQuery(targetWin.document)[0].getElementsByTagName('frame')[0];

			if ( frame.contentWindow.DoCPExit ) {
				moduleController.replaceRequest( frame.contentWindow, targetURL, 'Captivate' );

				frame.contentWindow.DoCPExit = function() {
					captivateFunctions.DoCPExit( window, openMethod );
				};
			}

			jQuery(frame).on( 'load ready', function() {
				moduleController.replaceRequest( jQuery(this)[0].contentWindow, targetURL, 'Captivate' );

				jQuery(this)[0].contentWindow.DoCPExit = function() {
					captivateFunctions.DoCPExit( window, openMethod );
				};
			});

		// Captivate Normal : index_TINCAN.html
		} else if ( targetURL.indexOf('index_TINCAN.html') >= 0 ) {
			moduleController.replaceRequest( targetWin, targetURL, 'Captivate' );

			targetWin.initializeCP = function() {
				captivateFunctions.initializeCP( window, targetWin );
			};

		// Captivate From SCORM
		} else {
			frame = jQuery( targetWin );

			jQuery(frame).ready( function() {
				moduleController.replaceRequest( targetWin, targetURL, 'Captivate' );

				targetWin.DoCPExit = function() {
					captivateFunctions.DoCPExit( window, openMethod );
				};
			});
		}
	};

	this.checkH5PCompletiton = function() {
		if (!moduleController || !moduleController.count || moduleController.count.H5P <= 0)
			return;

		// Detect Ajax Completion
		jQuery(document).ajaxComplete(function( event, xhr, settings ) {
			// Detect H5P xAPI
			if (settings.url === WP_H5P_XAPI_STATEMENT_URL) {
				// Get Verb
				var verb = settings.data.match(/http%3A%2F%2Fadlnet.gov%2Fexpapi%2Fverbs%2F([a-z]+)/);

				eval(unescape(settings.data));
				var id = statement.object.definition.extensions['http://h5p.org/x-api/h5p-local-content-id'];

				if (!moduleController.hasCompletionSetting) {
					moduleController.completion.H5P[ id ] = true;
					moduleController.setButton(true);
                    moduleController.markCompleted( 'H5P' );
					return;
				}

				if ( xhr.responseJSON.message == 'true' ) {
					moduleController.completion.H5P[ id ] = true;
					moduleController.setButton(true);
                    moduleController.markCompleted( 'H5P' );
				}
			}
		});
	};
};

var tincannyCaptivateFunctions = function() {
	var $this = this;

	this.initializeCP = function( win, targetWin ) {
		if( targetWin.initialized )
			return;

		targetWin.initCalled = true ;

		if( targetWin.cp && targetWin.cp.pg && targetWin.deviceReady === false)
			return;

		targetWin.cpInit = function() {
			targetWin.document.body.innerHTML = " <div class='cpMainContainer' id='cpDocument' style='left: 0px; top:0px;' >	<div id='main_container' style='top:0px;position:absolute;width:100%;height:100%;'>	<div id='projectBorder' style='top:0px;left:0px;width:100%;height:100%;position:absolute;display:block'></div>	<div class='shadow' id='project_container' style='left: 0px; top:0px;width:100%;height:100%;position:absolute;overflow:hidden;' >	<div id='project' class='cp-movie' style='width:100% ;height:100%;overflow:hidden;'>		<div id='project_main' class='cp-timeline cp-main'>			<div id='div_Slide' onclick='cp.handleClick(event)' style='top:0px; width:100% ;height:100% ;position:absolute;-webkit-tap-highlight-color: rgba(0,0,0,0);'></div>			<canvas id='slide_transition_canvas'></canvas>		</div>		<div id='autoplayDiv' style='display:block;text-align:center;position:absolute;left:0px;top:0px;'>			<img id='autoplayImage' src='' style='position:absolute;display:block;vertical-align:middle;'/>			<div id='playImage' tabindex='9999' role='button' aria-label='play' onkeydown='cp.CPPlayButtonHandle(event)' onClick='cp.movie.play()' style='position:absolute;display:block;vertical-align:middle;'></div>		</div>	</div>	<div id='toc' style='left:0px;position:absolute;-webkit-tap-highlight-color: rgba(0,0,0,0);'>	</div>	<div id='playbar' style='bottom:0px; position:fixed'>	</div>	<div id='cc' style='left:0px; position:fixed;visibility:hidden;pointer-events:none;' onclick='cp.handleCCClick(event)'>		<div id='ccText' style='left:0px;float:left;position:absolute;width:100%;height:100%;'>		<p style='margin-left:8px;margin-right:8px;margin-top:2px;'>		</p>		</div>		<div id='ccClose' style='background-image:url(./assets/htmlimages/ccClose.png);right:10px; position:absolute;cursor:pointer;width:13px;height:11px;' onclick='cp.showHideCC()'>		</div>	</div>	<div id='gestureIcon' class='gestureIcon'>	</div>	<div id='gestureHint' class='gestureHintDiv'>		<div id='gImage' class='gesturesHint'></div>	</div>	<div id='pwdv' style='display:block;text-align:center;position:absolute;width:100%;height:100%;left:0px;top:0px'></div>	<div id='exdv' style='display:block;text-align:center;position:absolute;width:100%;height:100%;left:0px;top:0px'></div>	</div>	</div></div><div id='blockUserInteraction' class='blocker' style='width:100%;height:100%;'>	<table style='width:100%;height:100%;text-align:center;vertical-align:middle' id='loading' class='loadingBackground'>		<tr style='width:100%;height:100%;text-align:center;vertical-align:middle'>			<td style='width:100%;height:100%;text-align:center;vertical-align:middle'>				<image id='preloaderImage'></image>				<div id='loadingString' class='loadingString'>Loading...</div>			</td>		</tr>	</table></div> <div id='initialLoading'></div>";

			targetWin.cp.DoCPInit();
			targetWin.lCpExit = targetWin.DoCPExit;

			targetWinDoCPExit = function() {
				if ( openMethod == 'iFrame' )
					return;

				if ( openMethod == 'lightbox' ) {
					$this.closeOverlay( win );
					return;
				}

				if( targetWin.cp.UnloadActivties)
					targetWin.cp.UnloadActivties();

				targetWin.lCpExit();
			};
		};

		targetWin.cpInit();
		targetWin.initialized = true;
	};

	this.DoCPExit = function( win, openMethod ) {
		if ( openMethod == 'iFrame' )
			return;

		var win_;

		if( win != win.parent && win.parent && win.parent.DoCPExit !== undefined ) {
			win.parent.DoCPExit();

		} else {
			if( win.top == self ) {
				win_ = win.open("","_self");
				$this.closeWindow( win_, openMethod );

			} else {
				win_ = win.top.open("","_self");
				$this.closeWindow( win_.top, openMethod );
			}
		}
	};

	this.closeWindow = function( win, openMethod ) {
		if ( openMethod == 'lightbox' ) {
			$this.closeOverlay( win );
			return;
		}

		if ( window.popup )
			window.popup.close();
		else
			win.close();
	};

	this.closeOverlay = function( win ) {
		var elements = win.document.getElementsByClassName("nivo-lightbox-overlay");
		while(elements.length > 0){
			elements[0].parentNode.removeChild(elements[0]);
		}
	};
};

var tincannyStorylineFunctions = function(moduleController) {
	this.OnSendComplete = function(targetWin, commObj) {
		if (commObj.MessageType == targetWin.TYPE_RESUME_RESTORE) {
			targetWin.GetPlayer().SetTinCanResume(commObj.responseText);
		}

		targetWin.g_bWaitingTinCanResponse = false;
		targetWin.g_oCurrentRequest = null;

		if ( targetWin.g_arrTinCanMsgQueue.length > 0 && !targetWin.g_bStopPosting ) {
			var test = targetWin.SendRequest(targetWin.g_arrTinCanMsgQueue.shift());
		}

		moduleController.checkCompletion(jQuery(targetWin.document).context.URL, commObj.responseText);
	};
};

var moduleController = new tincannyModuleController();

/*
jQuery(document).ready(function() {
	// jQuery( "body" ).append( document.createTextNode( "Global Ready" ) );

});
*/
