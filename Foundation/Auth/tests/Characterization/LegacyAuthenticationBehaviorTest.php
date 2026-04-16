<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Characterization;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class LegacyAuthenticationBehaviorTest extends TestCase
{
    public function testLegacyAuthenticationLifecycleRemainsStable() : void
    {
        $auth = $this->buildAuth();

        $auth->register(data: new RegistrationData(
            email   : 'legacy-auth@example.com',
            username: 'legacy-auth',
            password: 'secret'
        ));

        $login = $auth->login(credentials: new Credentials(
            identifier: 'legacy-auth@example.com',
            password  : 'secret'
        ));

        $this->assertTrue(condition: $login->isAuthenticated());
        $this->assertTrue(condition: $auth->check());
        $this->assertSame(expected: 'legacy-auth@example.com', actual: $auth->user()?->email);

        $resolved = $auth->authenticateRequest(request: AuthenticationRequest::bearer(
            bearerToken: $login->accessToken() ?? ''
        ));

        $this->assertTrue(condition: $resolved->isAuthenticated());
        $this->assertSame(expected: $login->user()?->id, actual: $resolved->user()?->id);

        $auth->logout();

        $this->assertFalse(condition: $auth->check());
        $this->assertNull(actual: $auth->user());
    }

    private function buildAuth() : Auth
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();

        return Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec(secret: 'legacy-auth-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->ready();
    }
}
