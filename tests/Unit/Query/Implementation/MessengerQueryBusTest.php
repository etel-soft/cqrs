<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation;

use DateTimeImmutable;
use Etel\CQRS\Query\Implementation\Exception\InvalidQueryReturnConfigurationException;
use Etel\CQRS\Query\Implementation\Exception\UnexpectedQueryResultException;
use Etel\CQRS\Query\Implementation\MessengerQueryBus;
use Etel\CQRS\Query\QueryBus;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\StampInterface;

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
    #[TestDox('Dispatches without waiting for result when return is false')]
    public function testAsyncDispatchReturnsFalse(): void
    {
        $query = new stdClass();

        $bus = new MessengerQueryBus(
            queryMessageBus: $this->createMockConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', arguments: [$query, []], return: new Envelope(message: $query))
                ->getSealedMock()
        );

        $this->assertNull($bus->query(query: $query, return: false));
    }

    #[Test]
    #[TestDox('Passes custom stamps when dispatching asynchronously')]
    public function testAsyncDispatchForwardsStamps(): void
    {
        $query = new stdClass();
        $stamp = $this->createStub(StampInterface::class);

        $bus = new MessengerQueryBus(
            queryMessageBus: $this->createMockConfig(type: MessageBusInterface::class)
                ->addMethodReturns(
                    name: 'dispatch',
                    arguments: [$query, [$stamp]],
                    return: new Envelope(message: $query)
                )
                ->getSealedMock()
        );

        $bus->query(query: $query, return: false, stamps: [$stamp]);
    }

    #[Test]
    #[TestDox('Returns handler result when return is true (default)')]
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

        $this->assertSame($expectedResult, $bus->query(query: $query, return: DateTimeImmutable::class));
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

        $bus->query(query: $query, return: DateTimeImmutable::class);
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
    #[TestDox('Re-throws LogicException unrelated to zero-handled messages')]
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
}
