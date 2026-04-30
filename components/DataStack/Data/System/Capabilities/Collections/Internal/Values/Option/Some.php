<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Values\Option;

use Override;

/**
 * Present option value.
 */
final class Some extends Option
{
    public function __construct(
        private readonly mixed $value,
    ) {}

    #[Override]
    public function isSome() : bool
    {
        return true;
    }

    #[Override]
    public function unwrap() : mixed
    {
        return $this->value;
    }
}
