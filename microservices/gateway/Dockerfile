FROM php:8.4-apache
RUN a2enmod rewrite
COPY . /var/www/html/
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
 && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
 && printf 'RewriteEngine On\nRewriteCond %%{REQUEST_FILENAME} !-f\nRewriteRule ^ index.php [L]\n' > /var/www/html/public/.htaccess
EXPOSE 80
