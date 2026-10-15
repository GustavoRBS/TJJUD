up:
	docker compose up -d --build

down:
	docker compose down

restart:
	docker compose restart

sh:
	docker compose exec php sh

logs:
	docker compose logs -f

cache-clear:
	docker compose exec php php bin/console cache:clear

migration:
	docker compose exec php php bin/console make:migration

migrate:
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

mysql:
	docker compose exec database mysql -usymfony -psymfony tjjud
