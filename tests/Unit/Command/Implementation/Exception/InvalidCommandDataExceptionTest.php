<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation\Exception;

use Etel\CQRS\Command\CommandInput;
use Etel\CQRS\Command\Exception\InvalidCommandData;
use Etel\CQRS\Command\Implementation\Exception\InvalidCommandDataException;
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
#[CoversClass(InvalidCommandDataException::class)]
final class InvalidCommandDataExceptionTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Formats message for command input')]
    public function testCommandInputMessage(): void
    {
        $command = $this->createStub(CommandInput::class);

        $exception = InvalidCommandDataException::create(command: $command, message: 'something went wrong');

        $this->assertSame(
            sprintf('Command input "%s" has invalid data (message: something went wrong).', $command::class),
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Formats message for regular command')]
    public function testRegularCommandMessage(): void
    {
        $exception = InvalidCommandDataException::create(command: new stdClass(), message: 'validation failed');

        $this->assertSame(
            'Command "stdClass" has invalid data (message: validation failed).',
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Preserves previous exception')]
    public function testPreservesPreviousException(): void
    {
        $previous = new RuntimeException(message: 'original error');

        $exception = InvalidCommandDataException::create(command: new stdClass(), message: 'test', previous: $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    #[TestDox('Implements required contracts')]
    public function testImplementsContracts(): void
    {
        $exception = InvalidCommandDataException::create(command: new stdClass(), message: 'test');

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidCommandData::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
