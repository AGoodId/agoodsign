#!/bin/bash
#
# Build a distributable plugin zip for AGoodSign.
# Output: agoodsign.zip in the repo root.
#

set -e

PLUGIN_SLUG="agoodsign"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
BUILD_DIR="$(mktemp -d)"

echo "Building ${PLUGIN_SLUG}.zip …"

# Copy plugin files, excluding dev/build artifacts.
rsync -a --exclude='.git' \
         --exclude='.github' \
         --exclude='.claude' \
         --exclude='.DS_Store' \
         --exclude='node_modules' \
         --exclude='build-zip.sh' \
         --exclude='*.zip' \
         --exclude='SPRINT.md' \
         --exclude='CLAUDE.md' \
         --exclude='.gitignore' \
         "$SCRIPT_DIR/" "$BUILD_DIR/$PLUGIN_SLUG/"

# Create zip.
cd "$BUILD_DIR"
zip -r "${PLUGIN_SLUG}.zip" "$PLUGIN_SLUG/"
mv "${PLUGIN_SLUG}.zip" "$SCRIPT_DIR/"

# Clean up.
rm -rf "$BUILD_DIR"

echo "Done → ${SCRIPT_DIR}/${PLUGIN_SLUG}.zip"
