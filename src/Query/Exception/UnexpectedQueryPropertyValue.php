<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Exception;

/**
 * Exception for cases when query/input passed validation, but property still has an unexpected value.
 */
interface UnexpectedQueryPropertyValue extends InvalidQueryData {}
