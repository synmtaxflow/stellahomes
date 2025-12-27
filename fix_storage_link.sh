#!/bin/bash

# Script to fix storage link for cPanel
# Run this script from project root: /home/stellahomes/repositories/stellahomes

echo "Fixing storage link for cPanel..."

# Navigate to project root (adjust path if needed)
cd /home/stellahomes/repositories/stellahomes || exit 1

# Remove old links if they exist
echo "Removing old storage links..."
rm -f public/storage
rm -f public_html/storage

# Create correct link in public_html (cPanel document root)
echo "Creating storage link in public_html..."
ln -s ../storage/app/public public_html/storage

# Verify the link
echo "Verifying storage link..."
if [ -L "public_html/storage" ]; then
    echo "✓ Storage link created successfully!"
    ls -la public_html/ | grep storage
    echo ""
    echo "Link target:"
    readlink -f public_html/storage
else
    echo "✗ Error: Storage link was not created!"
    exit 1
fi

# Check if storage directory exists
if [ ! -d "storage/app/public" ]; then
    echo "Creating storage directories..."
    mkdir -p storage/app/public/blocks
    mkdir -p storage/app/public/rooms
    mkdir -p storage/app/public/profile_pictures
    mkdir -p storage/app/public/owner-profiles
    mkdir -p storage/app/public/landing-page
    mkdir -p storage/app/public/hostel
    
    # Set permissions
    chmod -R 755 storage/app/public
    echo "✓ Storage directories created with proper permissions"
fi

echo ""
echo "Done! Storage link is now correctly configured for cPanel."



