<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation\Exception;

use Etel\CQRS\Query\Exception\InvalidQueryReturnConfiguration;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

use function sprintf;

/**
 * @see InvalidQueryReturnConfiguration
 */
final class InvalidQueryReturnConfigurationException extends InvalidArgumentException implements
    InvalidQueryReturnConfiguration,
    ExceptionInterface
{
    public static function create(object $query): self
    {
        return new self(message: sprintf(
            'Query "%s" was handled zero times (asynchronous?) but bus require a result from handler.',
            $query::class
        ));
    }
}
