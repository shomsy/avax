<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Examples\SecureRegistrationApi;

use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Flows\CompileEventListeners\CompileEventListeners;
use Avax\Components\Operations\Events\System\Foundation\EventEmitter;
use Avax\Components\Operations\Events\System\Foundation\GlobalEventListenerState;
use Avax\Examples\SecureRegistrationApi\ProjectRegisteredUser;
use Avax\Examples\SecureRegistrationApi\ReadRegisteredUser;
use Avax\Examples\SecureRegistrationApi\RecordRegistrationAudit;
use Avax\Examples\SecureRegistrationApi\ReferenceEventHistoryStore;
use Avax\Examples\SecureRegistrationApi\RegisteredUserView;
use Avax\Examples\SecureRegistrationApi\RegistrationController;
use Avax\Examples\SecureRegistrationApi\ReplayEventHistory;
use Avax\Examples\SecureRegistrationApi\UserRegistered;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Avax\Components\Operations\Events\System\PublicSurface\emit;
use function Avax\Components\Operations\Events\System\PublicSurface\onEventSetRegistry;

/**
 * V5.7-09: Real Dogfooding Tests.
 *
 * Proves the canonical AvaX Events runtime works in a real reference flow.
 */
final class EventsDogfoodingTest extends TestCase
{
    protected function setUp(): void
    {
        RegistrationController::reset();
        $registry = new ListenerRegistry();
        onEventSetRegistry($registry);
        RegistrationController::wireEventListeners();

        // Compile and wire the global emitter.
        \Avax\Components\Operations\Events\System\Configuration\Builders\RegisterEventDependencies::compileAndWire($registry);
    }

    protected function tearDown(): void
    {
        RegistrationController::reset();
    }

    #[Test]
    public function registration_emits_user_registered_event(): void
    {
        $controller = new RegistrationController();
        $result = $controller->register([
            'email' => 'alice@example.com',
            'password' => 'secret-password-123',
        ]);

        // Response body contains the registration result.
        self::assertStringContainsString('registered', (string) $result->getBody());
    }

    #[Test]
    public function audit_listener_records_expected_audit(): void
    {
        $controller = new RegistrationController();
        $controller->register([
            'email' => 'bob@example.com',
            'password' => 'secure-password-456',
        ]);

        self::assertCount(1, RecordRegistrationAudit::$auditLog);
        $entry = RecordRegistrationAudit::$auditLog[0];

        self::assertSame('bob@example.com', $entry['email']);
        self::assertSame('user.registered', $entry['action']);
        self::assertNotEmpty($entry['userId']);
        self::assertNotEmpty($entry['timestamp']);
    }

    #[Test]
    public function projection_listener_builds_registered_user_view(): void
    {
        $controller = new RegistrationController();
        $controller->register([
            'email' => 'carol@example.com',
            'password' => 'strong-password-789',
        ]);

        $view = RegisteredUserView::all();
        self::assertCount(1, $view);

        $user = $view[0];
        self::assertSame('carol@example.com', $user->email);
        self::assertNotEmpty($user->userId);
        self::assertNotEmpty($user->registeredAt);
    }

    #[Test]
    public function read_registered_user_returns_projection(): void
    {
        $controller = new RegistrationController();
        $controller->register([
            'email' => 'dave@example.com',
            'password' => 'password-is-secure-000',
        ]);

        $reader = new ReadRegisteredUser();
        $all = $reader->all();
        self::assertCount(1, $all);

        $user = $reader->byUserId($all[0]->userId);
        self::assertNotNull($user);
        self::assertSame('dave@example.com', $user->email);
    }

    #[Test]
    public function without_listener_registration_projection_is_not_created(): void
    {
        // Reset to clean state without wiring.
        RegistrationController::reset();
        $registry = new ListenerRegistry();
        onEventSetRegistry($registry);

        // Do NOT wire listeners.
        \Avax\Components\Operations\Events\System\Configuration\Builders\RegisterEventDependencies::compileAndWire($registry);

        $controller = new RegistrationController();
        $controller->register([
            'email' => 'eve@example.com',
            'password' => 'password-for-eve-111',
        ]);

        // Projection was not built because listeners are not registered.
        self::assertCount(0, RegisteredUserView::all());
    }

