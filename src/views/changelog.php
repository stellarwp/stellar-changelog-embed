<?php
/**
 * Template to display changelog on the frontend.
 *
 * @since 2.0.0
 *
 * @var array $changelog_data    Array of changelog versions.
 * @var int   $versions_per_page Number of versions to show per page.
 *
 * @package StellarWP\ChangelogEmbed
 */

defined( 'ABSPATH' ) || exit;

use StellarWP\ChangelogEmbed\Helper;

$total_versions    = count( $changelog_data );
$versions_per_page = isset( $versions_per_page ) ? max( 1, intval( $versions_per_page ) ) : 5;
$total_pages       = ceil( $total_versions / $versions_per_page );
$unique_id         = 'changelog-' . wp_rand( 1000, 9999 ); // Generate unique ID for multiple blocks.
?>
<?php if ( empty( $changelog_data ) ) : ?>
	<div class="stellar-changelog-embed__no-entries">
		<?php esc_html_e( 'No changelog entries found.', 'stellar-changelog-embed' ); ?>
	</div>
<?php else : ?>
	<div
		class="stellar-changelog-embed" 
		data-versions-per-page="<?php echo esc_attr( $versions_per_page ); ?>" 
		data-total-versions="<?php echo esc_attr( $total_versions ); ?>" 
		id="<?php echo esc_attr( $unique_id ); ?>"
	>
		<div class="stellar-changelog-embed__header">
			<h2 class="stellar-changelog-embed__title">
				<?php esc_html_e( 'Changelog', 'stellar-changelog-embed' ); ?>
			</h2>
			
			<?php if ( $total_pages > 1 ) : ?>
				<div class="stellar-changelog-embed__pagination-info">
					<span class="stellar-changelog-embed__pagination-text">
						<?php esc_html_e( 'Page', 'stellar-changelog-embed' ); ?> 
						<span class="stellar-changelog-embed__current-page">1</span> 
						<?php esc_html_e( 'of', 'stellar-changelog-embed' ); ?> 
						<span class="stellar-changelog-embed__total-pages"><?php echo esc_html( $total_pages ); ?></span> 
						(<?php echo esc_html( $total_versions ); ?> <?php esc_html_e( 'versions', 'stellar-changelog-embed' ); ?>)
					</span>
				</div>
			<?php endif; ?>
		</div>
		
		<div class="stellar-changelog-embed__versions">
			<?php
			foreach ( $changelog_data as $index => $version ) :
				$version_id = wp_generate_uuid4();
				?>
					<div 
					class="stellar-changelog-embed__version" 
						data-version-index="<?php echo esc_attr( $index ); ?>" 
						data-page="<?php echo esc_attr( floor( $index / $versions_per_page ) + 1 ); ?>">
					
					<div class="stellar-changelog-embed__version-header" data-version="<?php echo esc_attr( $version['version'] ); ?>">
						<div class="stellar-changelog-embed__version-info">
							<h3 class="stellar-changelog-embed__version-title">
								<?php
								/* translators: %s: Version number */
								printf( esc_html__( 'Version %s', 'stellar-changelog-embed' ), esc_html( $version['version'] ) );
								?>
							</h3>
							
							<?php if ( ! empty( $version['date'] ) ) : ?>
								<span class="stellar-changelog-embed__version-date">
									<span class="screen-reader-text">
										<?php esc_html_e( 'Released on:', 'stellar-changelog-embed' ); ?>
									</span>
									<?php
									echo esc_html(
										wp_date(
											get_option( 'date_format', 'F j, Y' ),
											strtotime( $version['date'] ), // Assumes UTC for release.
											new DateTimeZone( 'UTC' ) // Force UTC to avoid issues with different site timezones.
										)
									);
									?>
								</span>
							<?php endif; ?>
							
							<?php if ( ! empty( $version['isLatest'] ) ) : ?>
								<span class="stellar-changelog-embed__version-tag stellar-changelog-embed__version-tag--latest">
									<?php esc_html_e( 'Latest', 'stellar-changelog-embed' ); ?>
								</span>
							<?php endif; ?>
						</div>
						
						<button
							aria-expanded="true"
							aria-controls="stellar-changelog-embed__version-content--<?php echo esc_attr( $version_id ); ?>"
							class="stellar-changelog-embed__toggle"
							type="button"
						>
							<span class="screen-reader-text">
								<?php
								echo esc_html(
									sprintf(
										// translators: %s: Version number.
										__( 'Toggle version %s changelog details.', 'stellar-changelog-embed' ),
										$version['version']
									)
								);
								?>
							</span>
							<span class="stellar-changelog-embed__toggle-icon" aria-hidden="true"></span>
						</button>
					</div>
					
					<div
						class="stellar-changelog-embed__version-content"
						id="stellar-changelog-embed__version-content--<?php echo esc_attr( $version_id ); ?>"
					>
						<?php
						// Group changes by type.
						$grouped_changes = [];
						foreach ( $version['changes'] as $change ) {
							if ( ! isset( $grouped_changes[ $change['type'] ] ) ) {
								$grouped_changes[ $change['type'] ] = [];
							}
							$grouped_changes[ $change['type'] ][] = $change['content'];
						}
						?>
						
						<?php // phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited -- This does not apply to this file. ?>
						<?php foreach ( $grouped_changes as $type => $changes ) : ?>
							<div class="stellar-changelog-embed__section" data-type="<?php echo esc_attr( $type ); ?>">
								<h4 class="stellar-changelog-embed__section-header">
									<span class="stellar-changelog-embed__section-title">
										<?php echo esc_html( Helper::pluralize_type( $type ) ); ?>
									</span>
									<button
										aria-expanded="true"
										aria-controls="stellar-changelog-embed__changes--<?php echo esc_attr( $version_id ); ?>-<?php echo esc_attr( strtolower( $type ) ); ?>"
										class="stellar-changelog-embed__section-count"
										type="button"
									>
										<span class="screen-reader-text">
											<?php
											echo esc_html(
												sprintf(
													// translators: %1$d: Number of changes. %2$s: Section title. %3$s: Version number.
													__( 'Toggle %1$d %2$s for version %3$s.', 'stellar-changelog-embed' ),
													count( $changes ),
													Helper::pluralize_type( $type ),
													$version['version']
												)
											);
											?>
										</span>
										<span aria-hidden="true">
											<?php echo esc_html( count( $changes ) ); ?>
										</span>
									</button>
								</h4>
								
								<ul
									class="stellar-changelog-embed__changes"
									id="stellar-changelog-embed__changes--<?php echo esc_attr( $version_id ); ?>-<?php echo esc_attr( strtolower( $type ) ); ?>"
								>
									<?php foreach ( $changes as $change ) : ?>
										<li class="stellar-changelog-embed__change">
											<?php // TODO: Investigate what the previous code did here. ?>
											<?php echo wp_kses_post( $change ); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		
		<?php if ( $total_pages > 1 ) : ?>
			<div class="stellar-changelog-embed__pagination">
				<div class="stellar-changelog-embed__pagination-controls">
					<button type="button" class="stellar-changelog-embed__pagination-btn stellar-changelog-embed__pagination-btn--prev" disabled>
						<span aria-hidden="true">&laquo;</span>
						<span class="screen-reader-text">
							<?php esc_html_e( 'Previous page', 'stellar-changelog-embed' ); ?>
						</span>
					</button>
					
					<div class="stellar-changelog-embed__pagination-numbers">
						<?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
							<button
								type="button" 
								class="stellar-changelog-embed__pagination-btn stellar-changelog-embed__pagination-btn--number <?php echo $i === 1 ? 'stellar-changelog-embed__pagination-btn--active' : ''; ?>" 
								data-page="<?php echo esc_attr( $i ); ?>"
							>
								<?php echo esc_html( $i ); ?>
							</button>
						<?php endfor; ?>
					</div>

					<button
						class="stellar-changelog-embed__pagination-btn stellar-changelog-embed__pagination-btn--next" 
						<?php echo $total_pages <= 1 ? 'disabled' : ''; ?>
						type="button" 
					>
						<span aria-hidden="true">&raquo;</span>
						<span class="screen-reader-text">
							<?php esc_html_e( 'Next page', 'stellar-changelog-embed' ); ?>
						</span>
					</button>
				</div>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
