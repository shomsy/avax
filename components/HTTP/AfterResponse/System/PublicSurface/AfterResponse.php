<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\AfterResponse\System\PublicSurface;

use Avax\Components\HTTP\AfterResponse\System\Capabilities\Tasks\AfterResponseQueue;
use Avax\Components\HTTP\AfterResponse\System\Capabilities\Tasks\AfterResponseTask;
use Closure;

final class AfterResponse
{
    private static AfterResponseQueue $afterResponseQueue;

    public static function run(Closure $task): void
    {
        self::queue()->enqueue(new AfterResponseTask($task));
    }

    private static function queue(): AfterResponseQueue
    {
        if (! isset(self::$afterResponseQueue)) {
            self::$afterResponseQueue = new AfterResponseQueue();
        }

        return self::$afterResponseQueue;
    }

    public static function execute(): void
    {
        self::queue()->execute();
    }

    public static function isEmpty(): bool
    {
        return self::queue()->isEmpty();
    }

    public static function count(): int
    {
        return self::queue()->count();
    }
}
