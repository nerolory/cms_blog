<?php

namespace App\Enums;

/**
 * Перечисление search driver.
 */
enum SearchDriver: string
{
    case Database = 'database';
    case Elasticsearch = 'elasticsearch';
    case Sphinx = 'sphinx';
    case Solr = 'solr';
    case Ai = 'ai';

    /**
     * label.

     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Database => __('admin.search.drivers.database'),
            self::Elasticsearch => __('admin.search.drivers.elasticsearch'),
            self::Sphinx => __('admin.search.drivers.sphinx'),
            self::Solr => __('admin.search.drivers.solr'),
            self::Ai => __('admin.search.drivers.ai'),
        };
    }

    /**
     * Проверяет recommended.

     *
     * @return bool
     */
    public function isRecommended(): bool
    {
        return $this !== self::Database;
    }

    /**
     * try from string.

     *
     * @return self
     */
    public static function tryFromString(?string $value): self
    {
        if ($value === null || $value === '') {
            return self::Database;
        }

        return self::tryFrom($value) ?? self::Database;
    }
}
