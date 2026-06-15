.PHONY: ci ci-php ci-python ci-frontend ci-audit install install-tools

ci: ci-php ci-python ci-frontend ci-audit

ci-php:
	docker compose exec -T php composer check:php

ci-python:
	cd ai-service && pip install -e ".[dev]" && ruff check . && pytest

ci-audit:
	docker compose exec -T php composer audit
	npm audit --audit-level=moderate

# Frontend на хосте: node_modules в Docker volume несовместимы между ОС.
ci-frontend:
	npm run check:frontend
	npm run build

install-tools:
	docker compose exec -T php composer install
	npm install

ifeq ($(OS),Windows_NT)
install:
	powershell -ExecutionPolicy Bypass -File scripts/install.ps1
else
install:
	bash scripts/install.sh
endif

lint-php:
	docker compose exec -T php composer lint:php

analyse:
	docker compose exec -T php composer analyse

test:
	docker compose exec -T php composer test

fix-php:
	docker compose exec -T php composer fix:php
