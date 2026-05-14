<?php

declare(strict_types=1);

namespace Avax\Tests\Feature\Framework;

use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Capabilities\Runtime\ResetApplicationState;
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
    public function request_a_state_does_not_leak_into_request_b_after_reset(): void
    {
        // Build the core runtime components directly
        $requestScopeStore = new RequestScopeStore();
        $runtimeContext = new RuntimeContext();
        $stateResetRegistry = new StateResetRegistry();
        $stateResetRegistry->register(name: 'request-scopes', state: $requestScopeStore);
        $stateResetRegistry->register(name: 'runtime-context', state: $runtimeContext);

        // --- Request A: simulate mutating request-scoped state ---
        $requestA = new RuntimeRequest(method: 'GET', uri: '/request-a');

        // Open a request scope and write request-A-specific data
        $openScopeA = new OpenHttpRequestScope(
            runtimeContext: $runtimeContext,
            requestScopes : $requestScopeStore,
        );
        $openScopeA->open(request: $requestA);

        // Simulate session data mutation
        $requestScope = $requestScopeStore->current();
        $requestScope->write(key: 'session_user_id', value: 42);
        $requestScope->write(key: 'session_role', value: 'admin');
        $requestScope->write(key: 'session_tenant', value: 'tenant-a');

        // Simulate auth context
        $runtimeContext->recordResult(
            result: RuntimeResult::fromConsoleOutput(output: 'request-a-response', exitCode: 200),
        );

        // Close request A scope
        $closeScopeA = new CloseHttpRequestScope(requestScopes: $requestScopeStore);
        $closeScopeA->close();

        // Verify request A data was stored
        self::assertNotNull($runtimeContext->lastResult());

        // --- Reset all state ---
        $stateResetReport = $stateResetRegistry->resetAll();
        self::assertNotEmpty($stateResetReport->resetComponents());

        // --- Request B: handle a completely different request ---
        $requestB = new RuntimeRequest(method: 'GET', uri: '/request-b');

        $openScopeB = new OpenHttpRequestScope(
            runtimeContext: $runtimeContext,
            requestScopes : $requestScopeStore,
        );
        $openScopeB->open(request: $requestB);

        // Request B should have a fresh scope with no Request A data
        $scopeB = $requestScopeStore->current();

        // Assert: Request A session data must NOT be present
        self::assertNull(
            $scopeB->read(key: 'session_user_id'),
            'Request A session_user_id must not leak into Request B',
        );
        self::assertNull(
            $scopeB->read(key: 'session_role'),
            'Request A session_role must not leak into Request B',
        );
        self::assertNull(
            $scopeB->read(key: 'session_tenant'),
            'Request A session_tenant must not leak into Request B',
        );

        // Request B can write its own data
        $scopeB->write(key: 'session_user_id', value: 99);
        self::assertSame(99, $scopeB->read(key: 'session_user_id'));

        // Close request B scope
        $closeScopeB = new CloseHttpRequestScope(requestScopes: $requestScopeStore);
        $closeScopeB->close();
    }

    #[Test]
    public function runtime_context_is_cleared_between_requests(): void
    {
        $requestScopeStore = new RequestScopeStore();
        $runtimeContext = new RuntimeContext();
        $stateResetRegistry = new StateResetRegistry();
        $stateResetRegistry->register(name: 'request-scopes', state: $requestScopeStore);
        $stateResetRegistry->register(name: 'runtime-context', state: $runtimeContext);

        // Simulate request processing
        $runtimeRequest = new RuntimeRequest(method: 'POST', uri: '/api/login');

        // Set context as if request A was processed
        $openHttpRequestScope = new OpenHttpRequestScope(
            runtimeContext: $runtimeContext,
            requestScopes : $requestScopeStore,
        );
        $openHttpRequestScope->open(request: $runtimeRequest);

        $runtimeContext->recordResult(
            result: RuntimeResult::fromConsoleOutput(output: '{"user":"alice"}', exitCode: 200),
        );

        self::assertNotNull($runtimeContext->lastResult());
        $request = $runtimeContext->currentRequest();
        self::assertNotNull($request);
        self::assertSame('POST', $request->method());
        self::assertSame('/api/login', $request->uri());

        // Reset
        $stateResetRegistry->resetAll();

        // Context should be clean
        self::assertNull($runtimeContext->lastResult(), 'Last result should be null after reset');
        self::assertNull($runtimeContext->currentRequest(), 'Current request should be null after reset');
        self::assertNull($runtimeContext->currentScopeId(), 'Current scope ID should be null after reset');
    }

    #[Test]
    public function multiple_sequential_requests_remain_isolated(): void
    {
        $requestScopeStore = new RequestScopeStore();
        $runtimeContext = new RuntimeContext();
        $stateResetRegistry = new StateResetRegistry();
        $stateResetRegistry->register(name: 'request-scopes', state: $requestScopeStore);
        $stateResetRegistry->register(name: 'runtime-context', state: $runtimeContext);

        // Process 5 sequential requests, each with different data
        for ($i = 1; $i <= 5; $i++) {
            $request = new RuntimeRequest(method: 'GET', uri: '/page-'.$i);

            $openScope = new OpenHttpRequestScope(
                runtimeContext: $runtimeContext,
                requestScopes : $requestScopeStore,
            );
            $openScope->open(request: $request);

            $scope = $requestScopeStore->current();
            $scope->write(key: 'page_number', value: $i);
            $scope->write(key: 'page_uri', value: '/page-'.$i);

            // Verify only current request's data is visible
            self::assertSame($i, $scope->read(key: 'page_number'));
            self::assertSame('/page-'.$i, $scope->read(key: 'page_uri'));

            $runtimeContext->recordResult(
                result: RuntimeResult::fromConsoleOutput(output: 'page-'.$i, exitCode: 200),
            );

            $closeScope = new CloseHttpRequestScope(requestScopes: $requestScopeStore);
            $closeScope->close();

            // Reset between requests (simulating long-lived runtime behavior)
            $stateResetRegistry->resetAll();

            // After reset, context should be clean
            self::assertNull($runtimeContext->lastResult());
            self::assertNull($runtimeContext->currentRequest());
        }
    }

    #[Test]
    public function state_reset_registry_resets_all_registered_components(): void
    {
        $stateResetRegistry = new StateResetRegistry();

        // Create a mock resettable that tracks reset calls
        $mockResettable = new class () implements ResettableState {
            public int $resetCount = 0;

            public function resetState(): void
            {
                $this->resetCount++;
            }
        };

        $stateResetRegistry->register(name: 'test-component', state: $mockResettable);

        $stateResetReport = $stateResetRegistry->resetAll();

        self::assertSame(
            1,
            $mockResettable->resetCount,
            'ResettableState::resetState should be called once',
        );
        self::assertCount(
            1,
            $stateResetReport->resetComponents(),
            'Reset report should contain one component',
        );
    }

    #[Test]
    public function request_scope_store_resets_properly(): void
    {
        $requestScopeStore = new RequestScopeStore();

        // Open and populate a scope
        $requestScope = $requestScopeStore->open();
        $requestScope->write(key: 'leaked_data', value: 'should-not-persist');
        $requestScope->write(key: 'user_id', value: 12345);

        self::assertTrue($requestScopeStore->hasCurrent());
        self::assertSame('should-not-persist', $requestScope->read(key: 'leaked_data'));

        // Reset the store
        $requestScopeStore->resetState();

        self::assertFalse(
            $requestScopeStore->hasCurrent(),
            'Store should not have current scope after reset',
        );

        // Open a new scope -- should be completely fresh
        $newScope = $requestScopeStore->open();
        self::assertNull(
            $newScope->read(key: 'leaked_data'),
            'Leaked data from previous scope must not be present',
        );
        self::assertNull(
            $newScope->read(key: 'user_id'),
            'User ID from previous scope must not be present',
        );
    }

    #[Test]
    public function runtime_safety_detects_transaction_leaks(): void
    {
        $stateResetRegistry = new StateResetRegistry();
        $runtimeSafety = new ResetApplicationState(
            stateResetRegistry: $stateResetRegistry,
        );

        // Simulate a transaction starting
        $runtimeSafety->trackTransaction(connectionName: 'default');
        $runtimeSafety->trackTransaction(connectionName: 'analytics');

        self::assertTrue($runtimeSafety->hasTransactionLeaks());
        self::assertSame(2, $runtimeSafety->transactionLeakCount());

        $leaks = $runtimeSafety->detectTransactionLeaks();
        self::assertCount(2, $leaks);
        self::assertContains('default', $leaks);
        self::assertContains('analytics', $leaks);

        // Complete one transaction
        $runtimeSafety->completeTransaction(connectionName: 'default');
        self::assertSame(1, $runtimeSafety->transactionLeakCount());

        // Complete the other
        $runtimeSafety->completeTransaction(connectionName: 'analytics');
        self::assertFalse($runtimeSafety->hasTransactionLeaks());
        self::assertSame(0, $runtimeSafety->transactionLeakCount());
    }
}
