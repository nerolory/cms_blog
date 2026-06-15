<?php

declare(strict_types=1);

namespace App\Tools\PhpStan\Rules;

use App\DTO\AbstractData;
use Illuminate\Support\Collection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;

/**
 * Public/protected methods in application layers must not expose raw arrays — use DTO or Collection.
 *
 * @implements Rule<InClassMethodNode>
 */
final class DisallowRawArrayRule implements Rule
{
    private const TARGET_PREFIXES = [
        'App\\Services\\',
        'App\\Repositories\\',
        'App\\Http\\Controllers\\',
        'App\\Support\\',
    ];

    private const EXCLUDED_CLASS_PREFIXES = [
        'App\\Support\\HtmlSanitizer',
        'App\\Support\\Cache\\',
        'App\\Support\\FilamentPostBody',
        'App\\Support\\TypeCast',
        'App\\Support\\Logging\\',
        'App\\Support\\PostSlugRules',
    ];

    private const ALLOWED_METHOD_NAMES = [
        'rules',
        'messages',
        'attributes',
        'toArray',
        'via',
        'casts',
    ];

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof InClassMethodNode) {
            return [];
        }

        $method = $node->getOriginalNode();

        if ($method->isPrivate()) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if ($classReflection === null || ! $this->isTargetClass($classReflection)) {
            return [];
        }

        $methodName = $method->name->toString();

        if (in_array($methodName, self::ALLOWED_METHOD_NAMES, true)) {
            return [];
        }

        if (str_starts_with($methodName, 'from') || str_ends_with($methodName, 'FormState')) {
            return [];
        }

        $function = $scope->getFunction();

        if ($function === null) {
            return [];
        }

        $errors = [];

        foreach ($function->getVariants() as $variant) {
            foreach ($variant->getParameters() as $parameter) {
                if ($this->containsRawArrayType($parameter->getType())) {
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        'Parameter $%s of %s::%s() must not use a raw array type. Use a DTO or %s.',
                        $parameter->getName(),
                        $classReflection->getName(),
                        $methodName,
                        Collection::class,
                    ))->identifier('app.noRawArray')->build();
                }
            }

            if ($this->containsRawArrayType($variant->getReturnType())) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Return type of %s::%s() must not be a raw array. Use a DTO or %s.',
                    $classReflection->getName(),
                    $methodName,
                    Collection::class,
                ))->identifier('app.noRawArray')->build();
            }
        }

        return $errors;
    }

    private function isTargetClass(ClassReflection $classReflection): bool
    {
        $name = $classReflection->getName();

        foreach (self::EXCLUDED_CLASS_PREFIXES as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return false;
            }
        }

        foreach (self::TARGET_PREFIXES as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function containsRawArrayType(Type $type): bool
    {
        $contains = false;

        TypeTraverser::map($type, function (Type $inner, callable $traverse) use (&$contains): Type {
            if ($this->isRawArrayType($inner)) {
                $contains = true;
            }

            return $traverse($inner);
        });

        return $contains;
    }

    private function isRawArrayType(Type $type): bool
    {
        if ($type instanceof MixedType) {
            return false;
        }

        $collectionType = new ObjectType(Collection::class);
        if ($collectionType->isSuperTypeOf($type)->yes()) {
            return false;
        }

        $abstractDataType = new ObjectType(AbstractData::class);
        if ($abstractDataType->isSuperTypeOf($type)->yes()) {
            return false;
        }

        foreach ($type->getObjectClassNames() as $className) {
            if (str_starts_with($className, 'App\\DTO\\')) {
                return false;
            }
        }

        return $type->isArray()->yes();
    }
}
