FROM php:8.1-apache

# Instalar extensión PDO para PostgreSQL
RUN docker-php-ext-install pdo_pgsql

# Copiar archivos al servidor
COPY . /var/www/html/

EXPOSE 80
