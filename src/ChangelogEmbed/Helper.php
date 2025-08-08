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
			'Fix'         => 'Fixes',
			'Tweak'       => 'Tweaks',
			'Feature'     => 'Features',
			'Security'    => 'Security',
			'Performance' => 'Performance',
			'Enhancement' => 'Enhancements',
			'Update'      => 'Updates',
			'Improvement' => 'Improvements',
			'Change'      => 'Changes',
			'Addition'    => 'Additions',
			'Removal'     => 'Removals',
			'Deprecation' => 'Deprecations',
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
