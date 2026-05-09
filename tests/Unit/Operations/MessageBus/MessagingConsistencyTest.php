<?php

declare(strict_types=1);

namespace Tests\Unit\Operations\MessageBus;

use Avax\Components\Operations\MessageBus\System\Capabilities\Envelope\MessageEnvelope;
use Avax\Components\Operations\MessageBus\System\Capabilities\Inbox\InMemoryInbox;
use Avax\Components\Operations\MessageBus\System\Capabilities\Projection\Projection;
use Avax\Components\Operations\MessageBus\System\Flows\PublishToOutbox\PublishToOutbox;
use Avax\Components\Operations\Resilience\System\Capabilities\Outbox\InMemoryOutboxStore;
use PHPUnit\Framework\TestCase;

final class MessagingConsistencyTest extends TestCase
{
    // -- MessageEnvelope tests

    public function test_create_envelope() : void
    {
        $envelope = MessageEnvelope::create(
            type: 'UserRegistered',
            body: ['userId' => '123', 'email' => 'user@example.com'],
        );

        self::assertStringStartsWith('msg_', $envelope->messageId);
        self::assertSame('UserRegistered', $envelope->type);
        self::assertSame('123', $envelope->body['userId']);
        self::assertStringStartsWith('corr_', $envelope->correlationId);
        self::assertGreaterThan(0, $envelope->timestamp);
    }

    public function test_envelope_from_array() : void
    {
        $data = [
            'messageId' => 'msg-1',
            'type' => 'OrderPlaced',
            'body' => ['orderId' => '456'],
            'correlationId' => 'corr-1',
            'version' => 2,
        ];

        $envelope = MessageEnvelope::fromArray($data);

        self::assertSame('msg-1', $envelope->messageId);
        self::assertSame('OrderPlaced', $envelope->type);
        self::assertSame(2, $envelope->version);
    }

    public function test_envelope_to_array_roundtrip() : void
    {
        $envelope = MessageEnvelope::create(type: 'Test', body: ['key' => 'value']);
        $array = $envelope->toArray();

        $restored = MessageEnvelope::fromArray($array);

        self::assertSame($envelope->messageId, $restored->messageId);
        self::assertSame($envelope->type, $restored->type);
        self::assertSame($envelope->body, $restored->body);
    }

    public function test_envelope_with_causation() : void
    {
        $original = MessageEnvelope::create(type: 'A', body: []);
        $child = $original->withCausation(causationId: $original->messageId);

        self::assertSame($original->messageId, $child->messageId);
        self::assertSame($original->messageId, $child->causationId);
    }

    // -- InMemoryInbox tests

    public function test_inbox_detects_duplicate() : void
    {
        $inbox = new InMemoryInbox();

        self::assertFalse($inbox->isDuplicate('msg-1'));
        $inbox->markProcessed('msg-1');
        self::assertTrue($inbox->isDuplicate('msg-1'));
    }

    public function test_inbox_allows_unique_messages() : void
    {
        $inbox = new InMemoryInbox();
        $inbox->markProcessed('msg-1');

        self::assertFalse($inbox->isDuplicate('msg-2'));
    }

    public function test_inbox_size() : void
    {
        $inbox = new InMemoryInbox();
        $inbox->markProcessed('msg-1');
        $inbox->markProcessed('msg-2');

        self::assertSame(2, $inbox->size());
    }

    public function test_inbox_clear() : void
    {
        $inbox = new InMemoryInbox();
        $inbox->markProcessed('msg-1');
        $inbox->clear();

        self::assertSame(0, $inbox->size());
        self::assertFalse($inbox->isDuplicate('msg-1'));
    }

    // -- Projection tests

    public function test_projection_applies_event() : void
    {
        $projection = new Projection();
        $projection->on(
            'UserRegistered',
            static function (MessageEnvelope $event) use ($projection) : void {
                $projection->setState('userCount', ($projection->getState('userCount', 0)) + 1);
            },
        );

        $envelope = MessageEnvelope::create(type: 'UserRegistered', body: ['userId' => '1']);
        $projection->apply($envelope);

        self::assertSame(1, $projection->getState('userCount'));
    }

    public function test_projection_applies_multiple_events() : void
    {
        $projection = new Projection();
        $projection->on(
            'ItemAdded',
            static function (MessageEnvelope $event) use ($projection) : void {
                $items = $projection->getState('items', []);
                $items[] = $event->body['item'];
                $projection->setState('items', $items);
            },
        );

        $events = [
            MessageEnvelope::create(type: 'ItemAdded', body: ['item' => 'apple']),
            MessageEnvelope::create(type: 'ItemAdded', body: ['item' => 'banana']),
        ];

        $projection->applyAll($events);

        self::assertSame(['apple', 'banana'], $projection->getState('items'));
    }

    public function test_projection_ignores_unregistered_events() : void
    {
        $projection = new Projection();
        $projection->setState('count', 0);

        $envelope = MessageEnvelope::create(type: 'UnknownEvent', body: []);
        $projection->apply($envelope);

        self::assertSame(0, $projection->getState('count'));
    }

    public function test_projection_reset() : void
    {
        $projection = new Projection();
        $projection->setState('key', 'value');
        $projection->reset();

        self::assertSame([], $projection->state());
    }

    // -- PublishToOutbox tests

    public function test_publish_to_outbox() : void
    {
        $store = new InMemoryOutboxStore();
        $publisher = new PublishToOutbox($store);

        $envelope = $publisher->execute(
            eventType: 'UserRegistered',
            body: ['userId' => '123'],
        );

        self::assertSame('UserRegistered', $envelope->type);
        self::assertSame(1, $store->pendingCount());
    }

    public function test_publish_to_outbox_with_correlation() : void
    {
        $store = new InMemoryOutboxStore();
        $publisher = new PublishToOutbox($store);

        $envelope = $publisher->execute(
            eventType: 'OrderPlaced',
            body: ['orderId' => '456'],
            correlationId: 'my-correlation',
        );

        self::assertSame('my-correlation', $envelope->correlationId);
    }

    // -- Composition: outbox + inbox + projection

    public function test_outbox_inbox_projection_composition() : void
    {
        $store = new InMemoryOutboxStore();
        $publisher = new PublishToOutbox($store);
        $inbox = new InMemoryInbox();
        $projection = new Projection();

        $projection->on(
            'UserRegistered',
            static function (MessageEnvelope $event) use ($projection) : void {
                $projection->setState('lastUser', $event->body['userId']);
            },
        );

        // Publish to outbox
        $envelope = $publisher->execute(
            eventType: 'UserRegistered',
            body: ['userId' => 'user-1'],
        );

        // Dequeue and process (simulating outbox dispatcher)
        $pending = $store->dequeue();
        foreach ($pending as $entry) {
            $eventEnvelope = MessageEnvelope::fromArray($entry['payload']);

            if ($inbox->isDuplicate($eventEnvelope->messageId)) {
                continue;
            }

            $projection->apply($eventEnvelope);
            $inbox->markProcessed($eventEnvelope->messageId);
            $store->markProcessed($entry['id']);
        }

        self::assertSame('user-1', $projection->getState('lastUser'));
        self::assertSame(0, $store->pendingCount());
        self::assertTrue($inbox->isDuplicate($envelope->messageId));
    }
}
