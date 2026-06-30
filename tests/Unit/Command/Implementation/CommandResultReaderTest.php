<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation;

use DateTimeImmutable;
use Etel\CQRS\Command\Implementation\CommandResultReader;
use Etel\CQRSTests\Unit\Command\Implementation\Fixture\CommandWithNullableResult;
use Etel\CQRSTests\Unit\Command\Implementation\Fixture\CommandWithResult;
use Etel\CQRSTests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionProperty;
use stdClass;

/**
 * @internal
 */
#[CoversClass(CommandResultReader::class)]
final class CommandResultReaderTest extends UnitTestCase
{
    /**
     * Reset the process-global memoisation cache so each test exercises a cold lookup (reflection), independent of
     * what other tests dispatched earlier.
     */
    protected function setUp(): void
    {
        parent::setUp();

        new ReflectionProperty(class: CommandResultReader::class, property: 'cache')
            ->setValue(objectOrValue: null, value: []);
    }

    #[Test]
    #[TestDox('Reads the type and nullability declared by a CommandResult attribute')]
    public function testReadsDeclaredResultMeta(): void
    {
        $this->assertSame(
            [DateTimeImmutable::class, false],
            CommandResultReader::read(command: new CommandWithResult())
        );
        $this->assertSame(
            [DateTimeImmutable::class, true],
            CommandResultReader::read(command: new CommandWithNullableResult())
        );
    }

    #[Test]
    #[TestDox('Returns null for a command without a CommandResult attribute')]
    public function testReturnsNullWithoutAttribute(): void
    {
        $this->assertNull(CommandResultReader::read(command: new stdClass()));
    }

    #[Test]
    #[TestDox('Returns a stable result across repeated reads of the same class (memoised)')]
    public function testMemoisesResultPerClass(): void
    {
        $first = CommandResultReader::read(command: new CommandWithResult());
        $second = CommandResultReader::read(command: new CommandWithResult());

        $this->assertSame($first, $second);
    }
}
