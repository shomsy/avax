<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\MessageBus;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\CommandBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\QueryBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Envelope\MessageEnvelope;
use Avax\Components\Operations\MessageBus\System\Capabilities\Inbox\InMemoryInbox;
use Avax\Components\Operations\MessageBus\System\Capabilities\Projection\Projection;
use Avax\Components\Operations\MessageBus\System\Flows\DispatchCommand\DispatchCommand;
use Avax\Components\Operations\MessageBus\System\Flows\PublishEvent\PublishEvent;
use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;
use Avax\Components\Operations\Resilience\System\Capabilities\Outbox\InMemoryOutboxStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommandBus::class)]
#[CoversClass(EventBus::class)]
#[CoversClass(QueryBus::class)]
#[CoversClass(MessageEnvelope::class)]
#[CoversClass(InMemoryInbox::class)]
#[CoversClass(Projection::class)]
#[CoversClass(DispatchCommand::class)]
#[CoversClass(PublishEvent::class)]
#[CoversClass(InMemoryOutboxStore::class)]
final class MessagingIntegrationTest extends TestCase
{
    public function test_command_bus_dispatch(): void
    {
        $bus = new CommandBus();
        $result = null;

        $bus->register(TestCommand::class, new TestCommandHandler(static function (TestCommand $cmd) use (&$result): void {
            $result = $cmd->value;
        }));

        $dispatch = new DispatchCommand();
        $dispatch->dispatch($bus, new TestCommand('hello'));

        self::assertSame('hello', $result);
    }

    public function test_query_bus_dispatch(): void
    {
        $bus = new QueryBus();
        $bus->register(TestQuery::class, static fn (TestQuery $q): int => strlen($q->query));

        $result = $bus->dispatch(new TestQuery('test'));

        self::assertSame(4, $result);
    }

    public function test_event_bus_register_and_dispatch(): void
    {
        $bus = new EventBus();
        $collected = [];

        $bus->register(TestEvent::class, new TestEventHandler(static function (TestEvent $e) use (&$collected): void {
            $collected[] = 'listener1:'.$e->data;
        }));
        $bus->register(TestEvent::class, new TestEventHandler(static function (TestEvent $e) use (&$collected): void {
            $collected[] = 'listener2:'.$e->data;
        }));

        $publish = new PublishEvent();
        $publish->publish($bus, new TestEvent('broadcast'));

        self::assertCount(2, $collected);
    }

    public function test_inbox_duplicate_detection(): void
    {
        $inbox = new InMemoryInbox();

        $messageId = 'msg-1';
        self::assertFalse($inbox->isDuplicate($messageId));

        $inbox->markProcessed($messageId);
        self::assertTrue($inbox->isDuplicate($messageId));
    }

    public function test_outbox_enqueue_dequeue_mark_processed(): void
    {
        $store = new InMemoryOutboxStore();

        $store->enqueue('test_message', ['key' => 'value']);
        $pending = $store->dequeue();

        self::assertCount(1, $pending);

        $store->markProcessed($pending[0]['id']);
        self::assertSame(0, $store->pendingCount());
    }

    public function test_message_envelope_creation(): void
    {
        $envelope = MessageEnvelope::create('test_event', ['data' => 'value']);

        self::assertNotEmpty($envelope->messageId);
        self::assertSame('test_event', $envelope->type);
        self::assertSame(['data' => 'value'], $envelope->body);
        self::assertNotEmpty($envelope->correlationId);
        self::assertGreaterThan(0, $envelope->timestamp);
    }

    public function test_projection_apply(): void
    {
        $state = [];
        $projection = new Projection();
        $projection->on('projected_event', static function (MessageEnvelope $e) use (&$state): void {
            $state[] = $e->body;
        });

        $envelope = MessageEnvelope::create('projected_event', ['data' => 'projected']);
        $projection->apply($envelope);

        self::assertCount(1, $state);
        self::assertSame(['data' => 'projected'], $state[0]);
    }
}

// Test message classes
final readonly class TestCommand implements Command
{
    public function __construct(public string $value) {}
}

final readonly class TestCommandHandler
{
    public function __construct(private \Closure $fn) {}

    public function __invoke(TestCommand $cmd): void
    {
        ($this->fn)($cmd);
    }
}

final readonly class TestQuery
{
    public function __construct(public string $query) {}
}

final readonly class TestEvent
{
    public function __construct(public string $data) {}
}

final readonly class TestEventHandler
{
    public function __construct(private \Closure $fn) {}

    public function __invoke(object $event): void
    {
        ($this->fn)($event);
    }
}
