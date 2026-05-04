<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation\Exception;

use Etel\CQRS\Command\CommandInput;
use Etel\CQRS\Command\Exception\UnexpectedCommandResult;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

use function gettype;
use function is_object;
use function sprintf;

/**
 * @see UnexpectedCommandResult
 */
final class UnexpectedCommandResultException extends InvalidArgumentException implements
    UnexpectedCommandResult,
    ExceptionInterface
{
    /**
     * @param class-string $expectedType
     */
    public static function create(object $command, string $expectedType, mixed $result): self
    {
        return new self(message: sprintf(
            'Result type "%s" not matched expected type "%s" in command%s "%s".',
            is_object(value: $result) ? $result::class : gettype(value: $result),
            $expectedType,
            $command instanceof CommandInput ? ' input' : '',
            $command::class
        ));
    }
}
