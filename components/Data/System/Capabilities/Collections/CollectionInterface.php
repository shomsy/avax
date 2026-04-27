<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections;

use Countable;
use IteratorAggregate;

interface CollectionInterface extends Countable, IteratorAggregate
{
    public function all(): array;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;

    public function has(int|string $key): bool;

    public function get(int|string $key, mixed $default = null): mixed;

    public function first(): mixed;

    public function last(): mixed;

    public function keys(): array;

    public function values(): array;
}