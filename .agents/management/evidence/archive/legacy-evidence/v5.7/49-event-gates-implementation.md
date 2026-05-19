# V5.7-49: Event Gates Implementation

**Date:** 2026-05-13
**Branch:** main
**Stage:** V5.7 Final Acceptance Audit — Event Gates

## Event Gates Implemented

9 new event gate scripts created in `tooling/events/`:

1. **check-fluent-dsl-registration.php** (10 checks)
   - onEvent() exists, returns EventListenerDsl with do() method
   - DSL registers listeners, does not dispatch
   - Uses canonical ListenerRegistry
   - No EventInterface/ListenerInterface requirement

2. **check-event-emission-api.php** (10 checks)
   - emit(object $event): object exists
   - Delegates to EventEmitter
   - Does not register listeners
   - Does not accept class-string/array/DTO
   - No EventInterface requirement

3. **check-listens-to-attribute.php** (11 checks)
   - #[ListensTo] attribute exists, targets classes
   - Declaration only (compile-time)
   - No queued/async/afterCommit production claims
   - No EventInterface/ListenerInterface requirement

4. **check-compiled-listener-registry.php** (20 checks)
   - DSL + attribute sources compile into single CompiledListenerRegistry
   - Priority/order metadata preserved
   - ListenerSource tracking (DSL, Attribute)
   - Registry freezes after compilation

5. **check-dispatch-runtime.php** (12 checks)
   - emit() reaches EventEmitter
   - Reads CompiledListenerRegistry (no reflection)
   - Invokes via ResolveEventListeners + InvokeEventListener
   - Listener failure bubbles by default
   - No-listener returns event unchanged
   - No runtime attribute reflection

6. **check-psr14-interop.php** (13 checks)
   - psr/event-dispatcher installed
   - Psr14EventDispatcherAdapter delegates to AvaX EventEmitter
   - Psr14ListenerProviderAdapter delegates to CompiledListenerRegistry
   - Neither bypasses canonical registry

7. **check-events-no-hot-path-reflection.php** (13 checks)
   - EventEmitter::emit() has no ReflectionClass/getAttributes/ReflectionAttribute
   - CompileEventListeners uses reflection (documented, boot-time only)
   - PSR adapters use compiled registry

8. **check-real-dogfooding.php** (27 checks)
   - SecureRegistrationApi emits UserRegistered
   - onEvent()->do() wiring exists
   - Audit/projection/event-history listeners exist
   - Read model exists (RegisteredUserView)
   - Event-history proof exists
   - Production Event Sourcing NOT claimed

9. **check-event-sourcing-not-default.php** (14 checks)
   - Docs don't claim event sourcing as default persistence
   - Event-history marked reference/proof only
   - Missing ES components documented
   - No premature ES GREEN claim

## All Event Gates Result: PASS (10/10, including pre-existing check-canonical-event-owner.php)
