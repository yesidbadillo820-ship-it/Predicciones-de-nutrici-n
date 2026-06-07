# =============================================================
# NutriPredict Escolar — Imagen de aplicación (PHP + Apache)
# =============================================================
FROM php:8.4-apache

# Extensiones PHP requeridas por la app
RUN docker-php-ext-install mysqli \
    && a2enmod rewrite headers

# Configuración de Apache: permitir .htaccess (AllowOverride All)
RUN sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# No exponer la versión de PHP
RUN { echo 'expose_php = Off'; } > /usr/local/etc/php/conf.d/security.ini

WORKDIR /var/www/html

# Copiar la aplicación
COPY . /var/www/html/

# Permisos para el usuario de Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
