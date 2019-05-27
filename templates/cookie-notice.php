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
  <a class="cookie-notice__close" href="#" id="cookies-accept" aria-label="<?= __('OK', Plugin::L10N) ?>"><svg class="cookie-notice__icon" viewBox="0 0 25.156 25.078"><path d="M13.672 12.578l11.25-11.25a.756.756 0 0 0 0-1.094.755.755 0 0 0-1.094 0l-11.25 11.25L1.328.234a1.337 1.337 0 0 0-1.094 0 1.337 1.337 0 0 0 0 1.094l11.25 11.25-11.25 11.25a.756.756 0 0 0 0 1.094c.156.156.469.156.625.156.156 0 .312 0 .469-.156l11.25-11.25 11.25 11.25c.156.156.312.156.469.156.157 0 .312 0 .469-.156a.756.756 0 0 0 0-1.094l-11.094-11.25z"></path></svg></a>
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
