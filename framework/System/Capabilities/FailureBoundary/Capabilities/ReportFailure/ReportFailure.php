<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ReportFailure;

use Avax\Components\Operations\Observability\System\Capabilities\Logging\Logger;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Throwable;

/**
 * ReportFailure — Reports a failure to configured channels.
 *
 * Uses Observability Logger when available (structured, redacted, testable).
 * Falls back to error_log when Logger is not provided (backward compatibility).
 */
final readonly class ReportFailure
{
    public function __construct(
        private ?Logger $logger = null,
    ) {
    }

    public function for(Throwable $failure, FailureContext $context, FailurePolicy $policy): bool
    {
        $channel = $policy->reportChannel ?? 'default';

        if ($this->logger !== null) {
            $this->logger->error(
                sprintf('[%s][%s] %s', $channel, $context->kind->value, $failure::class),
                [
                    'message' => $failure->getMessage(),
                    'file' => $failure->getFile(),
                    'line' => $failure->getLine(),
                    'target_class' => $context->targetClass,
                    'target_method' => $context->targetMethod,
                    'kind' => $context->kind->value,
                    'channel' => $channel,
                ],
            );

            return true;
        }

        // Fallback: error_log when Logger is not provided
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
