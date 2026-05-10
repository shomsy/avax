<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Health\Foundation;

final readonly class HealthFinding
{
    public function __construct(
        public string $check,
        public HealthStatus $status,
        public string $message = '',
    ) {
    }
}
