<?php

declare(strict_types=1);

namespace Etel\CQRS\Query;

final readonly class QueryBusOptions
{
    /**
     * @param null|list<string> $validationGroups
     */
    public function __construct(public ?array $validationGroups = null) {}
}
