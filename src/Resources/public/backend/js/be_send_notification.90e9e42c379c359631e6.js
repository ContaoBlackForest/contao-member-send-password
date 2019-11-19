!function(t){var r={};function s(e){if(r[e])return r[e].exports;var n=r[e]={i:e,l:!1,exports:{}};return t[e].call(n.exports,n,n.exports,s),n.l=!0,n.exports}s.m=t,s.c=r,s.d=function(e,n,t){s.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:t})},s.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},s.t=function(n,e){if(1&e&&(n=s(n)),8&e)return n;if(4&e&&"object"==typeof n&&n&&n.__esModule)return n;var t=Object.create(null);if(s.r(t),Object.defineProperty(t,"default",{enumerable:!0,value:n}),2&e&&"string"!=typeof n)for(var r in n)s.d(t,r,function(e){return n[e]}.bind(null,r));return t},s.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return s.d(n,"a",n),n},s.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},s.p="",s(s.s="./src/Resources/assets/backend/javascript/be_send_notification.js")}({"./node_modules/document-ready/index.js":function(module,exports,__webpack_require__){"use strict";eval("\n\nmodule.exports = ready\n\nfunction ready (callback) {\n  if (typeof document === 'undefined') {\n    throw new Error('document-ready only runs in the browser')\n  }\n  var state = document.readyState\n  if (state === 'complete' || state === 'interactive') {\n    return setTimeout(callback, 0)\n  }\n\n  document.addEventListener('DOMContentLoaded', function onLoad () {\n    callback()\n  })\n}\n\n\n//# sourceURL=webpack:///./node_modules/document-ready/index.js?")},"./src/Resources/assets/backend/javascript/be_send_notification.js":function(module,__webpack_exports__,__webpack_require__){"use strict";eval('__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _scss_be_send_notification_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../scss/be_send_notification.scss */ "./src/Resources/assets/backend/scss/be_send_notification.scss");\n/* harmony import */ var _scss_be_send_notification_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_scss_be_send_notification_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _send_notification__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./send_notification */ "./src/Resources/assets/backend/javascript/send_notification.js");\n\n\n\n//# sourceURL=webpack:///./src/Resources/assets/backend/javascript/be_send_notification.js?')},"./src/Resources/assets/backend/javascript/send_notification.js":function(module,__webpack_exports__,__webpack_require__){"use strict";eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var document_ready__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! document-ready */ \"./node_modules/document-ready/index.js\");\n/* harmony import */ var document_ready__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(document_ready__WEBPACK_IMPORTED_MODULE_0__);\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\nfunction _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }\n\nfunction _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }\n\n\n\nvar SendNotification =\n/*#__PURE__*/\nfunction () {\n  function SendNotification() {\n    _classCallCheck(this, SendNotification);\n\n    this.terminate = false;\n    this.sendNotificationWrapper = document.querySelector('#tl_send_notification_action');\n    this.errortWrapper = document.querySelector('#error');\n    this.sendNotificationProgressbar = this.sendNotificationWrapper.querySelector('.progress-bar-inner');\n    this.sendDelay = 20;\n    this.debug = location.href.search('app_dev.php') > 1 ? '/app_dev.php' : '';\n  }\n\n  _createClass(SendNotification, [{\n    key: \"init\",\n    value: function init() {\n      var self = this;\n      document.querySelector('#terminate').addEventListener('click', function (event) {\n        event.preventDefault();\n        self.terminate = true;\n      });\n      this.sendNotification();\n    }\n  }, {\n    key: \"sendNotification\",\n    value: function sendNotification() {\n      if (this.terminate) {\n        return false;\n      }\n\n      var route = this.debug + '/contao/cb/member/send_password/send/notification';\n      var self = this;\n      this.sendNotificationWrapper.classList.remove('not_active');\n      this.request(route, function (response) {\n        self.sendNotificationProgressbar.style.width = response.progress + '%';\n\n        if (response.progress < 100) {\n          setTimeout(function () {\n            self.sendNotification();\n          }, response.sendDelay);\n          return false;\n        }\n\n        self.sendNotificationProgressbar.style.width = response.progress + '%';\n        self.sendNotificationProgressbar.addEventListener('transitionend', function () {\n          self.finish();\n        });\n      });\n    }\n  }, {\n    key: \"finish\",\n    value: function finish() {\n      location.href = this.debug + '/contao?do=member';\n    }\n  }, {\n    key: \"request\",\n    value: function request(route, callback) {\n      if (this.terminate) {\n        return false;\n      }\n\n      var request = new XMLHttpRequest();\n      var self = this;\n\n      request.onreadystatechange = function () {\n        if (4 === request.readyState) {\n          var response = JSON.parse(request.responseText);\n\n          if (undefined === response.error) {\n            callback(response);\n            return true;\n          }\n\n          self.errortWrapper.innerHTML = response.error;\n        }\n      };\n\n      request.open('GET', route);\n      request.send();\n    }\n  }]);\n\n  return SendNotification;\n}();\n\ndocument_ready__WEBPACK_IMPORTED_MODULE_0___default()(function () {\n  var importer = new SendNotification();\n  importer.init();\n});\n\n//# sourceURL=webpack:///./src/Resources/assets/backend/javascript/send_notification.js?")},"./src/Resources/assets/backend/scss/be_send_notification.scss":function(module,exports,__webpack_require__){eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./src/Resources/assets/backend/scss/be_send_notification.scss?")}});