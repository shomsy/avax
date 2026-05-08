<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Values\Money;

/**
 * Currency Value Object.
 */
final readonly class Currency
{
    public function __construct(
        public string $code,
        public string $symbol = '$',
    ) {
    }
}
