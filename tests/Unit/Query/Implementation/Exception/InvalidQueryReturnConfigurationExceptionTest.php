<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation\Exception;

use Etel\CQRS\Query\Exception\InvalidQueryReturnConfiguration;
use Etel\CQRS\Query\Implementation\Exception\InvalidQueryReturnConfigurationException;
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
#[CoversClass(InvalidQueryReturnConfigurationException::class)]
final class InvalidQueryReturnConfigurationExceptionTest extends UnitTestCase
{
    #[Test]
    #[TestDox('Formats message with query class name')]
    public function testMessage(): void
    {
        $exception = InvalidQueryReturnConfigurationException::create(query: new stdClass());

        $this->assertSame(
            'Query "stdClass" was handled zero times (asynchronous?) but bus require a result from handler.',
            $exception->getMessage()
        );
    }

    #[Test]
    #[TestDox('Implements required contracts')]
    public function testImplementsContracts(): void
    {
        $exception = InvalidQueryReturnConfigurationException::create(query: new stdClass());

        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidQueryReturnConfiguration::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        /* @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
