<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\MessageBus;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Consumer\Consumer;
use Avax\Components\Operations\MessageBus\System\Capabilities\Envelope\MessageEnvelope;
use Avax\Components\Operations\Resilience\System\Capabilities\Outbox\InMemoryOutboxStore;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class ConsumerTest extends TestCase
{
    private InMemoryOutboxStore $outboxStore;
    private EventBus $eventBus;
    private Consumer $consumer;

    protected function setUp() : void
    {
        $this->outboxStore = new InMemoryOutboxStore();
        $this->eventBus = new EventBus();
        $this->consumer = new Consumer($this->outboxStore, $this->eventBus, batchSize: 5);
    }

    #[Test]
    public function consume_dispatches_pending_messages() : void
    {
        $dispatched = [];
        $this->eventBus->register(MessageEnvelope::class, static function (MessageEnvelope $e) use (&$dispatched) : void {
            $dispatched[] = $e;
        });

        $this->outboxStore->enqueue('user.created', ['user_id' => 1, 'name' => 'Alice']);
        $this->outboxStore->enqueue('user.created', ['user_id' => 2, 'name' => 'Bob']);

        $result = $this->consumer->consume();

        self::assertSame(2, $result['dispatched']);
        self::assertSame(0, $result['failed']);
        self::assertSame(0, $this->outboxStore->pendingCount());
    }

    #[Test]
    public function consume_respects_batch_size() : void
    {
        $this->eventBus->register(MessageEnvelope::class, static fn () => null);

        for ($i = 0; $i < 10; $i++) {
            $this->outboxStore->enqueue('event.test', ['i' => $i]);
        }

        $result = $this->consumer->consume();

        self::assertSame(5, $result['dispatched']);
        self::assertSame(5, $this->outboxStore->pendingCount());
    }

    #[Test]
    public function consume_handles_failed_messages() : void
    {
        $failingBus = new EventBus();
        $failingBus->register(MessageEnvelope::class, static fn () => throw new RuntimeException('always fails'));

        $consumer = new Consumer($this->outboxStore, $failingBus, batchSize: 5);

        $this->outboxStore->enqueue('fail.always', ['data' => 'bad']);

        $result = $consumer->consume();

        self::assertSame(0, $result['dispatched']);
        self::assertSame(1, $result['failed']);
        self::assertCount(1, $result['errors']);
    }

    #[Test]
    public function consume_all_processes_all_batches() : void
    {
        $this->eventBus->register(MessageEnvelope::class, static fn () => null);

        for ($i = 0; $i < 12; $i++) {
            $this->outboxStore->enqueue('event.test', ['i' => $i]);
        }

        $result = $this->consumer->consumeAll(maxBatches: 10);

        self::assertSame(12, $result['totalDispatched']);
        self::assertSame(0, $result['totalFailed']);
    }

    #[Test]
    public function pending_count_returns_correct_value() : void
    {
        self::assertSame(0, $this->consumer->pendingCount());

        $this->outboxStore->enqueue('event.test', []);
        $this->outboxStore->enqueue('event.test', []);

        self::assertSame(2, $this->consumer->pendingCount());
    }

    #[Test]
    public function consume_does_nothing_when_empty() : void
    {
        $result = $this->consumer->consume();

        self::assertSame(0, $result['dispatched']);
        self::assertSame(0, $result['failed']);
    }
}
