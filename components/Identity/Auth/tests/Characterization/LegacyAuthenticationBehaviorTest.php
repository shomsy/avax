<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Characterization;

use Avax\Components\Identity\Auth\System\Auth;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationData;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class LegacyAuthenticationBehaviorTest extends TestCase
{
    /**
     * @throws AuthenticationFailed
     * @throws RegistrationFailed
     * @throws RateLimitException
     */
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
