printenv | sed 's/^\(.*\)$/export "\1"/g' > "/.schedule-env.sh" | chmod +x "/.schedule-env.sh" &
composer install --no-dev |
php artisan migrate --force |
php artisan report:resetStatus |
mkdir /var/log/supervisor |
chown root /etc/crontabs/* &
chmod +x "/var/www/html/storage/*" &
chmod +x "/var/www/html/storage/framework/cache" &
chmod +x "/var/www/html/storage/framework/cache/data" &
crond &
docker-php-entrypoint php-fpm
