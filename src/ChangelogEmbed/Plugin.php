<?php
/**
 * Plugin class.
 *
 * @since 2.0.0
 *
 * @package StellarWP\ChangelogEmbed
 */

namespace StellarWP\ChangelogEmbed;

use WP_REST_Request;

/**
 * Plugin class.
 *
 * @since 2.0.0
 */
class Plugin {
	/**
	 * Instance of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return self
	 */
	public static function instance() {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Register the block.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register() {
		register_block_type(
			STELLAR_CHANGELOG_EMBED_DIR . '/build/modules/blocks/changelog-embed',
			[
				'render_callback' => [ $this, 'render' ],
			]
		);
	}

	/**
	 * Render the block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes The block attributes.
	 * @return string|\WP_Error
	 */
	public function render( $attributes ) {
		$request = new WP_REST_Request();

		foreach ( $attributes as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$api      = new API();
		$response = $api->get_changelog( $request );

		// TODO: Maybe switch to the template lib in a separate PR?

		if ( is_wp_error( $response ) ) {
			$error_message = sprintf(
				/* translators: %s is the error message. */
				__( 'Error fetching changelog data: %s', 'stellar-changelog-embed' ),
				$response->get_error_message()
			);

			ob_start();
			include STELLAR_CHANGELOG_EMBED_DIR . '/src/views/error.php';
			$template = (string) ob_get_clean();

			return wp_kses_post( $template );
		}

		$changelog_data = $response->get_data();

		ob_start();
		include_once STELLAR_CHANGELOG_EMBED_DIR . '/src/views/changelog.php';
		$template = (string) ob_get_clean();

		// Return the contents of the text file.
		return wp_kses_post( $template );
	}
}
