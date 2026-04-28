<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RequestScope;

interface RequestScopeInterface
{
    public function id(): RequestScopeId;

    public function isOpen(): bool;

    public function has(string $key): bool;

    public function read(string $key): mixed;

    public function write(string $key, mixed $value): void;

    public function remove(string $key): void;

    /**
     * @return array<string, mixed>
     */
    public function all(): array;

    public function close(): void;
}
