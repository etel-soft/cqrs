<?php

declare(strict_types=1);

namespace Etel\CQRS\Command;

use Etel\CQRS\Command\Exception\InvalidCommandData;
use Etel\CQRS\Command\Exception\UnexpectedCommandPropertyValue;

/**
 * Represents raw data before validation.
 *
 * In concepts where a command must contain valid data in a specific format, there must be an intermediate DTO
 * to store raw data for later validation and creation of a command if the data is valid.
 * This functionality is implemented by CommandInput.
 *
 * The main feature of this concept is a strict contract. Command, which comes to the handler, must be valid.
 * This means that all non-nullable properties must be non-nullable by contract,
 * and all enums or VOs must be presented as enums/VOs respectively, not as strings/ints/etc.
 *
 * After the successful validation, CommandInput will transform into a command by the middleware using
 * toCommand() method.
 *
 * Of course, this step can be omitted when the source data is already validated and the command can be filled directly.
 */
interface CommandInput
{
    /**
     * Returns a filled command or raises exception when input data is not valid.
     *
     * @throws InvalidCommandData             When input data invalid by any reason
     * @throws UnexpectedCommandPropertyValue When input property has an unexpected value
     */
    public function toCommand(): object;
}
