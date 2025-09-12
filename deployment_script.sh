#!/bin/bash
# psy-nexus-deployment script

echo "Starting the Psy-Nexus deployment process..."

# Define your GitHub repository URL
REPO_URL="https://github.com/poenixmoench/Psy-NEXUS-Tool.git"

# Define the local directory where the repository will be cloned
# This will be the current directory where the script is executed
INSTALL_DIR=$(pwd)

echo "Cloning the repository from $REPO_URL into $INSTALL_DIR..."

# Check if the directory already exists
if [ -d "$INSTALL_DIR/Psy-NEXUS-Tool" ]; then
    echo "Directory already exists. Updating the repository..."
    cd "$INSTALL_DIR/Psy-NEXUS-Tool"
    git pull
else
    echo "Cloning the repository..."
    git clone "$REPO_URL" "$INSTALL_DIR/Psy-NEXUS-Tool"
    cd "$INSTALL_DIR/Psy-NEXUS-Tool"
fi

echo "Repository has been successfully cloned/updated."
echo "Deployment complete."
