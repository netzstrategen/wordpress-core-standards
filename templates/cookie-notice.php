<?php

namespace Netzstrategen\CoreStandards;

$options = [
  [
    'name' => 'basic',
    'title' => __('Nur Butterkekse', Plugin::L10N),
    'desc' => __('Erforderliche Funktionen', Plugin::L10N),
    'atts' => [
      'disabled' => TRUE,
      'checked' => TRUE,
    ],
  ],
  [
    'name' => 'statistics',
    'title' => __('Schokokekse', Plugin::L10N),
    'desc' => __('Statistiken', Plugin::L10N),
  ],
  [
    'name' => 'personalization',
    'title' => __('Space-Cookies', Plugin::L10N),
    'desc' => __('Personalisierung', Plugin::L10N),
  ],
];

function create_attributes($atts) {
  $attributes = [];
  foreach ($atts as $name => $value) {
    $attributes[] = $name . '="' . esc_attr($value) . '"';
  }
  return implode($attributes, ' ');
}
?>

<div class="cookie-notice" id="cookie-notice" role="banner">
  <div class="cookie-notice__body">
    <h1><?= __('Kekse?', Plugin::L10N) ?></h1>
    <p><?= sprintf(__('Für den bestmöglichen Service bietet Ihnen diese Website Cookies. Mehr Infos finden Sie in unserer <a href="%s">Cookie-Bar</a>.', Plugin::L10N), apply_filters('core_standards/cookie_notice/policy_link', '/privacy')); ?></p>
    <?php if ($options): ?>
      <div class="options">
        <?php foreach ($options as $option): ?>
          <div class="form-item form-checkbox">
            <label for="<?= esc_attr(sanitize_title($option['name'])) ?>">
              <input id="<?= esc_attr(sanitize_title($option['name'])) ?>" name="cookies" type="checkbox" value="<?= esc_attr($option['name']) ?>" <?= create_attributes($option['atts'] ?? []); ?> />
              <?= esc_html($option['title']) ?>
              <br><small><?= esc_html($option['desc']) ?></small>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="form-actions">
      <button class="button btn" data-js="confirm"><?= __('Meine Auswahl bestätigen', Plugin::L10N) ?></button>
      <button class="button button--primary btn btn--primary" data-js="confirm-all"><?= __('Gönn ich mir alle', Plugin::L10N) ?></button>
    </div>
  </div>
</div>

<script>
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
      const values = [];
      const checkboxes = document.querySelectorAll('input[name=cookies]');
      for (checkbox of checkboxes) {
        if (event.target.dataset.js === 'confirm-all') {
          checkbox.checked = true;
        }
        if (checkbox.checked) {
          values.push(checkbox.value);
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
</script>
