<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Exception;

use Throwable;

/**
 * Exception for cases when command cannot be handled immediately (i.e., asynchronous), but the bus requires return
 * value ($expectResult parameter specifies a result (i.e., not FALSE)).
 */
interface InvalidCommandReturnConfiguration extends Throwable {}
