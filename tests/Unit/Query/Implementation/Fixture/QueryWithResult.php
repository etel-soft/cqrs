<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit\Query\Implementation\Fixture;

use DateTimeImmutable;
use Etel\CQRS\Query\QueryResult;

#[QueryResult(type: DateTimeImmutable::class)]
final readonly class QueryWithResult {}
