<?php

declare(strict_types=1);

namespace Etel\CQRSTests\Unit;

use Etel\PHPUnitDouble\Helper\MockHelperTrait;
use Etel\PHPUnitDouble\Helper\StubHelperTrait;
use PHPUnit\Framework\TestCase;

/**
 * Base test case for all unit tests in library.
 */
abstract class UnitTestCase extends TestCase
{
    use MockHelperTrait;
    use StubHelperTrait;
}
