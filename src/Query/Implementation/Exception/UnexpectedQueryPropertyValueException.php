<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation\Exception;

use Etel\CQRS\Query\Exception\UnexpectedQueryPropertyValue;
use Etel\CQRS\Query\QueryInput;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

use function get_debug_type;
use function sprintf;

/**
 * @see UnexpectedQueryPropertyValue
 */
final class UnexpectedQueryPropertyValueException extends InvalidArgumentException implements
    UnexpectedQueryPropertyValue,
    ExceptionInterface
{
    public static function create(object $query, string $propertyName, mixed $value, ?Throwable $previous = null): self
    {
        return new self(
            message: sprintf(
                'Property "%s" with value type "%s" in query%s "%s" failed requirements.',
                $propertyName,
                get_debug_type(value: $value),
                $query instanceof QueryInput ? ' input' : '',
                $query::class
            ),
            previous: $previous
        );
    }
}
