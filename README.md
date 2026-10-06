# UptimeFixer Theme

Custom WordPress theme used on [UptimeFixer.com](https://uptimefixer.com/).

The theme is built around a large collection of practical online tools, so the main priorities are fast page loads, simple navigation, responsive layouts and loading tool-specific assets only where they are needed.

![UptimeFixer Theme Preview](screenshot.png)

## Highlights

- Custom WordPress theme structure
- Tool directory and category templates
- Website, developer, image, text, PDF and calculator interfaces
- Conditional CSS and JavaScript loading
- Responsive layouts and dark mode support
- Yoast SEO compatibility
- Security and content-quality helpers
- Browser-side media tooling where appropriate

## Performance approach

UptimeFixer has many different tool types, so loading every script on every page would be wasteful. The theme separates tool bundles by group and loads heavier libraries only on pages that need them.

Examples include:

- separate base, extra, growth and SEO tool bundles
- QR code assets only on the QR tool
- font editor assets only on the font converter
- deferred non-critical frontend scripts
- lightweight shared studio controls
- local browser processing for supported tools

## Project structure

```text
assets/
  css/
  images/
  js/
  vendor/
inc/
template-parts/
functions.php
front-page.php
single-alltool.php
style.css
```

The `inc/` directory contains the theme setup, routing, SEO, security, performance and tool registry logic. Frontend assets are kept under `assets/` and tool templates are separated from the core theme files.

## Requirements

- WordPress 5.8 or newer
- PHP 7.4 or newer

## Installation

1. Download or clone the repository.
2. Place the theme folder inside `wp-content/themes/`.
3. Activate **Uptime Fixer** from WordPress.
4. Save permalinks once after activation if tool routes need to be refreshed.
5. Clear WordPress/server/CDN caches after replacing an existing version.

## Current version

**4.4.0**

This release focuses on conditional asset loading and safe Yoast SEO metadata defaults while preserving existing tool URLs and manual SEO fields.

See [CHANGELOG.md](CHANGELOG.md) for release notes.

## Live project

https://uptimefixer.com/

## Author

Azhar Mehmood  
DigiPlex Creations

## License

GPL-2.0-or-later. See `LICENSE`.
