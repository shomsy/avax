<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Messaging;

use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Acknowledgement\AcknowledgementPolicy;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Broker\Broker;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Consumers\Consumer;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Cqrs\CommandSide;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Cqrs\QuerySide;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\DeadLetters\DeadLetterQueue;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Inbox\Inbox;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Outbox\Outbox;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Retry\RetryPolicy;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Types\Message;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Types\MessageType;

/**
 * Messaging model — aggregates all messaging value objects.
 *
 * @experimental V3 labs
 *
 * Models the messaging architecture: message vocabulary,
 * outbox/inbox patterns, consumer topology, dead letter queues,
 * retry/ack policies, and CQRS command/query sides.
 */
final readonly class MessagingModel
{
    /**
     * @param list<Message>        $messages
     * @param list<Consumer>       $consumers
     * @param Broker|null $broker
     * @param Outbox|null          $outbox
     * @param Inbox|null           $inbox
     * @param DeadLetterQueue|null $deadLetterQueue
     * @param RetryPolicy|null     $retryPolicy
     * @param CommandSide|null     $commandSide
     * @param QuerySide|null       $querySide
     */
    public function __construct(
        public string                $system,
        public array                 $messages,
        public array                 $consumers,
        public Broker|null          $broker = null,
        public Outbox|null          $outbox = null,
        public Inbox|null           $inbox = null,
        public DeadLetterQueue|null $deadLetterQueue = null,
        public RetryPolicy|null     $retryPolicy = null,
        public AcknowledgementPolicy $ackPolicy = AcknowledgementPolicy::Manual,
        public CommandSide|null     $commandSide = null,
        public QuerySide|null       $querySide = null,
    ) {}

    /**
     * Create a MessagingModel from config array.
     *
     * @param array<int|string, mixed> $config
     */
    public static function fromConfig(array $config) : self
    {
        $messaging = $config['messaging'] ?? [];

        // Messages
        $messages    = [];
        $messageList = $messaging['messages'] ?? [];

        if (is_array($messageList)) {
            foreach ($messageList as $msgConfig) {
                if (is_array($msgConfig)) {
                    $type       = MessageType::tryFrom($msgConfig['type'] ?? 'message') ?? MessageType::Message;
                    $messages[] = new Message(
                        name      : (string) ($msgConfig['name'] ?? 'unknown'),
                        type      : $type,
                        idempotent: (bool) ($msgConfig['idempotent'] ?? $type->requiresIdempotency()),
                        maxRetries: (int) ($msgConfig['max_retries'] ?? 3),
                        timeoutMs : (int) ($msgConfig['timeout_ms'] ?? 5000),
                    );
                }
            }
        }

        // Consumers
        $consumers    = [];
        $consumerList = $messaging['consumers'] ?? [];

        if (is_array($consumerList)) {
            foreach ($consumerList as $name => $consumerConfig) {
                if (is_array($consumerConfig)) {
                    $consumers[] = new Consumer(
                        name               : is_string($name) ? $name : (string) ($consumerConfig['name'] ?? 'unknown'),
                        throughputPerSecond: (int) ($consumerConfig['throughput_per_second'] ?? 1000),
                        partitionCount     : (int) ($consumerConfig['partition_count'] ?? 1),
                        ordered            : (bool) ($consumerConfig['ordered'] ?? false),
                    );
                }
            }
        }

        // Broker
        $brokerConfig = $messaging['broker'] ?? null;
        $broker       = null;

        if (is_array($brokerConfig)) {
            $broker = new Broker(
                brokerType       : (string) ($brokerConfig['broker_type'] ?? 'kafka'),
                partitionCount   : (int) ($brokerConfig['partition_count'] ?? 12),
                replicationFactor: (int) ($brokerConfig['replication_factor'] ?? 3),
                deliverySemantics: (string) ($brokerConfig['delivery_semantics'] ?? 'at_least_once'),
            );
        }

        // Outbox
        $outboxConfig = $messaging['outbox'] ?? null;
        $outbox       = null;

        if (is_array($outboxConfig)) {
            $outbox = new Outbox(
                enabled       : (bool) ($outboxConfig['enabled'] ?? true),
                pollIntervalMs: (int) ($outboxConfig['poll_interval_ms'] ?? 100),
                maxBatchSize  : (int) ($outboxConfig['max_batch_size'] ?? 100),
                retentionHours: (int) ($outboxConfig['retention_hours'] ?? 24),
            );
        }

        // Inbox
        $inboxConfig = $messaging['inbox'] ?? null;
        $inbox       = null;

        if (is_array($inboxConfig)) {
            $inbox = new Inbox(
                enabled         : (bool) ($inboxConfig['enabled'] ?? true),
                dedupWindowHours: (int) ($inboxConfig['dedup_window_hours'] ?? 24),
                dedupKeyStrategy: (string) ($inboxConfig['dedup_key_strategy'] ?? 'message_id'),
            );
        }

        // Dead Letter Queue
        $dlqConfig       = $messaging['dead_letter_queue'] ?? null;
        $deadLetterQueue = null;

        if (is_array($dlqConfig)) {
            $deadLetterQueue = new DeadLetterQueue(
                enabled                : (bool) ($dlqConfig['enabled'] ?? true),
                maxRetries             : (int) ($dlqConfig['max_retries'] ?? 5),
                reprocessingWindowHours: (int) ($dlqConfig['reprocessing_window_hours'] ?? 72),
                alertChannel           : (string) ($dlqConfig['alert_channel'] ?? 'ops-alerts'),
            );
        }

        // Retry Policy
        $retryConfig = $messaging['retry_policy'] ?? null;
        $retryPolicy = null;

        if (is_array($retryConfig)) {
            $retryPolicy = new RetryPolicy(
                maxRetries     : (int) ($retryConfig['max_retries'] ?? 3),
                backoffStrategy: (string) ($retryConfig['backoff_strategy'] ?? 'exponential'),
                initialDelayMs : (int) ($retryConfig['initial_delay_ms'] ?? 100),
                maxDelayMs     : (int) ($retryConfig['max_delay_ms'] ?? 30000),
            );
        }

        // Ack Policy
        $ackPolicy = AcknowledgementPolicy::tryFrom($messaging['ack_policy'] ?? 'manual') ?? AcknowledgementPolicy::Manual;

        // CQRS
        $cqrs        = $messaging['cqrs'] ?? null;
        $commandSide = null;
        $querySide   = null;

        if (is_array($cqrs)) {
            $cmd = $cqrs['command'] ?? null;

            if (is_array($cmd)) {
                $commandSide = new CommandSide(
                    aggregate       : (string) ($cmd['aggregate'] ?? 'default'),
                    usesOutbox      : (bool) ($cmd['uses_outbox'] ?? ($outbox !== null && $outbox->enabled)),
                    consistencyModel: (string) ($cmd['consistency_model'] ?? 'strong'),
                    commandTimeoutMs: (int) ($cmd['command_timeout_ms'] ?? 5000),
                );
            }

            $qry = $cqrs['query'] ?? null;

            if (is_array($qry)) {
                $querySide = new QuerySide(
                    aggregate           : (string) ($qry['aggregate'] ?? 'default'),
                    readConsistencyModel: (string) ($qry['read_consistency_model'] ?? 'eventual'),
                    queryTimeoutMs      : (int) ($qry['query_timeout_ms'] ?? 1000),
                    usesCaching         : (bool) ($qry['uses_caching'] ?? true),
                    cacheTtlSeconds     : (int) ($qry['cache_ttl_seconds'] ?? 60),
                );
            }
        }

        return new self(
            system         : (string) ($config['system'] ?? 'unknown'),
            messages       : $messages,
            consumers      : $consumers,
            broker         : $broker,
            outbox         : $outbox,
            inbox          : $inbox,
            deadLetterQueue: $deadLetterQueue,
            retryPolicy    : $retryPolicy,
            ackPolicy      : $ackPolicy,
            commandSide    : $commandSide,
            querySide      : $querySide,
        );
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        foreach ($this->messages as $message) {
            $result = $message->validate();
            $errors = array_merge($errors, $result['errors']);
        }

        foreach ($this->consumers as $consumer) {
            $result = $consumer->validate();
            $errors = array_merge($errors, $result['errors']);
        }

        foreach ([$this->broker, $this->outbox, $this->inbox, $this->deadLetterQueue, $this->retryPolicy, $this->commandSide, $this->querySide] as $component) {
            if ($component !== null) {
                $result = $component->validate();
                $errors = array_merge($errors, $result['errors']);
            }
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Messages that require idempotency.
     *
     * @return list<Message>
     */
    public function idempotentMessages() : array
    {
        return array_values(array_filter(
                                $this->messages,
                                static fn (Message $m) : bool => $m->idempotent,
                            ));
    }

    /**
     * Messages that are fire-and-forget.
     *
     * @return list<Message>
     */
    public function fireAndForgetMessages() : array
    {
        return array_values(array_filter(
                                $this->messages,
                                static fn (Message $m) : bool => $m->type->isFireAndForget(),
                            ));
    }

    /**
     * Whether the system uses CQRS.
     */
    public function usesCqrs() : bool
    {
        return $this->commandSide !== null && $this->querySide !== null;
    }

    /**
     * Whether the system uses the outbox pattern.
     */
    public function usesOutbox() : bool
    {
        return $this->outbox !== null && $this->outbox->enabled;
    }

    /**
     * Whether the system has a dead letter queue.
     */
    public function hasDeadLetterQueue() : bool
    {
        return $this->deadLetterQueue !== null && $this->deadLetterQueue->enabled;
    }

    /**
     * Total consumer throughput across all consumers.
     */
    public function totalConsumerThroughput() : int
    {
        $total = 0;

        foreach ($this->consumers as $consumer) {
            $total += $consumer->totalThroughput();
        }

        return $total;
    }
}
