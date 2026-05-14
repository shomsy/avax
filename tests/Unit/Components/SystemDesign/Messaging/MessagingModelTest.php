<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\SystemDesign\Messaging;

use Avax\Components\SystemDesign\System\Capabilities\Messaging\Acknowledgement\AcknowledgementPolicy;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Broker\Broker;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\CommandQuery\CommandSide;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\CommandQuery\QuerySide;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Consumers\Consumer;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\DeadLetters\DeadLetterQueue;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Envelope\MessageEnvelope;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Inbox\Inbox;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\MessagingModel;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Outbox\Outbox;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Retry\RetryPolicy;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Types\Message;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\Types\MessageType;
use Avax\Components\SystemDesign\System\Flows\DetectMessagingRisk\DetectMessagingRisk;
use Avax\Components\SystemDesign\System\Flows\ValidateMessagingModel\ValidateMessagingModel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * V3-04: Messaging and CQRS model tests.
 */
final class MessagingModelTest extends TestCase
{
    // -- MessageType --

    #[Test]
    public function commandExpectsResponse() : void
    {
        self::assertTrue(MessageType::Command->expectsResponse());
    }

    #[Test]
    public function eventIsFact() : void
    {
        self::assertTrue(MessageType::Event->isFact());
    }

    #[Test]
    public function eventIsFireAndForget() : void
    {
        self::assertTrue(MessageType::Event->isFireAndForget());
    }

    #[Test]
    public function jobIsFireAndForget() : void
    {
        self::assertTrue(MessageType::Job->isFireAndForget());
    }

    #[Test]
    public function commandIsNotFireAndForget() : void
    {
        self::assertFalse(MessageType::Command->isFireAndForget());
    }

    #[Test]
    public function commandRequiresIdempotency() : void
    {
        self::assertTrue(MessageType::Command->requiresIdempotency());
    }

    #[Test]
    public function eventRequiresIdempotency() : void
    {
        self::assertTrue(MessageType::Event->requiresIdempotency());
    }

    #[Test]
    public function jobDoesNotRequireIdempotency() : void
    {
        self::assertFalse(MessageType::Job->requiresIdempotency());
    }

    // -- Message --

    #[Test]
    public function validMessage() : void
    {
        $message = new Message('CreateUser', MessageType::Command, true, 3, 5000);

        self::assertTrue($message->validate()['valid']);
    }

    #[Test]
    public function messageRejectsNegativeRetries() : void
    {
        $message = new Message('Test', MessageType::Event, false, -1, 1000);

        self::assertFalse($message->validate()['valid']);
    }

    #[Test]
    public function messageRejectsZeroTimeout() : void
    {
        $message = new Message('Test', MessageType::Event, false, 0, 0);

        self::assertFalse($message->validate()['valid']);
    }

    // -- MessageEnvelope --

    #[Test]
    public function validEnvelope() : void
    {
        $envelope = new MessageEnvelope('msg-1', 'corr-1', 'caus-1', 'users.created', 3600, 1);

        self::assertTrue($envelope->validate()['valid']);
    }

    #[Test]
    public function envelopeRejectsEmptyMessageId() : void
    {
        $envelope = new MessageEnvelope('', 'corr-1', null, 'topic', 60, 1);

        self::assertFalse($envelope->validate()['valid']);
    }

    // -- Outbox --

    #[Test]
    public function validOutbox() : void
    {
        $outbox = new Outbox(enabled: true, pollIntervalMs: 100, maxBatchSize: 100, retentionHours: 24);

        self::assertTrue($outbox->validate()['valid']);
    }

    #[Test]
    public function disabledOutboxAlwaysValidates() : void
    {
        $outbox = new Outbox(enabled: false, pollIntervalMs: 0, maxBatchSize: 0, retentionHours: 0);

        self::assertTrue($outbox->validate()['valid']);
    }

    #[Test]
    public function outboxRelayLag() : void
    {
        $outbox = new Outbox(enabled: true, pollIntervalMs: 200, maxBatchSize: 50, retentionHours: 12);

        self::assertSame(250, $outbox->estimatedRelayLagMs());
    }

    // -- Inbox --

    #[Test]
    public function validInbox() : void
    {
        $inbox = new Inbox(enabled: true, dedupWindowHours: 24, dedupKeyStrategy: 'message_id');

        self::assertTrue($inbox->validate()['valid']);
    }

    // -- DeadLetterQueue --

    #[Test]
    public function validDeadLetterQueue() : void
    {
        $dlq = new DeadLetterQueue(enabled: true, maxRetries: 5, reprocessingWindowHours: 72, alertChannel: 'ops');

        self::assertTrue($dlq->validate()['valid']);
    }

    #[Test]
    public function deadLetterShouldDeadLetter() : void
    {
        $dlq = new DeadLetterQueue(enabled: true, maxRetries: 3, reprocessingWindowHours: 24, alertChannel: 'ops');

        self::assertTrue($dlq->shouldDeadLetter(3));
        self::assertFalse($dlq->shouldDeadLetter(2));
    }

    // -- Consumer --

