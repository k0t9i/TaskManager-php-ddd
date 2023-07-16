.PHONY: code-style
code-style:
	docker exec task_manager-php ./symfony/vendor/bin/php-cs-fixer fix --config ./symfony/.php-cs-fixer.dist.php --allow-risky=yes -vv --dry-run --show-progress=dots

.PHONY: static-analysis
static-analysis:
	docker exec task_manager-php ./symfony/vendor/bin/psalm --config=symfony/psalm.dist.xml --memory-limit=-1

.PHONY: test
test:
	docker exec task_manager-php php symfony/bin/phpunit src/tests

.PHONY: check-all
check-all: code-style static-analysis test

.PHONY: composer-install
composer-install:
	docker exec task_manager-php composer install -d ./symfony --ignore-platform-reqs

.PHONY: generate-ssl-keys
generate-ssl-keys:
	docker exec task_manager-php php symfony/bin/console lexik:jwt:generate-keypair --overwrite

.PHONY: clean-cache
clean-cache:
	docker exec task_manager-php rm -rf symfony/var/cache/

.PHONY: warmup-cache
warmup-cache:
	docker exec task_manager-php php symfony/bin/console cache:warmup

.PHONY: migrate
migrate:
	docker exec task_manager-php php symfony/bin/console --no-interaction doctrine:migrations:migrate

.PHONY: supervisor-reload
supervisor-reload:
	docker exec task_manager-php supervisorctl reload

.PHONY: projections-reload
projections-reload:
	docker exec task_manager-php php symfony/bin/console projections:reload -vv

.PHONY: setup
setup: composer-install generate-ssl-keys migrate clean-cache warmup-cache supervisor-reload