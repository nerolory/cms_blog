<?php

declare(strict_types=1);

namespace App\Tools\PhpStan\Rules;

use App\Tools\PhpStan\Support\PhpDocClassAnalyzer;
use App\Tools\PhpStan\Support\PhpDocRequirements;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Enforces mandatory PHPDoc on application classes in app/.
 *
 * @implements Rule<ClassLike>
 */
final class RequirePhpDocRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof ClassLike) {
            return [];
        }

        if ($node->name === null) {
            return [];
        }

        $file = $scope->getFile();

        if (PhpDocRequirements::shouldSkipPath($file)) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        $fqcn = $classReflection?->getName() ?? $scope->getNamespace().'\\'.$node->name->toString();

        if (! PhpDocRequirements::shouldAnalyzeAppClass($fqcn, $file)) {
            return [];
        }

        $issues = PhpDocClassAnalyzer::analyze($node, $fqcn, $file, appOnly: true);

        return array_map(
            static fn (array $issue): RuleError => RuleErrorBuilder::message($issue['message'])
                ->line($issue['line'])
                ->identifier('app.requirePhpDoc')
                ->build(),
            $issues,
        );
    }
}
