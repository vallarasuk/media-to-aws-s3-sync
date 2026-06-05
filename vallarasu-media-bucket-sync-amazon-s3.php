<?php
/**
 * Plugin Name:       Vallarasu Media Bucket Sync for Amazon S3
 * Plugin URI:        https://github.com/vallarasuk/vallarasu-media-bucket-sync-amazon-s3
 * Description:       A powerful and standalone plugin to sync media attachments to Amazon S3.
 * Version:           1.0.2
 * Author:            Vallarasu Kanthasamy
 * Author URI:        https://github.com/vallarasuk
 * License:           GPL-2.0+
 * Text Domain:       vallarasu-media-bucket-sync-amazon-s3
 * Tags:              amazon s3, sync media, aws, offload media
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load S3 Helper Class
require_once plugin_dir_path( __FILE__ ) . 'includes/class-media-to-aws-s3-sync-s3.php';

/**
 * Enqueue scripts and styles in the WP admin area.
 */
function media_to_aws_s3_sync_admin_scripts() {
    wp_enqueue_style(
        'media-to-aws-s3-sync-admin-css',
        plugins_url( 'admin/css/media-to-aws-s3-sync-admin.css', __FILE__ ),
        array(),
        '1.0.0'
    );

    wp_add_inline_style( 'media-to-aws-s3-sync-admin-css', '
        .m2s3-switch { position: relative; display: inline-block; width: 40px; height: 20px; }
        .m2s3-switch input { opacity: 0; width: 0; height: 0; }
        .m2s3-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
        .m2s3-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: white; transition: .4s; border-radius: 50%; }
        .m2s3-switch input:checked + .m2s3-slider { background-color: #2271b1; }
        .m2s3-switch input:focus + .m2s3-slider { box-shadow: 0 0 1px #2271b1; }
        .m2s3-switch input:checked + .m2s3-slider:before { transform: translateX(20px); }
    ');

    wp_enqueue_script(
        'media-to-aws-s3-sync-admin-js',
        plugins_url( 'admin/js/media-to-aws-s3-sync-admin.js', __FILE__ ),
        array( 'jquery' ),
        '1.0.0',
        true
    );

    wp_add_inline_script( 'media-to-aws-s3-sync-admin-js', '
        document.addEventListener("DOMContentLoaded", function() {
            var toggle = document.getElementById("m2s3_toggle");
            if (!toggle) return;
            function updateVisibility() {
                var toggleRow = toggle.closest("tr");
                if (!toggleRow) return;
                
                var sibling = toggleRow.nextElementSibling;
                while (sibling) {
                    sibling.style.display = toggle.checked ? "" : "none";
                    sibling = sibling.nextElementSibling;
                }
            }
            toggle.addEventListener("change", updateVisibility);
            updateVisibility(); // Run on load
        });
    ');

    wp_localize_script(
        'media-to-aws-s3-sync-admin-js',
        'media_to_aws_s3_sync_vars',
        array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'media_to_aws_s3_sync_nonce' ),
        )
    );
}
add_action( 'admin_enqueue_scripts', 'media_to_aws_s3_sync_admin_scripts' );

/**
 * Add settings page to the menu.
 */
function media_to_aws_s3_sync_add_settings_page() {
    add_options_page(
        'Media to S3 Sync Settings',
        'Media to S3 Sync',
        'manage_options',
        'vallarasu-media-bucket-sync-amazon-s3',
        'media_to_aws_s3_sync_render_settings_page'
    );
}
add_action( 'admin_menu', 'media_to_aws_s3_sync_add_settings_page' );

/**
 * Register settings and their fields.
 */
function media_to_aws_s3_sync_register_settings() {
    register_setting( 'media_to_aws_s3_sync_settings_group', 'media_to_aws_s3_sync_enabled', array(
        'type'              => 'integer',
        'sanitize_callback' => 'absint'
    ) );
    register_setting( 'media_to_aws_s3_sync_settings_group', 'media_to_aws_s3_sync_aws_access_key_id', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field'
    ) );
    register_setting( 'media_to_aws_s3_sync_settings_group', 'media_to_aws_s3_sync_aws_secret_access_key', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field'
    ) );
    register_setting( 'media_to_aws_s3_sync_settings_group', 'media_to_aws_s3_sync_aws_region', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field'
    ) );
    register_setting( 'media_to_aws_s3_sync_settings_group', 'media_to_aws_s3_sync_aws_s3_bucket', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field'
    ) );

    add_settings_section(
        'media_to_aws_s3_sync_aws_section',
        'AWS S3 Configuration',
        'media_to_aws_s3_sync_section_callback',
        'vallarasu-media-bucket-sync-amazon-s3'
    );

    add_settings_field(
        'media_to_aws_s3_sync_enabled',
        'Enable AWS S3 Sync',
        'media_to_aws_s3_sync_enabled_callback',
        'vallarasu-media-bucket-sync-amazon-s3',
        'media_to_aws_s3_sync_aws_section'
    );

    add_settings_field(
        'media_to_aws_s3_sync_aws_access_key_id',
        'AWS Access Key ID',
        'media_to_aws_s3_sync_access_key_callback',
        'vallarasu-media-bucket-sync-amazon-s3',
        'media_to_aws_s3_sync_aws_section'
    );

    add_settings_field(
        'media_to_aws_s3_sync_aws_secret_access_key',
        'AWS Secret Access Key',
        'media_to_aws_s3_sync_secret_access_key_callback',
        'vallarasu-media-bucket-sync-amazon-s3',
        'media_to_aws_s3_sync_aws_section'
    );

    add_settings_field(
        'media_to_aws_s3_sync_aws_region',
        'AWS Region',
        'media_to_aws_s3_sync_region_callback',
        'vallarasu-media-bucket-sync-amazon-s3',
        'media_to_aws_s3_sync_aws_section'
    );

    add_settings_field(
        'media_to_aws_s3_sync_aws_s3_bucket',
        'S3 Bucket Name',
        'media_to_aws_s3_sync_bucket_callback',
        'vallarasu-media-bucket-sync-amazon-s3',
        'media_to_aws_s3_sync_aws_section'
    );
}
add_action( 'admin_init', 'media_to_aws_s3_sync_register_settings' );

