<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Functional;

/**
 * Fixed-size two-slot tuple.
 */
final readonly class Tuple2
{
    public function __construct(
        private mixed $first,
        private mixed $second,
    ) {
    }

    public function first(): mixed
    {
        return $this->first;
    }

    public function second(): mixed
    {
        return $this->second;
    }

    /** @return array<mixed> */
    public function toArray(): array
    {
        return [$this->first, $this->second];
    }
}
