<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Orchestration\Kubernetes;

final class ReadinessProbe
{
    private static bool $ready = false;

    public static function markReady(): void
    {
        self::$ready = true;
    }

    public static function markNotReady(): void
    {
        self::$ready = false;
    }

    public static function isReady(): bool
    {
        return self::$ready;
    }
}