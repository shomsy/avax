<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Access\RequireAuthentication;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RequireAuthentication access boundary.
 */
class RequireAuthenticationTest extends TestCase
{
    /**
     * @throws Unauthenticated
     */
    public function testRequireAuthenticationSuccess() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode: AuthenticationMode::SESSION
        ));

        $requirement = new RequireAuthentication(currentAuthentication: $currentAuthentication);
        $requirement->execute();

        $this->assertTrue(condition: true); // No exception thrown
    }

    public function testRequireAuthenticationFailure() : void
    {
        $requirement = new RequireAuthentication(currentAuthentication: new CurrentAuthentication());

        $this->expectException(exception: Unauthenticated::class);

        $requirement->execute();
    }
}
