<?php

declare(strict_types=1);

namespace Etel\CQRS\PHPStan;

use Etel\CQRS\Command\CommandBus;
use Etel\CQRS\Command\CommandResult;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * Infers the return type of {@see CommandBus::command()} from a {@see CommandResult} attribute on the dispatched
 * command, so callers get the concrete (optionally nullable) result type without repeating the FQCN at the call site.
 *
 * When $expectResult is given explicitly (a class-string, or true for "any result") the extension steps aside and
 * lets the method's declared conditional return type apply.
 */
final class CommandResultReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return CommandBus::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'command';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        $args = $methodCall->getArgs();

        if ($args === []) {
            return null;
        }

        if (isset($args[1])) {
            $expectResultType = $scope->getType($args[1]->value);

            // An explicit class-string, or true ("any result"), is already covered by the declared return type;
            // only the default/false case is augmented by the attribute.
            if ($expectResultType->getConstantStrings() !== [] || $expectResultType->isTrue()->yes()) {
                return null;
            }
        }

        return AttributeResultType::resolve(
            messageType: $scope->getType($args[0]->value),
            attributeName: CommandResult::class
        );
    }
}
