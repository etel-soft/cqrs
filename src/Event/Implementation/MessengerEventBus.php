<?php

declare(strict_types=1);

namespace Etel\CQRS\Event\Implementation;

use Etel\CQRS\Event\EventBus;
use Etel\CQRS\Event\EventBusOptions;
use Override;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Decorator for Symfony Messenger.
 */
final readonly class MessengerEventBus implements EventBus
{
    public function __construct(private MessageBusInterface $eventMessageBus) {}

    #[Override]
    public function publish(object $event, ?EventBusOptions $options = null): void
    {
        $stamps = $options !== null ? [new EventBusOptionsStamp(options: $options)] : [];

        $this->eventMessageBus->dispatch($event, $stamps);
    }
}
