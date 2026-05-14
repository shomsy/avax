<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

/**
 * CompiledPolicyCache — Static in-memory cache for compiled failure policies.
 *
 * Follows the DataShape static cache pattern: reflection happens once at compile time,
 * then the compiled policy is cached in memory for subsequent requests.
 */
final class CompiledPolicyCache
{
    /** @var array<string, CompiledMethodPolicy> */
    private static array $policies = [];

    public static function get(string $key): CompiledMethodPolicy|null
    {
        if (!isset(self::$policies[$key])) {
            return null;
        }
        $policy = self::$policies[$key];
        if ($policy->isStale()) {
            unset(self::$policies[$key]);
            return null;
        }
        return $policy;
    }

    public static function put(string $key, CompiledMethodPolicy $policy): void
    {
        self::$policies[$key] = $policy;
    }

    public static function invalidate(string $key): void
    {
        unset(self::$policies[$key]);
    }

    public static function clear(): void
    {
        self::$policies = [];
    }

    /**
     * @return array<string, CompiledMethodPolicy>
     */
    public static function all(): array
    {
        return self::$policies;
    }
}
