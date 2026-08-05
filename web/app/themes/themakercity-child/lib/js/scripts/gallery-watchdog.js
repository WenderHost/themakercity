/**
 * Automatically detects and reports failures in the ACF "Gallery Images"
 * field on the profile editor, instead of relying on users noticing
 * something's wrong and manually clicking "Share Your System Info".
 *
 * Reports three kinds of failure to the `send_system_info` AJAX endpoint:
 *  1. gallery_add_no_modal   - "Add to gallery" was clicked but no media
 *                              modal appeared (JS/init failure).
 *  2. gallery_upload_*       - the actual upload XHR to WordPress' media
 *                              endpoints failed or was rejected server-side
 *                              (e.g. disallowed file type, capability
 *                              denied, file too large). This carries the
 *                              real WordPress error text.
 *  3. gallery_js_error       - an uncaught JS error while the user was
 *                              actively interacting with the gallery field.
 */
(function ($) {
  'use strict';

  if (typeof wpvars === 'undefined' || !wpvars.ajax_url) return;

  var WATCH_TIMEOUT_MS = 5000;
  var MAX_REPORTS = 5;
  var reportCount = 0;
  var reportedKeys = {};

  function reportEvent(event, detail, jsError) {
    var key = event + '|' + (detail || '');
    if (reportedKeys[key] || reportCount >= MAX_REPORTS) return;
    reportedKeys[key] = true;
    reportCount++;

    fetch(wpvars.ajax_url + '?action=send_system_info', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        event: event,
        detail: detail || '',
        jsError: jsError || '',
        browser: navigator.userAgent,
        os: navigator.platform,
        screenResolution: screen.width + 'x' + screen.height,
        user_email: wpvars.user_email,
        nonce: wpvars.nonce
      })
    }).catch(function () {
      // Best-effort diagnostic logging; don't surface network errors to the user.
    });
  }

  /**
   * Watchdog #1: click "Add to gallery"/"Edit" and confirm a media modal
   * actually appears. If ACF's gallery field JS failed to initialize or
   * its click handler never bound, nothing happens and the user just sees
   * an unresponsive link - this catches that silently.
   */
  $(document).on('click', '.acf-gallery-add, .acf-gallery-edit', function () {
    var label = this.className || 'unknown';
    setTimeout(function () {
      if (!document.querySelector('.media-modal')) {
        reportEvent(
          'gallery_add_no_modal',
          'Clicked "' + label + '" but no media modal appeared within ' + WATCH_TIMEOUT_MS + 'ms.'
        );
      }
    }, WATCH_TIMEOUT_MS);
  });

  /**
   * Watchdog #2: intercept the actual upload XHR WordPress' media modal
   * sends (Plupload/browser uploader use XMLHttpRequest, not fetch) and
   * report failures with the real server-side error text - e.g. "Sorry,
   * you are not allowed to upload this file type" or a capability error.
   * This is the most direct way to see *why* an upload failed.
   */
  var origOpen = XMLHttpRequest.prototype.open;
  var origSend = XMLHttpRequest.prototype.send;

  XMLHttpRequest.prototype.open = function (method, url) {
    this.__mkcUploadUrl = url;
    return origOpen.apply(this, arguments);
  };

  XMLHttpRequest.prototype.send = function (body) {
    var xhr = this;
    var url = this.__mkcUploadUrl || '';

    if (/upload-attachment|async-upload\.php/i.test(url)) {
      xhr.addEventListener('load', function () {
        if (xhr.status >= 400) {
          reportEvent(
            'gallery_upload_http_error',
            'Upload request failed with HTTP ' + xhr.status + ': ' + url,
            (xhr.responseText || '').substring(0, 500)
          );
          return;
        }
        try {
          var json = JSON.parse(xhr.responseText);
          if (json && json.success === false) {
            reportEvent(
              'gallery_upload_rejected',
              'Upload request succeeded (HTTP ' + xhr.status + ') but the server reported failure.',
              JSON.stringify(json).substring(0, 500)
            );
          }
        } catch (e) {
          // Response wasn't JSON - not necessarily an error, ignore.
        }
      });
      xhr.addEventListener('error', function () {
        reportEvent('gallery_upload_network_error', 'Network-level error on upload request: ' + url);
      });
    }

    return origSend.apply(this, arguments);
  };

  /**
   * Watchdog #3: uncaught JS errors while the user is actively interacting
   * with the gallery field. Scoped to a short window after interaction so
   * we don't flood reports with unrelated third-party script errors
   * (e.g. the Google Maps deprecation warnings on this same page).
   */
  var recentGalleryInteraction = false;
  var interactionTimer = null;

  $(document).on('click', '.acf-field-gallery', function () {
    recentGalleryInteraction = true;
    clearTimeout(interactionTimer);
    interactionTimer = setTimeout(function () {
      recentGalleryInteraction = false;
    }, WATCH_TIMEOUT_MS);
  });

  window.addEventListener('error', function (e) {
    if (!recentGalleryInteraction) return;
    reportEvent(
      'gallery_js_error',
      e.message + ' (' + e.filename + ':' + e.lineno + ')',
      e.error && e.error.stack ? e.error.stack : ''
    );
  });
}(jQuery));
