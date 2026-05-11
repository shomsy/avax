<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ReportFailure;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Throwable;

/**
 * ReportFailure — Reports a failure to configured channels.
 *
 * For MVP: uses error_log with structured context.
 * Extensible for Observability component integration later.
 */
final readonly class ReportFailure
{
    public function for(Throwable $failure, FailureContext $context, FailurePolicy $policy): bool
    {
        $channel = $policy->reportChannel ?? 'default';
        $message = sprintf(
            '[FailureBoundary][%s][%s] %s in %s::%s — %s',
            $channel,
            $context->kind->value,
            $failure::class,
            $context->targetClass,
            $context->targetMethod,
            $failure->getMessage(),
        );

        error_log($message);

        return true;
    }
}
