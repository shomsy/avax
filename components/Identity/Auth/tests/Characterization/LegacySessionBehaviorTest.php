<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Characterization;

use Avax\Auth\Tests\Support\ArraySessionStore;
use Avax\Components\Identity\Auth\System\Auth;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\InMemorySessionRegistry;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationData;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class LegacySessionBehaviorTest extends TestCase
{
    /**
     * @throws AuthenticationFailed
     * @throws Unauthenticated
     * @throws RateLimitException
     * @throws RegistrationFailed
     */
    public function testLegacySessionReadAndRevocationBehaviorRemainsStable() : void
    {
        $sessionRegistry = new InMemorySessionRegistry();
        $auth            = Auth::configuration()
            ->forUser(userSource: new InMemoryUserSource())
            ->withIdentity(identity: new Identity(sessionIdentity: new SessionIdentity(
                                                                       store          : new ArraySessionStore(),
                                                                       sessionRegistry: $sessionRegistry
                                                                   )))
            ->withSessionRegistry(sessionRegistry: $sessionRegistry)
            ->ready();

        $auth->register(data: new RegistrationData(
                                  email   : 'legacy-session@example.com',
                                  username: 'legacy-session',
                                  password: 'secret'
                              ));
        $auth->login(credentials: new Credentials(
                                      identifier: 'legacy-session@example.com',
                                      password  : 'secret',
                                      ipAddress : '127.0.0.1',
                                      userAgent : 'PHPUnit'
                                  ));

        $sessions = $auth->readActiveSessions();

        $this->assertCount(expectedCount: 1, haystack: $sessions);
        $this->assertTrue(condition: $sessions[0]->current);

        $auth->revokeSession(sessionId: $sessions[0]->sessionId);

        $this->assertFalse(condition: $auth->check());
        $this->expectException(Unauthenticated::class);

        $auth->readActiveSessions();
    }
}
