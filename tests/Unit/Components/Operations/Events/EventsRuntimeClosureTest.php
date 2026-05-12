<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Events;

use Avax\Components\Operations\Events\System\Capabilities\InvokeEventListener\InvokeEventListener;
use Avax\Components\Operations\Events\System\Capabilities\Psr14\Psr14EventDispatcherAdapter;
use Avax\Components\Operations\Events\System\Capabilities\Psr14\Psr14ListenerProviderAdapter;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Capabilities\ResolveEventListeners\ResolveEventListeners;
use Avax\Components\Operations\Events\System\Flows\CompileEventListeners\CompileEventListeners;
use Avax\Components\Operations\Events\System\Flows\EmitEvent\EmitEvent;
use Avax\Components\Operations\Events\System\Foundation\CompiledListener;
use Avax\Components\Operations\Events\System\Foundation\CompiledListenerRegistry;
use Avax\Components\Operations\Events\System\Foundation\EventEmitter;
use Avax\Components\Operations\Events\System\Foundation\GlobalEventListenerState;
use Avax\Components\Operations\Events\System\Foundation\ListenerExecutionMode;
use Avax\Components\Operations\Events\System\Foundation\ListenerRegistration;
use Avax\Components\Operations\Events\System\Foundation\ListenerSource;
use Avax\Components\Operations\Events\System\Foundation\ListensTo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

use function Avax\Components\Operations\Events\System\PublicSurface\emit;
use function Avax\Components\Operations\Events\System\PublicSurface\onEvent;
use function Avax\Components\Operations\Events\System\PublicSurface\onEventSetRegistry;

