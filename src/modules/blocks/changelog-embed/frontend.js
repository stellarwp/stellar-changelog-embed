/**
 * Frontend JavaScript for Changelog Viewer.
 * 
 * Handles toggling of changelog versions, sections, and pagination.
 * 
 * @since 2.0.0
 * 
 * @param {jQuery} $ The jQuery object.
 * 
 * @return {void}
 */
(function($) {
    'use strict';
    
    /**
     * Initialize the changelog functionality.
	 * 
	 * @since 2.0.0
	 * 
	 * @return {void}
     */
    function initChangelog() {
        // Initialize pagination for each changelog viewer.
        $('.wp-changelog-viewer').each(function() {
            initPagination($(this));
        });
        
        // Handle version header clicks.
        $('.stellar-changelog-embed__version-header').on('click', function() {
            const $header = $(this);
            const $version = $header.closest('.stellar-changelog-embed__version');
            const $content = $version.find('.stellar-changelog-embed__version-content');
            const $toggle = $header.find('.stellar-changelog-embed__toggle');
            
            // Toggle display.
            $content.slideToggle(200);
            
            // Update aria attributes.
            const isExpanded = $toggle.attr('aria-expanded') === 'true';
            $toggle.attr('aria-expanded', !isExpanded);
            
            // Store expanded state in browser storage.
            const version = $header.data('version');
            if (version) {
                const expandedVersions = getExpandedVersions();
                
                if (isExpanded) {
                    // Remove from expanded versions.
                    const index = expandedVersions.indexOf(version);
                    if (index !== -1) {
                        expandedVersions.splice(index, 1);
                    }
                } else {
                    // Add to expanded versions.
                    if (!expandedVersions.includes(version)) {
                        expandedVersions.push(version);
                    }
                }
                
                // Save updated list.
                saveExpandedVersions(expandedVersions);
            }
        });
        
        // Handle section header clicks.
        $('.stellar-changelog-embed__section-header').on('click', function() {
            const $header = $(this);
            const $section = $header.closest('.stellar-changelog-embed__section');
            const $changes = $section.find('.stellar-changelog-embed__changes');
            
            // Toggle display.
            $changes.slideToggle(200);
            
            // Toggle aria attribute.
            const isExpanded = $header.attr('aria-expanded') === 'true';
            $header.attr('aria-expanded', !isExpanded);
        });
        
        // Set initial states based on stored preferences.
        restoreExpandedVersions();
    }
    
    /**
     * Initialize pagination for a changelog viewer.
	 * 
	 * @since 2.0.0
     * 
     * @param {jQuery} $viewer The changelog viewer element.
	 * 
	 * @return void
     */
    function initPagination($viewer) {
        const versionsPerPage = parseInt($viewer.data('versions-per-page')) || 5;
        const totalVersions = parseInt($viewer.data('total-versions')) || 0;
        
        if (totalVersions <= versionsPerPage) {
            return; // No pagination needed.
        }
        
        let currentPage = 1;
        const totalPages = Math.ceil(totalVersions / versionsPerPage);
        
        // Show first page initially.
        showPage($viewer, currentPage, versionsPerPage);
        
        // Handle pagination button clicks.
        $viewer.on('click', '.stellar-changelog-embed__pagination-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            
            if ($btn.prop('disabled')) {
                return;
            }
            
            if ($btn.hasClass('stellar-changelog-embed__pagination-prev')) {
                currentPage = Math.max(1, currentPage - 1);
            } else if ($btn.hasClass('stellar-changelog-embed__pagination-next')) {
                currentPage = Math.min(totalPages, currentPage + 1);
            } else if ($btn.hasClass('stellar-changelog-embed__pagination-number')) {
                currentPage = parseInt($btn.data('page'));
            }
            
            showPage($viewer, currentPage, versionsPerPage);
            updatePaginationControls($viewer, currentPage, totalPages);
            
            // Scroll to top of changelog.
            $viewer[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
    
    /**
     * Show specific page of changelog versions.
	 * 
	 * @since 2.0.0
     * 
     * @param {jQuery} $viewer The changelog viewer element.
     * @param {number} page The page number to show.
     * @param {number} versionsPerPage Number of versions per page.
	 * 
	 * @return void
     */
    function showPage($viewer, page, versionsPerPage) {
        const $versions = $viewer.find('.stellar-changelog-embed__version');
        const startIndex = (page - 1) * versionsPerPage;
        const endIndex = startIndex + versionsPerPage;
        
        $versions.each(function(index) {
            const $version = $(this);
            if (index >= startIndex && index < endIndex) {
                $version.show();
            } else {
                $version.hide();
            }
        });
        
        // Update pagination info.
        const $paginationText = $viewer.find('.wp-changelog-pagination-text .current-page');
        if ($paginationText.length) {
            $paginationText.text(page);
        }
    }
    
    /**
     * Update pagination controls state
	 * 
	 * @since 2.0.0
     * 
     * @param {jQuery} $viewer The changelog viewer element.
     * @param {number} currentPage Current page number.
     * @param {number} totalPages Total number of pages.
	 * 
	 * @return void
     */
    function updatePaginationControls($viewer, currentPage, totalPages) {
        const $prevBtn = $viewer.find('.stellar-changelog-embed__pagination-prev');
        const $nextBtn = $viewer.find('.stellar-changelog-embed__pagination-next');
        const $numberBtns = $viewer.find('.stellar-changelog-embed__pagination-number');
        
        // Update prev/next buttons.
        $prevBtn.prop('disabled', currentPage <= 1);
        $nextBtn.prop('disabled', currentPage >= totalPages);
        
        // Update number buttons.
        $numberBtns.removeClass('active');
        $numberBtns.filter(`[data-page="${currentPage}"]`).addClass('active');
    }
    
    /**
     * Get array of expanded version numbers from localStorage.
	 * 
	 * @since 2.0.0
     * 
     * @return {Array} List of expanded version numbers.
	 * 
	 * @return void
     */
    function getExpandedVersions() {
        try {
            const stored = localStorage.getItem('stellar_changelog_embed_expanded');
            return stored ? JSON.parse(stored) : [];
        } catch (e) {
            console.error('Error reading changelog preferences', e);
            return [];
        }
    }
    
    /**
     * Save array of expanded version numbers to localStorage.
	 * 
	 * @since 2.0.0
     * 
     * @param {Array} versions List of expanded version numbers.
	 * 
	 * @return void
     */
    function saveExpandedVersions(versions) {
        try {
            localStorage.setItem('stellar_changelog_embed_expanded', JSON.stringify(versions));
        } catch (e) {
            console.error('Error saving changelog preferences', e);
        }
    }
    
    /**
     * Restore expanded state of versions from localStorage.
	 * 
	 * @since 2.0.0
	 * 
	 * @return void
     */
    function restoreExpandedVersions() {
        const expandedVersions = getExpandedVersions();
        
        // First collapse all versions (except the latest if no preferences exist).
        $('.stellar-changelog-embed__version:visible').each(function(index) {
            const $version = $(this);
            const $header = $version.find('.stellar-changelog-embed__version-header');
            const $content = $version.find('.stellar-changelog-embed__version-content');
            const $toggle = $header.find('.stellar-changelog-embed__toggle');
            const version = $header.data('version');
            
            // Determine if this version should be expanded.
            let shouldBeExpanded;
            
            if (expandedVersions.length > 0) {
                // Use stored preferences.
                shouldBeExpanded = expandedVersions.includes(version);
            } else {
                // Default: only expand latest version (first visible one).
                shouldBeExpanded = index === 0;
            }
            
            // Set initial state.
            if (!shouldBeExpanded) {
                $content.hide();
                $toggle.attr('aria-expanded', 'false');
            }
        });
    }
    
    // Initialize when DOM is fully loaded.
    $(document).ready(function() {
        initChangelog();
    });
    
})(jQuery);
