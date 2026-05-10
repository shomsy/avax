<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Health\Foundation;

final readonly class DiagnosticFinding
{
    public function __construct(
        public string $component,
        public DiagnosticSeverity $severity,
        public string $message = '',
    ) {
    }
}
