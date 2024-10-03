<?php
/**
 * Plugin Name: Stellar Text Embed Block
 * Description: A block that displays the content of a text file from a user-specified URL.
 * Version: 1.0
 * Author: StellarWP
 * License: GPLv2 or later
 */

// Register the block and enqueue the block JavaScript
function swp_text_file_block_register() {
	register_block_type(
		__DIR__ . '/build',
		[
			'render_callback' => 'swp_text_file_block_render',
		]
	);
}

add_action( 'init', 'swp_text_file_block_register' );

// Server-side rendering of the block
function swp_text_file_block_render( $attributes ) {
	// Get the URL from the block attributes
	$text_file_url = isset( $attributes['textFileUrl'] ) ? esc_url_raw( $attributes['textFileUrl'] ) : '';

	// If no URL is provided, display a message
	if ( empty( $text_file_url ) ) {
		return '';
	}

	// Fetch the contents of the text file
	$response = wp_remote_get( $text_file_url );

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
