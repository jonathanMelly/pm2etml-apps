# PM2ETML-INTRANET [![wakatime](https://wakatime.com/badge/user/bf7fcc14-d7d0-41c4-99cb-bbe8ecef41bf/project/4fb00346-5e05-4e6b-a906-57e91c256d09.svg)](https://wakatime.com/badge/user/bf7fcc14-d7d0-41c4-99cb-bbe8ecef41bf/project/4fb00346-5e05-4e6b-a906-57e91c256d09) ![coverage](http://intranet.pm2etml.ch/coverage_badge.svg)

Plateforme de mise en relation entre le monde du travail et les apprentis...


## Environnement de développement
Si vous voulez contribuer au projet, voici comment monter l’environnement de développement.

### Clone du dépôt
```shell
git clone git@github.com:jonathanMelly/pm2etml-intranet.git
```

### Prérequis
- PHP 8.1
- Composer
- NPM

OU

- Docker (avec sail, voir docker-compose.yml)

### Dépendances PHP
Si nécessaire, installer composer (et PHP 8.1 par la même occasion)
```shell
composer install
```

### Dépendances Javascript
Si nécessaire, installer NPM
```shell
npm install
npm run dev
```

### Fichier de configuration
```shell
cp .env.example .env
php artisan key:generate --ansi
```

#### Base de données
Adapter si nécessaire la configuration (fichier .env) avec la base de données utilisée (voir ci-après avec docker)

##### Mot de passe
###### Version simple
Décommenter / Ajouter cette ligne dans le fichier .env
```text
FAKE_AUTHENTICATOR_PASSWORD=123456789
```

###### Version complète
La fin du fichier [config/auth.php](https://raw.githubusercontent.com/jonathanMelly/pm2etml-intranet/dev/config/auth.php) expose la mécanique utilisée et donc la possibilité d’adapter la configuration selon ses besoins...

### Base de données
Il faut impérativement utiliser MariaDB car le projet se base sur une utilisation particulière de la clause Group By...
Si besoin, démarrer une instance avec Docker
```shell
docker run --detach --env MARIADB_ROOT_PASSWORD=123 -p3306:3306  mariadb:10.3.34
```
Puis créer/remplir la base de données
```shell
php artisan migrate:fresh --seed
```

## Démarrage de l’application
```shell
php artisan serve --port 80
```

## Rechargement à chaud des ressources javascript/css
```shell
npm run watch
```
