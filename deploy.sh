#!/bin/bash

# Exit instantly if any command fails
set -e

# Define your exact local directory paths
SRC_DIR="/home/vallarasu/Desktop/Personal/vallarasu-media-bucket-sync-amazon-s3"
SVN_DIR="/home/vallarasu/Desktop/Personal/svn-plugin/vallarasu-media-bucket-sync-amazon-s3"
MAIN_FILE="vallarasu-media-bucket-sync-amazon-s3.php"

echo "=================================================="
echo "🚀 Starting Automated WordPress SVN Deployment"
echo "=================================================="

# 1. Read version from your main local PHP file header
VERSION=$(grep -i "Version:" "$SRC_DIR/$MAIN_FILE" | awk -F: '{print $2}' | sed 's/ //g' | tr -d '\r')

if [ -z "$VERSION" ]; then
    echo "❌ Error: Could not detect version number from $MAIN_FILE"
    exit 1
fi

echo "📦 Detected Version to Deploy: $VERSION"

# 2. Confirm deployment with the user
read -p "Do you want to deploy version $VERSION live? (y/n): " confirm
if [[ $confirm != [yY] && $confirm != [yY][eE][sS] ]]; then
    echo "❌ Deployment cancelled."
    exit 1
fi

# 3. Ask for the SVN Commit/Changelog Message
read -p "📝 Enter commit message (e.g., Bug fixes and enhancements): " COMMIT_MSG
if [ -z "$COMMIT_MSG" ]; then
    COMMIT_MSG="Update to version $VERSION"
fi

echo "🧹 Cleaning out the SVN trunk folder..."
rm -rf "$SVN_DIR/trunk/"*

echo "📂 Copying fresh files from development to SVN trunk..."
cp -r "$SRC_DIR"/{admin,includes,tests,assets,index.php,uninstall.php,readme.txt,$MAIN_FILE,CHANGELOG.md,README.md,TESTING.md,build-zip.sh,phpunit.xml.dist} "$SVN_DIR/trunk/"

# Remove the deploy script itself from trunk so it stays private on your system
rm -f "$SVN_DIR/trunk/deploy.sh"

echo "⚙️ Syncing SVN tracking files..."
cd "$SVN_DIR"

# Tell SVN to look for newly added files, and mark deleted files to be removed
svn status | grep '^\?' | sed 's/^[? ]*//' | xargs -I{} svn add "{}" --force || true
svn status | grep '^\!' | sed 's/^[! ]*//' | xargs -I{} svn rm "{}" || true

# 4. Handle Subversion Tagging for Release Management
if [ -d "$SVN_DIR/tags/$VERSION" ]; then
    echo "⚠️ Tag $VERSION already exists. Overwriting tag..."
    rm -rf "$SVN_DIR/tags/$VERSION"
fi

echo "🏷️ Creating release tag $VERSION..."
cp -r "$SVN_DIR/trunk" "$SVN_DIR/tags/$VERSION"
svn add "tags/$VERSION" --force || true

# 5. Commit Live to WordPress Directory
echo "🌐 Transmitting file updates to WordPress SVN servers..."
svn commit -m "$COMMIT_MSG"

echo "=================================================="
echo "🎉 SUCCESS! Version $VERSION is live on WordPress.org!"
echo "=================================================="
