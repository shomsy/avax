<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Trimmed
{
    /**
     * Trims the given value if it is a string.
     *
     * @param mixed $value The value to trim.
     *
     * @return mixed The trimmed value.
     */
    public function apply(mixed $value) : mixed
    {
        return is_string($value) ? trim($value) : $value;
    }
}