    #[Test]
    public function validConsumer() : void
    {
        $consumer = new Consumer('order-processor', 1000, 6, true);

        self::assertTrue($consumer->validate()['valid']);
    }

    #[Test]
    public function consumerTotalThroughput() : void
    {
        $consumer = new Consumer('test', 500, 4, false);

        self::assertSame(2000, $consumer->totalThroughput());
    }

    // -- RetryPolicy --

    #[Test]
    public function validRetryPolicy() : void
    {
        $policy = new RetryPolicy(3, 'exponential', 100, 30000);

        self::assertTrue($policy->validate()['valid']);
    }

    #[Test]
    public function worstCaseRetryTime() : void
    {
        $policy = new RetryPolicy(5, 'exponential', 100, 10000);

        self::assertSame(50000, $policy->worstCaseRetryTimeMs());
    }

    // -- AcknowledgementPolicy --

    #[Test]
    public function autoAckIsAutoAck() : void
    {
        self::assertTrue(AcknowledgementPolicy::Auto->isAutoAck());
    }

    #[Test]
    public function autoAckHasHighLossRisk() : void
    {
        self::assertSame('high', AcknowledgementPolicy::Auto->lossRisk());
    }

    #[Test]
    public function manualAckHasLowLossRisk() : void
    {
        self::assertSame('low', AcknowledgementPolicy::Manual->lossRisk());
    }

    // -- Broker --

    #[Test]
    public function validBroker() : void
    {
        $broker = new Broker('kafka', 12, 3, 'at_least_once');

        self::assertTrue($broker->validate()['valid']);
    }

    #[Test]
    public function brokerRejectsInvalidSemantics() : void
    {
        $broker = new Broker('kafka', 12, 3, 'invalid');

        self::assertFalse($broker->validate()['valid']);
    }

    // -- CommandSide --

    #[Test]
    public function validCommandSide() : void
    {
        $cmd = new CommandSide('Order', true, 'strong', 5000);

        self::assertTrue($cmd->validate()['valid']);
    }

    // -- QuerySide --

    #[Test]
    public function validQuerySide() : void
    {
        $qry = new QuerySide('Order', 'eventual', 1000, true, 60);

        self::assertTrue($qry->validate()['valid']);
    }

    #[Test]
    public function querySideRejectsZeroCacheTtl() : void
    {
        $qry = new QuerySide('Order', 'eventual', 1000, true, 0);

        self::assertFalse($qry->validate()['valid']);
    }

    // -- MessagingModel aggregate --

    #[Test]
    public function messagingModelFromConfig() : void
    {
        $config = [
            'system'    => 'order-service',
            'messaging' => [
                'messages'          => [
                    ['name' => 'CreateOrder', 'type' => 'command', 'idempotent' => true, 'max_retries' => 3, 'timeout_ms' => 5000],
                    ['name' => 'OrderCreated', 'type' => 'event', 'idempotent' => true, 'max_retries' => 0, 'timeout_ms' => 1000],
                ],
                'consumers'         => [
                    'order-handler' => ['throughput_per_second' => 500, 'partition_count' => 6, 'ordered' => true],
                ],
                'broker'            => ['broker_type' => 'kafka', 'partition_count' => 12, 'replication_factor' => 3, 'delivery_semantics' => 'at_least_once'],
                'outbox'            => ['enabled' => true, 'poll_interval_ms' => 100, 'max_batch_size' => 100, 'retention_hours' => 24],
                'inbox'             => ['enabled' => true, 'dedup_window_hours' => 24, 'dedup_key_strategy' => 'message_id'],
                'dead_letter_queue' => ['enabled' => true, 'max_retries' => 5, 'reprocessing_window_hours' => 72, 'alert_channel' => 'ops'],
                'retry_policy'      => ['max_retries' => 3, 'backoff_strategy' => 'exponential', 'initial_delay_ms' => 100, 'max_delay_ms' => 30000],
            ],
        ];

        $model  = MessagingModel::fromConfig($config);
        $result = $model->validate();

        self::assertTrue($result['valid']);
        self::assertCount(2, $model->messages);
        self::assertCount(1, $model->consumers);
        self::assertNotNull($model->broker);
        self::assertTrue($model->usesOutbox());
        self::assertTrue($model->hasDeadLetterQueue());
    }

    #[Test]
    public function messagingModelWithCqrs() : void
    {
        $config = [
            'system'    => 'order-service',
            'messaging' => [
                'messages'  => [],
                'consumers' => [],
                'cqrs'      => [
                    'command' => ['aggregate' => 'Order', 'uses_outbox' => true, 'consistency_model' => 'strong', 'command_timeout_ms' => 5000],
                    'query'   => ['aggregate' => 'Order', 'read_consistency_model' => 'eventual', 'query_timeout_ms' => 1000, 'uses_caching' => true, 'cache_ttl_seconds' => 60],
                ],
            ],
        ];

        $model = MessagingModel::fromConfig($config);

        self::assertTrue($model->usesCqrs());
        self::assertNotNull($model->commandSide);
        self::assertNotNull($model->querySide);
        self::assertSame('Order', $model->commandSide->aggregate);
        self::assertSame('Order', $model->querySide->aggregate);
    }

