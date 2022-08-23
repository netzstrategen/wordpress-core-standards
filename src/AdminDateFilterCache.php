<?php

/**
 * @file
 * Contains \Netzstrategen\CoreStandards\AdminDateFilterCache.
 *
 * @thanks https://github.com/Automattic/vip-go-mu-plugins-built/blob/master/performance/vip-tweaks.php
 */

namespace Netzstrategen\CoreStandards;

class AdminDateFilterCache {

  /**
   * Caches an expensive query for available months/years in media library filters.
   *
   * @implements media_library_months_with_files
   */
  public static function media_library_months_with_files($months) {
    // Abort if something else is customizing the value.
    if (isset($months)) {
      return $months;
    }
    return static::getAvailableMonths('attachment');
  }

  /**
   * Caches an expensive query for available months/years in posts filters.
   *
   * @implements pre_months_dropdown_query
   */
  public static function pre_months_dropdown_query($months, $post_type) {
    // Abort if something else is customizing the value.
    if (isset($months)) {
      return $months;
    }
    return static::getAvailableMonths($post_type);
  }

  /**
   * Caches results of available months/years for a given post type.
   *
   * @param string $post_type
   *   The post type for which to look up available months/years.
   *
   * @return object[]
   *
   * @see \WP_List_Table::months_dropdown()
   * @see wp_enqueue_media()
   */
  public static function getAvailableMonths($post_type) {
    global $wpdb;

    $months = wp_cache_get($post_type, 'admin_date_filter');
    if (is_array($months)) {
      return $months;
    }

    $where_post_status = '';
    if ($post_type !== 'attachment') {
      $where_post_status = "AND post_status NOT IN ('auto-draft', 'trash')";
    }

    $months = $wpdb->get_results($wpdb->prepare("
      SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
      FROM $wpdb->posts
      WHERE post_type = %s $where_post_status
      ORDER BY post_date DESC
    ", $post_type));

    wp_cache_set($post_type, $months, 'admin_date_filter', DAY_IN_SECONDS);
    return $months;
  }

  /**
   * Invalidates the cache for a post type when a new month is added.
   *
   * Only invalidates the cache when a new month is added. For deletions, the
   * cache will just expire after a day.
   *
   * @param string $post_id
   *   The post ID to add.
   */
  public static function invalidateCacheByPost($post_id) {
    $post_type = get_post_type($post_id);
    $months = wp_cache_get($post_type, 'admin_date_filter');
    if (!is_array($months)) {
      return false;
    }

    $year  = get_the_time('Y', $post_id);
    $month = get_the_time('n', $post_id);

    foreach ($months as $date) {
      if ($year === $date->year && $month === $date->month) {
        // The month/year option already exists, nothing to do.
        return;
      }
    }
    // The option does not exist yet, invalidate the cache.
    wp_cache_delete($post_type, 'admin_date_filter');
  }

}
