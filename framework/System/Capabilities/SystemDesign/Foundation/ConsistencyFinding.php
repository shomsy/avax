<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\SystemDesign\Foundation;

final readonly class ConsistencyFinding
{
    public function __construct(
        public string $check,
        public string $status,
        public string $detail = '',
    ) {
    }
}