/**
 * Settings section callback.
 */
function media_to_aws_s3_sync_section_callback() {
    echo '<p>Configure your AWS S3 bucket details below to enable media syncing. All files will be uploaded as public-read.</p>';
}

/**
 * Fields callbacks.
 */
function media_to_aws_s3_sync_enabled_callback() {
    $value = get_option( 'media_to_aws_s3_sync_enabled', '0' );
    
    // Hidden input ensures '0' is sent if unchecked
    echo '<input type="hidden" name="media_to_aws_s3_sync_enabled" value="0">';
    
    // The Toggle Checkbox
    echo '<label class="m2s3-switch">
            <input type="checkbox" id="m2s3_toggle" name="media_to_aws_s3_sync_enabled" value="1" ' . checked( '1', $value, false ) . '>
            <span class="m2s3-slider"></span>
          </label>';
}

function media_to_aws_s3_sync_access_key_callback() {
    $value = get_option( 'media_to_aws_s3_sync_aws_access_key_id', '' );
    echo '<input type="text" name="media_to_aws_s3_sync_aws_access_key_id" value="' . esc_attr( $value ) . '" class="regular-text" />';
}

function media_to_aws_s3_sync_secret_access_key_callback() {
    $value = get_option( 'media_to_aws_s3_sync_aws_secret_access_key', '' );
    echo '<input type="password" name="media_to_aws_s3_sync_aws_secret_access_key" value="' . esc_attr( $value ) . '" class="regular-text" autocomplete="new-password" />';
}

function media_to_aws_s3_sync_region_callback() {
    $value = get_option( 'media_to_aws_s3_sync_aws_region', 'us-east-1' );
    echo '<input type="text" name="media_to_aws_s3_sync_aws_region" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="e.g. us-east-1" />';
}

function media_to_aws_s3_sync_bucket_callback() {
    $value = get_option( 'media_to_aws_s3_sync_aws_s3_bucket', '' );
    echo '<input type="text" name="media_to_aws_s3_sync_aws_s3_bucket" value="' . esc_attr( $value ) . '" class="regular-text" />';
}

/**
 * Render the settings page.
 */
