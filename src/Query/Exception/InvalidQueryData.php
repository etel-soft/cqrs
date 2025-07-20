<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Exception;

use Throwable;

/**
 * Exception for cases when query/input data passed validation, but still invalid by any reason (some validation
 * is missing or not properly configured).
 */
interface InvalidQueryData extends Throwable {}
