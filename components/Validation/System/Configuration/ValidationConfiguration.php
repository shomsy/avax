<?php

declare(strict_types=1);

namespace Avax\Components\Validation\System\Configuration;

final class ValidationConfiguration
{
    public function __construct(
        public readonly bool $stopOnFirstFailure = false,
        public readonly bool $trimStrings = true,
    ) {
    }
}