function media_to_aws_s3_sync_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'media_to_aws_s3_sync_settings_group' );
            do_settings_sections( 'vallarasu-media-bucket-sync-amazon-s3' );
            submit_button( 'Save AWS Settings' );
            ?>
        </form>

        <hr>
        <h2>Bulk Sync Tool</h2>
        <p>Use this tool to automatically sync all existing media attachments to Amazon S3 in the background. It will process files that have not yet been synced.</p>
        <div class="m2s3-bulk-sync-wrapper">
            <button type="button" id="m2s3-bulk-sync-btn" class="button button-primary">Start Bulk Sync</button>
            <span class="spinner" id="m2s3-bulk-sync-spinner"></span>
            
            <div id="m2s3-bulk-sync-progress-container" style="display:none; margin-top: 15px;">
                <div class="m2s3-progress-bar-wrap">
                    <div class="m2s3-progress-bar-fill" id="m2s3-bulk-sync-progress-fill"></div>
                </div>
                <p id="m2s3-bulk-sync-status-text">Processing: 0 / 0</p>
                <div class="m2s3-log-box" id="m2s3-bulk-sync-log"></div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Helper to build the attachment sync control HTML.
 * Decoupled CSS is used instead of inline styles.
 */
function media_to_aws_s3_sync_get_attachment_html( $attachment_id ) {
    $is_enabled = get_option('media_to_aws_s3_sync_enabled', '0');
    
    if ( ! $is_enabled ) {
        return '';
    }

    $is_synced = get_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_synced', true );
    $s3_url    = get_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_url', true );

    $aws_key    = get_option('media_to_aws_s3_sync_aws_access_key_id');
    $aws_secret = get_option('media_to_aws_s3_sync_aws_secret_access_key');
    $aws_region = get_option('media_to_aws_s3_sync_aws_region');
    $aws_bucket = get_option('media_to_aws_s3_sync_aws_s3_bucket');

    $is_configured = ! empty( $aws_key ) && ! empty( $aws_secret ) && ! empty( $aws_region ) && ! empty( $aws_bucket );

    $html = '<div class="media-to-aws-s3-sync-container" data-attachment-id="' . esc_attr( $attachment_id ) . '" data-configured="' . ( $is_configured ? '1' : '0' ) . '">';

    if ( ! $is_configured ) {
        $html .= '<span class="media-to-aws-s3-sync-status-msg error-msg">AWS S3 is not configured</span>';
        $html .= '<p class="description media-to-aws-s3-sync-description">Please configure AWS credentials in <a href="' . esc_url( admin_url( 'options-general.php?page=media-to-aws-s3-sync' ) ) . '" target="_blank">Settings &gt; Media to S3 Sync</a>.</p>';
        $html .= '<div class="media-to-aws-s3-sync-buttons">';
        $html .= '<button type="button" class="button button-secondary media-to-aws-s3-sync-btn">Sync to S3</button>';
        $html .= '</div>';
    } elseif ( $is_synced && ! empty( $s3_url ) ) {
        // If synced, do not show "✓ Synced to S3" text, just fields and buttons.
        $html .= '<input type="text" value="' . esc_url( $s3_url ) . '" readonly class="media-to-aws-s3-sync-url-input regular-text" onclick="this.select();">';
        $html .= '<div class="media-to-aws-s3-sync-buttons">';
        $html .= '<a href="' . esc_url( $s3_url ) . '" target="_blank" class="button button-secondary">View on S3</a>';
        $html .= '<button type="button" class="button button-primary media-to-aws-s3-sync-copy-btn" data-s3-url="' . esc_attr( $s3_url ) . '">Copy URL</button>';
        $html .= '<button type="button" class="button media-to-aws-s3-sync-btn">Re-sync to S3</button>';
        $html .= '</div>';
    } else {
        $html .= '<span class="media-to-aws-s3-sync-status-msg error-msg">Not Synced to S3</span>';
        $html .= '<div class="media-to-aws-s3-sync-buttons">';
        $html .= '<button type="button" class="button button-primary media-to-aws-s3-sync-btn">Sync to S3</button>';
        $html .= '</div>';
    }
    
    $html .= ' <span class="spinner"></span>';
    $html .= '</div>';

    return $html;
}

/**
 * Filter attachment edit fields to add S3 Sync field.
 */
function media_to_aws_s3_sync_attachment_fields_to_edit( $form_fields, $post ) {
    $is_enabled = get_option('media_to_aws_s3_sync_enabled', '0');
    
    if ( ! $is_enabled ) {
        return $form_fields;
    }

    $html = media_to_aws_s3_sync_get_attachment_html( $post->ID );

    $form_fields['media_to_aws_s3_sync'] = array(
        'label' => __( 'S3 Sync', 'vallarasu-media-bucket-sync-amazon-s3' ),
        'input' => 'html',
        'html'  => $html,
    );

    return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'media_to_aws_s3_sync_attachment_fields_to_edit', 10, 2 );

