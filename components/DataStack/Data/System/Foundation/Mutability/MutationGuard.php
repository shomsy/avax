<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Mutability;

use Avax\Components\DataStack\Data\System\Foundation\Failure\MutationException;

/**
 * Guards immutable-first state owners against write operations.
 */
final class MutationGuard
{
    private bool $locked = false;

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function lock(): void
    {
        if ($this->locked) {
            throw MutationException::valueIsAlreadyLocked();
        }

        $this->locked = true;
    }

    public function assertWritable(): void
    {
        $this->assertMutable();
    }

    public function assertMutable(): void
    {
        if ($this->locked) {
            throw MutationException::valueIsLocked();
        }
    }

    public function getLockedState(): bool
    {
        return $this->locked;
    }
}
