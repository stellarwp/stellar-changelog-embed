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

		return set_transient( $cache_key, $content, $duration );
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

		return delete_transient( $cache_key );
	}

	/**
	 * Clears all plugin cache.
	 * 
	 * TODO: Test it later.
	 *
	 * @since 2.0.0
	 *
	 * @return int Number of cache items cleared.
	 */
	public function clear_all(): int {
		global $wpdb;

		// Get all matching transients.
		$transients = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
				'_transient_' . $this->cache_prefix . '%'
			)
		);

		$count = 0;

		// Delete each transient using WordPress API for compatibility.
		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient );
			if ( delete_transient( $transient_name ) ) {
				$count++;
			}
		}

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
} 
