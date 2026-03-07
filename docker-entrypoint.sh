#!/bin/bash
if [ ! -d /var/www/html/vendor ]; then
    composer install --no-dev --optimize-autoloader --working-dir=/var/www/html
fi

mkdir -p /var/www/html/Plakhotnikov_office_6/generated
chown -R www-data:www-data /var/www/html/Plakhotnikov_office_6/generated

exec apache2-foreground
