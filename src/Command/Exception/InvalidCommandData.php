<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Exception;

use Throwable;

/**
 * Exception for cases when command/input data passed validation, but still invalid by any reason (some validation
 * is missing or not properly configured, or since the last validation, the data has lost its uniqueness).
 */
interface InvalidCommandData extends Throwable {}
