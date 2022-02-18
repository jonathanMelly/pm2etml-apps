if(!(Test-NetConnection -ComputerName "localhost"  -Port 80 -InformationLevel quiet)) {
    php artisan serve --port 80 &
 }else {
    echo "server already running"
 }

