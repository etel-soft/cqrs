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
     * The parameter $return can specify the return behavior, where:
     *  - FALSE means no value will be returned (default);
     *  - TRUE means any possible return value;
     *  - FQCN as class-string for specify the instance of the returned value.
     *
     * It is worth noting that in the case of asynchronous command execution, if the $return parameter contains
     * any value other than FALSE, an InvalidCommandReturnConfiguration will be thrown.
     *
     * @template T of object
     *
     * @param bool|class-string<T> $return
     *
     * @throws InvalidCommandData                When command/input data passed validation, but still invalid
     *                                           by any reason
     * @throws InvalidCommandReturnConfiguration When command cannot be handled immediately (i.e., asynchronous),
     *                                           but $return parameter specify a result (i.e., not FALSE)
     * @throws UnexpectedCommandPropertyValue    When command/input passed validation but property
     *                                           still has an unexpected value
     * @throws UnexpectedCommandResult           When handler returns something not matched to specified FQCN in $return
     *                                           parameter
     *
     * @return null|mixed|T The handler returned value
     */
    public function command(object $command, bool|string $return = false): mixed;
}
