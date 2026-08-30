FROM php:8.4-apache
RUN docker-php-ext-install pdo_mysql
RUN a2enmod rewrite
RUN sed -ri 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
