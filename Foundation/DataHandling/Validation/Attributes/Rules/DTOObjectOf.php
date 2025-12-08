<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class DTOObjectOf
{
    public function __construct(public string $class) {}

    public function apply(array|object|null $value) : AbstractDTO|null
    {
        if (is_null($value)) {
            return null;
        }

        return new ($this->class)((array) $value);
    }
}
