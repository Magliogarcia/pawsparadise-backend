#!/bin/sh
sed -i "s/\${PORT}/$PORT/g" /etc/nginx/http.d/default.conf
php-fpm -D
nginx -g "daemon off;"