<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\TrackingOptOut.
 */

namespace Netzstrategen\CoreStandards;

class TrackingOptOut {

  /**
   * @implements admin_init
   */
  public static function admin_init() {
    static::addSettingsFields();
  }

  /**
   * @implements init
   */
  public static function init() {

    // Register the shortcode to call the opt-out cookie creation script.
    add_shortcode('opt-out-link', __CLASS__ . '::registerOptOutShortcode');

    // Enqueue frontend scripts.
    if (get_option('core_standards_opt_out_ids')) {
      add_action('wp_enqueue_scripts', __CLASS__ . '::wp_enqueue_scripts');
      // Injects the Google Analytics IDs and the opt-out script in the page head
      // section before any tracking script.
      add_action('wp_head', __CLASS__ . '::wp_head', 5);
      add_action('wp_enqueue_scripts', __CLASS__ . '::enqueueScriptsFirstHead', 1);
    }
  }

  /**
   * Adds Opt-Out settings fields to 'Reading' settings page.
   */
  public static function addSettingsFields() {
    register_setting(
      'reading',
      'core_standards_opt_out_ids',
      'esc_attr'
    );
    add_settings_field(
      'core_standards_opt_out_ids',
      __('Google Analytics IDs', Plugin::L10N),
      __CLASS__ . '::optOutGoogleIdsFieldRender',
      'reading',
      'default'
    );
  }

  /**
   * Renders Google Analytics IDs list field.
   */
  public static function optOutGoogleIdsFieldRender() {
    echo '<textarea id="core-standards-opt-out-ids" name="core_standards_opt_out_ids" rows="8" cols="40">' .
      esc_attr(get_option('core_standards_opt_out_ids')) . '</textarea>';
  }

  /**
   * @implements wp_head
   */
  public static function wp_head() {
    if (!get_option('core_standards_opt_out_ids')) {
      return;
    }
    // Removes white-space characters from the string to get a comma-separated list
    // of Google Analytics IDs.
    $opt_out_ids = preg_split('/[\s,]+/', get_option('core_standards_opt_out_ids'));
  ?>

<script>
// List of Google Analytics IDs.
window.gaIdList = <?= json_encode($opt_out_ids) ?>;
</script>
  <?php
  }

  /**
   * Renders opt-out link shortcode.
   *
   * @param array $atts
   *   Shortcode parameters as attributes.
   * @param string $content
   *   Shortcode wrapped content.
   *
   * @return string
   */
  public static function registerOptOutShortcode ($atts, $content = NULL) {
    if (get_option('core_standards_opt_out_ids')) {
      return '<a class="core-standards-opt-out-link" href="#">' . $content . '</a>';
    }
    else {
      return $content;
    }
  }

  /**
   * Renders the settings page template.
   */
  public static function renderSettings() {
    Plugin::renderTemplate(['templates/settings.php']);
  }

  public static function enqueueScriptsFirstHead() {
    wp_enqueue_script(Plugin::PREFIX . '-google_analytics_opt_out', Plugin::getBaseUrl() . '/dist/scripts/google-analytics-opt-out.js', FALSE);
    if (COOKIE_DOMAIN) {
      wp_localize_script(Plugin::PREFIX . '-google_analytics_opt_out', 'core_standards_opt_out', ['cookie_domain' => COOKIE_DOMAIN ? '.' . ltrim(COOKIE_DOMAIN, '.') : '']);
    }
  }

  /**
   * @implements wp_enqueue_scripts.
   */
  public static function wp_enqueue_scripts() {
    wp_enqueue_script(Plugin::PREFIX . '-enable-opt_out', Plugin::getBaseUrl() . '/dist/scripts/enable-opt-out.js', ['jquery'], TRUE);
    wp_localize_script(Plugin::PREFIX . '-enable-opt_out', 'core_standards_opt_out_toggle', ['confirmation_message' => __('Google Analytics tracking has been disabled.', Plugin::L10N)]);
  }

}
