<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation\Middleware;

use Etel\CQRS\Query\Implementation\Middleware\QueryInputTransformerMiddleware;
use Etel\CQRS\Query\QueryInput;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @internal
 */
#[CoversClass(QueryInputTransformerMiddleware::class)]
final class QueryInputTransformerMiddlewareTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Passes non-QueryInput envelope to next middleware unchanged')]
    public function testPassesThroughRegularMessage(): void
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

        $result = new QueryInputTransformerMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertSame($envelope, $result);
    }

    #[Test]
    #[TestDox('Transforms QueryInput message to query before forwarding')]
    public function testTransformsQueryInputToQuery(): void
    {
        $query = new stdClass();
        $envelope = new Envelope(
            message: $this->createStubConfig(type: QueryInput::class)
                ->addMethodReturns(name: 'toQuery', return: $query)
                ->getSealedStub()
        );
        $capturedEnvelope = null;

        $stack = $this->createCapturingStack(captured: $capturedEnvelope);

        new QueryInputTransformerMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertNotNull($capturedEnvelope);
        $this->assertSame($query, $capturedEnvelope->getMessage());
    }

    #[Test]
    #[TestDox('Preserves stamps when transforming QueryInput')]
    public function testPreservesStampsOnTransformation(): void
    {
        $query = new stdClass();
        $stamp = new class implements StampInterface {};
        $envelope = new Envelope(
            message: $this->createStubConfig(type: QueryInput::class)
                ->addMethodReturns(name: 'toQuery', return: $query)
                ->getSealedStub(),
            stamps: [$stamp]
        );
        $capturedEnvelope = null;

        $stack = $this->createCapturingStack(captured: $capturedEnvelope);

        new QueryInputTransformerMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertNotNull($capturedEnvelope);
        $this->assertNotEmpty($capturedEnvelope->all(stampFqcn: $stamp::class));
    }

    #[Test]
    #[TestDox('Returns result from next middleware')]
    public function testReturnsResultFromNextMiddleware(): void
    {
        $inputEnvelope = new Envelope(message: new stdClass());
        $outputEnvelope = new Envelope(message: new stdClass());

        $stack = $this->createStubConfig(type: StackInterface::class)
            ->addMethodReturns(
                name: 'next',
                return: $this->createStubConfig(type: MiddlewareInterface::class)
                    ->addMethodReturns(name: 'handle', return: $outputEnvelope)
                    ->getSealedStub()
            )
            ->getSealedStub();

        $result = new QueryInputTransformerMiddleware()->handle(envelope: $inputEnvelope, stack: $stack);

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
