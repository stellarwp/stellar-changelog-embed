<?php
// Get the URL from the block attributes
$text_file_url = isset( $attributes['textFileUrl'] ) ? esc_url_raw( $attributes['textFileUrl'] ) : '';

// If no URL is provided, display a message
if ( empty( $text_file_url ) ) {
		return;
}

// Fetch the contents of the text file
$response = wp_remote_get( $text_file_url );

if ( is_wp_error( $response ) ) {
		return;
}

$body = wp_remote_retrieve_body( $response );

// Replace each asterisk at the beginning of a line with a list item <li>.
$body = preg_replace('/^\* (.+)/m', '<li>$1</li>', $body);

// Add <ul> before and after the list items within a version block.
$body = preg_replace('/= \[(\d+\.\d+\.\d+)\] =/', "\n</ul>\n<h3>$1</h3>\n<ul>", $body);

// Ensure that any unclosed <ul> tags are properly closed at the end.
$body = preg_replace('/(<\/li>\n*)*(?=\n= \[\d+\.\d+\.\d+\] =|\Z)/', "\n</ul>", $body);

// Replace each backtick-enclosed text with a <code> tag.
$body = preg_replace('/`([^`]+)`/', '<code>$1</code>', $body );

// Return the contents of the text file
echo wp_kses_post( $body );
