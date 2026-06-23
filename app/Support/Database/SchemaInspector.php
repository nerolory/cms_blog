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

     *
     * @return bool
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

     *
     * @return bool
     */
    public static function hasSettingsTable(): bool
    {
        if (app()->runningUnitTests()) {
            return self::probeSettingsTable();
        }

        if (self::$hasSettingsTable !== null) {
            return self::$hasSettingsTable;
        }

        self::$hasSettingsTable = self::probeSettingsTable();

        return self::$hasSettingsTable;
    }

    /**
     * Проверяет наличие таблицы site_templates.

     *
     * @return bool
     */
    public static function hasSiteTemplatesTable(): bool
    {
        if (app()->runningUnitTests()) {
            return self::probeSiteTemplatesTable();
        }

        if (self::$hasSiteTemplatesTable !== null) {
            return self::$hasSiteTemplatesTable;
        }

        self::$hasSiteTemplatesTable = self::probeSiteTemplatesTable();

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

    private static function probeSettingsTable(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }

    private static function probeSiteTemplatesTable(): bool
    {
        try {
            return Schema::hasTable('site_templates');
        } catch (\Throwable) {
            return false;
        }
    }
}
