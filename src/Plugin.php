<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Plugin.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Main front-end functionality.
 */
class Plugin {

  /**
   * Gettext localization domain.
   *
   * @var string
   */
  const L10N = 'core-standards';

  /**
   * @var string
   */
  private static $baseUrl;

  /**
   * @implements init
   */
  public static function init() {
    // Add Facebook to oEmbed providers.
    // @see https://developers.facebook.com/docs/plugins/oembed-endpoints
    // @see https://developer.mozilla.org/en-US/Firefox/Privacy/Tracking_Protection
    add_filter('oembed_providers', __CLASS__ . '::oembed_providers');

    // Allow SVG files in media library.
    add_filter('upload_mimes', __CLASS__ . '::upload_mime_types');

    if (is_admin()) {
      return;
    }
    UserFrontend::init();
  }

  /**
   * @implements oembed_providers
   */
  public static function oembed_providers(array $providers) {
    $providers['#https?://(www\.)?facebook\.com/(.+/)?video.+#i'] = ['https://www.facebook.com/plugins/video/oembed.{format}', TRUE];
    $providers['#https?://(www\.)?facebook\.com/.+#i'] = ['https://www.facebook.com/plugins/post/oembed.{format}', TRUE];
    return $providers;
  }

  /**
   * @implements upload_mimes
   */
  public static function upload_mime_types(array $mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
  }

  /**
   * The base URL path to this plugin's folder.
   *
   * Uses plugins_url() instead of plugin_dir_url() to avoid a trailing slash.
   */
  public static function getBaseUrl() {
    if (!isset(static::$baseUrl)) {
      static::$baseUrl = plugins_url('', static::getBasePath() . '/custom.php');
    }
    return static::$baseUrl;
  }

  /**
   * The absolute filesystem base path of this plugin.
   *
   * @return string
   */
  public static function getBasePath() {
    return dirname(__DIR__);
  }

}
