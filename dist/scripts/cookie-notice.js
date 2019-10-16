"use strict";

(function () {
  if (typeof window.localStorage === 'undefined') {
    return;
  }

  function hideCookieNotice() {
    var cookie_notice = document.getElementById('cookie-notice');
    cookie_notice.setAttribute('hidden', 'true');
    document.body.classList.remove('has-cookie-notice');
  }

  function DOMContentLoaded() {
    document.body.classList.add('has-cookie-notice');
    document.addEventListener('click', function (event) {
      if (event.target.dataset.js !== 'confirm' && event.target.dataset.js !== 'confirm-all') {
        return;
      }

      var values = {};
      var checkboxes = document.querySelectorAll('input[name=cookies]');
      var _iteratorNormalCompletion = true;
      var _didIteratorError = false;
      var _iteratorError = undefined;

      try {
        for (var _iterator = checkboxes[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
          checkbox = _step.value;

          if (event.target.dataset.js === 'confirm-all') {
            checkbox.checked = true;
          }

          values[checkbox.value] = checkbox.checked;
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

      window.localStorage.setItem('cookies-accepted', JSON.stringify(values));
      hideCookieNotice();
    });
  }

  if (JSON.parse(window.localStorage.getItem('cookies-accepted'))) {
    hideCookieNotice();
    return false;
  }

  document.addEventListener('DOMContentLoaded', DOMContentLoaded);
})();