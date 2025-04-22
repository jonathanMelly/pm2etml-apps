#!/bin/bash
#common stuff between deploy scripts
# shellcheck disable=SC2034
php='/opt/php82/bin/php'

#taken from /usr/local/bin/composer (which composer)
composer_plesk='/usr/local/psa/var/modules/composer/composer.phar'
composer="$php $composer_plesk"
