#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Удаляет @property/@property-read/@property-write из class PHPDoc (PHPStan level 9).
 * Свойства документируются через native types и конструктор; class doc — только описание.
 */
require __DIR__.'/../vendor/autoload.php';

use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

$roots = [__DIR__.'/../app', __DIR__.'/../tests', __DIR__.'/../database'];
$parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 3));
$printer = new PrettyPrinter;
$fixed = 0;

foreach ($roots as $root) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getPathname(), '.php')) {
            continue;
        }

        $source = file_get_contents($file->getPathname());

        if ($source === false) {
            continue;
        }

        $ast = $parser->parse($source);

        if ($ast === null) {
            continue;
        }

        $changed = false;

        foreach ($ast as $stmt) {
            $stmts = $stmt instanceof Namespace_ ? $stmt->stmts : [$stmt];

            foreach ($stmts as $inner) {
                if (! $inner instanceof Class_ || $inner->getDocComment() === null) {
                    continue;
                }

                $text = $inner->getDocComment()->getText();
                $lines = explode("\n", $text);
                $filtered = array_values(array_filter(
                    $lines,
                    static fn (string $line): bool => ! preg_match('/^\s*\*\s*@property(-read|-write)?\b/', $line),
                ));

                if (count($filtered) === count($lines)) {
                    continue;
                }

                $inner->setDocComment(new Doc(implode("\n", $filtered)));
                $changed = true;
            }
        }

        if ($changed) {
            file_put_contents($file->getPathname(), $printer->prettyPrintFile($ast));
            echo 'Stripped properties: '.$file->getPathname().PHP_EOL;
            $fixed++;
        }
    }
}

echo "Done. {$fixed} file(s) updated.\n";
