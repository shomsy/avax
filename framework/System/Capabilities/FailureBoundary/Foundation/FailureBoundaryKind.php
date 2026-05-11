<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

/**
 * FailureBoundaryKind — Identifies the execution context for failure handling.
 */
enum FailureBoundaryKind: string
{
    case Http = 'http';
    case Console = 'console';
    case Queue = 'queue';
    case Worker = 'worker';
    case Scheduler = 'scheduler';
    case Webhook = 'webhook';
}
