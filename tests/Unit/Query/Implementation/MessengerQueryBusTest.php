<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation;

use DateTimeImmutable;
use Etel\CQRS\Query\Implementation\Exception\InvalidQueryReturnConfigurationException;
use Etel\CQRS\Query\Implementation\Exception\UnexpectedQueryResultException;
use Etel\CQRS\Query\Implementation\MessengerQueryBus;
use Etel\CQRS\Query\Implementation\QueryBusOptionsStamp;
use Etel\CQRS\Query\QueryBus;
use Etel\CQRS\Query\QueryBusOptions;
use Etel\CQRSTests\Unit\Query\Implementation\Fixture\QueryWithNullableResult;
use Etel\CQRSTests\Unit\Query\Implementation\Fixture\QueryWithResult;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @internal
 */
#[CoversClass(MessengerQueryBus::class)]
final class MessengerQueryBusTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Implements QueryBus interface')]
    public function testImplementsInterface(): void
    {
        $bus = new MessengerQueryBus(queryMessageBus: $this->createStub(MessageBusInterface::class));

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(QueryBus::class, $bus);
    }

    #[Test]
    #[TestDox('Wraps QueryBusOptions in QueryBusOptionsStamp when dispatching')]
    public function testDispatchWrapsOptionsInBusOptionsStamp(): void
    {
        $query = new stdClass();
        $options = new QueryBusOptions(validationGroups: ['Default']);
        $capturedStamps = null;

        $bus = new MessengerQueryBus(
            queryMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturnsCallback(
                    name: 'dispatch',
                    callback: function (object $message, array $stamps) use (&$capturedStamps): Envelope {
                        $capturedStamps = $stamps;

                        return new Envelope($message, [new HandledStamp(result: new stdClass(), handlerName: 'test')]);
                    }
                )
                ->getSealedStub()
        );

        $bus->query(query: $query, options: $options);

        $this->assertIsArray($capturedStamps);
        $this->assertCount(1, $capturedStamps);
        $this->assertInstanceOf(QueryBusOptionsStamp::class, $capturedStamps[0]);
        $this->assertSame($options, $capturedStamps[0]->options);
    }

    #[Test]
    #[TestDox('Returns handler result when expectResult is true (default)')]
    public function testSyncDispatchReturnsResult(): void
    {
        $query = new stdClass();
        $expectedResult = new DateTimeImmutable();

        $bus = new MessengerQueryBus(
            queryMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $query, stamps: [
                    new HandledStamp(result: $expectedResult, handlerName: 'handler'),
                ]))
                ->getSealedStub()
        );

        $this->assertSame($expectedResult, $bus->query(query: $query));
    }

    #[Test]
    #[TestDox('Returns handler result when result type matches expected class')]
    public function testSyncDispatchWithMatchingTypedReturn(): void
    {
        $query = new stdClass();
        $expectedResult = new DateTimeImmutable();

        $bus = new MessengerQueryBus(
            queryMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $query, stamps: [
                    new HandledStamp(result: $expectedResult, handlerName: 'handler'),
                ]))
                ->getSealedStub()
        );

        $this->assertSame($expectedResult, $bus->query(query: $query, expectResult: DateTimeImmutable::class));
    }

    #[Test]
    #[TestDox('Throws UnexpectedQueryResultException when result type does not match')]
    public function testThrowsOnResultTypeMismatch(): void
    {
        $query = new stdClass();

        $bus = new MessengerQueryBus(
            queryMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $query, stamps: [
                    new HandledStamp(result: 'wrong type', handlerName: 'handler'),
                ]))
                ->getSealedStub()
        );

        $this->expectException(UnexpectedQueryResultException::class);

        $bus->query(query: $query, expectResult: DateTimeImmutable::class);
    }

    #[Test]
    #[TestDox('Throws InvalidQueryReturnConfigurationException when message handled zero times')]
    public function testThrowsOnHandledZeroTimes(): void
    {
        $query = new stdClass();

        $bus = new MessengerQueryBus(
            queryMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $query))
                ->getSealedStub()
        );

        $this->expectException(InvalidQueryReturnConfigurationException::class);

        $bus->query(query: $query);
    }

    #[Test]
    #[TestDox('Throws LogicException when multiple handlers are found')]
    public function testRethrowsUnrelatedLogicException(): void
    {
        $query = new stdClass();

        $bus = new MessengerQueryBus(
            queryMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $query, stamps: [
                    new HandledStamp(result: 'result1', handlerName: 'handler1'),
                    new HandledStamp(result: 'result2', handlerName: 'handler2'),
                ]))
                ->getSealedStub()
        );

        $this->expectException(LogicException::class);

        $bus->query(query: $query);
    }

    #[Test]
    #[TestDox('Infers and accepts the result type declared by a QueryResult attribute')]
    public function testReturnsResultMatchingQueryResultAttribute(): void
    {
        $query = new QueryWithResult();
        $expectedResult = new DateTimeImmutable();

        $bus = $this->busReturning(query: $query, result: $expectedResult);

        $this->assertSame($expectedResult, $bus->query(query: $query));
    }

    #[Test]
    #[TestDox('Throws when the result does not match the QueryResult attribute type')]
    public function testThrowsWhenResultViolatesQueryResultAttribute(): void
    {
        $query = new QueryWithResult();

        $bus = $this->busReturning(query: $query, result: 'wrong type');

        $this->expectException(UnexpectedQueryResultException::class);

        $bus->query(query: $query);
    }

    #[Test]
    #[TestDox('Throws on null result when the QueryResult attribute is not nullable')]
    public function testThrowsOnNullResultWhenAttributeNotNullable(): void
    {
        $query = new QueryWithResult();

        $bus = $this->busReturning(query: $query, result: null);

        $this->expectException(UnexpectedQueryResultException::class);

        $bus->query(query: $query);
    }

    #[Test]
    #[TestDox('Accepts null result when the QueryResult attribute is nullable')]
    public function testAcceptsNullResultWhenAttributeNullable(): void
    {
        $query = new QueryWithNullableResult();

        $bus = $this->busReturning(query: $query, result: null);

        $this->assertNull($bus->query(query: $query));
    }

    private function busReturning(object $query, mixed $result): MessengerQueryBus
    {
        return new MessengerQueryBus(
            queryMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $query, stamps: [
                    new HandledStamp(result: $result, handlerName: 'handler'),
                ]))
                ->getSealedStub()
        );
    }
}
