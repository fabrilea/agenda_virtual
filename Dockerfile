# Imagen base oficial de PHP 8.2 con Apache
FROM php:8.2-apache

# Instala todas las dependencias necesarias, incluyendo libsodium-dev
RUN apt-get update && apt-get install -y \
    git zip unzip libssl-dev libzip-dev pkg-config libsodium-dev \
    && docker-php-ext-install zip sodium

# Copia el código de la aplicación al contenedor
COPY . /var/www/html/

# Instala Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Instala las dependencias del proyecto (Firebase, etc.)
RUN composer install --no-dev --optimize-autoloader

# Define el directorio de trabajo
WORKDIR /var/www/html/public

# Expone el puerto usado por Render
EXPOSE 10000

# Comando que inicia el servidor PHP integrado
CMD ["php", "-S", "0.0.0.0:10000", "-t", "/var/www/html/public"]
