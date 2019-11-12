<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Logger.
 */

namespace Netzstrategen\CoreStandards;

class Logger {

  const LOG_FILE = 'cookie-consent.log';

  public static function getLogFile(): string {
    $log_file = apply_filters('core-standards/cookie-consent/logfile', wp_upload_dir()['path'] . '/cookie-consent.log');
    return $log_file;
  }

  public static function writelog($message, $level = 'info') {
    $log_file = static::getLogFile();
    $file_handle = fopen($log_file, 'a');
    fwrite($file_handle, json_encode($message) . "\r\n");
    fclose($file_handle);
    File::chmod($log_file);
  }

}
