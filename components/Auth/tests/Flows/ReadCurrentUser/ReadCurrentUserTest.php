<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\ReadCurrentUser;

use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\ReadCurrentUser\ReadCurrentUser;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ReadCurrentUser flow.
 */
class ReadCurrentUserTest extends TestCase
{
    public function testReadCurrentUserReturnsStoredUser() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $user                  = new AuthenticatedUser(id: 123, email: 'user@example.com', username: 'user');
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: $user,
            mode: AuthenticationMode::SESSION
        ));

        $readCurrentUser = new ReadCurrentUser(currentAuthentication: $currentAuthentication);

        $result = $readCurrentUser->execute();

        $this->assertSame(expected: $user, actual: $result);
    }

    public function testReadCurrentUserReturnsNullForGuestContext() : void
    {
        $readCurrentUser = new ReadCurrentUser(currentAuthentication: new CurrentAuthentication());

        $result = $readCurrentUser->execute();

        $this->assertNull(actual: $result);
    }
}
