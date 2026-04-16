<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Characterization;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Flow\Token\RefreshAuthenticationRequest;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class LegacyTokenRefreshBehaviorTest extends TestCase
{
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
