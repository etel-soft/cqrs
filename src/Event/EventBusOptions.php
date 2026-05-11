<?php

declare(strict_types=1);

namespace Etel\CQRS\Event;

final readonly class EventBusOptions
{
    public function __construct(
        public int $delayMs = 0,
        public bool $dispatchAfterCurrentBus = false
    ) {}
}
