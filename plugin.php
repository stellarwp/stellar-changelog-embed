<?php
/**
 * Plugin Name: Stellar Changelog Embed
 * Description: A block that displays the contents of a changelog file from a user-specified URL.
 * Version: 1.0.0
 * Author: StellarWP
 * License: GPLv2 or later
 */

namespace StellarWP\ChangelogEmbed;

require_once __DIR__ . '/vendor/autoload.php';

define( 'STELLAR_CHANGELOG_EMBED_DIR', __DIR__ );

add_action( 'init', static function() {
	Plugin::instance()->register();
} );