    #[Test]
    public function event_history_stores_user_registered_event(): void
    {
        $controller = new RegistrationController();
        $controller->register([
            'email' => 'frank@example.com',
            'password' => 'frank-secure-password-222',
        ]);

        $events = ReferenceEventHistoryStore::all();
        self::assertCount(1, $events);
        self::assertInstanceOf(UserRegistered::class, $events[0]);
        self::assertSame('frank@example.com', $events[0]->email);
    }

    #[Test]
    public function event_history_replay_rebuilds_registered_user_view(): void
    {
        // Step 1: Register a user (stores event in history + builds projection).
        $controller = new RegistrationController();
        $controller->register([
            'email' => 'grace@example.com',
            'password' => 'grace-password-333',
        ]);

        // Step 2: Clear the view (simulate projection loss).
        RegisteredUserView::reset();
        self::assertCount(0, RegisteredUserView::all());

        // Step 3: Replay from event history.
        $replay = new ReplayEventHistory();
        $replay->replay(ReferenceEventHistoryStore::eventsOf(UserRegistered::class));

        // Step 4: View should be rebuilt.
        $view = RegisteredUserView::all();
        self::assertCount(1, $view);
        self::assertSame('grace@example.com', $view[0]->email);
    }

    #[Test]
    public function event_history_is_reference_proof_only(): void
    {
        // The event-history store is deliberately simple — no versioning,
        // no streams, no serialization, no snapshots.
        // This test documents it as reference/proof only.

        $store = new ReferenceEventHistoryStore();
        $event = new UserRegistered('test-1', 'test@example.com', '2026-01-01T00:00:00+00:00');
        $store::append($event);

        self::assertCount(1, ReferenceEventHistoryStore::all());
        self::assertSame($event, ReferenceEventHistoryStore::all()[0]);

        // No stream identity, no versioning — just a simple list.
        self::assertFalse(property_exists($event, 'streamId'));
        self::assertFalse(property_exists($event, 'version'));
        self::assertFalse(property_exists($event, 'streamVersion'));
    }

    #[Test]
    public function user_registered_is_plain_object_no_event_interface(): void
    {
        $event = new UserRegistered('user-1', 'test@example.com', '2026-01-01T00:00:00+00:00');

        // Plain readonly object — no interface.
        self::assertFalse(method_exists($event, 'getEventName'));
        self::assertFalse(method_exists($event, 'getPayload'));
        self::assertObjectNotHasProperty('eventId', $event);

        // But it works with emit().
        $controller = new RegistrationController();
        $controller->register([
            'email' => 'interface-test@example.com',
            'password' => 'interface-test-password-444',
        ]);

        self::assertCount(1, RecordRegistrationAudit::$auditLog);
    }

    #[Test]
    public function listeners_are_invokable_no_listener_interface(): void
    {
        // RecordRegistrationAudit and ProjectRegisteredUser are plain invokable classes.
        // No ListenerInterface required.

        $audit = new RecordRegistrationAudit();
        self::assertTrue(method_exists($audit, '__invoke'));

        $projector = new ProjectRegisteredUser();
        self::assertTrue(method_exists($projector, '__invoke'));
    }

    #[Test]
    public function multiple_registrations_create_multiple_events(): void
    {
        $controller = new RegistrationController();
        $controller->register([
            'email' => 'user1@example.com',
            'password' => 'password-user1-555',
        ]);
        $controller->register([
            'email' => 'user2@example.com',
            'password' => 'password-user2-666',
        ]);
        $controller->register([
            'email' => 'user3@example.com',
            'password' => 'password-user3-777',
        ]);

        self::assertCount(3, RecordRegistrationAudit::$auditLog);
        self::assertCount(3, RegisteredUserView::all());
        self::assertCount(3, ReferenceEventHistoryStore::all());
    }

    #[Test]
    public function emit_uses_canonical_avax_runtime(): void
    {
        // Verify that emit() uses the canonical Events runtime, not a fake.
        $emitter = GlobalEventListenerState::emitter();
        self::assertInstanceOf(EventEmitter::class, $emitter);

        $compiled = $emitter->getRegistry();
        self::assertTrue($compiled->isFrozen());
        self::assertTrue($compiled->hasListeners(UserRegistered::class));
    }
}
