/* globals SIP,user,moment, Stopwatch */

/*
authorizationUser: this.sipInfo.authorizationId,
password: this.sipInfo.password,
turnServers: iceServers,
log: {
   level: options.logLevel || 1,
   builtinEnabled: typeof options.builtinEnabled === 'undefined' ? true : options.builtinEnabled,
   connector: options.connector || null
},
domain: this.sipInfo.domain,
autostart: false,
register: true,
hackIpInContact: true,
hackWssInTransport: true,
hackViaTcp: false,
registerExpires: 60,
iceCheckingTimeout: 5000,
wsServerMaxReconnection: 5000,
wsServerReconnectionTimeout: 1,
connectionRecoveryMaxInterval: 3,
connectionRecoveryMinInterval: 2,
contactTransport: 'wss',
hackAllowUnregisteredOptionTags: true,
userAgentString: userAgentString,
displayName: this.sipInfo.displayName,
contactName: this.sipInfo.username,
allowLegacyNotifications: true,
allowOutOfDialogRefers: true,
*/


var devSip;
var sessionDescriptionHandlerFactoryOptions = {
			peerConnectionOptions: {
				iceCheckingTimeout: 5000,
				rtcConfiguration: {
					sdpSemantics: 'unified-plan',
					iceServers: ['stun.l.google.com:19302']
				}
			}
		};