/**
 * AJAX action handler for syncing files.
 */
function media_to_aws_s3_sync_ajax_handler() {
    check_ajax_referer( 'media_to_aws_s3_sync_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array( 'message' => 'Unauthorized action.' ) );
    }

    $attachment_id = isset( $_POST['attachment_id'] ) ? intval( $_POST['attachment_id'] ) : 0;
    if ( ! $attachment_id ) {
        wp_send_json_error( array( 'message' => 'Invalid attachment ID.' ) );
    }

    $file_path = get_attached_file( $attachment_id );
    if ( ! $file_path || ! file_exists( $file_path ) ) {
        wp_send_json_error( array( 'message' => 'File not found on local disk.' ) );
    }

    $mime_type = get_post_mime_type( $attachment_id );
    if ( ! $mime_type ) {
        $mime_type = 'application/octet-stream';
    }

    $upload_dir = wp_upload_dir();
    $relative_path = ltrim( str_replace( $upload_dir['basedir'], '', $file_path ), '/' );
    $s3_key = 'uploads/' . $relative_path;

    // Trigger upload for main file
    $upload_result = Media_To_AWS_S3_Sync_S3::upload( $file_path, $s3_key, $mime_type );

    if ( is_wp_error( $upload_result ) ) {
        wp_send_json_error( array( 'message' => $upload_result->get_error_message() ) );
    }

    // Trigger upload for sub-sizes (thumbnails, medium, large, etc)
    $metadata = wp_get_attachment_metadata( $attachment_id );
    $synced_sizes = array('original');
    $sync_errors = array();

    if ( ! empty( $metadata['sizes'] ) ) {
        $base_dir = dirname( $file_path ) . '/';
        $base_s3_key = dirname( $s3_key ) . '/';
        foreach ( $metadata['sizes'] as $size => $size_info ) {
             $size_path = $base_dir . $size_info['file'];
             $size_s3_key = $base_s3_key . $size_info['file'];
             $size_mime = isset( $size_info['mime-type'] ) ? $size_info['mime-type'] : $mime_type;
             if ( file_exists( $size_path ) ) {
                 $sub_res = Media_To_AWS_S3_Sync_S3::upload( $size_path, $size_s3_key, $size_mime );
                 if ( is_wp_error( $sub_res ) ) {
                     $sync_errors[] = $size . ' (' . $sub_res->get_error_message() . ')';
                 } else {
                     $synced_sizes[] = $size;
                 }
             } else {
                 $sync_errors[] = $size . ' (File not found locally)';
             }
        }
    }

    // Success! Save metadata
    $aws_region = get_option( 'media_to_aws_s3_sync_aws_region' );
    $aws_bucket = get_option( 'media_to_aws_s3_sync_aws_s3_bucket' );
    $s3_url = 'https://' . $aws_bucket . '.s3.' . $aws_region . '.amazonaws.com/' . ltrim( $s3_key, '/' );

    update_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_synced', '1' );
    update_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_url', $s3_url );

    $html = media_to_aws_s3_sync_get_attachment_html( $attachment_id );

    wp_send_json_success( array(
        'html'         => $html,
        's3_url'       => $s3_url,
        'synced_sizes' => $synced_sizes,
        'sync_errors'  => $sync_errors
    ) );
}
add_action( 'wp_ajax_media_to_aws_s3_sync_action', 'media_to_aws_s3_sync_ajax_handler' );

/**
 * AJAX action handler for getting total unsynced attachments.
 */
function media_to_aws_s3_bulk_sync_get_unsynced() {
    check_ajax_referer( 'media_to_aws_s3_sync_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Unauthorized action.' ) );
    }

    $aws_key    = get_option('media_to_aws_s3_sync_aws_access_key_id');
    $aws_secret = get_option('media_to_aws_s3_sync_aws_secret_access_key');
    $aws_region = get_option( 'media_to_aws_s3_sync_aws_region' );
    $aws_bucket = get_option( 'media_to_aws_s3_sync_aws_s3_bucket' );

    if ( empty( $aws_key ) || empty( $aws_secret ) || empty( $aws_region ) || empty( $aws_bucket ) ) {
        wp_send_json_error( array( 'message' => 'AWS S3 is not fully configured.' ) );
    }

    global $wpdb;
    
    // Query attachments that do NOT have the synced meta flag set to '1'
    $query = "
        SELECT p.ID 
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_media_to_aws_s3_sync_s3_synced')
        WHERE p.post_type = 'attachment' 
        AND p.post_status = 'inherit'
        AND (pm.meta_value IS NULL OR pm.meta_value != '1')
        ORDER BY p.ID DESC
    ";

    $unsynced_ids = $wpdb->get_col( $query );

    wp_send_json_success( array(
        'ids'   => $unsynced_ids,
        'total' => count( $unsynced_ids )
    ) );
}
add_action( 'wp_ajax_media_to_aws_s3_bulk_sync_get_unsynced', 'media_to_aws_s3_bulk_sync_get_unsynced' );

