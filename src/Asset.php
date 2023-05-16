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
    // Skip scripts loaded from remote URLs.
    if (strpos($src, site_url()) !== 0) {
      return $tag;
    }

    if ($git_version = self::getGitCommitHash()) {
      if (strpos($src, '?') !== FALSE) {
        if (preg_match('@[?&](ver=[^&$]*)@', $src, $matches)) {
          $new_src = str_replace($matches[1], 'ver=' . $git_version, $src);
        }
        else {
          $new_src = $src . '&ver=' . $git_version;
        }
      }
      else {
        $new_src = $src . '?ver=' . $git_version;
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
