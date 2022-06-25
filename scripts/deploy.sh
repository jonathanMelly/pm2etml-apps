#!/bin/bash
############# START CONFIG ############
SHA=$1
if [ -z "$SHA" ] || [ "$SHA" = "--help" ] ; then
  echo "Usage: deploy.sh <SHA> [--no-interaction]"
  exit 1
fi
NO_INTERACTION=$2

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
  php='/opt/php81/bin/php'

  composer=$(which composer)

  composer_install="$php $composer install --optimize-autoloader --no-dev --no-interaction"

  #FIRST install
  if [ ! -d "vendor" ]; then
      echo "FIRST INSTALL"
      {
        $composer_install && $php artisan key:generate --no-interaction --force
      } >> "$log" 2>&1
  fi

  #STANDARD for each deploy
  {
      #MAINTENANCE MODE
      $php artisan down && \

      #GIT UPDATE
      git merge --ff-only "$SHA" && \

      #COMPOSER UPDATE
      $composer_install && \

      #CACHE REGEN
      $php artisan optimize:clear && \
      $php artisan optimize && \
      #done by optimize
      #$php artisan config:cache 2>&1 >> $log
      $php artisan event:cache && \
      $php artisan permission:cache-reset && \
      #done by optimize
      #$php artisan route:cache 2>&1 >> $log
      $php artisan view:cache && \

      #Backup DB
      $php artisan backup:run --only-db && \

      #Migrate
      $php artisan migrate --no-interaction --force && \

      #Put back site online
      $php artisan up
  } >> "$log" 2>&1

  cat "$log"
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
git fetch || exit $?
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

