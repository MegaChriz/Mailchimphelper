<?php

namespace Drupal\mailchimphelper\Mailchimp;

trait CacheTrait {

  /**
   * Returns cached data.
   *
   * @param string $cid
   *   The cache item to get.
   *
   * @return mixed|null
   *   The cached data or null when there's no cached data.
   */
  protected function cacheGet($cid) {
    $cached_data = \Drupal::cache('mailchimp')->get($cid);
    if ($cached_data) {
      return $cached_data->data;
    }
  }

  /**
   * Stores data in the persistent cache.
   *
   * @param string $cid
   *   The cache ID of the data to store.
   * @param mixed $data
   *   The data to store in the cache.
   */
  protected function cacheSet($cid, $data) {
    return \Drupal::cache('mailchimp')->set($cid, $data);
  }

}
