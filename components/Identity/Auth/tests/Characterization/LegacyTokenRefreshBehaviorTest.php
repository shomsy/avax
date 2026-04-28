<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Characterization;

use Avax\Components\Identity\Auth\System\Auth;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationRequest;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationData;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use DateMalformedStringException;
use PHPUnit\Framework\TestCase;

final class LegacyTokenRefreshBehaviorTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
     * @throws AuthenticationFailed
     * @throws RateLimitException
     * @throws RegistrationFailed
     */
    public function testLegacyRefreshRotationBehaviorRemainsStable() : void
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $auth          = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                                                                   userSource       : $userSource,
                                                                   codec            : new HmacTokenCodec(secret: 'legacy-refresh-secret'),
                                                                   clock            : new Clock(),
                                                                   revocationStore  : new InMemoryTokenRevocationStore(),
                                                                   refreshTokenStore: $refreshTokens
                                                               )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->ready();

        $auth->register(data: new RegistrationData(
                                  email   : 'legacy-refresh@example.com',
                                  username: 'legacy-refresh',
                                  password: 'secret'
                              ));
        $login = $auth->login(credentials: new Credentials(
                                               identifier: 'legacy-refresh@example.com',
                                               password  : 'secret'
                                           ));

        $refreshed = $auth->refresh(request: new RefreshAuthenticationRequest(
                                                 refreshToken: $login->refreshToken()
                                             ));

        $this->assertTrue(condition: $refreshed->isAuthenticated());
        $this->assertNotSame(expected: $login->refreshToken(), actual: $refreshed->refreshToken());
    }
}
