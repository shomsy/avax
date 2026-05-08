<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesignKit\Integration;

use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\MessagingModel;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Types\MessageType;
use Avax\Labs\SystemDesignKit\System\Flows\DetectMessagingRisk\DetectMessagingRisk;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * V3-05: Prove V3 MessagingModel can describe V2 MessageBus runtime behavior.
 *
 * V3 = design-time modeling. V2 MessageBus = runtime execution.
 * This test proves V3 models can accurately describe what V2 MessageBus does.
 */
final class V3MessagingModelDescribesV2MessageBusTest extends TestCase
{
    #[Test]
    public function v3ModelDescribesV2CommandBusBehavior() : void
    {
        // V2 CommandBus: one command -> one handler, throws if no handler registered
        // V3 models this as a Command message type with idempotency requirement
        $config = [
            'system'    => 'v2-messagebus',
            'messaging' => [
                'messages'  => [
                    [
                        'name'        => 'CreateUserCommand',
                        'type'        => 'command',
                        'idempotent'  => true,
                        'max_retries' => 0,
                        'timeout_ms'  => 5000,
                    ],
                ],
                'consumers' => [],
            ],
        ];

        $model = MessagingModel::fromConfig($config);

        self::assertCount(1, $model->messages);
        self::assertSame('CreateUserCommand', $model->messages[0]->name);
        self::assertSame(MessageType::Command, $model->messages[0]->type);
        self::assertTrue($model->messages[0]->idempotent);
        self::assertTrue($model->messages[0]->type->requiresIdempotency());
        self::assertFalse($model->messages[0]->type->isFireAndForget());
        self::assertTrue($model->messages[0]->type->expectsResponse());
    }

    #[Test]
    public function v3ModelDescribesV2EventBusBehavior() : void
    {
        // V2 EventBus: one event -> many handlers, fire-and-forget
        // V3 models this as Event type with fire-and-forget semantics
        $config = [
            'system'    => 'v2-messagebus',
            'messaging' => [
                'messages'  => [
                    [
                        'name'        => 'UserCreatedEvent',
                        'type'        => 'event',
                        'idempotent'  => true,
                        'max_retries' => 0,
                        'timeout_ms'  => 1000,
                    ],
                ],
                'consumers' => [],
            ],
        ];

        $model = MessagingModel::fromConfig($config);

        self::assertCount(1, $model->messages);
        self::assertSame('UserCreatedEvent', $model->messages[0]->name);
        self::assertSame(MessageType::Event, $model->messages[0]->type);
        self::assertTrue($model->messages[0]->type->isFireAndForget());
        self::assertTrue($model->messages[0]->type->isFact());
        self::assertFalse($model->messages[0]->type->expectsResponse());
    }

    #[Test]
    public function v3ModelDescribesV2QueryBusBehavior() : void
    {
        // V2 QueryBus: one query -> one handler, returns result
        // V3 models this as a Command type (expects response)
        $config = [
            'system'    => 'v2-messagebus',
            'messaging' => [
                'messages'  => [
                    [
                        'name'        => 'GetUserQuery',
                        'type'        => 'command',
                        'idempotent'  => true,
                        'max_retries' => 0,
                        'timeout_ms'  => 2000,
                    ],
                ],
                'consumers' => [],
            ],
        ];

        $model = MessagingModel::fromConfig($config);

        self::assertCount(1, $model->messages);
        self::assertTrue($model->messages[0]->type->expectsResponse());
    }

    #[Test]
    public function v3RiskDetectionMatchesMinimalV2MessageBusConfig() : void
    {
        // V2 MessageBus default config: no outbox, no inbox, no DLQ, no retry policy
        // V3 should detect these as risks (outbox/inbox risks require at-least-once broker)
        $config = [
            'system'    => 'v2-messagebus-minimal',
            'messaging' => [
                'messages'  => [
                    [
                        'name'        => 'CreateOrder',
                        'type'        => 'command',
                        'idempotent'  => false,
                        'max_retries' => 0,
                        'timeout_ms'  => 5000,
                    ],
                ],
                'consumers' => [],
                'broker'    => [
                    'broker_type'        => 'kafka',
                    'partition_count'    => 6,
                    'replication_factor' => 3,
                    'delivery_semantics' => 'at_least_once',
                ],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $categories = array_column($risks, 'category');

        // No outbox with at-least-once broker -> risk
        self::assertContains('missing_outbox', $categories);

        // No inbox with at-least-once broker -> risk
        self::assertContains('missing_inbox', $categories);

        // No DLQ configured -> risk
        self::assertContains('missing_dlq', $categories);

        // No retry policy configured -> risk
        self::assertContains('missing_retry_policy', $categories);

        // Non-idempotent command -> risk
        $nonIdempotent = array_filter($risks, fn ($r) => $r['category'] === 'non_idempotent_message');
        self::assertNotEmpty($nonIdempotent);
    }

    #[Test]
    public function v3ModelAlignsWithV2MessageBusConfigurationDefaults() : void
    {
        // V2 MessageBusConfiguration defaults:
        //   enableTransactionMiddleware = true
        //   enableLoggingMiddleware = false
        //   enableValidationMiddleware = false
        //   defaultRetryAttempts = 0
        // V3 model should reflect retry attempts = 0 and identify risks
        $config = [
            'system'    => 'v2-messagebus-defaults',
            'messaging' => [
                'messages'     => [],
                'consumers'    => [],
                'retry_policy' => [
                    'max_retries'      => 0,
                    'backoff_strategy' => 'none',
                    'initial_delay_ms' => 0,
                    'max_delay_ms'     => 0,
                ],
            ],
        ];

        $model = MessagingModel::fromConfig($config);

        // V3 retry policy with max_retries=0 still validates (no retries is a valid config)
        self::assertNotNull($model->retryPolicy);

        $risks = (new DetectMessagingRisk())->execute($model);

        // With zero retries, missing DLQ and outbox are still risks
        $categories = array_column($risks, 'category');
        self::assertContains('missing_dlq', $categories);
    }

    #[Test]
    public function v3DetectsMissingIdempotencyForV2Commands() : void
    {
        // V2 CommandBus does not enforce idempotency at runtime.
        // V3 should flag non-idempotent commands as a risk.
        $config = [
            'system'    => 'v2-messagebus',
            'messaging' => [
                'messages'          => [
                    [
                        'name'        => 'ChargeCustomer',
                        'type'        => 'command',
                        'idempotent'  => false,
                        'max_retries' => 3,
                        'timeout_ms'  => 5000,
                    ],
                ],
                'consumers'         => [],
                'retry_policy'      => [
                    'max_retries'      => 3,
                    'backoff_strategy' => 'exponential',
                    'initial_delay_ms' => 100,
                    'max_delay_ms'     => 30000,
                ],
                'outbox'            => ['enabled' => true, 'poll_interval_ms' => 100, 'max_batch_size' => 100, 'retention_hours' => 24],
                'inbox'             => ['enabled' => true, 'dedup_window_hours' => 24, 'dedup_key_strategy' => 'message_id'],
                'dead_letter_queue' => ['enabled' => true, 'max_retries' => 5, 'reprocessing_window_hours' => 72, 'alert_channel' => 'ops'],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $idempotencyRisk = array_filter($risks, fn ($r) => $r['category'] === 'non_idempotent_message');
        self::assertNotEmpty($idempotencyRisk);
        self::assertStringContainsString('ChargeCustomer', $idempotencyRisk[array_keys($idempotencyRisk)[0]]['description']);
    }
}
