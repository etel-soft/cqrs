<?php

declare(strict_types=1);

namespace Etel\CQRS\Event;

interface EventBus
{
    /**
     * Publishes an event. Unlike commands or queries, events may have zero or more handlers.
     */
    public function publish(object $event, ?EventBusOptions $options = null): void;
}
