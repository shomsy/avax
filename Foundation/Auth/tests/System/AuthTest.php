<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\System;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

/**
 * Smoke tests for the public Auth facade.
 */
final class AuthTest extends TestCase
{
    public function testAuthConfigurationReturnsBuilder() : void
    {
        $this->assertInstanceOf(AuthBuilder::class, Auth::configuration());
    }

    public function testAuthFacadeRunsCorePublicFlowSurface() : void
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $auth = Auth::configuration()
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec('auth-test-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore($refreshTokens)
            ->ready();

        $registration = $auth->register(new RegistrationData(
            email   : 'facade@example.com',
            username: 'facade',
            password: 'secret'
        ));
        $this->assertSame('facade@example.com', $registration->user()->email);

        $login = $auth->login(new Credentials(
            identifier: 'facade@example.com',
            password  : 'secret'
        ));

        $this->assertTrue($login->isAuthenticated());
        $this->assertNotNull($login->accessToken());
        $this->assertNotNull($login->refreshToken());
        $this->assertTrue($auth->check());
        $this->assertSame($login->user()?->id, $auth->current()->user()?->id);
        $this->assertSame($login->user()?->email, $auth->user()?->email);
        $this->assertInstanceOf(AccessInterface::class, $auth->access());

        $resolved = $auth->authenticateRequest(AuthenticationRequest::bearer($login->accessToken() ?? ''));
        $this->assertTrue($resolved->isAuthenticated());
        $this->assertSame('facade@example.com', $resolved->user()?->email);

        $auth->logout();

        $this->assertFalse($auth->check());
        $this->assertNull($auth->user());
    }
}
