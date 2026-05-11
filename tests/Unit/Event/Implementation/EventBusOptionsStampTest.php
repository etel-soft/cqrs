<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Event\Implementation;

use Etel\CQRS\Event\EventBusOptions;
use Etel\CQRS\Event\Implementation\EventBusOptionsStamp;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @internal
 */
#[CoversClass(EventBusOptionsStamp::class)]
final class EventBusOptionsStampTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Exposes wrapped EventBusOptions')]
    public function testExposesOptions(): void
    {
        $options = new EventBusOptions(delayMs: 2000);
        $stamp = new EventBusOptionsStamp(options: $options);

        $this->assertSame($options, $stamp->options);
    }

    #[Test]
    #[TestDox('Implements StampInterface')]
    public function testImplementsStampInterface(): void
    {
        $this->assertInstanceOf(
            StampInterface::class,
            new EventBusOptionsStamp(options: new EventBusOptions())
        );
    }
}
