<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Diagnostics;

final readonly class CorrelationId
{
    public function __construct(
        public string $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(value: bin2hex(random_bytes(8)));
    }
}
