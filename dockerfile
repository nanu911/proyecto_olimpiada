FROM php:8.1-apache

# Instala extensiones necesarias para MySQL (pdo_mysql)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copia tu aplicación
COPY . /var/www/html/

EXPOSE 80
