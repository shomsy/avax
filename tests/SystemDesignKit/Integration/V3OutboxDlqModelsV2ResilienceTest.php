<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesignKit\Integration;

use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\DeadLetters\DeadLetterQueue;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Inbox\Inbox;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\MessagingModel;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Outbox\Outbox;
use Avax\Labs\SystemDesignKit\System\Flows\DetectMessagingRisk\DetectMessagingRisk;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * V3-05: Prove V3 Outbox/DLQ model V2 Resilience patterns.
 *
 * V3 = design-time modeling (Outbox, Inbox, DeadLetterQueue VOs).
 * V2 Resilience = runtime execution (OutboxStore, DeadLetterStore interfaces + implementations).
 * This test proves V3 models describe V2 Resilience patterns without duplicating runtime ownership.
 */
final class V3OutboxDlqModelsV2ResilienceTest extends TestCase
{
    #[Test]
    public function v3OutboxModelsV2OutboxStoreConfiguration() : void
    {
        // V2 OutboxStore interface: enqueue, dequeue, markProcessed, pendingCount
        // V3 Outbox VO: enabled, pollIntervalMs, maxBatchSize, retentionHours
        // V3 models the configuration; V2 provides the runtime storage.
        $outbox = new Outbox(
            enabled       : true,
            pollIntervalMs: 100,
            maxBatchSize  : 100,
            retentionHours: 24,
        );

        self::assertTrue($outbox->validate()['valid']);
        self::assertSame(150, $outbox->estimatedRelayLagMs()); // pollIntervalMs + 50
    }

    #[Test]
    public function v3DeadLetterQueueModelsV2DeadLetterStoreConfiguration() : void
    {
        // V2 DeadLetterStore interface: store, count, all
        // V3 DeadLetterQueue VO: enabled, maxRetries, reprocessingWindowHours, alertChannel
        // V3 models the policy; V2 provides the runtime storage.
        $dlq = new DeadLetterQueue(
            enabled                : true,
            maxRetries             : 5,
            reprocessingWindowHours: 72,
            alertChannel           : 'ops-alerts',
        );

        self::assertTrue($dlq->validate()['valid']);
        self::assertTrue($dlq->shouldDeadLetter(5));
        self::assertFalse($dlq->shouldDeadLetter(4));
    }

    #[Test]
    public function v3InboxModelsConsumerSideDeduplication() : void
    {
        // V2 does not have a runtime Inbox implementation (only OutboxStore).
        // V3 Inbox models the consumer-side dedup configuration.
        $inbox = new Inbox(
            enabled         : true,
            dedupWindowHours: 24,
            dedupKeyStrategy: 'message_id',
        );

        self::assertTrue($inbox->validate()['valid']);
    }

