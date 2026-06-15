#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Проверяет качество PHPDoc: язык, осмысленность, запрет шаблонов автогенератора.
 */
require __DIR__.'/../vendor/autoload.php';

$projectRoot = realpath(__DIR__.'/..');

if ($projectRoot === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(2);
}

$scanRoots = [
    $projectRoot.'/app',
    $projectRoot.'/tests',
    $projectRoot.'/database',
];

$forbiddenPatterns = [
    '/\bЗначение\s+\w/i' => 'шаблон «Значение …»',
    '/\bРезультат выполнения\b/u' => 'шаблон «Результат выполнения»',
    '/\bРезультат проверки\b/u' => 'шаблон «Результат проверки»',
];

/** @var list<string> */
$englishOnlyMethodDescriptions = [
    '/^\s*\*\s*(request|approve|reject|execute|handle|boot|register)\.\s*$/mi',
    '/^\s*\*\s*calculate tokens required\.\s*$/mi',
    '/^\s*\*\s*set up\.\s*$/mi',
];

$issues = [];

foreach ($scanRoots as $root) {
    if (! is_dir($root)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if (! $fileInfo instanceof SplFileInfo || ! $fileInfo->isFile()) {
            continue;
        }

        $path = $fileInfo->getPathname();

        if (! str_ends_with($path, '.php') || str_contains($path, '/migrations/')) {
            continue;
        }

        $source = file_get_contents($path);

        if ($source === false) {
            continue;
        }

        $relative = str_replace('\\', '/', substr($path, strlen($projectRoot) + 1));

        foreach ($forbiddenPatterns as $pattern => $label) {
            if (preg_match($pattern, $source)) {
                $issues[] = "{$relative}: найден {$label}";
            }
        }

        foreach ($englishOnlyMethodDescriptions as $pattern) {
            if (preg_match($pattern, $source)) {
                $issues[] = "{$relative}: описание метода на английском (одно слово)";
            }
        }
    }
}

if ($issues !== []) {
    fwrite(STDERR, "PHPDoc quality check failed:\n");

    foreach (array_slice($issues, 0, 50) as $issue) {
        fwrite(STDERR, "  - {$issue}\n");
    }

    if (count($issues) > 50) {
        fwrite(STDERR, '  ... и ещё '.(count($issues) - 50)." замечаний.\n");
    }

    exit(1);
}

echo 'PHPDoc quality check passed.'.PHP_EOL;
