<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access\RequirePermission;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
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
        $currentAuthentication->store(AuthenticationContext::authenticated(
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
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode: AuthenticationMode::SESSION
        ));

        $requirement = new RequirePermission(currentAuthentication: $currentAuthentication);
        try {
            $requirement->execute(permission: $permission);
            self::fail('PermissionDenied was not raised.');
        } catch (PermissionDenied $exception) {
            $this->assertSame('Access denied.', $exception->getMessage());
            $this->assertSame('delete_user', $exception->requirement()->value);
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
