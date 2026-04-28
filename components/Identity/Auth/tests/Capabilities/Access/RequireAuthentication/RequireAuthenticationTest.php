<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Capabilities\Access\RequireAuthentication;

use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Tests\TestCase;
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

    /**
     * @throws Unauthenticated
     */
    public function testRequireAuthenticationFailure() : void
    {
        $requirement = new RequireAuthentication(currentAuthentication: new CurrentAuthentication());

        $this->expectException(exception: Unauthenticated::class);

        $requirement->execute();
    }
}
