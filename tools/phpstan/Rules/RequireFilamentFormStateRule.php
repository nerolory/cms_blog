<?php

declare(strict_types=1);

namespace App\Tools\PhpStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * PostData::fromFilament() must only be called from DTO layer (use FilamentPostFormState at UI boundary).
 *
 * @implements Rule<StaticCall>
 */
final class RequireFilamentFormStateRule implements Rule
{
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof StaticCall) {
            return [];
        }

        if (! $node->class instanceof Node\Name) {
            return [];
        }

        if (! $node->name instanceof Node\Identifier) {
            return [];
        }

        if ($node->name->name !== 'fromFilament') {
            return [];
        }

        $class = $node->class->toString();

        if (! in_array($class, ['PostData', 'SeoData', 'App\\DTO\\PostData', 'App\\DTO\\SeoData'], true)) {
            return [];
        }

        $file = $scope->getFile();

        if (str_contains($file, DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'DTO'.DIRECTORY_SEPARATOR)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '%s::fromFilament() must not be called outside DTO layer. Use FilamentPostFormState::fromArray() at Filament boundary.',
                $class,
            ))->identifier('app.filamentFormState')->build(),
        ];
    }
}
