#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Проверяет длину строк в PHP-файлах (лимит 120 символов).
 */
$root = realpath(__DIR__.'/..');

if ($root === false) {
    exit(2);
}

$limit = 120;
$scanRoots = [
    $root.'/app',
    $root.'/tests',
    $root.'/database',
];

$violations = [];

foreach ($scanRoots as $scanRoot) {
    if (! is_dir($scanRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if (! $fileInfo instanceof SplFileInfo || ! $fileInfo->isFile() || ! str_ends_with($fileInfo->getPathname(), '.php')) {
            continue;
        }

        if (str_contains($fileInfo->getPathname(), '/migrations/')) {
            continue;
        }

        $lines = file($fileInfo->getPathname());

        if ($lines === false) {
            continue;
        }

        $relative = str_replace('\\', '/', substr($fileInfo->getPathname(), strlen($root) + 1));

        foreach ($lines as $index => $line) {
            if (strlen(rtrim($line, "\r\n")) > $limit) {
                $violations[] = sprintf('%s:%d (%d chars)', $relative, $index + 1, strlen(rtrim($line, "\r\n")));
            }
        }
    }
}

if ($violations === []) {
    echo "Line length check passed (limit {$limit}).\n";
    exit(0);
}

echo "Line length check failed (>{$limit} chars):\n";

foreach (array_slice($violations, 0, 40) as $violation) {
    echo "  - {$violation}\n";
}

if (count($violations) > 40) {
    echo '  ... and '.(count($violations) - 40)." more\n";
}

echo 'TOTAL='.count($violations).PHP_EOL;
exit(1);
