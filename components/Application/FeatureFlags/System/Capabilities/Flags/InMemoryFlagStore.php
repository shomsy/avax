<?php

declare(strict_types=1);

namespace Avax\Components\Application\FeatureFlags\System\Capabilities\Flags;

use Avax\Components\Application\FeatureFlags\System\PublicSurface\FlagStoreInterface;

final class InMemoryFlagStore implements FlagStoreInterface
{
    public function __construct(
        private array $flags = [],
    ) {}

    public function get(string $flag) : mixed
    {
        return $this->flags[$flag] ?? false;
    }

    public function set(string $flag, mixed $value) : void
    {
        $this->flags[$flag] = $value;
    }

    public function all() : array
    {
        return $this->flags;
    }
}