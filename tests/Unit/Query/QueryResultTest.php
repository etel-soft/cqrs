<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query;

use DateTimeImmutable;
use Etel\CQRS\Query\QueryResult;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @internal
 */
#[CoversClass(QueryResult::class)]
final class QueryResultTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Holds the declared result type and nullability')]
    public function testHoldsTypeAndNullability(): void
    {
        $attribute = new QueryResult(type: DateTimeImmutable::class, nullable: true);

        $this->assertSame(DateTimeImmutable::class, $attribute->type);
        $this->assertTrue($attribute->nullable);
    }

    #[Test]
    #[TestDox('Defaults to no concrete type and non-nullable')]
    public function testDefaults(): void
    {
        $attribute = new QueryResult();

        $this->assertNull($attribute->type);
        $this->assertFalse($attribute->nullable);
    }
}
