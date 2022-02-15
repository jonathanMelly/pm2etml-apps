#!/bin/bash
cp .env .env.$(date +%F_%Hh%mm%Ss)
cp .env.example .env
#TODO: use sed to reuse key...
php artisan key:generate --ansi
echo "WWWGROUP=$(id -g)" >> .env
echo "WWWUSER=$(id -u)" >> .env
