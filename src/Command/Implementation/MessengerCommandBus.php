<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation;

use Etel\CQRS\Command\CommandBus;
use Etel\CQRS\Command\Implementation\Exception\InvalidCommandDataException;
use Etel\CQRS\Command\Implementation\Exception\InvalidCommandReturnConfigurationException;
use Etel\CQRS\Command\Implementation\Exception\UnexpectedCommandPropertyValueException;
use Etel\CQRS\Command\Implementation\Exception\UnexpectedCommandResultException;
use Override;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

use function str_contains;

/**
 * Decorator for Symfony Messenger.
 */
final class MessengerCommandBus implements CommandBus
{
    use HandleTrait;

    public function __construct(MessageBusInterface $commandMessageBus)
    {
        $this->messageBus = $commandMessageBus;
    }

    /**
     * @param array<StampInterface> $stamps
     *
     * @throws InvalidCommandDataException                When command/input data passed validation, but still invalid
     *                                                    by any reason
     * @throws InvalidCommandReturnConfigurationException When command cannot be handled immediately
     *                                                    (i.e., asynchronous), but $return parameter not FALSE
     * @throws UnexpectedCommandPropertyValueException    When command/input passed validation but property
     *                                                    still has an unexpected value
     * @throws UnexpectedCommandResultException           When a handler returns something not matched to specified FQCN
     *                                                    in $return parameter
     * @throws ExceptionInterface                         For any other exceptions
     */
    #[Override]
    public function command(object $command, bool|string $return = false, array $stamps = []): mixed
    {
        if ($return === false) {
            $this->messageBus->dispatch($command, $stamps);

            return null;
        }

        try {
            $result = $this->handle(message: $command, stamps: $stamps);
        } catch (LogicException $exception) {
            if (str_contains(haystack: $exception->getMessage(), needle: 'was handled zero times')) {
                throw InvalidCommandReturnConfigurationException::create(command: $command);
            }

            throw $exception;
        }

        if ($return !== true && !$result instanceof $return) {
            throw UnexpectedCommandResultException::create(command: $command, expectedType: $return, result: $result);
        }

        return $result;
    }
}
