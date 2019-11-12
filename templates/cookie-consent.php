<?php

namespace Netzstrategen\CoreStandards;

$options = [
  [
    'name' => 'essentials',
    'title' => __('Butter cookies only', Plugin::L10N),
    'desc' => __('Required functioanlity', Plugin::L10N),
    'atts' => [
      'disabled' => TRUE,
      'checked' => TRUE,
    ],
  ],
  [
    'name' => 'statistics',
    'title' => __('Chocolate cookies', Plugin::L10N),
    'desc' => __('Statistics', Plugin::L10N),
  ],
  [
    'name' => 'personalization',
    'title' => __('Space cookies', Plugin::L10N),
    'desc' => __('Personalization', Plugin::L10N),
  ],
];

$texts = [
  'title' => __('Cookies?', Plugin::L10N),
  'desc' => sprintf(__('For the best possible service, this website offers you cookies. More information can be found in our <a href=%s>Cookie-Bar</a>.', Plugin::L10N), apply_filters('core_standards/cookie_consent/policy_link', '/privacy')),
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
    <h1><?= esc_html($texts['title']) ?></h1>
    <p><?= $texts['desc'] ?></p>
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
      <button class="button btn" data-js="confirm"><?= esc_html($texts['button_confirm']) ?></button>
      <button class="button button--primary btn btn--primary" data-js="confirm-all"><?= esc_html($texts['button_confirm_all']) ?></button>
    </div>
  </div>
</div>

<script>
<?php
  $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
  echo file_get_contents(Plugin::getBasePath() . '/dist/scripts/cookie-consent' . $min . '.js');
?>
</script>
