# Changelog

All notable changes to the **Media to AWS S3 Sync** plugin will be documented in this file.

## [1.0.2] - 2026-06-05
### Added
- Introduced the Bulk Sync Tool: Automatically batch-syncs your entire older media library to Amazon S3.
- Added real-time progress bar and upload logging UI.
- Improved error handling for S3 sub-size uploads and configuration validation.

## [1.0.1] - 2026-06-03
### Added
- Added global enable/disable master toggle.
- Implemented robust frontend URL rewriting (via `wp_get_attachment_url`, `wp_get_attachment_image_src`, `wp_calculate_image_srcset`, and `the_content` filters) to serve S3 images dynamically.
- Upgraded the S3 sync script to automatically upload all WordPress-generated responsive sub-sizes (thumbnails, medium, large, etc.) alongside the original image.
- Added meaningful labels and help text for all settings.
- Added full SEO metadata to the plugin header (Tags, Author URI, Plugin URI).
- Created a `readme.txt` file specifically formatted for the WordPress Plugin Repository to enhance SEO (targeting keywords like "search media to s3 sync", "medi to aws sync", etc.).
- Created `CHANGELOG.md` for proper version-wise tracking.
- Added `uninstall.php` to clean up AWS credentials and options upon plugin deletion, conforming to WordPress standards.
- Added `index.php` files to directories to prevent directory traversal.

## [1.0.0] - Initial Release
### Added
- Initial standalone release of the Media to AWS S3 Sync plugin.
- Basic integration for syncing media attachments from WordPress to AWS S3.
