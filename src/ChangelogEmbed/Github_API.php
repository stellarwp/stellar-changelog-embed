<?php
/**
 * GitHub API Integration.
 *
 * @since 2.0.0
 *
 * @package StellarWP\ChangelogEmbed
 */

namespace StellarWP\ChangelogEmbed;

use WP_Error;

/**
 * Handles interactions with the GitHub API.
 *
 * @since 2.0.0
 */
class GitHub_API {
	/**
	 * GitHub API base URL.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private string $api_base_url = 'https://api.github.com';

	/**
	 * Get file content from GitHub repository.
	 * 
	 * TODO: Test with public and non-public (with a token and without a token) repositories.
	 *
	 * @since 2.0.0
	 *
	 * @param string $owner     Repository owner/organization.
	 * @param string $repo      Repository name.
	 * @param string $file_path Path to the file within the repository.
	 * @param string $branch    Branch name. Default is 'main'.
	 *
	 * @return string|WP_Error File content or error.
	 */
	public function get_file_content( string $owner, string $repo, string $file_path, string $branch = 'main' ) {
		// Validate inputs.
		if (
			empty( $owner )
			|| empty( $repo )
			|| empty( $file_path )
		) {
			return new WP_Error(
				'missing_params',
				__( 'Missing required parameters for GitHub API request.', 'stellar-changelog-embed' )
			);
		}

		// Create cache key.
		$cache_key = 'stellar_changelog_embed_' . md5( $owner . '_' . $repo . '_' . $file_path . '_' . $branch );

		// Check for cached content.
		$cached_content = get_transient( $cache_key );
		if ( false !== $cached_content ) {
			return $cached_content;
		}

		// Build request URL.
		$url = sprintf(
			'%s/repos/%s/%s/contents/%s?ref=%s',
			$this->api_base_url,
			urlencode( $owner ),
			urlencode( $repo ),
			urlencode( $file_path ),
			urlencode( $branch )
		);

		// Set up request arguments.
		$args = [
			'headers' => [
				'Accept'     => 'application/vnd.github.v3.raw',
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			],
			'timeout' => 30,
		];

		// Add authentication if token is set.
		$github_token = get_option( 'stellar_changelog_embed_github_token', '' );

		if ( ! empty( $github_token ) ) {
			$args['headers']['Authorization'] = 'token ' . $github_token;
		}

		// Make the request.
		$response = wp_remote_get( $url, $args );

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Check response code.

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			return new WP_Error(
				'github_api_error',
				sprintf(
					__( 'GitHub API error (HTTP %1$d): %2$s', 'stellar-changelog-embed' ),
					$response_code,
					wp_remote_retrieve_response_message( $response )
				)
			);
		}

		// Get response body.
		$content = wp_remote_retrieve_body( $response );

		// Cache the result with configurable duration.
		set_transient( $cache_key, $content, $this->get_cache_duration() );

		return $content;
	}

	/**
	 * Clears the plugin's cache.
	 * 
	 * TODO: Test it later.
	 *
	 * @since 2.0.0
	 *
	 * @return int Number of cache items cleared.
	 */
	public function clear_cache(): int {
		global $wpdb;

		// Get all matching transients.
		$transients = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
				'_transient_stellar_changelog_embed_%'
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
		return apply_filters( 'wp_changelog_viewer_cache_duration', HOUR_IN_SECONDS );
	}
}
