=== Vallarasu Media Bucket Sync for Amazon S3 ===
Contributors: vallarasuk
Tags: amazon s3, sync media, aws, offload media
Requires at least: 5.0
Tested up to: 7.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful and standalone plugin to sync media attachments to AWS S3. 

== Description ==

The **Vallarasu Media Bucket Sync for Amazon S3** plugin, developed by **vallarasuk**, allows WordPress users to effortlessly sync their media library to Amazon S3. 

With a master enable/disable toggle, you have complete control over whether media is synchronized to Amazon S3. Your media is securely transferred to your configured S3 bucket, saving your server space and offloading media delivery.

== External services ==

This plugin connects to Amazon Web Services (AWS) S3 to store and serve your media files. 
It requires an AWS account and a configured S3 bucket.

When you choose to sync an image or file, the plugin sends the file data, file name, and MIME type directly to your configured Amazon S3 bucket via the AWS API.

This service is provided by Amazon Web Services, Inc.:
* [AWS Terms of Service](https://aws.amazon.com/terms/)
* [AWS Privacy Policy](https://aws.amazon.com/privacy/)

### Key Features
* Seamlessly sync media attachments to your AWS S3 bucket.
* Global Enable/Disable master toggle for immediate frontend control.
* Automatically syncs all WordPress-generated responsive image sub-sizes.
* Bulk Sync Tool: Automatically background-sync your entire existing media library to Amazon S3 without server timeouts.
* Dynamically rewrites frontend image URLs to securely serve them directly from S3.
* User-friendly configuration screen.
* Lightweight and highly performant.
* Open source and easy to extend.

== Installation ==

1. Upload the `media-to-aws-s3-sync` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **Settings > Media to S3 Sync**.
4. Enable the synchronization using the master toggle.
5. Enter your AWS Access Key, Secret Key, Region, and Bucket name.
6. Start syncing your media directly from the Media Library!

== Frequently Asked Questions ==

= Do I need an AWS Account? =
Yes, you need an active AWS account and an S3 bucket configured for public read access.

= How do I disable the sync temporarily? =
Go to **Settings > Media to S3 Sync** and toggle the Enable/Disable button to OFF. This globally disables the sync options in the Media Library.

= Who developed this plugin? =
This plugin was developed by Vallarasu kanthasamy to solve media offloading issues easily.

== Changelog ==

= 1.0.2 =
* Introduced the Bulk Sync Tool: Automatically batch-syncs your entire older media library to Amazon S3.
* Added real-time progress bar and upload logging UI.
* Improved error handling for S3 sub-size uploads and configuration validation.

= 1.0.1 =
* Added global enable/disable master toggle.
* Added dynamic frontend URL rewriting to instantly serve images directly from S3.
* Upgraded sync logic to upload all responsive image sub-sizes.
* Enhanced UI elements and conditional setting fields.
* Improved SEO tags and descriptions for the WordPress Plugin Repository.

= 1.0.0 =
* Initial release.
