#!/bin/bash
if [ ! -d /var/www/html/vendor ]; then
    composer install --no-dev --optimize-autoloader --working-dir=/var/www/html
fi
exec apache2-foreground
