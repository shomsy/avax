<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\MessageBus\System\Capabilities\Projection;

use Avax\Components\Operations\MessageBus\System\Capabilities\Envelope\MessageEnvelope;
use Avax\Components\Operations\MessageBus\System\Capabilities\Projection\Projection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProjectionTest extends TestCase
{
    #[Test]
    public function it_starts_with_empty_state() : void
    {
        $projection = new Projection();

        self::assertSame([], $projection->state());
    }

    #[Test]
    public function it_sets_and_gets_state() : void
    {
        $projection = new Projection();
        $projection->setState('user_count', 10);

        self::assertSame(10, $projection->getState('user_count'));
    }

    #[Test]
    public function it_returns_default_for_missing_state() : void
    {
        $projection = new Projection();

        self::assertNull($projection->getState('missing'));
        self::assertSame('fallback', $projection->getState('missing', 'fallback'));
    }

    #[Test]
    public function it_registers_handler_for_event_type() : void
    {
        $projection    = new Projection();
        $handlerCalled = false;

        $result = $projection->on('user.created', static function () use (&$handlerCalled) : void {
            $handlerCalled = true;
        });

        self::assertInstanceOf(Projection::class, $result);

        $event = MessageEnvelope::create(type: 'user.created', body: []);
        $projection->apply($event);

        self::assertTrue($handlerCalled);
    }

    #[Test]
    public function it_applies_event_to_handler() : void
    {
        $projection       = new Projection();
        $receivedEnvelope = null;

        $projection->on('order.placed', static function (MessageEnvelope $e) use (&$receivedEnvelope) : void {
            $receivedEnvelope = $e;
        });

        $event = MessageEnvelope::create(type: 'order.placed', body: ['order_id' => 1]);
        $projection->apply($event);

        self::assertSame($event, $receivedEnvelope);
    }

    #[Test]
    public function it_ignores_events_without_registered_handler() : void
    {
        $projection = new Projection();

        $event = MessageEnvelope::create(type: 'unknown.event', body: []);

        $projection->apply($event);

        self::assertSame([], $projection->state());
    }

    #[Test]
    public function it_builds_read_model_from_events() : void
    {
        $projection = new Projection();

        $projection->on('user.created', static function (MessageEnvelope $e) use ($projection) : void {
            $count = $projection->getState('user_count', 0);
            $projection->setState('user_count', $count + 1);
        });

        $projection->apply(MessageEnvelope::create(type: 'user.created', body: []));
        $projection->apply(MessageEnvelope::create(type: 'user.created', body: []));
        $projection->apply(MessageEnvelope::create(type: 'user.created', body: []));

        self::assertSame(3, $projection->getState('user_count'));
    }

    #[Test]
    public function it_applies_multiple_events_in_order() : void
    {
        $projection = new Projection();
        $order      = [];

        $projection->on('event.a', static function () use (&$order) : void {
            $order[] = 'a';
        });
        $projection->on('event.b', static function () use (&$order) : void {
            $order[] = 'b';
        });

        $events = [
            MessageEnvelope::create(type: 'event.b', body: []),
            MessageEnvelope::create(type: 'event.a', body: []),
            MessageEnvelope::create(type: 'event.b', body: []),
        ];

        $projection->applyAll($events);

        self::assertSame(['b', 'a', 'b'], $order);
    }

    #[Test]
    public function it_applies_all_with_empty_array() : void
    {
        $projection = new Projection();

        $projection->applyAll([]);

        self::assertSame([], $projection->state());
    }

    #[Test]
    public function it_resets_state() : void
    {
        $projection = new Projection();
        $projection->setState('key1', 'value1');
        $projection->setState('key2', 'value2');

        $projection->reset();

        self::assertSame([], $projection->state());
    }

    #[Test]
    public function it_still_processes_events_after_reset() : void
    {
        $projection = new Projection();

        $projection->on('item.added', static function (MessageEnvelope $e) use ($projection) : void {
            $items   = $projection->getState('items', []);
            $items[] = $e->body['name'];
            $projection->setState('items', $items);
        });

        $projection->apply(MessageEnvelope::create(type: 'item.added', body: ['name' => 'A']));
        $projection->reset();
        $projection->apply(MessageEnvelope::create(type: 'item.added', body: ['name' => 'B']));

        self::assertSame(['B'], $projection->getState('items'));
    }

    #[Test]
    public function it_replaces_handler_when_registered_twice() : void
    {
        $projection   = new Projection();
        $firstCalled  = false;
        $secondCalled = false;

        $projection->on('event.x', static function () use (&$firstCalled) : void {
            $firstCalled = true;
        });
        $projection->on('event.x', static function () use (&$secondCalled) : void {
            $secondCalled = true;
        });

        $projection->apply(MessageEnvelope::create(type: 'event.x', body: []));

        self::assertFalse($firstCalled);
        self::assertTrue($secondCalled);
    }

    #[Test]
    public function it_handles_complex_state_updates() : void
    {
        $projection = new Projection();

        $projection->on('cart.item_added', static function (MessageEnvelope $e) use ($projection) : void {
            $items   = $projection->getState('cart_items', []);
            $items[] = $e->body['item'];
            $projection->setState('cart_items', $items);
        });

        $projection->on('cart.item_removed', static function (MessageEnvelope $e) use ($projection) : void {
            $items = $projection->getState('cart_items', []);
            $items = array_values(array_filter($items, static fn ($item) => $item !== $e->body['item']));
            $projection->setState('cart_items', $items);
        });

        $projection->apply(MessageEnvelope::create(type: 'cart.item_added', body: ['item' => 'book']));
        $projection->apply(MessageEnvelope::create(type: 'cart.item_added', body: ['item' => 'pen']));
        $projection->apply(MessageEnvelope::create(type: 'cart.item_added', body: ['item' => 'book']));
        $projection->apply(MessageEnvelope::create(type: 'cart.item_removed', body: ['item' => 'pen']));

        self::assertSame(['book', 'book'], $projection->getState('cart_items'));
    }

    #[Test]
    public function it_supports_chaining_on_calls() : void
    {
        $projection = new Projection();

        $result = $projection
            ->on('a', static function () : void {})
            ->on('b', static function () : void {})
            ->on('c', static function () : void {});

        self::assertInstanceOf(Projection::class, $result);
    }

    #[Test]
    public function it_handles_state_with_null_values() : void
    {
        $projection = new Projection();
        $projection->setState('nullable', null);

        // Projection uses ?? operator, so null values return the default
        self::assertNull($projection->getState('nullable'));
        self::assertSame('default', $projection->getState('nullable', 'default'));
        self::assertTrue(array_key_exists('nullable', $projection->state()));
        self::assertNull($projection->state()['nullable']);
    }

    #[Test]
    public function it_handles_state_with_array_values() : void
    {
        $projection = new Projection();
        $data       = ['nested' => ['key' => 'value']];
        $projection->setState('complex', $data);

        self::assertSame($data, $projection->getState('complex'));
    }
}
