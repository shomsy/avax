<?php

declare(strict_types=1);

namespace Avax\Tests\Feature\Framework;

use Avax\Components\HTTP\Session\System\Capabilities\Storage\ArraySessionStore;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeAlreadyClosed;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeNotOpen;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Flows\HandleIncomingHttp\CloseHttpRequestScope;
use Avax\Framework\System\Flows\HandleIncomingHttp\OpenHttpRequestScope;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Request Scope Isolation Feature Test.
 *
 * Verifies that request-scoped services are properly reset between requests:
 * - Request-scoped services reset properly
 * - Session state is request-bound
 * - Auth current user resets between requests
 */
final class RequestScopeIsolationFeatureTest extends TestCase
{
    #[Test]
    public function request_scoped_services_reset_properly() : void
    {
        $store = new RequestScopeStore();

        // Request 1: open scope, write service data
        $scope1 = $store->open();
        $scope1->write(key: 'db_connection', value: 'primary-conn-1');
        $scope1->write(key: 'query_builder', value: ['table' => 'users', 'limit' => 10]);
        $scope1->write(key: 'service_container_scope', value: 'scope-1-id');

        self::assertSame('primary-conn-1', $scope1->read(key: 'db_connection'));
        self::assertSame('scope-1-id', $scope1->read(key: 'service_container_scope'));

        // Close scope (simulates end of request)
        $scope1->close();

        // Verify scope is closed
        self::assertFalse($scope1->isOpen());

        // Request 2: open new scope
        $scope2 = $store->open();

        // All request-1 data must be gone
        self::assertNull($scope2->read(key: 'db_connection'),
                         'DB connection from request 1 must not persist');
        self::assertNull($scope2->read(key: 'query_builder'),
                         'Query builder from request 1 must not persist');
        self::assertNull($scope2->read(key: 'service_container_scope'),
                         'Service container scope from request 1 must not persist');

        // Request 2 writes its own data
        $scope2->write(key: 'db_connection', value: 'replica-conn-2');
        self::assertSame('replica-conn-2', $scope2->read(key: 'db_connection'));

        $scope2->close();
    }

    #[Test]
    public function session_state_is_request_bound() : void
    {
        $store = new ArraySessionStore();

        // --- Session A (Request A) ---
        $scopeA = new SessionScope(store: $store);

        // Manually set started state (avoiding native session_start in tests)
        $reflectionA  = new ReflectionClass($scopeA);
        $startedPropA = $reflectionA->getProperty('started');
        $startedPropA->setValue($scopeA, true);
        $idPropA = $reflectionA->getProperty('id');
        $idPropA->setValue($scopeA, 'session-request-a');

        // Set session data for request A
        $scopeA->set(key: 'user_id', value: 100);
        $scopeA->set(key: 'user_email', value: 'alice@example.com');
        $scopeA->set(key: 'cart_items', value: ['item-1', 'item-2']);

        self::assertSame(100, $scopeA->get(key: 'user_id'));
        self::assertSame('alice@example.com', $scopeA->get(key: 'user_email'));
        self::assertCount(2, $scopeA->get(key: 'cart_items'));

        // Destroy session A
        $scopeA->destroy();

        self::assertFalse($scopeA->isStarted());
        self::assertSame('', $scopeA->id());

        // --- Session B (Request B) ---
        $scopeB = new SessionScope(store: $store);

        // Manually start session B
        $reflectionB  = new ReflectionClass($scopeB);
        $startedPropB = $reflectionB->getProperty('started');
        $startedPropB->setValue($scopeB, true);
        $idPropB = $reflectionB->getProperty('id');
        $idPropB->setValue($scopeB, 'session-request-b');

        // Session B should NOT have any of Session A's data
        self::assertNull($scopeB->get(key: 'user_id'),
                         'User ID from session A must not leak into session B');
        self::assertNull($scopeB->get(key: 'user_email'),
                         'User email from session A must not leak into session B');
        self::assertNull($scopeB->get(key: 'cart_items'),
                         'Cart items from session A must not leak into session B');

        // Session B sets its own data
        $scopeB->set(key: 'user_id', value: 200);
        $scopeB->set(key: 'user_email', value: 'bob@example.com');

        self::assertSame(200, $scopeB->get(key: 'user_id'));
        self::assertSame('bob@example.com', $scopeB->get(key: 'user_email'));

        $scopeB->destroy();
    }

