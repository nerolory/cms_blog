#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Исправляет docblock с лишним закрывающим star-slash после @return.
 */
$roots = [
    realpath(__DIR__.'/../app/Repositories'),
    realpath(__DIR__.'/../app/Services'),
];

$fixed = 0;

foreach ($roots as $root) {
    if ($root === false) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if (! $fileInfo->isFile() || ! str_ends_with($fileInfo->getPathname(), '.php')) {
            continue;
        }

        $source = file_get_contents($fileInfo->getPathname());

        if ($source === false) {
            continue;
        }

        $original = $source;

        $source = preg_replace(
            '/\s*\/\*\*\s*\n\s*\* ([^\n*][^\n]*)\.\s*\n\s*\*\s*\n\s*\* (@return[^\n]+) \*\/\s*\n\s*\*\/\s*\n(\s*public function)/s',
            "    /**\n     * $1.\n     *\n     * $2\n     */\n$3",
            $source,
        ) ?? $source;

        if ($source !== $original) {
            file_put_contents($fileInfo->getPathname(), $source);
            $fixed++;
            echo "Fixed: {$fileInfo->getPathname()}\n";
        }
    }
}

echo "Fixed malformed docblocks in {$fixed} file(s).\n";
