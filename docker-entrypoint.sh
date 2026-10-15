#!/bin/sh
set -e

cd /var/www/html

echo ">>> composer install"
composer install --no-interaction --no-progress --optimize-autoloader

echo ">>> Aguardando banco de dados ficar disponível..."
until php bin/console doctrine:database:create --if-not-exists --no-interaction > /dev/null 2>&1; do
    echo "    banco ainda não respondeu, tentando novamente em 2s..."
    sleep 2
done
echo ">>> Banco pronto."

echo ">>> Preparando banco de testes (tjjud_test)..."
php -r '
$pdo = new PDO("mysql:host=database;port=3306", "root", "root");
$pdo->exec("CREATE DATABASE IF NOT EXISTS tjjud_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("GRANT ALL PRIVILEGES ON tjjud_test.* TO \"symfony\"@\"%\"");
$pdo->exec("FLUSH PRIVILEGES");
' || true

if [ -d migrations ] && [ -n "$(find migrations -maxdepth 1 -name 'Version*.php' -print -quit 2>/dev/null)" ]; then
    echo ">>> Executando migrations (dev)..."
    php bin/console doctrine:migrations:migrate --no-interaction

    echo ">>> Executando migrations (test)..."
    php bin/console doctrine:migrations:migrate --env=test --no-interaction
fi

exec "$@"
