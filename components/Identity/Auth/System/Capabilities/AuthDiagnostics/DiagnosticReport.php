<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics;

final readonly class DiagnosticReport
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        public array $details = [],
    ) {}
}
