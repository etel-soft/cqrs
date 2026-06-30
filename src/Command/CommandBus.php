<?php

declare(strict_types=1);

namespace Etel\CQRS\Command;

use Etel\CQRS\Command\Exception\InvalidCommandData;
use Etel\CQRS\Command\Exception\InvalidCommandReturnConfiguration;
use Etel\CQRS\Command\Exception\UnexpectedCommandPropertyValue;
use Etel\CQRS\Command\Exception\UnexpectedCommandResult;

/**
 * Interface for command bus.
 */
interface CommandBus
{
    /**
     * Dispatches a command and optionally returns the handler result.
     *
     * By default, ($expectResult = false) the result is discarded — use this
     * for fire-and-forget or asynchronous commands. Pass true or a FQCN
     * when you need the result synchronously (e.g. a newly created entity's ID).
     * A {@see CommandResult} attribute on the command opts it into a synchronous,
     * type-checked result without passing $expectResult at the call site.
     *
     * @template T of object = object
     *
     * @param bool|class-string<T> $expectResult
     *
     * @throws InvalidCommandData                When command/input data passed validation, but still invalid
     *                                           by any reason
     * @throws InvalidCommandReturnConfiguration When command cannot be handled immediately (i.e., asynchronous),
     *                                           but $expectResult parameter specifies a result (i.e., not FALSE)
     * @throws UnexpectedCommandPropertyValue    When command/input passed validation but property
     *                                           still has an unexpected value
     * @throws UnexpectedCommandResult           When handler returns something not matched to specified FQCN in
     *                                           $expectResult parameter
     *
     * @phpstan-return ($expectResult is false ? null : ($expectResult is class-string<T> ? T : mixed))
     */
    public function command(
        object $command,
        bool|string $expectResult = false,
        ?CommandBusOptions $options = null
    ): mixed;
}
