# WP Changelog Viewer Implementation Guide

This guide provides detailed instructions for implementing, customizing, and extending the WP Changelog Viewer plugin.

## Installation and Setup

### Prerequisites

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Node.js and npm (for development only)

### Installation Steps

1. **Create Plugin Directory**:
   Create a directory named `stellar-changelog-embed` in your WordPress plugins folder (`wp-content/plugins/`).

2. **Add Plugin Files**:
   Copy all the plugin files to the directory, maintaining the folder structure as described in the README.

3. **Install Development Dependencies** (for development only):
   ```bash
   cd wp-content/plugins/stellar-changelog-embed
   npm install
   ```

4. **Build Block Assets** (for development only):
   ```bash
   npm run build
   ```

5. **Activate the Plugin**:
   Log in to your WordPress admin area and activate the plugin from the Plugins menu.

## Configuration

### GitHub API Token

To avoid rate limiting and access private repositories:

1. Create a Personal Access Token on GitHub:
   - Go to GitHub > Settings > Developer Settings > Personal Access Tokens
   - Generate a new token with the `repo` scope

2. Add the token to the plugin:
   - Go to WordPress admin > Settings > Changelog Viewer
   - Paste your GitHub token
   - Save changes

## Technical Details

### Processing Flow

1. User inserts Changelog Viewer block and configures repo details
2. Plugin saves these settings as block attributes
3. When page loads, plugin:
   - Fetches changelog file from GitHub (using cached version if available)
   - Parses the changelog text into structured data
   - Renders the changelog using the template
   - Applies JavaScript for interactive elements

### Caching System

- GitHub API responses are cached using WordPress transients
- Default cache duration: 1 hour
- Cache key format: `wp_changelog_viewer_[MD5_HASH]`
- Cache can be manually cleared from Settings > Changelog Viewer

### Block Implementation

The plugin implements a Gutenberg block with:

- Server-side rendering for improved performance and SEO
- Block inspector controls for configuration options
- Live preview during editing

## Customization

### Adding Custom Change Types

The parser automatically detects change types from the format `* Type - Description`. If you need to style additional types beyond the default ones (Fix, Feature, Tweak, Security):

1. Add CSS styles for the new types in `assets/css/changelog-viewer.css`:
   ```css
   .changelog-section[data-type="YourType"] .changelog-section-header {
       background-color: #your-color;
   }
   
   .changelog-section[data-type="YourType"] .changelog-section-title {
       color: #your-text-color;
   }
   
   .changelog-section[data-type="YourType"] .changelog-section-count {
       background-color: #your-lighter-color;
       color: #your-darker-color;
   }
   ```

2. Update the template if needed in `src/views/changelog.php`

### Advanced Template Customization

To create a completely custom template:

1. Copy `src/views/changelog.php` to your theme:
   ```
   wp-content/themes/your-theme/stellar-changelog-embed/changelog.php
   ```

2. Add a filter to WordPress to use your custom template:
   ```php
   add_filter( 'wp_changelog_viewer_template_path', function( $template_path ) {
       $custom_template = get_stylesheet_directory() . '/stellar-changelog-embed/changelog.php';
       if ( file_exists( $custom_template ) ) {
           return $custom_template;
       }
       return $template_path;
   } );
   ```

3. Customize your template file. The template has access to:
   - `$changelog_data`: Array of parsed changelog entries

### JavaScript Event Hooks

The frontend JavaScript triggers custom events you can hook into:

```javascript
// Listen for when a changelog version is expanded
jQuery(document).on('changelog_version_expanded', function(event, versionData) {
    console.log('Version expanded:', versionData.version);
});

// Listen for when a changelog version is collapsed
jQuery(document).on('changelog_version_collapsed', function(event, versionData) {
    console.log('Version collapsed:', versionData.version);
});
```

## Integration with Other Plugins

### Integration with WP GitHub Updater

If you're using GitHub to host your plugins and want auto-updates:

1. Ensure your changelog file is at the root of your GitHub repository
2. Set up GitHub Updater with the same repository details
3. This plugin will automatically display the latest changes when updates are available

### Integration with WooCommerce

If you sell plugins/themes via WooCommerce:

1. Add the changelog block to your product description
2. Configure it to point to your GitHub repository
3. Customers will always see up-to-date changelog info

## Common Code Modifications

### Change Default Branch

To change the default branch from "main" to something else:

```php
// Add to your theme's functions.php.
add_filter( 'wp_changelog_viewer_default_branch', function() {
    return 'master'; // Or any other branch name.
} );
```

### Custom Parsing Rules

To support a different changelog format:

1. Create a custom parser class that extends `WP_Changelog_Viewer_Parser`.
2. Override the `parse()` method with your custom logic.
3. Replace the default parser with your custom one:

```php
add_filter( 'wp_changelog_viewer_parser', function() {
    return new Your_Custom_Parser();
} );
```

### Enable Shortcode Support

To add shortcode support (in addition to the block):

```php
// Add to your plugin or theme.
function changelog_viewer_shortcode( $atts ) {
    $attributes = shortcode_atts( [
        'owner'        => '',
        'repo'         => '',
        'file_path'    => 'changelog.txt',
        'branch'       => 'main',
        'max_versions' => 5,
    ], $atts );
    
    return WP_Changelog_Viewer::render_block( $attributes );
}
add_shortcode( 'changelog_viewer', 'changelog_viewer_shortcode' );
```

## Security Considerations

- The plugin validates and sanitizes all user inputs
- GitHub API requests are made server-side to avoid exposing API tokens
- Content from GitHub is sanitized before display to prevent XSS attacks
- WordPress nonces are used for all admin actions
- AJAX requests include permission checks

## Performance Optimization

- Use browser caching for CSS/JS files (configure in your web server)
- Consider increasing the cache duration for changelog data:
  ```php
  add_filter( 'wp_changelog_viewer_cache_duration', function() {
      return DAY_IN_SECONDS; // Cache for 24 hours.
  } );
  ```
- If you have multiple blocks showing the same repository, they will share the cache

## Support and Maintenance

For future plugin updates, you may need to:

1. Update the JavaScript dependencies in package.json
2. Rebuild the block editor scripts with `npm run build`
3. Test compatibility with new WordPress versions
