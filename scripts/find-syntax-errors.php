#!/usr/bin/env php
<?php

declare(strict_types=1);

$roots = [__DIR__.'/../app', __DIR__.'/../tests', __DIR__.'/../database'];

foreach ($roots as $root) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if (! $fileInfo->isFile() || ! str_ends_with($fileInfo->getPathname(), '.php')) {
            continue;
        }

        exec('php -l '.escapeshellarg($fileInfo->getPathname()).' 2>&1', $output, $code);

        if ($code !== 0) {
            echo implode("\n", $output)."\n";
        }
    }
}
