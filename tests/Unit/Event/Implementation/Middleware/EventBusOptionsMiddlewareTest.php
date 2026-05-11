<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Event\Implementation\Middleware;

use Etel\CQRS\Event\EventBusOptions;
use Etel\CQRS\Event\Implementation\EventBusOptionsStamp;
use Etel\CQRS\Event\Implementation\Middleware\EventBusOptionsMiddleware;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * @internal
 */
#[CoversClass(EventBusOptionsMiddleware::class)]
final class EventBusOptionsMiddlewareTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Passes envelope unchanged when no EventBusOptionsStamp present')]
    public function testPassesThroughWhenNoStamp(): void
    {
        $envelope = new Envelope(message: new stdClass());
        $stack = $this->createStubConfig(type: StackInterface::class)
            ->addMethodReturns(
                name: 'next',
                return: $this->createStubConfig(type: MiddlewareInterface::class)
                    ->addMethodReturnsCallback(name: 'handle', callback: fn (Envelope $envelope) => $envelope)
                    ->getSealedStub()
            )
            ->getSealedStub();

        $result = new EventBusOptionsMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertSame($envelope, $result);
    }

    #[Test]
    #[TestDox('Adds DelayStamp and DispatchAfterCurrentBusStamp and removes EventBusOptionsStamp')]
    public function testAddsDelayAndDispatchAfterCurrentBusStampsAndRemovesOptionsStamp(): void
    {
        $options = new EventBusOptions(delayMs: 3000, dispatchAfterCurrentBus: true);
        $envelope = new Envelope(message: new stdClass(), stamps: [new EventBusOptionsStamp(options: $options)]);
        $capturedEnvelope = null;
        $stack = $this->createCapturingStack(captured: $capturedEnvelope);

        new EventBusOptionsMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertNotNull($capturedEnvelope);
        $this->assertEmpty($capturedEnvelope->all(stampFqcn: EventBusOptionsStamp::class));
        $delayStamps = $capturedEnvelope->all(stampFqcn: DelayStamp::class);
        $this->assertCount(1, $delayStamps);
        $this->assertSame(3000, $delayStamps[0]->getDelay());
        $this->assertNotEmpty($capturedEnvelope->all(stampFqcn: DispatchAfterCurrentBusStamp::class));
    }

    #[Test]
    #[TestDox('Adds no stamps when options are at default values')]
    public function testAddsNoStampsWithDefaultOptions(): void
    {
        $envelope = new Envelope(
            message: new stdClass(),
            stamps: [new EventBusOptionsStamp(options: new EventBusOptions())]
        );
        $capturedEnvelope = null;
        $stack = $this->createCapturingStack(captured: $capturedEnvelope);

        new EventBusOptionsMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertNotNull($capturedEnvelope);
        $this->assertEmpty($capturedEnvelope->all(stampFqcn: EventBusOptionsStamp::class));
        $this->assertEmpty($capturedEnvelope->all(stampFqcn: DelayStamp::class));
        $this->assertEmpty($capturedEnvelope->all(stampFqcn: DispatchAfterCurrentBusStamp::class));
    }

    #[Test]
    #[TestDox('Returns result from next middleware')]
    public function testReturnsResultFromNextMiddleware(): void
    {
        $outputEnvelope = new Envelope(message: new stdClass());
        $stack = $this->createStubConfig(type: StackInterface::class)
            ->addMethodReturns(
                name: 'next',
                return: $this->createStubConfig(type: MiddlewareInterface::class)
                    ->addMethodReturns(name: 'handle', return: $outputEnvelope)
                    ->getSealedStub()
            )
            ->getSealedStub();

        $result = new EventBusOptionsMiddleware()
            ->handle(envelope: new Envelope(message: new stdClass()), stack: $stack);

        $this->assertSame($outputEnvelope, $result);
    }

    private function createCapturingStack(?Envelope &$captured): StackInterface
    {
        return $this->createStubConfig(type: StackInterface::class)
            ->addMethodReturns(
                name: 'next',
                return: $this->createStubConfig(type: MiddlewareInterface::class)
                    ->addMethodReturnsCallback(
                        name: 'handle',
                        callback: function (Envelope $envelope) use (&$captured): Envelope {
                            $captured = $envelope;

                            return $envelope;
                        }
                    )
                    ->getSealedStub()
            )
            ->getSealedStub();
    }
}
