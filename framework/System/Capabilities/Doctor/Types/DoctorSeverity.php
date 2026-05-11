<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor\Types;

/**
 * DoctorSeverity — Severity level for doctor findings.
 */
enum DoctorSeverity: string
{
    case Green   = 'GREEN';
    case Yellow  = 'YELLOW';
    case Red     = 'RED';
    case Unknown = 'UNKNOWN';
}
