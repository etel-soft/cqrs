<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation\Middleware;

use Etel\CQRS\Query\Implementation\Middleware\QueryBusOptionsMiddleware;
use Etel\CQRS\Query\Implementation\QueryBusOptionsStamp;
use Etel\CQRS\Query\QueryBusOptions;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ValidationStamp;

/**
 * @internal
 */
#[CoversClass(QueryBusOptionsMiddleware::class)]
final class QueryBusOptionsMiddlewareTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Passes envelope unchanged when no QueryBusOptionsStamp present')]
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

        $result = new QueryBusOptionsMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertSame($envelope, $result);
    }

    #[Test]
    #[TestDox('Adds ValidationStamp and removes QueryBusOptionsStamp')]
    public function testAddsValidationStampAndRemovesOptionsStamp(): void
    {
        $options = new QueryBusOptions(validationGroups: ['Default', 'strict']);
        $envelope = new Envelope(message: new stdClass(), stamps: [new QueryBusOptionsStamp(options: $options)]);
        $capturedEnvelope = null;
        $stack = $this->createCapturingStack(captured: $capturedEnvelope);

        new QueryBusOptionsMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertNotNull($capturedEnvelope);
        $this->assertEmpty($capturedEnvelope->all(stampFqcn: QueryBusOptionsStamp::class));
        $validationStamps = $capturedEnvelope->all(stampFqcn: ValidationStamp::class);
        $this->assertCount(1, $validationStamps);
        $this->assertSame(['Default', 'strict'], $validationStamps[0]->getGroups());
    }

    #[Test]
    #[TestDox('Adds no ValidationStamp when validationGroups is null')]
    public function testAddsNoValidationStampWhenGroupsAreNull(): void
    {
        $envelope = new Envelope(
            message: new stdClass(),
            stamps: [new QueryBusOptionsStamp(options: new QueryBusOptions())]
        );
        $capturedEnvelope = null;
        $stack = $this->createCapturingStack(captured: $capturedEnvelope);

        new QueryBusOptionsMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertNotNull($capturedEnvelope);
        $this->assertEmpty($capturedEnvelope->all(stampFqcn: QueryBusOptionsStamp::class));
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

        $result = new QueryBusOptionsMiddleware()
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
