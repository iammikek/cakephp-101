.PHONY: test serve migrate docker-up docker-down

test:
	./vendor/bin/phpunit

serve:
	bin/cake server -H $${APP_HOST:-127.0.0.1} -p $${PORT:-8017}

migrate:
	bin/cake migrations migrate

docker-up:
	docker compose up --build

docker-down:
	docker compose down
