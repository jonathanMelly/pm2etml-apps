#!/bin/bash

#load shared config
. deploy.config.sh

#Put back site online
# shellcheck disable=SC2154
echo "-->Stop Maintenance" && $php artisan up
