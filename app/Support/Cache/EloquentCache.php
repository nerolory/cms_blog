<?php

namespace App\Support\Cache;

use App\Support\TypeCast;
use DateInterval;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Кэширует Eloquent-модели и пагинаторы как массивы (Laravel 13:
 * cache.serializable_classes=false).
 */
final class EloquentCache
{
    /**
     * Возвращает пагинатор из кэша или вычисляет через resolver.
     *
     * @template TModel of Model
     *
     * @param  callable(): LengthAwarePaginator<int, TModel>  $resolver
     * @return LengthAwarePaginator<int, TModel>
     */
    public static function rememberPaginator(string $key, DateTimeInterface|DateInterval|int|null $ttl,
        callable $resolver): LengthAwarePaginator
    {
        $payload = self::rememberPayload(
            $key,
            $ttl,
            fn (): array => self::serializePaginator($resolver()),
            fn (mixed $cached): bool => self::isPaginatorPayload($cached),
        );

        /** @var LengthAwarePaginator<int, TModel> $paginator */
        $paginator = self::deserializePaginator($payload);

        return $paginator;
    }

    /**
     * Возвращает модель из кэша или вычисляет через resolver.
     *
     * @param  callable(): Model  $resolver
     * @return Model
     */
    public static function rememberModel(string $key, DateTimeInterface|DateInterval|int|null $ttl,
        callable $resolver): Model
    {
        $payload = self::rememberPayload($key, $ttl, fn (): array => self::serializeModel($resolver()),
            fn (mixed $cached): bool => self::isModelPayload($cached));
        if (! self::isModelPayload($payload)) {
            throw new \RuntimeException('Invalid cached model payload.');
        }

        /** @var array{
         *     class: class-string<Model>,
         *     attributes: array<string, mixed>,
         *     relations?: array<string, mixed>
         * } $payload */
        return self::deserializeModel($payload);
    }

    /**
     * Сериализует пагинатор для кэша.
     *
     * @template TModel of Model
     *
     * @param  LengthAwarePaginator<int, TModel>  $paginator
     * @return array<string, mixed>
     */
    public static function serializePaginator(LengthAwarePaginator $paginator): array
    {
        /** @var list<array{
         *     class: class-string<Model>,
         *     attributes: array<string, mixed>,
         *     relations: array<string, mixed>
         * }> $items */
        $items = array_values($paginator->getCollection()->map(
            fn (Model $model): array => self::serializeModel($model),
        )->all());

        return ['items' => $items, 'total' => $paginator->total(), 'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(), 'path' => TypeCast::string($paginator->path() ?? ''),
            'page_name' => $paginator->getPageName()];
    }

    /**
     * Восстанавливает пагинатор из кэша.
     *
     * @param  array<string, mixed>  $payload
     * @return LengthAwarePaginator<int, Model>
     */
    public static function deserializePaginator(array $payload): LengthAwarePaginator
    {
        $itemsRaw = $payload['items'] ?? [];
        if (! is_array($itemsRaw)) {
            $itemsRaw = [];
        }
        /** @var Collection<int, Model> $items */
        $items = collect($itemsRaw)->map(function (mixed $item): Model {
            if (! is_array($item) || ! isset($item['class'], $item['attributes']) || ! is_string($item['class'])) {
                throw new \RuntimeException('Invalid cached paginator item payload.');
            }

            /** @var array{
             *     class: class-string<Model>,
             *     attributes: array<string, mixed>,
             *     relations?: array<string, mixed>
             * } $item */
            return self::deserializeModel($item);
        });

        return new LengthAwarePaginator($items, TypeCast::int($payload['total'] ?? 0),
            TypeCast::int($payload['per_page'] ?? 15), TypeCast::int($payload['current_page'] ?? 1),
            ['path' => TypeCast::string($payload['path'] ?? ''),
                'pageName' => TypeCast::string($payload['page_name'] ?? 'page')]);
    }

    /**
     * serialize model.
     *
     * @return array<string, mixed>
     */
    public static function serializeModel(Model $model): array
    {
        return ['class' => $model::class, 'attributes' => $model->getAttributes(), 'relations' => collect($model
            ->getRelations())->map(fn (mixed $relation): mixed => self::serializeRelation($relation))->all()];
    }

    /**
     * deserialize model.
     *
     * @param  array<string, mixed>  $payload
     * @return Model
     */
    public static function deserializeModel(array $payload): Model
    {
        /** @var class-string<Model> $class */
        $class = $payload['class'];
        $attributes = $payload['attributes'];
        if (! is_array($attributes)) {
            throw new \RuntimeException('Invalid cached model attributes payload.');
        }

        /** @var array<string, mixed> $attributes */
        $model = (new $class)->newFromBuilder($attributes);
        $relations = $payload['relations'] ?? [];
        if (! is_array($relations)) {
            $relations = [];
        }
        foreach ($relations as $name => $relation) {
            $model->setRelation((string) $name, self::deserializeRelation($relation));
        }

        return $model;
    }

    /**
     * @param  callable(): array<string, mixed>  $resolver
     * @param  callable(mixed): bool  $isValidPayload
     * @return array<string, mixed>
     */
    private static function rememberPayload(string $key, DateTimeInterface|DateInterval|int|null $ttl,
        callable $resolver, callable $isValidPayload): array
    {
        $cached = Cache::get($key);
        if (! $isValidPayload($cached) || ! is_array($cached)) {
            if ($cached !== null) {
                Cache::forget($key);
            }
            $payload = $resolver();
            Cache::put($key, $payload, $ttl);

            /** @var array<string, mixed> $payload */
            return $payload;
        }

        /** @var array<string, mixed> $cached */
        return $cached;
    }

    private static function isPaginatorPayload(mixed $cached): bool
    {
        if (! is_array($cached)) {
            return false;
        }

        return array_key_exists('items', $cached) && array_key_exists('total', $cached) && array_key_exists('per_page',
            $cached) && array_key_exists('current_page', $cached) && is_array($cached['items']);
    }

    private static function isModelPayload(mixed $cached): bool
    {
        return is_array($cached) && isset($cached['class'],
            $cached['attributes']) && is_string($cached['class']) && is_array($cached['attributes']);
    }

    private static function serializeRelation(mixed $relation): mixed
    {
        if ($relation instanceof Model) {
            return self::serializeModel($relation);
        }
        if ($relation instanceof Collection) {
            return $relation
                ->map(fn (mixed $item): mixed => $item instanceof Model ? self::serializeModel($item) : $item)->all();
        }

        return $relation;
    }

    private static function deserializeRelation(mixed $relation): mixed
    {
        if (is_array($relation) && isset($relation['class'], $relation['attributes'])) {
            return self::deserializeModel($relation);
        }
        if (is_array($relation) && array_is_list($relation)) {
            return collect($relation)
                ->map(fn (mixed $item): mixed => is_array($item) ? self::deserializeRelation($item) : $item);
        }

        return $relation;
    }
}
