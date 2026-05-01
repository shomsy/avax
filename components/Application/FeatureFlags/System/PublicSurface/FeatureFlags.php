<?php

declare(strict_types=1);

namespace Avax\Components\Application\FeatureFlags\System\PublicSurface;

use Avax\Components\Application\FeatureFlags\System\Capabilities\Flags\FeatureFlag;
use Avax\Components\Application\FeatureFlags\System\Capabilities\Flags\InMemoryFlagStore;

interface FlagStoreInterface
{
    public function get(string $flag) : mixed;

    public function set(string $flag, mixed $value) : void;

    public function all() : array;
}

final class FeatureFlags
{
    private static FlagStoreInterface|null $flagStore = null;

    public static function setStore(FlagStoreInterface $flagStore) : void
    {
        self::$flagStore = $flagStore;
    }

    public static function enable(string $flag) : void
    {
        self::store()->set($flag, true);
    }

    private static function store() : FlagStoreInterface
    {
        if (self::$flagStore === null) {
            self::$flagStore = new InMemoryFlagStore();
        }

        return self::$flagStore;
    }

    public static function disable(string $flag) : void
    {
        self::store()->set($flag, false);
    }

    public static function enabled(string $flag) : bool
    {
        $store = self::store();
        $value = $store->get($flag);

        return $value === true || $value === 'true' || $value === '1' || $value === 1;
    }

    public static function variant(string $flag) : string
    {
        $store = self::store();

        return (string) $store->get($flag);
    }

    public static function all() : array
    {
        return self::store()->all();
    }
}