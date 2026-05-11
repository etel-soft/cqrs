<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Event;

use Etel\CQRS\Event\EventBusOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(EventBusOptions::class)]
final class EventBusOptionsTest extends TestCase
{
    #[Test]
    #[TestDox('Default delayMs is zero and dispatchAfterCurrentBus is false')]
    public function testDefaults(): void
    {
        $options = new EventBusOptions();

        $this->assertSame(0, $options->delayMs);
        $this->assertFalse($options->dispatchAfterCurrentBus);
    }

    #[Test]
    #[TestDox('Accepts custom delayMs and dispatchAfterCurrentBus')]
    public function testCustomValues(): void
    {
        $options = new EventBusOptions(delayMs: 3000, dispatchAfterCurrentBus: true);

        $this->assertSame(3000, $options->delayMs);
        $this->assertTrue($options->dispatchAfterCurrentBus);
    }
}
