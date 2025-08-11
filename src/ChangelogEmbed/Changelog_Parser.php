<?php
/**
 * Changelog Parser.
 *
 * @since 2.0.0
 *
 * @package StellarWP\ChangelogEmbed
 */

namespace StellarWP\ChangelogEmbed;

/**
 * Handles parsing of changelog files in WordPress plugin format.
 *
 * @since 2.0.0
 */
class Changelog_Parser {
	/**
	 * Parse changelog content.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content      Raw changelog content.
	 * @param int    $max_versions Maximum number of versions to return. Default is 5.
	 *
	 * @return array Parsed changelog data.
	 */
	public function parse( string $content, int $max_versions = 5 ): array {
		// Initialize variables.
		$changelog_data  = [];
		$current_version = null;

		// Split content into lines.
		$lines = explode( "\n", $content );

		foreach ( $lines as $line ) {
			$line = trim( $line );

			// Skip empty lines.
			if ( empty( $line ) ) {
				continue;
			}

			// Check for version headers (e.g., "= [4.21.1] =", "= [4.21.1] 2025-08-07 =").
			if ( preg_match( '/^=\s*\[([^\]]+)\]\s*([^=]+)?\s*=$/i', $line, $matches ) ) {
				// If we've hit the max versions, stop processing.
				if ( count( $changelog_data ) >= $max_versions ) {
					break;
				}

				$version = $matches[1];
				// Extract date if available (sometimes it's added after the version).
				$date = isset( $matches[2] ) ? trim( $matches[2] ) : '';

				$current_version = [
					'version'  => $version,
					'date'     => $date,
					'isLatest' => count( $changelog_data ) === 0, // First one is latest.
					'changes'  => [],
				];

				$changelog_data[] = $current_version;
				continue;
			}

			// Process change entries (e.g., "* Fix - Fixed an issue...").
			if ( $current_version !== null &&
				preg_match( '/^\*\s*([\w\-]+)\s*-\s*(.+)$/i', $line, $matches ) ) {

				$type    = trim( $matches[1] );
				$content = trim( $matches[2] );

				$content = $this->escape_shortcodes( $content );
				$content = $this->escape_code_blocks( $content );
				$content = $this->convert_bold_text( $content );
				$content = $this->convert_italic_text( $content );

				$change = [
					'type'    => $type,
					'content' => $content,
				];

				// Add to current version's changes.
				$index                                 = count( $changelog_data ) - 1;
				$changelog_data[ $index ]['changes'][] = $change;
			}
		}

		return $changelog_data;
	}

	/**
	 * Escapes registered shortcodes that are used in the content.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $content Content to escape.
	 *
	 * @return string          Escaped content.
	 */
	private function escape_shortcodes( string $content ): string {
		global $shortcode_tags;

		$registered_shortcodes = array_map(
			function ( $tag ) {
				return preg_quote( $tag, '/' );
			},
			array_keys( $shortcode_tags )
		);

		return (string) preg_replace(
			'/\[(' . implode( '|', $registered_shortcodes ) . ')\]/',
			'[[$1]]',
			$content
		);
	}

	/**
	 * Escapes code blocks in the content and wraps them in <code> tags.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $content Content to escape.
	 *
	 * @return string          Escaped content.
	 */
	private function escape_code_blocks( string $content ): string {
		return (string) preg_replace_callback(
			'/`{1,3}([^`]+)`{1,3}/',
			function ( $matches ) {
				$is_multiline = strpos( $matches[0], '```' ) !== false;
				$code         = esc_html( trim( $matches[1] ) );

				if ( $is_multiline ) {
					$code = preg_replace( '/\n/', '<br />', $code );

					return "\n\n" . '<code class="stellar-changelog-embed__code stellar-changelog-embed__code--multiline">' . $code . '</code>' . "\n\n";
				}

				return '<code class="stellar-changelog-embed__code">' . $code . '</code>';
			},
			$content
		);
	}

	/**
	 * Converts bold text in the content to <strong> tags.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $content Content to convert.
	 *
	 * @return string          Converted content.
	 */
	private function convert_bold_text( string $content ): string {
		return (string) preg_replace(
			'/\*\*([^\*]+)\*\*/',
			'<strong>$1</strong>',
			$content
		);
	}

	/**
	 * Converts italic text styled as *italic* in the content to <em> tags.
	 * _italic_ text is not converted, as attempting to account for this would be too complex as that pattern would often have false positives with things like action and hook names.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $content Content to convert.
	 *
	 * @return string          Converted content.
	 */
	private function convert_italic_text( string $content ): string {
		return (string) preg_replace(
			'/(?<!\*)\*([^\*]+)\*(?!\*)/',
			'<em>$1</em>',
			$content
		);
	}
}
