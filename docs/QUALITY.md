# Проверки качества кода

Стандарты: [`CODING_STANDARDS.md`](CODING_STANDARDS.md)  
План работ: [`REMEDIATION_PLAN.md`](REMEDIATION_PLAN.md)  
CI: [`CI.md`](CI.md)

## Локально (Docker + хост)

```bash
# PHP — только Docker
docker compose exec -T php composer check:php

# Python
cd ai-service && pip install -e ".[dev]" && ruff check . && pytest

# Frontend — на хосте (не в Docker)
npm run check:frontend
npm run build

# Полный прогон (Linux/macOS)
make ci
# Windows: scripts/ci.ps1
```

## Что проверяется

| Команда | Инструмент |
|---------|------------|
| `composer lint:php` | Laravel Pint (PSR-12) |
| `composer check:line-length` | Лимит 120 символов |
| `composer analyse` | Larastan / PHPStan **level 9** |
| `composer check:phpdoc` | Структура PHPDoc + `@return` |
| `composer check:phpdoc-quality` | Качество текста PHPDoc (RU) |
| `composer test` | PHPUnit |
| `npm run lint:js` | ESLint (JS + TS) |
| `npm run typecheck:js` | TypeScript `tsc --noEmit` |
| `npm run test:unit` | Vitest + coverage (`resources/js/lib/**`) |
| `npm run lint:css` | Stylelint |
| `npm run lint:html` | W3C HTML fixtures |
| `npm run format:blade:check` | blade-formatter |
| `npm run build` | Vite production build |
| `npm run test:e2e` | Playwright (smoke + dirty-state) |

## CI

GitHub Actions: `.github/workflows/code-quality.yml` — jobs `php-quality`, `python-quality`, `php-tests`, `php-tests-pgsql`, `frontend-quality`, `e2e-smoke`.

## Текущий статус

| Проверка | Статус | Примечание |
|----------|--------|------------|
| Pint (`lint:php`) | ✅ PASS | |
| PHPStan level 9 | ✅ **0 ошибок** | |
| PHPUnit | ✅ | feature + unit |
| PHPDoc | ✅ | |
| Python ruff/pytest | ✅ | `ai-service/` |
| Frontend lint + TS + Vitest | ✅ | coverage ≥85% lib |
| **Длина строки ≤120** | ✅ | `check:line-length` в gate |

**Техдолг:** **0 открытых**. **F0–F7:** выполнены.

### Покрытие тестами (ключевые области)

| Область | Файл |
|---------|------|
| RBAC / Policy | `PostPolicyTest` |
| Visibility | `PostVisibilityTest` |
| Moderation (Service) | `PostModerationTest`, `PostServiceTest`, `PostModerationLogTest` |
| Filament smoke | `FilamentAdminTest`, `FilamentModerationTest` |
| Auth / Profile / Avatar | `AuthProfileTest` |
| Auth throttle | `AuthThrottleTest` |
| Email verification / activation | `EmailVerificationTest` |
| Mail settings | `MailSettingsTest`, `MailSettingsServiceTest` |
| Password reset | `PasswordResetTest` |
| API (Sanctum) | `PostApiTest` |
| i18n locale | `LocaleTest` |
| Post factory | `PostFactoryTest` |
| Themes | `ThemeTest` |
| Post CRUD / IDOR | `PostControllerTest` |
| XSS sanitizer | `HtmlSanitizerTest` |
| Frontend lib | `tests/js/*.test.ts` |
| E2E dirty-state | `e2e/tests/form-dirty-state.spec.ts` |

Backlog: [`TECH_DEBT.md`](TECH_DEBT.md) · Prod deploy: [`PRODUCTION_DEPLOY.md`](PRODUCTION_DEPLOY.md)
