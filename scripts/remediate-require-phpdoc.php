#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * F6: закрывает пробелы RequirePhpDocRule — @return, описания классов/методов, @var.
 * Без шаблонов «Значение …» / «Результат выполнения» (в отличие от fix-phpdoc.php).
 *
 * Правит только PHPDoc в исходнике, без PrettyPrinter (сохраняет форматирование файла).
 */

use App\Tools\PhpStan\Support\PhpDocRequirements;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\UnionType;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;

require __DIR__.'/../vendor/autoload.php';

$projectRoot = realpath(__DIR__.'/..');

if ($projectRoot === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(2);
}

$dryRun = in_array('--dry-run', $argv, true);
$scanRoots = [
    $projectRoot.'/app',
    $projectRoot.'/tests',
    $projectRoot.'/database',
];

$parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 3));
$fixedFiles = 0;

foreach ($scanRoots as $root) {
    if (! is_dir($root)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if (! $fileInfo instanceof SplFileInfo || ! $fileInfo->isFile() || ! str_ends_with($fileInfo->getPathname(), '.php')) {
            continue;
        }

        $path = $fileInfo->getPathname();

        if (PhpDocRequirements::shouldSkipPath($path)) {
            continue;
        }

        $source = file_get_contents($path);

        if ($source === false) {
            continue;
        }

        try {
            $ast = $parser->parse($source);
        } catch (Throwable) {
            continue;
        }

        if ($ast === null) {
            continue;
        }

        /** @var list<array{type: 'replace'|'insert', startLine: int, endLine?: int, content: string}> $patches */
        $patches = [];
        $namespace = null;

        foreach ($ast as $stmt) {
            if ($stmt instanceof Namespace_) {
                $namespace = $stmt->name?->toString();
                $patches = array_merge($patches, collectClassPatches($stmt->stmts, $namespace, $source));
            } elseif ($stmt instanceof ClassLike && $stmt->name !== null) {
                $fqcn = $namespace !== null ? $namespace.'\\'.$stmt->name : $stmt->name->toString();
                $patches = array_merge($patches, collectClassLikePatches($stmt, $fqcn, $source));
            }
        }

        if ($patches === []) {
            continue;
        }

        $output = applySourcePatches($source, $patches);

        if (! str_ends_with($output, "\n")) {
            $output .= "\n";
        }

        if ($dryRun) {
            echo "Would fix: {$path}\n";
        } else {
            file_put_contents($path, $output);
            echo "Fixed: {$path}\n";
        }

        $fixedFiles++;
    }
}

echo $dryRun
    ? "Dry run complete. {$fixedFiles} file(s) would be updated.\n"
    : "Done. {$fixedFiles} file(s) updated.\n";

/**
 * @param  list<Node\Stmt>|null  $statements
 * @return list<array{type: 'replace'|'insert', startLine: int, endLine?: int, content: string}>
 */
function collectClassPatches(?array $statements, ?string $namespace, string $source): array
{
    if ($statements === null) {
        return [];
    }

    $patches = [];

    foreach ($statements as $stmt) {
        if (! $stmt instanceof ClassLike || $stmt->name === null) {
            continue;
        }

        $fqcn = $namespace !== null ? $namespace.'\\'.$stmt->name : $stmt->name->toString();
        $patches = array_merge($patches, collectClassLikePatches($stmt, $fqcn, $source));
    }

    return $patches;
}

/**
 * @return list<array{type: 'replace'|'insert', startLine: int, endLine?: int, content: string}>
 */
