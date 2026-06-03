<?php
/**
 * Class Test_S3_Class
 *
 * @package Media_To_Aws_S3_Sync
 */

class Test_S3_Class extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		// Clear credentials to test failure conditions
		update_option( 'media_to_aws_s3_sync_aws_access_key_id', '' );
		update_option( 'media_to_aws_s3_sync_aws_secret_access_key', '' );
		update_option( 'media_to_aws_s3_sync_aws_region', '' );
		update_option( 'media_to_aws_s3_sync_aws_s3_bucket', '' );
	}

	/**
	 * Test that upload returns WP_Error when not configured.
	 */
	public function test_upload_returns_error_if_not_configured() {
		$file_path = '/tmp/dummy.jpg';
		$s3_key = 'uploads/dummy.jpg';
		$mime_type = 'image/jpeg';

		$result = Media_To_AWS_S3_Sync_S3::upload( $file_path, $s3_key, $mime_type );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'missing_credentials', $result->get_error_code() );
	}

	/**
	 * Test that upload returns WP_Error when file does not exist.
	 */
	public function test_upload_returns_error_if_file_missing() {
		// Mock config
		update_option( 'media_to_aws_s3_sync_aws_access_key_id', 'dummy' );
		update_option( 'media_to_aws_s3_sync_aws_secret_access_key', 'dummy' );
		update_option( 'media_to_aws_s3_sync_aws_region', 'dummy' );
		update_option( 'media_to_aws_s3_sync_aws_s3_bucket', 'dummy' );

		$file_path = '/tmp/nonexistent-file.jpg';
		$s3_key = 'uploads/nonexistent-file.jpg';
		$mime_type = 'image/jpeg';

		$result = Media_To_AWS_S3_Sync_S3::upload( $file_path, $s3_key, $mime_type );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'file_not_found', $result->get_error_code() );
	}
}
