<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\PublicSurface;

use Avax\Components\Session\System\Capabilities\Storage\SessionStore;

interface SessionInterface
{
    public function start(string $sessionId): void;

    public function has(string $key): bool;

    public function get(string $key): mixed;

    public function set(string $key, mixed $value): void;

    public function forget(string $key): void;

    public function flush(): void;

    public function regenerate(): string;

    public function destroy(): void;
}