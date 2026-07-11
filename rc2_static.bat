docker exec -w /var/www/html fastcomputercombd-laravel.test-1 composer dump-autoload
docker exec -w /var/www/html fastcomputercombd-laravel.test-1 php artisan optimize:clear
docker exec -w /var/www/html fastcomputercombd-laravel.test-1 php artisan optimize
docker exec -w /var/www/html fastcomputercombd-laravel.test-1 vendor/bin/pint --test
