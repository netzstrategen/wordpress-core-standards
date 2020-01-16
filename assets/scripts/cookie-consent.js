(function () {
  if (typeof window.localStorage === 'undefined') {
    return;
  }

  function addCookieSettingsButton() {
    const cookie_settings_button = document.querySelector('[data-trigger-cookie-consent]');
    if (cookie_settings_button) {
      cookie_settings_button.addEventListener('click', showCookieNotice);
    }
  }

  function hideCookieNotice() {
    const cookie_notice = document.getElementById('cookie-consent');
    cookie_notice.setAttribute('hidden', 'true');
    document.body.classList.remove('has-cookie-consent');
  }

  function showCookieNotice() {
    const cookie_notice = document.getElementById('cookie-consent');
    cookie_notice.removeAttribute('hidden');
    document.body.classList.add('has-cookie-consent');
  }

  function DOMContentLoaded() {
    addCookieSettingsButton();
    const consent = JSON.parse(window.localStorage.getItem('cookie-consent'));
    if (consent && consent.version === window.core_standards.consent_version) {
      hideCookieNotice();
    }
    else {
      showCookieNotice();
    }
    document.addEventListener('click', (event) => {
      if (event.target.dataset.js !== 'confirm' && event.target.dataset.js !== 'confirm-all') {
        return;
      }
      const data = {
        version: window.core_standards.consent_version,
        consent: {},
        consent_id: generate_uuid(),
      };
      const checkboxes = document.querySelectorAll('input[name=cookies]');
      for (const checkbox of checkboxes) {
        if (event.target.dataset.js === 'confirm-all') {
          checkbox.checked = true;
        }
        data.consent[checkbox.value] = checkbox.checked;
      }
      window.localStorage.setItem('cookie-consent', JSON.stringify(data));
      jQuery.post(window.core_standards.ajaxurl, {
        action: 'core-standards/log_cookie_consent',
        consent: data,
        referer: window.location.pathname,
      });
      dataLayer.push({ 'event': 'Consent Submitted' });
      hideCookieNotice();
    });
    if (window.location.href.indexOf("?nobanner") !== -1) {
      hideCookieNotice();
    }

  }

  document.addEventListener('DOMContentLoaded', DOMContentLoaded);
})();

function generate_uuid() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}
