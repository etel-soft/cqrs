<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation\Exception;

use Etel\CQRS\Query\Exception\UnexpectedQueryResult;
use Etel\CQRS\Query\QueryInput;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

use function gettype;
use function is_object;
use function sprintf;

/**
 * @see UnexpectedQueryResult
 */
final class UnexpectedQueryResultException extends InvalidArgumentException implements
    UnexpectedQueryResult,
    ExceptionInterface
{
    /**
     * @param class-string $expectedType
     */
    public static function create(object $query, string $expectedType, mixed $result): self
    {
        return new self(message: sprintf(
            'Result type "%s" not matched expected type "%s" in query%s "%s".',
            is_object(value: $result) ? $result::class : gettype(value: $result),
            $expectedType,
            $query instanceof QueryInput ? ' input' : '',
            $query::class
        ));
    }
}
