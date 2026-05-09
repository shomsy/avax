<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\RingBufferStorage;
use Countable;
use Override;

final readonly class RingBuffer implements Countable
{
    public function __construct(private RingBufferStorage $storage) {}

    public static function empty(int $capacity) : self
    {
        return new self(storage: new RingBufferStorage(capacity: $capacity));
    }

    public function enqueue(mixed $value) : self
    {
        return new self(storage: $this->storage->enqueue(value: $value));
    }

    public function dequeue() : self
    {
        return new self(storage: $this->storage->dequeue());
    }

    public function front() : mixed
    {
        return $this->storage->front();
    }

    public function isFull() : bool
    {
        return $this->storage->isFull();
    }

    /**
     * @return list<mixed>
     */
    public function toArray() : array
    {
        return $this->storage->values();
    }

    public function isEmpty() : bool
    {
        return $this->storage->isEmpty();
    }

    #[Override]
    public function count() : int
    {
        return $this->storage->count();
    }
}
