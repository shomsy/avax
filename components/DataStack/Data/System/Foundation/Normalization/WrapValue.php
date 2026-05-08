<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Normalization;

final readonly class WrapValue
{
    /**
     * @return array<array-key, mixed>
     */
    public function intoArray(mixed $value): array
    {
        return match (true) {
            is_array(value: $value) => $value,
            default => [$value],
        };
    }
}
