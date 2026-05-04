<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation\Exception;

use Etel\CQRS\Command\Exception\InvalidCommandReturnConfiguration;
use Etel\CQRS\Command\Implementation\Exception\InvalidCommandReturnConfigurationException;
use Etel\CQRSTests\Unit\UnitTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

/**
 * @internal
 */
#[CoversClass(InvalidCommandReturnConfigurationException::class)]
final class InvalidCommandReturnConfigurationExceptionTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Formats message with command class name')]
    public function testMessage(): void
    {
        $exception = InvalidCommandReturnConfigurationException::create(command: new stdClass());

        $this->assertSame(
            'Command "stdClass" was handled zero times (asynchronous?) but bus require a result from handler.',
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Implements required contracts')]
    public function testImplementsContracts(): void
    {
        $exception = InvalidCommandReturnConfigurationException::create(command: new stdClass());

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidCommandReturnConfiguration::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
