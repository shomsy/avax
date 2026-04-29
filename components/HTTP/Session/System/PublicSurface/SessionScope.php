<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\PublicSurface;

final class SessionScope
{
    public function start(): bool { return true; }
    public function get(string $key, mixed $default = null): mixed { return $default; }
    public function set(string $key, mixed $value): void {}
    public function forget(string $key): void {}
    public function clear(): void {}
    public function destroy(): void {}
    public function regenerate(bool $destroy = false): bool { return true; }
}
