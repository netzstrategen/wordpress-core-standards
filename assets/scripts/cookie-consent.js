(function () {
  if (typeof window.localStorage === 'undefined') {
    return;
  }

  function hideCookieNotice() {
    const cookie_notice = document.getElementById('cookie-consent');
    cookie_notice.setAttribute('hidden', 'true');
    document.body.classList.remove('has-cookie-consent');
  }

  function DOMContentLoaded() {
    const consent = JSON.parse(window.localStorage.getItem('cookie-consent'));
    if (consent && consent.version === window.core_standards.consent_version) {
      hideCookieNotice();
      return;
    }
    const cookie_notice = document.getElementById('cookie-consent');
    cookie_notice.removeAttribute('hidden');
    document.body.classList.add('has-cookie-consent');
    document.addEventListener('click', (event) => {
      if (event.target.dataset.js !== 'confirm' && event.target.dataset.js !== 'confirm-all') {
        return;
      }
      const data = {
        version: window.core_standards.consent_version,
        consent: {},
        id: generate_uuid(),
      };
      const checkboxes = document.querySelectorAll('input[name=cookies]');
      for (const checkbox of checkboxes) {
        if (event.target.dataset.js === 'confirm-all') {
          checkbox.checked = true;
        }
        data.consent[checkbox.value] = checkbox.checked;
      }
      window.localStorage.setItem('cookie-consent', JSON.stringify(data));
      $.post(window.core_standards.ajaxurl, {
        action: 'core-standards/log_cookie_consent',
        consent: data.consent,
        id: generate_uuid(),
      });
      dataLayer.push({'event':'Consent Submitted'});
      hideCookieNotice();
    });
  }

  document.addEventListener('DOMContentLoaded', DOMContentLoaded);
})();

function generate_uuid() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}
