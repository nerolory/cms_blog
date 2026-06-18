<?php

namespace App\Support\Database;

use Illuminate\Support\Facades\Schema;

/**
 * Кэшированные проверки схемы БД на время одного запроса.
 */
final class SchemaInspector
{
    private static ?bool $hasSettingsTable = null;

    private static ?bool $hasSiteTemplatesTable = null;

    /**
     * Включена ли проверка существования таблиц через pg_catalog.
     */
    public static function isEnabled(): bool
    {
        if (app()->runningUnitTests()) {
            return true;
        }

        return (bool) config('app.schema_inspector', false);
    }

    /**
     * Проверяет наличие таблицы settings.
     */
    public static function hasSettingsTable(): bool
    {
        if (! self::isEnabled()) {
            return true;
        }

        if (self::$hasSettingsTable !== null) {
            return self::$hasSettingsTable;
        }

        try {
            self::$hasSettingsTable = Schema::hasTable('settings');
        } catch (\Throwable) {
            self::$hasSettingsTable = false;
        }

        return self::$hasSettingsTable;
    }

    /**
     * Проверяет наличие таблицы site_templates.
     */
    public static function hasSiteTemplatesTable(): bool
    {
        if (! self::isEnabled()) {
            return true;
        }

        if (self::$hasSiteTemplatesTable !== null) {
            return self::$hasSiteTemplatesTable;
        }

        try {
            self::$hasSiteTemplatesTable = Schema::hasTable('site_templates');
        } catch (\Throwable) {
            self::$hasSiteTemplatesTable = false;
        }

        return self::$hasSiteTemplatesTable;
    }

    /**
     * Сбрасывает кэш (для тестов).
     */
    public static function forgetCachedChecks(): void
    {
        self::$hasSettingsTable = null;
        self::$hasSiteTemplatesTable = null;
    }
}
