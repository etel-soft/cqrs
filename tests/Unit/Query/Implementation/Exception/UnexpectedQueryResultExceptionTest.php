<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation\Exception;

use DateTime;
use DateTimeImmutable;
use Etel\CQRS\Query\Exception\UnexpectedQueryResult;
use Etel\CQRS\Query\Implementation\Exception\UnexpectedQueryResultException;
use Etel\CQRS\Query\QueryInput;
use Etel\CQRSTests\Unit\UnitTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(UnexpectedQueryResultException::class)]
final class UnexpectedQueryResultExceptionTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Formats message for query input with null result')]
    public function testQueryInputWithNullResult(): void
    {
        $query = $this->createStub(QueryInput::class);

        $exception = UnexpectedQueryResultException::create(
            query: $query,
            expectedType: DateTimeImmutable::class,
            result: null
        );

        $this->assertSame(
            sprintf(
                'Result type "NULL" not matched expected type "DateTimeImmutable" in query input "%s".',
                $query::class
            ),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message for regular query with object result')]
    public function testRegularQueryWithObjectResult(): void
    {
        $exception = UnexpectedQueryResultException::create(
            query: new stdClass(),
            expectedType: DateTimeImmutable::class,
            result: new DateTime()
        );

        $this->assertSame(
            'Result type "DateTime" not matched expected type "DateTimeImmutable" in query "stdClass".',
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message with scalar result type')]
    public function testWithScalarResult(): void
    {
        $exception = UnexpectedQueryResultException::create(
            query: new stdClass(),
            expectedType: DateTimeImmutable::class,
            result: 'unexpected string'
        );

        $this->assertSame(
            'Result type "string" not matched expected type "DateTimeImmutable" in query "stdClass".',
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Implements required contracts')]
    public function testImplementsContracts(): void
    {
        $exception = UnexpectedQueryResultException::create(
            query: new stdClass(),
            expectedType: DateTimeImmutable::class,
            result: null
        );

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(UnexpectedQueryResult::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
