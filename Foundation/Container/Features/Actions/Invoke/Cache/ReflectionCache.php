<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Invoke\Cache;

use ReflectionFunctionAbstract;

/**
 * In-memory reflection cache for callable invocation.
 *
 * @see docs_md/Features/Actions/Invoke/Cache/ReflectionCache.md#quick-summary
 */
final class ReflectionCache
{
    /** @var array<string, ReflectionFunctionAbstract> */
    private array $cache  = [];
    private bool  $locked = false;

    /**
     * @see docs_md/Features/Actions/Invoke/Cache/ReflectionCache.md#method-get
     */
    public function get(string $key): ReflectionFunctionAbstract|null
    {
        return $this->cache[$key] ?? null;
    }

    /**
     * @see docs_md/Features/Actions/Invoke/Cache/ReflectionCache.md#method-set
     */
    public function set(string $key, ReflectionFunctionAbstract $reflection): void
    {
        $this->cache[$key] = $reflection;
    }
}
