# Testing Procedures for Media to AWS S3 Sync

This document outlines how to set up the testing environment and run the test suite for the `media-to-aws-s3-sync` plugin using standard WordPress PHPUnit practices.

## Prerequisites

1.  **WP-CLI**: Ensure you have WP-CLI installed. If not, follow instructions at [wp-cli.org](https://wp-cli.org/).
2.  **PHPUnit**: PHPUnit 7.x (for older PHP/WP versions) or 9.x depending on your PHP version.
3.  **Local Database**: A local MySQL/MariaDB database server running.
4.  **Subversion (SVN)**: The WP test library setup script requires `svn` to download WordPress core test files.

## Step 1: Initialize the Testing Environment

WordPress provides a script to set up a separate database dedicated for testing so it doesn't touch your local site's data.

Run the following command in your terminal. Replace the placeholders with your local database credentials:

```bash
cd /path/to/wp-content/plugins/media-to-aws-s3-sync
bash bin/install-wp-tests.sh <test_db_name> <db_user> <db_pass> [db_host] [wp_version] [skip_database_creation]
```
*Example:* `bash bin/install-wp-tests.sh wordpress_test root password localhost latest`
*(Note: If you don't have the `bin/install-wp-tests.sh` script, you can scaffold it using wp-cli: `wp scaffold plugin-tests media-to-aws-s3-sync`)*

## Step 2: Run the Tests

Once the test database and WordPress testing library are set up, you can run the test suite using PHPUnit.

Navigate to the plugin directory and run:

```bash
phpunit
```

## What the Tests Check

-   `tests/test-plugin-functions.php`: Verifies that settings are properly registered, callbacks generate the correct HTML, and the global toggle effectively hides/shows the sync UI.
-   `tests/test-s3-class.php`: Tests the internal upload methods to ensure error handling is correct when AWS credentials are not fully configured.
