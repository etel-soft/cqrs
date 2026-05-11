<?php

declare(strict_types=1);

namespace Etel\CQRS\Event;

use Attribute;

/**
 * Marks a class as an event handler.
 *
 * This attribute is not required to be used, but it makes it easier to configure handlers in application.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AsEventHandler {}
