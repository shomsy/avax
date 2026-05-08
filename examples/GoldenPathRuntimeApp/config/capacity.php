<?php

declare(strict_types=1);

/**
 * V3 CapacityModel configuration for the Webhook Ingestion Pipeline.
 *
 * Describes expected traffic, storage, cache, queue, latency, and
 * availability requirements. Used by V3 flows to validate the
 * architecture design before deployment.
 */
return [
    'system'       => 'webhook-ingestion-pipeline',
    'traffic'      => [
        'requests_per_second' => 500,
        'reads_per_second'    => 100,
        'writes_per_second'   => 400,
        'read_write_ratio'    => 1,
        'peak_multiplier'     => 3,
    ],
    'storage'      => [
        'growth_per_day'            => 50_000_000,
        'average_record_size_bytes' => 2048,
        'retention_days'            => 30,
    ],
    'cache'        => [
        'hit_ratio_target'             => 0.85,
        'miss_penalty_ms'              => 25,
        'stampede_protection_required' => true,
    ],
    'queue'        => [
        'max_depth'                      => 10_000,
        'delay_budget_ms'                => 5000,
        'consumer_throughput_per_second' => 1000,
    ],
    'latency'      => [
        'p50_ms' => 50,
        'p95_ms' => 200,
        'p99_ms' => 500,
    ],
    'availability' => [
        'slo'                              => 99.9,
        'failure_budget_minutes_per_month' => 43.8,
    ],
];
