<?php

declare(strict_types=1);

namespace Etel\CQRS\Query;

use Attribute;

/**
 * Declares the result a query handler is expected to return.
 *
 * Placed on the dispatched query (or query input) class, this attribute lets the
 * query bus verify the handler result at runtime and lets static analysis infer
 * the return type of {@see QueryBus::query()} without repeating the FQCN at every
 * call site:
 *
 * ```php
 * #[QueryResult(UserView::class)]            // handler must return a UserView
 * #[QueryResult(UserView::class, nullable: true)] // ...or null
 * final readonly class GetUserById { ... }
 * ```
 *
 * A null $type means "any result" — no type is enforced (e.g. a scalar or array).
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class QueryResult
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
