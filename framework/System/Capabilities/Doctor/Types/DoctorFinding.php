<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor\Types;

/**
 * DoctorFinding — A single doctor check result.
 */
final readonly class DoctorFinding
{
    public function __construct(
        public string         $check,
        public DoctorSeverity $severity,
        public string         $message,
    ) {}
}
