<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation\Exception;

use Etel\CQRS\Command\CommandInput;
use Etel\CQRS\Command\Exception\InvalidCommandData;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

use function sprintf;

/**
 * @see InvalidCommandData
 */
final class InvalidCommandDataException extends InvalidArgumentException implements
    InvalidCommandData,
    ExceptionInterface
{
    public static function create(object $command, string $message, ?Throwable $previous = null): self
    {
        return new self(
            message: sprintf(
                'Command%s "%s" has invalid data (message: %s).',
                $command instanceof CommandInput ? ' input' : '',
                $command::class,
                $message
            ),
            previous: $previous
        );
    }
}
