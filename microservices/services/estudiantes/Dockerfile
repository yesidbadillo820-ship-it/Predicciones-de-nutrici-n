FROM php:8.4-apache
RUN docker-php-ext-install mysqli && a2enmod rewrite
COPY . /var/www/html/
# DocumentRoot al directorio public/ y front-controller (todo a index.php)
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
 && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
 && printf 'RewriteEngine On\nRewriteRule .* - [E=HTTP_AUTHORIZATION:%%{HTTP:Authorization}]\nRewriteCond %%{REQUEST_FILENAME} !-f\nRewriteRule ^ index.php [L]\n' > /var/www/html/public/.htaccess
EXPOSE 80
