<?php

namespace StellarWP\ChangelogEmbed;

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
		// Get the URL from the block attributes
		$changelog_url = isset( $attributes['changelogUrl'] ) ? esc_url_raw( $attributes['changelogUrl'] ) : '';

		// If no URL is provided, display a message
		if ( empty( $changelog_url ) ) {
			return '';
		}

		// Fetch the contents of the text file
		$response = wp_remote_get( $changelog_url );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$body = wp_remote_retrieve_body( $response );

		// Replace each backtick-enclosed text with a <code> tag.
		$body = preg_replace('/`([^`]+)`/', '<code>$1</code>', $body );

		// Make headings <h3> tags.
		$body = preg_replace('/= \[(\d+\.\d+\.\d+)\] =/', "\n<h3>$1</h3>", $body);

		// Replace each asterisk at the beginning of a line with a list item <li>.
		$body = preg_replace('/^\* (.+)/m', '<li>$1</li>', $body);

		// Wrap all groups of list items in an unordered list <ul>.
		$body = preg_replace('/(<li>.+<\/li>\n)+/', '<ul>$0</ul>', $body);

		// Return the contents of the text file.
		return wp_kses_post( $body );
	}
}
