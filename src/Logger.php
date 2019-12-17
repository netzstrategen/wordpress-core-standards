<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Logger.
 */

namespace Netzstrategen\CoreStandards;

/**
 * Class for logging cookie consent actions.
 */
class Logger {

  /**
   * Returns cookie consent log file path.
   *
   * @return string
   */
  public static function getLogFile(): string {
    $log_file = apply_filters('core-standards/cookie-consent/logfile', wp_upload_dir()['path'] . '/cookie-consent.log');
    return $log_file;
  }


  /**
   * Writes the given message into the cookie consent log file.
   *
   * @param array $message
   *   The message to write into the log file.
   */
  public static function writelog(array $message) {
    $log_file = static::getLogFile();
    $message = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    file_put_contents($log_file, $message . "\n", FILE_APPEND);
    File::chmod($log_file);
  }

}
