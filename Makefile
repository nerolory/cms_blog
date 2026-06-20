.PHONY: ci ci-php ci-python ci-frontend ci-audit install install-tools verify-ui

ci: ci-frontend ci-php ci-python ci-audit

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

# После правок CSS/Blade: сборка на хосте + smoke-проверка в контейнере.
verify-ui:
	npm run build
	docker compose exec -T php php scripts/verify-ui-changes.php
	docker compose exec -T php php scripts/verify-engagement-markup.php
	docker compose exec -T php php artisan test --filter="FilamentAdminTest::test_admin_topbar|PostControllerTest::test_show_warm"

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
