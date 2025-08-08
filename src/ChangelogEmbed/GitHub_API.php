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
 *
 * @uses apply_filters() Calls 'stellar_changelog_embed_supported_file_types' to filter
 *                      the list of supported file extensions for changelog files.
 *                      Default: ['txt', 'md', 'markdown']
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

		// Validate file type.
		$file_extension = pathinfo( $file_path, PATHINFO_EXTENSION );

		/**
		 * Filters the list of supported file extensions for changelog files.
		 *
		 * @since 2.0.0
		 *
		 * @param array $supported_types Array of supported file extensions (without dots). Default: 'txt' only.
		 *
		 * @return array Modified array of supported file extensions.
		 */
		$supported_types = apply_filters(
			'stellar_changelog_embed_supported_file_types',
			[ 'txt' ]
		);

		if ( ! in_array( strtolower( $file_extension ), $supported_types, true ) ) {
			return new WP_Error(
				'unsupported_file_type',
				sprintf(
					/* translators: %1$s: File extension. */
					__( 'Unsupported file type "%1$s"', 'stellar-changelog-embed' ),
					$file_extension
				)
			);
		}

		// Build request URL.
		$url = sprintf(
			'%s/repos/%s/%s/contents/%s?ref=%s',
			$this->api_base_url,
			rawurlencode( $owner ),
			rawurlencode( $repo ),
			rawurlencode( $file_path ),
			rawurlencode( $branch )
		);

		// Set up request arguments.
		$args = [
			'headers' => [
				'Accept'     => 'application/vnd.github.v4.raw',
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
					/* translators: %1$d: HTTP response code, %2$s: Response message */
					__( 'GitHub API error (HTTP %1$d): %2$s', 'stellar-changelog-embed' ),
					$response_code,
					wp_remote_retrieve_response_message( $response )
				)
			);
		}

		// Return the response body.
		return wp_remote_retrieve_body( $response );
	}
}
