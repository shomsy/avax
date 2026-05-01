<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime;

/**
 * Package-owned session storage contract.
 */
interface SessionStoreInterface
{
    public function start(): void;

    public function regenerate(): string;

    public function id(): ?string;

    public function get(string $key): mixed;

    public function put(string $key, mixed $value): void;

    public function forget(string $key): void;

    public function invalidate(): void;
}
