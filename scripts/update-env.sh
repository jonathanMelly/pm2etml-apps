#!/bin/bash
cp .env .env.$(date +%F_%Hh%mm%Ss)
cp .env.example .env
php artisan key:generate --ansi
