<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\Commands\UserExport.
 */

namespace Netzstrategen\CoreStandards\Commands;

class UserExport extends \WP_CLI_Command {

  /**
   * Export users into a CSV file.
   *
   * Implement the filter hook 'user_contactmethods' to control which user meta
   * fields are exported.
   *
   * The CSV file will contain the hashed passwords. Use the wrapper command
   * `wp user import-csv-raw` to import them identically into the target site.
   *
   * Note: When intended to be used with import-csv, the target site needs to
   * support the same list of additional user meta fields.
   *
   * WARNING: Even though the `wp user import-csv` command attempts to disable
   * email notifications for password and email changes by default, your site
   * may still send email notifications during import. Install the plugin
   * disable-emails to ensure no emails are sent out.
   *
   * @subcommand export-csv
   * @synopsis <file>
   */
  public function __invoke(array $args, array $options) {
    $filename = $args[0];

    $columns = NULL;
    $meta_keys = _get_additional_user_keys([]);
    $delimiter = ',';
    $enclosure = '"';
    $escape_char = '\\';

    $users = get_users([
      'count_total' => FALSE,
      'fields' => 'all_with_meta',
      // 'number' => 10,
      // 'paged' => 100,
    ]);
    array_walk($users, function (&$user) use (&$columns, $meta_keys) {
      // import-csv looks for 'none' to assign no role.
      // @see https://github.com/wp-cli/entity-command/blob/master/src/User_Command.php#L938-L939
      $role = isset($user->roles[0]) ? $user->roles[0] : 'none';
      $user = $user->to_array();
      $user['role'] = $role;
      foreach ($meta_keys as $meta_key) {
        $meta[$meta_key] = get_user_meta($user['ID'], $meta_key, TRUE);
      }
      unset($user['ID']);
      if (!isset($columns)) {
        $columns = array_merge(array_keys($user), $meta_keys);
      }
      $user += $meta;
    });

    $file = fopen($filename, 'w');
    fputcsv($file, $columns, $delimiter, $enclosure, $escape_char);
    foreach ($users as $user) {
      fputcsv($file, $user, $delimiter, $enclosure, $escape_char);
    }
    \WP_CLI::success(sprintf("Exported %d users into %s.", count($users), $filename));
  }

}
