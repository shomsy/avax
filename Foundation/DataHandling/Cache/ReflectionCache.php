<?php

declare(strict_types=1);

namespace Avax\DataHandling\Cache;

use ReflectionClass;
use ReflectionException;

class ReflectionCache
{
    private static array $reflectionCache = [];

    /**
     * @throws ReflectionException
     */
    public static function getReflectionClass(string $dtoClass) : ReflectionClass
    {
        return self::$reflectionCache[$dtoClass] ??= new ReflectionClass(objectOrClass: $dtoClass);
    }
}
