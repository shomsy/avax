<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\PublicSurface;

final class Session implements SessionInterface
{
    public function __construct(
        private SessionScope $scope
    ) {}

    public function start(): bool { return $this->scope->start(); }
    public function get(string $key, mixed $default = null): mixed { return $this->scope->get($key, $default); }
    public function set(string $key, mixed $value): void { $this->scope->set($key, $value); }
    public function forget(string $key): void { $this->scope->forget($key); }
    public function clear(): void { $this->scope->clear(); }
    public function destroy(): void { $this->scope->destroy(); }
    public function regenerate(bool $destroy = false): bool { return $this->scope->regenerate($destroy); }
}
