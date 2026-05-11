<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation\Exception;

use DateTime;
use Etel\CQRS\Command\CommandInput;
use Etel\CQRS\Command\Exception\UnexpectedCommandPropertyValue;
use Etel\CQRS\Command\Implementation\Exception\UnexpectedCommandPropertyValueException;
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
#[CoversClass(UnexpectedCommandPropertyValueException::class)]
final class UnexpectedCommandPropertyValueExceptionTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Formats message with scalar value type for regular command')]
    public function testRegularCommandWithScalarValue(): void
    {
        $command = new class {
            public string $status = 'active';
        };

        $exception = UnexpectedCommandPropertyValueException::create(
            command: $command,
            propertyName: 'status',
            value: 'active'
        );

        $this->assertSame(
            sprintf(
                'Property "status" with value type "string" in command "%s" failed requirements.',
                $command::class,
            ),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message with object value type for command input')]
    public function testCommandInputWithObjectValue(): void
    {
        $command = new class implements CommandInput {
            public function __construct(public DateTime $date = new DateTime()) {}

            public function toCommand(): object
            {
                return new stdClass();
            }
        };

        $exception = UnexpectedCommandPropertyValueException::create(
            command: $command,
            propertyName: 'date',
            value: new DateTime()
        );

        $this->assertSame(
            sprintf(
                'Property "date" with value type "DateTime" in command input "%s" failed requirements.',
                $command::class
            ),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Preserves previous exception')]
    public function testPreservesPreviousException(): void
    {
        $previous = new RuntimeException(message: 'root cause');
        $command = new class {
            public string $status = 'ok';
        };

        $exception = UnexpectedCommandPropertyValueException::create(
            command: $command,
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
        $command = new class {
            public string $status = 'ok';
        };

        $exception = UnexpectedCommandPropertyValueException::create(
            command: $command,
            propertyName: 'status',
            value: 'ok'
        );

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(UnexpectedCommandPropertyValue::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
