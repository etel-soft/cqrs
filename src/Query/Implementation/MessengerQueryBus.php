<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation;

use Etel\CQRS\Query\Implementation\Exception\InvalidQueryDataException;
use Etel\CQRS\Query\Implementation\Exception\InvalidQueryReturnConfigurationException;
use Etel\CQRS\Query\Implementation\Exception\UnexpectedQueryPropertyValueException;
use Etel\CQRS\Query\Implementation\Exception\UnexpectedQueryResultException;
use Etel\CQRS\Query\QueryBus;
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
final class MessengerQueryBus implements QueryBus
{
    use HandleTrait;

    public function __construct(MessageBusInterface $queryMessageBus)
    {
        $this->messageBus = $queryMessageBus;
    }

    /**
     * @param array<StampInterface> $stamps
     *
     * @throws InvalidQueryDataException                When query/input data passed validation, but still invalid
     *                                                  by any reason
     * @throws InvalidQueryReturnConfigurationException When a query cannot be handled immediately
     *                                                  (i.e., asynchronous), but $return parameter not FALSE
     * @throws UnexpectedQueryPropertyValueException    When query/input passed validation but property
     *                                                  still has an unexpected value
     * @throws UnexpectedQueryResultException           When a handler returns something not matched to specified FQCN
     *                                                  in $return parameter
     * @throws ExceptionInterface                       For any other exceptions
     */
    #[Override]
    public function query(object $query, bool|string $return = true, array $stamps = []): mixed
    {
        if ($return === false) {
            $this->messageBus->dispatch($query, $stamps);

            return null;
        }

        try {
            $result = $this->handle(message: $query, stamps: $stamps);
        } catch (LogicException $exception) {
            if (str_contains(haystack: $exception->getMessage(), needle: 'was handled zero times')) {
                throw InvalidQueryReturnConfigurationException::create(query: $query);
            }

            throw $exception;
        }

        if ($return !== true && !$result instanceof $return) {
            throw UnexpectedQueryResultException::create(query: $query, expectedType: $return, result: $result);
        }

        return $result;
    }
}
