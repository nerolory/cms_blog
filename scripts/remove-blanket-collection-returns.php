#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Удаляет ошибочные @return Collection<int, mixed> из PHPDoc (добавлены fix-array-return-types).
 */
$roots = [
    realpath(__DIR__.'/../app'),
    realpath(__DIR__.'/../database'),
];

$fixed = 0;

foreach ($roots as $root) {
    if ($root === false || ! is_dir($root)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if (! $fileInfo instanceof SplFileInfo || ! $fileInfo->isFile() || ! str_ends_with($fileInfo->getPathname(), '.php')) {
            continue;
        }

        $source = file_get_contents($fileInfo->getPathname());

        if ($source === false) {
            continue;
        }

        $updated = preg_replace(
            '/\s*\*\s*@return \\\\Illuminate\\\\Support\\\\Collection<int, mixed>\s*\n/',
            "\n",
            $source,
        );

        if ($updated !== null && $updated !== $source) {
            file_put_contents($fileInfo->getPathname(), $updated);
            $fixed++;
        }
    }
}

echo "Removed blanket Collection return tags from {$fixed} files.\n";
