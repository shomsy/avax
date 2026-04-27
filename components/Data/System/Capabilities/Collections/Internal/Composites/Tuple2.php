<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Composites;

/**
 * Immutable 2-element tuple.
 */
final readonly class Tuple2
{
    public function __construct(public mixed $v1, public mixed $v2) {}

    public function toArray() : array { return [$this->v1, $this->v2]; }
}
