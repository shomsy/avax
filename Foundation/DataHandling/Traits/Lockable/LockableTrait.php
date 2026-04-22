<?php

declare(strict_types=1);

namespace Avax\DataHandling\Traits\Lockable;

use RuntimeException;

/**
 * Provides locking mechanism for immutability enforcement.
 */
trait LockableTrait
{
    private bool $locked = false;

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function lock(): static
    {
        if ($this->locked) {
            throw new RuntimeException('Collection is already locked.');
        }

        $clone = clone $this;
        $clone->locked = true;

        return $clone;
    }

    public function locked(): static
    {
        if ($this->locked) {
            return $this;
        }

        $clone = clone $this;
        $clone->locked = true;

        return $clone;
    }

    public function toImmutable(): static
    {
        $clone = clone $this;
        $clone->locked = true;

        return $clone;
    }

    public function lockedFrom(iterable $items): static
    {
        return (new static($items))->lock();
    }

    protected function assertNotLocked(): void
    {
        if ($this->locked) {
            throw new RuntimeException(
                'Collection is locked and cannot be modified.'
            );
        }
    }
}