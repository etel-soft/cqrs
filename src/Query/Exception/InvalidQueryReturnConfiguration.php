<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Exception;

use Throwable;

/**
 * Exception for cases when a query acts as asynchronous, but query can not be asynchronous.
 */
interface InvalidQueryReturnConfiguration extends Throwable {}
