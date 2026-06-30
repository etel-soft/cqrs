<?php

declare(strict_types=1);

namespace Etel\CQRS\PHPStan;

use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

use function is_string;

/**
 * Resolves the result type declared by a QueryResult/CommandResult attribute on a dispatched message type.
 *
 * @internal shared by {@see QueryResultReturnTypeExtension} and {@see CommandResultReturnTypeExtension}
 */
final class AttributeResultType
{
    /**
     * @param class-string $attributeName
     *
     * @return null|Type The declared (optionally nullable) result type; a {@see MixedType} when the attribute is
     *                   present but declares no concrete type; or null when the message carries no such attribute
     *                   (in which case the method's declared return type should apply)
     */
    public static function resolve(Type $messageType, string $attributeName): ?Type
    {
        foreach ($messageType->getObjectClassReflections() as $classReflection) {
            $attributes = $classReflection->getNativeReflection()->getAttributes($attributeName);

            if ($attributes === []) {
                continue;
            }

            $arguments = $attributes[0]->getArguments();
            $type = $arguments['type'] ?? $arguments[0] ?? null;
            $nullable = $arguments['nullable'] ?? $arguments[1] ?? false;

            // Attribute present but no concrete class — a result is expected, of an unconstrained type.
            if (!is_string($type)) {
                return new MixedType();
            }

            $objectType = new ObjectType($type);

            return $nullable === true ? TypeCombinator::addNull($objectType) : $objectType;
        }

        return null;
    }
}
