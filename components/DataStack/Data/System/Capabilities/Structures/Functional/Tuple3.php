<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Functional;

/**
 * Fixed-size three-slot tuple.
 */
final readonly class Tuple3
{
    public function __construct(
        private mixed $first,
        private mixed $second,
        private mixed $third,
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

    public function third(): mixed
    {
        return $this->third;
    }

    /** @return array<mixed> */
    public function toArray(): array
    {
        return [$this->first, $this->second, $this->third];
    }
}
