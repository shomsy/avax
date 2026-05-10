<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Health\Foundation;

enum HealthStatus: string
{
    case Green = 'green';
    case Yellow = 'yellow';
    case Red = 'red';
    case Unknown = 'unknown';
}
