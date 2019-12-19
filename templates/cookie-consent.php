<?php

namespace Netzstrategen\CoreStandards;

$options = [
  [
    'name' => 'essentials',
    'title' => __('Only necessary', Plugin::L10N),
    'desc' => __('Required functionality', Plugin::L10N),
    'atts' => [
      'disabled' => TRUE,
      'checked' => TRUE,
    ],
  ],
  [
    'name' => 'statistics',
    'title' => __('Behavior', Plugin::L10N),
    'desc' => __('Statistics', Plugin::L10N),
  ],
  [
    'name' => 'personalization',
    'title' => __('Marketing', Plugin::L10N),
    'desc' => __('Personalization', Plugin::L10N),
  ],
];

$cookiepolicy_url = WP_SITETERMPATH ?? '';

$texts = [
  'title' => __('Cookie Settings', Plugin::L10N),
  'desc' => sprintf(__('For the best possible service, this website offers you cookies. Please make your choice to use the site. More information can be found in our <a href=%s target="_blank">privacy policy</a>.', Plugin::L10N), apply_filters('core_standards/cookie_consent/policy_link', $cookiepolicy_url . '?nobanner')),
  'button_confirm' => __('Confirm selection', Plugin::L10N),
  'button_confirm_all' => __('Get me anything', Plugin::L10N),
];

function create_attributes($atts) {
  $attributes = [];
  foreach ($atts as $name => $value) {
    $attributes[] = $name . '="' . esc_attr($value) . '"';
  }
  return implode($attributes, ' ');
}
?>

<div class="cookie-consent" id="cookie-consent" role="banner" hidden="hidden">
  <div class="cookie-consent__body">
    <span class="h1"><?= esc_html($texts['title']) ?></span>
    <p><?= $texts['desc'] ?></p>
    <?php if ($options): ?>
      <div class="options">
        <?php foreach ($options as $option): ?>
          <div class="form-item form-checkbox">
           <input id="<?= esc_attr(sanitize_title($option['name'])) ?>" name="cookies" type="checkbox" value="<?= esc_attr($option['name']) ?>" <?= create_attributes($option['atts'] ?? []); ?> />
            <label for="<?= esc_attr(sanitize_title($option['name'])) ?>">
              <?= esc_html($option['title']) ?>
              <br><small><?= esc_html($option['desc']) ?></small>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="form-actions">
      <button class="button--link btn--link" data-js="confirm"><?= esc_html($texts['button_confirm']) ?></button>
      <button class="button button--primary btn btn--primary" data-js="confirm-all"><?= esc_html($texts['button_confirm_all']) ?></button>
    </div>
  </div>
</div>
