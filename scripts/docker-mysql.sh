docker run --name pm2etml-mariadb --detach --env MARIADB_ROOT_PASSWORD=123 -p3306:3306 -v pm2etml-db:/var/lib/mysql  mariadb:10.3.34
#And then docker exec -it pm2etml-mariadb /bin/mysql -u root -p => create database pm2etml_intranet
