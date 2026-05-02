<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Fallback\System\PublicSurface;

use RuntimeException;
use Throwable;

final class Fallback
{
    private static array $strategies = [];

    public static function register(string $dependency, FallbackStrategyItem $fallbackStrategyItem) : void
    {
        self::$strategies[$dependency] = $fallbackStrategyItem;
    }

    public static function for(string $dependency): FallbackBuilder
    {
        return new FallbackBuilder($dependency);
    }

    public static function isDegraded(string $dependency): bool
    {
        $strategy = self::$strategies[$dependency] ?? null;

        return $strategy?->isActive() ?? false;
    }

    public static function markDegraded(string $dependency): void
    {
        $strategy = self::$strategies[$dependency] ?? null;
        $strategy?->activate();
    }

    public static function recover(string $dependency): void
    {
        $strategy = self::$strategies[$dependency] ?? null;
        $strategy?->deactivate();
    }

    public static function execute(array $fallbacks): mixed
    {
        $lastException = null;

        foreach ($fallbacks as $fallback) {
            try {
                return $fallback();
            } catch (Throwable $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?? new RuntimeException('All fallbacks failed');
    }
}

final readonly class FallbackBuilder
{
    public function __construct(
        private string $dependency,
    ) {
    }

    public function when() : self
    {
        return $this;
    }

    public function use(string $fallbackClass): void
    {
        $fallbackStrategyItem = new FallbackStrategyItem($this->dependency, $fallbackClass);
        Fallback::register($this->dependency, $fallbackStrategyItem);
    }
}

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
