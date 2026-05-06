<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime;

use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ResettableInterface;
use Throwable;

/**
 * Stores shared runtime instances outside the scoped stack.
 */
final class DependencyPool
{
    /** @var array<string, mixed> */
    private array $items = [];

    /** @var array<string, bool> */
    private array $disposable = [];

    /** @var array<string, list<mixed>> */
    private array $pooled = [];

    /** @var array<string, array{maxSize: int, resetBeforeReuse: bool, disposable: bool}> */
    private array $pooledOptions = [];

    /** @var array{hits: int, misses: int, releases: int, overflows: int, unsafe: int} */
    private array $pooledStats
        = [
            'hits' => 0,
            'misses' => 0,
            'releases' => 0,
            'overflows' => 0,
            'unsafe' => 0,
        ];

    /**
     * Reports whether one shared instance exists.
     */
    public function has(string $abstract): bool
    {
        return array_key_exists(key: $abstract, array: $this->items);
    }

    /**
     * Returns one shared instance when available.
     */
    public function get(string $abstract): mixed
    {
        return $this->items[$abstract] ?? null;
    }

    /**
     * Stores one shared instance.
     */
    public function set(string $abstract, mixed $instance, bool $disposable = false): void
    {
        $this->items[$abstract] = $instance;
        $this->disposable[$abstract] = $disposable;
    }

    /**
     * Removes one shared instance.
     */
    public function forget(string $abstract): void
    {
        unset($this->items[$abstract]);
        unset($this->disposable[$abstract]);
    }

    /**
     * @return array{items: array<string, mixed>, disposable: array<string, bool>}
     */
    public function drain(): array
    {
        $drained = [
            'items' => $this->items,
            'disposable' => $this->disposable,
        ];

        $this->flush();

        return $drained;
    }

    /**
     * Clears all shared instances.
     */
    public function flush(): void
    {
        $this->items = [];
        $this->disposable = [];
        $this->pooled = [];
        $this->pooledOptions = [];
        $this->pooledStats = [
            'hits' => 0,
            'misses' => 0,
            'releases' => 0,
            'overflows' => 0,
            'unsafe' => 0,
        ];
    }

    /**
     * Returns the number of shared runtime instances.
     */
    public function count(): int
    {
        return count(value: $this->items);
    }

    public function hasPooled(string $abstract): bool
    {
        return ($this->pooled[$abstract] ?? []) !== [];
    }

    /**
     * @return array{hit: bool, instance: mixed}
     */
    public function checkoutPooled(string $abstract): array
    {
        $bucket = $this->pooled[$abstract] ?? [];
        if ($bucket === []) {
            $this->pooledStats['misses']++;

            return [
                'hit' => false,
                'instance' => null,
            ];
        }

        $instance = array_pop(array: $bucket);
        $this->pooled[$abstract] = $bucket;
        if ($bucket === []) {
            unset($this->pooled[$abstract]);
        }

        $this->pooledStats['hits']++;

        return [
            'hit' => true,
            'instance' => $instance,
        ];
    }

    /**
     * @return array{returned: bool, overflow: bool, unsafe: bool, reason: string}
     */
    public function releasePooled(
        string $abstract,
        mixed $instance,
        int $maxSize,
        ?bool $resetBeforeReuse = null,
        bool $disposable = false,
    ): array {
        $resetBeforeReuse ??= true;
        $this->pooledOptions[$abstract] = [
            'maxSize' => max(1, $maxSize),
            'resetBeforeReuse' => $resetBeforeReuse,
            'disposable' => $disposable,
        ];

        if (! is_object(value: $instance)) {
            $this->pooledStats['unsafe']++;

            return [
                'returned' => false,
                'overflow' => false,
                'unsafe' => true,
                'reason' => 'only objects can participate in pooled lifetime reuse',
            ];
        }

        if ($resetBeforeReuse) {
            if (! $instance instanceof ResettableInterface) {
                $this->pooledStats['unsafe']++;

                return [
                    'returned' => false,
                    'overflow' => false,
                    'unsafe' => true,
                    'reason' => 'pooled service does not implement ResettableInterface',
                ];
            }

            try {
                $instance->reset();
            } catch (Throwable) {
                $this->pooledStats['unsafe']++;

                return [
                    'returned' => false,
                    'overflow' => false,
                    'unsafe' => true,
                    'reason' => 'pooled service failed during reset()',
                ];
            }
        }

        $bucket = $this->pooled[$abstract] ?? [];
        if (count(value: $bucket) >= max(1, $maxSize)) {
            $this->pooledStats['overflows']++;

            return [
                'returned' => false,
                'overflow' => true,
                'unsafe' => false,
                'reason' => 'pooled bucket is already at max size',
            ];
        }

        $bucket[] = $instance;
        $this->pooled[$abstract] = $bucket;
        $this->pooledStats['releases']++;

        return [
            'returned' => true,
            'overflow' => false,
            'unsafe' => false,
            'reason' => 'pooled service returned to the available bucket',
        ];
    }

    public function pooledCount(string $abstract = ''): int
    {
        if ($abstract !== '') {
            return count(value: $this->pooled[$abstract] ?? []);
        }

        return array_sum(array: array_map(
            callback: static fn (array $bucket): int => count(value: $bucket),
            array   : $this->pooled,
        ));
    }

    /**
     * @return list<string>
     */
    public function ids(): array
    {
        $ids = array_keys(array: $this->items);
        sort(array: $ids);

        return $ids;
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        return $this->items;
    }

    /**
     * @return array<string, bool>
     */
    public function disposableMap(): array
    {
        return $this->disposable;
    }

    /**
     * @return array<string, list<string>>
     */
    public function pooledSnapshot(): array
    {
        $snapshot = [];

        foreach ($this->pooled as $serviceId => $bucket) {
            $snapshot[$serviceId] = array_map(
                callback: get_debug_type(...),
                array   : $bucket,
            );
        }

        ksort(array: $snapshot);

        return $snapshot;
    }

    /**
     * @return array{hits: int, misses: int, releases: int, overflows: int, unsafe: int}
     */
    public function pooledStats(): array
    {
        return $this->pooledStats;
    }

    /**
     * @return array<string, array{maxSize: int, resetBeforeReuse: bool, disposable: bool}>
     */
    public function pooledOptions(): array
    {
        $options = $this->pooledOptions;
        ksort(array: $options);

        return $options;
    }

    /**
     * @return array{
     *     items: array<string, list<mixed>>,
     *     options: array<string, array{maxSize: int, resetBeforeReuse: bool, disposable: bool}>
     * }
     */
    public function drainPooled(): array
    {
        $drained = [
            'items' => $this->pooled,
            'options' => $this->pooledOptions,
        ];

        $this->pooled = [];
        $this->pooledOptions = [];

        return $drained;
    }
}
