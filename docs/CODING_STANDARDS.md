# Стандарты кода

Краткая версия [`WORK_PLAN.md`](WORK_PLAN.md). При конфликте с Laravel conventions — **Laravel в приоритете**.

## Правило «стоп»

Если задача противоречит этому документу или Laravel — **не реализовывать**. Сообщить: запрос → конфликт → альтернативы.

## Архитектура

```
Controller / Form Request  →  Service  →  Repository  →  Model / DB
```

- **БД только в репозиториях.** Сервис, контроллер, middleware, job — **без** `Model::query()`, `DB::`, `->save()` на моделях.
- Контроллер inject'ит **только сервисы** (через контракты).
- **Маршруты и binding:** см. [`ROUTING.md`](ROUTING.md). **Вариант A (утверждён):** в action-методах контроллера **нет Eloquent-моделей** в параметрах — только примитивы (`string $postSlug`, `int $postId`, …) → сервис → репозиторий → один запрос к БД.

## HTTP-ответы

| Контекст | Тип ответа |
|----------|------------|
| Web (Blade) | `View`, `RedirectResponse` |
| API | `JsonResource`, `ResourceCollection` |

## Параметры методов (`app/`)

**Разрешено:** примитивы, DTO, `Collection`, `Paginator`, Model (оговорённо), объекты Laravel.

**Запрещено:** `array` в параметрах публичных методов `app/` (кроме сигнатур Laravel: `rules(): array` и т.п.).

### Осознанные исключения `array` (утверждено в F3)

| Контекст | Допустимо | Примечание |
|----------|-----------|------------|
| DTO readonly-свойства | `list<int>`, `array<string, mixed>` | Данные после `FormRequest::validated()` |
| Laravel / Filament | `rules(): array`, `mutateFormDataBeforeFill(array $data)` | Сигнатуры фреймворка |
| Support / cache | `@param array<string, mixed>` с PHPDoc | Только с явным `@param`/`@return` |
| Приватные хелперы сервиса | `list<string> $paths` | Не экспортировать в публичный API |

Новые публичные методы с `array` — только через **правило «стоп»** и правку этого раздела.

## DTO

- `readonly`, strict types в конструкторе.
- HTTP-ввод — Form Request + `ValidationException`.
- Инварианты домена — `App\Exceptions\...` (напр. `InvalidPostDataException`).
- `toDto(): PostData` — конкретный тип, не `object`.

## PHPDoc

Обязателен для всего кода в `app/`, `tests/`, `database/` (кроме `database/migrations/`).

**Язык:** только **русский** в описаниях. Запрещены шаблоны автогенератора: «Значение …», «Результат выполнения», однословные английские описания (`execute.`, `request.`).

**Смысл:** описание отвечает на вопрос «что делает и зачем», а не дублирует имя метода. `@param` / `@return` — только если смысл или тип неочевидны из сигнатуры (см. ниже).

Формат — стандартный PHPDoc; native type hints в сигнатуре не дублировать в тегах, если тип однозначен.

### Класс

- У каждого класса, интерфейса, трейта и enum — **class-level** docblock с кратким описанием назначения.
- Для **constructor-promoted** свойств (`public function __construct(protected Foo $foo)`) — перечислить их в docblock класса через `@property-read` или `@property` (тип + имя).

Пример:

```php
/**
 * Оркестрация сценариев работы с постами.
 *
 * @property-read PostRepositoryContract $postRepository
 * @property-read PostMutationPipeline $pipeline
 */
class PostService
{
    public function __construct(
        protected PostMutationPipeline $pipeline,
        protected PostRepositoryContract $postRepository,
    ) {}
}
```

### Методы (public / protected)

- Docblock с **русским описанием** того, что делает метод.
- `@param` — для каждого параметра, если тип или смысл неочевидны из сигнатуры (сложные DTO, `mixed`, коллекции с дженериками).
- `@return` — **обязателен**, если native return type не `void`. Для дженериков и пагинаторов указывать полный тип: `@return LengthAwarePaginator<int, Post>`.
- `{@inheritdoc}` допустим для реализаций контрактов (считается описанием; `@return` может наследоваться из интерфейса).

### Свойства (public / protected)

