#!/usr/bin/env bash
composer install --no-interaction --prefer-dist --optimize-autoloader
echo "" | sudo -S service php7.3-fpm reload

if [ -f artisan ]
then
    php artisan migrate --force
    php artisan cache:clear
    #php artisan queue:work --timeout=0
    php artisan optimize
fi