#!/bin/bash
while true
do
    php /var/www/html/artisan schedule:run >> /dev/null 2>&1
    sleep 60
done
