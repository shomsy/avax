<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

/**
 * FailureDecision — The action to take when a failure occurs.
 */
enum FailureDecision: string
{
    case Retry = 'retry';
    case Fallback = 'fallback';
    case Recover = 'recover';
    case MapToResult = 'map_to_result';
    case DeadLetter = 'dead_letter';
    case Rethrow = 'rethrow';
    case ReportOnly = 'report_only';
}
