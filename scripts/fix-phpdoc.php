#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * УСТАРЕЛО для массового прогона: генерирует шаблонный PHPDoc низкого качества.
 * Использовать только как черновик; править вручную под CODING_STANDARDS.md.
 * Предпочтительно: писать docblock вручную + `scripts/check-phpdoc-quality.php`.
 */

use App\Tools\PhpStan\Support\PhpDocClassAnalyzer;
use App\Tools\PhpStan\Support\PhpDocRequirements;
use PhpParser\Comment\Doc;
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
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

require __DIR__.'/../vendor/autoload.php';

$projectRoot = realpath(__DIR__.'/..');

if ($projectRoot === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(2);
}

$dryRun = in_array('--dry-run', $argv, true);

if (! in_array('--force', $argv, true)) {
    fwrite(STDERR, "fix-phpdoc.php заморожен (TD-QUAL-04). Используйте scripts/remediate-require-phpdoc.php или правьте вручную.\n");
    exit(1);
}
$scanRoots = [
    $projectRoot.'/app',
    $projectRoot.'/tests',
    $projectRoot.'/database',
];

$parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 3));
$printer = new PrettyPrinter;
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

        $changed = false;
        $namespace = null;

        foreach ($ast as $stmt) {
            if ($stmt instanceof Namespace_) {
                $namespace = $stmt->name?->toString();
                $changed = fixStatements($stmt->stmts, $namespace, $path) || $changed;
            } elseif ($stmt instanceof ClassLike) {
                $fqcn = $namespace !== null ? $namespace.'\\'.$stmt->name : (string) $stmt->name;
                $changed = fixClassLike($stmt, $fqcn, $path) || $changed;
            }
        }

        if (! $changed) {
            continue;
        }

        $output = $printer->prettyPrintFile($ast);

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
 * @param  list<Node\Stmt>  $statements
 */
function fixStatements(array $statements, ?string $namespace, string $path): bool
{
    $changed = false;

    foreach ($statements as $stmt) {
        if (! $stmt instanceof ClassLike || $stmt->name === null) {
            continue;
        }

        $fqcn = $namespace !== null ? $namespace.'\\'.$stmt->name : $stmt->name->toString();
        $changed = fixClassLike($stmt, $fqcn, $path) || $changed;
    }

    return $changed;
}

function fixClassLike(ClassLike $classLike, string $fqcn, string $path): bool
{
    $appOnly = str_starts_with($fqcn, 'App\\');
    $issues = PhpDocClassAnalyzer::analyze($classLike, $fqcn, $path, $appOnly);

    if ($issues === [] && PhpDocRequirements::docblockHasDescription($classLike->getDocComment()?->getText())) {
        return false;
    }

    $changed = false;
    $shortName = classBasename($fqcn);

    if (! PhpDocRequirements::docblockHasDescription($classLike->getDocComment()?->getText())) {
        $classLike->setDocComment(new Doc(buildClassDoc($classLike, $fqcn, $shortName)));
        $changed = true;
    } else {
        $existing = $classLike->getDocComment()?->getText() ?? '';
        $promoted = collectPromotedPropertyNames($classLike);
        $append = [];

        foreach ($promoted as $propertyName) {
            if (! PhpDocRequirements::classDocDocumentsProperty($existing, $propertyName)) {
                $param = findPromotedParam($classLike, $propertyName);
                $type = $param !== null ? typeToString($param->type) : 'mixed';
                $append[] = "@property-read {$type} \${$propertyName} ".propertyDescription($propertyName);
            }
        }

        if ($append !== []) {
            $classLike->setDocComment(new Doc(mergeClassDoc($existing, $append)));
            $changed = true;
        }
    }

    foreach ($classLike->getMethods() as $method) {
        if ($method->isPrivate() || PhpDocRequirements::shouldSkipMethod($method->name->toString())) {
            continue;
        }

        if ($method->name->toString() === '__construct') {
            continue;
        }

        $doc = $method->getDocComment()?->getText();

        if (! PhpDocRequirements::docblockHasDescription($doc)) {
            $method->setDocComment(new Doc(buildMethodDoc($method, $shortName)));
            $changed = true;
        } elseif (PhpDocRequirements::methodRequiresReturnTag($method) && ! PhpDocRequirements::docblockHasTag($doc, 'return')) {
            $method->setDocComment(new Doc(mergeMethodReturn($doc, $method)));
            $changed = true;
        }
    }

    foreach ($classLike->getProperties() as $property) {
        if ($property->isPrivate() || $property->isStatic()) {
            continue;
        }

        $promoted = collectPromotedPropertyNames($classLike);
        $classDocText = $classLike->getDocComment()?->getText() ?? '';
        $propertyAppend = [];

        foreach ($property->props as $prop) {
            $name = $prop->name->toString();

            if (in_array($name, $promoted, true)) {
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
            $propertyAppend[] = '@property '.$type.' $'.$name.' '.propertyDescription($name);
        }

        if ($propertyAppend !== []) {
            $classLike->setDocComment(new Doc(mergeClassDoc($classDocText ?: '/** */', $propertyAppend)));
            $changed = true;
        }
    }

    return $changed;
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
        $lines[] = ' * @property-read '.$type.' $'.$propertyName.' '.propertyDescription($propertyName);
    }

    $lines[] = ' */';

    return implode("\n", $lines);
}

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
        $lines[] = ' * @param '.$type.' $'.$param->var->name.' '.paramDescription($param->var->name);
    }

    if (PhpDocRequirements::methodRequiresReturnTag($method)) {
        $returnType = typeToString($method->getReturnType()) ?: 'mixed';
        $lines[] = ' * @return '.$returnType.' '.returnDescription($name);
    }

    $lines[] = ' */';

    return implode("\n", $lines);
}

function mergeMethodReturn(string $existing, ClassMethod $method): string
{
    $trimmed = rtrim($existing);
    $trimmed = preg_replace('/\*\/\s*$/', '', $trimmed) ?? $trimmed;
    $returnType = typeToString($method->getReturnType()) ?: 'mixed';
    $name = $method->name->toString();

    return $trimmed."\n * @return {$returnType} ".returnDescription($name)."\n */";
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
        str_starts_with($name, 'get') => 'Возвращает '.humanize(substr($name, 3)),
        str_starts_with($name, 'find') => 'Находит '.humanize(substr($name, 4)),
        str_starts_with($name, 'create') => 'Создаёт '.humanize(substr($name, 6)),
        str_starts_with($name, 'update') => 'Обновляет '.humanize(substr($name, 6)),
        str_starts_with($name, 'delete') => 'Удаляет '.humanize(substr($name, 6)),
        str_starts_with($name, 'is') => 'Проверяет '.humanize(substr($name, 2)),
        str_starts_with($name, 'has') => 'Проверяет наличие '.humanize(substr($name, 3)),
        str_starts_with($name, 'can') => 'Проверяет возможность '.humanize(substr($name, 3)),
        default => humanize($name),
    };
}

function paramDescription(string $name): string
{
    return 'Значение '.humanize($name);
}

function propertyDescription(string $name): string
{
    return humanize($name);
}

function returnDescription(string $methodName): string
{
    return match (true) {
        str_starts_with($methodName, 'get'), str_starts_with($methodName, 'find') => 'Найденное значение',
        str_starts_with($methodName, 'is'), str_starts_with($methodName, 'has'), str_starts_with($methodName, 'can') => 'Результат проверки',
        default => 'Результат выполнения',
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
