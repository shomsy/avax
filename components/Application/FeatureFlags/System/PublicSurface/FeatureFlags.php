<?php

declare(strict_types=1);

namespace Avax\Components\Application\FeatureFlags\System\PublicSurface;

use Avax\Components\Application\FeatureFlags\System\Capabilities\Flags\InMemoryFlagStore;

final class FeatureFlags
{
    private static ?FlagStoreInterface $flagStore = null;

    public static function setStore(FlagStoreInterface $flagStore): void
    {
        self::$flagStore = $flagStore;
    }

    /**
     * Reset the feature flags store to null.
     *
     * Required for long-lived worker safety: prevents flag state from leaking
     * across requests in FrankenPHP, RoadRunner, Swoole, and similar runtimes.
     */
    public static function reset(): void
    {
        self::$flagStore = null;
    }

    public static function enable(string $flag): void
    {
        self::store()->set($flag, true);
    }

    private static function store(): FlagStoreInterface
    {
        if (! self::$flagStore instanceof FlagStoreInterface) {
            self::$flagStore = new InMemoryFlagStore();
        }

        return self::$flagStore;
    }

    public static function disable(string $flag): void
    {
        self::store()->set($flag, false);
    }

    public static function enabled(string $flag): bool
    {
        $store = self::store();
        $value = $store->get($flag);

        return in_array($value, [true, 'true', '1', 1], true);
    }

    public static function variant(string $flag): string
    {
        $store = self::store();

        return (string) $store->get($flag);
    }

    public static function all(): array
    {
        return self::store()->all();
    }
}
