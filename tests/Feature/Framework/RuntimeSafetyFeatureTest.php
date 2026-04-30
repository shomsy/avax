<?php

declare(strict_types=1);

namespace Avax\Tests\Feature\Framework;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Capabilities\Runtime\RuntimeSafety;
use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Flows\HandleIncomingHttp\CloseHttpRequestScope;
use Avax\Framework\System\Flows\HandleIncomingHttp\OpenHttpRequestScope;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Runtime Safety Feature Test.
 *
 * Verifies that the framework properly isolates state between sequential
 * requests in the same process -- critical for long-lived runtimes
 * (Swoole, RoadRunner, FrankenPHP, Workerman).
 *
 * Tests:
 * - Boot framework
 * - Handle request A (mutate session, set auth context)
 * - Reset state
 * - Handle request B
 * - Assert request A state does not leak into request B
 */
final class RuntimeSafetyFeatureTest extends TestCase
{
    #[Test]
    public function request_a_state_does_not_leak_into_request_b_after_reset() : void
    {
        // Build the core runtime components directly
        $scopeStore = new RequestScopeStore();
        $context    = new RuntimeContext();
        $registry   = new StateResetRegistry();
        $registry->register(name: 'request-scopes', state: $scopeStore);
        $registry->register(name: 'runtime-context', state: $context);

        // --- Request A: simulate mutating request-scoped state ---
        $requestA = new RuntimeRequest(method: 'GET', uri: '/request-a');

        // Open a request scope and write request-A-specific data
        $openScopeA = new OpenHttpRequestScope(
            requestScopes : $scopeStore,
            runtimeContext: $context,
        );
        $openScopeA->open(request: $requestA);

        // Simulate session data mutation
        $scopeA = $scopeStore->current();
        $scopeA->write(key: 'session_user_id', value: 42);
        $scopeA->write(key: 'session_role', value: 'admin');
        $scopeA->write(key: 'session_tenant', value: 'tenant-a');

        // Simulate auth context
        $context->recordResult(
            result: RuntimeResult::fromConsoleOutput(output: 'request-a-response', exitCode: 200),
        );

        // Close request A scope
        $closeScopeA = new CloseHttpRequestScope(requestScopes: $scopeStore);
        $closeScopeA->close();

        // Verify request A data was stored
        self::assertNotNull($context->lastResult());

        // --- Reset all state ---
        $report = $registry->resetAll();
        self::assertNotEmpty($report->resetComponents());

        // --- Request B: handle a completely different request ---
        $requestB = new RuntimeRequest(method: 'GET', uri: '/request-b');

        $openScopeB = new OpenHttpRequestScope(
            requestScopes : $scopeStore,
            runtimeContext: $context,
        );
        $openScopeB->open(request: $requestB);

        // Request B should have a fresh scope with no Request A data
        $scopeB = $scopeStore->current();

        // Assert: Request A session data must NOT be present
        self::assertNull($scopeB->read(key: 'session_user_id'),
                         'Request A session_user_id must not leak into Request B');
        self::assertNull($scopeB->read(key: 'session_role'),
                         'Request A session_role must not leak into Request B');
        self::assertNull($scopeB->read(key: 'session_tenant'),
                         'Request A session_tenant must not leak into Request B');

        // Request B can write its own data
        $scopeB->write(key: 'session_user_id', value: 99);
        self::assertSame(99, $scopeB->read(key: 'session_user_id'));

        // Close request B scope
        $closeScopeB = new CloseHttpRequestScope(requestScopes: $scopeStore);
        $closeScopeB->close();
    }

    #[Test]
    public function runtime_context_is_cleared_between_requests() : void
    {
        $scopeStore = new RequestScopeStore();
        $context    = new RuntimeContext();
        $registry   = new StateResetRegistry();
        $registry->register(name: 'request-scopes', state: $scopeStore);
        $registry->register(name: 'runtime-context', state: $context);

        // Simulate request processing
        $requestA = new RuntimeRequest(method: 'POST', uri: '/api/login');

        // Set context as if request A was processed
        $openScope = new OpenHttpRequestScope(
            requestScopes : $scopeStore,
            runtimeContext: $context,
        );
        $openScope->open(request: $requestA);

        $context->recordResult(
            result: RuntimeResult::fromConsoleOutput(output: '{"user":"alice"}', exitCode: 200),
        );

        self::assertNotNull($context->lastResult());
        self::assertSame('POST', $context->currentRequest()->method());
        self::assertSame('/api/login', $context->currentRequest()->uri());

        // Reset
        $registry->resetAll();

        // Context should be clean
        self::assertNull($context->lastResult(), 'Last result should be null after reset');
        self::assertNull($context->currentRequest(), 'Current request should be null after reset');
        self::assertNull($context->currentScopeId(), 'Current scope ID should be null after reset');
    }

