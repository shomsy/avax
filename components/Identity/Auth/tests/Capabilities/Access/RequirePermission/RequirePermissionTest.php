<?php

declare(strict_types=1);

namespace Avax\Components\Auth\Tests\Capabilities\Access\RequirePermission;

use Avax\Components\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Components\Auth\System\Capabilities\Access\RequirePermission\RequirePermission;
use Avax\Components\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Tests\TestCase;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RequirePermission access boundary.
 */
class RequirePermissionTest extends TestCase
{
    /**
     * @throws Unauthenticated
     * @throws PermissionDenied
     */
    public function testRequirePermissionSuccess() : void
    {
        $permission            = new UserPermission(value: 'delete_user');
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id         : 1,
                      email      : 'delete@example.com',
                      username   : 'deleter',
                      permissions: ['delete_user']
                  ),
            mode: AuthenticationMode::SESSION
        ));

        $requirement = new RequirePermission(currentAuthentication: $currentAuthentication);
        $requirement->execute(permission: $permission);

        $this->assertTrue(condition: true); // No exception thrown
    }

    /**
     * @throws Unauthenticated
     */
    public function testRequirePermissionFailure() : void
    {
        $permission            = new UserPermission(value: 'delete_user');
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode: AuthenticationMode::SESSION
        ));

        $requirement = new RequirePermission(currentAuthentication: $currentAuthentication);
        try {
            $requirement->execute(permission: $permission);
            self::fail(message: 'PermissionDenied was not raised.');
        } catch (PermissionDenied $exception) {
            $this->assertSame(expected: 'Access denied.', actual: $exception->getMessage());
            $this->assertSame(expected: 'delete_user', actual: $exception->requirement()->value);
        }
    }

    /**
     * @throws PermissionDenied
     */
    public function testRequirePermissionFailureUserNotLoggedIn() : void
    {
        $permission  = new UserPermission(value: 'delete_user');
        $requirement = new RequirePermission(currentAuthentication: new CurrentAuthentication());

        $this->expectException(exception: Unauthenticated::class);

        $requirement->execute(permission: $permission);
    }
}
