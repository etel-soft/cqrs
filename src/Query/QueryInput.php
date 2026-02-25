<?php

declare(strict_types=1);

namespace Etel\CQRS\Query;

use Etel\CQRS\Query\Exception\InvalidQueryData;
use Etel\CQRS\Query\Exception\UnexpectedQueryPropertyValue;

/**
 * Represents raw data before validation.
 *
 * In concepts where a query must contain valid data in a specific format, there must be an intermediate DTO
 * to store raw data for later validation and creation of a query if the data is valid.
 * This functionality is implemented by QueryInput.
 *
 * The main feature of this concept is a strict contract. Query, which comes to the handler, must be valid.
 * This means that all non-nullable properties must be non-nullable by contract,
 * and all enums or VOs must be presented as enums/VOs respectively, not as strings/ints/etc.
 *
 * After the successful validation, QueryInput will transform into a query by the middleware using toQuery() method.
 *
 * Of course, this step can be omitted when the source data is already validated and the query can be filled directly.
 */
interface QueryInput
{
    /**
     * Returns a filled query or raises exception when input data is not valid.
     *
     * @throws InvalidQueryData             When input data invalid by any reason
     * @throws UnexpectedQueryPropertyValue When input property has an unexpected value
     */
    public function toQuery(): object;
}