function collectClassLikePatches(ClassLike $classLike, string $fqcn, string $source): array
{
    $patches = [];
    $shortName = classBasename($fqcn);
    $classDoc = $classLike->getDocComment();

    if (! PhpDocRequirements::docblockHasDescription($classDoc?->getText())) {
        $indent = lineIndentAt($source, $classLike->getStartLine());
        $patches[] = [
            'type' => 'insert',
            'startLine' => $classLike->getStartLine(),
            'content' => indentDocblock(buildClassDoc($classLike, $fqcn, $shortName), $indent),
        ];
        $classDocText = buildClassDoc($classLike, $fqcn, $shortName);
    } else {
        $classDocText = $classDoc?->getText() ?? '';
        $promoted = collectPromotedPropertyNames($classLike);
        $append = [];

        foreach ($promoted as $propertyName) {
            if (! PhpDocRequirements::classDocDocumentsProperty($classDocText, $propertyName)) {
                $param = findPromotedParam($classLike, $propertyName);
                $type = $param !== null ? typeToString($param->type) : 'mixed';
                $append[] = "@property-read {$type} \${$propertyName}";
            }
        }

        foreach ($classLike->getProperties() as $property) {
            if ($property->isPrivate() || $property->isStatic()) {
                continue;
            }

            foreach ($property->props as $prop) {
                $name = $prop->name->toString();

                if (in_array($name, $promoted, true) || PhpDocRequirements::shouldSkipPropertyName($name)) {
                    continue;
                }

                if (PhpDocRequirements::classDocDocumentsProperty($classDocText, $name)) {
                    continue;
                }

                $propDoc = $prop->getDocComment()?->getText() ?? $property->getDocComment()?->getText();

                if (PhpDocRequirements::docblockHasTag($propDoc, 'var')) {
                    continue;
                }

                $type = inferPropertyType($name, $prop->type ?? $property->type, $fqcn);
                $append[] = '@property '.$type.' $'.$name;
            }
        }

        if ($append !== [] && $classDoc !== null) {
            $patches[] = [
                'type' => 'replace',
                'startLine' => $classDoc->getStartLine(),
                'endLine' => $classDoc->getEndLine(),
                'content' => mergeClassDoc($classDocText, $append),
            ];
        }
    }

    foreach ($classLike->getMethods() as $method) {
        if ($method->isPrivate() || PhpDocRequirements::shouldSkipMethod($method->name->toString())) {
            continue;
        }

        if ($method->name->toString() === '__construct') {
            continue;
        }

        $doc = $method->getDocComment();
        $docText = $doc?->getText();

        if (! PhpDocRequirements::docblockHasDescription($docText)) {
            $indent = lineIndentAt($source, $method->getStartLine());
            $patch = [
                'type' => 'insert',
                'startLine' => $method->getStartLine(),
                'content' => indentDocblock(buildMethodDoc($method, $shortName), $indent),
            ];

            if ($doc !== null) {
                $patch = [
                    'type' => 'replace',
                    'startLine' => $doc->getStartLine(),
                    'endLine' => $doc->getEndLine(),
                    'content' => buildMethodDoc($method, $shortName),
                ];
            }

            $patches[] = $patch;
        } elseif (PhpDocRequirements::methodRequiresReturnTag($method) && ! PhpDocRequirements::docblockHasTag($docText, 'return') && $doc !== null) {
            $patches[] = [
                'type' => 'replace',
                'startLine' => $doc->getStartLine(),
                'endLine' => $doc->getEndLine(),
                'content' => mergeMethodReturn($docText, $method),
            ];
        }
    }

    return $patches;
}

/**
 * @param  list<array{type: 'replace'|'insert', startLine: int, endLine?: int, content: string}>  $patches
 */
function applySourcePatches(string $source, array $patches): string
{
    $lines = explode("\n", $source);

    usort(
        $patches,
        static fn (array $a, array $b): int => $b['startLine'] <=> $a['startLine'],
    );

    foreach ($patches as $patch) {
        $startIndex = $patch['startLine'] - 1;

        if ($patch['type'] === 'insert') {
            $newLines = explode("\n", rtrim($patch['content'], "\n"));
            array_splice($lines, $startIndex, 0, $newLines);

            continue;
        }

        $endIndex = ($patch['endLine'] ?? $patch['startLine']) - 1;
        $replaceCount = $endIndex - $startIndex + 1;
        $newLines = explode("\n", rtrim($patch['content'], "\n"));
        array_splice($lines, $startIndex, $replaceCount, $newLines);
    }

    return implode("\n", $lines);
}

function lineIndentAt(string $source, int $lineNumber): string
{
    if ($source === '') {
        return '    ';
    }

    $lines = explode("\n", $source);
    $line = $lines[$lineNumber - 1] ?? '';

    if (preg_match('/^(\s*)/', $line, $matches) === 1) {
        return $matches[1];
    }

    return '    ';
}

function indentDocblock(string $docblock, string $indent): string
{
    $lines = explode("\n", trim($docblock));

    return implode("\n", array_map(
        static fn (string $line): string => $line === '/**' || $line === ' */' || str_starts_with($line, ' *')
            ? $indent.$line
            : $indent.' * '.$line,
        $lines,
    ));
}

/**
 * @return list<string>
 */
