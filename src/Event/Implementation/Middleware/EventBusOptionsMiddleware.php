<?php

declare(strict_types=1);

namespace Etel\CQRS\Event\Implementation\Middleware;

use Etel\CQRS\Event\Implementation\EventBusOptionsStamp;
use Override;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * Translates EventBusOptions into Messenger stamps.
 */
final class EventBusOptionsMiddleware implements MiddlewareInterface
{
    /**
     * @throws ExceptionInterface When next step raise exception
     */
    #[Override]
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $stamp = $envelope->last(stampFqcn: EventBusOptionsStamp::class);

        if ($stamp !== null) {
            $envelope = $envelope->withoutAll(stampFqcn: EventBusOptionsStamp::class);

            if ($stamp->options->delayMs > 0) {
                $envelope = $envelope->with(new DelayStamp(delay: $stamp->options->delayMs));
            }

            if ($stamp->options->dispatchAfterCurrentBus) {
                $envelope = $envelope->with(new DispatchAfterCurrentBusStamp());
            }
        }

        return $stack->next()->handle(envelope: $envelope, stack: $stack);
    }
}
