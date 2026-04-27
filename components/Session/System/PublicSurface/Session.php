<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\PublicSurface;

use Avax\Components\Session\System\Capabilities\Storage\SessionStore;

final readonly class Session implements SessionInterface
{
    public function __construct(
        private SessionStore $store,
    ) {
    }

    public function start(string $sessionId): void
    {
        $this->store->open(sessionId: $sessionId);
    }

    public function has(string $key): bool
    {
        return $this->store->current()->has(key: $key);
    }

    public function get(string $key): mixed
    {
        return $this->store->current()->read(key: $key);
    }

    public function set(string $key, mixed $value): void
    {
        $this->store->current()->write(key: $key, value: $value);
    }

    public function forget(string $key): void
    {
        $this->store->current()->remove(key: $key);
    }

    public function flush(): void
    {
        $this->store->current()->clear();
    }

    public function regenerate(): string
    {
        $oldId = $this->store->current()->id()->toString();
        $this->store->regenerate(oldSessionId: $oldId);

        return $this->store->current()->id()->toString();
    }

    public function destroy(): void
    {
        $this->store->destroy();
    }
}