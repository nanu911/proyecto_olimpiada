FROM php:8.1-apache

# Instalar la extensión PDO para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Copiar los archivos de tu proyecto
COPY . /var/www/html/

EXPOSE 80
