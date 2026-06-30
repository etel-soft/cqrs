<?php

declare(strict_types=1);

namespace Etel\CQRS\Command\Implementation;

use Etel\CQRS\Command\CommandResult;
use ReflectionClass;

use function array_key_exists;

/**
 * Reads and memoises the {@see CommandResult} contract declared on command classes.
 *
 * Reflection is performed at most once per command class for the lifetime of the process; subsequent dispatches of
 * the same class — including attribute-less fire-and-forget commands, whose lookup is cached as null — resolve
 * through an in-memory map without touching reflection again.
 */
final class CommandResultReader
{
    /** @var array<class-string, null|array{0: null|class-string, 1: bool}> */
    private static array $cache = [];

    /**
     * @return null|array{0: null|class-string, 1: bool} Tuple of [expected type, nullable], or null when the
     *                                                   command declares no CommandResult attribute
     */
    public static function read(object $command): ?array
    {
        $class = $command::class;

        if (array_key_exists(key: $class, array: self::$cache)) {
            return self::$cache[$class];
        }

        $meta = null;
        $attributes = new ReflectionClass(objectOrClass: $command)->getAttributes(name: CommandResult::class);

        if ($attributes !== []) {
            $attribute = $attributes[0]->newInstance();
            $meta = [$attribute->type, $attribute->nullable];
        }

        return self::$cache[$class] = $meta;
    }
}
