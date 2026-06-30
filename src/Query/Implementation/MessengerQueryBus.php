<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation;

use Etel\CQRS\Query\Implementation\Exception\InvalidQueryDataException;
use Etel\CQRS\Query\Implementation\Exception\InvalidQueryReturnConfigurationException;
use Etel\CQRS\Query\Implementation\Exception\UnexpectedQueryPropertyValueException;
use Etel\CQRS\Query\Implementation\Exception\UnexpectedQueryResultException;
use Etel\CQRS\Query\QueryBus;
use Etel\CQRS\Query\QueryBusOptions;
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
final readonly class MessengerQueryBus implements QueryBus
{
    public function __construct(private MessageBusInterface $queryMessageBus) {}

    /**
     * @throws InvalidQueryDataException                When query/input data passed validation, but still invalid
     *                                                  by any reason
     * @throws InvalidQueryReturnConfigurationException When a query cannot be handled immediately
     *                                                  (i.e., asynchronous), but $expectResult parameter not FALSE
     * @throws UnexpectedQueryPropertyValueException    When query/input passed validation but property
     *                                                  still has an unexpected value
     * @throws UnexpectedQueryResultException           When a handler returns something not matched to specified FQCN
     *                                                  in $expectResult parameter
     * @throws ExceptionInterface                       For any other exceptions
     */
    #[Override]
    public function query(object $query, string|true $expectResult = true, ?QueryBusOptions $options = null): mixed
    {
        $stamps = $options !== null ? [new QueryBusOptionsStamp(options: $options)] : [];

        $envelope = $this->queryMessageBus->dispatch($query, $stamps);
        $handledStamps = $envelope->all(stampFqcn: HandledStamp::class);
        $handled = count(value: $handledStamps);

        if ($handled === 0) {
            throw InvalidQueryReturnConfigurationException::create(query: $query);
        }

        if ($handled > 1) {
            throw new LogicException(message: sprintf(
                'Query of type "%s" was handled multiple times. Only one handler is allowed, got %d handlers.',
                $query::class,
                $handled
            ));
        }

        $result = $handledStamps[0]->getResult();

        $expectedType = is_string(value: $expectResult) ? $expectResult : null;
        $nullable = false;

        if ($expectedType === null) {
            $meta = QueryResultReader::read(query: $query);

            if ($meta !== null) {
                [$expectedType, $nullable] = $meta;
            }
        }

        if ($expectedType !== null) {
            $matches = $result === null ? $nullable : $result instanceof $expectedType;

            if (!$matches) {
                throw UnexpectedQueryResultException::create(
                    query: $query,
                    expectedType: $expectedType,
                    result: $result
                );
            }
        }

        return $result;
    }
}
