<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Event\Implementation;

use Etel\CQRS\Event\EventBus;
use Etel\CQRS\Event\EventBusOptions;
use Etel\CQRS\Event\Implementation\EventBusOptionsStamp;
use Etel\CQRS\Event\Implementation\MessengerEventBus;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[CoversClass(MessengerEventBus::class)]
final class MessengerEventBusTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Implements EventBus interface')]
    public function testImplementsInterface(): void
    {
        $bus = new MessengerEventBus(eventMessageBus: $this->createStub(MessageBusInterface::class));

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(EventBus::class, $bus);
    }

    #[Test]
    #[TestDox('Dispatches event without expecting a result')]
    public function testDispatchesEvent(): void
    {
        $event = new stdClass();
        $bus = new MessengerEventBus(
            eventMessageBus: $this->createMockConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', arguments: [$event, []], return: new Envelope(message: $event))
                ->getSealedMock()
        );

        $bus->publish(event: $event);
    }

    #[Test]
    #[TestDox('Wraps EventBusOptions in EventBusOptionsStamp when dispatching')]
    public function testDispatchWrapsOptionsInBusOptionsStamp(): void
    {
        $event = new stdClass();
        $options = new EventBusOptions(delayMs: 1000);
        $capturedStamps = null;

        $bus = new MessengerEventBus(
            eventMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturnsCallback(
                    name: 'dispatch',
                    callback: function (object $message, array $stamps) use (&$capturedStamps): Envelope {
                        $capturedStamps = $stamps;

                        return new Envelope(message: $message);
                    }
                )
                ->getSealedStub()
        );

        $bus->publish(event: $event, options: $options);

        $this->assertIsArray($capturedStamps);
        $this->assertCount(1, $capturedStamps);
        $this->assertInstanceOf(EventBusOptionsStamp::class, $capturedStamps[0]);
        $this->assertSame($options, $capturedStamps[0]->options);
    }

    #[Test]
    #[TestDox('Does not throw when event has zero handlers')]
    public function testDoesNotThrowOnZeroHandlers(): void
    {
        $event = new stdClass();
        $bus = new MessengerEventBus(
            eventMessageBus: $this->createStubConfig(type: MessageBusInterface::class)
                ->addMethodReturns(name: 'dispatch', return: new Envelope(message: $event))
                ->getSealedStub()
        );

        $bus->publish(event: $event);

        $this->addToAssertionCount(1);
    }
}
