<?php

declare(strict_types=1);

namespace App\Tools\PhpStan\Support;

use PhpParser\Modifiers;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

/**
 * Inspects a single class-like AST node against mandatory PHPDoc rules.
 */
final class PhpDocClassAnalyzer
{
    /**
     * @return list<array{message: string, line: int}>
     */
    public static function analyze(
        ClassLike $classLike,
        ?string $fqcn,
        string $filePath,
        bool $appOnly,
    ): array {
        $shouldAnalyze = $appOnly
            ? PhpDocRequirements::shouldAnalyzeAppClass($fqcn, $filePath)
            : PhpDocRequirements::shouldAnalyzeScannedClass($fqcn, $filePath);

        if (! $shouldAnalyze || self::isFilamentBoilerplatePage($classLike, $fqcn)) {
            return [];
        }

        $issues = [];
        $classDoc = $classLike->getDocComment()?->getText();
        $classLine = $classLike->getStartLine();

        if (! PhpDocRequirements::docblockHasDescription($classDoc)) {
            $issues[] = [
                'message' => sprintf(
                    'Class %s must have a class-level PHPDoc block with a Russian description.',
                    $fqcn ?? '(anonymous)',
                ),
                'line' => $classLine,
            ];
        }

        $promotedPropertyNames = self::collectPromotedPropertyNames($classLike);

        foreach ($promotedPropertyNames as $propertyName) {
            if (PhpDocRequirements::shouldSkipPropertyName($propertyName)) {
                continue;
            }

            $param = self::findPromotedParam($classLike, $propertyName);

            if ($param !== null && $param->type !== null) {
                continue;
            }

            if (! PhpDocRequirements::classDocDocumentsProperty($classDoc, $propertyName)) {
                $issues[] = [
                    'message' => sprintf(
                        'Constructor-promoted property $%s in %s must be documented via @param on __construct() or class PHPDoc.',
                        $propertyName,
                        $fqcn ?? '(anonymous)',
                    ),
                    'line' => $classLine,
                ];
            }
        }

        foreach ($classLike->getMethods() as $method) {
            $issues = array_merge($issues, self::analyzeMethod($method, $fqcn));
        }

        foreach ($classLike->getProperties() as $property) {
            $issues = array_merge(
                $issues,
                self::analyzeProperty($property, $fqcn, $classDoc, $promotedPropertyNames),
            );
        }

        return $issues;
    }

    /**
     * @return list<string>
     */
    private static function collectPromotedPropertyNames(ClassLike $classLike): array
    {
        $constructor = $classLike->getMethod('__construct');

        if ($constructor === null) {
            return [];
        }

        $names = [];

        foreach ($constructor->params as $param) {
            if (! $param instanceof Param) {
                continue;
            }

            if (! self::isPromotedParam($param) || $param->isPrivate()) {
                continue;
            }

            if (! $param->var instanceof Variable || ! is_string($param->var->name)) {
                continue;
            }

            $names[] = $param->var->name;
        }

        return $names;
    }

    private static function findPromotedParam(ClassLike $classLike, string $propertyName): ?Param
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

    private static function isPromotedParam(Param $param): bool
    {
        return ($param->flags & Modifiers::PUBLIC) !== 0
            || ($param->flags & Modifiers::PROTECTED) !== 0
            || ($param->flags & Modifiers::PRIVATE) !== 0;
    }

    /**
     * @return list<array{message: string, line: int}>
     */
    private static function analyzeMethod(ClassMethod $method, ?string $fqcn): array
    {
        if ($method->isPrivate()) {
            return [];
        }

        $methodName = $method->name->toString();

        if (PhpDocRequirements::shouldSkipMethod($methodName)) {
            return [];
        }

        if ($methodName === '__construct') {
            return [];
        }

        $docComment = $method->getDocComment()?->getText();
        $issues = [];

        if (! PhpDocRequirements::docblockHasDescription($docComment)) {
            $issues[] = [
                'message' => sprintf(
                    'Public/protected method %s::%s() must have a PHPDoc block with a Russian description.',
                    $fqcn ?? '(anonymous)',
                    $methodName,
                ),
                'line' => $method->getStartLine(),
            ];
        }

        if (
            PhpDocRequirements::methodRequiresReturnTag($method)
            && ! PhpDocRequirements::isInheritDoc($docComment)
            && ! PhpDocRequirements::docblockHasTag($docComment, 'return')
        ) {
            $issues[] = [
                'message' => sprintf(
                    'Method %s::%s() must declare @return in PHPDoc when the native return type is not void.',
                    $fqcn ?? '(anonymous)',
                    $methodName,
                ),
                'line' => $method->getStartLine(),
            ];
        }

        return $issues;
    }

    /**
     * @param  list<string>  $promotedPropertyNames
     * @return list<array{message: string, line: int}>
     */
    private static function analyzeProperty(
        Property $property,
        ?string $fqcn,
        ?string $classDoc,
        array $promotedPropertyNames,
    ): array {
        if ($property->isPrivate() || $property->isStatic()) {
            return [];
        }

        $propertyDoc = $property->getDocComment()?->getText();
        $issues = [];

        foreach ($property->props as $prop) {
            $propertyName = $prop->name->toString();

            if (in_array($propertyName, $promotedPropertyNames, true)) {
                continue;
            }

            if (PhpDocRequirements::shouldSkipPropertyName($propertyName)) {
                continue;
            }

            if (
                PhpDocRequirements::docblockHasTag($propertyDoc, 'var')
                || PhpDocRequirements::classDocDocumentsProperty($classDoc, $propertyName)
            ) {
                continue;
            }

            $issues[] = [
                'message' => sprintf(
                    'Public/protected property $%s in %s must have @var in the property or class PHPDoc.',
                    $propertyName,
                    $fqcn ?? '(anonymous)',
                ),
                'line' => $property->getStartLine(),
            ];
        }

        return $issues;
    }

    private static function isFilamentBoilerplatePage(ClassLike $classLike, ?string $fqcn): bool
    {
        if ($fqcn === null || ! preg_match('#^App\\\\Filament\\\\Resources\\\\.+\\\\Pages\\\\#', $fqcn)) {
            return false;
        }

        foreach ($classLike->getMethods() as $method) {
            if ($method->isPrivate()) {
                continue;
            }

            if ($method->name->toString() === '__construct') {
                continue;
            }

            return false;
        }

        foreach ($classLike->getProperties() as $property) {
            foreach ($property->props as $prop) {
                if ($prop->name->toString() !== 'resource') {
                    return false;
                }
            }
        }

        return true;
    }
}
