<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Diagnostics;

final class CorrelationId
{
    public function __construct(
        public readonly string $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(value: bin2hex(random_bytes(8)));
    }
}
