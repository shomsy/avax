<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\System\PublicSurface;

use Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\System\Capabilities\Keys\IdempotencyStore;
use Closure;

final class Idempotency
{
    private static IdempotencyStore $store;

    public static function check(string $key) : bool
    {
        return self::store()->has($key);
    }

    private static function store() : IdempotencyStore
    {
        if (! isset(self::$store)) {
            self::$store = new InMemoryIdempotencyStore;
        }

        return self::$store;
    }

    public static function record(string $key, array $response) : void
    {
        $ttl = self::getTtl();
        self::store()->set($key, $response, $ttl);
    }

    private static function getTtl() : int
    {
        $ttl = getenv('IDEMPOTENCY_TTL') ?: '86400';

        return (int) $ttl;
    }

    public static function replay(string $key) : ?array
    {
        return self::store()->get($key);
    }

    public static function generate() : string
    {
        return bin2hex(random_bytes(16));
    }

    public static function fromHeader(string $header) : ?string
    {
        return $header ?: null;
    }
}

final class IdempotencyMiddleware
{
    private string $headerName;

    public function __construct(string $headerName = 'Idempotency-Key')
    {
        $this->headerName = $headerName;
    }

    public function handle(object $request, Closure $next) : mixed
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
