<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access;

use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
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
 * Unit test for Access capability boundaries.
 */
class AccessBoundaryTest extends TestCase
{
    /**
     * @throws \Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated
     */
    public function testRequireAuthenticationSuccess() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'a@example.com', username: 'a'),
            mode: AuthenticationMode::SESSION
        ));

        $boundary = new RequireAuthentication(currentAuthentication: $currentAuthentication);
        $boundary->execute();
        $this->assertTrue(condition: true); // No exception thrown
    }

    public function testRequireAuthenticationFailure() : void
    {
        $boundary = new RequireAuthentication(currentAuthentication: new CurrentAuthentication());

        $this->expectException(exception: Unauthenticated::class);
        $boundary->execute();
    }

    /**
     * @throws \Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied
     * @throws Unauthenticated
     */
    public function testRequirePermissionSuccess() : void
    {
        $permission            = new UserPermission(value: 'write');
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id         : 1,
                      email      : 'write@example.com',
                      username   : 'writer',
                      permissions: ['write']
                  ),
            mode: AuthenticationMode::SESSION
        ));

        $boundary = new RequirePermission(currentAuthentication: $currentAuthentication);
        $boundary->execute(permission: $permission);
        $this->assertTrue(condition: true);
    }

    /**
     * @throws \Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied
     */
    public function testRequirePermissionFailureUserNotLoggedIn() : void
    {
        $boundary = new RequirePermission(currentAuthentication: new CurrentAuthentication());

        $this->expectException(exception: Unauthenticated::class);
        $boundary->execute(permission: new UserPermission(value: 'any'));
    }

    /**
     * @throws Unauthenticated
     */
    public function testRequirePermissionFailurePermissionMissing() : void
    {
        $permission            = new UserPermission(value: 'write');
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'read@example.com', username: 'reader'),
            mode: AuthenticationMode::SESSION
        ));

        $boundary = new RequirePermission(currentAuthentication: $currentAuthentication);

        $this->expectException(exception: PermissionDenied::class);
        $boundary->execute(permission: $permission);
    }
}
