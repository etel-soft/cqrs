<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation\Exception;

use Etel\CQRS\Query\Exception\InvalidQueryData;
use Etel\CQRS\Query\QueryInput;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Throwable;

use function sprintf;

/**
 * @see InvalidQueryData
 */
final class InvalidQueryDataException extends InvalidArgumentException implements
    InvalidQueryData,
    ExceptionInterface
{
    public static function create(object $query, string $message, ?Throwable $previous = null): self
    {
        return new self(
            message: sprintf(
                'Query%s "%s" has invalid data (message: %s).',
                $query instanceof QueryInput ? ' input' : '',
                $query::class,
                $message
            ),
            previous: $previous
        );
    }
}
