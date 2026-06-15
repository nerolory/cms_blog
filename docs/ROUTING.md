# Маршрутизация (web + API)

**Статус:** утверждено  
**Связано:** [`CODING_STANDARDS.md`](CODING_STANDARDS.md) · [`REMEDIATION_PLAN.md`](REMEDIATION_PLAN.md) F3.3  
**Решение:** **вариант A** — без implicit model binding в контроллерах; загрузка только через сервис → репозиторий.

---

## Принцип

```
HTTP Request
  → Route (примитив в URL: slug, id, token)
  → Controller (только сервисы, без Model в сигнатуре)
  → Service (бизнес-логика, без SQL)
  → Repository (единственный слой с БД)
  → Model
```

**Запрещено в контроллере:** `Post $post`, `User $user`, `TokenPackage $package` и любой Eloquent в параметрах action.

**Разрешено в сигнатуре:** `string $postSlug`, `int $postId`, `int $commentId`, `string $previewToken`, Form Request, `Request`.

---

## Web (`routes/web.php`)

### Структура файла (секции)

| # | Секция | Middleware | Назначение |
|---|--------|------------|------------|
| 1 | Система | — | home, health, locale |
| 2 | Гость (auth) | `guest` | login, register, password reset |
| 3 | Аутентифицированный | `auth` | logout, email verification, account pending |
| 4 | Профиль и токены | `auth`, `account.active` | profile, tokens |
| 5 | Публичные фиды и SEO | — | RSS, Atom, search, authors, sitemap, robots |
| 6 | Посты — коллекция | см. ниже | index, create, store |
| 7 | Посты — preview по токену | signed/auth | черновой просмотр |
| 8 | Посты — member + nested | `auth`, `account.active` | edit, update, destroy, comments, reactions, AI |
| 9 | Посты — публичный show | `conditional.get` | show (гость и auth) |

**Порядок важен:** статические сегменты (`posts/create`) **выше** параметрических (`posts/{postSlug}`).

### Именование параметров (целевое, F3)

| Параметр в route | Тип в контроллере | Загрузка |
|------------------|-------------------|----------|
| `{postSlug}` | `string $postSlug` | `PostService::getVisiblePostBySlug()` |
| `{comment}` → `{commentId}` | `int $commentId` | `CommentService` + проверка принадлежности посту |
| `{user}` → `{authorId}` | `int $authorId` | `AuthorService::getAuthorProfile()` |
| `{package}` → `{packageId}` | `int $packageId` | `TokenWalletService` (не репозиторий в контроллере) |
| `{token}` | `string $previewToken` | `PostPreviewService` |

URL поста на web — **всегда slug** (`/posts/my-article`), не числовой id.

### Member vs nested (не путать)

| Тип | Примеры | Смысл |
|-----|---------|-------|
| **Member** (тот же ресурс) | `GET /posts/{slug}`, `…/edit`, PATCH, DELETE | CRUD одного поста |
| **Nested** (дочерний ресурс) | `…/comments`, `…/reactions`, `…/ai-analysis` | Действие над дочерней сущностью поста |

Оба типа — **вариант A**: slug в path, один запрос через сервис.

---

## API (`routes/api.php`)

| Параметр | Тип в контроллере | Загрузка |
|----------|-------------------|----------|
| `{post:id}` → `{postId}` | `int $postId` | `PostService::getPostForApi($id, $user)` |

API использует **числовой id** (стабильность для клиентов). Binding `{post:id}` убирается в пользу `int $postId` + сервис.

Префикс `v1`, middleware: `conditional.get` (read), `auth:sanctum` (write).

---

## Матрица миграции (F3.3)

| Контроллер | Метод | Сейчас | Цель A |
|------------|-------|--------|--------|
| `PostController` | show, edit, update, destroy | `Post $post` | `string $postSlug` |
| `CommentController` | store, destroy | `Post $post`, `PostComment $comment` | `string $postSlug`, `int $commentId` |
| `ReactionController` | store | `Post $post` | `string $postSlug` |
| `PostPreviewController` | storeForPost | `Post $post` | `string $postSlug` |
| `AiAnalysisOrderController` | store | `Post $post` | `string $postSlug` |
| `AuthorController` | show | `User $user` | `int $authorId` |
| `TokenWalletController` | purchase | `TokenPackage $package` | `int $packageId` + сервис |
| `PostApiController` | show, update, destroy | `Post $post` | `int $postId` |

**DoD:** один SELECT на загрузку поста; нет `getVisiblePost($boundModel)` после binding.

---

## Сервисные методы (добавить в F3)

| Метод | Назначение |
|-------|------------|
| `PostService::getVisiblePostBySlug(string $slug, ?User $viewer): Post` | web show/edit/nested |
| `PostService::getPostForApi(int $id, ?User $viewer): Post` | API |
| `AuthorService::getAuthorForProfile(int $authorId): User` | authors.show |
| `TokenWalletService::purchasePackageById(User $user, int $packageId): void` | tokens purchase |

---

## Комментарии в файлах маршрутов

В `routes/*.php` допустимы **только секционные** блоки:

```php
// --- Секция: Посты (member, auth) ---
```

Без описания логики middleware внутри action. Детали — в этом документе и PHPDoc контроллера после рефакторинга.

---

## Что не меняем

- Flat REST для постов (`/posts`, `/posts/{slug}`) — оставляем.
- Nested URL для comments/reactions — оставляем.
- `Route::resource()` не обязателен; ручные маршруты с секциями — ок.
