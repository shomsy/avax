<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\PublicSurface;

interface SessionInterface
{
    public function start(): bool;
    public function isStarted(): bool;
    public function id(): string;
    public function has(string $key): bool;
    public function get(string $key, mixed $default = null): mixed;
    public function all(): array;
    public function set(string $key, mixed $value): void;
    public function forget(string $key): void;
    public function clear(): void;
    public function destroy(): void;
    public function regenerate(bool $destroy = false): bool;
    public function save(): void;
}
