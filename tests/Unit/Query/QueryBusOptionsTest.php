<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query;

use Etel\CQRS\Query\QueryBusOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(QueryBusOptions::class)]
final class QueryBusOptionsTest extends TestCase
{
    #[Test]
    #[TestDox('Default validationGroups is null')]
    public function testDefaults(): void
    {
        $this->assertNull(new QueryBusOptions()->validationGroups);
    }

    #[Test]
    #[TestDox('Accepts custom validationGroups')]
    public function testCustomValues(): void
    {
        $options = new QueryBusOptions(validationGroups: ['Default']);

        $this->assertSame(['Default'], $options->validationGroups);
    }
}
