#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Дополняет голые @return array и @return Collection типами для PHPStan.
 */
$root = realpath(__DIR__.'/../app');

if ($root === false) {
    exit(2);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
);

$fixed = 0;

foreach ($iterator as $fileInfo) {
    if (! $fileInfo instanceof SplFileInfo || ! $fileInfo->isFile() || ! str_ends_with($fileInfo->getPathname(), '.php')) {
        continue;
    }

    $source = file_get_contents($fileInfo->getPathname());

    if ($source === false) {
        continue;
    }

    $original = $source;

    $source = preg_replace(
        '/@return array\s*\n(\s*\*\/\s*\n\s*(?:public|protected) static function getPages\()/m',
        "@return array<string, \\Filament\\Resources\\Pages\\PageRegistration>\n$1",
        $source,
    ) ?? $source;

    $source = preg_replace(
        '/@return array\s*\n(\s*\*\/\s*\n\s*(?:public|protected) static function getRelations\()/m',
        "@return array<string, class-string<\\Filament\\Resources\\RelationManagers\\RelationManager>>\n$1",
        $source,
    ) ?? $source;

    $source = preg_replace(
        '/@return array\s*\n(\s*\*\/\s*\n\s*(?:public|protected) function getHeaderActions\()/m',
        "@return array<int, \\Filament\\Actions\\Action>\n$1",
        $source,
    ) ?? $source;

    $source = preg_replace(
        '/@return array\s*\n(\s*\*\/\s*\n\s*(?:public|protected) function getTabs\()/m',
        "@return array<string, \\Filament\\Resources\\Components\\Tab>\n$1",
        $source,
    ) ?? $source;

    $source = preg_replace(
        '/@return array\s*\n(\s*\*\/\s*\n\s*(?:public|protected) function components\()/m',
        "@return array<int, \\Filament\\Schemas\\Components\\Component>\n$1",
        $source,
    ) ?? $source;

    $source = preg_replace(
        '/@return Collection\s*\n(\s*\*\/\s*\n\s*public function )/m',
        "@return \\Illuminate\\Support\\Collection<int, mixed>\n$1",
        $source,
    ) ?? $source;

    $source = preg_replace(
        '/@return array\s*\n(\s*\*\/\s*\n\s*(?:public|protected) function rules\()/m',
        "@return array<string, mixed>\n$1",
        $source,
    ) ?? $source;

    $source = preg_replace(
        '/@return array\s*\n(\s*\*\/\s*\n\s*(?:public|protected) function messages\()/m',
        "@return array<string, string>\n$1",
        $source,
    ) ?? $source;

    $source = preg_replace(
        '/@return array\s*\n(\s*\*\/\s*\n\s*(?:public|protected|private) function )/m',
        "@return array<string, mixed>\n$1",
        $source,
    ) ?? $source;

    if ($source !== $original) {
        file_put_contents($fileInfo->getPathname(), $source);
        $fixed++;
    }
}

echo "Fixed array/collection return types in {$fixed} files.\n";