    #[Test]
    public function idempotentMessages() : void
    {
        $config = [
            'system'    => 'test',
            'messaging' => [
                'messages'  => [
                    ['name' => 'Cmd', 'type' => 'command', 'idempotent' => true, 'max_retries' => 0, 'timeout_ms' => 1000],
                    ['name' => 'Job', 'type' => 'job', 'idempotent' => false, 'max_retries' => 0, 'timeout_ms' => 1000],
                ],
                'consumers' => [],
            ],
        ];

        $model      = MessagingModel::fromConfig($config);
        $idempotent = $model->idempotentMessages();

        self::assertCount(1, $idempotent);
        self::assertSame('Cmd', $idempotent[0]->name);
    }

    #[Test]
    public function fireAndForgetMessages() : void
    {
        $config = [
            'system'    => 'test',
            'messaging' => [
                'messages'  => [
                    ['name' => 'Cmd', 'type' => 'command', 'idempotent' => false, 'max_retries' => 0, 'timeout_ms' => 1000],
                    ['name' => 'Evt', 'type' => 'event', 'idempotent' => true, 'max_retries' => 0, 'timeout_ms' => 1000],
                    ['name' => 'Job', 'type' => 'job', 'idempotent' => false, 'max_retries' => 0, 'timeout_ms' => 1000],
                ],
                'consumers' => [],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $faf   = $model->fireAndForgetMessages();

        self::assertCount(2, $faf);
    }

    #[Test]
    public function totalConsumerThroughput() : void
    {
        $config = [
            'system'    => 'test',
            'messaging' => [
                'messages'  => [],
                'consumers' => [
                    'c1' => ['throughput_per_second' => 500, 'partition_count' => 4, 'ordered' => false],
                    'c2' => ['throughput_per_second' => 200, 'partition_count' => 2, 'ordered' => true],
                ],
            ],
        ];

        $model = MessagingModel::fromConfig($config);

        // 500*4 + 200*2 = 2400
        self::assertSame(2400, $model->totalConsumerThroughput());
    }

    // -- ValidateMessagingModel flow --

    #[Test]
    public function validateMessagingModelFlowPasses() : void
    {
        $config = [
            'system'    => 'test',
            'messaging' => [
                'messages'  => [
                    ['name' => 'TestCmd', 'type' => 'command', 'idempotent' => true, 'max_retries' => 3, 'timeout_ms' => 5000],
                ],
                'consumers' => [
                    'test-consumer' => ['throughput_per_second' => 100, 'partition_count' => 1, 'ordered' => false],
                ],
            ],
        ];

        $result = (new ValidateMessagingModel())->execute($config);

        self::assertTrue($result['valid']);
        self::assertNotNull($result['model']);
    }

    // -- DetectMessagingRisk flow --

    #[Test]
    public function detectMessagingRiskFindsMissingOutbox() : void
    {
        $config = [
            'system'    => 'test',
            'messaging' => [
                'messages'  => [],
                'consumers' => [],
                'broker'    => ['broker_type' => 'kafka', 'partition_count' => 12, 'replication_factor' => 3, 'delivery_semantics' => 'at_least_once'],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $missingOutbox = array_filter($risks, fn ($r) => $r['category'] === 'missing_outbox');

        self::assertNotEmpty($missingOutbox);
    }

    #[Test]
    public function detectMessagingRiskFindsMissingDlq() : void
    {
        $config = [
            'system'    => 'test',
            'messaging' => [
                'messages'  => [],
                'consumers' => [],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $missingDlq = array_filter($risks, fn ($r) => $r['category'] === 'missing_dlq');

        self::assertNotEmpty($missingDlq);
    }

    #[Test]
    public function detectMessagingRiskFindsAutoAck() : void
    {
        $config = [
            'system'    => 'test',
            'messaging' => [
                'messages'   => [],
                'consumers'  => [],
                'ack_policy' => 'auto',
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $autoAck = array_filter($risks, fn ($r) => $r['category'] === 'auto_ack');

        self::assertNotEmpty($autoAck);
    }

    #[Test]
    public function detectMessagingRiskFindsNonIdempotentCommand() : void
    {
        $config = [
            'system'    => 'test',
            'messaging' => [
                'messages'  => [
                    ['name' => 'CreateOrder', 'type' => 'command', 'idempotent' => false, 'max_retries' => 0, 'timeout_ms' => 5000],
                ],
                'consumers' => [],
            ],
        ];

        $model = MessagingModel::fromConfig($config);
        $risks = (new DetectMessagingRisk())->execute($model);

        $nonIdempotent = array_filter($risks, fn ($r) => $r['category'] === 'non_idempotent_message');

        self::assertNotEmpty($nonIdempotent);
    }

    #[Test]
    public function detectMessagingRiskNoRisksOnWellConfigured() : void
    {
        $config = [
            'system'    => 'test',
            'messaging' => [
                'messages'          => [
                    ['name' => 'CreateOrder', 'type' => 'command', 'idempotent' => true, 'max_retries' => 3, 'timeout_ms' => 5000],
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
}
