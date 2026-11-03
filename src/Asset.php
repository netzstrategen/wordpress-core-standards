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
    return str_replace($src, self::versionUrl($src), $tag);
  }

  /**
   * Replaces default version to Git reference in preload hint URLs.
   *
   * Without this, a preloaded asset URL differs from the URL of its script or
   * style tag after loader_tag(), so the browser discards the preloaded file
   * and downloads the asset a second time.
   *
   * @implements wp_preload_resources
   */
  public static function wp_preload_resources($resources) {
    foreach ($resources as $key => $resource) {
      if (isset($resource['href'])) {
        $resources[$key]['href'] = self::versionUrl($resource['href']);
      }
    }
    return $resources;
  }

  /**
   * Replaces default version to Git reference in a local asset URL.
   *
   * @param string $src
   *   The asset URL.
   *
   * @return string
   *   The asset URL with the version updated.
   */
  private static function versionUrl($src) {
    // Skip scripts loaded from remote URLs.
    if (strpos($src, site_url()) !== 0) {
      return $src;
    }
    // Skip assets that are already versioned.
    // - Elementor post/template CSS uses last modification timestamp.
    // - Stripe UPE blocks use settings version.
    if (str_contains($src, 'elementor/css/') || str_contains($src, 'upe-blocks.css')) {
      return $src;
    }

    if ($git_version = self::getGitCommitHash()) {
      if (strpos($src, '?') !== FALSE) {
        if (preg_match('@[?&](ver=[^&$]*)@', $src, $matches)) {
          return str_replace($matches[1], 'ver=' . $git_version, $src);
        }
        return $src . '&ver=' . $git_version;
      }
      return $src . '?ver=' . $git_version;
    }
    return $src;
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
