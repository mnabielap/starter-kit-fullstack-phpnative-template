#!/bin/bash
set -e

echo "--- Starter Kit PHP Native Docker Entrypoint ---"

# 1. Install Composer Dependencies if vendor missing
if [ ! -d "vendor" ]; then
    echo "Installing Composer Dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# 2. Ensure Database Directory Exists & Permissions (For SQLite)
if [ ! -f "database/db.sqlite" ]; then
    echo "Creating empty SQLite database file..."
    touch database/db.sqlite
fi

# 3. Fix Permissions for Persistence Volumes
# We give www-data (Apache user) ownership of the persistent folders
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/database
chown -R www-data:www-data /var/www/html/public/uploads

# 4. Optional: Run Migrations
# Since this is PHP Native, you might want to automate schema import for MySQL
# Example logic (requires mysql-client installed in Dockerfile if you want to use it):
# if [ "$DB_CONNECTION" = "mysql" ]; then
#    echo "Waiting for MySQL..."
#    # Logic to import database/schema.mysql.sql if tables empty
# fi

echo "Starting Apache on port 5005..."
exec "$@"