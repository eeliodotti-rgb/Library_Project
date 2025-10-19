.PHONY: up down build migrate seed test
up: ; docker compose up -d --build
down: ; docker compose down -v
migrate: ; docker compose exec php bin/console doctrine:migrations:migrate -n
seed: ; docker compose exec php bin/console doctrine:fixtures:load -n
cs: ; docker compose exec php ./vendor/bin/php-cs-fixer fix
stan: ; docker compose exec php ./vendor/bin/phpstan analyse -l 8 src
unit: ; docker compose exec php ./vendor/bin/phpunit --testsuite Unit
integration: ; docker compose exec php ./vendor/bin/phpunit --testsuite Integration
test: ; docker compose exec php ./vendor/bin/phpunit
