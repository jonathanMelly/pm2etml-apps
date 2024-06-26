#!/bin/bash

#load shared configs
SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
. "$SCRIPT_DIR"/deploy.config.sh

#Put back site online
# shellcheck disable=SC2154
echo "-->Stop Maintenance" && $php artisan up
