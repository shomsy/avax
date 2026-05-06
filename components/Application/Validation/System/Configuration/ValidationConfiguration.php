<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Configuration;

final readonly class ValidationConfiguration
{
    public function __construct(
        public bool $stopOnFirstFailure = false,
        public bool $trimStrings = true,
    ) {
    }
}
