<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Middleware;

use Closure;

final readonly class BusTransactionMiddleware implements BusMiddleware
{
    public function process(object $message, Closure $next): mixed
    {
        // commit transaction here
        return $next($message);
    }
}
