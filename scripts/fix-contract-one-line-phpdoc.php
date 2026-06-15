#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Расширяет one-line @return в контрактах до полного docblock с русским описанием.
 */
$descriptions = [
    'allOrdered' => 'Возвращает записи в порядке сортировки',
    'search' => 'Выполняет поиск постов по фильтрам',
    'getPublishedPosts' => 'Возвращает опубликованные посты автора',
    'getCompletedForPost' => 'Возвращает завершённые AI-результаты для поста',
    'countsForPost' => 'Возвращает счётчики реакций для поста',
    'syncForPost' => 'Синхронизирует теги поста',
    'listForPost' => 'Возвращает версии поста',
    'listActive' => 'Возвращает активные пакеты токенов',
    'latest' => 'Возвращает последние отчёты о здоровье сайта',
    'historyFor' => 'Возвращает историю версий поста',
    'getVisibleTreeForPost' => 'Возвращает дерево комментариев поста',
    'getVisibleRootCommentsForPost' => 'Возвращает корневые комментарии поста',
    'getThemeSlugs' => 'Возвращает slug тем шаблона',
    'getAll' => 'Возвращает все шаблоны сайта',
    'flushPendingCounts' => 'Сбрасывает отложенные счётчики просмотров',
    'availableDrivers' => 'Возвращает доступные драйверы поиска',
    'availablePresets' => 'Возвращает доступные пресеты почты',
    'availableThemeSlugs' => 'Возвращает slug доступных тем',
    'forPost' => 'Возвращает записи журнала модерации поста',
    'record' => 'Сохраняет запись журнала модерации',
    'getDueForScheduledPublish' => 'Возвращает посты для отложенной публикации',
    'getPublishedGuestPostsForSitemap' => 'Возвращает гостевые посты для sitemap',
];

$roots = [
    realpath(__DIR__.'/../app/Repositories/Contracts'),
    realpath(__DIR__.'/../app/Repositories/Search/Contracts'),
    realpath(__DIR__.'/../app/Services/Contracts'),
];

$fixed = 0;

foreach ($roots as $root) {
    if ($root === false || ! is_dir($root)) {
        continue;
    }

    foreach (glob($root.'/*.php') ?: [] as $path) {
        $source = file_get_contents($path);

        if ($source === false) {
            continue;
        }

        $original = $source;

        $source = preg_replace_callback(
            '/^\s*\/\*\* @return ([^\n]+) \*\/\s*\n(\s*public function (\w+)\()/m',
            static function (array $m) use ($descriptions): string {
                $method = $m[3];
                $description = $descriptions[$method] ?? 'Метод '.$method;

                return "    /**\n     * {$description}.\n     *\n     * @return {$m[1]}\n     */\n{$m[2]}";
            },
            $source,
        ) ?? $source;

        if ($source !== $original) {
            file_put_contents($path, $source);
            $fixed++;
        }
    }
}

echo "Expanded contract PHPDoc in {$fixed} file(s).\n";
