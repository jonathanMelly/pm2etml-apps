# PM2ETML-INTRANET [![wakatime](https://wakatime.com/badge/user/bf7fcc14-d7d0-41c4-99cb-bbe8ecef41bf/project/4fb00346-5e05-4e6b-a906-57e91c256d09.svg)](https://wakatime.com/badge/user/bf7fcc14-d7d0-41c4-99cb-bbe8ecef41bf/project/4fb00346-5e05-4e6b-a906-57e91c256d09) ![coverage](http://intranet.pm2etml.ch/coverage_badge.svg)

Plateforme de mise en relation entre le monde du travail et les apprentis...


## Environnement de développement
Si vous voulez contribuer au projet, voici comment monter l’environnement de développement.

### Clone du dépôt
```bash
git clone git@github.com:jonathanMelly/pm2etml-intranet.git
```

### Prérequis
- PHP 8.1
- Composer
- NPM

OU

- Docker (avec sail, voir docker-compose.yml)

### Dépenedances PHP
Si nécessaire, installer composer (et PHP 8.1 par la même occasion)
```bash
composer install
```

### Dépendances Javascript
Si nécessaire, installer NPM
```bash
npm install
npm run dev
```

### Fichier de configuration
```bash
cp .env-example .env
php artisan key:generate --ansi
```
*Adapter si nécessaire la configuration avec la base de données*

### Base de données
Il faut impérativement utiliser MariaDB car le projet se base sur une utilisation particulière de la clause Group By...
Si besoin, démarrer une instance avec Docker
```bash
docker run --detach --env MARIADB_ROOT_PASSWORD=123 -p3306:3306  mariadb:10.3.34
php artisan migrate:fresh --seed
```

## Démarrage de l’application
```bash
php artisan serve --port 80
```

## Rechargement à chaud des ressources javascript/css
```bash
npm run watch
```