$(document).ready(function() {


    if (typeof(user) === 'undefined') {
        user = JSON.parse(localStorage.getItem('SIPCreds'));
    }

    devSip = {
     
        config : {
            password        : user.Pass,
            displayName     : user.Display,
            uri             : 'sip:'+user.User+'@'+user.Realm,
            transportOptions: {
			wsServers: user.WSServer,
			traceSip: true,
			connectionTimeout: 3500
            },
            domain: user.Realm,
            sessionDescriptionHandlerFactoryOptions: sessionDescriptionHandlerFactoryOptions,
            autostart: false,
            register: true,
            hackIpInContact: true,
            hackWssInTransport: true,
            hackViaTcp: false,
            registerExpires: 60,
            iceCheckingTimeout: 5000,
            wsServerMaxReconnection: 5000,
            wsServerReconnectionTimeout: 1,
            connectionRecoveryMaxInterval: 3,
            connectionRecoveryMinInterval: 2,
            contactTransport: 'wss',
            hackAllowUnregisteredOptionTags: true,
            userAgentString: 'SIP Dev',
            contactName: user.User,
            allowLegacyNotifications: true,
            allowOutOfDialogRefers: true,
            log             : {
                level : 10,
                builtinEnabled: true
            }
        },
        ringtone     : document.getElementById('ringtone'),
        ringbacktone : document.getElementById('ringbacktone'),
        dtmfTone     : document.getElementById('dtmfTone'),

        Sessions     : [],
        callTimers   : {},
        callActiveID : null,
        callVolume   : 1,
        Stream       : null,

        /**
         * Parses a SIP uri and returns a formatted US phone number.
         *
         * @param  {string} phone number or uri to format
         * @return {string}       formatted number
         */
        formatPhone : function(phone) {

            var num;

            if (phone.indexOf('@')) {
                num =  phone.split('@')[0];
            } else {
                num = phone;
            }

            num = num.toString().replace(/[^0-9]/g, '');

            if (num.length === 10) {
                return '(' + num.substr(0, 3) + ') ' + num.substr(3, 3) + '-' + num.substr(6,4);
            } else if (num.length === 11) {
                return '(' + num.substr(1, 3) + ') ' + num.substr(4, 3) + '-' + num.substr(7,4);
            } else {
                return num;
            }
        },

        // Sound methods
        startRingTone : function() {
            try { devSip.ringtone.play(); } catch (e) { }
        },

        stopRingTone : function() {
            try { devSip.ringtone.pause(); } catch (e) { }
        },

        startRingbackTone : function() {
            try { devSip.ringbacktone.play(); } catch (e) { }
        },

        stopRingbackTone : function() {
            try { devSip.ringbacktone.pause(); } catch (e) { }
        },

        // Genereates a rendom string to ID a call
        getUniqueID : function() {
            return Math.random().toString(36).substr(2, 9);
        },

        newSession : function(newSess) {

            newSess.displayName = newSess.remoteIdentity.displayName || newSess.remoteIdentity.uri.user;
            newSess.ctxid       = devSip.getUniqueID();

            var status;

            if (newSess.direction === 'incoming') {
                status = "Incoming: "+ newSess.displayName;
                devSip.startRingTone();
            } else {
                status = "Trying: "+ newSess.displayName;
                devSip.startRingbackTone();
            }

            devSip.logCall(newSess, 'ringing');

            devSip.setCallSessionStatus(status);

            // EVENT CALLBACKS

            newSess.on('progress',function(e) {
                if (e.direction === 'outgoing') {
                    devSip.setCallSessionStatus('Calling...');
                }
            });

            newSess.on('connecting',function(e) {
                if (e.direction === 'outgoing') {
                    devSip.setCallSessionStatus('Connecting...');
                }
            });

            newSess.on('accepted',function(e) {
                // If there is another active call, hold it
                if (devSip.callActiveID && devSip.callActiveID !== newSess.ctxid) {
                    devSip.phoneHoldButtonPressed(devSip.callActiveID);
                }

                devSip.stopRingbackTone();
                devSip.stopRingTone();
                devSip.setCallSessionStatus('Answered');
                devSip.logCall(newSess, 'answered');
                devSip.callActiveID = newSess.ctxid;
            });

            newSess.on('hold', function(e) {
                devSip.callActiveID = null;
                devSip.logCall(newSess, 'holding');
            });

            newSess.on('unhold', function(e) {
                devSip.logCall(newSess, 'resumed');
                devSip.callActiveID = newSess.ctxid;
            });

            newSess.on('muted', function(e) {
                devSip.Sessions[newSess.ctxid].isMuted = true;
                devSip.setCallSessionStatus("Muted");
            });

            newSess.on('unmuted', function(e) {
                devSip.Sessions[newSess.ctxid].isMuted = false;
                devSip.setCallSessionStatus("Answered");
            });

            newSess.on('cancel', function(e) {
                devSip.stopRingTone();
                devSip.stopRingbackTone();
                devSip.setCallSessionStatus("Canceled");
                if (this.direction === 'outgoing') {
                    devSip.callActiveID = null;
                    newSess             = null;
                    devSip.logCall(this, 'ended');
                }
            });

            newSess.on('bye', function(e) {
                devSip.stopRingTone();
                devSip.stopRingbackTone();
                devSip.setCallSessionStatus("");
                devSip.logCall(newSess, 'ended');
                devSip.callActiveID = null;
                newSess             = null;
            });

            newSess.on('failed',function(e) {
                devSip.stopRingTone();
                devSip.stopRingbackTone();
                devSip.setCallSessionStatus('Terminated');
            });

            newSess.on('rejected',function(e) {
                devSip.stopRingTone();
                devSip.stopRingbackTone();
                devSip.setCallSessionStatus('Rejected');
                devSip.callActiveID = null;
                devSip.logCall(this, 'ended');
                newSess             = null;
            });

            devSip.Sessions[newSess.ctxid] = newSess;

        },

        // getUser media request refused or device was not present
        getUserMediaFailure : function(e) {
            window.console.error('getUserMedia failed:', e);
            devSip.setError(true, 'Media Error.', 'You must allow access to your microphone.  Check the address bar.', true);
        },

        getUserMediaSuccess : function(stream) {
             devSip.Stream = stream;
        },

        /**
         * sets the ui call status field
         *
         * @param {string} status
         */
        setCallSessionStatus : function(status) {
            $('#txtCallStatus').html(status);
        },

        /**
         * sets the ui connection status field
         *
         * @param {string} status
         */
        setStatus : function(status) {
            $("#txtRegStatus").html('<i class="fa fa-signal"></i> '+status);
        },

        /**
         * logs a call to localstorage
         *
         * @param  {object} session
         * @param  {string} status Enum 'ringing', 'answered', 'ended', 'holding', 'resumed'
         */
        logCall : function(session, status) {

            var log = {
                    clid : session.displayName,
                    uri  : session.remoteIdentity.uri.toString(),
                    id   : session.ctxid,
                    time : new Date().getTime()
                },
                calllog = JSON.parse(localStorage.getItem('sipCalls'));

            if (!calllog) { calllog = {}; }

            if (!calllog.hasOwnProperty(session.ctxid)) {
                calllog[log.id] = {
                    id    : log.id,
                    clid  : log.clid,
                    uri   : log.uri,
                    start : log.time,
                    flow  : session.direction
                };
            }

            if (status === 'ended') {
                calllog[log.id].stop = log.time;
            }

            if (status === 'ended' && calllog[log.id].status === 'ringing') {
                calllog[log.id].status = 'missed';
            } else {
                calllog[log.id].status = status;
            }

            localStorage.setItem('sipCalls', JSON.stringify(calllog));
            devSip.logShow();
        },

        /**
         * adds a ui item to the call log
         *
         * @param  {object} item log item
         */
        logItem : function(item) {

            var callActive = (item.status !== 'ended' && item.status !== 'missed'),
                callLength = (item.status !== 'ended')? '<span id="'+item.id+'"></span>': moment.duration(item.stop - item.start).humanize(),
                callClass  = '',
                callIcon,
                i;

            switch (item.status) {
                case 'ringing'  :
                    callClass = 'list-group-item-success';
                    callIcon  = 'fa-bell';
                    break;

                case 'missed'   :
                    callClass = 'list-group-item-danger';
                    if (item.flow === "incoming") { callIcon = 'fa-chevron-left'; }
                    if (item.flow === "outgoing") { callIcon = 'fa-chevron-right'; }
                    break;

                case 'holding'  :
                    callClass = 'list-group-item-warning';
                    callIcon  = 'fa-pause';
                    break;

                case 'answered' :
                case 'resumed'  :
                    callClass = 'list-group-item-info';
                    callIcon  = 'fa-phone-square';
                    break;

                case 'ended'  :
                    if (item.flow === "incoming") { callIcon = 'fa-chevron-left'; }
                    if (item.flow === "outgoing") { callIcon = 'fa-chevron-right'; }
                    break;
            }


            i  = '<div class="list-group-item sip-logitem clearfix '+callClass+'" data-uri="'+item.uri+'" data-sessionid="'+item.id+'" title="Double Click to Call">';
            i += '<div class="clearfix"><div class="pull-left">';
            i += '<i class="fa fa-fw '+callIcon+' fa-fw"></i> <strong>'+devSip.formatPhone(item.uri)+'</strong><br><small>'+moment(item.start).format('DD/MM hh:mm:ss a')+'</small>';
            i += '</div>';
            i += '<div class="pull-right text-right"><em>'+item.clid+'</em><br>' + callLength+'</div></div>';

            if (callActive) {
                i += '<div class="btn-group btn-group-xs pull-right">';
                if (item.status === 'ringing' && item.flow === 'incoming') {
                    i += '<button class="btn btn-xs btn-success btnCall" title="Call"><i class="fa fa-phone"></i></button>';
                } else {
                    i += '<button class="btn btn-xs btn-primary btnHoldResume" title="Hold"><i class="fa fa-pause"></i></button>';
                    i += '<button class="btn btn-xs btn-info btnTransfer" title="Transfer"><i class="fa fa-random"></i></button>';
                    i += '<button class="btn btn-xs btn-warning btnMute" title="Mute"><i class="fa fa-fw fa-microphone"></i></button>';
                }
                i += '<button class="btn btn-xs btn-danger btnHangUp" title="Hangup"><i class="fa fa-stop"></i></button>';
                i += '</div>';
            }
            i += '</div>';

            $('#sip-logitems').append(i);


            // Start call timer on answer
            if (item.status === 'answered') {
                var tEle = document.getElementById(item.id);
                devSip.callTimers[item.id] = new Stopwatch(tEle);
                devSip.callTimers[item.id].start();
            }

            if (callActive && item.status !== 'ringing') {
                devSip.callTimers[item.id].start({startTime : item.start});
            }

            $('#sip-logitems').scrollTop(0);
        },

        /**
         * updates the call log ui
         */
        logShow : function() {

            var calllog = JSON.parse(localStorage.getItem('sipCalls')),
                x       = [];

            if (calllog !== null) {

                $('#sip-splash').addClass('hide');
                $('#sip-log').removeClass('hide');

                // empty existing logs
                $('#sip-logitems').empty();

                // JS doesn't guarantee property order so
                // create an array with the start time as
                // the key and sort by that.

                // Add start time to array
                $.each(calllog, function(k,v) {
                    x.push(v);
                });

                // sort descending
                x.sort(function(a, b) {
                    return b.start - a.start;
                });

                $.each(x, function(k, v) {
                    devSip.logItem(v);
                });

            } else {
                $('#sip-splash').removeClass('hide');
                $('#sip-log').addClass('hide');
            }
        },

        /**
         * removes log items from localstorage and updates the UI
         */
        logClear : function() {

            localStorage.removeItem('sipCalls');
            devSip.logShow();
        },

        sipCall : function(target) {

            try {
                var s = devSip.phone.invite(target, {
                    media : {
                        stream      : devSip.Stream,
                        constraints : { audio : true, video : false },
                        render      : {
                            remote : $('#audioRemote').get()[0]
                        },
                        RTCConstraints : { "optional": [{ 'DtlsSrtpKeyAgreement': 'true'} ]}
                    }
                });
                s.direction = 'outgoing';
                devSip.newSession(s);

            } catch(e) {
                throw(e);
            }
        },

        sipTransfer : function(sessionid) {

            var s      = devSip.Sessions[sessionid],
                target = window.prompt('Enter destination number', '');

            devSip.setCallSessionStatus('<i>Transfering the call...</i>');
            s.refer(target);
        },

        sipHangUp : function(sessionid) {

            var s = devSip.Sessions[sessionid];
            // s.terminate();
            if (!s) {
                return;
            } else if (s.startTime) {
                s.bye();
            } else if (s.reject) {
                s.reject();
            } else if (s.cancel) {
                s.cancel();
            }

        },

        sipSendDTMF : function(digit) {

            try { devSip.dtmfTone.play(); } catch(e) { }

            var a = devSip.callActiveID;
            if (a) {
                var s = devSip.Sessions[a];
                s.dtmf(digit);
            }
        },

        phoneCallButtonPressed : function(sessionid) {

            var s      = devSip.Sessions[sessionid],
                target = $("#numDisplay").val();

            if (!s) {

                $("#numDisplay").val("");
                devSip.sipCall(target);

            } else if (s.accept && !s.startTime) {

                s.accept({
                    media : {
                        stream      : devSip.Stream,
                        constraints : { audio : true, video : false },
                        render      : {
                            remote : { audio: $('#audioRemote').get()[0] }
                        },
                        RTCConstraints : { "optional": [{ 'DtlsSrtpKeyAgreement': 'true'} ]}
                    }
                });
            }
        },

        phoneMuteButtonPressed : function (sessionid) {

            var s = devSip.Sessions[sessionid];

            if (!s.isMuted) {
                s.mute();
            } else {
                s.unmute();
            }
        },

        phoneHoldButtonPressed : function(sessionid) {

            var s = devSip.Sessions[sessionid];

            if (s.isOnHold().local === true) {
                s.unhold();
            } else {
                s.hold();
            }
        },


        setError : function(err, title, msg, closable) {

            // Show modal if err = true
            if (err === true) {
                $("#mdlError p").html(msg);
                $("#mdlError").modal('show');

                if (closable) {
                    var b = '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $("#mdlError .modal-header").find('button').remove();
                    $("#mdlError .modal-header").prepend(b);
                    $("#mdlError .modal-title").html(title);
                    $("#mdlError").modal({ keyboard : true });
                } else {
                    $("#mdlError .modal-header").find('button').remove();
                    $("#mdlError .modal-title").html(title);
                    $("#mdlError").modal({ keyboard : false });
                }
                $('#numDisplay').prop('disabled', 'disabled');
            } else {
                $('#numDisplay').removeProp('disabled');
                $("#mdlError").modal('hide');
            }
        },

        /**
         * Tests for a capable browser, return bool, and shows an
         * error modal on fail.
         */
        hasWebRTC : function() {

            if (navigator.webkitGetUserMedia) {
                return true;
            } else if (navigator.mozGetUserMedia) {
                return true;
            } else if (navigator.getUserMedia) {
                return true;
            } else {
                devSip.setError(true, 'Unsupported Browser.', 'Your browser does not support the features required for this phone.');
                window.console.error("WebRTC support not found");
                return false;
            }
        }
    };




    // Throw an error if the browser can't hack it.
    if (!devSip.hasWebRTC()) {
        return true;
    }

    devSip.phone = new SIP.UA(devSip.config);

    devSip.phone.on('connected', function(e) {
        devSip.setStatus("Connected");
    });

    devSip.phone.on('disconnected', function(e) {
        devSip.setStatus("Disconnected");

        // disable phone
        devSip.setError(true, 'Websocket Disconnected.', 'An Error occurred connecting to the websocket.');

        // remove existing sessions
        $("#sessions > .session").each(function(i, session) {
            devSip.removeSession(session, 500);
        });
    });

    devSip.phone.on('registered', function(e) {

        var closeEditorWarning = function() {
            return 'If you close this window, you will not be able to make or receive calls from your browser.';
        };

        var closePhone = function() {
            // stop the phone on unload
            localStorage.removeItem('ctxPhone');
            devSip.phone.stop();
        };

        window.onbeforeunload = closeEditorWarning;
        window.onunload       = closePhone;

        // This key is set to prevent multiple windows.
        localStorage.setItem('ctxPhone', 'true');

        $("#mldError").modal('hide');
        devSip.setStatus("Ready");


    });

    devSip.phone.on('registrationFailed', function(e) {
        devSip.setError(true, 'Registration Error.', 'An Error occurred registering your phone. Check your settings.');
        devSip.setStatus("Error: Registration Failed");
    });

    devSip.phone.on('unregistered', function(e) {
        devSip.setError(true, 'Registration Error.', 'An Error occurred registering your phone. Check your settings.');
        devSip.setStatus("Error: Registration Failed");
    });

    devSip.phone.on('invite', function (incomingSession) {

        var s = incomingSession;

        s.direction = 'incoming';
        devSip.newSession(s);
    });

    // Auto-focus number input on backspace.
    $('#sipClient').keydown(function(event) {
        if (event.which === 8) {
            $('#numDisplay').focus();
        }
    });

    $('#numDisplay').keypress(function(e) {
        // Enter pressed? so Dial.
        if (e.which === 13) {
            devSip.phoneCallButtonPressed();
        }
    });

    $('.digit').click(function(event) {
        event.preventDefault();
        var num = $('#numDisplay').val(),
            dig = $(this).data('digit');

        $('#numDisplay').val(num+dig);

        devSip.sipSendDTMF(dig);
        return false;
    });

    $('#phoneUI .dropdown-menu').click(function(e) {
        e.preventDefault();
    });

    $('#phoneUI').delegate('.btnCall', 'click', function(event) {
        devSip.phoneCallButtonPressed();
        // to close the dropdown
        return true;
    });

    $('.sipLogClear').click(function(event) {
        event.preventDefault();
        devSip.logClear();
    });

    $('#sip-logitems').delegate('.sip-logitem .btnCall', 'click', function(event) {
        var sessionid = $(this).closest('.sip-logitem').data('sessionid');
        devSip.phoneCallButtonPressed(sessionid);
        return false;
    });

    $('#sip-logitems').delegate('.sip-logitem .btnHoldResume', 'click', function(event) {
        var sessionid = $(this).closest('.sip-logitem').data('sessionid');
        devSip.phoneHoldButtonPressed(sessionid);
        return false;
    });

    $('#sip-logitems').delegate('.sip-logitem .btnHangUp', 'click', function(event) {
        var sessionid = $(this).closest('.sip-logitem').data('sessionid');
        devSip.sipHangUp(sessionid);
        return false;
    });

    $('#sip-logitems').delegate('.sip-logitem .btnTransfer', 'click', function(event) {
        var sessionid = $(this).closest('.sip-logitem').data('sessionid');
        devSip.sipTransfer(sessionid);
        return false;
    });

    $('#sip-logitems').delegate('.sip-logitem .btnMute', 'click', function(event) {
        var sessionid = $(this).closest('.sip-logitem').data('sessionid');
        devSip.phoneMuteButtonPressed(sessionid);
        return false;
    });

    $('#sip-logitems').delegate('.sip-logitem', 'dblclick', function(event) {
        event.preventDefault();

        var uri = $(this).data('uri');
        $('#numDisplay').val(uri);
        devSip.phoneCallButtonPressed();
    });

    $('#sldVolume').on('change', function() {

        var v      = $(this).val() / 100,
            // player = $('audio').get()[0],
            btn    = $('#btnVol'),
            icon   = $('#btnVol').find('i'),
            active = devSip.callActiveID;

        // Set the object and media stream volumes
        if (devSip.Sessions[active]) {
            devSip.Sessions[active].player.volume = v;
            devSip.callVolume                     = v;
        }

        // Set the others
        $('audio').each(function() {
            $(this).get()[0].volume = v;
        });

        if (v < 0.1) {
            btn.removeClass(function (index, css) {
                   return (css.match (/(^|\s)btn\S+/g) || []).join(' ');
                })
                .addClass('btn btn-sm btn-danger');
            icon.removeClass().addClass('fa fa-fw fa-volume-off');
        } else if (v < 0.8) {
            btn.removeClass(function (index, css) {
                   return (css.match (/(^|\s)btn\S+/g) || []).join(' ');
               }).addClass('btn btn-sm btn-info');
            icon.removeClass().addClass('fa fa-fw fa-volume-down');
        } else {
            btn.removeClass(function (index, css) {
                   return (css.match (/(^|\s)btn\S+/g) || []).join(' ');
               }).addClass('btn btn-sm btn-primary');
            icon.removeClass().addClass('fa fa-fw fa-volume-up');
        }
        return false;
    });

    // Hide the spalsh after 3 secs.
    setTimeout(function() {
        devSip.logShow();
    }, 3000);


    /**
     * Stopwatch object used for call timers
     *
     * @param {dom element} elem
     * @param {[object]} options
     */
    var Stopwatch = function(elem, options) {

        // private functions
        function createTimer() {
            return document.createElement("span");
        }

        var timer = createTimer(),
            offset,
            clock,
            interval;

        // default options
        options           = options || {};
        options.delay     = options.delay || 1000;
        options.startTime = options.startTime || Date.now();

        // append elements
        elem.appendChild(timer);

        function start() {
            if (!interval) {
                offset   = options.startTime;
                interval = setInterval(update, options.delay);
            }
        }

        function stop() {
            if (interval) {
                clearInterval(interval);
                interval = null;
            }
        }

        function reset() {
            clock = 0;
            render();
        }

        function update() {
            clock += delta();
            render();
        }

        function render() {
            timer.innerHTML = moment(clock).format('mm:ss');
        }

        function delta() {
            var now = Date.now(),
                d   = now - offset;

            offset = now;
            return d;
        }

        // initialize
        reset();

        // public API
        this.start = start; //function() { start; }
        this.stop  = stop; //function() { stop; }
    };

});
