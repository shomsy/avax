<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RequestScope;

final class RequestScope implements RequestScopeInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $values = [];

    private bool $open = true;

    public function __construct(private readonly RequestScopeId $id)
    {
    }

    public function id(): RequestScopeId
    {
        return $this->id;
    }

    public function isOpen(): bool
    {
        return $this->open;
    }

    public function has(string $key): bool
    {
        $this->guardOpen();

        return array_key_exists($key, $this->values);
    }

    public function read(string $key): mixed
    {
        $this->guardOpen();

        return $this->values[$key] ?? null;
    }

    public function write(string $key, mixed $value): void
    {
        $this->guardOpen();
        $this->values[$key] = $value;
    }

    public function remove(string $key): void
    {
        $this->guardOpen();
        unset($this->values[$key]);
    }

    public function all(): array
    {
        $this->guardOpen();

        return $this->values;
    }

    public function close(): void
    {
        $this->guardOpen();
        $this->open   = false;
        $this->values = [];
    }

    private function guardOpen(): void
    {
        if (! $this->open) {
            throw new RequestScopeAlreadyClosed(message: 'Request scope is already closed.');
        }
    }
}
