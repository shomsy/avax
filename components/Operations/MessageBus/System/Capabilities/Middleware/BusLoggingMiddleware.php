<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Middleware;

use Closure;

final readonly class BusLoggingMiddleware implements BusMiddleware
{
    public function process(object $message, Closure $next): mixed
    {
        $class = $message::class;
        $start = microtime(true);

        echo sprintf('Dispatching: %s%s', $class, PHP_EOL);

        $result = $next($message);

        $duration = (microtime(true) - $start) * 1000;

        echo "Completed: {$class} in {$duration}ms\n";

        return $result;
    }
}