    #[Test]
    public function auth_current_user_resets_between_requests() : void
    {
        $context    = new RuntimeContext();
        $scopeStore = new RequestScopeStore();
        $registry   = new StateResetRegistry();
        $registry->register(name: 'request-scopes', state: $scopeStore);
        $registry->register(name: 'runtime-context', state: $context);

        // --- Request A: authenticated as admin ---
        $requestA = new RuntimeRequest(method: 'GET', uri: '/admin/dashboard');

        $openA = new OpenHttpRequestScope(
            requestScopes : $scopeStore,
            runtimeContext: $context,
        );
        $openA->open(request: $requestA);

        // Simulate auth context in request scope
        $scopeA = $scopeStore->current();
        $scopeA->write(key: 'auth_user_id', value: 1);
        $scopeA->write(key: 'auth_user_name', value: 'admin');
        $scopeA->write(key: 'auth_user_role', value: 'administrator');
        $scopeA->write(key: 'auth_authenticated', value: true);

        self::assertTrue($scopeA->read(key: 'auth_authenticated'));
        self::assertSame('admin', $scopeA->read(key: 'auth_user_name'));

        $closeA = new CloseHttpRequestScope(requestScopes: $scopeStore);
        $closeA->close();

        // Reset state
        $registry->resetAll();

        // --- Request B: unauthenticated user ---
        $requestB = new RuntimeRequest(method: 'GET', uri: '/public/home');

        $openB = new OpenHttpRequestScope(
            requestScopes : $scopeStore,
            runtimeContext: $context,
        );
        $openB->open(request: $requestB);

        $scopeB = $scopeStore->current();

        // Auth context from Request A must NOT persist
        self::assertNull($scopeB->read(key: 'auth_user_id'),
                         'Auth user_id from request A must not leak into request B');
        self::assertNull($scopeB->read(key: 'auth_user_name'),
                         'Auth user_name from request A must not leak into request B');
        self::assertNull($scopeB->read(key: 'auth_user_role'),
                         'Auth user_role from request A must not leak into request B');
        self::assertNull($scopeB->read(key: 'auth_authenticated'),
                         'Auth authenticated flag from request A must not leak into request B');

        $closeB = new CloseHttpRequestScope(requestScopes: $scopeStore);
        $closeB->close();
    }

    #[Test]
    public function request_scope_id_is_unique_per_request() : void
    {
        $store = new RequestScopeStore();

        $scope1 = $store->open();
        $id1    = $scope1->id();

        $scope1->close();
        $store->resetState();

        $scope2 = $store->open();
        $id2    = $scope2->id();

        $scope2->close();

        // Each request should get a unique scope ID
        self::assertNotSame(
            $id1->toString(),
            $id2->toString(),
            'Each request must receive a unique scope ID',
        );
    }

    #[Test]
    public function closed_scope_throws_on_access() : void
    {
        $scope = new RequestScope(id: RequestScopeId::generate());
        $scope->write(key: 'data', value: 'sensitive');
        $scope->close();

        $this->expectException(RequestScopeAlreadyClosed::class);
        $scope->read(key: 'data');
    }

    #[Test]
    public function request_scope_operations_throw_when_no_scope_open() : void
    {
        $store = new RequestScopeStore();

        // Without opening a scope, operations should fail
        $this->expectException(RequestScopeNotOpen::class);
        $store->current();
    }

    #[Test]
    public function concurrent_like_sequential_requests_maintain_isolation() : void
    {
        // Simulates what happens in a worker-loop runtime where
        // requests are handled sequentially in the same process
        $context    = new RuntimeContext();
        $scopeStore = new RequestScopeStore();
        $registry   = new StateResetRegistry();
        $registry->register(name: 'request-scopes', state: $scopeStore);
        $registry->register(name: 'runtime-context', state: $context);

        $requests = [
            ['uri' => '/api/users/1', 'user_id' => 1, 'role' => 'viewer'],
            ['uri' => '/api/users/2', 'user_id' => 2, 'role' => 'editor'],
            ['uri' => '/api/admin', 'user_id' => 3, 'role' => 'admin'],
            ['uri' => '/api/public', 'user_id' => null, 'role' => 'anonymous'],
        ];

        foreach ($requests as $requestData) {
            $request = new RuntimeRequest(method: 'GET', uri: $requestData['uri']);

            $open = new OpenHttpRequestScope(
                requestScopes : $scopeStore,
                runtimeContext: $context,
            );
            $open->open(request: $request);

            $scope = $scopeStore->current();

            // Write request-specific data
            $scope->write(key: 'current_user_id', value: $requestData['user_id']);
            $scope->write(key: 'current_role', value: $requestData['role']);
            $scope->write(key: 'request_uri', value: $requestData['uri']);

            // Verify we read back exactly what we just wrote
            self::assertSame($requestData['user_id'], $scope->read(key: 'current_user_id'),
                             "User ID mismatch for {$requestData['uri']}");
            self::assertSame($requestData['role'], $scope->read(key: 'current_role'),
                             "Role mismatch for {$requestData['uri']}");
            self::assertSame($requestData['uri'], $scope->read(key: 'request_uri'),
                             "URI mismatch for {$requestData['uri']}");

            // Close and reset (simulating worker loop behavior)
            $close = new CloseHttpRequestScope(requestScopes: $scopeStore);
            $close->close();
            $registry->resetAll();

            // Verify clean state
            self::assertFalse($scopeStore->hasCurrent(),
                              'Scope store should not have current scope after reset');
            self::assertNull($context->lastResult(),
                             'Context should not have last result after reset');
        }
    }

    #[Test]
    public function state_reset_registry_tracks_component_count() : void
    {
        $registry = new StateResetRegistry();

        // The framework registers at least these components
        $registry->register(name: 'request-scopes', state: new RequestScopeStore());
        $registry->register(name: 'runtime-context', state: new RuntimeContext());

        $report = $registry->resetAll();

        self::assertCount(2, $report->resetComponents(),
                          'Both registered components should be reset');
        self::assertEmpty($report->failures(),
                          'No failures should occur during reset');
    }
}
