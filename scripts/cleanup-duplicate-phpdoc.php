#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Сливает дубли docblock и переносит отдельные one-line @return в основной блок.
 */
$roots = [
    realpath(__DIR__.'/../app'),
    realpath(__DIR__.'/../tests'),
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

        if (str_contains($fileInfo->getPathname(), '/migrations/')) {
            continue;
        }

        $source = file_get_contents($fileInfo->getPathname());

        if ($source === false) {
            continue;
        }

        $original = $source;

        $source = preg_replace(
            '/(\/\*\*(?:[^*]|\*(?!\/))*\*\/\s*\n)(\s*\1)+/',
            '$1',
            $source,
        ) ?? $source;

        $source = preg_replace_callback(
            '/(\/\*\*(?:.*?\n\s*)*?\*\/\s*\n)\s*(\/\*\*\s*@return[^\n]*\n\s*\*\/\s*\n)(\s*(?:public|protected|private) function )/s',
            static function (array $m): string {
                $main = rtrim($m[1]);
                $main = preg_replace('/\s*\*\/\s*$/', '', $main) ?? $main;
                $returnLine = trim(preg_replace('/^\/\*\*\s*|\s*\*\/\s*$/', '', $m[2]) ?? $m[2]);

                if (str_contains($main, '@return')) {
                    return $m[1].$m[2].$m[3];
                }

                return $main."\n * {$returnLine}\n */".$m[3];
            },
            $source,
        ) ?? $source;

        if ($source !== $original) {
            file_put_contents($fileInfo->getPathname(), $source);
            $fixed++;
        }
    }
}

echo "Cleaned duplicate PHPDoc blocks in {$fixed} file(s).\n";
