# Imagen base de PHP 8.2 con Apache
FROM php:8.2-apache

# Instala dependencias necesarias para Firebase PHP SDK
RUN apt-get update && apt-get install -y git zip unzip libssl-dev libzip-dev \
    && docker-php-ext-install zip sodium

# Copia todo el código de tu proyecto
COPY . /var/www/html/

# Instala Composer y las dependencias del proyecto
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev

# Configura el directorio público
WORKDIR /var/www/html/public

# Render usará este puerto automáticamente
EXPOSE 10000

# Comando de inicio del servidor
CMD ["php", "-S", "0.0.0.0:10000", "-t", "/var/www/html/public"]
