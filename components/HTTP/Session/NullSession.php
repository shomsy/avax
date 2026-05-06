<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session;

/**
 * Null session implementation — no-op session for contexts where
 * session state is not available or not needed.
 */
final class NullSession
{
    public function start(): bool
    {
        return false;
    }

    public function isStarted(): bool
    {
        return false;
    }

    public function id(): string
    {
        return '';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    public function has(string $key): bool
    {
        return false;
    }

    public function regenerate(bool $destroy = false): bool
    {
        return false;
    }

    public function all(): array
    {
        return [];
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return $default;
    }
}
