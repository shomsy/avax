<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Create;

final readonly class WrapValue
{
    public function intoArray(mixed $value) : array
    {
        return match (true) {
            is_array(value: $value) => $value,
            default                 => [$value],
        };
    }
}
