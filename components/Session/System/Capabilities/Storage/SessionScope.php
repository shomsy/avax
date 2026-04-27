<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Capabilities\Storage;

final class SessionScope
{
    /**
     * @var array<string, mixed>
     */
    private array $data;

    public function __construct(
        private readonly SessionId $id,
        array $data = [],
    ) {
        $this->data = $data;
    }

    public function id(): SessionId
    {
        return $this->id;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function read(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function write(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }
}