<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Provisioning;

use Avax\Auth\System\Capability\AdminRealm\AdminElevationRecord;
use Avax\Auth\System\Capability\AdminRealm\InMemoryAdminElevationStore;
use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Provisioning\DeprovisionUser\DeprovisionUser;
use Avax\Auth\System\Flow\Provisioning\ReactivateUser\ReactivateUser;
use Avax\Auth\System\Flow\Provisioning\SuspendUser\SuspendUser;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class ProvisioningTest extends TestCase
{
    public function testSuspendReactivateAndDeprovisionLifecycle() : void
    {
        $clock         = new Clock();
        $userSource    = new InMemoryUserSource();
        $targetUser    = User::create(
            id          : new UserId(2),
            email       : new UserEmail('user@example.com'),
            username    : 'user',
            passwordHash: 'hash',
            roles       : [UserRole::USER]
        );
        $userSource->create($targetUser);

        $current = new CurrentAuthentication();
        $current->store(AuthenticationContext::authenticated(
            user      : new AuthenticatedUser(
                id       : 1,
                email    : 'admin@example.com',
                username : 'admin',
                roles    : [UserRole::ADMIN->value]
            ),
            mode      : AuthenticationMode::SESSION,
            sessionId : 'session-admin'
        ));
        $elevations = new InMemoryAdminElevationStore();
        $elevations->start(new AdminElevationRecord(1, 'session-admin', $clock->now()->modify('+15 minutes')));

        $guard      = new RequireAdminElevation($current, $elevations, $clock);
        $suspend    = new SuspendUser(
            userSource         : $userSource,
            requireAdminElevation: $guard,
            auditLog           : new InMemoryAuditLog(),
            clock              : $clock,
            sessionRegistry    : new InMemorySessionRegistry(),
            refreshTokenStore  : new InMemoryRefreshTokenStore(),
            adminElevationStore: $elevations
        );
        $reactivate = new ReactivateUser($userSource, $guard, new InMemoryAuditLog(), $clock);
        $deprovision = new DeprovisionUser(
            userSource         : $userSource,
            requireAdminElevation: $guard,
            auditLog           : new InMemoryAuditLog(),
            clock              : $clock,
            sessionRegistry    : new InMemorySessionRegistry(),
            refreshTokenStore  : new InMemoryRefreshTokenStore(),
            adminElevationStore: $elevations
        );

        $suspend->execute(2);
        $this->assertFalse($userSource->findById(new UserId(2))?->isActive() ?? true);

        $reactivate->execute(2);
        $this->assertTrue($userSource->findById(new UserId(2))?->isActive() ?? false);

        $deprovision->execute(2);
        $deprovisioned = $userSource->findById(new UserId(2));
        $this->assertFalse($deprovisioned?->isActive() ?? true);
        $this->assertSame([], $deprovisioned?->getRoles() ?? []);
    }
}
