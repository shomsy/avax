<?php

declare(strict_types=1);

namespace Avax\Components\Performance\System\Capabilities\QueryCaching;

use Closure;

final class QueryCache
{
    /** @var array<string, array{value:mixed,expires_at:int|null}> */
    private array $items = [];

    public function remember(string $key, Closure $query, ?int $ttlSeconds = 60): mixed
    {
        $cached = $this->items[$key] ?? null;

        if ($cached !== null && ($cached['expires_at'] === null || $cached['expires_at'] >= time())) {
            return $cached['value'];
        }

        $value = $query();
        $this->items[$key] = [
            'value' => $value,
            'expires_at' => $ttlSeconds === null ? null : time() + $ttlSeconds,
        ];

        return $value;
    }

    public function forget(string $key): void
    {
        unset($this->items[$key]);
    }
}
