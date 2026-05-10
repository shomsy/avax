<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability\System\Capabilities\Audit;

use Avax\Components\Operations\Observability\System\Capabilities\Audit\AuditEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AuditEventTest extends TestCase
{
    #[Test]
    public function it_constructs_with_required_fields() : void
    {
        $event = new AuditEvent(
            actor : 'admin@example.com',
            action: 'user.created',
            target: 'user:123',
        );

        self::assertSame('admin@example.com', $event->actor);
        self::assertSame('user.created', $event->action);
        self::assertSame('user:123', $event->target);
        self::assertSame([], $event->metadata);
        self::assertNull($event->timestamp);
    }

    #[Test]
    public function it_constructs_with_metadata() : void
    {
        $event = new AuditEvent(
            actor   : 'system',
            action  : 'config.changed',
            target  : 'settings',
            metadata: ['old_value' => 'a', 'new_value' => 'b'],
        );

        self::assertSame(['old_value' => 'a', 'new_value' => 'b'], $event->metadata);
    }

    #[Test]
    public function it_constructs_with_timestamp() : void
    {
        $event = new AuditEvent(
            actor    : 'admin',
            action   : 'login',
            target   : 'dashboard',
            metadata : [],
            timestamp: '2024-01-01T10:00:00Z',
        );

        self::assertSame('2024-01-01T10:00:00Z', $event->timestamp);
    }

    #[Test]
    public function it_constructs_with_all_fields() : void
    {
        $event = new AuditEvent(
            actor    : 'user@example.com',
            action   : 'document.deleted',
            target   : 'doc:456',
            metadata : ['reason' => 'expired', 'confirmed' => true],
            timestamp: '2024-06-15T14:30:00Z',
        );

        self::assertSame('user@example.com', $event->actor);
        self::assertSame('document.deleted', $event->action);
        self::assertSame('doc:456', $event->target);
        self::assertSame(['reason' => 'expired', 'confirmed' => true], $event->metadata);
        self::assertSame('2024-06-15T14:30:00Z', $event->timestamp);
    }

    #[Test]
    public function it_converts_to_array() : void
    {
        $event = new AuditEvent(
            actor   : 'admin',
            action  : 'role.assigned',
            target  : 'user:789',
            metadata: ['role' => 'editor'],
        );

        $array = $event->toArray();

        self::assertSame('admin', $array['actor']);
        self::assertSame('role.assigned', $array['action']);
        self::assertSame('user:789', $array['target']);
        self::assertSame(['role' => 'editor'], $array['metadata']);
    }

    #[Test]
    public function it_generates_timestamp_in_to_array_when_null() : void
    {
        $event = new AuditEvent(
            actor : 'system',
            action: 'backup.completed',
            target: 'database',
        );

        $array = $event->toArray();

        self::assertIsString($array['timestamp']);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', $array['timestamp']);
    }

    #[Test]
    public function it_preserves_explicit_timestamp_in_to_array() : void
    {
        $event = new AuditEvent(
            actor    : 'admin',
            action   : 'audit.export',
            target   : 'logs',
            metadata : [],
            timestamp: '2024-03-01T00:00:00Z',
        );

        $array = $event->toArray();

        self::assertSame('2024-03-01T00:00:00Z', $array['timestamp']);
    }

    #[Test]
    public function it_handles_empty_metadata() : void
    {
        $event = new AuditEvent(
            actor : 'system',
            action: 'heartbeat',
            target: 'monitor',
        );

        $array = $event->toArray();

        self::assertSame([], $array['metadata']);
    }

    #[Test]
    public function it_handles_complex_metadata() : void
    {
        $metadata = [
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'changes'    => [
                'field' => 'email',
                'old'   => 'old@test.com',
                'new'   => 'new@test.com',
            ],
        ];

        $event = new AuditEvent(
            actor   : 'admin',
            action  : 'user.updated',
            target  : 'user:1',
            metadata: $metadata,
        );

        self::assertSame($metadata, $event->metadata);
    }

    #[Test]
    public function it_has_readonly_properties() : void
    {
        $reflection = new ReflectionClass(AuditEvent::class);

        foreach (['actor', 'action', 'target', 'metadata', 'timestamp'] as $propName) {
            $prop = $reflection->getProperty($propName);
            self::assertTrue($prop->isReadOnly(), "Property {$propName} should be readonly");
        }
    }

    #[Test]
    public function it_handles_actor_as_service_name() : void
    {
        $event = new AuditEvent(
            actor : 'cron-service',
            action: 'cleanup.executed',
            target: 'temp-files',
        );

        self::assertSame('cron-service', $event->actor);
    }

    #[Test]
    public function it_handles_action_with_namespace() : void
    {
        $event = new AuditEvent(
            actor : 'admin',
            action: 'billing.subscription.upgraded',
            target: 'subscription:premium',
        );

        self::assertSame('billing.subscription.upgraded', $event->action);
    }
}
