<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Capabilities\Access\RequireRole;

use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireRole\RequireRole;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RequireRole access boundary.
 */
class RequireRoleTest extends TestCase
{
    /**
     * @throws RoleDenied
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
