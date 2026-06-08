#!/bin/sh
# Ajusta el puerto (Render/Railway asignan $PORT), inicializa la BD y arranca Apache.
PORT="${PORT:-80}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php /var/www/html/docker/init-db.php || true
exec apache2-foreground