function collectPromotedPropertyNames(ClassLike $classLike): array
{
    $constructor = $classLike->getMethod('__construct');

    if ($constructor === null) {
        return [];
    }

    $names = [];

    foreach ($constructor->params as $param) {
        if (! $param instanceof Param || ! isPromotedParam($param)) {
            continue;
        }

        if ($param->var instanceof Variable && is_string($param->var->name)) {
            $names[] = $param->var->name;
        }
    }

    return $names;
}

function findPromotedParam(ClassLike $classLike, string $propertyName): ?Param
{
    $constructor = $classLike->getMethod('__construct');

    if ($constructor === null) {
        return null;
    }

    foreach ($constructor->params as $param) {
        if ($param instanceof Param
            && $param->var instanceof Variable
            && is_string($param->var->name)
            && $param->var->name === $propertyName) {
            return $param;
        }
    }

    return null;
}

function isPromotedParam(Param $param): bool
{
    return ($param->flags & Modifiers::PUBLIC) !== 0
        || ($param->flags & Modifiers::PROTECTED) !== 0
        || ($param->flags & Modifiers::PRIVATE) !== 0;
}

function buildClassDoc(ClassLike $classLike, string $fqcn, string $shortName): string
{
    $lines = ['/**', ' * '.classDescription($fqcn, $shortName).'.'];

    foreach (collectPromotedPropertyNames($classLike) as $propertyName) {
        $param = findPromotedParam($classLike, $propertyName);
        $type = $param !== null ? typeToString($param->type) : 'mixed';
        $lines[] = ' * @property-read '.$type.' $'.$propertyName;
    }

    $lines[] = ' */';

    return implode("\n", $lines);
}

/**
 * @param  list<string>  $append
 */
function mergeClassDoc(string $existing, array $append): string
{
    $trimmed = rtrim($existing);
    $trimmed = preg_replace('/\*\/\s*$/', '', $trimmed) ?? $trimmed;

    foreach ($append as $line) {
        $trimmed .= "\n * ".$line;
    }

    return $trimmed."\n */";
}

function buildMethodDoc(ClassMethod $method, string $classShortName): string
{
    $name = $method->name->toString();
    $lines = ['/**', ' * '.methodDescription($name, $classShortName).'.'];

    foreach ($method->params as $param) {
        if (! $param instanceof Param || ! $param->var instanceof Variable || ! is_string($param->var->name)) {
            continue;
        }

        $type = typeToString($param->type) ?: 'mixed';
        $lines[] = ' * @param '.$type.' $'.$param->var->name;
    }

    if (PhpDocRequirements::methodRequiresReturnTag($method)) {
        $returnType = typeToString($method->getReturnType()) ?: 'mixed';
        $lines[] = ' * @return '.$returnType;
    }

    $lines[] = ' */';

    return implode("\n", $lines);
}

function mergeMethodReturn(string $existing, ClassMethod $method): string
{
    $trimmed = rtrim($existing);
    $trimmed = preg_replace('/\*\/\s*$/', '', $trimmed) ?? $trimmed;
    $returnType = typeToString($method->getReturnType()) ?: 'mixed';

    return $trimmed."\n * @return {$returnType}\n */";
}

function classBasename(string $fqcn): string
{
    $parts = explode('\\', $fqcn);

    return end($parts) ?: $fqcn;
}

function classDescription(string $fqcn, string $shortName): string
{
    return match (true) {
        str_contains($fqcn, '\\Services\\Contracts\\') => 'Контракт сервиса '.humanize($shortName),
        str_contains($fqcn, '\\Repositories\\Contracts\\') => 'Контракт репозитория '.humanize($shortName),
        str_contains($fqcn, '\\Services\\') => 'Сервис '.humanize($shortName),
        str_contains($fqcn, '\\Repositories\\') => 'Репозиторий '.humanize($shortName),
        str_contains($fqcn, '\\Http\\Controllers\\') => 'HTTP-контроллер '.humanize($shortName),
        str_contains($fqcn, '\\Http\\Middleware\\') => 'HTTP middleware '.humanize($shortName),
        str_contains($fqcn, '\\Http\\Requests\\') => 'Валидация запроса '.humanize($shortName),
        str_contains($fqcn, '\\DTO\\') => 'DTO '.humanize($shortName),
        str_contains($fqcn, '\\Models\\') => 'Eloquent-модель '.humanize($shortName),
        str_contains($fqcn, '\\Enums\\') => 'Перечисление '.humanize($shortName),
        str_contains($fqcn, '\\Exceptions\\') => 'Исключение домена '.humanize($shortName),
        str_contains($fqcn, '\\Jobs\\') => 'Задача очереди '.humanize($shortName),
        str_contains($fqcn, '\\Listeners\\') => 'Обработчик события '.humanize($shortName),
        str_contains($fqcn, '\\Policies\\') => 'Политика доступа '.humanize($shortName),
        str_contains($fqcn, '\\Providers\\') => 'Service provider '.humanize($shortName),
        str_contains($fqcn, '\\Filament\\') => 'Компонент Filament '.humanize($shortName),
        str_contains($fqcn, '\\Console\\Commands\\') => 'Artisan-команда '.humanize($shortName),
        str_contains($fqcn, '\\Support\\') => 'Вспомогательный класс '.humanize($shortName),
        str_contains($fqcn, '\\Presenters\\') => 'Презентер '.humanize($shortName),
        str_contains($fqcn, '\\Tests\\') => 'Тест '.humanize($shortName),
        str_contains($fqcn, '\\Factories\\') => 'Фабрика модели '.humanize($shortName),
        str_contains($fqcn, '\\Seeders\\') => 'Сидер БД '.humanize($shortName),
        str_contains($fqcn, '\\Concerns\\') => 'Трейт '.humanize($shortName),
        default => 'Класс '.humanize($shortName),
    };
}

