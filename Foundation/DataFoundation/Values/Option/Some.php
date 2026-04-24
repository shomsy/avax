<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Values\Option;

/**
 * Present option value.
 */
final readonly class Some extends Option
{
    public function __construct(
        private mixed $value,
    ) {}

    public function isSome() : bool
    {
        return true;
    }

    public function unwrap() : mixed
    {
        return $this->value;
    }
}
