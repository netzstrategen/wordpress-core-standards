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
   * Prefix for naming.
   *
   * @var string
   */
  const PREFIX = 'core-standards';

  /**
   * Gettext localization domain.
   *
   * @var string
   */
  const L10N = self::PREFIX;

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

    // Add wrapper to oEmbed elements.
    add_filter('embed_oembed_html', __CLASS__ . '::embed_oembed_html', 10, 4);

    // Allow SVG files in media library.
    add_filter('upload_mimes', __CLASS__ . '::upload_mime_types');

    // Remove size attributes from SVG images, so they scale correctly without a CSS size reset.
    add_filter('the_content', __CLASS__ . '::the_content');
    add_filter('wp_get_attachment_metadata', __CLASS__ . '::removeSvgSizeAttributes', 10, 2);
    add_filter('post_thumbnail_html', __CLASS__ . '::removeSvgSizeAttributes', 10, 3);
    // Limit to two arguments since the third parameter is the image caption.
    add_filter('image_send_to_editor', __CLASS__ . '::removeSvgSizeAttributes', 10, 2);

    // Set proper From header for all emails.
    // Remove "[$blogname]" prefix in email subjects of user account mails.
    add_filter('wp_mail', __NAMESPACE__ . '\Mail::wp_mail');

    add_shortcode('user-login-form', __NAMESPACE__ . '\UserLoginForm::getOutput');

    if (is_admin()) {
      return;
    }
    // Add teaser image to RSS feeds.
    add_action('rss2_item', __NAMESPACE__ . '\Feed::rss2_item');

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
   * Wrap oEmbed output for styling purposes.
   */
  public static function embed_oembed_html($html, $url, $attr, $post_id) {
    return '<div class="oembed">' . $html . '</div>';
  }

  /**
   * @implements upload_mimes
   */
  public static function upload_mime_types(array $mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
  }

  /**
   * Removes SVG image size attributes from the content.
   *
   * @implements the_content
   */
  public static function the_content($content) {
    $content = preg_replace_callback('@<img [^>]*src=[^>]+?.svg[^>]*>@', function ($matches) {
      return static::removeSizeAttributes($matches[0]);
    }, $content);
    return $content;
  }

  /**
   * Removes size attributes from SVG images.
   *
   * @implements wp_get_attachment_metadata
   * @implements image_send_to_editor
   * @implements post_thumbnail_html
   */
  public static function removeSvgSizeAttributes($data, $post_id, $attachment_id = NULL) {
    if (!$data || get_post_mime_type($attachment_id ?? $post_id) !== 'image/svg+xml') {
      return $data;
    }
    // For wp_get_attachment_metadata, $data is an array.
    if (is_array($data)) {
      unset($data['width'], $data['height']);
    }
    else {
      $data = static::removeSizeAttributes($data);
    }
    return $data;
  }

  /**
   * Removes size attributes from the given HTML string.
   */
  public static function removeSizeAttributes($html) {
    return preg_replace('@(?:width|height)="1"\s+@', '', $html);
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
