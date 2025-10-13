FROM php:8.2-apache

# Instala todas las dependencias necesarias para Firebase SDK y libsodium
RUN apt-get update && apt-get install -y \
    git zip unzip libssl-dev libzip-dev pkg-config libsodium-dev \
    && docker-php-ext-install zip sodium

# Copia el proyecto
COPY . /var/www/html/

# Instala Composer y dependencias PHP
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

# Directorio público y puerto
WORKDIR /var/www/html/public
EXPOSE 10000

# Inicia el servidor PHP integrado
CMD ["php", "-S", "0.0.0.0:10000", "-t", "/var/www/html/public"]
