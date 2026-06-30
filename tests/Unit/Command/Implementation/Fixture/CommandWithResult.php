<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Command\Implementation\Fixture;

use DateTimeImmutable;
use Etel\CQRS\Command\CommandResult;

#[CommandResult(type: DateTimeImmutable::class)]
final readonly class CommandWithResult {}
