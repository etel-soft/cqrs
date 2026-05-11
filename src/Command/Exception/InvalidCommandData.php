<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Exception;

use Throwable;

/**
 * Exception for cases when command/input data passed validation, but is still invalid for any reason.
 */
interface InvalidCommandData extends Throwable {}
