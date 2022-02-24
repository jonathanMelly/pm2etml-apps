#!/bin/bash
#WARNING: to be run from base project path: scripts/....sh or .ps1
#PS1 is a symlink (New-Item -ItemType SymbolicLink -Path "update-env.ps1" -Target "update-env.sh")
cp .env .env.$(date +%F_%Hh%mm%Ss)
cp .env.example .env
#TODO: use sed to reuse key...
php artisan key:generate --ansi
echo "WWWGROUP=$(id -g)" >> .env
echo "WWWUSER=$(id -u)" >> .env
