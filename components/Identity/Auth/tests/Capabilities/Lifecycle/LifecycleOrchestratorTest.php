<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Capabilities\Lifecycle;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\InMemoryLifecycleStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleFailed;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleState;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class LifecycleOrchestratorTest extends TestCase
{
    public function testLifecycleTransitionsPersistAndMutateUserState() : void
    {
        $userSource = new InMemoryUserSource();
        $userSource->create(user: User::create(
            id          : new UserId(value: 42),
            email       : new UserEmail(value: 'lifecycle@example.com'),
            username    : 'lifecycle-user',
            passwordHash: 'hash',
            roles       : [UserRole::USER]
        ));
        $store        = new InMemoryLifecycleStore();
        $orchestrator = new LifecycleOrchestrator(
            userSource: $userSource,
            store     : $store,
            auditLog  : new InMemoryAuditLog(),
            clock     : new Clock()
        );

        $suspended = $orchestrator->suspend(
            userId: new UserId(value: 42),
            source: LifecycleSource::ADMIN,
            reason: 'manual_suspend'
        );

        $this->assertSame(expected: LifecycleState::SUSPENDED, actual: $suspended->state);
        $this->assertFalse(condition: $userSource->findById(id: new UserId(value: 42))?->isActive() ?? true);

        $reactivated = $orchestrator->activate(
            userId: new UserId(value: 42),
            source: LifecycleSource::ADMIN,
            reason: 'manual_reactivate'
        );

        $this->assertSame(expected: LifecycleState::ACTIVE, actual: $reactivated->state);
        $this->assertTrue(condition: $userSource->findById(id: new UserId(value: 42))?->isActive() ?? false);

        $deprovisioned = $orchestrator->deprovision(
            userId: new UserId(value: 42),
            source: LifecycleSource::SCIM,
            reason: 'directory_deleted'
        );

        $this->assertSame(expected: LifecycleState::DEPROVISIONED, actual: $deprovisioned->state);
        $this->assertSame(expected: [], actual: $userSource->findById(id: new UserId(value: 42))?->getRoles() ?? []);
        $this->assertFalse(condition: $orchestrator->allowsAuthentication(userId: new UserId(value: 42)));
        $this->assertSame(expected: LifecycleState::DEPROVISIONED, actual: $store->find(userId: new UserId(value: 42))?->state);
    }

    public function testDeprovisionedUserCannotTransitionBackToActive() : void
    {
        $userSource = new InMemoryUserSource();
        $userSource->create(user: User::create(
            id          : new UserId(value: 43),
            email       : new UserEmail(value: 'closed@example.com'),
            username    : 'closed-user',
            passwordHash: 'hash'
        ));
        $orchestrator = new LifecycleOrchestrator(
            userSource: $userSource,
            store     : new InMemoryLifecycleStore(),
            auditLog  : new InMemoryAuditLog(),
            clock     : new Clock()
        );

        $orchestrator->deprovision(
            userId: new UserId(value: 43),
            source: LifecycleSource::ADMIN,
            reason: 'hard_close'
        );

        $this->expectException(LifecycleFailed::class);
        $this->expectExceptionMessage('Lifecycle transition from [deprovisioned] to [active] is not allowed.');

        $orchestrator->activate(
            userId: new UserId(value: 43),
            source: LifecycleSource::ADMIN,
            reason: 'reopen'
        );
    }
}
