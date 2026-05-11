<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\SendFailureToDeadLetter;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePipelineResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Throwable;

/**
 * SendFailureToDeadLetter — Sends a failure to a dead letter queue.
 *
 * For MVP: serializes failure data and calls a configurable handler.
 * Queue integration comes when the Queue component is available.
 */
final readonly class SendFailureToDeadLetter
{
    public function send(
        Throwable $failure,
        FailureContext $context,
        FailurePolicy $policy,
    ): FailurePipelineResult {
        $queue = $policy->deadLetterQueue ?? 'failed';

        $payload = [
            'queue' => $queue,
            'exception_class' => $failure::class,
            'message' => $failure->getMessage(),
            'file' => $failure->getFile(),
            'line' => $failure->getLine(),
            'trace' => $failure->getTraceAsString(),
            'context' => [
                'kind' => $context->kind->value,
                'target_class' => $context->targetClass,
                'target_method' => $context->targetMethod,
            ],
            'timestamp' => date('c'),
        ];

        // For MVP: log the dead letter payload.
        // In production, this would send to an actual dead letter queue.
        error_log('[DeadLetter] ' . json_encode($payload, JSON_THROW_ON_ERROR));

        return FailurePipelineResult::deadLettered();
    }
}
