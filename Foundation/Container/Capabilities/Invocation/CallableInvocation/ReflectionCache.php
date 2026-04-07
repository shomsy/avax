<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Invocation\CallableInvocation;

use ReflectionFunctionAbstract;

/**
 * In-memory reflection cache for callable invocation.
 *
 */
final class ReflectionCache
{
    /** @var array<string, ReflectionFunctionAbstract> */
    private array $cache = [];

    private bool $locked = false;

    /**
     */
    public function get(string $key) : ReflectionFunctionAbstract|null
    {
        return $this->cache[$key] ?? null;
    }

    /**
     */
    public function set(string $key, ReflectionFunctionAbstract $reflection) : void
    {
        $this->cache[$key] = $reflection;
    }
}
