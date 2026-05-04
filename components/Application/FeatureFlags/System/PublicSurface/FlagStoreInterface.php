<?php

declare(strict_types=1);

namespace Avax\Components\Application\FeatureFlags\System\PublicSurface;

use Avax\Components\Application\FeatureFlags\System\Capabilities\Flags\InMemoryFlagStore;

interface FlagStoreInterface
{
    public function get(string $flag): mixed;

    public function set(string $flag, mixed $value): void;

    public function all(): array;
}
