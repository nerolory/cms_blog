#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * TD-FMT-01: разбивает строки >120 символов без полного PrettyPrinter.
 * Итеративно: запятые на глубине скобок, затем пробелы для цепочек ->.
 */
$root = realpath(__DIR__.'/..');

if ($root === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(2);
}

$limit = 120;
$dryRun = in_array('--dry-run', $argv, true);
$scanRoots = [
    $root.'/app',
    $root.'/tests',
    $root.'/database',
];

$totalFixed = 0;

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

        $path = $fileInfo->getPathname();
        $source = file_get_contents($path);

        if ($source === false) {
            continue;
        }

        $changed = false;
        $maxPasses = 50;

        for ($pass = 0; $pass < $maxPasses; $pass++) {
            $lines = explode("\n", str_replace("\r\n", "\n", $source));
            $newLines = [];
            $fileChanged = false;

            foreach ($lines as $line) {
                $wrapped = wrapLongLine($line, $limit);

                if ($wrapped !== [$line]) {
                    $fileChanged = true;
                }

                foreach ($wrapped as $wrappedLine) {
                    $newLines[] = $wrappedLine;
                }
            }

            $source = implode("\n", $newLines);

            if (! $fileChanged) {
                break;
            }

            $changed = true;
        }

        if (! $changed) {
            continue;
        }

        if (! str_ends_with($source, "\n")) {
            $source .= "\n";
        }

        if ($dryRun) {
            echo "Would fix: {$path}\n";
        } else {
            file_put_contents($path, $source);
            echo "Fixed: {$path}\n";
        }

        $totalFixed++;
    }
}

echo $dryRun
    ? "Dry run complete. {$totalFixed} file(s) would be updated.\n"
    : "Done. {$totalFixed} file(s) updated.\n";

/**
 * @return list<string>
 */
function wrapLongLine(string $line, int $limit): array
{
    $content = rtrim($line, "\r\n");

    if ($content === '' || strlen($content) <= $limit) {
        return [$line];
    }

    if (preg_match('/^\s*(\/\/|#)/', $content) === 1) {
        return [$line];
    }

    if (preg_match('/^\s*\/\*\*?/', $content) === 1 || preg_match('/^\s*\*\/\s*$/', $content) === 1) {
        return [$line];
    }

    preg_match('/^(\s*)/', $content, $indentMatch);
    $indent = $indentMatch[1];
    $continuation = $indent.'    ';

    $commaBreak = breakByCommas($content, $limit, $indent, $continuation);

    if ($commaBreak !== null) {
        return $commaBreak;
    }

    $commaBreak = breakByCommasAggressive($content, $limit, $indent, $continuation);

    if ($commaBreak !== null) {
        return $commaBreak;
    }

    $wordBreak = breakByWords($content, $limit, $indent);

    if ($wordBreak !== null) {
        return $wordBreak;
    }

    $arrowBreak = breakByArrow($content, $limit, $indent, $continuation);

    if ($arrowBreak !== null) {
        return $arrowBreak;
    }

    return [$line];
}

/**
 * @return list<string>|null
 */
function breakByCommas(string $content, int $limit, string $indent, string $continuation): ?array
{
    $breaks = findCommaBreakPositions($content);

    if ($breaks === []) {
        return null;
    }

    $segments = [];
    $start = 0;
    $length = strlen($content);

    foreach ($breaks as $breakPos) {
        $segments[] = substr($content, $start, $breakPos - $start);
        $start = $breakPos;
    }

    $segments[] = substr($content, $start);

    $lines = [];
    $current = '';

    foreach ($segments as $index => $segment) {
        $candidate = $current === '' ? $segment : $current.$segment;

        if ($current !== '' && strlen(rtrim($candidate)) > $limit) {
            $lines[] = rtrim($current);
            $current = $continuation.ltrim($segment);
        } else {
            $current = $candidate;
        }
    }

    if ($current !== '') {
        $lines[] = rtrim($current);
    }

    if (count($lines) <= 1) {
        return null;
    }

    foreach ($lines as $resultLine) {
        if (strlen($resultLine) > $limit) {
            return null;
        }
    }

    return $lines;
}

/**
 * @return list<int>
 */
