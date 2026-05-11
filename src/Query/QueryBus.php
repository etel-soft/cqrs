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
     * Handles queries and returns result.
     *
     * The parameter $expectResult can specify the expected result behavior, where:
     * - TRUE means any possible result value (default);
     * - FQCN as class-string to specify the expected instance type of the result.
     *
     * @template T of object
     *
     * @param class-string<T>|true $expectResult
     *
     * @throws InvalidQueryData                When query/input data passed validation, but still invalid by any reason
     * @throws InvalidQueryReturnConfiguration When a query configured as asynchronous
     * @throws UnexpectedQueryPropertyValue    When query/input passed validation but property
     *                                         still has an unexpected value
     * @throws UnexpectedQueryResult           When handler returns something not matched to specified FQCN in
     *                                         $expectResult parameter
     *
     * @phpstan-return ($expectResult is class-string<T> ? T : mixed)
     */
    public function query(object $query, string|true $expectResult = true, ?QueryBusOptions $options = null): mixed;
}
