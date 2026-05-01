<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Middleware;

use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;
use Avax\Components\Operations\MessageBus\System\PublicSurface\DomainEvent;
use Avax\Components\Operations\MessageBus\System\PublicSurface\Query;
use Closure;
use Throwable;

interface BusMiddleware
{
    public function process(object $message, Closure $next) : mixed;
}

final readonly class BusLoggingMiddleware implements BusMiddleware
{
    public function process(object $message, Closure $next) : mixed
    {
        $class = $message::class;
        $start = microtime(true);

        echo "Dispatching: {$class}\n";

        $result = $next($message);

        $duration = (microtime(true) - $start) * 1000;

        echo "Completed: {$class} in {$duration}ms\n";

        return $result;
    }
}

final readonly class BusValidationMiddleware implements BusMiddleware
{
    private object $validator;

    public function __construct(object $validator)
    {
        $this->validator = $validator;
    }

    public function process(object $message, Closure $next) : mixed
    {
        if (method_exists($this->validator, 'validate')) {
            $this->validator->validate($message);
        }

        return $next($message);
    }
}

final readonly class BusTransactionMiddleware implements BusMiddleware
{
    public function process(object $message, Closure $next) : mixed
    {
        try {
            $result = $next($message);

            // commit transaction here

            return $result;
        } catch (Throwable $e) {
            // rollback transaction here

            throw $e;
        }
    }
}