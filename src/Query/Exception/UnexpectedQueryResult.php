<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Exception;

use Throwable;

/**
 * Exception for cases when query handler returns an unexpected result.
 */
interface UnexpectedQueryResult extends Throwable {}
