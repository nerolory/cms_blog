# Схема базы данных

**Последнее обновление:** 2026-06-15 (фаза F7)  
**СУБД production:** PostgreSQL 16 (Docker)  
**СУБД tests:** SQLite in-memory

---

## Обзор

| Таблица | Назначение |
|---------|------------|
| `users` | Пользователи, профиль, локаль, статус аккаунта |
| `posts` | Статьи блога, модерация, видимость, поиск |
| `post_versions` | Снимки версий поста (FIFO, max 2) |
| `post_moderation_logs` | Журнал действий модерации |
| `settings` | Key-value настройки (почта и др.) |
| `roles`, `permissions`, pivots | RBAC (Spatie Permission) |
| `personal_access_tokens` | Sanctum API tokens |
| `cache`, `jobs`, `sessions` | Laravel infrastructure |

---

## `posts`

Основная таблица контента.

| Колонка | Тип | Описание |
|---------|-----|----------|
| `id` | bigint PK | |
| `title` | string | Заголовок |
| `slug` | string UNIQUE | URL slug |
| `excerpt` | text nullable | Краткое описание |
| `body` | longText | HTML-содержимое |
| `featured_image_path` | string nullable | Обложка |
| `background_image_path` | string nullable | Фон статьи |
| `theme_primary_color` | string(7) nullable | HEX primary |
| `theme_accent_color` | string(7) nullable | HEX accent |
| `content_opacity` | tinyint | 0–100 |
| `editor_mode` | string(16) | `simple` \| `advanced` |
| `status` | string | `draft` \| `pending_moderation` \| `published` \| `rejected` |
| `visibility` | string | `guest` \| `authenticated` \| `admin` \| `permission` |
| `required_permission_id` | bigint FK nullable | → `permissions.id` (для `permission`) |
| `rejection_reason` | text nullable | Причина отклонения |
| `is_published` | boolean | **Deprecated.** Синхронизируется из `status` при save |
| `published_at` | datetime nullable | Дата публикации |
| `user_id` | bigint FK nullable | → `users.id` (автор) |
| `deleted_at` | timestamp nullable | Soft delete |
| `created_at`, `updated_at` | timestamps | |

### Индексы (F7)

| Индекс | Колонки | Назначение |
|--------|---------|------------|
| `posts_status_published_at_index` | `(status, published_at)` | Листинг опубликованных постов |
| `posts_visibility_index` | `visibility` | Фильтрация по видимости |
| `posts_search_vector_gin_index` | `search_vector` GIN | Полнотекстовый поиск (PostgreSQL only) |

### Поиск (PostgreSQL only)

| Колонка | Тип | Описание |
|---------|-----|----------|
| `search_vector` | tsvector GENERATED | `to_tsvector('simple', title || ' ' || excerpt)` |

> Колонка и GIN-индекс создаются только при `DB_CONNECTION=pgsql`.  
> UI поиска и ранжирование — фаза F10.

### Enum-значения

**`status`** (`App\Enums\PostStatus`):

- `draft` — черновик
- `pending_moderation` — на модерации
- `published` — опубликован
- `rejected` — отклонён

**`visibility`** (`App\Enums\PostVisibility`):

- `guest` — все посетители
- `authenticated` — только авторизованные
- `admin` — admin/owner
- `permission` — пользователи с конкретным permission

---

## `post_versions`

| Колонка | Тип | Описание |
|---------|-----|----------|
| `id` | bigint PK | |
| `post_id` | bigint FK | → `posts.id` CASCADE |
| `created_by` | bigint FK nullable | → `users.id` |
| `version_number` | tinyint | 1 или 2 (FIFO) |
| `snapshot` | json | Снимок полей поста |
| `created_at`, `updated_at` | timestamps | |

**Индексы:** UNIQUE `(post_id, version_number)`, `(post_id, created_at)`.

---

## `post_moderation_logs`

| Колонка | Тип | Описание |
|---------|-----|----------|
| `id` | bigint PK | |
| `post_id` | bigint FK | → `posts.id` CASCADE |
| `actor_id` | bigint FK nullable | → `users.id` |
| `action` | string | Тип действия |
| `reason` | text nullable | |
| `metadata` | json nullable | |
| `created_at`, `updated_at` | timestamps | |

**Индекс:** `(post_id, created_at)`.

---

## `users`

| Колонка | Тип | Описание |
|---------|-----|----------|
| `id` | bigint PK | |
| `name` | string | |
| `email` | string UNIQUE | |
| `email_verified_at` | timestamp nullable | |
| `password` | string | |
| `remember_token` | string nullable | |
| `account_status` | string | `pending` \| `active` \| `suspended` |
| `locale` | string(5) | `en` \| `ru` |
| `created_at`, `updated_at` | timestamps | |

---

## RBAC (Spatie Permission)

Стандартные таблицы: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.

---

## Production vs Tests

| Аспект | Production (`.env.production.example`) | Tests |
|--------|----------------------------------------|-------|
| DB | PostgreSQL | SQLite `:memory:` |
| Cache / Session / Queue | Redis | array / database |
| `search_vector` | Есть | Нет (миграция пропускает) |
| `is_published` | Deprecated, sync on save | То же |

---

## Миграции

Порядок применения — по timestamp в `database/migrations/`.  
Ключевая миграция F7: `2026_06_15_110000_optimize_posts_table_indexes_and_search.php`.
