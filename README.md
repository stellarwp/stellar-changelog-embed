# StellarWP Changelog Embed

A WordPress plugin that allows you to display changelogs from your GitHub repositories in a clean, organized format using a Gutenberg block.

## Features

- Connects to GitHub to fetch changelog.txt files directly from your repositories
- Parses WordPress plugin-style changelog entries
- Displays changelogs in an expandable/collapsible interface
- Groups changes by type (Fix, Feature, Tweak, Security, etc.)
- Caches GitHub API responses to improve performance
- Full Gutenberg block integration with live preview
- Responsive design that works on all devices
- Preserves user preferences for expanded/collapsed versions

## Installation

1. Upload the `stellar-changelog-embed` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add a StellarWP Changelog Embed block to any post or page

## Usage

### Using the Block

1. Add a "StellarWP Changelog Embed" block to your post or page
2. In the block settings, enter:
   - Repository Owner (GitHub username or organization)
   - Repository Name
   - Changelog File Path (defaults to "changelog.txt")
   - Branch (defaults to "main")
   - Maximum Versions to display
3. Save your post or page

### GitHub API Configuration

To avoid GitHub API rate limits or to access private repositories:

1. Go to Settings > StellarWP Changelog Embed
2. Enter your GitHub Personal Access Token
3. Save Changes

## Changelog Format

The plugin expects changelog files in this format:

```
= [4.21.1] =
* Fix - Fixed missing quiz points in the activity report widget.
* Tweak - Improved the UX of the quiz template saving process.
* Feature - Added new functionality for users.
* Security - Fixed potential vulnerability in the login system.

= [4.21.0] =
* Feature - Added new quiz statistics visualization tools.
* Fix - Resolved an issue with the course progress not updating correctly.
...
```

## Development

For detailed implementation instructions, customization options, and technical details, see the [Implementation Guide](IMPLEMENTATION-GUIDE.md).

### Key Components

- **plugin.php**: Main plugin file
- **class-github-api.php**: Handles GitHub API integration and caching
- **class-changelog-parser.php**: Parses changelog text into structured data
- **class-settings.php**: Manages plugin settings and admin interface
- **class-api.php**: Registers REST API endpoints
- **index.js**: Block editor implementation
- **frontend.js**: Frontend interaction handling
- **src/views/changelog.php**: View template for rendering the changelog (uses BEM CSS classes)

### Building the JavaScript

This plugin uses @wordpress/scripts for building:

1. Install dependencies: `npm install`
2. Development build with watch mode: `npm start`
3. Production build: `npm run build`

## Customization

### CSS Customization

The plugin includes styling that should work with most themes. The CSS uses BEM methodology with the `stellar-changelog-embed` block. If you need to customize the appearance, you can:

1. Add custom CSS to your theme targeting the BEM classes:
   ```css
   .stellar-changelog-embed__version-header {
       /* Custom styles for version headers */
   }
   
   .stellar-changelog-embed__section[data-type="Feature"] {
       /* Custom styles for feature sections */
   }
   ```
2. Or edit the `assets/css/changelog-viewer.css` file directly

### Template Customization

To customize the HTML output:

1. Copy `src/views/changelog.php` to your theme folder under `my-theme/stellar-changelog-embed/changelog.php`
2. Edit the template as needed. The template uses BEM CSS classes for styling:
   - Main container: `stellar-changelog-embed`
   - Elements: `stellar-changelog-embed__element`
   - Modifiers: `stellar-changelog-embed__element--modifier`

## Troubleshooting

- **GitHub API Rate Limit**: If you see errors about rate limits, add a GitHub Personal Access Token in the plugin settings
- **No Changelog Data**: Verify your repository details and ensure the changelog file exists at the specified path
- **Parse Errors**: Check that your changelog follows the expected format

## Credits

- Built by Your Name
- Inspired by the React component provided as a reference
