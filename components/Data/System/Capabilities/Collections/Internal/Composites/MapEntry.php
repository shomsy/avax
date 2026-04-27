<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Composites;

/**
 * Immutable key-value entry.
 */
final readonly class MapEntry
{
    public function __construct(
        private int|string $key,
        private mixed      $value,
    ) {}

    public function key() : int|string { return $this->key; }

    public function value() : mixed { return $this->value; }
}
