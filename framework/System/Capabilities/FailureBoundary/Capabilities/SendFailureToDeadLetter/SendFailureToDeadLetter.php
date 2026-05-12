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
 * Produces a structured dead-letter envelope.
 * Current transport: error_log as NDJSON (testable, observable).
 * When a real Queue/Messaging component is available, this class
 * should accept it via constructor and enqueue the envelope.
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

        // Transport: error_log as NDJSON.
        // When Messaging/Queue component is available, enqueue the envelope instead.
        error_log(json_encode($envelope, JSON_THROW_ON_ERROR));

        return FailurePipelineResult::deadLettered();
    }
}
