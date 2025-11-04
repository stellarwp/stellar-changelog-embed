<?php
/**
 * Helper functions for the plugin.
 *
 * @since 2.0.0
 *
 * @package StellarWP\ChangelogEmbed
 */

namespace StellarWP\ChangelogEmbed;

/**
 * Helper class containing utility functions.
 *
 * @since 2.0.0
 */
class Helper {
	/**
	 * Properly pluralize change type names.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The change type (e.g., 'Fix', 'Feature').
	 *
	 * @return string The pluralized version.
	 */
	public static function pluralize_type( string $type ): string {
		$plurals = [
			__( 'Addition', 'stellar-changelog-embed' )    => __( 'Additions', 'stellar-changelog-embed' ),
			__( 'Change', 'stellar-changelog-embed' )      => __( 'Changes', 'stellar-changelog-embed' ),
			__( 'Deprecation', 'stellar-changelog-embed' ) => __( 'Deprecations', 'stellar-changelog-embed' ),
			__( 'Enhancement', 'stellar-changelog-embed' ) => __( 'Enhancements', 'stellar-changelog-embed' ),
			__( 'Feature', 'stellar-changelog-embed' )     => __( 'Features', 'stellar-changelog-embed' ),
			__( 'Fix', 'stellar-changelog-embed' )         => __( 'Fixes', 'stellar-changelog-embed' ),
			__( 'Improvement', 'stellar-changelog-embed' ) => __( 'Improvements', 'stellar-changelog-embed' ),
			__( 'Performance', 'stellar-changelog-embed' ) => __( 'Performance', 'stellar-changelog-embed' ),
			__( 'Removal', 'stellar-changelog-embed' )     => __( 'Removals', 'stellar-changelog-embed' ),
			__( 'Security', 'stellar-changelog-embed' )    => __( 'Security', 'stellar-changelog-embed' ),
			__( 'Tweak', 'stellar-changelog-embed' )       => __( 'Tweaks', 'stellar-changelog-embed' ),
			__( 'Update', 'stellar-changelog-embed' )      => __( 'Updates', 'stellar-changelog-embed' ),
		];

		/**
		 * Filters the plural forms for change types.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $plurals Array of singular => plural mappings.
		 * @param string $type    The change type being pluralized.
		 *
		 * @return array Modified array of plural forms.
		 */
		$plurals = apply_filters( 'stellar_changelog_embed_type_plurals', $plurals, $type );

		// Return the predefined plural if it exists, otherwise add 's'.
		return isset( $plurals[ $type ] )
			? $plurals[ $type ]
			: $type . 's';
	}
}
