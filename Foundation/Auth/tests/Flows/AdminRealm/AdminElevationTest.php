<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\AdminRealm;

use Avax\Auth\System\Capability\AdminRealm\InMemoryAdminElevationStore;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AdminRealm\AdminElevationFailed;
use Avax\Auth\System\Flow\AdminRealm\BeginAdminElevation\BeginAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\EndAdminElevation\EndAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class AdminElevationTest extends TestCase
{
    public function testAdminElevationLifecycle() : void
    {
        $clock = new Clock();
        $current = new CurrentAuthentication();
        $current->store(AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                id        : 1,
                email     : 'admin@example.com',
                username  : 'admin',
                roles     : [UserRole::ADMIN->value],
                mfaEnabled: true
            ),
            mode         : AuthenticationMode::SESSION,
            sessionId    : 'session-1',
            mfaVerifiedAt: $clock->now()
        ));

        $store = new InMemoryAdminElevationStore();
        $begin = new BeginAdminElevation(
            currentAuthentication: $current,
            requireFreshMfa      : new RequireFreshMfa($current, $clock),
            elevationStore       : $store,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock
        );
        $guard = new RequireAdminElevation($current, $store, $clock);
        $end   = new EndAdminElevation($current, $store, new InMemoryAuditLog(), $clock);

        $elevation = $begin->execute();
        $this->assertSame('session-1', $elevation->bindingId);

        $guard->execute();
        $end->execute();

        $this->expectException(AdminElevationFailed::class);
        $guard->execute();
    }
}
