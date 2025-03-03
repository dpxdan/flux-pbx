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
var cur_call = null;
var ua;
var which_server;
var isAndroid = false;
var isIOS = false;
var isOnMute = false;
var isOnHold = false;
var clicklogin = "no";
var isRecording = false;
var isDnd = false;
var isNoRing = false;
var isAutoAnswer = false;
var isRegistered = false;
var vmail_subscription = false;
var presence_array = new Array();
var incomingsession = null;
var audioElement = document.createElement('audio');
var callTimer;
var oldext = false;
var gotopanel = false;
var isIncomingCall = false;
var isOutboundCall = false;
var sessionDescriptionHandlerFactoryOptions = {
			peerConnectionOptions: {
				iceCheckingTimeout: 500,
				rtcConfiguration: {
					sdpSemantics: 'unified-plan',
					iceServers: [{urls: 'stun:stun.l.google.com:19302'},{urls: 'turn:sbcdev4.flux.net.br:5349',username: 'fluxDev',credential: 'fluxDevTurn2010'}]
				}
			},
			constraints: {
			audio: true,
			video: false
			},
			alwaysAcquireMediaFirst: false
		};
$(document).ready(function() {


    if (typeof(user) === 'undefined') {
        user = JSON.parse(localStorage.getItem('SIPCreds'));
        if (!user){
        init();
        }

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
            autostart: true,
            register: true,
            hackIpInContact: true,
            hackWssInTransport: true,
            hackViaTcp: false,
            registerExpires: 60,
            wsServerMaxReconnection: 5000,
            wsServerReconnectionTimeout: 1,
            connectionRecoveryMaxInterval: 3,
            connectionRecoveryMinInterval: 2,
            sessionDescriptionHandlerFactoryOptions: sessionDescriptionHandlerFactoryOptions,
//            contactTransport: 'wss',
            hackAllowUnregisteredOptionTags: true,
            userAgentString: 'SIP Dev',
            contactName: user.User,
            allowLegacyNotifications: true,
            allowOutOfDialogRefers: true,
            enableTurnServers: true,
            enableDefaultModifiers: true,
            stunServers: ['stun.l.google.com:19302'],
            turnServers: [{
              urls: 'turn:sbcdev4.flux.net.br:5349',
              username: 'fluxDev',
              credential: 'fluxDevTurn2010'
            }],
            iceTransportPolicy: "relay",
            iceCheckingTimeout: 500,
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
		formatPhone: function (phone) {

			var num;
			if (phone.indexOf('@')) {
			  num = phone.split('@')[0];
			} else {
			  num = phone;
			}
			num = num.toString().replace(/[^0-9]/g, '');
			if (num.length === 19) {
			  return '(' + num.substr(0, 3) + ') ' + num.substr(3, 3) + '-' + num.substr(6, 4);
			} else if (num.length === 13) {
			  return '(' + num.substr(1, 3) + ') ' + num.substr(4, 3) + '-' + num.substr(7, 4);
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
            newSess.devid       = devSip.getUniqueID();

            var status;

            if (newSess.direction === 'incoming') {
                devSip.notifyMe("Chamada de: " + newSess.displayName);
                status = "Chamada de: "+ newSess.displayName;
                devSip.startRingTone();
                
            } else {
                status = "Discando: "+ newSess.displayName;
            }

            devSip.logCall(newSess, 'ringing');

            devSip.setCallSessionStatus(status);

            // EVENT CALLBACKS

            newSess.on('progress',function(e) {
                if (e.direction === 'outgoing') {
                    devSip.setCallSessionStatus('Chamando...');
                }
            });

            newSess.on('connecting',function(e) {
                if (e.direction === 'outgoing') {
                    devSip.setCallSessionStatus('Conectando...');
                }
            });

            newSess.on('accepted',function(e) {
                // If there is another active call, hold it
                if (devSip.callActiveID && devSip.callActiveID !== newSess.devid) {
                    devSip.phoneHoldButtonPressed(devSip.callActiveID);
                }

                devSip.stopRingbackTone();
                devSip.stopRingTone();
                devSip.setCallSessionStatus('Atendida');
                devSip.logCall(newSess, 'answered');
                devSip.callActiveID = newSess.devid;
                
            });

            newSess.on('hold', function(e) {
                devSip.callActiveID = null;
                devSip.logCall(newSess, 'holding');
            });

            newSess.on('unhold', function(e) {
                devSip.logCall(newSess, 'resumed');
                devSip.callActiveID = newSess.devid;
            });

            newSess.on('muted', function(e) {
                devSip.Sessions[newSess.devid].isMuted = true;
                devSip.setCallSessionStatus("Mudo");
            });

            newSess.on('unmuted', function(e) {
                devSip.Sessions[newSess.devid].isMuted = false;
                devSip.setCallSessionStatus("Atendida");
            });

            newSess.on('cancel', function(e) {
                devSip.stopRingTone();
                devSip.stopRingbackTone();
                devSip.setCallSessionStatus("Cancelada");
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
                devSip.setCallSessionStatus('Finalizada');
            });

            newSess.on('rejected',function(e) {
                devSip.stopRingTone();
                devSip.stopRingbackTone();
                devSip.setCallSessionStatus('Rejeitada');
                devSip.callActiveID = null;
                devSip.logCall(this, 'ended');
                newSess             = null;
            });

            devSip.Sessions[newSess.devid] = newSess;

        },

        // getUser media request refused or device was not present
        getUserMediaFailure : function(e) {
            //window.console.error('getUserMedia failed:', e);
            //devSip.setError(true, 'Media Error.', 'You must allow access to your microphone.  Check the address bar.', true);
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
                    id   : session.devid,
                    time : new Date().getTime()
                },
                calllog = JSON.parse(localStorage.getItem('sipCalls'));

            if (!calllog) { calllog = {}; }

            if (!calllog.hasOwnProperty(session.devid)) {
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


            i  = '<div class="list-group-item sip-logitem sip-logitem-phone clearfix '+callClass+'" data-uri="'+item.uri+'" data-sessionid="'+item.id+'" title="Clique para Discar">';
            i += '<div class="clearfix"><div class="pull-left">';
            i += '<i class="fa fa-fw ' + callIcon + ' fa-fw"></i> <strong>' + devSip.formatPhone(item.uri) + '</strong><br><small>' + moment(item.start).format('DD/MM HH:mm a') + '</small>';
            i += '</div>';
            i += '<div class="pull-right text-right"><em>'+item.clid+'</em><br>' + callLength+'</div></div>';

            if (callActive) {
                i += '<div class="btn-group btn-group-xs pull-right" style="box-shadow: none;">';
                if (item.status === 'ringing' && item.flow === 'incoming') {
                    i += '<button class="btn btn-xs btn-success btnCall" title="Atender"><i class="fa fa-phone" style="color: #fff;"></i></button>';
                } 
                   else {
                    i += '<button class="btn btn-xs btn-primary btnHoldResume" title="Espera"><i class="fa fa-pause" style="color: #fff;"></i></button>';
                    i += '<button class="btn btn-xs btn-info btnTransfer" title="Transferir"><i class="fa fa-random" style="color: #fff;"></i></button>';
                    i += '<button class="btn btn-xs btn-warning btnMute" title="Mudo"><i class="fa fa-fw fa-microphone" style="color: #fff;"></i></button>';
                }
                i += '<button class="btn btn-xs btn-danger btnHangUp" title="Desligar"><i class="fa fa-stop" style="color: #fff;"></i></button>';
                i += '</div>';
            }
            i += '</div>';

            $('#sip-logitems').append(i);
            $('#sip-logitems-phone').append(i);


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
            $('#sip-logitems-phone').scrollTop(0);
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
                $('#sip-log-phone').removeClass('hide');

                // empty existing logs
                $('#sip-logitems').empty();
                $('#sip-logitems-phone').empty();

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
                $('#sip-log-phone').addClass('hide');
            }
        },

        logClear : function() {

            localStorage.removeItem('sipCalls');
            devSip.logShow();
        },
        
		notifyMe: function (msg) {

		if (Notification.permission === "granted") {
		  let img = '/assets/img/logo-ct.png';
		  let notification = new Notification('Ramal Dev', {
			body: msg,
			icon: img
		  });
		  notification.onclick = function () {
			parent.focus();
			window.focus();
			this.close();
		  };
		  notification.onclose = function () {
			parent.focus();
			window.focus();
			this.close();
		  };
		  notification.onerror = function () {
			parent.focus();
			window.focus();
			this.close();
		  };
		}

		},
		
        getDevices: function() {
        $("#audio-devices").show();
        
        var constraints = {
            audio: true,
            video: false,
        };
        navigator.mediaDevices.getUserMedia(constraints);
        navigator.mediaDevices.enumerateDevices()
            .then(function(devices) {
                var i = 1;
                var div = document.querySelector("#listmic"),
                    frag = document.createDocumentFragment(),
                    selectmic = document.createElement("select");
                    //selectmic.classList.add("form-select");
        
                while (div.firstChild) {
                 div.removeChild(div.firstChild);
                }
                i = 1;
                selectmic.id = "selectmic";
                
               //$('#selectmic').addClass('form-select');
                //selectmic.style = "background-color: black;";
        
                devices.forEach(function(device) {
        
        
                    if (device.kind === 'audioinput') {
        
                        selectmic.options.add(new Option('Microfone: ' + (device.label ? device.label : (i)), device.deviceId));
                        i++;
        
                    }
                });
        
                frag.appendChild(selectmic);
        
                div.appendChild(frag);
        
            })
            .catch(function(err) {
                console.log(err.name + ": " + err.message);
            });

        
        
        },
        
        extSettings: function() {
        
        $("#extension-settings").show();
        
        // Save form to localStorage and validate
       $('#extension-settings').delegate('.btnSaveExt', 'click', function(event) {

       
           var user = JSON.parse(localStorage.getItem('SIPUser'));
       
           if (user) {
               $.each(user, function(k, v) {
                   $('input[name="'+k+'"]').val(v);
               });
           }
        
            var user  = {},
                valid = true;
        
            event.preventDefault();
        
            // validate the form
            $('#extension-settings input').each(function(i) {
                if ($(this).val() === '') {
                    $(this).closest('.form-group').addClass('has-error');
                    valid = false;
                } else {
                    $(this).closest('.form-group').removeClass('has-error');
                }
                user[$(this).attr('name')] = $(this).val();
            });
        
            // launch the phone window.
            if (valid) {
                localStorage.setItem('SIPUser', JSON.stringify(user));
                $("#extension-settings").hide();
            }
        
        });
        
        
        },
        
		handleNotify: function (r) {
		console.log(r.request.method);
		console.log(r.request.body);
		console.log(r.request.headers);
		var headers = r.request.headers['Event'];
		console.log(r.request.headers);
		$("#vmailcount").show();
		var newMessages = 0;
		var oldMessages = 0;
		var span = document.getElementById('vmailcount');
		var gotmsg = r.request.body.match(/voice-message:\s*(\d+)\/(\d+)/i);
		if (gotmsg) {
		  newMessages = parseInt(gotmsg[1]);
		  oldMessages = parseInt(gotmsg[2]);
		  if (newMessages) {
			$("#vmailcount").removeClass('').addClass('btn-warning');

		  } else {
			$("#vmailcount").removeClass('btn-warning').addClass('');

		  }
		  span.innerText = newMessages + "/" + oldMessages;
		}


		},

        sipCall: function (target) {
            
                    try {
                      var session = devSip.phone.invite(target, {
                        media: {
                          remote: {
                            video: document.getElementById('remoteVideo'),
                            audio: document.getElementById('localVideo')
                          }
                        },
                        sessionDescriptionHandlerOptions: {
                          constraints: {
                            audio: true,
                            video: false
                          },
                          rtcConfiguration: {
                            RTCConstraints: {
                              "optional": [{
                                'DtlsSrtpKeyAgreement': 'true'
                              }]
                            },
                            stream: devSip.Stream
                          }
            
                        }
                      });
                      session.direction = 'outgoing';
                      devSip.newSession(session);
                      
                      var remoteVideo = document.getElementById('audioRemote');
                      var localVideo = document.getElementById('audioRemote');
            
            
                      session.on('trackAdded', function () {
                        // We need to check the peer connection to determine which track was added
            
                        var pc = session.sessionDescriptionHandler.peerConnection;
            
                        // Gets remote tracks
                        var remoteStream = new MediaStream();
                        pc.getReceivers().forEach(function (receiver) {
                          remoteStream.addTrack(receiver.track);
                        });
                        //remoteVideo.srcObject = remoteStream;
                        //remoteVideo.play();
                      });
            
            
                    } catch (e) {
                      throw (e);
                    }
                  },

        sipTransfer : function(sessionid) {

            var session      = devSip.Sessions[sessionid],
                target = window.prompt('Insira o destino', '');

            devSip.setCallSessionStatus('<i>Transferindo a chamada...</i>');
            session.refer(target);
        },

        sipHangUp : function(sessionid) {

            var session = devSip.Sessions[sessionid];
            // session.terminate();
            if (!session) {
                return;
            } else if (session.startTime) {
                session.bye();
            } else if (session.reject) {
                session.reject();
            } else if (session.cancel) {
                session.cancel();
            }

        },

        sipSendDTMF : function(digit) {

            try { devSip.dtmfTone.play(); } catch(e) { }

            var a = devSip.callActiveID;
            if (a) {
                var session = devSip.Sessions[a];
                session.dtmf(digit);
            }
        },

        phoneCallButtonPressed : function(sessionid) {

            var session      = devSip.Sessions[sessionid],
                target = $("#numDisplay").val();

            if (!session) {

                $("#numDisplay").val("");
                devSip.sipCall(target);

            } else if (session.accept && !session.startTime) {

            session.accept({
					  media: {
						remote: {
						  video: document.getElementById('audioRemote'),
						  audio: document.getElementById('audioRemote')
						}
					  },
					  sessionDescriptionHandlerOptions: {
						constraints: {
						  audio: true,
						  video: false
						},
						rtcConfiguration: {
						  RTCConstraints: {
							"optional": [{
							  'DtlsSrtpKeyAgreement': 'true'
							}]
						  },
						  stream: devSip.Stream
						}
		  
					  }
					});

                /*session.accept({
                    media : {
                        stream      : devSip.Stream,
                        constraints : { audio : true, video : false },
                        render      : {
                            remote : { audio: $('#audioRemote').get()[0] }
                        },
                        RTCConstraints : { "optional": [{ 'DtlsSrtpKeyAgreement': 'true'} ]}
                    }
                });*/
            }
        },

        phoneMuteButtonPressed : function (sessionid) {

            var session = devSip.Sessions[sessionid];

            if (!session.isMuted) {
                session.mute();
            } else {
                session.unmute();
            }
        },

		phoneHoldButtonPressed: function (session) {
	
			var session = devSip.Sessions[session];
			var direction = session.sessionDescriptionHandler.getDirection();
	
			if (direction === 'sendrecv') {
			  console.log('Chamada não está em espera');
			  session.hold();
			  devSip.logCall(session, 'holding');
			  devSip.setCallSessionStatus("Em espera");
			} else {
			  session.unhold();
			  devSip.logCall(session, 'resumed');
			  devSip.callActiveID = session.fluxid;
			  devSip.setCallSessionStatus("Conversando");
			  console.log('Saida Espera');
			}
		  },
		  
		setLogin: function() {
		data = JSON.parse(localStorage.getItem('SIPCreds'));
		 if (!data) {
		 $("#mdlLogin").modal('show');
		 
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
                //$('#numDisplay').prop('disabled', 'disabled');
            } else {
                //$('#numDisplay').removeProp('disabled');
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
               // devSip.setError(true, 'Unsupported Browser.', 'Your browser does not support the features required for this phone.');
               // window.console.error("WebRTC support not found");
                return true;
            }
        }
    };


    // Throw an error if the browser can't hack it.
    if (!devSip.hasWebRTC()) {
        return true;
    }

    devSip.phone = new SIP.UA(devSip.config);

    devSip.phone.on('connected', function(e) {
        devSip.setStatus("Conectado");
        console.log('Connected');
    });

    devSip.phone.on('disconnected', function(e) {
        devSip.setStatus("Desconectado");

        // disable phone
        //devSip.setError(true, 'Websocket Disconnected.', 'An Error occurred connecting to the websocket.');

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
            localStorage.removeItem('devPhone');
            devSip.phone.stop();
        };

        window.onbeforeunload = closeEditorWarning;
        window.onunload       = closePhone;

        // This key is set to prevent multiple windows.
        localStorage.setItem('devPhone', 'true');

        $("#mldError").modal('hide');
        devSip.setStatus("Registrado");


    });

    devSip.phone.on('registrationFailed', function(e) {
       // devSip.setError(true, 'Registration Error.', 'An Error occurred registering your phone. Check your settings.');
        devSip.setStatus("Error: Registration Failed");
    });

    devSip.phone.on('unregistered', function(e) {
       // devSip.setError(true, 'Registration Error.', 'An Error occurred registering your phone. Check your settings.');
        devSip.setStatus("Error: Registration Failed");
    });

    devSip.phone.on('invite', function (incomingSession) {

        var session = incomingSession;

        session.direction = 'incoming';
        devSip.newSession(session);
    });
    
    devSip.phone.on('notify', devSip.handleNotify);


    // Auto-focus number input on backspace.
    $('#sipClient').keydown(function(event) {
        if (event.which === 8) {
            $('#numDisplay').focus();
        }
    });


    $('#sipClient').keydown(function(event) {
        if (event.which === 27) {
          console.log('Event Pressed');
           $("#tab-contacts").trigger("click");
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

    $('#pills-settings').delegate('.btnSettings', 'click', function(event) {
         devSip.getDevices();
         // to close the dropdown
         return true;
     });

    $('#pills-settings').delegate('.btnExtSettings', 'click', function(event) {
          devSip.extSettings();
          // to close the dropdown
          return true;
      });

	$('#ex1').delegate('.tabPhone', 'click', function(event) {
	console.log('tabPhone');
	localStorage.setItem('userTab', 'tabPhone');
	});


	$('#ex1').delegate('.tabHistory', 'click', function(event) {
	console.log('tabHistory');
	localStorage.setItem('userTab', 'tabHistory');
	});


	$('#ex1').delegate('.tabContacts', 'click', function(event) {
	console.log('tabContacts');
	localStorage.setItem('userTab', 'tabContacts');
	});


	$('#ex1').delegate('.tabSettings', 'click', function(event) {
	console.log('tabSettings');
	localStorage.setItem('userTab', 'tabSettings');

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
    

    $('#sip-logitems-phone').delegate('.sip-logitem-phone .btnCall', 'click', function(event) {
            var sessionid = $(this).closest('.sip-logitem-phone').data('sessionid');
            devSip.phoneCallButtonPressed(sessionid);
            return false;
        });

    $('#asknotificationpermission').click(function (event) {
              event.preventDefault();
        
              // Let's check if the browser supports notifications
              if (!("Notification" in window)) {
                alert("Este navegador não oferece suporte para notificação de desktop");
              }
        
              // Otherwise, we need to ask the user for permission
              // Note, Chrome does not implement the permission static property
              // So we have to check for NOT 'denied' instead of 'default'
              else if (Notification.permission !== 'denied') {
                Notification.requestPermission(function (permission) {
        
                  // Whatever the user answers, we make sure we store the information
                  if (!('permission' in Notification)) {
                    Notification.permission = permission;
                  }
        
                  // If the user is okay, let's create a notification
                  if (permission === "granted") {
                    console.log("Permissão de notificação concedida!");
                    var notification = devSip.notifyMe("Permissão de notificação concedida!");
                    $("#asknotificationpermission").hide();
                  }
                });
              } else {
                alert(`Permission is ${Notification.permission}`);
              }
        
        
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


    $('#sip-logitems-phone').delegate('.sip-logitem-phone .btnHoldResume', 'click', function(event) {
        var sessionid = $(this).closest('.sip-logitem-phone').data('sessionid');
        devSip.phoneHoldButtonPressed(sessionid);
        return false;
    });
    
    $('#sip-logitems-phone').delegate('.sip-logitem-phone .btnHangUp', 'click', function(event) {
        var sessionid = $(this).closest('.sip-logitem-phone').data('sessionid');
        devSip.sipHangUp(sessionid);
        return false;
    });
    
    $('#sip-logitems-phone').delegate('.sip-logitem-phone .btnTransfer', 'click', function(event) {
        var sessionid = $(this).closest('.sip-logitem-phone').data('sessionid');
        devSip.sipTransfer(sessionid);
        return false;
    });
    
    $('#sip-logitems-phone').delegate('.sip-logitem-phone .btnMute', 'click', function(event) {
        var sessionid = $(this).closest('.sip-logitem-phone').data('sessionid');
        devSip.phoneMuteButtonPressed(sessionid);
        return false;
    });
    
    $('#sip-logitems-phone').delegate('.sip-logitem-phone', 'dblclick', function(event) {
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

    function init() {
    user = JSON.parse(localStorage.getItem('SIPCreds'));
    if (!user) {
    		 $("#mdlLogin").modal('show');
    		 
    		 }
    $('#mdlLogin').on('show.bs.modal', function() {
     
                  var user = JSON.parse(localStorage.getItem('SIPCreds'));
     
                  if (user) {
                      $.each(user, function(k, v) {
                          $('input[name="'+k+'"]').val(v);
                      });
                  }
              });
    $('#btnConfig').click(function(event) {
     
                  var user  = {},
                      valid = true;
     
                  event.preventDefault();
     
                  // validate the form
                  $('#mdlLogin input').each(function(i) {
                      if ($(this).val() === '') {
                          $(this).closest('.form-group').addClass('has-error');
                          valid = false;
                      } else {
                          $(this).closest('.form-group').removeClass('has-error');
                      }
                      user[$(this).attr('name')] = $(this).val();
                  });
     
                  // launch the phone window.
                  if (valid) {
                      localStorage.setItem('SIPCreds', JSON.stringify(user));
                      //$("#mdlLogin").modal('hide');
                      location.reload();
                     
                  }
     
              });
    };
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

$(document).ready(function() {
 
   
   $("#audio-devices").hide();
    $("#extension-settings").hide();
    
    var constraints = {
        audio: true,
        video: false,
    };
    navigator.mediaDevices.getUserMedia(constraints);
    navigator.mediaDevices.enumerateDevices()
        .then(function(devices) {
            var i = 1;
            var div = document.querySelector("#listmic"),
                frag = document.createDocumentFragment(),
                selectmic = document.createElement("select");
                selectmic.classList.add("form-select");
    
            while (div.firstChild) {
             div.removeChild(div.firstChild);
            }
            i = 1;
            
            selectmic.id = "selectmic";
           //$('#selectmic').addClass('form-select');
            //selectmic.style = "background-color: black;";
    
            devices.forEach(function(device) {
    
    
                if (device.kind === 'audioinput') {
    
                    selectmic.options.add(new Option('Microfone: ' + (device.label ? device.label : (i)), device.deviceId));
                    i++;
    
                }
            });
    
            frag.appendChild(selectmic);
    
            div.appendChild(frag);
    
        })
        .catch(function(err) {
            console.log(err.name + ": " + err.message);
        });

    audioElement.setAttribute('src', './audio/mp3/ring.mp3');
   // devSip.setLogin();

});