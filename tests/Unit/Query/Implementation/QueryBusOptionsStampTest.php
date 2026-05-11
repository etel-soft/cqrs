<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation;

use Etel\CQRS\Query\Implementation\QueryBusOptionsStamp;
use Etel\CQRS\Query\QueryBusOptions;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @internal
 */
#[CoversClass(QueryBusOptionsStamp::class)]
final class QueryBusOptionsStampTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Exposes wrapped QueryBusOptions')]
    public function testExposesOptions(): void
    {
        $options = new QueryBusOptions(validationGroups: ['Default']);
        $stamp = new QueryBusOptionsStamp(options: $options);

        $this->assertSame($options, $stamp->options);
    }

    #[Test]
    #[TestDox('Implements StampInterface')]
    public function testImplementsStampInterface(): void
    {
        $this->assertInstanceOf(
            StampInterface::class,
            new QueryBusOptionsStamp(options: new QueryBusOptions())
        );
    }
}
