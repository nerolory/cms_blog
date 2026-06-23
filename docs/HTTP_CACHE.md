# HTTP-кеширование (F6)

## Laravel middleware

Публичные маршруты `posts.index`, `posts.show` и API `GET /api/v1/posts*` используют middleware `conditional.get`:

- `Cache-Control: private, max-age=…, must-revalidate` (см. `config/seo.php`, `SEO_CACHE_MAX_AGE`)
- `Vary: Cookie` — отдельный кэш для гостя и каждой сессии (middleware `ApplyAudienceCacheHeaders` на всём публичном HTML)
- `ETag` и `Last-Modified` — поддержка `304 Not Modified`
- `X-Robots-Tag` — из `SeoService` (согласовано с HTML `<meta name="robots">`, C-3)

Preview (`posts.preview.*`) **исключён**: middleware `preview.no-cache` (`no-store`, `X-Robots-Tag: noindex, nofollow`).

## Nginx (production)

В `nginx.conf` рекомендуется включить gzip для текстовых типов и immutable cache для статики:

```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript application/xml text/xml;
gzip_min_length 256;

location ~* \.(css|js|woff2?|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
    access_log off;
}
```

Статика `/storage` уже отдаётся с `immutable` (см. текущий `nginx.conf`).

## Проверка

```bash
curl -I http://localhost/posts/{slug}
curl -I -H "If-None-Match: \"…\"" http://localhost/posts/{slug}
```

Ожидается `200` с заголовками кеша или `304` при совпадении ETag / If-Modified-Since.
