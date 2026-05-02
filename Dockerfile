FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite headers

COPY . /var/www/html/

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

ENV APACHE_PORT=8080
RUN sed -i 's/Listen 80/Listen ${APACHE_PORT}/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:${APACHE_PORT}>/' /etc/apache2/sites-enabled/000-default.conf

RUN chmod +x /usr/sbin/apache2ctl

EXPOSE 8080

CMD ["apache2ctl", "-D", "FOREGROUND"]
