<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Orchestration\Kubernetes;

final class LivenessProbe
{
    private static bool $alive = true;

    public static function isAlive(): bool
    {
        return self::$alive;
    }

    public static function markDead(): void
    {
        self::$alive = false;
    }
}