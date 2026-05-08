<?php

declare(strict_types=1);

/**
 * V3 MessagingModel configuration for the Webhook Ingestion Pipeline.
 *
 * Describes message types, broker config, outbox/inbox/DLQ policies,
 * retry strategies, and CQRS boundaries. Used by V3 flows to validate
 * the messaging architecture design.
 */
return [
    'system'    => 'webhook-ingestion-pipeline',
    'messaging' => [
        'messages'          => [
            ['name' => 'IngestWebhook', 'type' => 'command', 'idempotent' => true, 'max_retries' => 3, 'timeout_ms' => 5000],
            ['name' => 'ProcessWebhookPayload', 'type' => 'command', 'idempotent' => true, 'max_retries' => 3, 'timeout_ms' => 10000],
            ['name' => 'GetWebhookStatus', 'type' => 'query', 'idempotent' => true, 'max_retries' => 0, 'timeout_ms' => 2000],
            ['name' => 'WebhookIngested', 'type' => 'event', 'idempotent' => true, 'max_retries' => 0, 'timeout_ms' => 1000],
            ['name' => 'WebhookProcessingFailed', 'type' => 'event', 'idempotent' => true, 'max_retries' => 0, 'timeout_ms' => 1000],
        ],
        'consumers'         => [
            'webhook-processor' => [
                'throughput_per_second' => 1000,
                'partition_count'       => 4,
                'ordered'               => false,
            ],
        ],
        'broker'            => [
            'broker_type'        => 'in-memory',
            'partition_count'    => 4,
            'replication_factor' => 1,
            'delivery_semantics' => 'at_least_once',
        ],
        'outbox'            => [
            'enabled'          => true,
            'poll_interval_ms' => 100,
            'max_batch_size'   => 100,
            'retention_hours'  => 24,
        ],
        'inbox'             => [
            'enabled'            => true,
            'dedup_window_hours' => 24,
            'dedup_key_strategy' => 'correlation_id',
        ],
        'dead_letter_queue' => [
            'enabled'                   => true,
            'max_retries'               => 5,
            'reprocessing_window_hours' => 72,
            'alert_channel'             => 'ops-alerts',
        ],
        'retry_policy'      => [
            'max_retries'      => 3,
            'backoff_strategy' => 'exponential',
            'initial_delay_ms' => 100,
            'max_delay_ms'     => 30000,
        ],
        'ack_policy'        => 'manual',
        'cqrs'              => [
            'command' => [
                'aggregate'          => 'Webhook',
                'uses_outbox'        => true,
                'consistency_model'  => 'strong',
                'command_timeout_ms' => 5000,
            ],
            'query'   => [
                'aggregate'              => 'Webhook',
                'read_consistency_model' => 'eventual',
                'query_timeout_ms'       => 1000,
                'uses_caching'           => true,
                'cache_ttl_seconds'      => 60,
            ],
        ],
    ],
];
