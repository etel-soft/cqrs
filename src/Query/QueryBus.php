<?php

declare(strict_types=1);

namespace Etel\CQRS\Query;

use Etel\CQRS\Query\Exception\InvalidQueryData;
use Etel\CQRS\Query\Exception\InvalidQueryReturnConfiguration;
use Etel\CQRS\Query\Exception\UnexpectedQueryPropertyValue;
use Etel\CQRS\Query\Exception\UnexpectedQueryResult;

/**
 * Interface for query bus.
 */
interface QueryBus
{
    /**
     * Handles queries and (for synchronous queries) returns result.
     *
     * The parameter $expectResult can specify the expected result behavior, where:
     * - TRUE means any possible result value (default);
     * - FALSE means no result is expected;
     * - FQCN as class-string to specify the expected instance type of the result.
     *
     * It is worth noting that in the case of asynchronous query execution, if the $expectResult parameter contains
     * any value other than FALSE, an InvalidQueryReturnConfiguration will be thrown.
     *
     * @template T of object
     *
     * @param bool|class-string<T> $expectResult
     *
     * @throws InvalidQueryData                When query/input data passed validation, but still invalid by any reason
     * @throws InvalidQueryReturnConfiguration When a query cannot be handled immediately (i.e., asynchronous),
     *                                         but $expectResult parameter specifies a result (i.e., not FALSE)
     * @throws UnexpectedQueryPropertyValue    When query/input passed validation but property
     *                                         still has an unexpected value
     * @throws UnexpectedQueryResult           When handler returns something not matched to specified FQCN in
     *                                         $expectResult parameter
     *
     * @phpstan-return ($expectResult is false ? null : ($expectResult is class-string<T> ? T : mixed))
     */
    public function query(object $query, bool|string $expectResult = true): mixed;
}
