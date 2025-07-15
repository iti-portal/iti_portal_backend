#!/bin/sh

# Exit immediately if a command exits with a non-zero status.
set -e

# Create storage link
echo "Creating storage link..."
php artisan storage:link

# Execute the main container command
exec "$@"
