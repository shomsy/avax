<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Middleware;

use Closure;

interface BusMiddleware
{
    public function process(object $message, Closure $next): mixed;
}

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

final readonly class BusValidationMiddleware implements BusMiddleware
{
    public function __construct(private object $validator)
    {
    }

    public function process(object $message, Closure $next): mixed
    {
        if (method_exists($this->validator, 'validate')) {
            $this->validator->validate($message);
        }

        return $next($message);
    }
}

final readonly class BusTransactionMiddleware implements BusMiddleware
{
    public function process(object $message, Closure $next): mixed
    {
        // commit transaction here
        return $next($message);
    }
}