final class EventsRuntimeClosureTest extends TestCase
{
    private ListenerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new ListenerRegistry();
        onEventSetRegistry($this->registry);
    }

    protected function tearDown(): void
    {
        GlobalEventListenerState::reset();
    }

    // ============================================================
    // V5.7-04: emit() Public Surface
    // ============================================================

    #[Test]
    public function emit_returns_same_event_object(): void
    {
        $compiled = new CompiledListenerRegistry();
        $compiled->freeze();

        $emitter = new EventEmitter($compiled);
        GlobalEventListenerState::setEmitter($emitter);

        $event = new class {
            public string $id = 'test-123';
        };

        $result = emit($event); // @phpstan-ignore-line
        self::assertSame($event, $result);
        self::assertSame('test-123', $result->id);
    }

    #[Test]
    public function emit_works_with_no_listeners(): void
    {
        $compiled = new CompiledListenerRegistry();
        $compiled->freeze();

        $emitter = new EventEmitter($compiled);
        GlobalEventListenerState::setEmitter($emitter);

        $event = new \stdClass();
        $result = emit($event);

        self::assertSame($event, $result);
    }

    #[Test]
    public function emit_does_not_register_listeners(): void
    {
        $compiled = new CompiledListenerRegistry();
        $compiled->freeze();

        $emitter = new EventEmitter($compiled);
        GlobalEventListenerState::setEmitter($emitter);

        $event = new \stdClass();
        emit($event);

        // Registry should still be empty — emit() doesn't register.
        self::assertFalse($compiled->hasListeners($event::class));
    }

    #[Test]
    public function emit_does_not_mutate_global_state_beyond_dispatch(): void
    {
        $compiled = new CompiledListenerRegistry();
        $compiled->freeze();

        $emitter = new EventEmitter($compiled);
        GlobalEventListenerState::setEmitter($emitter);

        $beforeCount = $compiled->totalListenerCount();

        emit(new \stdClass());
        emit(new \stdClass());

        self::assertSame($beforeCount, $compiled->totalListenerCount());
    }

    #[Test]
    public function emit_delegates_to_emitter(): void
    {
        $flow = new EmitEvent(new EventEmitter(new CompiledListenerRegistry()));

        $event = new class {
            public bool $touched = false;
        };

        $result = $flow->execute($event);
        self::assertSame($event, $result);
    }

    // ============================================================
    // V5.7-05: ListensTo Attribute
    // ============================================================

    #[Test]
    public function listens_to_attribute_stores_event_class_and_priority(): void
    {
        $attr = new ListensTo(UserRegistered::class, priority: 50);

        self::assertSame(UserRegistered::class, $attr->eventClass);
        self::assertSame(50, $attr->priority);
    }

    #[Test]
    public function listens_to_attribute_default_priority_is_zero(): void
    {
        $attr = new ListensTo(UserRegistered::class);

        self::assertSame(0, $attr->priority);
    }

    #[Test]
    public function listens_to_attribute_is_class_target_only(): void
    {
        $reflection = new \ReflectionClass(ListensTo::class);
        $attribute = $reflection->getAttributes(\Attribute::class)[0]->newInstance();

        self::assertSame(\Attribute::TARGET_CLASS, $attribute->flags);
    }

    // ============================================================
    // V5.7-06: Compiled Listener Registry
    // ============================================================

    #[Test]
    public function dsl_registration_compiles_into_listener_registry(): void
    {
        onEvent('TestEvent')->do(static fn () => null);

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        self::assertTrue($compiled->hasListeners('TestEvent'));
        self::assertCount(1, $compiled->getListenersFor('TestEvent'));
    }

    #[Test]
    public function listens_to_attribute_compiles_into_listener_registry(): void
    {
        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute([TestAttributeListener::class]);

        self::assertTrue($compiled->hasListeners(UserRegistered::class));
        self::assertCount(1, $compiled->getListenersFor(UserRegistered::class));

        $listener = $compiled->getListenersFor(UserRegistered::class)[0];
        self::assertSame(\Avax\Components\Operations\Events\System\Foundation\ListenerSource::Attribute, $listener->source);
    }

    #[Test]
    public function dsl_and_attribute_declarations_share_one_registry(): void
    {
        onEvent(UserRegistered::class)->do(static fn () => null, priority: 50);

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute([TestAttributeListener::class]);

        $listeners = $compiled->getListenersFor(UserRegistered::class);
        self::assertCount(2, $listeners);

        // DSL source and Attribute source both present.
        $sources = array_map(static fn ($l) => $l->source, $listeners);
        self::assertContains(\Avax\Components\Operations\Events\System\Foundation\ListenerSource::Dsl, $sources);
        self::assertContains(\Avax\Components\Operations\Events\System\Foundation\ListenerSource::Attribute, $sources);
    }

    #[Test]
    public function compiled_registry_returns_empty_list_for_unknown_event(): void
    {
        $compiled = new CompiledListenerRegistry();

        self::assertSame([], $compiled->getListenersFor('UnknownEvent'));
    }

    #[Test]
    public function compiled_registry_sorts_by_priority_descending(): void
    {
        $compiled = new CompiledListenerRegistry();

        $compiled->add(new CompiledListener(
            eventClass: 'OrderPlaced',
            listener: static fn () => 'low',
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
        $compiled->add(new CompiledListener(
            eventClass: 'OrderPlaced',
            listener: static fn () => 'high',
            priority: 100,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 1,
        ));
        $compiled->add(new CompiledListener(
            eventClass: 'OrderPlaced',
            listener: static fn () => 'medium',
            priority: 50,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 2,
        ));

        $compiled->freeze();
        $listeners = $compiled->getListenersFor('OrderPlaced');

        self::assertSame('high', ($listeners[0]->listener)());
        self::assertSame('medium', ($listeners[1]->listener)());
        self::assertSame('low', ($listeners[2]->listener)());
    }

    #[Test]
    public function compiled_registry_preserves_order_for_same_priority(): void
    {
        $compiled = new CompiledListenerRegistry();

        $compiled->add(new CompiledListener(
            eventClass: 'AppBooted',
            listener: static fn () => 'first',
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
        $compiled->add(new CompiledListener(
            eventClass: 'AppBooted',
            listener: static fn () => 'second',
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 1,
        ));

        $compiled->freeze();
        $listeners = $compiled->getListenersFor('AppBooted');

        // Same priority → stable sort preserves insertion order.
        self::assertSame('first', ($listeners[0]->listener)());
        self::assertSame('second', ($listeners[1]->listener)());
    }

    #[Test]
    public function compiled_registry_tracks_listener_source(): void
    {
        onEvent('SourceTest')->do(static fn () => null);

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $listeners = $compiled->getListenersFor('SourceTest');
        self::assertSame(ListenerSource::Dsl, $listeners[0]->source);
    }

    #[Test]
    public function compiled_registry_freezes_and_rejects_further_registrations(): void
    {
        $compiled = new CompiledListenerRegistry();
        $compiled->freeze();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot register listener after CompiledListenerRegistry is frozen.');

        $compiled->add(new CompiledListener(
            eventClass: 'Blocked',
            listener: static fn () => null,
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
    }

    #[Test]
    public function event_does_not_need_event_interface(): void
    {
        $plainEvent = new class {
            public string $name = 'plain';
        };

        $received = null;
        onEvent($plainEvent::class)->do(static function ($e) use (&$received): void {
            $received = $e;
        });

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $emitter->emit($plainEvent);

        self::assertSame($plainEvent, $received);
        self::assertSame('plain', $received->name);
    }

    #[Test]
    public function listener_does_not_need_listener_interface(): void
    {
        $called = false;
        $plainListener = static function () use (&$called): void {
            $called = true;
        };

        onEvent('PlainListenerTest')->do($plainListener);

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $event = new class {};

        // Register under the anonymous class name
        $compiled2 = new CompiledListenerRegistry();
        $compiled2->add(new CompiledListener(
            eventClass: $event::class,
            listener: static function () use (&$called): void {
                $called = true;
            },
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
        $compiled2->freeze();

        $emitter2 = new EventEmitter($compiled2);
        $emitter2->emit($event);

        self::assertTrue($called);
    }

    // ============================================================
    // V5.7-07: Dispatch Runtime
    // ============================================================

    #[Test]
    public function emit_invokes_registered_listener(): void
    {
        $called = false;
        onEvent(UserRegistered::class)->do(static function () use (&$called): void {
            $called = true;
        });

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $emitter->emit(new UserRegistered('user-1'));

        self::assertTrue($called);
    }

    #[Test]
    public function emit_invokes_multiple_registered_listeners(): void
    {
        $count = 0;
        onEvent(UserRegistered::class)
            ->do(static function () use (&$count): void { $count++; })
            ->do(static function () use (&$count): void { $count++; })
            ->do(static function () use (&$count): void { $count++; });

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $emitter->emit(new UserRegistered('user-1'));

        self::assertSame(3, $count);
    }

    #[Test]
    public function emit_respects_priority_order(): void
    {
        $order = [];
        onEvent(UserRegistered::class)
            ->do(static function () use (&$order): void { $order[] = 'low'; }, priority: 0)
            ->do(static function () use (&$order): void { $order[] = 'high'; }, priority: 100)
            ->do(static function () use (&$order): void { $order[] = 'medium'; }, priority: 50);

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $emitter->emit(new UserRegistered('user-1'));

        self::assertSame(['high', 'medium', 'low'], $order);
    }

    #[Test]
    public function emit_preserves_registration_order_for_same_priority(): void
    {
        $order = [];
        onEvent(UserRegistered::class)
            ->do(static function () use (&$order): void { $order[] = 'first'; })
            ->do(static function () use (&$order): void { $order[] = 'second'; })
            ->do(static function () use (&$order): void { $order[] = 'third'; });

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $emitter->emit(new UserRegistered('user-1'));

        self::assertSame(['first', 'second', 'third'], $order);
    }

    #[Test]
    public function emit_returns_same_event(): void
    {
        onEvent(UserRegistered::class)->do(static fn () => null);

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $event = new UserRegistered('user-1');

        $result = $emitter->emit($event);

        self::assertSame($event, $result);
    }

    #[Test]
    public function emit_returns_event_when_no_listener_exists(): void
    {
        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $event = new UserRegistered('orphan');

        $result = $emitter->emit($event);

        self::assertSame($event, $result);
    }

    #[Test]
    public function listener_return_value_is_ignored(): void
    {
        onEvent(UserRegistered::class)->do(static function (): string {
            return 'this should be ignored';
        });

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $event = new UserRegistered('user-1');

        $result = $emitter->emit($event);

        self::assertSame($event, $result);
    }

    #[Test]
    public function listener_failure_bubbles(): void
    {
        onEvent(UserRegistered::class)->do(static function (): void {
            throw new \RuntimeException('Listener failed');
        });

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Listener failed');

        $emitter->emit(new UserRegistered('user-1'));
    }

    #[Test]
    public function stopped_event_stops_later_listeners(): void
    {
        $event = new StoppableTestEvent();

        $calls = [];
        onEvent(StoppableTestEvent::class)
            ->do(static function (StoppableTestEvent $e) use (&$calls): void {
                $calls[] = 'first';
                $e->stop();
            })
            ->do(static function () use (&$calls): void {
                $calls[] = 'second';
            });

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $emitter->emit($event);

        self::assertSame(['first'], $calls);
    }

    #[Test]
    public function plain_event_object_dispatches_without_event_interface(): void
    {
        $plainEvent = new class {
            public string $id = 'plain-event-1';
        };

        $received = null;
        onEvent($plainEvent::class)->do(static function ($e) use (&$received): void {
            $received = $e;
        });

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $emitter->emit($plainEvent);

        self::assertNotNull($received);
        self::assertSame($plainEvent, $received);
    }

    #[Test]
    public function invokable_listener_dispatches_without_listener_interface(): void
    {
        onEvent(UserRegistered::class)->do(PlainInvokableListener::class);

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $emitter->emit(new UserRegistered('user-1'));

        self::assertTrue(PlainInvokableListener::$called);
    }

    #[Test]
    public function runtime_dispatch_has_no_attribute_reflection(): void
    {
        // Attribute reflection only happens in CompileEventListeners (boot time).
        // The EventEmitter.resolve/invoker path does not read attributes.

        onEvent(UserRegistered::class)->do(static fn () => null);

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        $emitter = new EventEmitter($compiled);
        $event = new UserRegistered('user-1');

        // Dispatch should work without any attribute scanning.
        $result = $emitter->emit($event);
        self::assertSame($event, $result);

        // No attribute listeners were compiled.
        $listeners = $compiled->getListenersFor(UserRegistered::class);
        $attrListeners = array_filter($listeners, static fn ($l) => $l->source === ListenerSource::Attribute);
        self::assertCount(0, $attrListeners);
    }

    #[Test]
    public function dispatcher_uses_canonical_compiled_registry(): void
    {
        $compiled = new CompiledListenerRegistry();

        $compiled->add(new CompiledListener(
            eventClass: DirectTestEvent::class,
            listener: static function (DirectTestEvent $e): void {
                $e->direct = true;
            },
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
        $compiled->freeze();

        $emitter = new EventEmitter($compiled);
        $event = new DirectTestEvent();

        $emitter->emit($event);

        self::assertTrue($event->direct);
    }

    // ============================================================
    // V5.7-08: PSR-14 Adapter
    // ============================================================

    #[Test]
    public function psr_dispatch_delegates_to_avax_dispatcher(): void
    {
        $compiled = new CompiledListenerRegistry();

        $compiled->add(new CompiledListener(
            eventClass: \stdClass::class,
            listener: static function (\stdClass $e): void {
                $e->psr = true;
            },
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
        $compiled->freeze();

        $avaxEmitter = new EventEmitter($compiled);
        $psrAdapter = new Psr14EventDispatcherAdapter($avaxEmitter);

        $event = new \stdClass();
        $result = $psrAdapter->dispatch($event);

        self::assertTrue($result->psr);
        self::assertSame($event, $result);
    }

    #[Test]
    public function psr_listener_provider_returns_avax_listeners(): void
    {
        $compiled = new CompiledListenerRegistry();

        $compiled->add(new CompiledListener(
            eventClass: \stdClass::class,
            listener: static fn () => null,
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
        $compiled->freeze();

        $provider = new Psr14ListenerProviderAdapter($compiled);
        $listeners = iterator_to_array($provider->getListenersForEvent(new \stdClass()));

        self::assertCount(1, $listeners);
    }

    #[Test]
    public function psr_dispatch_returns_event(): void
    {
        $compiled = new CompiledListenerRegistry();
        $compiled->freeze();

        $avaxEmitter = new EventEmitter($compiled);
        $psrAdapter = new Psr14EventDispatcherAdapter($avaxEmitter);

        $event = new \stdClass();
        $result = $psrAdapter->dispatch($event);

        self::assertInstanceOf(EventDispatcherInterface::class, $psrAdapter);
        self::assertSame($event, $result);
    }

    #[Test]
    public function psr_stoppable_event_stops_propagation(): void
    {
        $compiled = new CompiledListenerRegistry();

        $order = [];
        $compiled->add(new CompiledListener(
            eventClass: StoppableTestEvent::class,
            listener: static function (StoppableTestEvent $e) use (&$order): void {
                $order[] = 'first';
                $e->stop();
            },
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
        $compiled->add(new CompiledListener(
            eventClass: StoppableTestEvent::class,
            listener: static function () use (&$order): void {
                $order[] = 'second';
            },
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 1,
        ));
        $compiled->freeze();

        $avaxEmitter = new EventEmitter($compiled);
        $psrAdapter = new Psr14EventDispatcherAdapter($avaxEmitter);

        $psrAdapter->dispatch(new StoppableTestEvent());

        self::assertSame(['first'], $order);
    }

    #[Test]
    public function psr_adapter_does_not_require_event_interface(): void
    {
        $compiled = new CompiledListenerRegistry();
        $compiled->freeze();

        $avaxEmitter = new EventEmitter($compiled);
        $psrAdapter = new Psr14EventDispatcherAdapter($avaxEmitter);

        $plainEvent = new class {
            public string $data = 'test';
        };

        $result = $psrAdapter->dispatch($plainEvent);
        self::assertSame('test', $result->data);
    }

    #[Test]
    public function psr_adapter_does_not_require_listener_interface(): void
    {
        $compiled = new CompiledListenerRegistry();

        $called = false;
        $compiled->add(new CompiledListener(
            eventClass: \stdClass::class,
            listener: static function () use (&$called): void {
                $called = true;
            },
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
        $compiled->freeze();

        $avaxEmitter = new EventEmitter($compiled);
        $provider = new Psr14ListenerProviderAdapter($compiled);

        self::assertInstanceOf(ListenerProviderInterface::class, $provider);

        $listeners = iterator_to_array($provider->getListenersForEvent(new \stdClass()));
        self::assertCount(1, $listeners);

        $listeners[0](new \stdClass());
        self::assertTrue($called);
    }

    #[Test]
    public function psr_adapter_does_not_bypass_canonical_registry(): void
    {
        $compiled = new CompiledListenerRegistry();
        $compiled->freeze();

        $avaxEmitter = new EventEmitter($compiled);
        $provider = new Psr14ListenerProviderAdapter($compiled);

        // Empty registry → no listeners.
        self::assertCount(0, iterator_to_array($provider->getListenersForEvent(new \stdClass())));
    }

    // ============================================================
    // ResolveEventListeners capability
    // ============================================================

    #[Test]
    public function resolve_returns_callable_for_dsl_listener(): void
    {
        $resolver = new ResolveEventListeners();

        $callable = static fn () => 'resolved';
        $compiled = new CompiledListener(
            eventClass: 'TestEvent',
            listener: $callable,
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        );

        $resolved = $resolver->resolve($compiled);
        self::assertSame('resolved', $resolved());
    }

    #[Test]
    public function resolve_instantiates_class_string_for_attribute_listener(): void
    {
        $resolver = new ResolveEventListeners();

        $compiled = new CompiledListener(
            eventClass: UserRegistered::class,
            listener: PlainInvokableListener::class,
            priority: 0,
            source: ListenerSource::Attribute,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        );

        PlainInvokableListener::$called = false;
        $resolved = $resolver->resolve($compiled);
        $resolved(new UserRegistered('user-1'));

        self::assertTrue(PlainInvokableListener::$called);
    }

    #[Test]
    public function resolve_throws_for_non_existent_class(): void
    {
        $resolver = new ResolveEventListeners();

        $compiled = new CompiledListener(
            eventClass: 'TestEvent',
            listener: 'NonExistentListener',
            priority: 0,
            source: ListenerSource::Attribute,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Listener class "NonExistentListener" does not exist.');

        $resolver->resolve($compiled);
    }

    // ============================================================
    // InvokeEventListener capability
    // ============================================================

    #[Test]
    public function invoker_calls_listener_with_event(): void
    {
        $invoker = new InvokeEventListener();
        $received = null;

        $listener = static function ($e) use (&$received): void {
            $received = $e;
        };

        $event = new \stdClass();
        $event->id = 'invoker-test';

        $invoker->invoke($listener, $event);

        self::assertSame($event, $received);
    }

    // ============================================================
    // CompileEventListeners flow
    // ============================================================

    #[Test]
    public function compile_produces_frozen_registry(): void
    {
        onEvent('CompileTest')->do(static fn () => null);

        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        self::assertTrue($compiled->isFrozen());
    }

    #[Test]
    public function compile_with_no_registrations_produces_empty_frozen_registry(): void
    {
        $compiler = new CompileEventListeners($this->registry);
        $compiled = $compiler->execute();

        self::assertTrue($compiled->isFrozen());
        self::assertCount(0, $compiled->getEventClasses());
    }

    // ============================================================
    // CompiledListenerRegistry utilities
    // ============================================================

    #[Test]
    public function compiled_registry_get_event_classes(): void
    {
        $compiled = new CompiledListenerRegistry();

        $compiled->add(new CompiledListener(
            eventClass: 'EventA',
            listener: static fn () => null,
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
        $compiled->add(new CompiledListener(
            eventClass: 'EventB',
            listener: static fn () => null,
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 1,
        ));

        $classes = $compiled->getEventClasses();
        self::assertContains('EventA', $classes);
        self::assertContains('EventB', $classes);
    }

    #[Test]
    public function compiled_registry_total_listener_count(): void
    {
        $compiled = new CompiledListenerRegistry();

        $compiled->add(new CompiledListener(
            eventClass: 'EventA',
            listener: static fn () => null,
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));
        $compiled->add(new CompiledListener(
            eventClass: 'EventA',
            listener: static fn () => null,
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 1,
        ));
        $compiled->add(new CompiledListener(
            eventClass: 'EventB',
            listener: static fn () => null,
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 2,
        ));

        self::assertSame(3, $compiled->totalListenerCount());
    }

    #[Test]
    public function compiled_registry_get_listeners_for_object(): void
    {
        $compiled = new CompiledListenerRegistry();

        $compiled->add(new CompiledListener(
            eventClass: \stdClass::class,
            listener: static fn () => 'object-test',
            priority: 0,
            source: ListenerSource::Dsl,
            mode: ListenerExecutionMode::Sync,
            order: 0,
        ));

        $compiled->freeze();
        $listeners = $compiled->getListenersFor(new \stdClass());

        self::assertCount(1, $listeners);
        self::assertSame('object-test', ($listeners[0]->listener)());
    }

    // ============================================================
    // RegisterEventDependencies
    // ============================================================

    #[Test]
    public function compile_and_wire_sets_global_emitter(): void
    {
        onEvent('WireTest')->do(static fn () => null);

        \Avax\Components\Operations\Events\System\Configuration\RegisterEventDependencies::compileAndWire($this->registry);

        $event = new \stdClass();
        $event->wired = false;

        // The global emit() should now work.
        $result = emit($event);
        self::assertSame($event, $result);
    }

    // ============================================================
    // ListenerRegistry getAllEvents
    // ============================================================

    #[Test]
    public function listener_registry_get_all_events(): void
    {
        $registry = new ListenerRegistry();

        $registry->subscribe('EventA', static fn () => null);
        $registry->subscribe('EventB', static fn () => null);
        $registry->subscribe('EventA', static fn () => null, priority: 10);

        $events = $registry->getAllEvents();
        self::assertContains('EventA', $events);
        self::assertContains('EventB', $events);
        self::assertCount(2, $events);
    }
}

// ============================================================
// Test Fixtures
// ============================================================

final class UserRegistered
{
    public function __construct(
        public string $userId,
    ) {
    }
}

#[ListensTo(UserRegistered::class, priority: 25)]
final readonly class TestAttributeListener
{
    public function __invoke(UserRegistered $event): void
    {
    }
}

final class StoppableTestEvent
{
    private bool $propagationStopped = false;

    public function stop(): void
    {
        $this->propagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}

final class PlainInvokableListener
{
    public static bool $called = false;

    public function __invoke(UserRegistered $event): void
    {
        self::$called = true;
    }
}

final class DirectTestEvent
{
    public bool $direct = false;
}
