"use strict";

(function () {
  if (typeof window.localStorage === 'undefined') {
    return;
  }

  function hideCookieNotice() {
    var cookie_notice = document.getElementById('cookie-consent');
    cookie_notice.setAttribute('hidden', 'true');
    document.body.classList.remove('has-cookie-consent');
  }

  function DOMContentLoaded() {
    var consent = JSON.parse(window.localStorage.getItem('cookie-consent'));

    if (consent && consent.version === window.core_standards.consent_version) {
      hideCookieNotice();
      return;
    }

    var cookie_notice = document.getElementById('cookie-consent');
    cookie_notice.removeAttribute('hidden');
    document.body.classList.add('has-cookie-consent');
    document.addEventListener('click', function (event) {
      if (event.target.dataset.js !== 'confirm' && event.target.dataset.js !== 'confirm-all') {
        return;
      }

      var data = {
        version: window.core_standards.consent_version,
        consent: {}
      };
      var checkboxes = document.querySelectorAll('input[name=cookies]');
      var _iteratorNormalCompletion = true;
      var _didIteratorError = false;
      var _iteratorError = undefined;

      try {
        for (var _iterator = checkboxes[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
          var checkbox = _step.value;

          if (event.target.dataset.js === 'confirm-all') {
            checkbox.checked = true;
          }

          data.consent[checkbox.value] = checkbox.checked;
        }
      } catch (err) {
        _didIteratorError = true;
        _iteratorError = err;
      } finally {
        try {
          if (!_iteratorNormalCompletion && _iterator.return != null) {
            _iterator.return();
          }
        } finally {
          if (_didIteratorError) {
            throw _iteratorError;
          }
        }
      }

      window.localStorage.setItem('cookie-consent', JSON.stringify(data));
      $.post(window.core_standards.ajaxurl, {
        action: 'core-standards/log_cookie_consent',
        consent: data.consent
      });
      hideCookieNotice();
    });
  }

  document.addEventListener('DOMContentLoaded', DOMContentLoaded);
})();