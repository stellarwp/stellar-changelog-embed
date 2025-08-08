<?php
/**
 * Error view template.
 *
 * @since 2.0.0
 *
 * @var string $error_message Error message to display.
 *
 * @package StellarWP\ChangelogEmbed
 */

defined( 'ABSPATH' ) || exit;

?>
<span class="stellar-changelog-embed__error" style="color: red;">
	<?php echo esc_html( $error_message ); ?>
</span>
