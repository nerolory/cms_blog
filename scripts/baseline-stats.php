<?php

declare(strict_types=1);

$lines = file(__DIR__.'/../phpstan-baseline.neon');
$paths = [];
foreach ($lines as $line) {
    if (preg_match('/path: (.+)/', $line, $m)) {
        $paths[$m[1]] = ($paths[$m[1]] ?? 0) + 1;
    }
}
arsort($paths);
foreach (array_slice($paths, 0, 30, true) as $path => $count) {
    echo "{$count}\t{$path}\n";
}
echo 'TOTAL='.array_sum($paths).PHP_EOL;
