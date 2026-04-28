<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Create;

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
