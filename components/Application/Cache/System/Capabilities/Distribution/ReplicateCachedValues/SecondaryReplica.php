<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

final readonly class SecondaryReplica
{
    public function __construct(
        public int $index,
        public int $priority = 1
    ) {}

    public static function fromIndex(int $index) : self
    {
        return new self(index: $index);
    }

    public function withPriority(int $priority) : self
    {
        return new self(index: $this->index, priority: $priority);
    }

    public function isBefore(self $other) : bool
    {
        return $this->priority > $other->priority
            || ($this->priority === $other->priority && $this->index < $other->index);
    }
}