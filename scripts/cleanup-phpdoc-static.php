#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Удаляет @property-теги для static-свойств из class PHPDoc (ложные срабатывания PHPStan).
 */
require __DIR__.'/../vendor/autoload.php';

use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
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
                if (! $inner instanceof Class_) {
                    continue;
                }

                $staticNames = [];

                foreach ($inner->getProperties() as $property) {
                    if (! $property->isStatic()) {
                        continue;
                    }

                    foreach ($property->props as $prop) {
                        $staticNames[] = $prop->name->toString();
                    }
                }

                if ($staticNames === []) {
                    continue;
                }

                $doc = $inner->getDocComment();

                if ($doc === null) {
                    continue;
                }

                $text = $doc->getText();
                $lines = explode("\n", $text);
                $filtered = [];

                foreach ($lines as $line) {
                    $drop = false;

                    foreach ($staticNames as $name) {
                        if (preg_match('/@(?:property|property-read|property-write)\b.+\$'.preg_quote($name, '/').'\b/', $line)) {
                            $drop = true;
                            break;
                        }
                    }

                    if (! $drop) {
                        $filtered[] = $line;
                    }
                }

                $newText = implode("\n", $filtered);

                if ($newText !== $text) {
                    $inner->setDocComment(new Doc($newText));
                    $changed = true;
                }
            }
        }

        if ($changed) {
            file_put_contents($file->getPathname(), $printer->prettyPrintFile($ast));
            echo 'Cleaned: '.$file->getPathname().PHP_EOL;
            $fixed++;
        }
    }
}

echo "Done. {$fixed} file(s) cleaned.\n";
