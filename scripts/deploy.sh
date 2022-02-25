#!/bin/bash
#Should be called upon deployment (prod)
#cd $PWD probably useless
log=storage/logs/deploy-$(date +%F_%Hh%MM%Ss).log
php='/opt/php81/bin/php'
composer="$php /usr/lib64/plesk-9.0/composer.phar"
$composer install --optimize-autoloader --no-dev --no-interaction 2>&1 >> $log
#TODO Regenerate key ??
$php artisan migrate --no-interaction --force 2>&1 >> $log
$php artisan optimize:clear 2>&1 >> $log
$php artisan optimize 2>&1 >> $log
#done by optimize
#$php artisan config:cache 2>&1 >> $log
$php artisan event:cache 2>&1 >> $log
$php artisan permission:cache-reset 2>&1 >> $log
#done by optimize
#$php artisan route:cache 2>&1 >> $log
$php artisan view:cache 2>&1 >> $log

#TODO put back site online [add put it offline with azure devops web hook]
#curl ...
