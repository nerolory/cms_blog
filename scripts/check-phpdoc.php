#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Tools\PhpStan\Support\PhpDocClassAnalyzer;
use App\Tools\PhpStan\Support\PhpDocRequirements;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;

require __DIR__.'/../vendor/autoload.php';

$projectRoot = realpath(__DIR__.'/..');

if ($projectRoot === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(2);
}

$scanRoots = [
    'app' => $projectRoot.'/app',
    'tests' => $projectRoot.'/tests',
    'database' => $projectRoot.'/database',
];

$parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 3));
$fileIssues = [];

foreach ($scanRoots as $label => $root) {
    if (! is_dir($root)) {
        fwrite(STDERR, "Skip missing directory: {$root}\n");

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

        if (! str_ends_with($path, '.php')) {
            continue;
        }

        if (PhpDocRequirements::shouldSkipPath($path)) {
            continue;
        }

        $source = file_get_contents($path);

        if ($source === false) {
            fwrite(STDERR, "Unable to read file: {$path}\n");
            exit(2);
        }

        try {
            $ast = $parser->parse($source);
        } catch (Throwable $exception) {
            fwrite(STDERR, "Parse error in {$path}: {$exception->getMessage()}\n");
            exit(2);
        }

        if ($ast === null) {
            continue;
        }

        $namespace = null;

        foreach ($ast as $stmt) {
            if ($stmt instanceof Namespace_) {
                $namespace = $stmt->name?->toString();
                $issues = analyzeStatements($stmt->stmts, $namespace, $path, $label === 'app');
                if ($issues !== []) {
                    $fileIssues[$path] = array_merge($fileIssues[$path] ?? [], $issues);
                }

                continue;
            }

            if ($stmt instanceof ClassLike && $stmt->name !== null) {
                $fqcn = $stmt->name->toString();
                $issues = PhpDocClassAnalyzer::analyze($stmt, $fqcn, $path, appOnly: $label === 'app');
                if ($issues !== []) {
                    $fileIssues[$path] = array_merge($fileIssues[$path] ?? [], $issues);
                }
            }
        }
    }
}

if ($fileIssues === []) {
    echo "PHPDoc check passed: no missing documentation found.\n";
    exit(0);
}

ksort($fileIssues);

$totalIssues = 0;

foreach ($fileIssues as $path => $issues) {
    $relativePath = str_replace('\\', '/', substr($path, strlen($projectRoot) + 1));
    echo PHP_EOL.$relativePath.PHP_EOL;

    foreach ($issues as $issue) {
        echo sprintf("  L%d: %s\n", $issue['line'], $issue['message']);
        $totalIssues++;
    }
}

echo PHP_EOL.sprintf(
    'PHPDoc check failed: %d issue(s) in %d file(s).',
    $totalIssues,
    count($fileIssues),
).PHP_EOL;

exit(1);

/**
 * @param  array<int, Node>|null  $statements
 * @return list<array{message: string, line: int}>
 */
function analyzeStatements(?array $statements, ?string $namespace, string $path, bool $appOnly): array
{
    if ($statements === null) {
        return [];
    }

    $issues = [];

    foreach ($statements as $stmt) {
        if (! $stmt instanceof ClassLike || $stmt->name === null) {
            continue;
        }

        $fqcn = $namespace !== null && $namespace !== ''
            ? $namespace.'\\'.$stmt->name->toString()
            : $stmt->name->toString();

        $issues = array_merge(
            $issues,
            PhpDocClassAnalyzer::analyze($stmt, $fqcn, $path, appOnly: $appOnly),
        );
    }

    return $issues;
}
