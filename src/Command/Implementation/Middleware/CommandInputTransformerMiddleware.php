<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation\Middleware;

use Etel\CQRS\Command\CommandInput;
use Etel\CQRS\Command\Exception\InvalidCommandData;
use Etel\CQRS\Command\Exception\UnexpectedCommandPropertyValue;
use Override;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * Transforms command input to command.
 *
 * This middleware should be called after a validation step.
 *
 * @see CommandInput
 */
final class CommandInputTransformerMiddleware implements MiddlewareInterface
{
    /**
     * @throws InvalidCommandData             When input data invalid by any reason
     * @throws UnexpectedCommandPropertyValue When input property has an unexpected value
     * @throws ExceptionInterface             When next step raise exception
     */
    #[Override]
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof CommandInput) {
            $message = $message->toCommand();
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
