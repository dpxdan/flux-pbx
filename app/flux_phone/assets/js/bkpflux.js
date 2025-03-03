/******/
(function(modules) { // webpackBootstrap
  /******/ // install a JSONP callback for chunk loading
  /******/
  function webpackJsonpCallback(data) {
    /******/
    var chunkIds = data[0];
    /******/
    var moreModules = data[1];
    /******/
    var executeModules = data[2];
    /******/
    /******/ // add "moreModules" to the modules object,
    /******/ // then flag all "chunkIds" as loaded and fire callback
    /******/
    var moduleId, chunkId, i = 0,
      resolves = [];
    /******/
    for (; i < chunkIds.length; i++) {
      /******/
      chunkId = chunkIds[i];
      /******/
      if (installedChunks[chunkId]) {
        /******/
        resolves.push(installedChunks[chunkId][0]);
        /******/
      }
      /******/
      installedChunks[chunkId] = 0;
      /******/
    }
    /******/
    for (moduleId in moreModules) {
      /******/
      if (Object.prototype.hasOwnProperty.call(moreModules, moduleId)) {
        /******/
        modules[moduleId] = moreModules[moduleId];
        /******/
      }
      /******/
    }
    /******/
    if (parentJsonpFunction) parentJsonpFunction(data);
    /******/
    /******/
    while (resolves.length) {
      /******/
      resolves.shift()();
      /******/
    }
    /******/
    /******/ // add entry modules from loaded chunk to deferred list
    /******/
    deferredModules.push.apply(deferredModules, executeModules || []);
    /******/
    /******/ // run deferred modules when all chunks ready
    /******/
    return checkDeferredModules();
    /******/
  };
  /******/
  function checkDeferredModules() {
    /******/
    var result;
    /******/
    for (var i = 0; i < deferredModules.length; i++) {
      /******/
      var deferredModule = deferredModules[i];
      /******/
      var fulfilled = true;
      /******/
      for (var j = 1; j < deferredModule.length; j++) {
        /******/
        var depId = deferredModule[j];
        /******/
        if (installedChunks[depId] !== 0) fulfilled = false;
        /******/
      }
      /******/
      if (fulfilled) {
        /******/
        deferredModules.splice(i--, 1);
        /******/
        result = __webpack_require__(__webpack_require__.s = deferredModule[0]);
        /******/
      }
      /******/
    }
    /******/
    /******/
    return result;
    /******/
  }
  /******/
  /******/ // The module cache
  /******/
  var installedModules = {};
  /******/
  /******/ // object to store loaded and loading chunks
  /******/ // undefined = chunk not loaded, null = chunk preloaded/prefetched
  /******/ // Promise = chunk loading, 0 = chunk loaded
  /******/
  var installedChunks = {
    /******/
    0: 0
      /******/
  };
  /******/
  /******/
  var deferredModules = [];
  /******/
  /******/ // The require function
  /******/
  function __webpack_require__(moduleId) {
    /******/
    /******/ // Check if module is in cache
    /******/
    if (installedModules[moduleId]) {
      /******/
      return installedModules[moduleId].exports;
      /******/
    }
    /******/ // Create a new module (and put it into the cache)
    /******/
    var module = installedModules[moduleId] = {
      /******/
      i: moduleId,
      /******/
      l: false,
      /******/
      exports: {}
      /******/
    };
    /******/
    /******/ // Execute the module function
    /******/
    modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
    /******/
    /******/ // Flag the module as loaded
    /******/
    module.l = true;
    /******/
    /******/ // Return the exports of the module
    /******/
    return module.exports;
    /******/
  }
  /******/
  /******/
  /******/ // expose the modules object (__webpack_modules__)
  /******/
  __webpack_require__.m = modules;
  /******/
  /******/ // expose the module cache
  /******/
  __webpack_require__.c = installedModules;
  /******/
  /******/ // define getter function for harmony exports
  /******/
  __webpack_require__.d = function(exports, name, getter) {
    /******/
    if (!__webpack_require__.o(exports, name)) {
      /******/
      Object.defineProperty(exports, name, {
        enumerable: true,
        get: getter
      });
      /******/
    }
    /******/
  };
  /******/
  /******/ // define __esModule on exports
  /******/
  __webpack_require__.r = function(exports) {
    /******/
    if (typeof Symbol !== 'undefined' && Symbol.toStringTag) {
      /******/
      Object.defineProperty(exports, Symbol.toStringTag, {
        value: 'Module'
      });
      /******/
    }
    /******/
    Object.defineProperty(exports, '__esModule', {
      value: true
    });
    /******/
  };
  /******/
  /******/ // create a fake namespace object
  /******/ // mode & 1: value is a module id, require it
  /******/ // mode & 2: merge all properties of value into the ns
  /******/ // mode & 4: return value when already ns object
  /******/ // mode & 8|1: behave like require
  /******/
  __webpack_require__.t = function(value, mode) {
    /******/
    if (mode & 1) value = __webpack_require__(value);
    /******/
    if (mode & 8) return value;
    /******/
    if ((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
    /******/
    var ns = Object.create(null);
    /******/
    __webpack_require__.r(ns);
    /******/
    Object.defineProperty(ns, 'default', {
      enumerable: true,
      value: value
    });
    /******/
    if (mode & 2 && typeof value != 'string')
      for (var key in value) __webpack_require__.d(ns, key, function(key) {
        return value[key];
      }.bind(null, key));
    /******/
    return ns;
    /******/
  };
  /******/
  /******/ // getDefaultExport function for compatibility with non-harmony modules
  /******/
  __webpack_require__.n = function(module) {
    /******/
    var getter = module && module.__esModule ?
      /******/
      function getDefault() {
        return module['default'];
      } :
      /******/
      function getModuleExports() {
        return module;
      };
    /******/
    __webpack_require__.d(getter, 'a', getter);
    /******/
    return getter;
    /******/
  };
  /******/
  /******/ // Object.prototype.hasOwnProperty.call
  /******/
  __webpack_require__.o = function(object, property) {
    return Object.prototype.hasOwnProperty.call(object, property);
  };
  /******/
  /******/ // __webpack_public_path__
  /******/
  __webpack_require__.p = "";
  /******/
  /******/
  var jsonpArray = window["webpackJsonp"] = window["webpackJsonp"] || [];
  /******/
  var oldJsonpFunction = jsonpArray.push.bind(jsonpArray);
  /******/
  jsonpArray.push = webpackJsonpCallback;
  /******/
  jsonpArray = jsonpArray.slice();
  /******/
  for (var i = 0; i < jsonpArray.length; i++) webpackJsonpCallback(jsonpArray[i]);
  /******/
  var parentJsonpFunction = oldJsonpFunction;
  /******/
  /******/
  /******/ // add entry module to deferred list
  /******/
  deferredModules.push([174, 1]);
  /******/ // run deferred modules when ready
  /******/
  return checkDeferredModules();
  /******/
})
/************************************************************************/
/******/
({

  /***/
  174:
  /***/
    (function(module, exports, __webpack_require__) {

    const SIP = __webpack_require__(175);
    const getStats = __webpack_require__(183);
    const $ = __webpack_require__(31);

    window.jQuery = $;
    window.getStats = getStats;
    window.SIP = SIP;


    $(function () {
    
      const bootstrap = __webpack_require__(184);
      const moment = __webpack_require__(0);
      const SDK = __webpack_require__(167);
      const WebPhone = __webpack_require__(206) || window.FluxPhone.WebPhone;
    
      /** @type {SDK} */
      var sdk = null;
      var platform = null;
      /** @type {WebPhone} */
      var webPhone = null;
      var config = null;
      var user = null;
    
    
      var logLevel = 10;
      var username = null;
      var extension = null;
      var sipInfo = null;
//      var cur_call = null;
//      var isDnd = null;
      var $app = $('#app');
    
      var nextCallID = 0;
      var conference = {};
    
      var $loginTemplate = $('#template-login-addon-login');
      var $loginFluxTemplate = $('#template-addon-login');
      var $historyTemplate = $('#template-call-log');
      var $phoneTemplate = $('#template-addon-phone');
      var $tabTemplate = $('#template-tab');
      var $callTemplate = $('#template-call');
    
      var $errorTemplate = $('#template-error');
      var $agentTemplate = $('#template-agent');
      var $transferTemplate = $('#template-transfer');
      var $incomingTemplate = $('#template-incoming');
      var $configTemplate = $('#template-config');
      var $acceptedTemplate = $('#template-accepted');
      var $conferenceItemTemplate = $('#template-conference-item');
      var $ItemsButtons = $('#sip-logitems');
    
      var remoteVideoElement = document.getElementById('remoteVideo');
      var localVideoElement = document.getElementById('localVideo');
      var activeCallInfo = '';
      var outboundCall = false;
      var agentlogin = false;
      var confSessionId = '';
    
      var ringtone = document.getElementById('ringtone');
      var ringbacktone = document.getElementById('ringbacktone');
      var dtmfTone = document.getElementById('dtmfTone');
      var Sessions = [];
      var callTimers = {};
      var callActiveID = null;
      var callVolume = 1;
      var Stream = null;
      var isIOS = null;
      var isOnMute = false;
      var isOnHold = false;
      var cur_call = null;
      var isIncomingCall = false;
      var isOutboundCall = false;
      var isRecording = false;
      var isDnd = false;
      var isNoRing = false;
      var isAutoAnswer = false;
      var isRegistered = false;
      var vmail_subscription = false;
      var presence_array = new Array();
      var windowUser = window.name;
    
      var cards = document.querySelectorAll('.card');
    
      [...cards].forEach((card) => {
        card.addEventListener('click', function () {
          card.classList.toggle('is-flipped');
        });
      });
    
    
      /**
       * @param {jQuery|HTMLElement} $tpl
       * @return {jQuery|HTMLElement}
       */
      function cloneTemplate($tpl) {
        return $($tpl.html());
      }
    
      function start() {
    
        var user = JSON.parse(localStorage.getItem('SIPCreds'));
        var statusPhone = JSON.parse(localStorage.getItem('fluxPhone'));
        console.log('Window ' + windowUser);
    
        if (!user) {
          login();
          return;
        } else
    
        {
          fluxInit();
    
        }
    
      }
    
      function login(server, appKey, appSecret, login, ext, password, ll) {
        sdk = new SDK({
          appKey,
          appSecret,
          server
        });
    
        platform = sdk.platform();
    
        // TODO: Improve later to support international phone number country codes better
        if (login) {
          login = login.match(/^[+1]/) ? login : '1' + login;
          login = login.replace(/\W/g, '');
        }
    
        platform.login({
          code: "U0pDMTJQMDFQQVMwMHxBQUM5TTVzVm1UTjIzdFVIaE96cEJOOTlMRGlKakRlR0U4ZEFVOVFHYjZxV3pic2g1dzBNQU5JUHZVY3duWGxSNmRWRWdzR1ZFX3NpSk5BM3hLT3JOc0d5cjcwT09aSzdpVjN5MW5ZM3VwRjQtLVRudTltTFlybWVfVks1TXVWbHZnQVdHTjR0ZGdpLXFjckJqUi0tdHpIQlZ1aDFpb0NhdEdMWlhKVG5vXzB8VHVSSjhRfHUtTjFNeld5TGd2YzdScXZ2d1JUdFF8QVE",
          redirectUri: "https://sbcdev4.flux.net.br/oauth.html"
        }).then(function (response) {
          console.log('The redirect uri value :', response);
        }).catch(function (e) {
          //run exception code
        });
      }
    
      function fluxInit() {
    
        var $fluxphone = cloneTemplate($phoneTemplate);
        var $fluxtab = cloneTemplate($tabTemplate);
        var user = JSON.parse(localStorage.getItem('SIPCreds'));
        var remoteVideoElement = document.getElementById('remoteVideo');
        var localVideoElement = document.getElementById('localVideo');
        var $server = 'https://sbcdev4.flux.net.br';
        var $appKey = 's6Xt27cgQlKJXA1QyWZTvg';
        var $appSecret = '5Rd9O5ACQjyJBaTKg8Ic_w7sh8uxtYQAato7qLIjIx5g';
        var $login = user.Display;
        var $ext = user.User;
        var $password = user.Pass;
        var $logLevel = 1;
        loginUser($server, $appKey, $appSecret, $ext, $password, $logLevel)
          .then(
            (message) => {
              fluxStart();
            }
          );
    
        $app.empty()
          .append($fluxtab)
          .append($fluxphone);
    
        // $app.empty()
        // .append($fluxphone);
      }
    
      function onInvite(session) {
        outboundCall = false;
        cur_call = session;
        //        console.log('EVENT: Invite', session.request);
        //console.log('UA INVITE', arguments);
        //      console.log('To', session.request.to.friendlyName, session.request.to.friendlyName);
        //    console.log('From', session.request.from.displayName, session.request.from.friendlyName);
        //notifyMe("Chamada de: "+ session.request.from.friendlyName);
    
        if (session.request.headers['h323-conf-id'] !== null) {
          var conf_id = session.request.headers['h323-conf-id'];
          console.log('EVENT: Conf ID', session.request.headers['h323-conf-id']);
    
        }
        if (session !== null && session.request.headers['Alert-Info'] && session.request.headers['Alert-Info'][0].raw === 'Auto Answer') {
          session
            .accept()
            .then(function () {
              onAccepted(session);
            })
            .catch(function (e) {
              console.error('Accept failed', e.stack || e);
            });
        } else {
          var $modal = cloneTemplate($incomingTemplate).modal({
            backdrop: 'static'
          });
    
    
          var $mdlHeader = $modal.find('.modal-header').eq(0);
          var $mdlTitle = $mdlHeader.find('.modal-title').eq(0);
          $mdlTitle.html("Chamada de: " + session.request.from.displayName);
          var $sendToVM = '*99' + session.request.to.uri.user;
    
          $modal.find('.answer').on('click', function () {
            $modal.find('.before-answer').css('display', 'none');
            $modal.find('.answered').css('display', '');
            session
              .accept()
              .then(function () {
                $modal.modal('hide');
                onAccepted(session);
              })
              .catch(function (e) {
                console.error('Accept failed', e.stack || e);
              });
          });
    
          $modal.find('.decline').on('click', function () {
            session.reject({
                        statusCode: '486',
                        reasonPhrase: 'Busy Here'
                      });
                      console.log('EVENT: BUSY');
          });
    
          $modal.find('.toVoicemail').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            session
              .forward($sendToVM)
              .then(function () {
                console.log('Encaminhada');
                $modal.modal('hide');
              })
              .catch(function (e) {
                console.error('Forward failed', e.stack || e);
              });
            // session.toVoicemail();
          });
    
          $modal.find('.forward-form').on('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            session
              .forward(
                $modal
                .find('input[name=forward]')
                .val()
                .trim()
              )
              .then(function () {
                console.log('Encaminhada');
                $modal.modal('hide');
              })
              .catch(function (e) {
                console.error('Forward failed', e.stack || e);
              });
          });
    
          $modal.find('.reply-form').on('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            session
              .replyWithMessage({
                replyType: 0,
                replyText: $modal.find('input[name=reply]').val()
              })
              .then(function () {
                console.log('Replied');
                $modal.modal('hide');
              })
              .catch(function (e) {
                console.error('Reply failed', e.stack || e);
              });
          });
    
          session.on('rejected', function () {
            $modal.modal('hide');
          });
        }
      }
    
      function handleInvite(session) {
        if (cur_call !== null) {
          session.reject({
            statusCode: '486',
            reasonPhrase: 'Busy Here'
          });
          console.log('EVENT: BUSY');
        }
        if (isDnd) {
          session.reject({
            statusCode: '486',
            reasonPhrase: 'Busy Here 2'
          });
          console.log('EVENT: BUSY 2');
        } else {
          if (!cur_call) {
            session.direction = 'incoming';
            fluxSip.newSession(session);
          }
        }
      }
    
    
      const activeCallInfoTemplate = () => ({
        id: '',
        from: '',
        to: '',
        direction: '',
        sipData: {
          toTag: '',
          fromTag: ''
        }
    
      });
    
      function captureActiveCallInfo(session) {
        const direction = outboundCall ? 'Outbound' : 'Inbound';
        var activeCallInfo = activeCallInfoTemplate();
        activeCallInfo.from = session.request.from.uri.user;
        activeCallInfo.fname = session.request.from.friendlyName;
        activeCallInfo.tname = session.request.to.friendlyName;
        activeCallInfo.to = session.request.to.uri.user;
        activeCallInfo.direction = direction;
        activeCallInfo.id = session.id;
        activeCallInfo.sipData.fromTag = session.dialog.id.remoteTag;
        activeCallInfo.sipData.toTag = session.dialog.id.localTag;
        activeCallInfo.start = session.startTime;
        if (!localStorage.getItem('activeCallInfo')) {
          localStorage.setItem('activeCallInfo', JSON.stringify(activeCallInfo));
    
    
          console.log('ACTIVE CALL INFO: ' + JSON.stringify(activeCallInfo));
          console.log('FROM: ' + activeCallInfo.from);
          console.log('FROM NAME: ' + activeCallInfo.fname);
          console.log('TO: ' + activeCallInfo.to);
          console.log('TO NAME: ' + activeCallInfo.tname);
    
    
          console.log('DIRECTION: ' + activeCallInfo.direction);
          console.log('ID: ' + activeCallInfo.id);
          // console.log('FROM TAG: ' + newSess.sipData.fromTag);
          // console.log('TO TAG: ' + newSess.sipData.toTag);
          console.log('START TIME: ' + activeCallInfo.start);
          //logCall(session, 'answered');
    
    
        }
      }
    
      function onAccepted(session) {
        console.log('EVENT: Accepted', session.request);
        console.log('To', session.request.to.friendlyName, session.request.to.friendlyName);
        console.log('From', session.request.from.displayName, session.request.from.friendlyName);
    
        var $modal = cloneTemplate($acceptedTemplate).modal();
    
        var $info = $modal.find('.info').eq(0);
        var $dtmf = $modal.find('input[name=dtmf]').eq(0);
        var $transfer = $modal.find('input[id=recipient-number]').eq(0);
        var $flip = $modal.find('input[name=flip]').eq(0);
        var $conference = $modal.find('input[name=conference]').eq(0);
    
        var interval = setInterval(function () {
          var time = session.startTime ? Math.round((Date.now() - session.startTime) / 1000) + 's' : 'Ringing';
    
          $info.text('time: ' + time + '\n' + 'startTime: ' + JSON.stringify(session.startTime, null, 2) + '\n');
        }, 1000);
    
    
        function close() {
          clearInterval(interval);
          cur_call = null;
          $modal.modal('hide');
        }
    
        $modal.find('.increase-volume').on('click', function () {
          session.ua.audioHelper.setVolume(
            (session.ua.audioHelper.volume !== null ? session.ua.audioHelper.volume : 0.5) + 0.1
          );
        });
    
        $modal.find('.decrease-volume').on('click', function () {
          session.ua.audioHelper.setVolume(
            (session.ua.audioHelper.volume !== null ? session.ua.audioHelper.volume : 0.5) - 0.1
          );
        });
    
        $modal.find('.mute').on('click', function () {
          session.mute();
        });
    
        $modal.find('.unmute').on('click', function () {
          session.unmute();
        });
    
        $modal.find('.hold').on('click', function () {
          session
            .hold()
            .then(function () {
              console.log('Holding');
            })
            .catch(function (e) {
              console.error('Holding failed', e.stack || e);
            });
        });
    
        $modal.find('.unhold').on('click', function () {
          session
            .unhold()
            .then(function () {
              console.log('UnHolding');
            })
            .catch(function (e) {
              console.error('UnHolding failed', e.stack || e);
            });
        });
        $modal.find('.startRecord').on('click', function () {
          session
            .startRecord()
            .then(function () {
              console.log('Recording Started');
            })
            .catch(function (e) {
              console.error('Recording Start failed', e.stack || e);
            });
        });
    
        $modal.find('.stopRecord').on('click', function () {
          session
            .stopRecord()
            .then(function () {
              console.log('Recording Stopped');
            })
            .catch(function (e) {
              console.error('Recording Stop failed', e.stack || e);
            });
        });
    
        $modal.find('.park').on('click', function () {
          session
            .park()
            .then(function () {
              console.log('Parked');
            })
            .catch(function (e) {
              console.error('Park failed', e.stack || e);
            });
        });
    
        $modal.find('.transfer-form').on('submit', function (e) {
          e.preventDefault();
          e.stopPropagation();
          session
            .transfer($transfer.val().trim())
            .then(function () {
              console.log('Transfered');
            })
            .catch(function (e) {
              console.error('Transfer failed', e.stack || e);
            });
        });
    
        $modal.find('.transfer-form .warm').on('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          session.hold().then(function () {
            console.log('Placing the call on hold, initiating attended transfer');
            var newSession = session.ua.invite($transfer.val().trim());
            newSession.once('accepted', function () {
              console.log('New call initated. Click Complete to complete the transfer');
              $modal.find('.transfer-form button.complete').on('click', function (e) {
                session
                  .warmTransfer(newSession)
                  .then(function () {
                    console.log('Warm transfer completed');
                  })
                  .catch(function (e) {
                    console.error('Transfer failed', e.stack || e);
                  });
              });
            });
          });
        });
    
        $modal.find('.flip-form').on('submit', function (e) {
          e.preventDefault();
          e.stopPropagation();
          session
            .flip($flip.val().trim())
            .then(function () {
              console.log('Flipped');
            })
            .catch(function (e) {
              console.error('Flip failed', e.stack || e);
            });
          $flip.val('');
        });
    
        $modal.find('.dtmf-form').on('submit', function (e) {
          e.preventDefault();
          e.stopPropagation();
          session.dtmf($dtmf.val().trim());
          $dtmf.val('');
        });
    
        var $startConfButton = $modal.find('.startConf');
    
        $startConfButton.on('click', function (e) {
          initConference();
        });
    
        $modal.find('.hangup').on('click', function () {
          session.terminate();
          close();
          //fluxStart();
        });
    
        session.on('accepted', function () {
          console.log('Event: Accepted');
          state = 'In a call';
//          send_agent_cmd('/app/basic_operator_panel/index.php?state=' + state + '');
          captureActiveCallInfo(session);
        });
        session.on('progress', function () {
          console.log('Event: Progress');
        });
        session.on('rejected', function () {
          console.log('Event: Rejected');
          close();
        });
        session.on('failed', function () {
          console.log('Event: Failed', arguments);
          close();
        });
        session.on('terminated', function () {
          console.log('Event: Terminated');
          localStorage.setItem('activeCallInfo', '');
          close();
        });
        session.on('cancel', function () {
          console.log('Event: Cancel');
          close();
        });
        session.on('refer', function () {
          console.log('Event: Refer');
          close();
        });
        session.on('replaced', function (newSession) {
          console.log('Event: Replaced: old session', session, 'has been replaced with', newSession);
          close();
          onAccepted(newSession);
        });
        session.on('dtmf', function () {
          console.log('Event: DTMF');
        });
        session.on('muted', function () {
          console.log('Event: Muted');
        });
        session.on('unmuted', function () {
          console.log('Event: Unmuted');
        });
        session.on('connecting', function () {
          console.log('Event: Connecting');
        });
        session.on('bye', function () {
          console.log('Event: Bye');
          close();
        });
      }
    
      function makeCall(number, homeCountryId) {
        outboundCall = true;
        homeCountryId =
          homeCountryId ||
          (extension &&
            extension.regionalSettings &&
            extension.regionalSettings.homeCountry &&
            extension.regionalSettings.homeCountry.id) ||
          null;
    
        try {
          var session = fluxPhone.userAgent.invite(number, {
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
                stream: fluxSip.Stream
              }
    
            }
          });
          session.direction = 'outgoing';
          fluxSip.newSession(session);
    
          var remoteVideo = document.getElementById('remoteVideo');
          var localVideo = document.getElementById('localVideo');
    
    
          session.on('trackAdded', function () {
            // We need to check the peer connection to determine which track was added
    
            var pc = session.sessionDescriptionHandler.peerConnection;
    
            // Gets remote tracks
            var remoteStream = new MediaStream();
            pc.getReceivers().forEach(function (receiver) {
              remoteStream.addTrack(receiver.track);
            });
            remoteVideo.srcObject = remoteStream;
            remoteVideo.play();
          });
    
    
        } catch (e) {
          throw (e);
        }
    
    
        //fluxSip.newSession(session);
    
    
      }
    
      function formatPhone(phone) {
        var num;
        if (phone.indexOf('@')) {
          num = phone.split('@')[0];
        } else {
          num = phone;
        }
        num = num.toString().replace(/[^0-9]/g, '');
        if (num.length === 10) {
          return '0' + num;
          //return '(' + num.substr(0, 3) + ') ' + num.substr(3, 3) + '-' + num.substr(6, 4);
        } else if (num.length === 19) {
          return '(' + num.substr(1, 3) + ') ' + num.substr(4, 3) + '-' + num.substr(7, 4);
        } else {
          return num;
        }
      }
    
      function startRingTone() {
        // @ts-ignore
        try {
          ringtone.play();
        } catch (e) {}
      }
    
      function stopRingTone() {
        try {
          ringtone.pause();
        } catch (e) {}
      }
    
      function startRingbackTone() {
        try {
          ringbacktone.play();
        } catch (e) {}
      }
    
      function stopRingbackTone() {
        try {
          ringbacktone.pause();
        } catch (e) {}
      }
    
      function getUniqueID() {
        return Math.random().toString(36).substr(2, 9);
      }
    
      function newSession(session) {
        session.displayName = session.remoteIdentity.displayName || session.remoteIdentity.uri.user;
        session.fluxid = getUniqueID();
        var status;
        if (session.direction === 'Inbound') {
          status = "Entrada: " + session.displayName;
          console.log("CHAMADA DE ENTRADA");
          startRingTone();
        } else if (session.direction === 'Transfer') {
          status = "Chamando: " + session.displayName;
          console.log("TRANSFERENCIA");
          console.log("URI RID: " + session.remoteIdentity.uri.user);
          console.log("DYSPLAY NAME: " + session.displayName);
          console.log("SESSION: " + session.fluxid);
          //startRingbackTone();
        } else {
          status = "Chamando: " + session.displayName;
          console.log("CHAMADA DE SAIDA");
          console.log("URI LOCAL: " + session.localIdentity.uri.user);
          console.log("URI REMOTE: " + session.remoteIdentity.uri.user);
          console.log("DYSPLAY NAME: " + session.displayName);
          console.log("SESSION: " + session.fluxid);
          numero = session.remoteIdentity.uri.user;
          console.log("NUMERO: " + numero);
          numero = numero;
          //startRingbackTone();
        }
        logCall(session, 'ringing');
        setCallSessionStatus(status);
        // EVENT CALLBACKS
        session.on('progress', function (e) {
          if (e.direction === 'outgoing') {
            setCallSessionStatus('Discando...');
          }
        });
        session.on('connecting', function (e) {
          if (e.direction === 'outgoing') {
            setCallSessionStatus('Conectando...');
          }
        });
        session.on('accepted', function (e) {
          // If there is another active call, hold it
          if (callActiveID && callActiveID !== session.id) {
            phoneHoldButtonPressed(callActiveID);
          }
          stopRingbackTone();
          stopRingTone();
          state = 'In a call';
//          send_agent_cmd('/app/basic_operator_panel/index.php?state=' + state + '');
          setCallSessionStatus('Atendida Agente');
          logCall(session, 'answered');
          callActiveID = session.id;
          User = user.User;
        });
        session.on('hold', function (e) {
          callActiveID = null;
          logCall(session, 'holding');
        });
        session.on('unhold', function (e) {
          logCall(session, 'resumed');
          callActiveID = session.id;
        });
        session.on('muted', function (e) {
          Sessions[session.fluxid].isMuted = true;
          setCallSessionStatus("Mudo");
        });
        session.on('unmuted', function (e) {
          Sessions[session.fluxid].isMuted = false;
          setCallSessionStatus("Atendida");
        });
        session.on('cancel', function (e) {
          stopRingTone();
          stopRingbackTone();
          setCallSessionStatus("Cancelada");
          if (this.direction === 'outgoing') {
            callActiveID = null;
            session = null;
            logCall(session, 'ended');
          }
        });
        session.on('bye', function (e) {
          stopRingTone();
          stopRingbackTone();
          setCallSessionStatus("");
          logCall(session, 'ended');
          callActiveID = null;
          session = null;
        });
        session.on('failed', function (e) {
          stopRingTone();
          stopRingbackTone();
          setCallSessionStatus("");
        });
        session.on('rejected', function (e) {
          stopRingTone();
          stopRingbackTone();
          setCallSessionStatus('Rejeitada');
          callActiveID = null;
          logCall(session, 'ended');
          session = null;
        });
        Sessions[session.fluxid] = session;
      }
    
      function getUserMediaFailure(e) {
        window.console.error('getUserMedia failed:', e);
        setError(true, 'Media Error.', 'You must allow access to your microphone.  Check the address bar.', true);
      }
    
      function getUserMediaSuccess(stream) {
        var Stream = stream;
        var constraints = {
          audio: true,
          video: false,
        };
        navigator.mediaDevices.getUserMedia(constraints);
        navigator.mediaDevices.enumerateDevices()
          .then(function (devices) {
            var i = 1;
            var div = document.querySelector("#listmic"),
              frag = document.createDocumentFragment(),
              selectmic = document.createElement("select");
    
            while (div.firstChild) {
              div.removeChild(div.firstChild);
            }
            i = 1;
            selectmic.id = "selectmic";
            selectmic.style = "background-color: white;";
    
            devices.forEach(function (device) {
    
    
              if (device.kind === 'audioinput') {
    
                selectmic.options.add(new Option('Microfone: ' + (device.label ? device.label : (i)), device.deviceId));
                i++;
    
              }
            });
    
            frag.appendChild(selectmic);
    
            div.appendChild(frag);
    
          })
          .catch(function (err) {
            console.log(err.name + ": " + err.message);
          });
      }
    
      function setCallSessionStatus(status) {
        $('#txtCallStatus').html(status);
      }
    
      function setStatus(status) {
        $("#txtRegStatus").html('<i class="fa fa-signal"></i> ' + status);
      }
    
      function logCall(session, status) {
        var log = {
            clid: session.request.from.uri.user,
            uri: session.request.to.uri.user,
            id: session.id,
            time: new Date().getTime()
          },
          calllog = JSON.parse(localStorage.getItem('sipCalls'));
    
        if (!calllog) {
          calllog = {};
        }
        if (!calllog.hasOwnProperty(session.id)) {
          calllog[log.id] = {
            id: log.id,
            clid: log.clid,
            uri: log.uri,
            start: log.time,
            flow: session.direction
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
        logShow();
      }
    
      function logItem(item) {
        var callActive = (item.status !== 'ended' && item.status !== 'missed'),
          callLength = (item.status !== 'ended') ? '<span id="' + item.id + '"></span>' : moment.duration(item.stop - item.start).humanize(),
          callClass = '',
          callIcon, i;
        switch (item.status) {
          case 'ringing':
            callClass = 'list-group-item-success';
            callIcon = 'fa-bell';
            break;
          case 'missed':
            callClass = 'list-group-item-danger';
            if (item.flow === "incoming") {
              callIcon = 'fa-chevron-left';
            }
            if (item.flow === "outgoing") {
              callIcon = 'fa-chevron-right';
            }
            break;
          case 'holding':
            callClass = 'list-group-item-warning';
            callIcon = 'fa-pause';
            break;
          case 'answered':
          case 'resumed':
            callClass = 'list-group-item-info';
            callIcon = 'fa-phone-square';
            break;
          case 'ended':
            if (item.flow === "incoming") {
              callIcon = 'fa-chevron-left';
            }
            if (item.flow === "outgoing") {
              callIcon = 'fa-chevron-right';
            }
            break;
        }
        i = '<div class="list-group-item sip-logitem clearfix ' + callClass + '" data-uri="' + item.uri + '" data-session="' + item.id + '" title="Dois cliques para discar">';
        i += '<div class="clearfix"><div class="pull-left">';
        i += '<i class="fa fa-fw ' + callIcon + ' fa-fw"></i> <strong>' + formatPhone(item.uri) + '</strong><br><small>' + moment(item.start).format('DD/MM hh:mm:ss a') + '</small>';
        i += '</div>';
        i += '<div class="pull-right text-right"><small>' + callLength + '</small></div></div>';
        if (callActive) {
          i += '<div class="btn-group btn-group-sm, pull-right">';
          if (item.status === 'ringing' && item.flow === 'incoming') {
            i += '<button class="btn btn-sm btn-success btnCall" title="Discar"><i class="fa fa-phone"></i></button>';
          } else {
            i += '<button class="btn btn-sm btn-primary btnHoldResume" title="Espera"><i class="fa fa-pause"></i></button>';
            i += '<button class="btn btn-sm btn-info btnTransfer" title="Transferir"><i class="fa fa-random"></i></button>';
            i += '<button class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#mdlTransfer" title="Assistida"><i class="fa fa-exchange"></i></button>';
            i += '<button class="btn btn-sm btn-warning btnMute" title="Mudo"><i class="fa fa-fw fa-microphone"></i></button>';
          }
          i += '<button class="btn btn-sm btn-danger btnHangUp" title="Desligar"><i class="fa fa-stop"></i></button>';
          i += '</div>';
        }
        i += '</div>';
        $('#sip-logitems').append(i);
        if (item.status === 'answered') {
          var tEle = document.getElementById(item.id);
          callTimers[item.id] = new Stopwatch(tEle);
          callTimers[item.id].start();
        }
        if (callActive && item.status !== 'ringing') {
          callTimers[item.id].start({
            startTime: item.start
          });
        }
        $('#sip-logitems').scrollTop(0);
      }
    
      function logShow() {
        var calllog = JSON.parse(localStorage.getItem('sipCalls')),
          x = [];
        if (calllog !== null) {
          $('#sip-splash').addClass('hide');
          $('#sip-log').removeClass('hide');
          $('#sip-logitems').empty();
          $.each(calllog, function (k, v) {
            x.push(v);
          });
          // sort descending
          x.sort(function (a, b) {
            return b.start - a.start;
          });
          $.each(x, function (k, v) {
            logItem(v);
          });
        } else {
          $('#sip-splash').removeClass('hide');
          $('#sip-log').addClass('hide');
        }
      }
    
      function logClear() {
        localStorage.removeItem('sipCalls');
        logShow();
      }
    
      function sipCall(target, homeCountryId) {
        homeCountryId = "1";
        makeCall(target, homeCountryId);
    
    
        outboundCall = true;
        homeCountryId =
          homeCountryId ||
          (extension &&
            extension.regionalSettings &&
            extension.regionalSettings.homeCountry &&
            extension.regionalSettings.homeCountry.id) ||
          null;
    
        var session = webPhone.userAgent.invite(target, {
          fromNumber: username,
          homeCountryId
        });
    
        onAccepted(session);
    
    
      }
    
      function sipHangUp(session) {
    
        var x = document.getElementsByClassName("btnHangUp");
        console.log(x);
    
      }
    
      function setStatus(status) {
        $("#txtRegStatus").html('<i class="fa fa-signal"></i> ' + status);
      }
    
      /*  function sipSendDTMF(digit) {
          try {
            dtmfTone.play();
          }
          catch (e) {}
          var a = callActiveID;
          if (a) {
            var session = Sessions[a];
            session.dtmf(digit);
          }
        }*/
    
      function phoneCallButtonPressed(session) {
        var target = $("#numDisplay").val();
        var homeCountryId = "1";
        if (!session) {
          $("#numDisplay").val("");
          makeCall(target, homeCountryId);
        } else if (session.accept && !session.startTime) {
    
        }
      }
    
      function phoneMuteButtonPressed(session) {
        var session = Sessions[session];
        if (!session.isMuted) {
          session.mute();
        } else {
          session.unmute();
        }
      }
    
      function phoneHoldButtonPressed(session) {
        var session = Sessions[session];
        if (session.isOnHold().local === true) {
          session.unhold();
        } else {
          session.hold();
        }
      }
    
      /* function setTimeout() {
         logShow();
       }*/
    
      function Stopwatch(elem, options) {
        // private functions
        function createTimer() {
          return document.createElement("span");
        }
        var timer = createTimer(),
          offset, clock, interval;
        // default options
        options = options || {};
        options.delay = options.delay || 1000;
        options.startTime = options.startTime || Date.now();
        // append elements
        elem.appendChild(timer);
    
        function start() {
          if (!interval) {
            offset = options.startTime;
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
            d = now - offset;
          offset = now;
          return d;
        }
        // initialize
        reset();
        // public API
        this.start = start; //function() { start; }
        this.stop = stop; //function() { stop; }
      }
    
      function notifyMe(msg) {
    
        if (Notification.permission === "granted") {
          console.log(msg);
          let img = '/themes/default/images/logo_side_contracted.png';
          let notification = new Notification('Flux', {
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
    
      }
    
      function send_agent_cmd(url) {
        if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
          xmlhttp = new XMLHttpRequest();
        } else { // code for IE6, IE5
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.open("GET", url, false);
        xmlhttp.send(null);
        document.getElementById('cmd_reponse_agent').innerHTML = xmlhttp.responseText;
      }
    
      function fluxStart() {
        var user = JSON.parse(localStorage.getItem('SIPCreds'));
        var data = JSON.parse(localStorage.getItem('User'));
        var sipD = JSON.parse(localStorage.getItem('sipData'));
        localStorage.setItem('windowUser', windowUser);
        //var user_block = JSON.parse(localStorage.getItem('Block'));
    
        console.log('Janela User ' + windowUser);
        
        sipInfo = data[0].sipInfo[0] || data.sipInfo;
        //block = user_block.regBlock;
        //console.log('Janela Block ' + block);
    
        sessionStorage.setItem('session-phone', sipInfo.appKey);
        moment.locale('pt-br');
    
        var remoteVideoElement = document.getElementById('remoteVideo');
        var localVideoElement = document.getElementById('localVideo');
        fluxSip = {
    
          remoteVideoElement: document.getElementById('remoteVideo'),
          localVideoElement: document.getElementById('localVideo'),
          ringtone: document.getElementById('ringtone'),
          ringbacktone: document.getElementById('ringbacktone'),
          dtmfTone: document.getElementById('dtmfTone'),
    
          Sessions: [],
          callTimers: {},
          callActiveID: null,
          callVolume: 1,
          Stream: null,
    
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
    
          deleteAllCookies: function () {
            var cookies = document.cookie.split(";");
    
            for (var i = 0; i < cookies.length; i++) {
              var cookie = cookies[i];
              var eqPos = cookie.indexOf("=");
              var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
              document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
            }
          },
    
          openTab: function (evt, tabName) {
            // Declare all variables
            var i, tabcontent, tablinks;
    
            // Get all elements with class="tabcontent" and hide them
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
              tabcontent[i].style.display = "none";
            }
    
            // Get all elements with class="tablinks" and remove the class "active"
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
              tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
    
            // Show the current tab, and add an "active" class to the button that opened the tab
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
          },
    
          // Sound methods
          startRingTone: function () {
            try {
              fluxSip.ringtone.play();
            } catch (e) {}
          },
    
          stopRingTone: function () {
            try {
              fluxSip.ringtone.pause();
            } catch (e) {}
          },
    
    
          notifyMe: function (msg) {
    
            if (Notification.permission === "granted") {
              console.log(msg);
              let img = '/themes/default/images/logo_side_contracted.png';
              let notification = new Notification('Flux', {
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
    
    
          startRingbackTone: function () {
            try {
              fluxSip.ringbacktone.play();
            } catch (e) {}
          },
    
          stopRingbackTone: function () {
            try {
              fluxSip.ringbacktone.pause();
            } catch (e) {}
          },
    
          // Genereates a rendom string to ID a call
          getUniqueID: function () {
            return Math.random().toString(36).substr(2, 9);
          },
    
          newSession: function (session) {
    
            session.displayName = session.remoteIdentity.displayName || session.remoteIdentity.uri.user;
            session.fluxid = fluxSip.getUniqueID();
            cur_call = session;
    
            var status;
            var txt = "---";
    
            if (session.direction === 'incoming') {
    
              fluxSip.notifyMe("Chamada de: " + session.displayName);
    
              status = "Chamada de: " + session.displayName;
              console.log('Incoming');
              fluxSip.startRingTone();
    
              onInvite(session);
    
            } 
            else if (session.direction === 'conference') {
              status = "Criando Sala";
              //fluxSip.startRingbackTone();
            } 
            else {
              status = "Chamando: " + session.displayName;
              session.direction === 'outgoing';
              //fluxSip.startRingbackTone();
            }
    
            fluxSip.logCall(session, 'ringing');
    
            fluxSip.setCallSessionStatus(status);
    
            // EVENT CALLBACKS
    
    
            var remoteVideo = document.getElementById('remoteVideo');
            var localVideo = document.getElementById('localVideo');
    
    
            session.on('progress', function (e) {
              if (e.direction === 'outgoing') {
                fluxSip.setCallSessionStatus('Chamando');
                fluxSip.logCall(session, 'ringing');
                //fluxSip.startRingbackTone();
    
                console.log('Chamando');
              }
            });
    
            session.on('connecting', function (e) {
              if (e.direction === 'outgoing') {
                fluxSip.setCallSessionStatus('Chamando');
                fluxSip.logCall(session, 'ringing');
                //fluxSip.startRingbackTone();
                console.log('Tocando');
              }
            });
    
    
            session.on('accepted', function (e) {
              // If there is another active call, hold it
              if (fluxSip.callActiveID && fluxSip.callActiveID !== session.fluxid) {
                fluxSip.phoneHoldButtonPressed(fluxSip.callActiveID);
                console.log('Aceita');
              }
    
    
              //fluxSip.stopRingbackTone();
              fluxSip.stopRingTone();
              fluxSip.setCallSessionStatus('Conversando');
              fluxSip.logCall(session, 'answered');
              fluxSip.callActiveID = session.fluxid;
//              state = 'In a call';
//              send_agent_cmd('/app/basic_operator_panel/index.php?state='+state+'');
              console.log('Atendida Conversando');
    
            });
    
            session.on('hold', function (e) {
              fluxSip.callActiveID = null;
              fluxSip.logCall(session, 'hold');
              fluxSip.setCallSessionStatus("Em espera");
              console.log('Espera');
            });
    
            session.on('unhold', function (e) {
              fluxSip.logCall(session, 'resumed');
              fluxSip.callActiveID = session.fluxid;
              fluxSip.setCallSessionStatus("Falando");
              console.log('Saida Espera');
            });
    
            session.on('muted', function (e) {
              fluxSip.Sessions[session.fluxid].isMuted = true;
              fluxSip.setCallSessionStatus("Mudo");
              console.log('Mudo');
            });
    
            session.on('unmuted', function (e) {
              fluxSip.Sessions[session.fluxid].isMuted = false;
              fluxSip.setCallSessionStatus("Falando");
              console.log('Saida Mudo');
            });
    
    
            session.on('cancel', function (e) {
              fluxSip.stopRingTone();
    
              //fluxSip.stopRingbackTone();
              fluxSip.setCallSessionStatus("Cancelada");
              console.log('Cancelada');
              cur_call = null;
              if (this.direction === 'outgoing') {
                fluxSip.callActiveID = null;
                session = null;
                fluxSip.logCall(this, 'ended');
              }
            });
    
            session.on('bye', function (e) {
              fluxSip.stopRingTone();
              //fluxSip.stopRingbackTone();
              fluxSip.setCallSessionStatus("");
              fluxSip.logCall(session, 'ended');
              fluxSip.callActiveID = null;
              session = null;
              cur_call = null;
              console.log('Finalizada');
              fluxSip.setCallSessionStatus("");
              state = 'Waiting';
//              send_agent_cmd('/app/basic_operator_panel/index.php?state='+state+'');
              //fluxPhone.userAgent.register();
    
            });
    
            session.on('failed', function (e) {
              fluxSip.stopRingTone();
              //fluxSip.stopRingbackTone();
              cur_call = null;
              fluxSip.setCallSessionStatus("");
              console.log('Falha');
            });
    
            session.on('rejected', function (e) {
              fluxSip.stopRingTone();
              //fluxSip.stopRingbackTone();
              cur_call = null;
              fluxSip.setCallSessionStatus('Rejeitada');
              fluxSip.callActiveID = null;
              fluxSip.logCall(this, 'ended');
              session = null;
              console.log('Rejeitada');
            });
    
    
            fluxSip.Sessions[session.fluxid] = session;
    
    
          },
    
          // getUser media request refused or device was not present
          getUserMediaFailure: function (e) {
            window.console.error('getUserMedia failed:', e);
            fluxSip.setError(true, 'Erro de dispositivo.', 'Você deve permitir o acesso ao seu microfone.', true);
          },
    
          getUserMediaSuccess: function (stream) {
            fluxSip.Stream = stream;
          },
    
          /**
           * sets the ui call status field
           *
           * @param {string} status
           */
          setCallSessionStatus: function (status) {
            $('#txtCallStatus').html(status);
          },
    
          /**
           * sets the ui connection status field
           *
           * @param {string} status
           */
          setStatus: function (status) {
            $("#txtRegStatus").html('<i class="fa fa-signal"></i> ' + status);
          },
    
          setUser: function (status) {
            $("#txtUserStatus").html('<i class="fa fa-phone"></i> ' + status);
          },
    
          setAgentStatus: function (status) {
            localStorage.setItem('AgentStatus', status);
            $("#txtAgentStatus").html('' + status);
          },
    
          /**
           * logs a call to localstorage
           *
           * @param  {object} session
           * @param  {string} status Enum 'ringing', 'answered', 'ended', 'holding', 'resumed'
           */
          logCall: function (session, status) {
    
            var log = {
                clid: session.displayName,
                uri: session.remoteIdentity.uri.toString(),
                id: session.fluxid,
                time: new Date().getTime()
              },
              calllog = JSON.parse(localStorage.getItem('sipCalls'));
    
            if (!calllog) {
              calllog = {};
            }
    
            if (!calllog.hasOwnProperty(session.fluxid)) {
              calllog[log.id] = {
                id: log.id,
                clid: log.clid,
                uri: log.uri,
                start: log.time,
                flow: session.direction
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
            fluxSip.logShow();
          },
    
          /**
           * adds a ui item to the call log
           *
           * @param  {object} item log item
           */
          logItem: function (item) {
    
            var callActive = (item.status !== 'ended' && item.status !== 'missed'),
              callLength = (item.status !== 'ended') ? '<span id="' + item.id + '"></span>' : moment.duration(item.stop - item.start).humanize(),
              callClass = '',
              callIcon,
              i;
    
            switch (item.status) {
              case 'ringing':
                callClass = 'list-group-item-success';
                callIcon = 'fa-bell';
                break;
    
              case 'missed':
                callClass = 'list-group-item-danger';
                if (item.flow === "incoming") {
                  callIcon = 'fa-chevron-left';
                }
                if (item.flow === "outgoing") {
                  callIcon = 'fa-chevron-right';
                }
                break;
    
              case 'holding':
                callClass = 'list-group-item-warning';
                callIcon = 'fa-pause';
                break;
    
              case 'answered':
              case 'resumed':
                callClass = 'list-group-item-info';
                callIcon = 'fa-phone-square';
                break;
    
              case 'ended':
                if (item.flow === "incoming") {
                  callIcon = 'fa-chevron-left';
                }
                if (item.flow === "outgoing") {
                  callIcon = 'fa-chevron-right';
                }
                break;
            }
    
    
            i = '<div class="list-group-item sip-logitem clearfix ' + callClass + '" data-uri="' + item.uri + '" data-session="' + item.id + '" title="Clique duas vezes para discar">';
            i += '<div class="clearfix"><div class="pull-left">';
            i += '<i class="fa fa-fw ' + callIcon + ' fa-fw"></i> <strong>' + fluxSip.formatPhone(item.uri) + '</strong><br><small>' + moment(item.start).format('DD/MM HH:mm') + '</small>';
            i += '</div>';
            i += '<div class="pull-right text-right">' + callLength + '</div></div>';
    
            if (callActive) {
              i += '<div class="btn-group btn-group-xs pull-right">';
              if (item.status === 'ringing' && item.flow === 'incoming') {
                i += '<button class="btn btn-sm btn-success btnCall" title="Chamada"><i class="fa fa-phone" style="color: #fff;"></i></button>';
              } else {
                i += '<button class="btn btn-sm btn-primary btnHoldResume" title="Espera"><i class="fa fa-pause" style="color: #fff;"></i></button>';
                i += '<button class="btn btn-sm btn-info btnTransfer" title="Transferir" data-toggle="modal" data-target="#mdlTransfer" data-whatever="transfer"><i class="fa fa-random" style="color: #fff;"></i></button>';
                i += '<button class="btn btn-sm btn-warning  btnMute" title="Mudo"><i class="fa fa-fw fa-microphone" style="color: #fff;"></i></button>';
              }
              i += '<button class="btn btn-sm btn-danger btnHangUp" title="Desligar"><i class="fa fa-stop" style="color: #fff;"></i></button>';
              i += '</div>';
            }
            i += '</div>';
            $('#sip-logitems').append(i);
    
    
            // Start call timer on answer
            if (item.status === 'answered') {
              var tEle = document.getElementById(item.id);
              fluxSip.callTimers[item.id] = new Stopwatch(tEle);
              fluxSip.callTimers[item.id].start();
              fluxSip.stopRingTone();
              //fluxSip.stopRingbackTone();
            }
    
            if (callActive && item.status !== 'ringing') {
              fluxSip.callTimers[item.id].start({
                startTime: item.start
              });
            }
    
            $('#sip-logitems').scrollTop(0);
          },
    
          /**
           * updates the call log ui
           */
          logShow: function () {
    
            var calllog = JSON.parse(localStorage.getItem('sipCalls')),
              x = [];
    
            if (calllog !== null) {
    
              //              $('#containerPhone').addClass('splash_hide');
              //              $('#containerHistory').removeClass('splash_hide');
    
              // empty existing logs
              $('#sip-logitems').empty();
    
              // JS doesn't guarantee property order so
              // create an array with the start time as
              // the key and sort by that.
    
              // Add start time to array
              $.each(calllog, function (k, v) {
                x.push(v);
              });
    
              // sort descending
              x.sort(function (a, b) {
                return b.start - a.start;
              });
    
              $.each(x, function (k, v) {
                fluxSip.logItem(v);
              });
    
            } else {
              $('#sip-splash').removeClass('hide');
              $('#sip-log').addClass('hide');
            }
          },
    
          /**
           * removes log items from localstorage and updates the UI
           */
          logClear: function () {
    
            localStorage.removeItem('sipCalls');
            //fluxSip.logShow();
          },
    
    
          phoneHistoryButtonPressed: function () {
            var $modal = cloneTemplate($historyTemplate).modal();
    
          },
    
          sipCall: function (target) {
    
            try {
              var session = fluxPhone.userAgent.invite(target, {
    
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
                    stream: fluxSip.Stream
                  }
    
                }
              });
              //outboundCall = true;
              session.direction = 'outgoing';
              //onAccepted(session);
              fluxSip.newSession(session);
    
    
            } catch (e) {
              throw (e);
            }
          },
    
          sipCallNew: function (target) {
    
            try {
              var session = fluxPhone.userAgent.invite(target, {
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
                    stream: fluxSip.Stream
                  }
    
                }
              });
              session.direction = 'outgoing';
              fluxSip.newSession(s);
    
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
                remoteVideo.srcObject = remoteStream;
                remoteVideo.play();
              });
    
    
            } catch (e) {
              throw (e);
            }
          },
    
    
          captureActiveCallInfo: function (session) {
    
            const direction = outboundCall ? 'outgoing' : 'incoming';
            var activeCallInfo = activeInfoTemplate();
            activeCallInfo.from = session.request.from.uri.user;
            activeCallInfo.fname = session.request.from.friendlyName;
            activeCallInfo.tname = session.request.to.friendlyName;
            activeCallInfo.to = session.request.to.uri.user;
            activeCallInfo.direction = direction;
            activeCallInfo.id = session.id;
            activeCallInfo.sipData.fromTag = session.dialog.id.remoteTag;
            activeCallInfo.sipData.toTag = session.dialog.id.localTag;
            activeCallInfo.start = session.startTime;
            if (!localStorage.getItem('activeCallInfo')) {
              localStorage.setItem('activeCallInfo', JSON.stringify(activeCallInfo));
    
    
              console.log('ACTIVE CALL INFO: ' + JSON.stringify(activeCallInfo));
              console.log('FROM: ' + activeCallInfo.from);
              console.log('FROM NAME: ' + activeCallInfo.fname);
              console.log('TO: ' + activeCallInfo.to);
              console.log('TO NAME: ' + activeCallInfo.tname);
    
    
              console.log('DIRECTION: ' + activeCallInfo.direction);
              console.log('ID: ' + activeCallInfo.id);
              console.log('FROM TAG: ' + activeCallInfo.sipData.fromTag);
              console.log('TO TAG: ' + activeCallInfo.sipData.toTag);
              console.log('START TIME: ' + activeCallInfo.start);
              //logCall(session, 'answered');
    
    
            }
          },
    
    
          onAccepted: function (session) {
    
            console.log('EVENT: Accepted', session.request);
            console.log('To', session.request.to.friendlyName, session.request.to.friendlyName);
            console.log('From', session.request.from.displayName, session.request.from.friendlyName);
            var $modal = cloneTemplate($transferTemplate).modal();
    
    
            var $info = $modal.find('.info').eq(0);
            var $dtmf = $modal.find('input[name=dtmf]').eq(0);
            var $transfer = $modal.find('input[name=transfer]').eq(0);
            var $flip = $modal.find('input[name=flip]').eq(0);
            var $conference = $modal.find('input[name=conference]').eq(0);
    
            var interval = setInterval(function () {
              var time = session.startTime ? Math.round((Date.now() - session.startTime) / 1000) + 's' : 'Ringing';
    
              $info.text('time: ' + time + '\n' + 'startTime: ' + JSON.stringify(session.startTime, null, 2) + '\n');
            }, 1000);
    
            function close() {
              clearInterval(interval);
              $modal.modal('hide');
            }
    
            $modal.find('.increase-volume').on('click', function () {
              session.ua.audioHelper.setVolume(
                (session.ua.audioHelper.volume !== null ? session.ua.audioHelper.volume : 0.5) + 0.1
              );
            });
    
            $modal.find('.decrease-volume').on('click', function () {
              session.ua.audioHelper.setVolume(
                (session.ua.audioHelper.volume !== null ? session.ua.audioHelper.volume : 0.5) - 0.1
              );
            });
    
            $modal.find('.mute').on('click', function () {
              session.mute();
            });
    
            $modal.find('.unmute').on('click', function () {
              session.unmute();
            });
    
            $modal.find('.hold').on('click', function () {
              session
                .hold()
                .then(function () {
                  console.log('Holding');
                })
                .catch(function (e) {
                  console.error('Holding failed', e.stack || e);
                });
            });
    
            $modal.find('.unhold').on('click', function () {
              session
                .unhold()
                .then(function () {
                  console.log('UnHolding');
                })
                .catch(function (e) {
                  console.error('UnHolding failed', e.stack || e);
                });
            });
            $modal.find('.startRecord').on('click', function () {
              session
                .startRecord()
                .then(function () {
                  console.log('Recording Started');
                })
                .catch(function (e) {
                  console.error('Recording Start failed', e.stack || e);
                });
            });
    
            $modal.find('.stopRecord').on('click', function () {
              session
                .stopRecord()
                .then(function () {
                  console.log('Recording Stopped');
                })
                .catch(function (e) {
                  console.error('Recording Stop failed', e.stack || e);
                });
            });
    
            $modal.find('.park').on('click', function () {
              session
                .park()
                .then(function () {
                  console.log('Parked');
                })
                .catch(function (e) {
                  console.error('Park failed', e.stack || e);
                });
            });
    
            $modal.find('.transfer-form').on('submit', function (e) {
              e.preventDefault();
              e.stopPropagation();
              session
                .transfer($transfer.val().trim())
                .then(function () {
                  console.log('Transfered');
                })
                .catch(function (e) {
                  console.error('Transfer failed', e.stack || e);
                });
            });
    
            $modal.find('.transfer-form .warm').on('click', function (e) {
              e.preventDefault();
              e.stopPropagation();
              session.hold().then(function () {
                console.log('Chamada Na Espera');
    
                var newSession = fluxPhone.userAgent.invite($transfer.val().trim(), {
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
                      stream: fluxSip.Stream
                    }
    
                  }
                });
                newSession.once('accepted', function () {
                  console.log('New call initated. Click Complete to complete the transfer');
                  $modal.find('.transfer-form button.complete').on('click', function (e) {
                    session
                      .warmTransfer(newSession)
                      .then(function () {
                        console.log('Warm transfer completed');
                      })
                      .catch(function (e) {
                        console.error('Transfer failed', e.stack || e);
                      });
                  });
                });
              });
            });
    
            $modal.find('.flip-form').on('submit', function (e) {
              e.preventDefault();
              e.stopPropagation();
              session
                .flip($flip.val().trim())
                .then(function () {
                  console.log('Flipped');
                })
                .catch(function (e) {
                  console.error('Flip failed', e.stack || e);
                });
              $flip.val('');
            });
    
            $modal.find('.dtmf-form').on('submit', function (e) {
              e.preventDefault();
              e.stopPropagation();
              session.dtmf($dtmf.val().trim());
              $dtmf.val('');
            });
    
            var $startConfButton = $modal.find('.startConf');
    
            $startConfButton.on('click', function (e) {
              initConference();
            });
    
            $modal.find('.hangup').on('click', function () {
              session.terminate();
              makeCallForm();
            });
    
            session.on('accepted', function () {
              console.log('Event: Accepted');
              fluxSip.captureActiveCallInfo(session);
            });
            session.on('progress', function () {
              console.log('Event: Progress');
            });
            session.on('rejected', function () {
              console.log('Event: Rejected');
              close();
            });
            session.on('failed', function () {
              console.log('Event: Failed', arguments);
              close();
            });
            session.on('terminated', function () {
              console.log('Event: Terminated');
              localStorage.setItem('activeCallInfo', '');
              close();
            });
            session.on('cancel', function () {
              console.log('Event: Cancel');
              close();
            });
            session.on('refer', function () {
              console.log('Event: Refer');
              close();
            });
            session.on('replaced', function (newSession) {
              console.log('Event: Replaced: old session', session, 'has been replaced with', newSession);
              close();
              onAccepted(newSession);
            });
            session.on('dtmf', function () {
              console.log('Event: DTMF');
            });
            session.on('muted', function () {
              console.log('Event: Muted');
            });
            session.on('unmuted', function () {
              console.log('Event: Unmuted');
            });
            session.on('connecting', function () {
              console.log('Event: Connecting');
            });
            session.on('bye', function () {
              console.log('Event: Bye');
              close();
            });
          },
    
    
          sipTransfer: function (session) {
            var $modal = cloneTemplate($transferTemplate).modal();
            $('#numTransfer').focus();
            var $transfer = $modal.find('input[name=transfer]').eq(0);
            var interval = setInterval(function () {
              var time = session.startTime ? Math.round((Date.now() - session.startTime) / 1000) + 's' : 'Ringing';
    
              // console.log('time: ' + time + '\n' + 'startTime: ' + JSON.stringify(session.startTime, null, 2) + '\n');
            }, 1000);
            var session = fluxSip.Sessions[session];
    
            function close() {
              clearInterval(interval);
              $modal.modal('hide');
            }
    
            $modal.find('.transfer-form').on('submit', function (e) {
              session
                .transfer($transfer.val().trim())
                .then(function () {
                  console.log('Transfered');
                  $modal.modal('hide');
                })
                .catch(function (e) {
                  console.error('Transfer failed', e.stack || e);
                });
            });
    
            $modal.find('.transfer-form button.warm').on('click', function (e) {
              e.preventDefault();
              e.stopPropagation();
              session.hold().then(function () {
                console.log('Chamada em Espera');
                fluxSip.logCall(session, 'holding');
                fluxSip.setCallSessionStatus("Em espera");
                $modal.find('.transfer-form button.warm').css('display', 'none');
                $modal.find('.transfer-form button.transferCancel').css('display', '');
                var newSession = session.ua.invite($transfer.val().trim());
                $modal.find('.transfer-form .transferCancel').on('click', function () {
                  newSession.terminate();
                });
                newSession.once('accepted', function () {
                  console.log('Nova Chamada Iniciada.');
                  $modal.find('.transfer-form button.complete').on('click', function (e) {
                    session
                      .warmTransfer(newSession)
                      .then(function () {
                        console.log('Transferencia Concluida');
                        //$modal.modal('hide');
                      })
                      .catch(function (e) {
                        console.error('Transfer failed', e.stack || e);
    
                      });
                  });
    
                });
    
              });
            });
    
            session.on('accepted', function () {
              console.log('Event: Accepted');
              fluxSip.captureActiveCallInfo(session);
            });
            session.on('ringing', function () {
              console.log('Event: Ringing');
              // fluxSip.captureActiveCallInfo(session);
            });
            session.on('trying', function () {
              console.log('Event: Trying');
              // fluxSip.captureActiveCallInfo(session);
            });
            session.on('progress', function () {
              console.log('Event: Progress');
            });
            session.on('rejected', function () {
              console.log('Event: Rejected');
              close();
            });
            session.on('failed', function () {
              console.log('Event: Failed', arguments);
              close();
            });
            session.on('terminated', function () {
              console.log('Event: Terminated');
              localStorage.setItem('activeCallInfo', '');
              close();
            });
            session.on('cancel', function () {
              console.log('Event: Cancel');
              close();
            });
            session.on('refer', function () {
              console.log('Event: Refer');
              close();
            });
            session.on('referAccepted', function () {
              console.log('Event: Refer Accepted');
              close();
            });
            session.on('replaced', function (newSession) {
              console.log('Event: Replaced: old session', session, 'has been replaced with', newSession);
              close();
              onAccepted(newSession);
            });
            session.on('dtmf', function () {
              console.log('Event: DTMF');
            });
            session.on('muted', function () {
              console.log('Event: Muted');
            });
            session.on('unmuted', function () {
              console.log('Event: Unmuted');
            });
            session.on('connecting', function () {
              console.log('Event: Connecting');
            });
            session.on('notify', function () {
              console.log('Event: Notify');
    
            });
    
            session.on('bye', function () {
              console.log('Event: Bye');
              close();
            });
          },
    
          sipHangUp: function (session) {
    
            var session = fluxSip.Sessions[session];
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
    
          sipSendDTMF: function (digit) {
    
            try {
              fluxSip.dtmfTone.play();
            } catch (e) {}
    
            var a = fluxSip.callActiveID;
            if (a) {
              var session = fluxSip.Sessions[a];
              session.dtmf(digit);
            }
          },
    
          phoneCallButtonPressed: function (session) {
    
            var session = fluxSip.Sessions[session],
              target = $("#numDisplay").val();
    
            if (!session) {
    
              $("#numDisplay").val("");
              makeCall(target, "1");
              //fluxSip.sipCall(target);
    
            } else if (session.accept && !session.startTime) {
    
              session.accept({
                media: {
                  remote: fluxSip.remoteVideoElement,
                  local: fluxSip.localVideoElement
                }
              });
            }
          },
    
          phoneCallNewButtonPressed: function (session) {
    
            var session = fluxSip.Sessions[session],
              target = $("#numDisplay").val();
    
            if (!session) {
    
              $("#numDisplay").val("");
              fluxSip.sipCall(target);
    
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
                    stream: fluxSip.Stream
                  }
    
                }
              });
            }
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
    
    
          phoneMuteButtonPressed: function (session) {
    
            var session = fluxSip.Sessions[session];
    
            if (!session.isMuted) {
              session.mute();
            } else {
              session.unmute();
            }
          },
    
          phoneHoldButtonPressed: function (session) {
    
            var session = fluxSip.Sessions[session];
            var direction = session.sessionDescriptionHandler.getDirection();
    
            if (direction === 'sendrecv') {
              console.log('Chamada não está em espera');
              session.hold();
              fluxSip.logCall(session, 'holding');
              fluxSip.setCallSessionStatus("Em espera");
            } else {
              session.unhold();
              fluxSip.logCall(session, 'resumed');
              fluxSip.callActiveID = session.fluxid;
              fluxSip.setCallSessionStatus("Conversando");
              console.log('Saida Espera');
            }
          },
    
    
          phoneCloseWindow: function () {
            fluxPhone.userAgent.stop();
            window.close();
          },
    
          phoneLogoutButtonPressed: function () {
    
            var closeEditorWarning = function () {
              return 'Se você fechar esta janela, não poderá fazer ou receber chamadas de seu navegador.';
            };
    
            var closePhone = function () {
              // stop the phone on unload
              localStorage.clear();
              sessionStorage.clear();
              fluxSip.deleteAllCookies();
              fluxSip.phoneCloseWindow();
              location.reload();
            };
            closePhone();
    
            window.onbeforeunload = closeEditorWarning;
            window.onunload = closePhone;
    
    
          },
    
    
          phoneContactsButtonPressed: function () {
            $('#sip-splash').removeClass('splash_hide');
    
          },
    
          setError: function (err, title, msg, closable) {
            var $modal = cloneTemplate($errorTemplate).modal();
            var $mdlError = $modal.find('#mdlError').eq(0);
            var $mdlHeader = $modal.find('#mdlErrorHeader').eq(0);
            var $mdlTitle = $modal.find('#mdlErrorTitle').eq(0);
            var $mdlBody = $modal.find('#mdlErrorBody').eq(0);
            var $mdlMsg = $modal.find('#mdlErrorMsg').eq(0);
            // Show modal if err = true
            if (err === true) {
              $mdlMsg.html(msg);
              $mdlTitle.html(title);
              //$modal('show');
    
              if (closable) {
                var b = '<button type="button" class="close" data-dismiss="modal">×</button>';
                $mdlHeader.find('button').remove();
                $mdlHeader.prepend(b);
                $mdlTitle.html(title);
                $mdlMsg.html(msg);
              } else {
                $mdlHeader.find('button').remove();
                $mdlTitle.html(title);
                $mdlMsg.html(msg);
              }
              $('#numDisplay').prop('disabled', 'disabled');
            } else {
              $('#numDisplay').removeProp('disabled');
              $error('hide');
            }
          },
    
    
          phoneLoginAgent: function (agentlogin, user, closable) {
            var $modal = cloneTemplate($agentTemplate).modal();
            var $user_status = $modal.find('input[name=user_status]').eq(0);
            var $select_user_status = $modal.find('select[name=select_user_status]').eq(0);
            console.log('Agent Login:' + agentlogin);
            console.log('Agent User:' + user);
            console.log('Agent Close:' + closable);
            localStorage.removeItem('AgentStatus');
            $modal.find('.agent-form').on('submit', function (e) {
              $status = $select_user_status.val();
              console.log('Agent Status:' + $status);
             send_agent_cmd('/app/basic_operator_panel/index.php?status=' + $status + '&custom_status=' + $status);
              fluxSip.setAgentStatus($status);
              $modal.modal('hide');
            });
          },
    
          phoneConfig: function (agentlogin, user, closable) {
    
            var $modal = cloneTemplate($configTemplate).modal({
              backdrop: 'static'
            });
          },
    
    
          /**
           * Tests for a capable browser, return bool, and shows an
           * error modal on fail.
           */
          hasWebRTC: function () {
    
            if (navigator.webkitGetUserMedia) {
              return true;
            } else if (navigator.mozGetUserMedia) {
              return true;
            } else if (navigator.getUserMedia) {
              return true;
            } else {
              //fluxSip.setError(true, 'Navegador não suportado.', 'Seu navegador não oferece suporte aos recursos necessários para este telefone.');
              //window.console.error("WebRTC support not found");
              return true;
            }
          }
        };
    
        if (!fluxSip.hasWebRTC()) {
          return true;
        }
    
        /*  if (block === 'Down') {
          //fluxSip.setError(true, 'Websocket Desconectado.', 'Ocorreu um erro ao conectar ao websocket.');
          fluxSip.setError(true, 'Fora do horário de Login.', 'Login bloqueado. Consulte seu Administrador');
            window.console.error("Fora do horário de Login");
            return false;
          }*/
    
        fluxPhone = new WebPhone(data[0], {
          appKey: sipInfo.appKey,
          appUser: sipInfo.appUser,
          appAgent: sipInfo.appAgent,
          audioHelper: {
            enabled: true,
            incoming: '/app/flux_phone/assets/audio/mp3/ringtone_in.mp3'
            //outgoing: '/app/flux_phone/assets/audio/mp3/ringtone_in.mp3'
          },
          logLevel: 3,
          appName: 'FluxPhone',
          appVersion: '5.2.9',
          displayName: sipInfo.displayName,
          contactName: sipInfo.username,
          domain: sipInfo.domain,
          register: true,
          media: {
            remote: remoteVideoElement,
            local: localVideoElement
          },
          enableQos: false,
          enableMediaReportLogging: false,
          enableTurnServers: true,
          hackViaTcp: false,
          appSecret: sipInfo.appKey,
          hackIpInContact: true,
          enableDefaultModifiers: true,
          hackWssInTransport: true,
          stunServers: ['stun.l.google.com:19302'],
          turnServers: [{
            urls: 'turn:sbcdev4.flux.net.br:5349',
            username: 'fluxDev',
            credential: 'fluxDevTurn2010'
          }],
          iceTransportPolicy: "relay",
          iceCheckingTimeout: 500
        });
    
        fluxPhone.userAgent.audioHelper.loadAudio({
          incoming: '/app/flux_phone/assets/audio/mp3/ringtone_in.mp3'
          // outgoing: '/app/flux_phone/assets/audio/mp3/ringtone_in.mp3'
        });
        fluxPhone.userAgent.audioHelper.setVolume(1.0);
    
        fluxPhone.userAgent.on('connected', function (e) {
          fluxSip.setStatus("Conectado");
          fluxSip.setError(false)
        });
    
    
        fluxPhone.userAgent.on('disconnected', function (e) {
          fluxSip.setStatus("Desconectado");
    
          // disable phone
          //fluxSip.setError(true, 'Websocket Desconectado.', 'Ocorreu um erro ao conectar ao websocket.');
    
        });
    
        fluxPhone.userAgent.on('registered', function (e) {
          console.log('UA REGISTRADO');
          //  fluxSip.setError(true, 'Websocket Desconectado.', 'Ocorreu um erro ao conectar ao websocket.');
          if (Notification.permission === "granted") {
            $(".asknotificationpermission").hide();
          } else {
            $(".asknotificationpermission").show();
          }
          var closeEditorWarning = function () {
            return 'Se você fechar esta janela, não poderá fazer ou receber chamadas de seu navegador.';
          };
    
          var closePhone = function () {
            // stop the phone on unload
            //fluxPhone.userAgent.unregister();
            localStorage.removeItem('fluxPhone');
            localStorage.removeItem('User');
            localStorage.removeItem('SIPCreds');
            localStorage.removeItem('ipApiUser');
            //localStorage.removeItem('sipCalls');
            localStorage.removeItem('AgentStatus');
            localStorage.removeItem('flux-webPhone-uuid');
            fluxSip.deleteAllCookies();
          };
    
          window.onbeforeunload = closeEditorWarning;
          window.onunload = closePhone;
          localStorage.setItem('fluxPhone', 'true');
    
    
          fluxSip.setStatus("Registrado");
          fluxSip.setAgentStatus(user.Status);
          fluxSip.setUser('<i>' + user.User + '@' + user.Realm + '</i>');
    
    
        });
    
        /*       fluxPhone.userAgent.on('notify', function() {
               console.log('UA Message', arguments);
            });*/
    
        fluxPhone.userAgent.on('notify', fluxSip.handleNotify);
    
        fluxPhone.userAgent.transport.on('switchBackProxy', function () {
          console.log('switching back to primary outbound proxy');
          fluxPhone.userAgent.transport.reconnect(true);
        });
        fluxPhone.userAgent.transport.on('closed', function () {
          console.log('WebSocket closed.');
    
        });
        fluxPhone.userAgent.transport.on('transportError', function () {
          console.log('WebSocket transportError occured');
        });
        fluxPhone.userAgent.transport.on('wsConnectionError', function () {
          console.log('WebSocket wsConnectionError occured');
        });
    
        fluxPhone.userAgent.on('registrationFailed', function (e) {
          console.log(e)
          //fluxSip.setError(true, 'Erro no Registro.', 'Ocorreu um erro ao registrar seu ramal. Verifique suas configurações.',true);
          fluxSip.setStatus("Sem Registro");
        });
    
        fluxPhone.userAgent.on('unregistered', function (e) {
          // fluxSip.setError(true, 'Erro no Registro.', 'Ocorreu um erro ao registrar seu ramal. Aguarde enquanto tentamos conectá-lo novamente.',true);
          // fluxPhone.userAgent.unregister();
          fluxSip.setStatus("Sem Registro");
        });
    
        fluxPhone.userAgent.on('invite', handleInvite);
    
    
        /*fluxPhone.userAgent.on('invite', 
        handleInvite();
        function (incomingSession) {
           
        var session = incomingSession;
    
        session.direction = 'incoming';
        fluxSip.newSession(session);
    
    
        },);*/
    
        // Auto-focus number input on backspace.
        $('#sipPhone').keydown(function (event) {
          if (event.which === 8) {
            $('#numDisplay').focus();
          }
        });
    
        $('#sip-phone').keydown(function (event) {
          if (event.which === 122) {
            $('#numDisplay').focus();
            console.log('KEY PRESS');
            fluxSip.setError(true, 'Status', 'Escolha seu status de agente.', true);
          }
        });
    
    
        $('#numDisplay').keypress(function (e) {
          // Enter pressed? so Dial.
          if (e.which === 13) {
            fluxSip.phoneCallNewButtonPressed();
          }
        });
    
    
        $('#sip-phone').keypress(function (e) {
          // Enter pressed? so Dial.
          if (e.which === 27) {
            e.preventDefault();
            console.log(user.User + ' Button Pressed');
            var agentlogin = true;
            fluxSip.phoneLoginAgent(true, user.User, true);
            //   fluxSip.phoneLoginAgent(true, user.User, true);
            //fluxSip.phoneLoginAgent();
          }
        });
    
        $('#phoneIcon').click(function (event) {
          // Enter pressed? so Dial.
          event.preventDefault();
          fluxSip.phoneCallNewButtonPressed();
        });
    
        $('#defaultOpen').click(function (event) {
          // Enter pressed? so Dial.
          event.preventDefault();
        });
    
        $('.digit').click(function (event) {
          event.preventDefault();
          $('#numDisplay').focus();
          var num = $('#numDisplay').val(),
            dig = $(this).data('digit');
          $('#numDisplay').val(num + dig);
          fluxSip.sipSendDTMF(dig);
          return false;
        });
    
        $('#phoneUI .dropdown-menu').click(function (e) {
          e.preventDefault();
        });
    
        $('#phoneUI').delegate('.btnCall', 'click', function (event) {
          fluxSip.phoneCallNewButtonPressed();
          // to close the dropdown
          return true;
        });
    
        $('#phoneUI').delegate('.btnConference', 'click', function (event) {
          fluxSip.phoneConferenceButtonPressed();
          // to close the dropdown
          return true;
        });
    
        $('#phoneUI').delegate('.btnDnd', 'click', function (event) {
          if (isDnd) {
            isDnd = false;
            console.log("Status DND: " + isDnd + "");
            fluxSip.setStatus("Registrado");
          } else {
            isDnd = true;
            console.log("Status DND: " + isDnd + "");
            fluxSip.setStatus("Não Perturbe");
          }
          return true;
        });
    
        $('#phoneUI').delegate('.btnLogout', 'click', function (event) {
          //    fluxPhone.userAgent.unregister();
          fluxSip.phoneLogoutButtonPressed();
          // to close the dropdown
          return true;
        });
    
    
        $('#nav-items').delegate('.content_history', 'click', function (event) {
          console.log('History click');
          fluxSip.phoneHistoryButtonPressed();
          return true;
        });
    
        $('#nav-items').delegate('.content_contacts', 'click', function (event) {
          console.log('Contacts click');
          fluxSip.phoneContactsButtonPressed();
          return true;
        });
    
        $('#nav-items').delegate('.content_agent', 'click', function (event) {
          console.log('Agent click');
          fluxSip.phoneLoginAgent(true, user.User, true);
          return true;
        });

    
        $('#nav-items').delegate('.content_settings', 'click', function (event) {
          console.log('Settings click');
          fluxSip.phoneConfig(true, user.User, true);
          return true;
        });
        
        $('#phone-tabs').delegate('.ramal', 'click', function (event) {
          console.log('Ramal click');
          //                    fluxSip.open(true, user.User, true);
          fluxSip.openTab(event, 'Ramal');
          return true;
        });
    
        $('#phone-tabs').delegate('.ramal', 'click', function (event) {
          console.log('Ramal click');
          //                    fluxSip.open(true, user.User, true);
          fluxSip.openTab(event, 'Ramal');
          return true;
        });
    
    
        $('#fluxSettings').delegate('.content_settings', 'click', function (event) {
          alert('Settings click');
          fluxSip.phoneConfig(true, user.User, true);
          return true;
        });

        $('#nav-items').delegate('.content_log', 'click', function (event) {
          //alert('Log click');
          fluxSip.phoneConfig(true, user.User, true);
          return true;
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
                var notification = fluxSip.notifyMe("Permissão de notificação concedida!");
                $("#asknotificationpermission").hide();
              }
            });
          } else {
            alert(`Permission is ${Notification.permission}`);
          }
    
    
        });
    
        $('#sipLogClear').click(function (event) {
          event.preventDefault();
          fluxSip.logClear();
        });
    
        $('#sip-logitems').delegate('.sip-logitem .btnCall', 'click', function (event) {
          var session = $(this).closest('.sip-logitem').data('session');
          fluxSip.phoneCallNewButtonPressed(session);
          return false;
        });
    
        $('.tablinks').click(function (event) {
          event.preventDefault();
          fluxSip.openTab();
        });
    
        $('#sip-logitems').delegate('.sip-logitem .btnHoldResume', 'click', function (event) {
          var session = $(this).closest('.sip-logitem').data('session');
          fluxSip.phoneHoldButtonPressed(session);
          return false;
        });
    
        $('#sip-logitems').delegate('.sip-logitem .btnHangUp', 'click', function (event) {
          var session = $(this).closest('.sip-logitem').data('session');
          fluxSip.sipHangUp(session);
          return false;
        });
    
        $('#sip-logitems').delegate('.sip-logitem .btnTransfer', 'click', function (event) {
          var session = $(this).closest('.sip-logitem').data('session');
          console.log(session);
          fluxSip.sipTransfer(session);
          return false;
        });
    
        $('#sip-logitems').delegate('.sip-logitem .btnMute', 'click', function (event) {
          var session = $(this).closest('.sip-logitem').data('session');
          fluxSip.phoneMuteButtonPressed(session);
          return false;
        });
    
        $('#sip-logitems').delegate('.sip-logitem', 'dblclick', function (event) {
          event.preventDefault();
    
          var uri = $(this).data('uri');
          $('#numDisplay').val(uri);
          fluxSip.phoneCallNewButtonPressed();
        });
    
        $('#sldVolume').on('change', function () {
    
          var v = $(this).val() / 100,
            // player = $('audio').get()[0],
            btn = $('#btnVol'),
            icon = $('#btnVol').find('i'),
            active = fluxSip.callActiveID;
    
          // Set the object and media stream volumes
          if (fluxSip.Sessions[active]) {
            fluxSip.Sessions[active].player.volume = v;
            fluxSip.callVolume = v;
          }
    
          // Set the others
          $('audio').each(function () {
            $(this).get()[0].volume = v;
          });
    
          if (v < 0.1) {
            btn.removeClass(function (index, css) {
                return (css.match(/(^|\s)btn\S+/g) || []).join(' ');
              })
              .addClass('btn btn-sm btn-danger');
            icon.removeClass().addClass('fa fa-fw fa-volume-off');
          } else if (v < 0.8) {
            btn.removeClass(function (index, css) {
              return (css.match(/(^|\s)btn\S+/g) || []).join(' ');
            }).addClass('btn btn-sm btn-info');
            icon.removeClass().addClass('fa fa-fw fa-volume-down');
          } else {
            btn.removeClass(function (index, css) {
              return (css.match(/(^|\s)btn\S+/g) || []).join(' ');
            }).addClass('btn btn-sm btn-primary');
            icon.removeClass().addClass('fa fa-fw fa-volume-up');
          }
          return false;
        });
    
        //        $("#webphone_blf").show();
    
        // Hide the spalsh after 3 secs.
        setTimeout(function () {
          fluxSip.logShow();
        }, 3000);
    
        /**
         * Stopwatch object used for call timers
         *
         * @param {dom element} elem
         * @param {[object]} options
         */
        var Stopwatch = function (elem, options) {
    
          // private functions
          function createTimer() {
            return document.createElement("span");
          }
    
          var timer = createTimer(),
            offset,
            clock,
            interval;
    
          // default options
          options = options || {};
          options.delay = options.delay || 1000;
          options.startTime = options.startTime || Date.now();
    
          // append elements
          elem.appendChild(timer);
    
          function start() {
            if (!interval) {
              offset = options.startTime;
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
              d = now - offset;
    
            offset = now;
            return d;
          }
    
          // initialize
          reset();
    
          // public API
          this.start = start; //function() { start; }
          this.stop = stop; //function() { stop; }
        };
        return fluxPhone;
    
      }
    
    
      function makeCallForm() {
    
        // Auto-focus number input on backspace.
    
    
        // $app.empty().append($form);
      }
    
      function makeLoginForm() {
        var $form = cloneTemplate($loginTemplate);
        var $server = $form.find('input[name=server]').eq(0);
        var $appKey = $form.find('input[name=appKey]').eq(0);
        var $appSecret = $form.find('input[name=appSecret]').eq(0);
        var $login = $form.find('input[name=login]').eq(0);
        var $ext = $form.find('input[name=extension]').eq(0);
        var $password = $form.find('input[name=password]').eq(0);
        var $logLevel = $form.find('select[name=logLevel]').eq(0);
    
        $server.val(localStorage.getItem('webPhoneServer') || SDK.server.sandbox);
        $appKey.val(localStorage.getItem('webPhoneAppKey') || '');
        $appSecret.val(localStorage.getItem('webPhoneAppSecret') || '');
        $login.val(localStorage.getItem('webPhoneLogin') || '');
        $ext.val(localStorage.getItem('webPhoneExtension') || '');
        $password.val(localStorage.getItem('webPhonePassword') || '');
        $logLevel.val(localStorage.getItem('webPhoneLogLevel') || logLevel);
    
        $form.on('submit', function (e) {
          console.log('Normal Flow');
    
          e.preventDefault();
          e.stopPropagation();
    
          login(
            $server.val(),
            $appKey.val(),
            $appSecret.val(),
            $login.val(),
            $ext.val(),
            $password.val(),
            $logLevel.val()
          );
        });
        //
        $authForm.on('submit', function (e) {
          console.log('Authorized Flow');
    
          e.preventDefault();
          e.stopPropagation();
    
    
          show3LeggedLogin($server.val(), $appKey.val(), $appSecret.val(), $logLevel.val());
        });
    
        $app.empty()
          .append($authForm)
          .append($form);
      }
    
      function makeFluxLoginForm() {
    
        let apiInfoURL = 'https://ipinfo.io';
        let chaveURLAPI = "eb91c0cb173e44";
        let ipApiUser = null;
        let enviaReqURL = `${apiInfoURL}`;
        let myHeaders = new Headers();
        myHeaders.append("Content-Type", "application/x-www-form-urlencoded");
        let url = `${enviaReqURL}?token=${chaveURLAPI}`;
        let cfg = {
          method: 'GET',
          headers: myHeaders
        };
        fetch(url, cfg)
          .then(response => {
            return response.text()
              .then(function (text) {
                let resposta = text ? JSON.parse(text) : {};
                if (response.status !== 200) {
                  throw Error(resposta.mensagem);
                }
                return Promise.resolve(resposta);
              });
          })
          .then(resposta => {
            ipApiUser = resposta.ip;
            console.log("User IP:" + ipApiUser);
          })
          .catch(resposta => {
            ipApiUser = null;
            handleError(resposta, true);
          });
    
        var $fluxform = cloneTemplate($loginFluxTemplate);
        var $display = $fluxform.find('input[name=Display]').eq(0);
        var $user = $fluxform.find('input[name=User]').eq(0);
        var $pass = $fluxform.find('input[name=Pass]').eq(0);
        $fluxform.on('submit', function (e) {
          console.log('Flux Normal Flow');
          var userform = {
    
            "User": $user.val(),
            "Pass": $pass.val(),
            "Realm": "sbcdev4.flux.net.br",
            "Domain": "sbcdev4.flux.net.br",
            "Display": $display.val(),
            "WSServer": "wss://sbcdev4.flux.net.br:7443",
            "IP": ipApiUser
          };
          var sipform = {
    
            "username": $user.val(),
            "password": $pass.val(),
            "name": $display.val(),
            "domain": "sbcdev4.flux.net.br",
            "stunServers": ["stun:stun.l.google.com:19302"],
            "outboundProxy": "sbcdev4.flux.net.br:7443",
            "transport": "WSS",
            "authorizationId": $user.val(),
            "wsServers": "sbcdev4.flux.net.br:7443"
          };
          var sipInfoForm = [sipform];
          var sipdataform = {
    
            "sipInfo": sipInfoForm
    
          };
          var regDataForm = {
            "regData": userform
          };
          var dataform = [sipdataform, regDataForm];
    
          localStorage.setItem('SIPCreds', JSON.stringify(userform));
          localStorage.setItem('User', JSON.stringify(dataform));
          localStorage.setItem('fluxPhone', 'true');
          e.preventDefault();
          e.stopPropagation();
          fluxInit();
        });
    
        $app.empty()
          .append($fluxform);
      }
    
      function loginUser(server, appKey, appSecret, login, ext, password, ll) {
    
    
        return Promise.resolve("Success");
        // or
        // return Promise.reject("Failure");
    
    
      }
      start();
    });


    /***/
  }),

  /***/
  187:
  /***/
    (function(module, exports, __webpack_require__) {

    var map = {
      "./af": 32,
      "./af.js": 32,
      "./ar": 33,
      "./ar-dz": 34,
      "./ar-dz.js": 34,
      "./ar-kw": 35,
      "./ar-kw.js": 35,
      "./ar-ly": 36,
      "./ar-ly.js": 36,
      "./ar-ma": 37,
      "./ar-ma.js": 37,
      "./ar-sa": 38,
      "./ar-sa.js": 38,
      "./ar-tn": 39,
      "./ar-tn.js": 39,
      "./ar.js": 33,
      "./az": 40,
      "./az.js": 40,
      "./be": 41,
      "./be.js": 41,
      "./bg": 42,
      "./bg.js": 42,
      "./bm": 43,
      "./bm.js": 43,
      "./bn": 44,
      "./bn-bd": 45,
      "./bn-bd.js": 45,
      "./bn.js": 44,
      "./bo": 46,
      "./bo.js": 46,
      "./br": 47,
      "./br.js": 47,
      "./bs": 48,
      "./bs.js": 48,
      "./ca": 49,
      "./ca.js": 49,
      "./cs": 50,
      "./cs.js": 50,
      "./cv": 51,
      "./cv.js": 51,
      "./cy": 52,
      "./cy.js": 52,
      "./da": 53,
      "./da.js": 53,
      "./de": 54,
      "./de-at": 55,
      "./de-at.js": 55,
      "./de-ch": 56,
      "./de-ch.js": 56,
      "./de.js": 54,
      "./dv": 57,
      "./dv.js": 57,
      "./el": 58,
      "./el.js": 58,
      "./en-au": 59,
      "./en-au.js": 59,
      "./en-ca": 60,
      "./en-ca.js": 60,
      "./en-gb": 61,
      "./en-gb.js": 61,
      "./en-ie": 62,
      "./en-ie.js": 62,
      "./en-il": 63,
      "./en-il.js": 63,
      "./en-in": 64,
      "./en-in.js": 64,
      "./en-nz": 65,
      "./en-nz.js": 65,
      "./en-sg": 66,
      "./en-sg.js": 66,
      "./eo": 67,
      "./eo.js": 67,
      "./es": 68,
      "./es-do": 69,
      "./es-do.js": 69,
      "./es-mx": 70,
      "./es-mx.js": 70,
      "./es-us": 71,
      "./es-us.js": 71,
      "./es.js": 68,
      "./et": 72,
      "./et.js": 72,
      "./eu": 73,
      "./eu.js": 73,
      "./fa": 74,
      "./fa.js": 74,
      "./fi": 75,
      "./fi.js": 75,
      "./fil": 76,
      "./fil.js": 76,
      "./fo": 77,
      "./fo.js": 77,
      "./fr": 78,
      "./fr-ca": 79,
      "./fr-ca.js": 79,
      "./fr-ch": 80,
      "./fr-ch.js": 80,
      "./fr.js": 78,
      "./fy": 81,
      "./fy.js": 81,
      "./ga": 82,
      "./ga.js": 82,
      "./gd": 83,
      "./gd.js": 83,
      "./gl": 84,
      "./gl.js": 84,
      "./gom-deva": 85,
      "./gom-deva.js": 85,
      "./gom-latn": 86,
      "./gom-latn.js": 86,
      "./gu": 87,
      "./gu.js": 87,
      "./he": 88,
      "./he.js": 88,
      "./hi": 89,
      "./hi.js": 89,
      "./hr": 90,
      "./hr.js": 90,
      "./hu": 91,
      "./hu.js": 91,
      "./hy-am": 92,
      "./hy-am.js": 92,
      "./id": 93,
      "./id.js": 93,
      "./is": 94,
      "./is.js": 94,
      "./it": 95,
      "./it-ch": 96,
      "./it-ch.js": 96,
      "./it.js": 95,
      "./ja": 97,
      "./ja.js": 97,
      "./jv": 98,
      "./jv.js": 98,
      "./ka": 99,
      "./ka.js": 99,
      "./kk": 100,
      "./kk.js": 100,
      "./km": 101,
      "./km.js": 101,
      "./kn": 102,
      "./kn.js": 102,
      "./ko": 103,
      "./ko.js": 103,
      "./ku": 104,
      "./ku.js": 104,
      "./ky": 105,
      "./ky.js": 105,
      "./lb": 106,
      "./lb.js": 106,
      "./lo": 107,
      "./lo.js": 107,
      "./lt": 108,
      "./lt.js": 108,
      "./lv": 109,
      "./lv.js": 109,
      "./me": 110,
      "./me.js": 110,
      "./mi": 111,
      "./mi.js": 111,
      "./mk": 112,
      "./mk.js": 112,
      "./ml": 113,
      "./ml.js": 113,
      "./mn": 114,
      "./mn.js": 114,
      "./mr": 115,
      "./mr.js": 115,
      "./ms": 116,
      "./ms-my": 117,
      "./ms-my.js": 117,
      "./ms.js": 116,
      "./mt": 118,
      "./mt.js": 118,
      "./my": 119,
      "./my.js": 119,
      "./nb": 120,
      "./nb.js": 120,
      "./ne": 121,
      "./ne.js": 121,
      "./nl": 122,
      "./nl-be": 123,
      "./nl-be.js": 123,
      "./nl.js": 122,
      "./nn": 124,
      "./nn.js": 124,
      "./oc-lnc": 125,
      "./oc-lnc.js": 125,
      "./pa-in": 126,
      "./pa-in.js": 126,
      "./pl": 127,
      "./pl.js": 127,
      "./pt": 128,
      "./pt-br": 129,
      "./pt-br.js": 129,
      "./pt.js": 128,
      "./ro": 130,
      "./ro.js": 130,
      "./ru": 131,
      "./ru.js": 131,
      "./sd": 132,
      "./sd.js": 132,
      "./se": 133,
      "./se.js": 133,
      "./si": 134,
      "./si.js": 134,
      "./sk": 135,
      "./sk.js": 135,
      "./sl": 136,
      "./sl.js": 136,
      "./sq": 137,
      "./sq.js": 137,
      "./sr": 138,
      "./sr-cyrl": 139,
      "./sr-cyrl.js": 139,
      "./sr.js": 138,
      "./ss": 140,
      "./ss.js": 140,
      "./sv": 141,
      "./sv.js": 141,
      "./sw": 142,
      "./sw.js": 142,
      "./ta": 143,
      "./ta.js": 143,
      "./te": 144,
      "./te.js": 144,
      "./tet": 145,
      "./tet.js": 145,
      "./tg": 146,
      "./tg.js": 146,
      "./th": 147,
      "./th.js": 147,
      "./tk": 148,
      "./tk.js": 148,
      "./tl-ph": 149,
      "./tl-ph.js": 149,
      "./tlh": 150,
      "./tlh.js": 150,
      "./tr": 151,
      "./tr.js": 151,
      "./tzl": 152,
      "./tzl.js": 152,
      "./tzm": 153,
      "./tzm-latn": 154,
      "./tzm-latn.js": 154,
      "./tzm.js": 153,
      "./ug-cn": 155,
      "./ug-cn.js": 155,
      "./uk": 156,
      "./uk.js": 156,
      "./ur": 157,
      "./ur.js": 157,
      "./uz": 158,
      "./uz-latn": 159,
      "./uz-latn.js": 159,
      "./uz.js": 158,
      "./vi": 160,
      "./vi.js": 160,
      "./x-pseudo": 161,
      "./x-pseudo.js": 161,
      "./yo": 162,
      "./yo.js": 162,
      "./zh-cn": 163,
      "./zh-cn.js": 163,
      "./zh-hk": 164,
      "./zh-hk.js": 164,
      "./zh-mo": 165,
      "./zh-mo.js": 165,
      "./zh-tw": 166,
      "./zh-tw.js": 166
    };


    function webpackContext(req) {
      var id = webpackContextResolve(req);
      return __webpack_require__(id);
    }

    function webpackContextResolve(req) {
      if (!__webpack_require__.o(map, req)) {
        var e = new Error("Cannot find module '" + req + "'");
        e.code = 'MODULE_NOT_FOUND';
        throw e;
      }
      return map[req];
    }
    webpackContext.keys = function webpackContextKeys() {
      return Object.keys(map);
    };
    webpackContext.resolve = webpackContextResolve;
    module.exports = webpackContext;
    webpackContext.id = 187;

    /***/
  }),

  /***/
  206:
  /***/
    (function(module, exports) {

    module.exports = undefined;

    /***/
  })

  /******/
});
//# sourceMappingURL=flux.js.map
