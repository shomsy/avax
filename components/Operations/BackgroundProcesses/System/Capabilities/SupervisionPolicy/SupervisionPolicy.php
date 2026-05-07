<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\Capabilities\SupervisionPolicy;

final class SupervisionPolicy
{
    public const STRATEGY_ALWAYS         = 'always';
    public const STRATEGY_ON_FAILURE     = 'on_failure';
    public const STRATEGY_UNLESS_STOPPED = 'unless_stopped';
    public const STRATEGY_NEVER          = 'never';

    public function shouldRestart(string $strategy, int $exitCode, int $restartCount, int $maxRestarts) : bool
    {
        return match ($strategy) {
            self::STRATEGY_ALWAYS         => $restartCount < $maxRestarts,
            self::STRATEGY_ON_FAILURE     => $exitCode !== 0 && $restartCount < $maxRestarts,
            self::STRATEGY_UNLESS_STOPPED => $restartCount < $maxRestarts,
            self::STRATEGY_NEVER          => false,
            default                       => false,
        };
    }
}
