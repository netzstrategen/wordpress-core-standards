<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\File.
 */

namespace Netzstrategen\CoreStandards;

/**
 * File-related helper functionality.
 */
class File {

  /**
   * Sets permissions for a file or directory.
   *
   * Inherits permissions from the respective parent directory, using a
   * different mask for files and directories to ensure that files created by
   * WordPress can be read and changed by the file's system group.
   *
   * @param string $pathname
   *   Path to the file or directory to change.
   */
  public static function chmod($pathname) {
    if (is_dir($pathname)) {
      $stat = stat(dirname($pathname));
      $perms = $stat['mode'] & 0007777;
      $perms = $perms & 0000775;
      chmod($pathname, $perms);
      clearstatcache();
    }
    elseif (is_file($pathname)) {
      $stat = stat(dirname($pathname));
      $perms = $stat['mode'] & 0000664;
      chmod($pathname, $perms);
      clearstatcache();
    }
  }

}
