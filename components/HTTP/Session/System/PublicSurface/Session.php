<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\PublicSurface;

/**
 * Session PublicSurface — thin delegation layer to SessionScope.
 * This is the only class external code should interact with.
 */
final class Session implements SessionInterface
{
    public function __construct(
        private readonly SessionScope $scope,
    ) {}

    public function start(): bool
    {
        return $this->scope->start();
    }

    public function isStarted(): bool
    {
        return $this->scope->isStarted();
    }

    public function id(): string
    {
        return $this->scope->id();
    }

    public function has(string $key): bool
    {
        return $this->scope->has($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->scope->get($key, $default);
    }

    public function all(): array
    {
        return $this->scope->all();
    }

    public function set(string $key, mixed $value): void
    {
        $this->scope->set($key, $value);
    }

    public function forget(string $key): void
    {
        $this->scope->forget($key);
    }

    public function clear(): void
    {
        $this->scope->clear();
    }

    public function destroy(): void
    {
        $this->scope->destroy();
    }

    public function regenerate(bool $destroy = false): bool
    {
        return $this->scope->regenerate($destroy);
    }

    public function save(): void
    {
        $this->scope->save();
    }
}
