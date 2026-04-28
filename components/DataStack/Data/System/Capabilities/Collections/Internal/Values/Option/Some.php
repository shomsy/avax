<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Values\Option;

/**
 * Present option value.
 */
final class Some extends Option
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
