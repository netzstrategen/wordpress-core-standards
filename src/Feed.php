<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Feed.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Enhances feeds.
 */
class Feed {

  /**
   * Adds a teaser image for each post in RSS feeds.
   *
   * RSS items in WordPress do not contain images by default, even though they
   * are supported by the RSS2 standard.
   *
   * @see https://validator.w3.org/feed/docs/rss2.html#ltenclosuregtSubelementOfLtitemgt
   */
  public static function rss2_item() {
    global $post;

    $attachment_id = get_post_thumbnail_id($post->ID);
    if (!$attachment_id) {
      return;
    }
    $image_size = 'post-thumbnail';
    $path = wp_upload_dir()['basedir'] . '/' . image_get_intermediate_size($attachment_id , $image_size)['path'];
    $url = wp_get_attachment_image_src($attachment_id, $image_size);
    if (!file_exists($path) || !isset($url[0])) {
      return;
    }
    $url = $url[0];
    $filesize = filesize($path);
    $mime_type = get_post_mime_type($attachment_id);
?>
        <enclosure url="<?= esc_url($url) ?>" length="<?= esc_attr($filesize) ?>" type="<?= esc_attr($mime_type) ?>" />
<?php
  }

}
