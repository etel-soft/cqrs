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
     * The parameter $return can specify the return behavior, where:
     * - TRUE means any possible return value (default);
     * - FALSE means no value will be returned;
     * - FQCN as class-string for the instance of the returned value.
     *
     * It is worth noting that in the case of asynchronous query execution, if the $return parameter contains any value
     * other than FALSE, an InvalidQueryReturnConfiguration will be thrown.
     *
     * @template T of object
     *
     * @param bool|class-string<T> $return
     *
     * @throws InvalidQueryData                When query/input data passed validation, but still invalid by any reason
     * @throws InvalidQueryReturnConfiguration When a query cannot be handled immediately (i.e., asynchronous),
     *                                         but $return parameter specify a result (i.e., not FALSE)
     * @throws UnexpectedQueryPropertyValue    When query/input passed validation but property
     *                                         still has an unexpected value
     * @throws UnexpectedQueryResult           When handler returns something not matched to specified FQCN in $return
     *                                         parameter
     *
     * @return null|mixed|T The handler returned value
     */
    public function query(object $query, bool|string $return = true): mixed;
}
