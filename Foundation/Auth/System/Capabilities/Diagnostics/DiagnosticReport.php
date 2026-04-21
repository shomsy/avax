<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Diagnostics;

final readonly class DiagnosticReport
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        public array $details = []
    ) {
    }
}
