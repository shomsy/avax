<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Health\Foundation;

enum DiagnosticSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
    case Critical = 'critical';
}
