setup:
	docker-compose up -d
	docker-compose exec php composer install
	docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
	docker-compose exec php bin/console doctrine:fixtures:load --no-interaction