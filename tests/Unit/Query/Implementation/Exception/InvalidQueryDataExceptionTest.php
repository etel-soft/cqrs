<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation\Exception;

use Etel\CQRS\Query\Exception\InvalidQueryData;
use Etel\CQRS\Query\Implementation\Exception\InvalidQueryDataException;
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
#[CoversClass(InvalidQueryDataException::class)]
final class InvalidQueryDataExceptionTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Formats message for query input')]
    public function testQueryInputMessage(): void
    {
        $query = $this->createStub(QueryInput::class);

        $exception = InvalidQueryDataException::create(query: $query, message: 'something went wrong');

        $this->assertSame(
            sprintf('Query input "%s" has invalid data (message: something went wrong).', $query::class),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message for regular query')]
    public function testRegularQueryMessage(): void
    {
        $exception = InvalidQueryDataException::create(query: new stdClass(), message: 'validation failed');

        $this->assertSame(
            'Query "stdClass" has invalid data (message: validation failed).',
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Preserves previous exception')]
    public function testPreservesPreviousException(): void
    {
        $previous = new RuntimeException(message: 'original error');

        $exception = InvalidQueryDataException::create(query: new stdClass(), message: 'test', previous: $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    #[TestDox('Implements required contracts')]
    public function testImplementsContracts(): void
    {
        $exception = InvalidQueryDataException::create(query: new stdClass(), message: 'test');

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidQueryData::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
