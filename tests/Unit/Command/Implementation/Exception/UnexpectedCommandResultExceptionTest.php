<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation\Exception;

use DateTime;
use DateTimeImmutable;
use Etel\CQRS\Command\CommandInput;
use Etel\CQRS\Command\Exception\UnexpectedCommandResult;
use Etel\CQRS\Command\Implementation\Exception\UnexpectedCommandResultException;
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
#[CoversClass(UnexpectedCommandResultException::class)]
final class UnexpectedCommandResultExceptionTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Formats message for command input with null result')]
    public function testCommandInputWithNullResult(): void
    {
        $command = $this->createStub(CommandInput::class);

        $exception = UnexpectedCommandResultException::create(
            command: $command,
            expectedType: DateTimeImmutable::class,
            result: null
        );

        $this->assertSame(
            sprintf(
                'Result type "NULL" not matched expected type "DateTimeImmutable" in command input "%s".',
                $command::class
            ),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message for regular command with object result')]
    public function testRegularCommandWithObjectResult(): void
    {
        $exception = UnexpectedCommandResultException::create(
            command: new stdClass(),
            expectedType: DateTimeImmutable::class,
            result: new DateTime()
        );

        $this->assertSame(
            'Result type "DateTime" not matched expected type "DateTimeImmutable" in command "stdClass".',
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message with scalar result type')]
    public function testWithScalarResult(): void
    {
        $exception = UnexpectedCommandResultException::create(
            command: new stdClass(),
            expectedType: DateTimeImmutable::class,
            result: 'unexpected string'
        );

        $this->assertSame(
            'Result type "string" not matched expected type "DateTimeImmutable" in command "stdClass".',
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Implements required contracts')]
    public function testImplementsContracts(): void
    {
        $exception = UnexpectedCommandResultException::create(
            command: new stdClass(),
            expectedType: DateTimeImmutable::class,
            result: null
        );

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(UnexpectedCommandResult::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
