<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Values\Option;

/**
 * Represents a present value in an Option.
 */
final readonly class Some extends Option
{
    public function __construct(private mixed $value) {}

    public function isSome() : bool { return true; }

    public function unwrap() : mixed { return $this->value; }
}
