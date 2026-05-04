<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Fallback;

final class FallbackStrategyItem
{
    public function __construct(
        public string $dependency,
        public string $fallbackClass,
        public bool $active = false,
    ) {
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(): void
    {
        $this->active = true;
    }

    public function deactivate(): void
    {
        $this->active = false;
    }
}
