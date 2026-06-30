<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation;

use DateTimeImmutable;
use Etel\CQRS\Command\CommandBus;
use Etel\CQRS\Command\CommandBusOptions;
use Etel\CQRS\Command\Implementation\CommandBusOptionsStamp;
use Etel\CQRS\Command\Implementation\Exception\InvalidCommandReturnConfigurationException;
use Etel\CQRS\Command\Implementation\Exception\UnexpectedCommandResultException;
use Etel\CQRS\Command\Implementation\MessengerCommandBus;
use Etel\CQRSTests\Unit\Command\Implementation\Fixture\CommandWithNullableResult;
use Etel\CQRSTests\Unit\Command\Implementation\Fixture\CommandWithResult;
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
#[CoversClass(MessengerCommandBus::class)]
final class MessengerCommandBusTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Implements CommandBus interface')]
    public function testImplementsInterface(): void
    {
        $bus = new MessengerCommandBus(commandMessageBus: $this->createStub(MessageBusInterface::class));

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(CommandBus::class, $bus);
    }

    #[Test]
    #[TestDox('Dispatches without waiting for result when expectResult is false')]
    public function testAsyncDispatch(): void
    {
        $command = new stdClass();
        $bus = new MessengerCommandBus(
            commandMessageBus: $this->createMockConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', arguments: [$command, []], return: new Envelope(message: $command))
                ->getSealedMock()
        );

        $bus->command(command: $command);
    }

    #[Test]
    #[TestDox('Wraps CommandBusOptions in CommandBusOptionsStamp when dispatching')]
    public function testDispatchWrapsOptionsInBusOptionsStamp(): void
    {
        $command = new stdClass();
        $options = new CommandBusOptions(delayMs: 1000);
        $capturedStamps = null;

        $bus = new MessengerCommandBus(
            commandMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturnsCallback(
                    name: 'dispatch',
                    callback: function (object $message, array $stamps) use (&$capturedStamps): Envelope {
                        $capturedStamps = $stamps;

                        return new Envelope(message: $message);
                    }
                )
                ->getSealedStub()
        );

        $bus->command(command: $command, options: $options);

        $this->assertIsArray($capturedStamps);
        $this->assertCount(1, $capturedStamps);
        $this->assertInstanceOf(CommandBusOptionsStamp::class, $capturedStamps[0]);
        $this->assertSame($options, $capturedStamps[0]->options);
    }

    #[Test]
    #[TestDox('Returns handler result when expectResult is true')]
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

        $this->assertSame($expectedResult, $bus->command(command: $command, expectResult: true));
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

        $this->assertSame($expectedResult, $bus->command(command: $command, expectResult: DateTimeImmutable::class));
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

        $bus->command(command: $command, expectResult: DateTimeImmutable::class);
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

        $bus->command(command: $command, expectResult: true);
    }

    #[Test]
    #[TestDox('Throws LogicException when multiple handlers are found')]
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

        $bus->command(command: $command, expectResult: true);
    }

    #[Test]
    #[TestDox('CommandResult attribute opts into a synchronous result and returns it')]
    public function testReturnsResultMatchingCommandResultAttribute(): void
    {
        $command = new CommandWithResult();
        $expectedResult = new DateTimeImmutable();

        $bus = $this->busReturning(command: $command, result: $expectedResult);

        $this->assertSame($expectedResult, $bus->command(command: $command));
    }

    #[Test]
    #[TestDox('Throws when the result does not match the CommandResult attribute type')]
    public function testThrowsWhenResultViolatesCommandResultAttribute(): void
    {
        $command = new CommandWithResult();

        $bus = $this->busReturning(command: $command, result: 'wrong type');

        $this->expectException(UnexpectedCommandResultException::class);

        $bus->command(command: $command);
    }

    #[Test]
    #[TestDox('Throws on null result when the CommandResult attribute is not nullable')]
    public function testThrowsOnNullResultWhenAttributeNotNullable(): void
    {
        $command = new CommandWithResult();

        $bus = $this->busReturning(command: $command, result: null);

        $this->expectException(UnexpectedCommandResultException::class);

        $bus->command(command: $command);
    }

    #[Test]
    #[TestDox('Accepts null result when the CommandResult attribute is nullable')]
    public function testAcceptsNullResultWhenAttributeNullable(): void
    {
        $command = new CommandWithNullableResult();

        $bus = $this->busReturning(command: $command, result: null);

        $this->assertNull($bus->command(command: $command));
    }

    private function busReturning(object $command, mixed $result): MessengerCommandBus
    {
        return new MessengerCommandBus(
            commandMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $command, stamps: [
                    new HandledStamp(result: $result, handlerName: 'handler'),
                ]))
                ->getSealedStub()
        );
    }
}
