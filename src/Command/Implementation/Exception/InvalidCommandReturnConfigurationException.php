<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation\Exception;

use Etel\CQRS\Command\Exception\InvalidCommandReturnConfiguration;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

use function sprintf;

/**
 * @see InvalidCommandReturnConfiguration
 */
final class InvalidCommandReturnConfigurationException extends InvalidArgumentException implements
    InvalidCommandReturnConfiguration,
    ExceptionInterface
{
    public static function create(object $command): self
    {
        return new self(message: sprintf(
            'Command "%s" was handled zero times (asynchronous?) but bus require a result from handler.',
            $command::class
        ));
    }
}
