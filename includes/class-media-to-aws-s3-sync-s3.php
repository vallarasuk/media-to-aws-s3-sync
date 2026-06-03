<?php
/**
 * Class to handle S3 uploads using AWS Signature Version 4.
 *
 * @package           MediaToAwsS3Sync
 * @subpackage        MediaToAwsS3Sync/includes
 * @author            Vallarasu Kanthasamy
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Media_To_AWS_S3_Sync_S3 {

    /**
     * Upload a local file to S3 using AWS Signature Version 4.
     *
     * @param string $file_path Absolute path to local file.
     * @param string $s3_key    Target S3 key/path.
     * @param string $mime_type MIME type of the file.
     * @return true|WP_Error True on success, WP_Error on failure.
     */
    public static function upload( $file_path, $s3_key, $mime_type ) {
        $aws_key    = get_option('media_to_aws_s3_sync_aws_access_key_id');
        $aws_secret = get_option('media_to_aws_s3_sync_aws_secret_access_key');
        $aws_region = get_option('media_to_aws_s3_sync_aws_region');
        $aws_bucket = get_option('media_to_aws_s3_sync_aws_s3_bucket');

        if (empty($aws_key) || empty($aws_secret) || empty($aws_region) || empty($aws_bucket)) {
            return new WP_Error('s3_missing_credentials', 'AWS credentials or S3 bucket not configured.');
        }

        $file_content = @file_get_contents($file_path);
        if ($file_content === false) {
            return new WP_Error('s3_file_error', 'Failed to read local file: ' . $file_path);
        }

        $host = $aws_bucket . '.s3.' . $aws_region . '.amazonaws.com';
        $request_uri = '/' . ltrim($s3_key, '/');
        $endpoint = 'https://' . $host . $request_uri;

        $amz_date = gmdate('Ymd\THis\Z');
        $date_stamp = gmdate('Ymd');
        $payload_hash = hash('sha256', $file_content);

        $method = 'PUT';
        $canonical_uri = str_replace('%2F', '/', rawurlencode($request_uri));
        $canonical_query = '';
        
        $canonical_headers = "content-type:" . strtolower($mime_type) . "\n" .
                             "host:" . strtolower($host) . "\n" .
                             "x-amz-acl:public-read\n" .
                             "x-amz-content-sha256:" . $payload_hash . "\n" .
                             "x-amz-date:" . $amz_date . "\n";
        
        $signed_headers = 'content-type;host;x-amz-acl;x-amz-content-sha256;x-amz-date';

        $canonical_request = implode("\n", array(
            $method,
            $canonical_uri,
            $canonical_query,
            $canonical_headers,
            $signed_headers,
            $payload_hash
        ));

        $algorithm = 'AWS4-HMAC-SHA256';
        $credential_scope = $date_stamp . '/' . $aws_region . '/s3/aws4_request';
        
        $string_to_sign = implode("\n", array(
            $algorithm,
            $amz_date,
            $credential_scope,
            hash('sha256', $canonical_request)
        ));

        // Sign the string
        $k_date = hash_hmac('sha256', $date_stamp, 'AWS4' . $aws_secret, true);
        $k_region = hash_hmac('sha256', $aws_region, $k_date, true);
        $k_service = hash_hmac('sha256', 's3', $k_region, true);
        $k_signing = hash_hmac('sha256', 'aws4_request', $k_service, true);
        $signature = hash_hmac('sha256', $string_to_sign, $k_signing);

        $authorization_header = "$algorithm Credential=$aws_key/$credential_scope, SignedHeaders=$signed_headers, Signature=$signature";

        $headers = array(
            'Content-Type' => $mime_type,
            'Host' => $host,
            'x-amz-acl' => 'public-read',
            'x-amz-content-sha256' => $payload_hash,
            'x-amz-date' => $amz_date,
            'Authorization' => $authorization_header,
        );

        $args = array(
            'method' => 'PUT',
            'headers' => $headers,
            'body' => $file_content,
            'timeout' => 60,
            'sslverify' => true,
        );

        $response = wp_remote_request($endpoint, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            $body = wp_remote_retrieve_body($response);
            return new WP_Error('s3_upload_failed', 'S3 upload failed with HTTP code ' . $code . '. Response: ' . $body);
        }

        return true;
    }
}
