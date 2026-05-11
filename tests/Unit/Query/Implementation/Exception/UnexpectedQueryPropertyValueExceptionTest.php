<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation\Exception;

use DateTime;
use Etel\CQRS\Query\Exception\UnexpectedQueryPropertyValue;
use Etel\CQRS\Query\Implementation\Exception\UnexpectedQueryPropertyValueException;
use Etel\CQRS\Query\QueryInput;
use Etel\CQRSTests\Unit\UnitTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(UnexpectedQueryPropertyValueException::class)]
final class UnexpectedQueryPropertyValueExceptionTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Formats message with scalar value type for regular query')]
    public function testRegularQueryWithScalarValue(): void
    {
        $query = new class {
            public string $status = 'active';
        };

        $exception = UnexpectedQueryPropertyValueException::create(
            query: $query,
            propertyName: 'status',
            value: 'active'
        );

        $this->assertSame(
            sprintf(
                'Property "status" with value type "string" in query "%s" failed requirements.',
                $query::class,
            ),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message with object value type for query input')]
    public function testQueryInputWithObjectValue(): void
    {
        $query = new class implements QueryInput {
            public function __construct(public DateTime $date = new DateTime()) {}

            public function toQuery(): object
            {
                return new stdClass();
            }
        };

        $exception = UnexpectedQueryPropertyValueException::create(
            query: $query,
            propertyName: 'date',
            value: new DateTime()
        );

        $this->assertSame(
            sprintf(
                'Property "date" with value type "DateTime" in query input "%s" failed requirements.',
                $query::class,
            ),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Preserves previous exception')]
    public function testPreservesPreviousException(): void
    {
        $previous = new RuntimeException(message: 'root cause');
        $query = new class {
            public string $status = 'ok';
        };

        $exception = UnexpectedQueryPropertyValueException::create(
            query: $query,
            propertyName: 'status',
            value: 'ok',
            previous: $previous
        );

        $this->assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    #[TestDox('Implements required contracts')]
    public function testImplementsContracts(): void
    {
        $query = new class {
            public string $status = 'ok';
        };

        $exception = UnexpectedQueryPropertyValueException::create(
            query: $query,
            propertyName: 'status',
            value: 'ok'
        );

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(UnexpectedQueryPropertyValue::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
