<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation\Middleware;

use Etel\CQRS\Query\Exception\InvalidQueryData;
use Etel\CQRS\Query\Exception\UnexpectedQueryPropertyValue;
use Etel\CQRS\Query\QueryInput;
use Override;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * Transforms query input to query.
 *
 * This middleware should be called after a validation step.
 *
 * @see QueryInput
 */
final class QueryInputTransformerMiddleware implements MiddlewareInterface
{
    /**
     * @throws InvalidQueryData             When input data invalid by any reason
     * @throws UnexpectedQueryPropertyValue When input property has an unexpected value
     * @throws ExceptionInterface           When next step raise exception
     */
    #[Override]
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof QueryInput) {
            $message = $message->toQuery();
            $stamps = [];

            foreach ($envelope->all() as $groupStamps) {
                foreach ($groupStamps as $stamp) {
                    $stamps[] = $stamp;
                }
            }

            $envelope = new Envelope(message: $message, stamps: $stamps);
        }

        return $stack->next()->handle(envelope: $envelope, stack: $stack);
    }
}
