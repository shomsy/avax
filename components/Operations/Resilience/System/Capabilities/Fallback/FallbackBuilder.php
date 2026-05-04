<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Fallback;

final readonly class FallbackBuilder
{
    public function __construct(
        private string $dependency,
    )
    {
    }

    public function when(): self
    {
        return $this;
    }

    public function use(string $fallbackClass): void
    {
        $fallbackStrategyItem = new FallbackStrategyItem($this->dependency, $fallbackClass);
        Fallback::register($this->dependency, $fallbackStrategyItem);
    }
}
