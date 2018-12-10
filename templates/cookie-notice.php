<?php

namespace Netzstrategen\CoreStandards;

?>

<style>
  #cookie-notice {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 100000;
    padding: 0.5em;
    text-align: center;
    background-color: #000;
    color: #fff;
  }

  <?= do_action('core_standards/cookie_notice/css_extend') ?>
</style>

<div id="cookie-notice" role="banner">
  <span><?= __('This website uses cookies to give you the best possible service. By continuing to use the website, you agree to the use of cookies.', Plugin::L10N) ?></span>
  <a href="#" id="cookies-accept" class="btn"><?= __('Okay') ?></a>
  <a href="<?= apply_filters('core_standards/cookie_notice/policy_link', '/datenschutzerklaerung') ?>" target="_self" class="btn"><?= __('Mehr Infos', Plugin::L10N) ?></a>
</div>

<script>
  if (typeof window.localStorage !== 'undefined') {
    var cookie_notice = document.getElementById('cookie-notice');
    if (window.localStorage.getItem('cookies-accepted')) {
      cookie_notice.setAttribute('hidden', 'true');
    }
    document.getElementById('cookies-accept').addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      window.localStorage.setItem('cookies-accepted', 'true');
      cookie_notice.setAttribute('hidden', 'true');
    });
  }
</script>
