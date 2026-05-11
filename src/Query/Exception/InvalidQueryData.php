<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Exception;

use Throwable;

/**
 * Exception for cases when query/input data passed validation, but is still invalid for any reason.
 */
interface InvalidQueryData extends Throwable {}