/**
 * AJAX action handler for processing a batch of attachments.
 */
function media_to_aws_s3_bulk_sync_process_batch() {
    check_ajax_referer( 'media_to_aws_s3_sync_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Unauthorized action.' ) );
    }

    $attachment_ids = isset( $_POST['attachment_ids'] ) ? array_map( 'intval', (array) $_POST['attachment_ids'] ) : array();
    if ( empty( $attachment_ids ) ) {
        wp_send_json_error( array( 'message' => 'No attachment IDs provided.' ) );
    }

    $results = array();
    $aws_region = get_option( 'media_to_aws_s3_sync_aws_region' );
    $aws_bucket = get_option( 'media_to_aws_s3_sync_aws_s3_bucket' );
    $upload_dir = wp_upload_dir();

    foreach ( $attachment_ids as $attachment_id ) {
        $file_path = get_attached_file( $attachment_id );
        if ( ! $file_path || ! file_exists( $file_path ) ) {
            $results[] = array( 'id' => $attachment_id, 'status' => 'error', 'message' => 'File not found locally.' );
            continue;
        }

        $mime_type = get_post_mime_type( $attachment_id );
        if ( ! $mime_type ) {
            $mime_type = 'application/octet-stream';
        }

        $relative_path = ltrim( str_replace( $upload_dir['basedir'], '', $file_path ), '/' );
        $s3_key = 'uploads/' . $relative_path;

        // Trigger upload for main file
        $upload_result = Media_To_AWS_S3_Sync_S3::upload( $file_path, $s3_key, $mime_type );

        if ( is_wp_error( $upload_result ) ) {
            $results[] = array( 'id' => $attachment_id, 'status' => 'error', 'message' => $upload_result->get_error_message() );
            continue;
        }

        // Trigger upload for sub-sizes
        $metadata = wp_get_attachment_metadata( $attachment_id );
        $has_errors = false;
        if ( ! empty( $metadata['sizes'] ) ) {
            $base_dir = dirname( $file_path ) . '/';
            $base_s3_key = dirname( $s3_key ) . '/';
            foreach ( $metadata['sizes'] as $size => $size_info ) {
                 $size_path = $base_dir . $size_info['file'];
                 $size_s3_key = $base_s3_key . $size_info['file'];
                 $size_mime = isset( $size_info['mime-type'] ) ? $size_info['mime-type'] : $mime_type;
                 if ( file_exists( $size_path ) ) {
                     $sub_res = Media_To_AWS_S3_Sync_S3::upload( $size_path, $size_s3_key, $size_mime );
                     if ( is_wp_error( $sub_res ) ) {
                         $has_errors = true;
                         $results[] = array( 'id' => $attachment_id, 'status' => 'error', 'message' => 'Sub-size ' . $size . ' failed: ' . $sub_res->get_error_message() );
                     }
                 }
            }
        }

        // Success! Save metadata (only if no critical errors, though main file succeeded)
        $s3_url = 'https://' . $aws_bucket . '.s3.' . $aws_region . '.amazonaws.com/' . ltrim( $s3_key, '/' );
        update_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_synced', '1' );
        update_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_url', $s3_url );

        if ( ! $has_errors ) {
            $results[] = array( 'id' => $attachment_id, 'status' => 'success', 'message' => 'Synced successfully.' );
        } else {
            $results[] = array( 'id' => $attachment_id, 'status' => 'info', 'message' => 'Main file synced, but some sub-sizes failed.' );
        }
    }

    wp_send_json_success( array(
        'results' => $results
    ) );
}
add_action( 'wp_ajax_media_to_aws_s3_bulk_sync_process_batch', 'media_to_aws_s3_bulk_sync_process_batch' );

/**
 * Filter to rewrite the main attachment URL to S3.
 */
