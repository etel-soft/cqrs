<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation\Middleware;

use Etel\CQRS\Command\CommandBusOptions;
use Etel\CQRS\Command\Implementation\CommandBusOptionsStamp;
use Etel\CQRS\Command\Implementation\Middleware\CommandBusOptionsMiddleware;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\ValidationStamp;

/**
 * @internal
 */
#[CoversClass(CommandBusOptionsMiddleware::class)]
final class CommandBusOptionsMiddlewareTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Passes envelope unchanged when no CommandBusOptionsStamp present')]
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

        $result = new CommandBusOptionsMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertSame($envelope, $result);
    }

    #[Test]
    #[TestDox('Adds DelayStamp and ValidationStamp and removes CommandBusOptionsStamp')]
    public function testAddsDelayAndValidationStampsAndRemovesOptionsStamp(): void
    {
        $options = new CommandBusOptions(delayMs: 5000, validationGroups: ['Default']);
        $envelope = new Envelope(message: new stdClass(), stamps: [new CommandBusOptionsStamp(options: $options)]);
        $capturedEnvelope = null;
        $stack = $this->createCapturingStack(captured: $capturedEnvelope);

        new CommandBusOptionsMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertNotNull($capturedEnvelope);
        $this->assertEmpty($capturedEnvelope->all(stampFqcn: CommandBusOptionsStamp::class));
        $delayStamps = $capturedEnvelope->all(stampFqcn: DelayStamp::class);
        $this->assertCount(1, $delayStamps);
        $this->assertSame(5000, $delayStamps[0]->getDelay());
        $validationStamps = $capturedEnvelope->all(stampFqcn: ValidationStamp::class);
        $this->assertCount(1, $validationStamps);
        $this->assertSame(['Default'], $validationStamps[0]->getGroups());
    }

    #[Test]
    #[TestDox('Adds no stamps when options are at default values')]
    public function testAddsNoStampsWithDefaultOptions(): void
    {
        $envelope = new Envelope(
            message: new stdClass(),
            stamps: [new CommandBusOptionsStamp(options: new CommandBusOptions())]
        );
        $capturedEnvelope = null;
        $stack = $this->createCapturingStack(captured: $capturedEnvelope);

        new CommandBusOptionsMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertNotNull($capturedEnvelope);
        $this->assertEmpty($capturedEnvelope->all(stampFqcn: CommandBusOptionsStamp::class));
        $this->assertEmpty($capturedEnvelope->all(stampFqcn: DelayStamp::class));
        $this->assertEmpty($capturedEnvelope->all(stampFqcn: ValidationStamp::class));
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

        $result = new CommandBusOptionsMiddleware()
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
