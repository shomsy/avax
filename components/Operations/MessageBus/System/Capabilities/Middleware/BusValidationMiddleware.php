<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Middleware;

use Closure;

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
