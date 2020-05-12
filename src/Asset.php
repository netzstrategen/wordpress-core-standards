<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Asset.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Asset-handling functionality.
 */
class Asset {

  /**
   * The current commit hash.
   *
   * @var string
   */
  private static $gitVersion;

  /**
   * Replaces default version to Git reference in script URL.
   *
   * @return string
   *   The tag to print with the version updated.
   */
  public static function loader_tag($tag, $handle, $src) {
    $wp_scripts = wp_scripts();
    $default_version = $wp_scripts->default_version;

    if ($git_version = self::getGitCommitHash()) {
      $parsed_src = parse_url($src);
      if (!empty($parsed_src['query'])) {
        parse_str($parsed_src['query'], $get_args);
        $get_args['ver'] = $git_version;
        $new_src = $parsed_src['scheme'] . '://' . $parsed_src['host'] . $parsed_src['path'] . '?' . http_build_query($get_args);
      }
      else {
        $new_src = add_query_arg('ver', $git_version, $src);
      }
      $tag = str_replace($src, $new_src, $tag);
    }
    return $tag;
  }

  /**
   * Gets the reference of the last commit.
   *
   * Helper function to handle asset version with wp_enqueue_*
   * functions to bust caches.
   *
   * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
   *
   * @return string|null
   *   The first 8 characters of the git reference.
   */
  public static function getGitCommitHash() {
    // Reuse the value if already calculated.
    if (isset(self::$gitVersion)) {
      return self::$gitVersion;
    }

    if (is_dir(ABSPATH . '.git')) {
      $ref = trim(file_get_contents(ABSPATH . '.git/HEAD'));
      if (strpos($ref, 'ref:') === 0) {
        $ref = substr($ref, 5);
        if (file_exists(ABSPATH . '.git/' . $ref)) {
          $ref = trim(file_get_contents(ABSPATH . '.git/' . $ref));
        }
        else {
          $ref = substr($ref, 11);
        }
      }
      self::$gitVersion = substr($ref, 0, 8);
    }
    else {
      self::$gitVersion = 0;
    }

    return self::$gitVersion;
  }

}
