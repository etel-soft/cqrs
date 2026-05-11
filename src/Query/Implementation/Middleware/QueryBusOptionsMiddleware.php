<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation\Middleware;

use Etel\CQRS\Query\Implementation\QueryBusOptionsStamp;
use Override;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ValidationStamp;

/**
 * Translates QueryBusOptions into Messenger stamps.
 */
final class QueryBusOptionsMiddleware implements MiddlewareInterface
{
    /**
     * @throws ExceptionInterface When next step raise exception
     */
    #[Override]
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $stamp = $envelope->last(stampFqcn: QueryBusOptionsStamp::class);

        if ($stamp !== null) {
            $envelope = $envelope->withoutAll(stampFqcn: QueryBusOptionsStamp::class);

            if ($stamp->options->validationGroups !== null) {
                $envelope = $envelope->with(new ValidationStamp(groups: $stamp->options->validationGroups));
            }
        }

        return $stack->next()->handle(envelope: $envelope, stack: $stack);
    }
}
