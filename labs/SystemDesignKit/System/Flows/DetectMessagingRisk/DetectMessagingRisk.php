<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Flows\DetectMessagingRisk;

use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Acknowledgement\AcknowledgementPolicy;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\MessagingModel;

/**
 * Detects messaging architecture risks.
 *
 * @experimental V3 labs
 */
final class DetectMessagingRisk
{
    /**
     * @return list<array{
     *     severity: string,
     *     category: string,
     *     description: string,
     *     path: string,
     *     recommendation: string,
     * }>
     */
    public function execute(MessagingModel $model) : array
    {
        $risks = [];

        // No outbox with at-least-once delivery
        if ($model->broker !== null && $model->broker->deliverySemantics === 'at_least_once' && ! $model->usesOutbox()) {
            $risks[] = [
                'severity'       => 'high',
                'category'       => 'missing_outbox',
                'description'    => 'At-least-once delivery without outbox pattern risks duplicate events on failure.',
                'path'           => 'messaging.outbox',
                'recommendation' => 'Enable the outbox pattern to ensure atomic writes with event publishing.',
            ];
        }

        // No inbox with at-least-once delivery
        if ($model->broker !== null && $model->broker->deliverySemantics === 'at_least_once' && ($model->inbox === null || ! $model->inbox->enabled)) {
            $risks[] = [
                'severity'       => 'high',
                'category'       => 'missing_inbox',
                'description'    => 'At-least-once delivery without inbox deduplication risks processing duplicate messages.',
                'path'           => 'messaging.inbox',
                'recommendation' => 'Enable the inbox pattern for consumer-side deduplication.',
            ];
        }

        // No dead letter queue
        if (! $model->hasDeadLetterQueue()) {
            $risks[] = [
                'severity'       => 'medium',
                'category'       => 'missing_dlq',
                'description'    => 'No dead letter queue configured. Poison messages will be retried indefinitely or lost.',
                'path'           => 'messaging.dead_letter_queue',
                'recommendation' => 'Configure a dead letter queue with alerting for failed message processing.',
            ];
        }

        // Auto ack policy
        if ($model->ackPolicy === AcknowledgementPolicy::Auto) {
            $risks[] = [
                'severity'       => 'high',
                'category'       => 'auto_ack',
                'description'    => 'Auto-ack policy risks message loss if consumer crashes after receive but before processing.',
                'path'           => 'messaging.ack_policy',
                'recommendation' => 'Use manual or batch acknowledgement for reliable processing.',
            ];
        }

        // Non-idempotent commands
        foreach ($model->messages as $message) {
            if ($message->type->requiresIdempotency() && ! $message->idempotent) {
                $risks[] = [
                    'severity'       => 'medium',
                    'category'       => 'non_idempotent_message',
                    'description'    => "Message '{$message->name}' ({$message->type->value}) requires idempotency but is not marked idempotent.",
                    'path'           => "messaging.messages.{$message->name}",
                    'recommendation' => 'Mark the message as idempotent and implement idempotency keys.',
                ];
            }
        }

        // No retry policy
        if ($model->retryPolicy === null) {
            $risks[] = [
                'severity'       => 'medium',
                'category'       => 'missing_retry_policy',
                'description'    => 'No retry policy configured. Transient failures will cause immediate message failure.',
                'path'           => 'messaging.retry_policy',
                'recommendation' => 'Configure a retry policy with exponential backoff.',
            ];
        }

        return $risks;
    }
}
