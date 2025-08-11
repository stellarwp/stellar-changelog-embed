<?php
/**
 * Cache Management.
 *
 * @since 2.0.0
 *
 * @package StellarWP\ChangelogEmbed
 */

namespace StellarWP\ChangelogEmbed;

/**
 * Handles caching functionality for the plugin.
 *
 * @since 2.0.0
 */
class Cache {
	/**
	 * Cache key prefix.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private string $cache_prefix = 'stellar_changelog_embed_';

	/**
	 * Gets cached content.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Cache key.
	 *
	 * @return mixed|false Cached content or false if not found.
	 */
	public function get( string $key ) {
		$cache_key = $this->get_cache_key( $key );

		return get_transient( $cache_key );
	}

	/**
	 * Sets cached content.
	 *
	 * @since 2.0.0
	 *
	 * @param string   $key      Cache key.
	 * @param mixed    $content  Content to cache.
	 * @param int|null $duration Cache duration in seconds. Default is null (uses default duration).
	 *
	 * @return bool True on success, false on failure.
	 */
	public function set( string $key, $content, ?int $duration = null ): bool {
		$cache_key = $this->get_cache_key( $key );
		$duration  = $duration ?? $this->get_cache_duration();

		$result = set_transient( $cache_key, $content, $duration );

		// Always maintain a list of our cache keys for clearing.
		if ( $result ) {
			$this->add_to_cache_keys_list( $cache_key );
		}

		return $result;
	}

	/**
	 * Deletes cached content.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Cache key.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete( string $key ): bool {
		$cache_key = $this->get_cache_key( $key );

		$result = delete_transient( $cache_key );

		// Always remove from our keys list.
		if ( $result ) {
			$this->remove_from_cache_keys_list( $cache_key );
		}

		return $result;
	}

	/**
	 * Clears all plugin cache.
	 *
	 * @since 2.0.0
	 *
	 * @return int Number of cache items cleared.
	 */
	public function clear_all(): int {
		$count = 0;

		// Get the list of all our cache keys.
		$cache_keys_list = get_transient( $this->cache_prefix . 'keys_list' );
		
		if ( is_array( $cache_keys_list ) ) {
			foreach ( $cache_keys_list as $key ) {
				if ( delete_transient( $key ) ) {
					++$count;
				}
			}
		}
		
		// Clear the keys list itself.
		delete_transient( $this->cache_prefix . 'keys_list' );

		return $count;
	}

	/**
	 * Gets the cache key with prefix.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Cache key.
	 *
	 * @return string Full cache key with prefix.
	 */
	private function get_cache_key( string $key ): string {
		return $this->cache_prefix . md5( $key );
	}

	/**
	 * Gets the current cache duration.
	 *
	 * @since 2.0.0
	 *
	 * @return int Cache duration in seconds.
	 */
	public function get_cache_duration(): int {
		/**
		 * Filters the cache duration.
		 *
		 * @since 2.0.0
		 *
		 * @param int $default_cache_duration The default cache duration in seconds. Default is 1 hour.
		 *
		 * @return int Cache duration in seconds.
		 */
		return apply_filters( 'stellar_changelog_embed_cache_duration', HOUR_IN_SECONDS );
	}

	/**
	 * Adds a cache key to the list for cache clearing.
	 *
	 * @since 2.0.0
	 *
	 * @param string $cache_key The cache key to add.
	 *
	 * @return void
	 */
	private function add_to_cache_keys_list( string $cache_key ): void {
		$keys_list_key = $this->cache_prefix . 'keys_list';
		$keys_list     = get_transient( $keys_list_key );

		if ( ! is_array( $keys_list ) ) {
			$keys_list = [];
		}

		// Add the key if it's not already in the list.
		if ( ! in_array( $cache_key, $keys_list, true ) ) {
			$keys_list[] = $cache_key;

			// Store the list with the same duration as individual cache items.
			set_transient( $keys_list_key, $keys_list, $this->get_cache_duration() );
		}
	}

	/**
	 * Removes a cache key from the list for cache clearing.
	 *
	 * @since 2.0.0
	 *
	 * @param string $cache_key The cache key to remove.
	 *
	 * @return void
	 */
	private function remove_from_cache_keys_list( string $cache_key ): void {
		$keys_list_key = $this->cache_prefix . 'keys_list';
		$keys_list     = get_transient( $keys_list_key );

		if ( is_array( $keys_list ) ) {
			$keys_list = array_diff( $keys_list, [ $cache_key ] );

			set_transient( $keys_list_key, $keys_list, $this->get_cache_duration() );
		}
	}
}
