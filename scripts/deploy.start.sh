#!/bin/bash
############# START CONFIG ############
SHA=$1
if [ -z "$SHA" ] || [ "$SHA" = "--help" ] ; then
  echo "Usage: deploy.sh <SHA> [--no-interaction]"
  exit 1
fi
NO_INTERACTION=$2

FIRST_DEPLOY=0

################ END CONFIG ##########

function reviewAndDelay()
{
    SHORT_STAT=$(git diff --shortstat "$SHA")

    echo -n "Starting deploy [${SHORT_STAT} @ ${SHA}] in (press CTRL-C to cancel) : "

    #DELAY to let a last possibility to stop
    for ((i=5;i>=1;i--));
    do
       echo -n "$i "
       sleep 1
    done
    echo -e "\nGO GO GO"
}

#MAIN BUSINESS
#Only MODIFY THIS function to avoid script issues
function deploy()
{
  reviewAndDelay

  log=storage/logs/deploy-$(date +%F_%Hh%MM%Ss).log

  #load shared configs
  SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
  . "$SCRIPT_DIR"/deploy.config.sh

  #sets the rest
  # shellcheck disable=SC2154
  composer_install="$composer install --optimize-autoloader --no-dev --no-interaction"

  tee="/bin2/tee -a"

  app_url=$(grep "APP_URL=" .env | awk -F'=' '{print $2}')
  secret="$RANDOM"
  cookie=".tmpcookie"

  urlSecret="$app_url/$secret"
  urlOptimize="$app_url/deploy/optimize"

  curl="curl"

  #MAINTENANCE MODE (except on first time as artisan has not been installed by composer)
  {
      if [ ! -d "vendor" ]; then
        echo "FIRST DEPLOY, regen app key"
        $composer_install && $php artisan key:generate --no-interaction --force
        #Disable next composer install as already done...
        composer_install=":"
      else
        $php artisan down --secret "$secret"
      fi
  } 2>&1 | $tee "$log"

  #STANDARD for each deploy
  {
      #GIT UPDATE
      echo "-->Git update" && \
      git merge --ff-only "$SHA" && \

      #COMPOSER UPDATE
      #Not needed on first deploy...
      echo "-->Composer install" && \
      $composer_install && \

      #CACHE REGEN
      echo "-->Artisan optimize" && \
      $php artisan optimize:clear && \

      #DONE BY OPTIMIZE
      #Configuration cached successfully!
      #Route cache cleared!
      #Routes cached successfully!
      #Files cached successfully!
      #/!\WARNING
      #Because of hosting CHROOT, config:cache must be run under HTTP env
      #$php artisan optimize && \
      echo "-->Web optimize" && \
      echo "---->Auth cookie at: $urlSecret" && $curl -s -c $cookie -o $cookie.out "$urlSecret" && \
      echo "---->Call optimize at: $urlOptimize" && $curl -s -b $cookie "$urlOptimize" && \
      rm "$cookie" && rm "$cookie.out" \

      #RESET remaining caches
      echo "-->Cache Events" && \
      $php artisan event:cache && \
      echo "-->Reset permission cache" && \
      $php artisan permission:cache-reset && \
      echo "-->Cache views" && \
      $php artisan view:cache && \

      #Backup DB
      echo "-->DB backup" && \
      $php artisan backup:run --only-db && \
      echo "-->DB backup OK" && \

      #Migrate
      echo "-->DB migrate" && \
      $php artisan migrate --no-interaction --force && \
      echo "-->DB migrate OK" && echo "deploy finished, [App is still offline, key=$secret], waiting for post actions before going UP again" \


  } 2>&1 | $tee "$log"

}
##END MAIN BUSINESS

function confirmDeploy()
{
  echo "######################################################################################"
  echo "# READY TO DEPLOY following CHANGES for rev ${SHA} #"
  echo "######################################################################################"
  git diff "$SHA" --compact-summary

  echo ""

  read -r -p "Do you really want to DEPLOY this ? [y/N] " response
  case "$response" in
      [yY][eE][sS]|[yY])
          deploy
          ;;
      *)
          echo "Operation cancelled by user"
          ;;
  esac

}

#Check if script has been modified (checkout and start other version... which has another path so $0 won’t match a second time)
#Remove ./ if present to match git diff and detect if script has been modified
DEPLOY_SCRIPT_CLEAN=$(echo "$0"| sed -e s~^\./~~)
UPDATED_REPO=".repo-$SHA"
GIT_SSL_NO_VERIFY=true git fetch || exit $?
git diff --stat "$SHA" | grep "$DEPLOY_SCRIPT_CLEAN"
LAST=$?
if [ $LAST -eq 0 ]; then
  echo "/!\DEPLOY SCRIPT UPDATE DETECTED - RUNNING UPDATED VERSION/!\ "
  git worktree add -q "$UPDATED_REPO" "$SHA" && \
    bash "$UPDATED_REPO/$0" "$@" && git worktree remove "$UPDATED_REPO"
else
  #No changes in deploy script, we can continue with that script
  if [ "$NO_INTERACTION" = "--no-interaction" ] ; then
    deploy
  else
    confirmDeploy
  fi
fi

