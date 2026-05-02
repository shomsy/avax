<?php

declare(strict_types=1);

namespace Avax\Components\GracefulShutdown\System\PublicSurface;

use Avax\Components\GracefulShutdown\System\Capabilities\ShutdownSequence;
use Closure;

final readonly class GracefulShutdown
{
    public static function install(int $timeoutSeconds = 30): void
    {
        if (! extension_loaded('pcntl')) {
            return;
        }

        pcntl_signal(SIGTERM, static fn () => ShutdownSequence::execute($timeoutSeconds));
        pcntl_signal(SIGINT, static fn () => ShutdownSequence::execute($timeoutSeconds));
    }

    public static function sequence(): ShutdownSequence
    {
        return new ShutdownSequence();
    }

    public static function onShutdown(Closure $callback): void
    {
        ShutdownSequence::register($callback);
    }
}
