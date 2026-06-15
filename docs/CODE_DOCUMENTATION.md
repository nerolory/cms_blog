# Документирование кода

**Обновлено:** 2026-06-16  
**Статус:** обязательно; **массовая автогенерация отменена** (см. TD-QUAL-04)

Handoff: [`AGENT_HANDOFF.md`](AGENT_HANDOFF.md)

---

## PHP (PHPDoc)

Полные правила: [`CODING_STANDARDS.md`](CODING_STANDARDS.md#phpdoc).

### Обязательно

| Элемент | Требование |
|---------|------------|
| Язык | **Только русский** в описаниях |
| Класс | Назначение класса («что делает в системе») |
| Метод | Что и зачем; не `execute.` / не `request.` |
| Запрещено | «Значение user», «Результат выполнения», EN+RU mix |
| `@param` / `@return` | Только если тип/смысл неочевидны из сигнатуры |
| Promoted props | `@property-read` в class doc |

### Эталон (после ручного рефакторинга)

`app/Services/AiAnalysisOrderService.php` — черновик для F4; **не** считать весь проект эталоном.

### Проверки

```bash
php scripts/check-phpdoc.php          # структура docblock
php scripts/check-phpdoc-quality.php  # язык и шаблоны (включить в CI на F4)
composer analyse                      # PHPStan
composer lint:php                     # Pint, line_length 120
```

### ⚠️ Не использовать

`scripts/fix-phpdoc.php` — генерирует низкокачественные шаблоны (TD-QUAL-04). Только ручной PHPDoc.

---

## Форматирование PHP (связано с документацией)

- **120 символов** на строку (`pint.json`)
- PSR-12: аргументы и цепочки `->` с переносами
- Метод ~25 строк тела → подметоды
- См. [`CODING_STANDARDS.md`](CODING_STANDARDS.md#форматирование-psr-12--pint)

---

## Python (PEP 257 + Google)

Каталог: `ai-service/src/`, `ai-service/tests/`.

- Описание **по делу**, не формальная заглушка
- Args / Returns / Raises
- Ruff: `[tool.ruff.lint.pydocstyle] convention = "google"`
- Фаза **F5**, TD-DOC-08

---

## JavaScript (JSDoc)

`resources/js/`, `scripts/*.mjs`.

- `@module`, `@param`, `@returns` на экспортах
- Фаза **F5**, TD-DOC-09

---

## HTML / Blade

- W3C, SEO, `npm run lint:html`
- Комментарии — **только секции** (`<!-- Navbar -->`)
- Фаза **F5**, TD-DOC-10

---

## Маршруты (комментарии в PHP)

`routes/web.php`, `routes/api.php` — секционные `// --- Секция N: … ---`.  
Правила binding: [`ROUTING.md`](ROUTING.md).

---

## Закрытие

Документирование считается выполненным только после **F4–F5** и **F7**.  
[`PROJECT_CLOSURE.md`](PROJECT_CLOSURE.md) — отозван до финала.
