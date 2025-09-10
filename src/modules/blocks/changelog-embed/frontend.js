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
        // Handle version header clicks.
        $('.stellar-changelog-embed__toggle--version').on('click touch', function() {
            const $header = $(this).closest('.stellar-changelog-embed__version-header');
            const $version = $header.closest('.stellar-changelog-embed__version');
            const $content = $version.find('.stellar-changelog-embed__version-content');
            const $toggle = $(this);
            
            // Toggle display.
            $content.slideToggle(200);
            
            // Update aria attributes.
            const isExpanded = $toggle.attr('aria-expanded') === 'true';
            $toggle.attr('aria-expanded', !isExpanded);
        });
        
        // Handle section header clicks.
        $('.stellar-changelog-embed__toggle--section').on('click touch', function() {
            const $header = $(this).closest('.stellar-changelog-embed__section-header');
            const $section = $header.closest('.stellar-changelog-embed__section');
            const $changes = $section.find('.stellar-changelog-embed__changes');
            
            // Toggle display.
            $changes.slideToggle(200);
            
            // Toggle aria attribute.
            const isExpanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', !isExpanded);
        });

        // Initialize pagination for each changelog viewer.
        $('.stellar-changelog-embed').each(function() {
            initPagination($(this));
        });
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

        // Show first page initially.
        showPage($viewer, 1, versionsPerPage);
        
        if (totalVersions <= versionsPerPage) {
            return; // No pagination needed.
        }
        
        $viewer.data('current-page', 1);
        const totalPages = Math.ceil(totalVersions / versionsPerPage);
        
        // Handle pagination button clicks.
        $viewer.on('click', '.stellar-changelog-embed__pagination-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            
            if ($btn.prop('disabled')) {
                return;
            }
            
            if ($btn.hasClass('stellar-changelog-embed__pagination-btn--prev')) {
                $viewer.data('current-page', Math.max(1, $viewer.data('current-page') - 1));
            } else if ($btn.hasClass('stellar-changelog-embed__pagination-btn--next')) {
                $viewer.data('current-page', Math.min(totalPages, $viewer.data('current-page') + 1));
            } else if ($btn.hasClass('stellar-changelog-embed__pagination-btn--number')) {
                $viewer.data('current-page', parseInt($btn.data('page')));
            }
            
            showPage($viewer, $viewer.data('current-page'), versionsPerPage);
            updatePaginationControls($viewer, $viewer.data('current-page'), totalPages);
            
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

                // Ensure the first version on the page is expanded and the rest are collapsed.
                if (index === startIndex) {
                    $version.find('.stellar-changelog-embed__toggle--version[aria-expanded="false"]').trigger('click');
                } else {
                    $version.find('.stellar-changelog-embed__toggle--version[aria-expanded="true"]').trigger('click');
                }
            } else {
                $version.hide();
            }
        });
        
        // Update pagination info.
        const $paginationText = $viewer.find('.stellar-changelog-embed__current-page');
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
        const $prevBtn = $viewer.find('.stellar-changelog-embed__pagination-btn--prev');
        const $nextBtn = $viewer.find('.stellar-changelog-embed__pagination-btn--next');
        const $numberBtns = $viewer.find('.stellar-changelog-embed__pagination-btn--number');
        
        // Update prev/next buttons.
        $prevBtn.prop('disabled', currentPage <= 1);
        $nextBtn.prop('disabled', currentPage >= totalPages);
        
        // Update number buttons.
        $numberBtns.removeClass('active').removeAttr('aria-current');
        $numberBtns.filter(`[data-page="${currentPage}"]`).addClass('active').attr('aria-current', 'page');
    }
    
    // Initialize when DOM is fully loaded.
    $(document).ready(function() {
        initChangelog();
    });
    
})(jQuery);
