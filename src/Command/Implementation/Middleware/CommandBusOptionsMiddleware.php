<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation\Middleware;

use Etel\CQRS\Command\Implementation\CommandBusOptionsStamp;
use Override;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\ValidationStamp;

/**
 * Translates CommandBusOptions into Messenger stamps.
 */
final class CommandBusOptionsMiddleware implements MiddlewareInterface
{
    /**
     * @throws ExceptionInterface When next step raise exception
     */
    #[Override]
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $stamp = $envelope->last(stampFqcn: CommandBusOptionsStamp::class);

        if ($stamp !== null) {
            $envelope = $envelope->withoutAll(stampFqcn: CommandBusOptionsStamp::class);

            if ($stamp->options->delayMs > 0) {
                $envelope = $envelope->with(new DelayStamp(delay: $stamp->options->delayMs));
            }

            if ($stamp->options->validationGroups !== null) {
                $envelope = $envelope->with(new ValidationStamp(groups: $stamp->options->validationGroups));
            }
        }

        return $stack->next()->handle(envelope: $envelope, stack: $stack);
    }
}
