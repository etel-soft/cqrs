<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation\Exception;

use Etel\CQRS\Query\Exception\UnexpectedQueryPropertyValue;
use Etel\CQRS\Query\QueryInput;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Throwable;

use function gettype;
use function is_object;
use function sprintf;

/**
 * @see UnexpectedQueryPropertyValue
 */
final class UnexpectedQueryPropertyValueException extends InvalidArgumentException implements
    UnexpectedQueryPropertyValue,
    ExceptionInterface
{
    public static function create(object $query, string $propertyPath, ?Throwable $previous = null): self
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        try {
            $value = $propertyAccessor->getValue(objectOrArray: $query, propertyPath: $propertyPath);
        } catch (UnexpectedTypeException $exception) {
            return new self(
                message: sprintf(
                    'Property path "%s" in query%s "%s" failed requirements.',
                    $propertyPath,
                    $query instanceof QueryInput ? ' input' : '',
                    $query::class
                ),
                previous: $exception
            );
        }

        return new self(
            message: sprintf(
                'Property by path "%s" with value type "%s" in query%s "%s" failed requirements.',
                $propertyPath,
                is_object(value: $value) ? $value::class : gettype(value: $value),
                $query instanceof QueryInput ? ' input' : '',
                $query::class
            ),
            previous: $previous
        );
    }
}
