<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command;

use DateTimeImmutable;
use Etel\CQRS\Command\CommandResult;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @internal
 */
#[CoversClass(CommandResult::class)]
final class CommandResultTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Holds the declared result type and nullability')]
    public function testHoldsTypeAndNullability(): void
    {
        $attribute = new CommandResult(type: DateTimeImmutable::class, nullable: true);

        $this->assertSame(DateTimeImmutable::class, $attribute->type);
        $this->assertTrue($attribute->nullable);
    }

    #[Test]
    #[TestDox('Defaults to no concrete type and non-nullable')]
    public function testDefaults(): void
    {
        $attribute = new CommandResult();

        $this->assertNull($attribute->type);
        $this->assertFalse($attribute->nullable);
    }
}
