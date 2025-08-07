<?php
/**
 * Changelog Parser.
 * 
 * TODO: Test it with multiple log files from different repositories.
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
		$changelog_data = [];
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
			if ( preg_match( '/^=\s*\[([^\]]+)\]\s*(?:(?:[^=])+)? =$/i', $line, $matches ) ) {
				// If we've hit the max versions, stop processing.
				if ( count( $changelog_data ) >= $max_versions ) {
					break;
				}

				$version = $matches[1];
				// Extract date if available (sometimes it's added after the version).
				$date = isset( $matches[2] ) ? trim( $matches[2] ) : '';

				$current_version = [
					'version'   => $version,
					'date'      => $date,
					'isLatest'  => count( $changelog_data ) === 0, // First one is latest.
					'changes'   => [],
				];

				$changelog_data[] = $current_version;
				continue;
			}

			// Process change entries (e.g., "* Fix - Fixed an issue...").
			if ( $current_version !== null && 
				preg_match( '/^\*\s*([\w\-]+)\s*-\s*(.+)$/i', $line, $matches ) ) {

				$type = trim( $matches[1] );
				$content = trim( $matches[2] );

				$change = [
					'type'    => $type,
					'content' => $content,
				];

				// Add to current version's changes.
				$index = count( $changelog_data ) - 1;
				$changelog_data[ $index ]['changes'][] = $change;
			}
		}

		return $changelog_data;
	}
}
