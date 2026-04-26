<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use InvalidArgumentException;
use Stringable;

final readonly class CacheNodeId implements Stringable
{
    public function __construct(
        public string $id
    )
    {
        if ($id === '') {
            throw new InvalidArgumentException(message: 'Node ID cannot be empty');
        }
    }

    public static function from(string $id) : self
    {
        return new self(id: $id);
    }

    public static function random() : self
    {
        return new self(id: uniqid(more_entropy: true));
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return $this->id;
    }
}