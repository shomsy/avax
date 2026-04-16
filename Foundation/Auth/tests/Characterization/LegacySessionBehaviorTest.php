<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Characterization;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\Tests\Support\ArraySessionStore;
use PHPUnit\Framework\TestCase;

final class LegacySessionBehaviorTest extends TestCase
{
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
