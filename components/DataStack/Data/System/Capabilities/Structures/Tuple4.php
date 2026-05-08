<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures;

/**
 * Fixed-size four-slot tuple.
 */
final readonly class Tuple4
{
    public function __construct(
        private mixed $first,
        private mixed $second,
        private mixed $third,
        private mixed $fourth,
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

    public function fourth(): mixed
    {
        return $this->fourth;
    }

    /** @return array<mixed> */
    public function toArray(): array
    {
        return [$this->first, $this->second, $this->third, $this->fourth];
    }
}
