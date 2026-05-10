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
     * Handles commands and (for synchronous commands) returns result.
     *
     * For performance & RAD purposes, command bus can return values.
     * The parameter $expectResult can specify the expected result behavior, where:
     *  - FALSE means no result is expected (default);
     *  - TRUE means any possible result value;
     *  - FQCN as class-string to specify the expected instance type of the result.
     *
     * It is worth noting that in the case of asynchronous command execution, if the $expectResult parameter contains
     * any value other than FALSE, an InvalidCommandReturnConfiguration will be thrown.
     *
     * @template T of object
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
    public function command(object $command, bool|string $expectResult = false): mixed;
}
