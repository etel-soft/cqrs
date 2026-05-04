<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation\Exception;

use Etel\CQRS\Command\CommandInput;
use Etel\CQRS\Command\Exception\UnexpectedCommandPropertyValue;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Throwable;

use function gettype;
use function is_object;
use function sprintf;

/**
 * @see UnexpectedCommandPropertyValue
 */
final class UnexpectedCommandPropertyValueException extends InvalidArgumentException implements
    UnexpectedCommandPropertyValue,
    ExceptionInterface
{
    public static function create(object $command, string $propertyPath, ?Throwable $previous = null): self
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        try {
            $value = $propertyAccessor->getValue(objectOrArray: $command, propertyPath: $propertyPath);
        } catch (UnexpectedTypeException $exception) {
            return new self(
                message: sprintf(
                    'Property path "%s" in command%s "%s" failed requirements.',
                    $propertyPath,
                    $command instanceof CommandInput ? ' input' : '',
                    $command::class
                ),
                previous: $exception
            );
        }

        return new self(
            message: sprintf(
                'Property by path "%s" with value type "%s" in command%s "%s" failed requirements.',
                $propertyPath,
                is_object(value: $value) ? $value::class : gettype(value: $value),
                $command instanceof CommandInput ? ' input' : '',
                $command::class
            ),
            previous: $previous
        );
    }
}
