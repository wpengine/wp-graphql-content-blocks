#!/bin/bash

# Exits with a status of 0 (true) if provided version number is higher than proceeding numbers.
version_gt() {
    test "$(printf '%s\n' "$@" | sort -V | head -n 1)" != "$1";
}

cd /var/www/html/wp-content/plugins/$WP_PLUGIN_FOLDER

# Setup WordPress test core files and database
bash -c "./bin/install-wp-tests.sh $WP_TESTS_DB_NAME $WORDPRESS_DB_USER $WORDPRESS_DB_PASSWORD $WORDPRESS_DB_HOST $WP_VERSION"

# Install composer deps
composer install

# Back to the root WP folder
cd /var/www/html/