function findCommaBreakPositions(string $content): array
{
    $breaks = [];
    $length = strlen($content);
    $depthParen = 0;
    $depthBracket = 0;
    $depthBrace = 0;
    $inString = false;
    $stringChar = '';
    $inSingleLineComment = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $content[$i];
        $next = $i + 1 < $length ? $content[$i + 1] : '';

        if ($inSingleLineComment) {
            if ($char === "\n") {
                $inSingleLineComment = false;
            }

            continue;
        }

        if (! $inString && $char === '/' && $next === '/') {
            $inSingleLineComment = true;

            continue;
        }

        if ($inString) {
            if ($char === '\\' && $next !== '') {
                $i++;

                continue;
            }

            if ($char === $stringChar) {
                $inString = false;
            }

            continue;
        }

        if ($char === '"' || $char === "'") {
            $inString = true;
            $stringChar = $char;

            continue;
        }

        match ($char) {
            '(' => $depthParen++,
            ')' => $depthParen = max(0, $depthParen - 1),
            '[' => $depthBracket++,
            ']' => $depthBracket = max(0, $depthBracket - 1),
            '{' => $depthBrace++,
            '}' => $depthBrace = max(0, $depthBrace - 1),
            ',' => ($depthParen > 0 || $depthBracket > 0) ? $breaks[] = $i + 1 : null,
            default => null,
        };
    }

    return $breaks;
}

/**
 * Одна запятая — новая строка (для монолитных вызовов new self / массивов).
 *
 * @return list<string>|null
 */
function breakByCommasAggressive(string $content, int $limit, string $indent, string $continuation): ?array
{
    $breaks = findCommaBreakPositions($content);

    if ($breaks === []) {
        return null;
    }

    $segments = [];
    $start = 0;

    foreach ($breaks as $breakPos) {
        $segments[] = substr($content, $start, $breakPos - $start);
        $start = $breakPos;
    }

    $segments[] = substr($content, $start);

    if (count($segments) <= 1) {
        return null;
    }

    $lines = [];

    foreach ($segments as $index => $segment) {
        $lines[] = $index === 0 ? rtrim($segment) : rtrim($continuation.ltrim($segment));
    }

    foreach ($lines as $resultLine) {
        if (strlen($resultLine) > $limit) {
            return null;
        }
    }

    return $lines;
}

/**
 * Перенос длинных PHPDoc по пробелам.
 *
 * @return list<string>|null
 */
function breakByWords(string $content, int $limit, string $indent): ?array
{
    if (preg_match('/^(\s*\*\s?)/', $content, $prefixMatch) !== 1) {
        return null;
    }

    $prefix = $prefixMatch[1];
    $text = substr($content, strlen($prefix));

    if (trim($text) === '' || ! str_contains($text, ' ')) {
        return null;
    }

    $words = preg_split('/\s+/', trim($text)) ?: [];

    if ($words === []) {
        return null;
    }

    $lines = [];
    $current = $indent.$prefix.$words[0];

    for ($i = 1, $count = count($words); $i < $count; $i++) {
        $word = $words[$i];
        $candidate = $current.' '.$word;

        if (strlen($candidate) > $limit) {
            $lines[] = $current;
            $current = $indent.$prefix.$word;
        } else {
            $current = $candidate;
        }
    }

    $lines[] = $current;

    if (count($lines) <= 1) {
        return null;
    }

    foreach ($lines as $resultLine) {
        if (strlen($resultLine) > $limit) {
            return null;
        }
    }

    return $lines;
}

/**
 * @return list<string>|null
 */
function breakByArrow(string $content, int $limit, string $indent, string $continuation): ?array
{
    if (! str_contains($content, '->')) {
        return null;
    }

    $parts = preg_split('/(?=\->)/', $content, -1, PREG_SPLIT_NO_EMPTY);

    if ($parts === false || count($parts) <= 1) {
        return null;
    }

    $lines = [];
    $current = array_shift($parts);

    foreach ($parts as $part) {
        $candidate = $current.$part;

        if (strlen(rtrim($candidate)) > $limit && $current !== '') {
            $lines[] = rtrim($current);
            $current = $continuation.ltrim($part);
        } else {
            $current = $candidate;
        }
    }

    if ($current !== '') {
        $lines[] = rtrim($current);
    }

    if (count($lines) <= 1) {
        return null;
    }

    foreach ($lines as $resultLine) {
        if (strlen($resultLine) > $limit) {
            return null;
        }
    }

    return $lines;
}
