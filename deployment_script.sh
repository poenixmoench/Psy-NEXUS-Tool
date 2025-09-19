#!/usr/bin/env bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "🚀 Starting Psy-NEXUS Deployment Process..."
echo "=============================================="

# Ensure we're on the main branch
git checkout main

# Pull latest changes to ensure we're up to date
echo "📥 Pulling latest changes..."
git pull origin main

# Remove the old gh-pages branch completely from the remote repository
echo "🗑️ Removing old gh-pages branch..."
git push origin --delete gh-pages 2>/dev/null || echo "ℹ️ gh-pages branch doesn't exist or already deleted"

# Recreate and force-push the gh-pages branch from the current main branch
echo "🔄 Creating fresh gh-pages branch..."
git push origin main:gh-pages --force

echo ""
echo "✅ Deployment successful! The gh-pages branch has been completely rebuilt."
echo "⏰ Note: It may take 1-2 minutes for GitHub Pages to update."
echo "🌐 Live URL: https://poenixmoench.github.io/Psy-NEXUS-Tool/"
echo ""
echo "✨ For Samsung devices: If caching issues persist, use the new URL"
echo "🌐 NEW LIVE URL: https://poenixmoench.github.io/Psy-NEXUS-Tool/template-tool.html"
