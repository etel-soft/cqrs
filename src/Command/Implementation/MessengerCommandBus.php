<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation;

use Etel\CQRS\Command\CommandBus;
use Etel\CQRS\Command\CommandBusOptions;
use Etel\CQRS\Command\Implementation\Exception\InvalidCommandDataException;
use Etel\CQRS\Command\Implementation\Exception\InvalidCommandReturnConfigurationException;
use Etel\CQRS\Command\Implementation\Exception\UnexpectedCommandPropertyValueException;
use Etel\CQRS\Command\Implementation\Exception\UnexpectedCommandResultException;
use Override;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

use function count;
use function is_string;
use function sprintf;

/**
 * Decorator for Symfony Messenger.
 */
final readonly class MessengerCommandBus implements CommandBus
{
    public function __construct(private MessageBusInterface $commandMessageBus) {}

    /**
     * @throws InvalidCommandDataException                When command/input data passed validation, but still invalid
     *                                                    by any reason
     * @throws InvalidCommandReturnConfigurationException When command cannot be handled immediately
     *                                                    (i.e., asynchronous), but $expectResult parameter not FALSE
     * @throws UnexpectedCommandPropertyValueException    When command/input passed validation but property
     *                                                    still has an unexpected value
     * @throws UnexpectedCommandResultException           When a handler returns something not matched to specified FQCN
     *                                                    in $expectResult parameter
     * @throws ExceptionInterface                         For any other exceptions
     */
    #[Override]
    public function command(
        object $command,
        bool|string $expectResult = false,
        ?CommandBusOptions $options = null
    ): mixed {
        $stamps = $options !== null ? [new CommandBusOptionsStamp(options: $options)] : [];

        $expectsResult = $expectResult !== false;
        $expectedType = is_string(value: $expectResult) ? $expectResult : null;
        $nullable = false;

        if (!$expectsResult) {
            $meta = CommandResultReader::read(command: $command);

            if ($meta !== null) {
                $expectsResult = true;
                [$expectedType, $nullable] = $meta;
            }
        }

        if (!$expectsResult) {
            $this->commandMessageBus->dispatch($command, $stamps);

            return null;
        }

        $envelope = $this->commandMessageBus->dispatch($command, $stamps);
        $handledStamps = $envelope->all(stampFqcn: HandledStamp::class);
        $handled = count(value: $handledStamps);

        if ($handled === 0) {
            throw InvalidCommandReturnConfigurationException::create(command: $command);
        }

        if ($handled > 1) {
            throw new LogicException(message: sprintf(
                'Command "%s" was handled multiple times. Only one handler is allowed, got %d handlers.',
                $command::class,
                $handled
            ));
        }

        $result = $handledStamps[0]->getResult();

        if ($expectedType !== null) {
            $matches = $result === null ? $nullable : $result instanceof $expectedType;

            if (!$matches) {
                throw UnexpectedCommandResultException::create(
                    command: $command,
                    expectedType: $expectedType,
                    result: $result
                );
            }
        }

        return $result;
    }
}
