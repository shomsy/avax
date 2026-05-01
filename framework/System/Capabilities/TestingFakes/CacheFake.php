<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\TestingFakes;

/**
 * Fake cache repository for testing.
 *
 * Stores values in memory and records all operations.
 */
final class CacheFake
{
    /**
     * @var array<string, mixed>
     */
    private array $store = [];

    /**
     * @var list<string>
     */
    private array $getCalls = [];

    /**
     * @var list<array{key: string, value: mixed, ttl: int|null}>
     */
    private array $setCalls = [];

    /**
     * @var list<string>
     */
    private array $deleteCalls = [];

    public function get(string $key, mixed $default = null): mixed
    {
        $this->getCalls[] = $key;

        return $this->store[$key] ?? $default;
    }

    public function set(string $key, mixed $value, int $ttl = null): void
    {
        $this->setCalls[]  = ['key' => $key, 'value' => $value, 'ttl' => $ttl];
        $this->store[$key] = $value;
    }

    public function forget(string $key): void
    {
        $this->deleteCalls[] = $key;
        unset($this->store[$key]);
    }

    public function clear(): void
    {
        $this->store       = [];
        $this->getCalls    = [];
        $this->setCalls    = [];
        $this->deleteCalls = [];
    }

    public function has(string $key): bool
    {
        $this->getCalls[] = $key;

        return array_key_exists($key, $this->store);
    }

    public function assertGet(string $key): self
    {
        if (! in_array($key, $this->getCalls, true)) {
            throw new TestingFakeException(
                sprintf("Cache::get('%s') was not called", $key),
            );
        }

        return $this;
    }

    public function assertSet(string $key, mixed $value = null): self
    {
        foreach ($this->setCalls as $call) {
            if ($call['key'] === $key && ($value === null || $call['value'] === $value)) {
                return $this;
            }
        }

        throw new TestingFakeException(
            sprintf(
                "Cache::set('%s'%s) was not called",
                $key,
                $value !== null ? ', ' . var_export($value, true) : '',
            ),
        );
    }

    public function assertForget(string $key): self
    {
        if (! in_array($key, $this->deleteCalls, true)) {
            throw new TestingFakeException(
                sprintf("Cache::forget('%s') was not called", $key),
            );
        }

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getCalls(): array
    {
        return $this->getCalls;
    }

    /**
     * @return list<array{key: string, value: mixed, ttl: int|null}>
     */
    public function setCalls(): array
    {
        return $this->setCalls;
    }

    /**
     * @return list<string>
     */
    public function deleteCalls(): array
    {
        return $this->deleteCalls;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->store;
    }
}
