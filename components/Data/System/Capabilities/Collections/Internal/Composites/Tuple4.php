<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Composites;

/**
 * Immutable 4-element tuple.
 */
final readonly class Tuple4
{
    public function __construct(public mixed $v1, public mixed $v2, public mixed $v3, public mixed $v4) {}

    public function toArray() : array { return [$this->v1, $this->v2, $this->v3, $this->v4]; }
}
