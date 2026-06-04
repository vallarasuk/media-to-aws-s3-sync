#!/bin/bash

# build-zip.sh
# This script packages the Media to AWS S3 Sync plugin into a clean .zip file
# suitable for submission to the WordPress Plugin Directory for review.

PLUGIN_DIR="vallarasu-media-bucket-sync-amazon-s3"
ZIP_NAME="vallarasu-media-bucket-sync-amazon-s3.zip"

echo "📦 Starting packaging process for WordPress Plugin Directory submission..."

# Navigate to the parent directory to zip the folder properly
cd ..

# Remove any existing zip file
if [ -f "$ZIP_NAME" ]; then
    echo "🗑️  Removing old zip file..."
    rm "$ZIP_NAME"
fi

echo "🗜️  Zipping files..."

# Zip the directory while excluding development and testing files
zip -r "$ZIP_NAME" "$PLUGIN_DIR" -x \
    "$PLUGIN_DIR/.git/*" \
    "$PLUGIN_DIR/.gitignore" \
    "$PLUGIN_DIR/tests/*" \
    "$PLUGIN_DIR/phpunit.xml.dist" \
    "$PLUGIN_DIR/TESTING.md" \
    "$PLUGIN_DIR/build-zip.sh" \
    "$PLUGIN_DIR/.*" \
    "*/.DS_Store"

echo ""
if [ -f "$ZIP_NAME" ]; then
    echo "✅ Success! Your plugin has been packaged."
    echo "📄 File: $(pwd)/$ZIP_NAME"
    echo "🚀 You can now upload this .zip file to https://wordpress.org/plugins/developers/add/ for review!"
else
    echo "❌ Error: Failed to create the zip file."
    exit 1
fi
