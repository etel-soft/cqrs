<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation;

use DateTimeImmutable;
use Etel\CQRS\Command\CommandBus;
use Etel\CQRS\Command\Implementation\Exception\InvalidCommandReturnConfigurationException;
use Etel\CQRS\Command\Implementation\Exception\UnexpectedCommandResultException;
use Etel\CQRS\Command\Implementation\MessengerCommandBus;
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
#[CoversClass(MessengerCommandBus::class)]
final class MessengerCommandBusTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Implements CommandBus interface')]
    public function testImplementsInterface(): void
    {
        $bus = new MessengerCommandBus($this->createStub(MessageBusInterface::class));

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(CommandBus::class, $bus);
    }

    #[Test]
    #[TestDox('Dispatches without waiting for result when return is false')]
    public function testAsyncDispatchReturnsFalse(): void
    {
        $command = new stdClass();
        $bus = new MessengerCommandBus(
            commandMessageBus: $this->createMockConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', arguments: [$command, []], return: new Envelope(message: $command))
                ->getSealedMock()
        );

        $this->assertNull($bus->command(command: $command));
    }

    #[Test]
    #[TestDox('Passes custom stamps when dispatching asynchronously')]
    public function testAsyncDispatchForwardsStamps(): void
    {
        $command = new stdClass();
        $stamp = $this->createStub(StampInterface::class);

        $bus = new MessengerCommandBus(
            commandMessageBus: $this->createMockConfig(type: MessageBusInterface::class)
                ->addMethodReturns(
                    name: 'dispatch',
                    arguments: [$command, [$stamp]],
                    return: new Envelope(message: $command)
                )
                ->getSealedMock()
        );

        $bus->command(command: $command, stamps: [$stamp]);
    }

    #[Test]
    #[TestDox('Returns handler result when return is true')]
    public function testSyncDispatchReturnsResult(): void
    {
        $command = new stdClass();
        $expectedResult = new DateTimeImmutable();

        $bus = new MessengerCommandBus(
            commandMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $command, stamps: [
                    new HandledStamp(result: $expectedResult, handlerName: 'handler'),
                ]))
                ->getSealedStub()
        );

        $this->assertSame($expectedResult, $bus->command(command: $command, return: true));
    }

    #[Test]
    #[TestDox('Returns handler result when result type matches expected class')]
    public function testSyncDispatchWithMatchingTypedReturn(): void
    {
        $command = new stdClass();
        $expectedResult = new DateTimeImmutable();

        $bus = new MessengerCommandBus(
            commandMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $command, stamps: [
                    new HandledStamp(result: $expectedResult, handlerName: 'handler'),
                ]))
                ->getSealedStub()
        );

        $this->assertSame($expectedResult, $bus->command(command: $command, return: DateTimeImmutable::class));
    }

    #[Test]
    #[TestDox('Throws UnexpectedCommandResultException when result type does not match')]
    public function testThrowsOnResultTypeMismatch(): void
    {
        $command = new stdClass();

        $bus = new MessengerCommandBus(
            commandMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $command, stamps: [
                    new HandledStamp(result: 'wrong type', handlerName: 'handler'),
                ]))
                ->getSealedStub()
        );

        $this->expectException(UnexpectedCommandResultException::class);

        $bus->command(command: $command, return: DateTimeImmutable::class);
    }

    #[Test]
    #[TestDox('Throws InvalidCommandReturnConfigurationException when message handled zero times')]
    public function testThrowsOnHandledZeroTimes(): void
    {
        $command = new stdClass();

        $bus = new MessengerCommandBus(
            commandMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $command))
                ->getSealedStub()
        );

        $this->expectException(InvalidCommandReturnConfigurationException::class);

        $bus->command(command: $command, return: true);
    }

    #[Test]
    #[TestDox('Re-throws LogicException unrelated to zero-handled messages')]
    public function testRethrowsUnrelatedLogicException(): void
    {
        $command = new stdClass();

        $bus = new MessengerCommandBus(
            commandMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $command, stamps: [
                    new HandledStamp(result: 'result1', handlerName: 'handler1'),
                    new HandledStamp(result: 'result2', handlerName: 'handler2'),
                ]))
                ->getSealedStub()
        );

        $this->expectException(LogicException::class);

        $bus->command(command: $command, return: true);
    }
}
