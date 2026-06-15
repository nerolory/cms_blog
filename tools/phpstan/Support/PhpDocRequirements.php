<?php

declare(strict_types=1);

namespace App\Tools\PhpStan\Support;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;

/**
 * Shared PHPDoc requirement helpers for PHPStan rule and CLI scanner.
 */
final class PhpDocRequirements
{
    private const SKIP_PATH_SEGMENTS = [
        '/database/migrations/',
        '\\database\\migrations\\',
        '/vendor/',
        '\\vendor\\',
    ];

    private const SKIP_PROPERTY_NAMES = [
        'fillable',
        'guarded',
        'hidden',
        'casts',
        'table',
        'primaryKey',
        'connection',
        'signature',
        'description',
        'model',
        'resource',
        'navigationIcon',
        'navigationLabel',
        'navigationSort',
        'title',
        'slug',
        'view',
        'relationship',
        'modelLabel',
        'pluralModelLabel',
        'latestReport',
        'password',
        'timestamps',
        'incrementing',
        'keyType',
    ];

    private const SKIP_MAGIC_METHODS = [
        '__destruct',
        '__call',
        '__callStatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__serialize',
        '__unserialize',
        '__clone',
        '__debugInfo',
    ];

    public static function shouldSkipPath(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);

        foreach (self::SKIP_PATH_SEGMENTS as $segment) {
            if (str_contains($normalized, str_replace('\\', '/', $segment))) {
                return true;
            }
        }

        return false;
    }

    public static function shouldAnalyzeAppClass(?string $fqcn, string $path): bool
    {
        if ($fqcn === null || $fqcn === '') {
            return false;
        }

        if (! str_starts_with($fqcn, 'App\\')) {
            return false;
        }

        if (str_starts_with($fqcn, 'App\\Tools\\PhpStan\\')) {
            return false;
        }

        if (self::shouldSkipPath($path)) {
            return false;
        }

        return true;
    }

    public static function shouldAnalyzeScannedClass(?string $fqcn, string $path): bool
    {
        if ($fqcn === null || $fqcn === '') {
            return false;
        }

        if (self::shouldSkipPath($path)) {
            return false;
        }

        $namespace = explode('\\', $fqcn)[0] ?? '';

        return in_array($namespace, ['App', 'Tests', 'Database'], true);
    }

    public static function docblockHasDescription(?string $docComment): bool
    {
        if ($docComment === null) {
            return false;
        }

        if (self::isInheritDoc($docComment)) {
            return true;
        }

        return self::extractDescription($docComment) !== '';
    }

    public static function isInheritDoc(?string $docComment): bool
    {
        if ($docComment === null) {
            return false;
        }

        return (bool) preg_match('/\{@inheritdoc\}|@inheritdoc\b/i', $docComment);
    }

    public static function extractDescription(string $docComment): string
    {
        $content = preg_replace('/^\/\*\*|\*\/$/s', '', $docComment) ?? '';
        $lines = [];

        foreach (explode("\n", $content) as $line) {
            $line = preg_replace('/^\s*\*\s?/', '', $line) ?? $line;
            $trimmed = trim($line);

            if ($trimmed !== '' && str_starts_with($trimmed, '@')) {
                break;
            }

            if ($trimmed !== '') {
                $lines[] = $trimmed;
            }
        }

        return trim(implode(' ', $lines));
    }

    public static function docblockHasTag(?string $docComment, string $tagName): bool
    {
        if ($docComment === null) {
            return false;
        }

        return (bool) preg_match('/@'.preg_quote($tagName, '/').'\b/', $docComment);
    }

    public static function classDocDocumentsProperty(?string $classDoc, string $propertyName): bool
    {
        if ($classDoc === null) {
            return false;
        }

        $pattern = '/@(?:var|property|property-read|property-write)\s+.+?\s+\$'
            .preg_quote($propertyName, '/')
            .'\b/s';

        return (bool) preg_match($pattern, $classDoc);
    }

    public static function methodRequiresReturnTag(Node\Stmt\ClassMethod $method): bool
    {
        $returnType = $method->getReturnType();

        if ($returnType === null) {
            return true;
        }

        if ($returnType instanceof Identifier) {
            return strtolower($returnType->name) !== 'void';
        }

        if ($returnType instanceof Name) {
            return strtolower($returnType->toString()) !== 'void';
        }

        if ($returnType instanceof NullableType) {
            return self::typeNodeIsVoid($returnType->type) === false;
        }

        if ($returnType instanceof UnionType) {
            foreach ($returnType->types as $type) {
                if (self::typeNodeIsVoid($type)) {
                    return false;
                }
            }

            return true;
        }

        return true;
    }

    public static function shouldSkipPropertyName(string $propertyName): bool
    {
        return in_array($propertyName, self::SKIP_PROPERTY_NAMES, true);
    }

    public static function shouldSkipMethod(string $methodName): bool
    {
        return in_array($methodName, self::SKIP_MAGIC_METHODS, true);
    }

    private static function typeNodeIsVoid(Node $type): bool
    {
        if ($type instanceof Identifier) {
            return strtolower($type->name) === 'void';
        }

        if ($type instanceof Name) {
            return strtolower($type->toString()) === 'void';
        }

        return false;
    }
}
