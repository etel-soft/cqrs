<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation\Exception;

use Etel\CQRS\Command\CommandInput;
use Etel\CQRS\Command\Exception\UnexpectedCommandPropertyValue;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

use function get_debug_type;
use function sprintf;

/**
 * @see UnexpectedCommandPropertyValue
 */
final class UnexpectedCommandPropertyValueException extends InvalidArgumentException implements
    UnexpectedCommandPropertyValue,
    ExceptionInterface
{
    public static function create(
        object $command,
        string $propertyName,
        mixed $value,
        ?Throwable $previous = null
    ): self {
        return new self(
            message: sprintf(
                'Property "%s" with value type "%s" in command%s "%s" failed requirements.',
                $propertyName,
                get_debug_type(value: $value),
                $command instanceof CommandInput ? ' input' : '',
                $command::class
            ),
            previous: $previous
        );
    }
}
