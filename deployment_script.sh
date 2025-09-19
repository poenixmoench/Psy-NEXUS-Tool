#!/usr/bin/env bash

# Exit immediately if a command exits with a non-zero status.
set -e

# Switch to the main branch
git checkout main

# Remove the old gh-pages branch completely from the remote repository
git push origin --delete gh-pages || true

# Recreate and force-push the gh-pages branch from the current main branch
git push origin main:gh-pages --force

echo "Deployment successful! The gh-pages branch has been completely rebuilt."
