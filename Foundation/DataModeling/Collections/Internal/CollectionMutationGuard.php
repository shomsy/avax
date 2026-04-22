<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Internal;

use RuntimeException;

/**
 * Manages collection mutability policy.
 */
final class CollectionMutationGuard
{
    private bool $locked = false;

    public function isLocked() : bool
    {
        return $this->locked;
    }

    public function lock() : void
    {
        if ($this->locked) {
            throw new RuntimeException(message: 'Collection is already locked.');
        }

        $this->locked = true;
    }

    public function assertMutable() : void
    {
        if ($this->locked) {
            throw new RuntimeException(message: 'Collection is locked and cannot be modified.');
        }
    }

    public function getLockedState() : bool
    {
        return $this->locked;
    }
}