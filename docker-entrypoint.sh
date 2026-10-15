#!/bin/sh
set -e

cd /var/www/html

if [ ! -f composer.json ]; then
    echo ">>> Nenhum projeto Symfony encontrado. Criando projeto do zero..."
    composer create-project symfony/skeleton:"7.4.*" . --no-interaction --no-progress

    echo ">>> Instalando pacotes (webapp + Doctrine ORM)..."
    composer require symfony/webapp-pack --no-interaction --no-progress
    composer require symfony/orm-pack --no-interaction --no-progress
    composer require --dev symfony/maker-bundle --no-interaction --no-progress

    echo ">>> Aplicando layout base (Bootstrap 5) e configuração do banco..."
    cp -rf /skeleton/. .

    mkdir -p var/cache var/log
fi

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
    php bin/console doctrine:migrations:migrate --no-interaction || true

    echo ">>> Executando migrations (test)..."
    php bin/console doctrine:migrations:migrate --env=test --no-interaction || true
fi

exec "$@"
