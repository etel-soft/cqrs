<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation;

use Etel\CQRS\Command\CommandBusOptions;
use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class CommandBusOptionsStamp implements StampInterface
{
    public function __construct(public CommandBusOptions $options) {}
}
