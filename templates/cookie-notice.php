<?php

namespace Netzstrategen\CoreStandards;

if (has_action('core_standards/cookie_notice/css_extend')) {
  echo '<style>';
  do_action('core_standards/cookie_notice/css_extend');
  echo '</style>';
}
?>

<div class="cookie-notice" id="cookie-notice" role="banner">
  <div class="cookie-notice__body">
    <?= __('This website uses cookies to give you the best possible service. By continuing to use the website, you agree to the use of cookies.', Plugin::L10N) ?>
    <a class="cookie-notice__link" href="<?= apply_filters('core_standards/cookie_notice/policy_link', '/privacy') ?>" target="_self"><?= __('Learn more', Plugin::L10N) ?></a>
  </div>
  <a class="cookie-notice__close" href="#" id="cookies-accept" aria-label="<?= __('OK', Plugin::L10N) ?>"><?= __('OK', Plugin::L10N) ?></a>
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
