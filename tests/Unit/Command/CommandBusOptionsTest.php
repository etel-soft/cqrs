<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command;

use Etel\CQRS\Command\CommandBusOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CommandBusOptions::class)]
final class CommandBusOptionsTest extends TestCase
{
    #[Test]
    #[TestDox('Default delayMs is zero and validationGroups is null')]
    public function testDefaults(): void
    {
        $options = new CommandBusOptions();

        $this->assertSame(0, $options->delayMs);
        $this->assertNull($options->validationGroups);
    }

    #[Test]
    #[TestDox('Accepts custom delayMs and validationGroups')]
    public function testCustomValues(): void
    {
        $options = new CommandBusOptions(delayMs: 5000, validationGroups: ['Default', 'strict']);

        $this->assertSame(5000, $options->delayMs);
        $this->assertSame(['Default', 'strict'], $options->validationGroups);
    }
}
