<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access\RequireRole;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RequireRole access boundary.
 */
class RequireRoleTest extends TestCase
{
    /**
     * @throws \Avax\Auth\System\Capability\Access\RequireRole\RoleDenied
     * @throws Unauthenticated
     */
    public function testRequireRoleSuccess() : void
    {
        $role                  = UserRole::ADMIN;
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id      : 1,
                      email   : 'admin@example.com',
                      username: 'admin',
                      roles   : [UserRole::ADMIN->value]
                  ),
            mode: AuthenticationMode::SESSION
        ));

        $requirement = new RequireRole(currentAuthentication: $currentAuthentication);
        $requirement->execute(requiredRole: $role);

        $this->assertTrue(condition: true); // No exception thrown
    }

    /**
     * @throws Unauthenticated
     */
    public function testRequireRoleFailure() : void
    {
        $role                  = UserRole::ADMIN;
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id      : 1,
                      email   : 'user@example.com',
                      username: 'user',
                      roles   : [UserRole::USER->value]
                  ),
            mode: AuthenticationMode::SESSION
        ));

        $requirement = new RequireRole(currentAuthentication: $currentAuthentication);
        try {
            $requirement->execute(requiredRole: $role);
            self::fail(message: 'RoleDenied was not raised.');
        } catch (RoleDenied $exception) {
            $this->assertSame(expected: 'Access denied.', actual: $exception->getMessage());
            $this->assertSame(expected: UserRole::ADMIN, actual: $exception->requirement());
        }
    }

    /**
     * @throws RoleDenied
     */
    public function testRequireRoleFailureUserNotLoggedIn() : void
    {
        $role        = UserRole::ADMIN;
        $requirement = new RequireRole(currentAuthentication: new CurrentAuthentication());

        $this->expectException(exception: Unauthenticated::class);

        $requirement->execute(requiredRole: $role);
    }
}
