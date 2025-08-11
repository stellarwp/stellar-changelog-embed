<?php
/**
 * Settings.
 *
 * @since 2.0.0
 *
 * @package StellarWP\ChangelogEmbed
 */

namespace StellarWP\ChangelogEmbed;

/**
 * Handles settings.
 *
 * @since 2.0.0
 */
class Settings {
	/**
	 * Instance of the plugin.
	 *
	 * @since 2.0.0
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
	 * Register hooks.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );

		add_action( 'wp_ajax_stellar_changelog_embed_clear_cache', [ $this, 'clear_cache_callback' ] );

		add_action( 'admin_print_footer_scripts-settings_page_stellar-changelog-embed', [ $this, 'render_settings_scripts' ] );
	}

	/**
	 * Add settings page to the admin menu.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'StellarWP Changelog Viewer Settings', 'stellar-changelog-embed' ),
			__( 'StellarWP Changelog Viewer', 'stellar-changelog-embed' ),
			'manage_options',
			'stellar-changelog-embed',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_settings(): void {
		// Register the main settings group.
		register_setting(
			'stellar_changelog_embed_settings',
			'stellar_changelog_embed_github_token',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			]
		);

		// Add settings section.
		add_settings_section(
			'stellar_changelog_embed_github_section',
			__( 'GitHub Configuration', 'stellar-changelog-embed' ),
			'',
			'stellar-changelog-embed'
		);

		// Add settings field.
		add_settings_field(
			'stellar_changelog_embed_github_token',
			__( 'GitHub API Token', 'stellar-changelog-embed' ),
			[ $this, 'render_github_token_field' ],
			'stellar-changelog-embed',
			'stellar_changelog_embed_github_section',
			[
				'label_for'   => 'stellar_changelog_embed_github_token',
				'description' => sprintf(
					/* translators: %s: URL to GitHub API token settings */
					__( 'Enter a GitHub API personal access token to increase API rate limits and access private repositories. You can create one %s.', 'stellar-changelog-embed' ),
					'<a href="https://github.com/settings/tokens" target="_blank">' . __( 'here', 'stellar-changelog-embed' ) . '</a>'
				) .
				'<ul style="list-style-type: disc; margin-left: 1em;">' .
					'<li>' . __( 'Preferred: Fine-grained personal access tokens: You will need the <code>Contents</code> scope set to <code>Read-only</code>.', 'stellar-changelog-embed' ) . '</li>' .
					'<li>' . __( 'Classic personal access tokens: The full <code>repo</code> scope is required for private repositories. If only public repositories are needed, the <code>public_repo</code> scope is sufficient.', 'stellar-changelog-embed' ) . '</li>' .
				'</ul>',
			]
		);
	}

	/**
	 * Render the GitHub API token field.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args The arguments for the field.
	 *
	 * @return void
	 */
	public function render_github_token_field( $args ): void {
		$value = (string) get_option( 'stellar_changelog_embed_github_token', '' );
		?>

		<input type="password" 
			id="<?php echo esc_attr( $args['label_for'] ); ?>" 
			name="stellar_changelog_embed_github_token" 
			value="<?php echo esc_attr( $value ); ?>" 
			class="regular-text"
			autocomplete="off"
		/>

		<button type="button" id="stellar_changelog_embed_toggle_token" class="button button-secondary" style="margin-left: 5px;">
			<?php esc_html_e( 'Show', 'stellar-changelog-embed' ); ?>
		</button>

		<p class="description">
			<?php echo wp_kses_post( $args['description'] ); ?>
		</p>
		<?php
	}

	/**
	 * Render the settings page.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<form method="post" action="options.php">
				<?php
				// Output security fields.
				settings_fields( 'stellar_changelog_embed_settings' );

				// Output setting sections.
				do_settings_sections( 'stellar-changelog-embed' );

				// Submit button.
				submit_button();
				?>
			</form>

			<hr />

			<?php $this->render_cache_management_section(); ?>
		</div>
		<?php
	}

	/**
	 * Render the cache management section in admin footer.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function render_cache_management_section(): void {
		// Get cache duration from GitHub API class (this ensures consistency).
		$cache          = new Cache();
		$cache_duration = $cache->get_cache_duration();
		$cache_hours    = $cache_duration / HOUR_IN_SECONDS;
		?>
		
		<h2><?php esc_html_e( 'Cache Management', 'stellar-changelog-embed' ); ?></h2>
		<p>
			<?php esc_html_e( 'Forcibly clear all cached changelog data to fetch fresh content.', 'stellar-changelog-embed' ); ?>
		</p>
		<p>
			<button type="button" id="stellar_changelog_embed_clear_cache" class="button">
				<?php esc_html_e( 'Clear Cache', 'stellar-changelog-embed' ); ?>
			</button>
			<span id="stellar_changelog_embed_cache_message" style="margin-left: 10px; display: none;"></span>
		</p>
		
		<p class="description" id="stellar_changelog_embed_cache_duration">
		<?php
		printf(
			/* translators: %s: number of hours */
			esc_html__( 'Cache duration: %s hours', 'stellar-changelog-embed' ),
			'<strong>' . esc_html( number_format( $cache_hours, 1 ) ) . '</strong>'
		);
		?>
		</p>
		
		<details style="margin-top: 15px;">
			<summary style="cursor: pointer; font-weight: 600;">
				<?php esc_html_e( 'Developer Information', 'stellar-changelog-embed' ); ?>
			</summary>
			<div style="margin-top: 10px; padding: 10px; background: #f6f7f7; border-left: 4px solid #00a0d2;">
				<p style="margin: 0;">
					<strong><?php esc_html_e( 'Cache Duration Filter:', 'stellar-changelog-embed' ); ?></strong><br>
					<?php esc_html_e( 'Developers can customize the cache duration using the following filter:', 'stellar-changelog-embed' ); ?>
				</p>
				<code style="display: block; margin: 10px 0; padding: 8px; background: #fff; border: 1px solid #ddd;">
					add_filter('stellar_changelog_embed_cache_duration', function($duration) {<br>
					&nbsp;&nbsp;&nbsp;&nbsp;return 6 * HOUR_IN_SECONDS; // 6 hours instead of default 1 hour<br>
					});
				</code>
				<p style="margin: 0; font-size: 0.9em; color: #666;">
					<?php esc_html_e( 'Default: 1 hour. Use WordPress time constants like HOUR_IN_SECONDS, DAY_IN_SECONDS, etc.', 'stellar-changelog-embed' ); ?>
				</p>
			</div>
		</details>
		<?php
	}

	/**
	 * Renders the JavaScript for the settings page.
	 * TODO: Move to a JS file.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function render_settings_scripts(): void {
		$cache          = new Cache();
		$cache_duration = $cache->get_cache_duration();
		$cache_hours    = $cache_duration / HOUR_IN_SECONDS;
		?>
		<script>
			jQuery(document).ready(function($) {
				// Get current cache duration for JavaScript calculations.
				var cacheHours = <?php echo wp_json_encode( $cache_hours ); ?>;
				
				// Handle cache clearing.
				$('#stellar_changelog_embed_clear_cache').on('click', function() {
					var $button = $(this);
					var $message = $('#stellar_changelog_embed_cache_message');
					
					$button.prop('disabled', true);
					$message.hide();
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'stellar_changelog_embed_clear_cache',
							_wpnonce: '<?php echo esc_attr( wp_create_nonce( 'stellar_changelog_embed_clear_cache' ) ); ?>'
						},
						success: function(response) {
							if (response.success) {
								$message.text(response.data.message).css('color', 'green').show();
								
								// Update the cache cleared timestamp and expiration.
								var now = new Date();
								var timeString = now.toLocaleString();
								
								// Calculate expiration time using the current cache duration.
								var expirationTime = new Date(now.getTime() + (cacheHours * 60 * 60 * 1000));
								var expirationString = expirationTime.toLocaleString();
								
								// Update timestamp.
								var $timestampElement = $('#stellar_changelog_embed_cache_timestamp');
								if ($timestampElement.length) {
									$timestampElement.html('<?php esc_html_e( 'Cache last cleared:', 'stellar-changelog-embed' ); ?> <strong>' + timeString + '</strong>');
								}
								
								// Update expiration.
								var $expirationElement = $('#stellar_changelog_embed_cache_expiration');
								if ($expirationElement.length) {
									$expirationElement.html('<?php esc_html_e( 'Cache expires:', 'stellar-changelog-embed' ); ?> <strong>' + expirationString + '</strong>');
								}
							} else {
								$message.text(response.data.error).css('color', 'red').show();
							}
						},
						error: function() {
							$message.text('<?php esc_html_e( 'An error occurred while clearing the cache.', 'stellar-changelog-embed' ); ?>')
								.css('color', 'red').show();
						},
						complete: function() {
							$button.prop('disabled', false);
						}
					});
				});
				
				// Handle token visibility toggle.
				$('#stellar_changelog_embed_toggle_token').on('click', function() {
					var $button = $(this);
					var $input = $('#stellar_changelog_embed_github_token');
					
					if ($input.attr('type') === 'password') {
						$input.attr('type', 'text');
						$button.text('<?php esc_html_e( 'Hide', 'stellar-changelog-embed' ); ?>');
					} else {
						$input.attr('type', 'password');
						$button.text('<?php esc_html_e( 'Show', 'stellar-changelog-embed' ); ?>');
					}
				});
			});
		</script>
		<?php
	}

	/**
	 * Clears the cache for all changelog embeds via AJAX.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function clear_cache_callback(): void {
		$post_data = wp_unslash( $_POST );

		// Check nonce.
		if (
			! isset( $post_data['_wpnonce'] )
			|| ! wp_verify_nonce(
				$post_data['_wpnonce'],
				'stellar_changelog_embed_clear_cache'
			)
		) {
			wp_send_json_error(
				array(
					'error' => __( 'Security check failed.', 'stellar-changelog-embed' ),
				)
			);
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'error' => __( 'You do not have permission to perform this action.', 'stellar-changelog-embed' ),
				)
			);
		}

		// Clear the cache.
		$cache   = new Cache();
		$cleared = $cache->clear_all();

		if ( $cleared > 0 ) {
			wp_send_json_success(
				array(
					'message' => sprintf(
						/* translators: %d: Number of cache items cleared */
						_n(
							'%d cache item cleared successfully.',
							'%d cache items cleared successfully.',
							$cleared,
							'stellar-changelog-embed'
						),
						$cleared
					),
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'No cached items found to clear.', 'stellar-changelog-embed' ),
			)
		);
	}
}
