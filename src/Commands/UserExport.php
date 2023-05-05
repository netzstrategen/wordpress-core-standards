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
   * Implement the filter hook 'user_contactmethods' to add user meta fields.
   * ```
   * add_filter('user_contactmethods', fn() => [
   *   'alg_wc_ev_is_activated' => 1,
   * ]);
   * ```
   *
   * Implement the filter hook 'wp user export-csv keys' to control which user meta
   * fields are exported.
   * ```
   * add_filter('wp user export-csv keys', fn() => [
   *   'ID' => 1,
   *   'user_login' => 1,
   *   'user_email' => 1,
   *   'user_pass' => 1,
   *   'user_registered' => 1,
   *   'alg_wc_ev_is_activated' => 1,
   *   'role' => 1,
   * ]);
   * ```
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
      $role = !empty($user->roles) ? implode(',', $user->roles) : 'none';
      $user = $user->to_array();
      $user['role'] = $role;
      foreach ($meta_keys as $meta_key) {
        $meta[$meta_key] = get_user_meta($user['ID'], $meta_key, TRUE);
        if (is_array($meta[$meta_key])) {
          $meta[$meta_key] = implode(',', $meta[$meta_key]);
        }
        elseif (!is_string($meta[$meta_key])) {
          $meta[$meta_key] = json_encode($meta[$meta_key], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
      }
      $user += $meta;
      if (!isset($export_keys)) {
        $export_keys = apply_filters('wp user export-csv keys', $user);
      }
      $user = array_intersect_key($user, $export_keys);
      if (!isset($columns)) {
        $columns = array_keys($user);
      }
    });

    $file = fopen($filename, 'w');
    fputcsv($file, $columns, $delimiter, $enclosure, $escape_char);
    foreach ($users as $user) {
      fputcsv($file, $user, $delimiter, $enclosure, $escape_char);
    }
    \WP_CLI::success(sprintf("Exported %d users into %s.", count($users), $filename));
  }

}
