<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Idempotency;

use Closure;

final readonly class IdempotencyMiddleware
{
    public function __construct(private string $headerName = 'Idempotency-Key')
    {
    }

    public function handle(object $request, Closure $next): mixed
    {
        $key = $request->getHeader($this->headerName) ?: Idempotency::generate();

        if (Idempotency::check($key)) {
            return Idempotency::replay($key);
        }

        $response = $next($request);

        Idempotency::record($key, (array) $response);

        return $response;
    }
}