function media_to_aws_s3_sync_filter_attachment_url( $url, $post_id ) {
    if ( ! get_option( 'media_to_aws_s3_sync_enabled', '0' ) ) {
        return $url;
    }

    $is_synced = get_post_meta( $post_id, '_media_to_aws_s3_sync_s3_synced', true );
    if ( $is_synced ) {
        $s3_url = get_post_meta( $post_id, '_media_to_aws_s3_sync_s3_url', true );
        if ( ! empty( $s3_url ) ) {
            return $s3_url;
        }
    }
    return $url;
}
add_filter( 'wp_get_attachment_url', 'media_to_aws_s3_sync_filter_attachment_url', 99, 2 );

/**
 * Filter to rewrite image src arrays (which includes thumbnails/sub-sizes) to S3.
 */
function media_to_aws_s3_sync_filter_image_src( $image, $attachment_id, $size, $icon ) {
    if ( ! get_option( 'media_to_aws_s3_sync_enabled', '0' ) || ! $image ) {
        return $image;
    }

    $is_synced = get_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_synced', true );
    if ( $is_synced ) {
        $s3_url = get_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_url', true );
        if ( ! empty( $s3_url ) ) {
            $upload_dir = wp_upload_dir();
            $base_url = $upload_dir['baseurl'];
            
            // Extract the S3 base directory (everything before the filename)
            $s3_base = dirname( $s3_url );
            $local_base = dirname( $image[0] );

            // If the local URL starts with the local uploads base, we can swap it safely
            if ( strpos( $image[0], $base_url ) === 0 ) {
                $file_name = wp_basename( $image[0] );
                $image[0] = $s3_base . '/' . $file_name;
            }
        }
    }
    return $image;
}
add_filter( 'wp_get_attachment_image_src', 'media_to_aws_s3_sync_filter_image_src', 99, 4 );

/**
 * Filter to rewrite responsive srcset URLs to S3.
 */
function media_to_aws_s3_sync_filter_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
    if ( ! get_option( 'media_to_aws_s3_sync_enabled', '0' ) || empty( $sources ) ) {
        return $sources;
    }

    $is_synced = get_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_synced', true );
    if ( $is_synced ) {
        $s3_url = get_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_url', true );
        if ( ! empty( $s3_url ) ) {
            $s3_base = dirname( $s3_url );
            
            foreach ( $sources as $width => $source ) {
                $file_name = wp_basename( $source['url'] );
                $sources[ $width ]['url'] = $s3_base . '/' . $file_name;
            }
        }
    }
    return $sources;
}
add_filter( 'wp_calculate_image_srcset', 'media_to_aws_s3_sync_filter_srcset', 99, 5 );

/**
 * Filter the_content to rewrite hardcoded src attributes for S3-synced images embedded in posts.
 */
function media_to_aws_s3_sync_filter_content_images( $content ) {
    if ( ! get_option( 'media_to_aws_s3_sync_enabled', '0' ) ) {
        return $content;
    }

    if ( ! preg_match_all( '/<img [^>]+>/', $content, $matches ) ) {
        return $content;
    }

    $upload_dir = wp_upload_dir();
    $base_url = $upload_dir['baseurl'];

    foreach ( $matches[0] as $image ) {
        // Find the wp-image-{id} class to uniquely identify the attachment
        if ( preg_match( '/wp-image-([0-9]+)/i', $image, $class_id ) ) {
            $attachment_id = absint( $class_id[1] );

            $is_synced = get_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_synced', true );
            if ( $is_synced ) {
                $s3_url = get_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_url', true );
                if ( ! empty( $s3_url ) ) {
                    $s3_base = dirname( $s3_url );

                    // Replace the src attribute specifically
                    if ( preg_match( '/src=["\']([^"\']+)["\']/i', $image, $src_match ) ) {
                        $local_src = $src_match[1];
                        // Verify it's a local upload URL before rewriting
                        if ( strpos( $local_src, $base_url ) === 0 ) {
                            $file_name = wp_basename( $local_src );
                            $new_src = $s3_base . '/' . $file_name;
                            
                            $new_image = str_replace( $src_match[0], 'src="' . esc_url( $new_src ) . '"', $image );
                            $content = str_replace( $image, $new_image, $content );
                        }
                    }
                }
            }
        }
    }
    return $content;
}
add_filter( 'the_content', 'media_to_aws_s3_sync_filter_content_images', 99 );
