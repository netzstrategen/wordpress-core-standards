(function () {
  if (typeof window.localStorage === 'undefined') {
    return;
  }

  function hideCookieNotice() {
    const cookie_notice = document.getElementById('cookie-notice');
    cookie_notice.setAttribute('hidden', 'true');
    document.body.classList.remove('has-cookie-notice');
  }

  function DOMContentLoaded() {
    document.body.classList.add('has-cookie-notice');
    document.addEventListener('click', (event) => {
      if (event.target.dataset.js !== 'confirm' && event.target.dataset.js !== 'confirm-all') {
        return;
      }
      const values = {};
      const checkboxes = document.querySelectorAll('input[name=cookies]');
      for (checkbox of checkboxes) {
        if (event.target.dataset.js === 'confirm-all') {
          checkbox.checked = true;
        }
        values[checkbox.value] = checkbox.checked;
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
