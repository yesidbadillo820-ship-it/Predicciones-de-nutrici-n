# =============================================================
# NutriPredict Escolar — Imagen de aplicación (PHP + Apache)
# =============================================================
FROM php:8.4-apache

# Extensiones PHP requeridas por la app (+ OPcache para rendimiento)
RUN docker-php-ext-install mysqli opcache \
    && a2enmod rewrite headers

# OPcache: cachea el bytecode PHP en producción
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=1'; \
        echo 'opcache.revalidate_freq=2'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Configuración de Apache: permitir .htaccess (AllowOverride All)
RUN sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# No exponer la versión de PHP
RUN { echo 'expose_php = Off'; } > /usr/local/etc/php/conf.d/security.ini

WORKDIR /var/www/html

# Copiar la aplicación
COPY . /var/www/html/

# Permisos y entrypoint (auto-inicializa la BD y arranca Apache)
RUN chown -R www-data:www-data /var/www/html \
    && chmod +x /var/www/html/docker/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
