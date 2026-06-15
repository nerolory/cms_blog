#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * F6: массовое исправление шаблонного PHPDoc и типов для PHPStan (не fix-phpdoc.php).
 */
require __DIR__.'/../vendor/autoload.php';

$projectRoot = realpath(__DIR__.'/../');

if ($projectRoot === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(2);
}

$scanRoots = [
    $projectRoot.'/app',
    $projectRoot.'/tests',
    $projectRoot.'/database',
];

$paramLabels = [
    'key' => 'ключ',
    'keys' => 'список полей',
    'ttl' => 'время жизни кэша',
    'resolver' => 'функция получения данных',
    'request' => 'HTTP-запрос',
    'user' => 'пользователь',
    'post' => 'пост',
    'data' => 'данные формы',
    'schema' => 'схема Filament',
    'payload' => 'сериализованные данные',
    'paginator' => 'пагинатор',
    'model' => 'модель',
    'mailSettingsService' => 'сервис настроек почты',
    'searchSettingsService' => 'сервис настроек поиска',
];

$fixedFiles = 0;

foreach ($scanRoots as $root) {
    if (! is_dir($root)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if (! $fileInfo instanceof SplFileInfo || ! $fileInfo->isFile() || ! str_ends_with($fileInfo->getPathname(), '.php')) {
            continue;
        }

        $path = $fileInfo->getPathname();

        if (str_contains($path, '/migrations/')) {
            continue;
        }

        $source = file_get_contents($path);

        if ($source === false) {
            continue;
        }

        $original = $source;

        $source = preg_replace('/\bРезультат выполнения\b/u', '', $source) ?? $source;
        $source = preg_replace('/\bРезультат проверки\b/u', '', $source) ?? $source;
        $source = preg_replace('/\bНайденное значение\b/u', '', $source) ?? $source;

        foreach ($paramLabels as $name => $label) {
            $source = preg_replace(
                '/@param\s+([^\n]+)\s+\$'.preg_quote($name, '/').'\s+Значение[^\n]*/u',
                '@param $1 $'.$name.' '.$label,
                $source,
            ) ?? $source;
        }

        $source = preg_replace('/@param\s+([^\n]+)\s+\$(\w+)\s+Значение\s+\w+/u', '@param $1 $$2', $source) ?? $source;

        $source = preg_replace(
            '/(\/\*\*[^*]*\* @return )array(\s*\*\/\s*\n\s*(?:public|protected|private) function rules\()/s',
            '$1array<string, mixed>$2',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/(\/\*\*[^*]*\* @return )array(\s*\*\/\s*\n\s*(?:public|protected|private) function messages\()/s',
            '$1array<string, string>$2',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/(\/\*\*[^*]*\* @return )array(\s*\*\/\s*\n\s*(?:public|protected|private) function getHeaderActions\()/s',
            '$1array<int, \\Filament\\Actions\\Action>$2',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/(\/\*\*[^*]*\* @return )array(\s*\*\/\s*\n\s*(?:public|protected) static function getPages\()/s',
            '$1array<string, \\Filament\\Resources\\Pages\\PageRegistration>$2',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/(\/\*\*[^*]*\* @return )array(\s*\*\/\s*\n\s*(?:public|protected) static function getRelations\()/s',
            '$1array<string, class-string<\\Filament\\Resources\\RelationManagers\\RelationManager>>$2',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/(\/\*\*[^*]*\* @return )array(\s*\*\/\s*\n\s*(?:public|protected) function getTabs\()/s',
            '$1array<string, \\Filament\\Resources\\Components\\Tab>$2',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/(\/\*\*[^*]*\* @return )array(\s*\*\/\s*\n\s*(?:public|protected) function components\()/s',
            '$1array<int, \\Filament\\Schemas\\Components\\Component>$2',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/@return array\s*\n(\s*\*\/\s*\n\s*(?:public|protected|private) function rules\()/m',
            "@return array<string, mixed>\n$1",
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/@return array\s*\n(\s*\*\/\s*\n\s*(?:public|protected|private) function messages\()/m',
            "@return array<string, string>\n$1",
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/@param\s+array\s+\$keys\s+Значение keys/u',
            '@param list<string> $keys',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/@param\s+array\s+\$keys/u',
            '@param list<string> $keys',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/^\s*\*\s*(request|approve|reject|execute|handle|boot|register)\.\s*$/mi',
            '',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/^\s*\*\s*calculate tokens required\.\s*$/mi',
            ' * Рассчитывает необходимое количество токенов.',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/^\s*\*\s*set up\.\s*$/mi',
            ' * Подготавливает окружение теста.',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/^\s*\*\s*@return bool\s*$/mu',
            '',
            $source,
        ) ?? $source;

        $source = preg_replace(
            '/^\s*\*\s*@return string\s*$/mu',
            '',
            $source,
        ) ?? $source;

        $source = preg_replace("/\n{3,}/", "\n\n", $source) ?? $source;

        if ($source !== $original) {
            file_put_contents($path, $source);
            $fixedFiles++;
        }
    }
}

echo "Remediated {$fixedFiles} files.\n";
