<?php
/**
 * Class Test_Bulk_Sync
 *
 * @package Media_To_Aws_S3_Sync
 */

class Test_Bulk_Sync extends WP_UnitTestCase {

	/**
	 * Setup before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		delete_option( 'media_to_aws_s3_sync_enabled' );
		delete_option( 'media_to_aws_s3_sync_aws_access_key_id' );
		delete_option( 'media_to_aws_s3_sync_aws_secret_access_key' );
		delete_option( 'media_to_aws_s3_sync_aws_region' );
		delete_option( 'media_to_aws_s3_sync_aws_s3_bucket' );
	}

	/**
	 * Test bulk sync AJAX missing credentials check.
	 * 
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_bulk_sync_fails_without_credentials() {
		// Ensure options are missing
		delete_option( 'media_to_aws_s3_sync_aws_access_key_id' );
		
		// Set up mock admin environment
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$_POST['nonce'] = wp_create_nonce( 'media_to_aws_s3_sync_nonce' );

		try {
			media_to_aws_s3_bulk_sync_get_unsynced();
			$this->fail( 'Expected wp_die() to be called via wp_send_json_error().' );
		} catch ( WPAjaxDieContinueException $e ) {
			// This exception is thrown by WP_UnitTestCase when wp_die() is called in AJAX context
			$this->assertTrue( true, 'The function correctly exited with an error when credentials were missing.' );
		}
	}

	/**
	 * Test bulk sync UI renders in settings.
	 */
	public function test_bulk_sync_ui_renders() {
		// Mock admin capability
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		ob_start();
		media_to_aws_s3_sync_render_settings_page();
		$output = ob_end_clean();

		$this->assertStringContainsString( 'id="m2s3-bulk-sync-btn"', $output, 'Bulk sync button is missing.' );
		$this->assertStringContainsString( 'Bulk Sync Tool', $output, 'Bulk sync header is missing.' );
	}
}
