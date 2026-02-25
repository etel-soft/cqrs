<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Exception;

use Throwable;

/**
 * Exception for cases when a query cannot be handled immediately (i.e., asynchronous), but the bus requires return
 * value ($return parameter specify a result (i.e., not FALSE)).
 */
interface InvalidQueryReturnConfiguration extends Throwable {}
