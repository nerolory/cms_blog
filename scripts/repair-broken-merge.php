#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Чинит синтаксис после неудачного merge-orphan-phpdoc.php.
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
    'history' => 'Возвращает историю отчётов',
    'getVisibleTreeForPost' => 'Возвращает дерево комментариев поста',
    'getVisibleRootCommentsForPost' => 'Возвращает корневые комментарии поста',
    'getThemeSlugs' => 'Возвращает slug тем шаблонов',
    'getAll' => 'Возвращает все шаблоны сайта',
    'flushPendingCounts' => 'Сбрасывает отложенные счётчики просмотров',
    'availableDrivers' => 'Возвращает доступные драйверы поиска',
    'availableThemeSlugs' => 'Возвращает slug доступных тем',
];

$files = array_merge(
    glob(__DIR__.'/../app/Repositories/*.php') ?: [],
    glob(__DIR__.'/../app/Repositories/Search/*.php') ?: [],
    glob(__DIR__.'/../app/Services/*.php') ?: [],
);

$fixed = 0;

foreach ($files as $path) {
    $source = file_get_contents($path);

    if ($source === false || ! str_contains($source, '* Метод')) {
        continue;
    }

    $original = $source;

    $source = preg_replace_callback(
        '/(?:\/\*\*(?:[^*]|\*(?!\/))*\*\/\s*\n)*\s*\/\*\*\s*\n\s*\* Метод\s+(public function (\w+)\([^)]*\)[^{]*\{)\.\s*\n\s*\*\s*\n\s*\* \/\*\* (@return[^\n]+)\s*\n\s*\n\s*\*\/@return[^\n]+\s*\n(\s*return[\s\S]*?\n\s*\})/',
        static function (array $m) use ($descriptions): string {
            $method = $m[2];
            $description = $descriptions[$method] ?? 'Метод '.$method;

            return "    /**\n     * {$description}.\n     *\n     * {$m[3]}\n     */\n    {$m[1]}\n{$m[4]}";
        },
        $source,
    ) ?? $source;

    if ($source !== $original) {
        file_put_contents($path, $source);
        $fixed++;
        echo "Repaired: {$path}\n";
    }
}

echo "Repaired {$fixed} file(s).\n";
