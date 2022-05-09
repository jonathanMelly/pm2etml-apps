docker run --detach --env MARIADB_ROOT_PASSWORD=123 -p3306:3306  mariadb:10.3.34
#And then docker exec -it ee2324aff825 /bin/mysql -u root -p => create database pm2etml_intranet
