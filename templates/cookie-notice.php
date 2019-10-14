<?php

namespace Netzstrategen\CoreStandards;
?>

<div class="cookie-notice" id="cookie-notice" role="banner">
  <div class="cookie-notice__body">
    <h1>Kekse?</h1>
    <p><?= sprintf(__('Für den bestmöglichen Service bietet Ihnen diese Website Cookies. Mehr Infos finden Sie in unserer <a href="%s">Cookie-Bar</a>.', Plugin::L10N), apply_filters('core_standards/cookie_notice/policy_link', '/privacy')); ?></p>
<div class="options">

<div class="form-item form-checkbox">
<input id="cookie-accept" type="checkbox" value="1" checked disabled />
<label for="cookie-accept">Nur Butterkekse
<br><small>Erforderliche Funktionen</small>
</label>
</div>
<div class="form-item form-checkbox">
<input id="cookie-accept" type="checkbox" value="1" />
<label for="cookie-accept">Schokokekse
<br><small>Statistiken</small>
</label>
</div>
<div class="form-item form-checkbox">
<input id="cookie-accept" type="checkbox" value="1" />
<label for="cookie-accept">Space-Cookies
<br><small>Personalisierung</small>
</label>
</div>

</div>

<div class="form-actions">
<button class="button btn">Meine Auswahl bestätigen</button>
<button class="button button--primary btn btn--primary">Gönn ich mir alle</button>
</div>

  </div>
  <!--a class="cookie-notice__close" href="#" id="cookies-accept" aria-label="<?= __('OK', Plugin::L10N) ?>"></a-->
</div>

<script>
(function () {
  if (typeof window.localStorage !== 'undefined') {
    var cookie_notice = document.getElementById('cookie-notice');
    if (window.localStorage.getItem('cookies-accepted')) {
      cookie_notice.setAttribute('hidden', 'true');
      return false;
    }
    document.getElementById('cookies-accept').addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      window.localStorage.setItem('cookies-accepted', 'true');
      cookie_notice.setAttribute('hidden', 'true');
    });
  }
})();
</script>
