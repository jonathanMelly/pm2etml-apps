#!/bin/bash
#Should be called upon deployment (prod)

log=storage/logs/deploy-$(date +%F_%Hh%MM%Ss).log
php='/opt/php81/bin/php'

#TODO use already installed composer with php8... (kreativmedia)
composer="$php /usr/lib64/plesk-9.0/composer.phar"

#Set MAINTENANCE MODE
#A)using artisan-remote (ideally this should be done before replacing files but currently azure devops webhook does not support custom json payload...)
#website=$(basename $PWD)
#api_url="https://$website/pilotage/commands/invoke"
#token=$(grep PILOTAGE_API_KEY .env | awk -F'=' '{print $2}')
#header="Content-type: application/json\nAuthorization: Bearer $token"
#curl -X POST -H "$header" -d '{"name":"down"}' $api_url 2>&1 >> $log

#B)using local artisan
#This will fail on FIRST deploy but as there was nothing before it’s not a problem
{
    $php artisan down && \

    # 2>&1 doesn’t seem to work with composer....
    $composer install --optimize-autoloader --no-dev --no-interaction && \
    #TODO Regenerate key ??

    #Backup DB
    $php artisan backup:run --only-db && \

    $php artisan migrate --no-interaction --force && \

    $php artisan optimize:clear && \
    $php artisan optimize && \
    #done by optimize
    #$php artisan config:cache 2>&1 >> $log
    $php artisan event:cache && \
    $php artisan permission:cache-reset && \
    #done by optimize
    #$php artisan route:cache 2>&1 >> $log
    $php artisan view:cache && \

    #Put back site online
    $php artisan up
} >> "$log" 2>&1
