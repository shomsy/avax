<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Fallback;

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
