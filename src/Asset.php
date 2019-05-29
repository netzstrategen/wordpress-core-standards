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

    return str_replace('?ver=' . $default_version, '?ver=' . self::getGitCommitHash(), $tag);
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
    if (self::$gitVersion != NULL) {
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

    return self::$gitVersion;
  }

}
