<?php

declare(strict_types=1);

namespace Etel\CQRS\Query\Implementation;

use Etel\CQRS\Query\QueryResult;
use ReflectionClass;

use function array_key_exists;

/**
 * Reads and memoises the {@see QueryResult} contract declared on query classes.
 *
 * Reflection is performed at most once per query class for the lifetime of the process; subsequent dispatches of the
 * same class — including queries without the attribute, whose lookup is cached as null — resolve through an in-memory
 * map without touching reflection again.
 */
final class QueryResultReader
{
    /** @var array<class-string, null|array{0: null|class-string, 1: bool}> */
    private static array $cache = [];

    /**
     * @return null|array{0: null|class-string, 1: bool} Tuple of [expected type, nullable], or null when the
     *                                                   query declares no QueryResult attribute
     */
    public static function read(object $query): ?array
    {
        $class = $query::class;

        if (array_key_exists(key: $class, array: self::$cache)) {
            return self::$cache[$class];
        }

        $meta = null;
        $attributes = new ReflectionClass(objectOrClass: $query)->getAttributes(name: QueryResult::class);

        if ($attributes !== []) {
            $attribute = $attributes[0]->newInstance();
            $meta = [$attribute->type, $attribute->nullable];
        }

        return self::$cache[$class] = $meta;
    }
}
