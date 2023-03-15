<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Hooks\Language.
 */

namespace Netzstrategen\CoreStandards\Hooks;

class Language {

  /**
   * Sets the language for the current process limiting results to specified language.
   *
   * Pass either the language locale or the literal string `all` to return
   * results for all languages. Defaults to the language set in the plugin.
   *
   * Note: Currently supporting WPML only.
   * Note: Using an env variable to avoid synopsis parser validation errors.
   *
   * ### EXAMPLES
   *
   *    WPLANG=en wp post list
   *    WPLANG=all wp post list
   *
   * @param array $args
   * @param array $assoc_args
   * @param array $options
   *
   * @return array
   */
  public static function limitLanguage(array $args, array $assoc_args, array $options): array {
    global $sitepress;
    if ($sitepress && $lang = getenv('WPLANG') ?? '') {
      $sitepress->switch_lang($lang, TRUE);
    }
    return $args;
  }
}
