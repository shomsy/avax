<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access;

use Avax\Auth\System\Capability\Access\Access;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the Access façade.
 */
class AccessTest extends TestCase
{
    public function testAccessFacadeDelegatesToBoundaries() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id         : 42,
                      email      : 'access@example.com',
                      username   : 'access',
                      roles      : [UserRole::ADMIN->value],
                      permissions: ['write']
                  ),
            mode: AuthenticationMode::SESSION
        ));

        $access = new Access(
            requireAuthentication: new RequireAuthentication(currentAuthentication: $currentAuthentication),
            requireRole          : new RequireRole(currentAuthentication: $currentAuthentication),
            requirePermission    : new RequirePermission(currentAuthentication: $currentAuthentication)
        );

        $access->requireAuthentication();
        $access->requireRole(requiredRole: UserRole::USER);
        $access->requirePermission(permission: new UserPermission(value: 'write'));

        $this->assertTrue(condition: true);
    }
}
