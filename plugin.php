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
	/*
	// Enqueue the block editor JavaScript
	wp_register_script(
		'swp-text-file-embed',
		plugins_url( 'block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-editor', 'wp-element', 'wp-components' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'block.js' )
	);

	// Register the block
	register_block_type( 'stellarwp/text-file-embed', array(
		'editor_script'   => 'swp-text-file-embed',
		'render_callback' => 'swp_text_file_block_render',
		'category'        => 'embed',
		'attributes'      => array(
			'textFileUrl' => array(
				'type'    => 'string',
				'default' => ''
			),
		),
	) );
	 */
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'swp_text_file_block_register' );


// Server-side rendering of the block
function swp_text_file_block_render( $attributes ) {
    // Get the URL from the block attributes
    $text_file_url = isset( $attributes['textFileUrl'] ) ? esc_url_raw( $attributes['textFileUrl'] ) : '';

    // If no URL is provided, display a message
    if ( empty( $text_file_url ) ) {
        return '<div>No URL provided.</div>';
    }

    // Fetch the contents of the text file
    $response = wp_remote_get( $text_file_url );

    if ( is_wp_error( $response ) ) {
        return '<div>Error fetching the text file.</div>';
    }

    $body = wp_remote_retrieve_body( $response );

    // Return the contents of the text file
    return '<pre>' . esc_html( $body ) . '</pre>';
}
