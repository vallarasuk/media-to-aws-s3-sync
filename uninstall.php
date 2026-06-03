<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When a user deletes the plugin, all data associated with it should be deleted.
 * This is a standard WordPress plugin rule for good housekeeping.
 */

// If uninstall is not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete options from wp_options table.
delete_option( 'media_to_aws_s3_sync_enabled' );
delete_option( 'media_to_aws_s3_sync_aws_access_key_id' );
delete_option( 'media_to_aws_s3_sync_aws_secret_access_key' );
delete_option( 'media_to_aws_s3_sync_aws_region' );
delete_option( 'media_to_aws_s3_sync_aws_s3_bucket' );

// Note: We are intentionally NOT deleting post meta data (like `_media_to_aws_s3_sync_s3_synced`
// or `_media_to_aws_s3_sync_s3_url`) from the attachments. This preserves the S3 URLs just in case
// the user wants to keep the media linked or re-installs the plugin later. 
// If full wipe is requested later, we could loop through and delete those post metas here.
