<?php

declare(strict_types=1);

namespace Etel\CQRS\Command;

use Attribute;

/**
 * Declares the result a command handler is expected to return.
 *
 * Placed on the dispatched command (or command input) class, this attribute opts
 * the command out of fire-and-forget: the command bus will read the handler result
 * synchronously, verify it at runtime and let static analysis infer the return type
 * of {@see CommandBus::command()} without repeating the FQCN at every call site:
 *
 * ```php
 * #[CommandResult(CreatedUser::class)]            // handler must return a CreatedUser
 * #[CommandResult(CreatedUser::class, nullable: true)] // ...or null
 * final readonly class CreateUser { ... }
 * ```
 *
 * A null $type means "any result" — a result is still expected, but no type is
 * enforced (e.g. a newly created entity id as a scalar). A command without this
 * attribute stays fire-and-forget unless $expectResult is passed explicitly.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class CommandResult
{
    /**
     * @param null|class-string $type     Expected instance type of the result, or null for any
     * @param bool              $nullable Whether the handler is allowed to return null
     */
    public function __construct(
        public ?string $type = null,
        public bool $nullable = false
    ) {}
}
