<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation;

use DateTimeImmutable;
use Etel\CQRS\Query\Implementation\QueryResultReader;
use Etel\CQRSTests\Unit\Query\Implementation\Fixture\QueryWithNullableResult;
use Etel\CQRSTests\Unit\Query\Implementation\Fixture\QueryWithResult;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionProperty;
use stdClass;

/**
 * @internal
 */
#[CoversClass(QueryResultReader::class)]
final class QueryResultReaderTest extends UnitTestCase
{
    /**
     * Reset the process-global memoisation cache so each test exercises a cold lookup (reflection), independent of
     * what other tests dispatched earlier.
     */
    protected function setUp(): void
    {
        parent::setUp();

        new ReflectionProperty(class: QueryResultReader::class, property: 'cache')
            ->setValue(objectOrValue: null, value: []);
    }

    #[Test]
    #[TestDox('Reads the type and nullability declared by a QueryResult attribute')]
    public function testReadsDeclaredResultMeta(): void
    {
        $this->assertSame([DateTimeImmutable::class, false], QueryResultReader::read(query: new QueryWithResult()));
        $this->assertSame(
            [DateTimeImmutable::class, true],
            QueryResultReader::read(query: new QueryWithNullableResult())
        );
    }

    #[Test]
    #[TestDox('Returns null for a query without a QueryResult attribute')]
    public function testReturnsNullWithoutAttribute(): void
    {
        $this->assertNull(QueryResultReader::read(query: new stdClass()));
    }

    #[Test]
    #[TestDox('Returns a stable result across repeated reads of the same class (memoised)')]
    public function testMemoisesResultPerClass(): void
    {
        $first = QueryResultReader::read(query: new QueryWithResult());
        $second = QueryResultReader::read(query: new QueryWithResult());

        $this->assertSame($first, $second);
    }
}
