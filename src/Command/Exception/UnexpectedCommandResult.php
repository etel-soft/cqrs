<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Exception;

use Throwable;

/**
 * Exception for cases when command handler returns an unexpected result.
 */
interface UnexpectedCommandResult extends Throwable {}