    #[Test]
    public function multiple_sequential_requests_remain_isolated() : void
    {
        $scopeStore = new RequestScopeStore();
        $context    = new RuntimeContext();
        $registry   = new StateResetRegistry();
        $registry->register(name: 'request-scopes', state: $scopeStore);
        $registry->register(name: 'runtime-context', state: $context);

        // Process 5 sequential requests, each with different data
        for ($i = 1; $i <= 5; $i++) {
            $request = new RuntimeRequest(method: 'GET', uri: "/page-$i");

            $openScope = new OpenHttpRequestScope(
                requestScopes : $scopeStore,
                runtimeContext: $context,
            );
            $openScope->open(request: $request);

            $scope = $scopeStore->current();
            $scope->write(key: 'page_number', value: $i);
            $scope->write(key: 'page_uri', value: "/page-$i");

            // Verify only current request's data is visible
            self::assertSame($i, $scope->read(key: 'page_number'));
            self::assertSame("/page-$i", $scope->read(key: 'page_uri'));

            $context->recordResult(
                result: RuntimeResult::fromConsoleOutput(output: "page-$i", exitCode: 200),
            );

            $closeScope = new CloseHttpRequestScope(requestScopes: $scopeStore);
            $closeScope->close();

            // Reset between requests (simulating long-lived runtime behavior)
            $registry->resetAll();

            // After reset, context should be clean
            self::assertNull($context->lastResult());
            self::assertNull($context->currentRequest());
        }
    }

    #[Test]
    public function state_reset_registry_resets_all_registered_components() : void
    {
        $registry = new StateResetRegistry();

        // Create a mock resettable that tracks reset calls
        $mockResettable = new class implements ResettableState {
            public int $resetCount = 0;

            public function resetState() : void { $this->resetCount++; }
        };

        $registry->register(name: 'test-component', state: $mockResettable);

        $report = $registry->resetAll();

        self::assertSame(1, $mockResettable->resetCount,
                         'ResettableState::resetState should be called once');
        self::assertCount(1, $report->resetComponents(),
                          'Reset report should contain one component');
    }

    #[Test]
    public function request_scope_store_resets_properly() : void
    {
        $store = new RequestScopeStore();

        // Open and populate a scope
        $scope = $store->open();
        $scope->write(key: 'leaked_data', value: 'should-not-persist');
        $scope->write(key: 'user_id', value: 12345);

        self::assertTrue($store->hasCurrent());
        self::assertSame('should-not-persist', $scope->read(key: 'leaked_data'));

        // Reset the store
        $store->resetState();

        self::assertFalse($store->hasCurrent(),
                          'Store should not have current scope after reset');

        // Open a new scope -- should be completely fresh
        $newScope = $store->open();
        self::assertNull($newScope->read(key: 'leaked_data'),
                         'Leaked data from previous scope must not be present');
        self::assertNull($newScope->read(key: 'user_id'),
                         'User ID from previous scope must not be present');
    }

    #[Test]
    public function runtime_safety_detects_transaction_leaks() : void
    {
        $registry = new StateResetRegistry();
        $safety   = new RuntimeSafety(
            stateResetRegistry: $registry,
        );

        // Simulate a transaction starting
        $safety->trackTransaction(connectionName: 'default');
        $safety->trackTransaction(connectionName: 'analytics');

        self::assertTrue($safety->hasTransactionLeaks());
        self::assertSame(2, $safety->transactionLeakCount());

        $leaks = $safety->detectTransactionLeaks();
        self::assertCount(2, $leaks);
        self::assertContains('default', $leaks);
        self::assertContains('analytics', $leaks);

        // Complete one transaction
        $safety->completeTransaction(connectionName: 'default');
        self::assertSame(1, $safety->transactionLeakCount());

        // Complete the other
        $safety->completeTransaction(connectionName: 'analytics');
        self::assertFalse($safety->hasTransactionLeaks());
        self::assertSame(0, $safety->transactionLeakCount());
    }
}
