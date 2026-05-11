<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation;

use Etel\CQRS\Query\QueryBusOptions;
use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class QueryBusOptionsStamp implements StampInterface
{
    public function __construct(public QueryBusOptions $options) {}
}
