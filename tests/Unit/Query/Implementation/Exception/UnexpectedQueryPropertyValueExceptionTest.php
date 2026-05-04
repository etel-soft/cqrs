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
    #[TestDox('Formats message with property value for regular query')]
    public function testRegularQueryWithReadableProperty(): void
    {
        $query = new class {
            public string $status = 'active';
        };

        $exception = UnexpectedQueryPropertyValueException::create(query: $query, propertyPath: 'status');

        $this->assertSame(
            sprintf(
                'Property by path "status" with value type "string" in query "%s" failed requirements.',
                $query::class,
            ),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message with property value for query input')]
    public function testQueryInputWithReadableProperty(): void
    {
        $query = new class implements QueryInput {
            public function __construct(public DateTime $date = new DateTime()) {}

            public function toQuery(): object
            {
                return new stdClass();
            }
        };

        $exception = UnexpectedQueryPropertyValueException::create(query: $query, propertyPath: 'date');

        $this->assertSame(
            sprintf(
                'Property by path "date" with value type "DateTime" in query input "%s" failed requirements.',
                $query::class,
            ),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message without value when property path cannot be traversed')]
    public function testWithUntraversablePropertyPath(): void
    {
        $query = new class {
            public string $name = 'test';
        };

        $exception = UnexpectedQueryPropertyValueException::create(
            query: $query,
            propertyPath: 'name.nested'
        );

        $this->assertSame(
            sprintf('Property path "name.nested" in query "%s" failed requirements.', $query::class),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats untraversable path message for query input')]
    public function testQueryInputWithUntraversablePropertyPath(): void
    {
        $query = new class implements QueryInput {
            public string $name = 'test';

            public function toQuery(): object
            {
                return new stdClass();
            }
        };

        $exception = UnexpectedQueryPropertyValueException::create(query: $query, propertyPath: 'name.nested');

        $this->assertSame(
            sprintf('Property path "name.nested" in query input "%s" failed requirements.', $query::class),
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
            propertyPath: 'status',
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

        $exception = UnexpectedQueryPropertyValueException::create(query: $query, propertyPath: 'status');

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(UnexpectedQueryPropertyValue::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
