<?php

declare(strict_types=1);

namespace Avax\Components\Application\FeatureFlags\System\PublicSurface;

interface FlagStoreInterface
{
    public function get(string $flag): mixed;

    public function set(string $flag, mixed $value): void;

    /** @return array<string, mixed> */
    public function all(): array;
}
