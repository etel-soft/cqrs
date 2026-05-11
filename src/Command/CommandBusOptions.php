<?php

declare(strict_types=1);

namespace Etel\CQRS\Command;

final readonly class CommandBusOptions
{
    /**
     * @param null|list<string> $validationGroups
     */
    public function __construct(public int $delayMs = 0, public ?array $validationGroups = null) {}
}
