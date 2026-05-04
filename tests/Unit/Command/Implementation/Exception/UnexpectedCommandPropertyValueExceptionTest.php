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
    #[TestDox('Formats message with property value for regular command')]
    public function testRegularCommandWithReadableProperty(): void
    {
        $command = new class {
            public string $status = 'active';
        };

        $exception = UnexpectedCommandPropertyValueException::create(command: $command, propertyPath: 'status');

        $this->assertSame(
            sprintf(
                'Property by path "status" with value type "string" in command "%s" failed requirements.',
                $command::class,
            ),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message with property value for command input')]
    public function testCommandInputWithReadableProperty(): void
    {
        $command = new class implements CommandInput {
            public function __construct(public DateTime $date = new DateTime()) {}

            public function toCommand(): object
            {
                return new stdClass();
            }
        };

        $exception = UnexpectedCommandPropertyValueException::create(command: $command, propertyPath: 'date');

        $this->assertSame(
            sprintf(
                'Property by path "date" with value type "DateTime" in command input "%s" failed requirements.',
                $command::class
            ),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message without value when property path cannot be traversed')]
    public function testWithUntraversablePropertyPath(): void
    {
        $command = new class {
            public string $name = 'test';
        };

        $exception = UnexpectedCommandPropertyValueException::create(command: $command, propertyPath: 'name.nested');

        $this->assertSame(
            sprintf('Property path "name.nested" in command "%s" failed requirements.', $command::class),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats untraversable path message for command input')]
    public function testCommandInputWithUntraversablePropertyPath(): void
    {
        $command = new class implements CommandInput {
            public string $name = 'test';

            public function toCommand(): object
            {
                return new stdClass();
            }
        };

        $exception = UnexpectedCommandPropertyValueException::create(command: $command, propertyPath: 'name.nested');

        $this->assertSame(
            sprintf('Property path "name.nested" in command input "%s" failed requirements.', $command::class),
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
            propertyPath: 'status',
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

        $exception = UnexpectedCommandPropertyValueException::create(command: $command, propertyPath: 'status');

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(UnexpectedCommandPropertyValue::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
