<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal;

final readonly class WrapValue
{
    public function intoArray(mixed $value): array
    {
        return match (true) {
            is_array(value: $value) => $value,
            default => [$value],
        };
    }
}