function methodDescription(string $name, string $classShortName): string
{
    return match (true) {
        $name === 'handle' && str_ends_with($classShortName, 'Command') => 'Выполняет команду',
        $name === 'rules' => 'Возвращает правила валидации',
        $name === 'authorize' => 'Проверяет право на выполнение запроса',
        $name === 'boot' => 'Регистрирует зависимости при загрузке',
        $name === 'register' => 'Регистрирует сервисы контейнера',
        $name === 'setUp' => 'Подготавливает окружение теста',
        $name === 'definition' => 'Возвращает определение фабрики',
        $name === 'run' => 'Выполняет сидер',
        str_starts_with($name, 'get') => 'Возвращает '.humanize(substr($name, 3)),
        str_starts_with($name, 'find') => 'Находит '.humanize(substr($name, 4)),
        str_starts_with($name, 'create') => 'Создаёт '.humanize(substr($name, 6)),
        str_starts_with($name, 'update') => 'Обновляет '.humanize(substr($name, 6)),
        str_starts_with($name, 'delete') => 'Удаляет '.humanize(substr($name, 6)),
        str_starts_with($name, 'is') => 'Проверяет '.humanize(substr($name, 2)),
        str_starts_with($name, 'has') => 'Проверяет наличие '.humanize(substr($name, 3)),
        str_starts_with($name, 'can') => 'Проверяет возможность '.humanize(substr($name, 3)),
        str_ends_with($name, 'Provider') => 'Поставщик данных для data provider',
        default => humanize($name),
    };
}

function humanize(string $value): string
{
    $value = preg_replace('/Contract$/', '', $value) ?? $value;
    $value = preg_replace('/(Service|Repository|Controller|Request|Exception|Data|Test)$/', '', $value) ?? $value;
    $spaced = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value) ?? $value;
    $spaced = str_replace('_', ' ', $spaced);

    return mb_strtolower(trim($spaced));
}

function inferPropertyType(string $name, ?Node $typeNode, string $fqcn): string
{
    if ($typeNode !== null) {
        return typeToString($typeNode);
    }

    return match ($name) {
        'fillable', 'hidden', 'guarded' => 'array<int, string>',
        'casts' => 'array<string, string>',
        'signature', 'description', 'view', 'slug', 'title', 'navigationLabel', 'navigationIcon', 'modelLabel', 'pluralModelLabel', 'relationship' => 'string',
        'navigationSort' => 'int',
        'model', 'resource' => str_contains($fqcn, 'Factory') ? 'class-string<Model>' : 'class-string',
        'latestReport' => 'mixed',
        'password' => 'string',
        default => 'mixed',
    };
}

function typeToString(?Node $type): string
{
    if ($type === null) {
        return 'mixed';
    }

    if ($type instanceof Identifier) {
        return $type->name === 'array' ? 'array<string, mixed>' : $type->name;
    }

    if ($type instanceof Name) {
        $name = $type->toString();

        return $name === 'array' ? 'array<string, mixed>' : $name;
    }

    if ($type instanceof NullableType) {
        $inner = typeToString($type->type);

        return str_starts_with($inner, '?') ? $inner : '?'.$inner;
    }

    if ($type instanceof UnionType) {
        return implode('|', array_map(static fn (Node $t): string => typeToString($t), $type->types));
    }

    return 'mixed';
}
