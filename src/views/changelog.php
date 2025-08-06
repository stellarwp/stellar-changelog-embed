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

$total_versions    = count( $changelog_data );
$versions_per_page = isset( $versions_per_page ) ? max( 1, intval( $versions_per_page ) ) : 5;
$total_pages       = ceil( $total_versions / $versions_per_page );
$unique_id         = 'changelog-' . wp_rand( 1000, 9999 ); // Generate unique ID for multiple blocks.
?>
<div class="stellar-changelog-embed" 
     data-versions-per-page="<?php echo esc_attr( $versions_per_page ); ?>" 
     data-total-versions="<?php echo esc_attr( $total_versions ); ?>" 
     id="<?php echo esc_attr( $unique_id ); ?>">
    
    <div class="stellar-changelog-embed-header">
        <h2 class="stellar-changelog-embed-title">
            <?php esc_html_e( 'Changelog', 'stellar-changelog-embed' ); ?>
        </h2>
        
        <?php if ( $total_pages > 1 ) : ?>
            <div class="stellar-changelog-pagination-info">
                <span class="stellar-changelog-pagination-text">
                    <?php esc_html_e( 'Page', 'stellar-changelog-embed' ); ?> 
                    <span class="current-page">1</span> 
                    <?php esc_html_e( 'of', 'stellar-changelog-embed' ); ?> 
                    <span class="total-pages"><?php echo esc_html( $total_pages ); ?></span> 
                    (<?php echo esc_html( $total_versions ); ?> <?php esc_html_e( 'versions', 'stellar-changelog-embed' ); ?>)
                </span>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="stellar-changelog-versions">
        <?php foreach ( $changelog_data as $index => $version ) : ?>
                <div 
                class="stellar-changelog-version" 
                    data-version-index="<?php echo esc_attr( $index ); ?>" 
                     data-page="<?php echo esc_attr( floor( $index / $versions_per_page ) + 1 ); ?>">
                
                <div class="stellar-changelog-version-header" data-version="<?php echo esc_attr( $version['version'] ); ?>">
                    <div class="stellar-changelog-version-info">
                        <h3 class="stellar-changelog-version-title">
                            <?php 
                            /* translators: %s: Version number */
                            printf( esc_html__( 'Version %s', 'stellar-changelog-embed' ), esc_html( $version['version'] ) ); 
                            ?>
                        </h3>
                        
                        <?php if ( ! empty( $version['date'] ) ) : ?>
                            <span class="stellar-changelog-version-date">
                                <span class="screen-reader-text">
                                    <?php esc_html_e( 'Released on:', 'stellar-changelog-embed' ); ?>
                                </span>
                                <?php echo esc_html( $version['date'] ); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $version['isLatest'] ) ) : ?>
                            <span class="stellar-changelog-version-tag stellar-changelog-version-latest">
                                <?php esc_html_e( 'Latest', 'stellar-changelog-embed' ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="stellar-changelog-toggle" aria-expanded="true">
                        <span class="screen-reader-text">
                            <?php esc_html_e( 'Toggle changelog details', 'stellar-changelog-embed' ); ?>
                        </span>
                        <span class="stellar-changelog-toggle-icon" aria-hidden="true"></span>
                    </button>
                </div>
                
                <div class="stellar-changelog-version-content">
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
                    
                    <?php foreach ( $grouped_changes as $type => $changes ) : ?>
                        <div class="stellar-changelog-section">
                            <h4 class="stellar-changelog-section-header">
                                <span class="stellar-changelog-section-title">
                                    <?php echo esc_html( WP_Changelog_Viewer::pluralize_change_type( $type ) ); ?>
                                </span>
                                <span class="stellar-changelog-section-count">
                                    <?php echo count( $changes ); ?>
                                </span>
                            </h4>
                            
                            <ul class="stellar-changelog-changes">
                                <?php foreach ( $changes as $change ) : ?>
                                    <li class="stellar-changelog-change">
                                        <?php echo wp_kses_post( WP_Changelog_Viewer::process_changelog_content( $change ) ); ?>
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
        <div class="stellar-changelog-pagination">
            <div class="stellar-changelog-pagination-controls">
                <button type="button" class="stellar-changelog-pagination-btn stellar-changelog-pagination-prev" disabled>
                    <span aria-hidden="true">&laquo;</span>
                    <span class="screen-reader-text">
                        <?php esc_html_e( 'Previous page', 'stellar-changelog-embed' ); ?>
                    </span>
                </button>
                
                <div class="stellar-changelog-pagination-numbers">
                    <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
                        <button type="button" 
                                class="stellar-changelog-pagination-btn stellar-changelog-pagination-number <?php echo $i === 1 ? 'active' : ''; ?>" 
                                data-page="<?php echo esc_attr( $i ); ?>">
                            <?php echo esc_html( $i ); ?>
                        </button>
                    <?php endfor; ?>
                </div>

                <button
                    class="stellar-changelog-pagination-btn stellar-changelog-pagination-next" 
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
