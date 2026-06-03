<?php
/**
 * Class Test_Plugin_Functions
 *
 * @package Media_To_Aws_S3_Sync
 */

class Test_Plugin_Functions extends WP_UnitTestCase {

	/**
	 * Setup before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		// Ensure settings are clean before each test
		delete_option( 'media_to_aws_s3_sync_enabled' );
	}

	/**
	 * Test if the settings are registered.
	 */
	public function test_settings_are_registered() {
		global $wp_settings_fields;
		
		// Run admin_init hooks manually
		do_action( 'admin_init' );

		// Check if our specific fields are registered in the global array
		$this->assertArrayHasKey( 'media-to-aws-s3-sync', $wp_settings_fields, 'Settings page not registered.' );
		$this->assertArrayHasKey( 'media_to_aws_s3_sync_aws_section', $wp_settings_fields['media-to-aws-s3-sync'], 'AWS section not registered.' );
		
		$fields = $wp_settings_fields['media-to-aws-s3-sync']['media_to_aws_s3_sync_aws_section'];
		
		$this->assertArrayHasKey( 'media_to_aws_s3_sync_enabled', $fields );
		$this->assertArrayHasKey( 'media_to_aws_s3_sync_aws_access_key_id', $fields );
		$this->assertArrayHasKey( 'media_to_aws_s3_sync_aws_secret_access_key', $fields );
		$this->assertArrayHasKey( 'media_to_aws_s3_sync_aws_region', $fields );
		$this->assertArrayHasKey( 'media_to_aws_s3_sync_aws_s3_bucket', $fields );
	}

	/**
	 * Test HTML output when master toggle is disabled.
	 */
	public function test_attachment_html_disabled() {
		// Ensure toggle is OFF
		update_option( 'media_to_aws_s3_sync_enabled', '0' );

		// Create a dummy attachment ID
		$attachment_id = 123;

		$html = media_to_aws_s3_sync_get_attachment_html( $attachment_id );
		
		$this->assertEquals( '', $html, 'HTML should be completely empty when disabled.' );
	}

	/**
	 * Test HTML output when master toggle is enabled but not configured.
	 */
	public function test_attachment_html_enabled_but_not_configured() {
		// Ensure toggle is ON
		update_option( 'media_to_aws_s3_sync_enabled', '1' );

		// Create a dummy attachment ID
		$attachment_id = 123;

		$html = media_to_aws_s3_sync_get_attachment_html( $attachment_id );
		
		$this->assertStringContainsString( 'media-to-aws-s3-sync-container', $html );
		$this->assertStringContainsString( 'AWS S3 is not configured', $html );
	}

	/**
	 * Test frontend URL filtering when synced.
	 */
	public function test_attachment_url_filtering() {
		// Mock local URL and ID
		$attachment_id = 123;
		$local_url = 'http://example.com/wp-content/uploads/2026/06/test.jpg';
		$s3_url = 'https://dummybucket.s3.us-east-1.amazonaws.com/uploads/2026/06/test.jpg';

		// Set mock post meta
		update_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_synced', '1' );
		update_post_meta( $attachment_id, '_media_to_aws_s3_sync_s3_url', $s3_url );

		// Test when disabled
		update_option( 'media_to_aws_s3_sync_enabled', '0' );
		$filtered_url_disabled = apply_filters( 'wp_get_attachment_url', $local_url, $attachment_id );
		$this->assertEquals( $local_url, $filtered_url_disabled, 'URL should remain local when plugin is disabled.' );

		// Test when enabled
		update_option( 'media_to_aws_s3_sync_enabled', '1' );
		$filtered_url_enabled = apply_filters( 'wp_get_attachment_url', $local_url, $attachment_id );
		$this->assertEquals( $s3_url, $filtered_url_enabled, 'URL should be rewritten to S3 when plugin is enabled.' );
	}
}
