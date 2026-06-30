<?php

declare(strict_types=1);

namespace Etel\CQRS\PHPStan;

use Etel\CQRS\Query\QueryBus;
use Etel\CQRS\Query\QueryResult;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * Infers the return type of {@see QueryBus::query()} from a {@see QueryResult} attribute on the dispatched query,
 * so callers get the concrete (optionally nullable) result type without repeating the FQCN at the call site.
 *
 * When $expectResult is given an explicit class-string the extension steps aside and lets the method's declared
 * conditional return type apply.
 */
final class QueryResultReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return QueryBus::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'query';
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

        // An explicit class-string in $expectResult is already covered by the declared conditional return type.
        if (isset($args[1]) && $scope->getType($args[1]->value)->getConstantStrings() !== []) {
            return null;
        }

        return AttributeResultType::resolve(
            messageType: $scope->getType($args[0]->value),
            attributeName: QueryResult::class
        );
    }
}