- Обычные свойства — `@var` в docblock свойства **или** `@property` / `@property-read` в docblock класса.
- Constructor-promoted — только в docblock класса (см. выше).
- Private-свойства и магические методы (`__get`, `__call`, …) — вне scope проверки.

### Исключения

- `database/migrations/`, `vendor/`.
- Тонкие Filament-страницы без собственных public/protected методов (только `$resource`).

### Проверки

```bash
# Наличие docblock (структура)
php scripts/check-phpdoc.php

# Качество текста (русский, без шаблонов) — постепенное внедрение по слоям
php scripts/check-phpdoc-quality.php

# Статический анализ
composer analyse
```

## Форматирование (PSR-12 + Pint)

- **Максимальная длина строки: 120 символов** (жёсткий лимит; в IDE — guide column).
- При превышении — перенос по [PSR-12](https://www.php-fig.org/psr/psr-12/): аргументы вызова — по одному на строку; цепочки `->` — с переносом; массивы — trailing comma + вертикальное выравнивание.
- **Конструктор** — promoted properties только в многострочной форме, если больше одного параметра.
- **Пустая строка** между логическими блоками внутри метода (валидация → загрузка → действие → return).

```bash
./vendor/bin/pint   # применить
composer lint:php   # проверить
```

## Размер и структура методов

- Целевой размер публичного метода — **до ~25 строк** тела; больше — повод для приватных подметодов.
- Один метод — **одна ответственность**. Длинный сценарий разбивать: `assert*`, `resolve*`, `finalize*` (см. `AiAnalysisOrderService`).
- Цепочки `if ($status === A) … elseif ($status === B)` по enum — **`match`** или отдельные методы по статусу.
- «Божественные методы» без декомпозиции — техдолг; шаговые комментарии внутри метода **не заменяют** выделение подметодов.

## Frontend

- CSS: **Bootstrap 5** (Tailwind убираем).
- Blade: blade-formatter.

### HTML и SEO (Blade)

Соответствие [W3C](https://www.w3.org/TR/html/) и базовым требованиям SEO для публичных страниц.

**Layout shell** (`themes/partials/app-shell.blade.php` и другие полные документы с `<!DOCTYPE html>`):

- `html[lang]` — локаль приложения (`str_replace('_', '-', app()->getLocale())`).
- `<meta charset="utf-8">`, `<meta name="viewport" …>`.
- `<main>` — единственный основной landmark для контента страницы.
- `<title>` — непустой; страницы с `@section('title', …)` передают осмысленное значение.
- `theme-color` — при необходимости (основной shell, standalone-страницы вроде maintenance).

**Семантика:**

- Навигация — `<nav>` (см. `components/layout/navbar.blade.php`).
- Контент поста — `<article>`; списки карточек — `<article>` на элемент.
- Один `<main>` на документ; не дублировать в `@section('content')`.

**Изображения:**

- У каждого `<img>` — непустой `alt` **или** `role="presentation"` для чисто декоративных.
- Featured image поста — `alt` с заголовком поста (`$post->title`).
- Превью в формах — `alt` из подписи поля (`$label`).

**SEO-компоненты** (`components/seo/*`):

- Страницы с `<x-seo.meta>` — только внутри `@push('meta')`.
- `meta.blade.php` — `canonical`, `og:title`, `og:description`, `og:url`, `og:locale` (и при необходимости `og:image`).

**Комментарии в шаблонах:**

- Допустимы краткие **маркеры секций** в HTML: `<!-- Header -->`.
- Запрещены «логические подсказки»: TODO/FIXME, описание ветвлений Blade/PHP, `@if`/`@foreach` в комментариях.
- Предпочтительно выразительная разметка и имена компонентов вместо комментариев.

**Проверка:**

```bash
npm run lint:html
```

## Проверки

```bash
docker compose exec php composer check:php
npm run check:frontend && npm run build
.\scripts\ci.ps1   # Windows — полный прогон
make ci            # Linux/macOS
```

## Дальнейший план

Фаза 4: [`PHASE_4_PLAN.md`](PHASE_4_PLAN.md).  
Устранение TD: [`REMEDIATION_PLAN.md`](REMEDIATION_PLAN.md).  
Маршруты: [`ROUTING.md`](ROUTING.md).
