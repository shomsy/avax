<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\SendFailureToDeadLetter;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\FailedJobsStore;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePipelineResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Throwable;

/**
 * SendFailureToDeadLetter — Sends a failure to a dead letter queue.
 *
 * Produces a structured dead-letter envelope.
 * Primary transport: Queue FailedJobsStore (canonical owner).
 * Fallback transport: error_log as NDJSON when no store is configured.
 *
 * Envelope shape:
 * {
 *   "type": "dead_letter",
 *   "version": 1,
 *   "queue": "<queue_name>",
 *   "failure": { "class": "...", "message": "...", "file": "...", "line": N },
 *   "context": { "kind": "...", "target_class": "...", "target_method": "..." },
 *   "timestamp": "ISO8601"
 * }
 */
final readonly class SendFailureToDeadLetter
{
    public function __construct(
        private ?FailedJobsStore $failedJobsStore = null,
    ) {}

    public function send(
        Throwable $failure,
        FailureContext $context,
        FailurePolicy $policy,
    ): FailurePipelineResult {
        $queue = $policy->deadLetterQueue ?? 'failed';

        $envelope = [
            'type' => 'dead_letter',
            'version' => 1,
            'queue' => $queue,
            'failure' => [
                'class' => $failure::class,
                'message' => $failure->getMessage(),
                'file' => $failure->getFile(),
                'line' => $failure->getLine(),
            ],
            'context' => [
                'kind' => $context->kind->value,
                'target_class' => $context->targetClass,
                'target_method' => $context->targetMethod,
            ],
            'timestamp' => date('c'),
        ];

        if ($this->failedJobsStore !== null) {
            $this->failedJobsStore->record(
                queue   : $queue,
                payload : $envelope,
                reason  : $failure::class . ': ' . $failure->getMessage(),
                failedAt: $envelope['timestamp'],
            );
        } else {
            // Fallback transport: error_log as NDJSON.
            error_log(json_encode($envelope, JSON_THROW_ON_ERROR));
        }

        return FailurePipelineResult::deadLettered();
    }
}
