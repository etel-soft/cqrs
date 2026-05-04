<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation\Middleware;

use Etel\CQRS\Command\CommandInput;
use Etel\CQRS\Command\Implementation\Middleware\CommandInputTransformerMiddleware;
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
#[CoversClass(CommandInputTransformerMiddleware::class)]
final class CommandInputTransformerMiddlewareTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Passes non-CommandInput envelope to next middleware unchanged')]
    public function testPassesThroughRegularMessage(): void
    {
        $envelope = new Envelope(new stdClass());
        $stack = $this->createStubConfig(type: StackInterface::class)
            ->addMethodReturns(
                name: 'next',
                return: $this->createStubConfig(type: MiddlewareInterface::class)
                    ->addMethodReturnsCallback(name: 'handle', callback: fn (Envelope $envelope) => $envelope)
                    ->getSealedStub()
            )
            ->getSealedStub();

        $result = new CommandInputTransformerMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertSame($envelope, $result);
    }

    #[Test]
    #[TestDox('Transforms CommandInput message to command before forwarding')]
    public function testTransformsCommandInputToCommand(): void
    {
        $command = new stdClass();
        $envelope = new Envelope(
            message: $this->createStubConfig(type: CommandInput::class)
                ->addMethodReturns(name: 'toCommand', return: $command)
                ->getSealedStub()
        );
        $capturedEnvelope = null;
        $stack = $this->createCapturingStack(captured: $capturedEnvelope);

        new CommandInputTransformerMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertNotNull($capturedEnvelope);
        $this->assertSame($command, $capturedEnvelope->getMessage());
    }

    #[Test]
    #[TestDox('Preserves stamps when transforming CommandInput')]
    public function testPreservesStampsOnTransformation(): void
    {
        $command = new stdClass();
        $stamp = new class implements StampInterface {};
        $envelope = new Envelope(
            message: $this->createStubConfig(type: CommandInput::class)
                ->addMethodReturns(name: 'toCommand', return: $command)
                ->getSealedStub(),
            stamps: [$stamp]
        );
        $capturedEnvelope = null;
        $stack = $this->createCapturingStack(captured: $capturedEnvelope);

        new CommandInputTransformerMiddleware()->handle(envelope: $envelope, stack: $stack);

        $this->assertNotNull($capturedEnvelope);
        $this->assertNotEmpty($capturedEnvelope->all(stampFqcn: $stamp::class));
    }

    #[Test]
    #[TestDox('Returns result from next middleware')]
    public function testReturnsResultFromNextMiddleware(): void
    {
        $inputEnvelope = new Envelope(new stdClass());
        $outputEnvelope = new Envelope(new stdClass());
        $stack = $this->createStubConfig(type: StackInterface::class)
            ->addMethodReturns(
                name: 'next',
                return: $this->createStubConfig(type: MiddlewareInterface::class)
                    ->addMethodReturns(name: 'handle', return: $outputEnvelope)
                    ->getSealedStub()
            )
            ->getSealedStub();

        $result = new CommandInputTransformerMiddleware()->handle(envelope: $inputEnvelope, stack: $stack);

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
