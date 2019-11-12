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
    $file_handle = fopen($log_file, 'a');
    fwrite($file_handle, json_encode($message) . "\r\n");
    fclose($file_handle);
    File::chmod($log_file);
  }

}
