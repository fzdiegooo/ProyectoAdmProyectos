FROM php:8.2-apache

# Instala la extensión PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# (Opcional) habilitar mod_rewrite si lo necesitas
RUN a2enmod rewrite

# Copia los archivos del proyecto dentro del contenedor
COPY . /var/www/html
