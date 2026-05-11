<?php

declare(strict_types=1);

namespace Etel\CQRS\Event\Implementation;

use Etel\CQRS\Event\EventBusOptions;
use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class EventBusOptionsStamp implements StampInterface
{
    public function __construct(public EventBusOptions $options) {}
}
