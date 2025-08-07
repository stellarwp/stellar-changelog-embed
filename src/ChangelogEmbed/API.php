<?php
/**
 * REST API Integration.
 *
 * @since 2.0.0
 *
 * @package StellarWP\ChangelogEmbed
 */

namespace StellarWP\ChangelogEmbed;

/**
 * Handles REST API endpoints for the plugin.
 *
 * @since 2.0.0
 */
class API {
	/**
	 * Registers REST API routes.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		register_rest_route(
			'stellar-changelog-embed/v1',
			'/changelog',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_changelog' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'owner'        => [
						'required'          => true,
						'description'       => __( 'GitHub_API repository owner', 'stellar-changelog-embed' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'repo'         => [
						'required'          => true,
						'description'       => __( 'GitHub_API repository name', 'stellar-changelog-embed' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'path'         => [
						'required'          => false,
						'default'           => 'changelog.txt',
						'description'       => __( 'Path to changelog file in repository', 'stellar-changelog-embed' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'branch'       => [
						'required'          => false,
						'default'           => 'main',
						'description'       => __( 'Repository branch', 'stellar-changelog-embed' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'max_versions' => [
						'required'          => false,
						'default'           => 5,
						'description'       => __( 'Maximum versions to return', 'stellar-changelog-embed' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
	}

	/**
	 * Gets changelog data.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Request $request REST API request.
	 *
	 * @return \WP_REST_Response|\WP_Error Changelog data or error.
	 */
	public static function get_changelog( \WP_REST_Request $request ) {
		// Get request parameters.
		$owner        = $request->get_param( 'owner' );
		$repo         = $request->get_param( 'repo' );
		$path         = $request->get_param( 'path' );
		$branch       = $request->get_param( 'branch' );
		$max_versions = $request->get_param( 'max_versions' );

		// Get GitHub_API API instance.
		$github_api = new GitHub_API();

		// Fetch changelog content.
		$changelog_content = $github_api->get_file_content( $owner, $repo, $path, $branch );

		if ( is_wp_error( $changelog_content ) ) {
			return rest_ensure_response( $changelog_content );
		}

		// Parse changelog.
		$changelog_parser = new Changelog_Parser();
		$changelog_data   = $changelog_parser->parse( $changelog_content, $max_versions );

		return rest_ensure_response( $changelog_data );
	}
}
