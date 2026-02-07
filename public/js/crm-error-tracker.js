(function () {
  'use strict';

  if (window.__crmErrorTrackerLoaded) {
    return;
  }
  window.__crmErrorTrackerLoaded = true;

  var endpoint = '/api/client-errors';
  var sentFingerprints = {};
  var maxUniqueErrors = 30;
  var sentCount = 0;

  function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function hash(str) {
    var h = 0;
    for (var i = 0; i < str.length; i++) {
      h = ((h << 5) - h) + str.charCodeAt(i);
      h |= 0;
    }
    return String(h);
  }

  function send(payload) {
    try {
      if (sentCount >= maxUniqueErrors) return;

      var fingerprint = hash((payload.message || '') + '|' + (payload.file || '') + '|' + (payload.line || ''));
      if (sentFingerprints[fingerprint]) return;
      sentFingerprints[fingerprint] = true;
      sentCount++;

      payload._token = getCsrfToken();
      var body = JSON.stringify(payload);
      if (navigator.sendBeacon) {
        var blob = new Blob([body], { type: 'application/json' });
        navigator.sendBeacon(endpoint, blob);
        return;
      }

      fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken()
        },
        credentials: 'same-origin',
        keepalive: true,
        body: body
      }).catch(function () {});
    } catch (e) {
      // tracker fail-safe
    }
  }

  window.addEventListener('error', function (event) {
    send({
      level: 'error',
      source: 'window.onerror',
      message: event.message || 'Unknown client error',
      file: event.filename || null,
      line: event.lineno || null,
      col: event.colno || null,
      stack: event.error && event.error.stack ? String(event.error.stack).slice(0, 4000) : null,
      url: window.location.href,
      user_agent: navigator.userAgent
    });
  });

  window.addEventListener('unhandledrejection', function (event) {
    var reason = event.reason;
    var message = 'Unhandled promise rejection';
    var stack = null;

    if (typeof reason === 'string') {
      message = reason;
    } else if (reason && reason.message) {
      message = reason.message;
      stack = reason.stack ? String(reason.stack).slice(0, 4000) : null;
    }

    send({
      level: 'error',
      source: 'window.unhandledrejection',
      message: message,
      stack: stack,
      url: window.location.href,
      user_agent: navigator.userAgent
    });
  });
})();
