<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Exception;

/**
 * Exception for cases when command/input passed validation, but property still has an unexpected value.
 */
interface UnexpectedCommandPropertyValue extends InvalidCommandData {}
