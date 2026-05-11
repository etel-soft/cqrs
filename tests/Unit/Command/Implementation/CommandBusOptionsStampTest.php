<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation;

use Etel\CQRS\Command\CommandBusOptions;
use Etel\CQRS\Command\Implementation\CommandBusOptionsStamp;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @internal
 */
#[CoversClass(CommandBusOptionsStamp::class)]
final class CommandBusOptionsStampTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Exposes wrapped CommandBusOptions')]
    public function testExposesOptions(): void
    {
        $options = new CommandBusOptions(delayMs: 1000);
        $stamp = new CommandBusOptionsStamp(options: $options);

        $this->assertSame($options, $stamp->options);
    }

    #[Test]
    #[TestDox('Implements StampInterface')]
    public function testImplementsStampInterface(): void
    {
        $this->assertInstanceOf(
            StampInterface::class,
            new CommandBusOptionsStamp(options: new CommandBusOptions())
        );
    }
}