    #[Test]
    public function v3DetectsMissingOutboxWhenV2AtLeastOnceDeliveryUsed() : void
    {
        // V2 MessageBus with at-least-once semantics but no outbox
        $config = [
            'system'    => 'v2-without-outbox',
            'messaging' => [
                'messages'          => [
                    ['name' => 'OrderPlaced', 'type' => 'event', 'idempotent' => true, 'max_retries' => 3, 'timeout_ms' => 5000],
                ],
                'consumers'         => [],
                'broker'            => [
                    'broker_type'        => 'kafka',
                    'partition_count'    => 6,
                    'replication_factor' => 3,
                    'delivery_semantics' => 'at_least_once',
                ],
                'dead_letter_queue' => ['enabled' => true, 'max_retries' => 5, 'reprocessing_window_hours' => 72, 'alert_channel' => 'ops'],
                'retry_policy'      => ['max_retries' => 3, 'backoff_strategy' => 'exponential', 'initial_delay_ms' => 100, 'max_delay_ms' => 30000],
                'inbox'             => ['enabled' => true, 'dedup_window_hours' => 24, 'dedup_key_strategy' => 'message_id'],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $missingOutbox = array_filter($risks, fn ($r) => $r['category'] === 'missing_outbox');
        self::assertNotEmpty($missingOutbox);
        self::assertStringContainsString('outbox', $missingOutbox[array_keys($missingOutbox)[0]]['description']);
    }

    #[Test]
    public function v3DetectsMissingInboxWhenV2AtLeastOnceDeliveryUsed() : void
    {
        $config = [
            'system'    => 'v2-without-inbox',
            'messaging' => [
                'messages'          => [],
                'consumers'         => [],
                'broker'            => [
                    'broker_type'        => 'kafka',
                    'partition_count'    => 6,
                    'replication_factor' => 3,
                    'delivery_semantics' => 'at_least_once',
                ],
                'outbox'            => ['enabled' => true, 'poll_interval_ms' => 100, 'max_batch_size' => 100, 'retention_hours' => 24],
                'dead_letter_queue' => ['enabled' => true, 'max_retries' => 5, 'reprocessing_window_hours' => 72, 'alert_channel' => 'ops'],
                'retry_policy'      => ['max_retries' => 3, 'backoff_strategy' => 'exponential', 'initial_delay_ms' => 100, 'max_delay_ms' => 30000],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $missingInbox = array_filter($risks, fn ($r) => $r['category'] === 'missing_inbox');
        self::assertNotEmpty($missingInbox);
    }

    #[Test]
    public function v3NoRisksWhenV2ResiliencePatternsFullyConfigured() : void
    {
        // Full V2 Resilience configuration: outbox, inbox, DLQ, retry, manual ack
        $config = [
            'system'    => 'v2-fully-configured',
            'messaging' => [
                'messages'          => [
                    ['name' => 'ProcessOrder', 'type' => 'command', 'idempotent' => true, 'max_retries' => 3, 'timeout_ms' => 5000],
                ],
                'consumers'         => [
                    'order-handler' => ['throughput_per_second' => 500, 'partition_count' => 6, 'ordered' => true],
                ],
                'broker'            => ['broker_type' => 'kafka', 'partition_count' => 12, 'replication_factor' => 3, 'delivery_semantics' => 'at_least_once'],
                'outbox'            => ['enabled' => true, 'poll_interval_ms' => 100, 'max_batch_size' => 100, 'retention_hours' => 24],
                'inbox'             => ['enabled' => true, 'dedup_window_hours' => 24, 'dedup_key_strategy' => 'message_id'],
                'dead_letter_queue' => ['enabled' => true, 'max_retries' => 5, 'reprocessing_window_hours' => 72, 'alert_channel' => 'ops'],
                'retry_policy'      => ['max_retries' => 3, 'backoff_strategy' => 'exponential', 'initial_delay_ms' => 100, 'max_delay_ms' => 30000],
                'ack_policy'        => 'manual',
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        self::assertEmpty($risks);
    }

    #[Test]
    public function v3OutboxDisabledDoesNotTriggerMissingOutboxRisk() : void
    {
        // Outbox explicitly disabled — no at-least-once broker
        $config = [
            'system'    => 'v2-no-broker',
            'messaging' => [
                'messages'          => [],
                'consumers'         => [],
                'outbox'            => ['enabled' => false, 'poll_interval_ms' => 0, 'max_batch_size' => 0, 'retention_hours' => 0],
                'dead_letter_queue' => ['enabled' => true, 'max_retries' => 5, 'reprocessing_window_hours' => 72, 'alert_channel' => 'ops'],
                'retry_policy'      => ['max_retries' => 3, 'backoff_strategy' => 'exponential', 'initial_delay_ms' => 100, 'max_delay_ms' => 30000],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $missingOutbox = array_filter($risks, fn ($r) => $r['category'] === 'missing_outbox');
        // No broker with at-least-once delivery, so missing_outbox risk should NOT be raised
        self::assertEmpty($missingOutbox);
    }

    #[Test]
    public function v3DeadLetterQueueDisabledTriggersRisk() : void
    {
        $config = [
            'system'    => 'v2-no-dlq',
            'messaging' => [
                'messages'     => [
                    ['name' => 'SendEmail', 'type' => 'command', 'idempotent' => true, 'max_retries' => 3, 'timeout_ms' => 5000],
                ],
                'consumers'    => [],
                'retry_policy' => ['max_retries' => 3, 'backoff_strategy' => 'exponential', 'initial_delay_ms' => 100, 'max_delay_ms' => 30000],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $missingDlq = array_filter($risks, fn ($r) => $r['category'] === 'missing_dlq');
        self::assertNotEmpty($missingDlq);
    }
}
