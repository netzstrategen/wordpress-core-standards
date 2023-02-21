<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Hooks\MultilingualSupport.
 */

namespace Netzstrategen\CoreStandards\Hooks;

class MultilingualSupport {

  /**
   * Changes the command language to return entities of matching language only.
   *
   * Currently only supporting WPML.
   *
   * @param array $args
   * @param array $assoc_args
   * @param array $options
   *
   * @return array
   */
  public static function changeLanguage(array $args, array $assoc_args, array $options): array {
    global $sitepress;
    if ($sitepress && $lang = $assoc_args['lang'] ?? '') {
      $sitepress->switch_lang($lang, TRUE);
    }
    return $args;
  }
}
