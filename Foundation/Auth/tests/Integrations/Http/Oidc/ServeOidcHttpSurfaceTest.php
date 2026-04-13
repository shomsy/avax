<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http\Oidc;

use Avax\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Auth\Integrations\Http\Oidc\ServeOidcHttpSurface;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\Oidc\OpenSslOidcProvider;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\OAuth\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class ServeOidcHttpSurfaceTest extends TestCase
{
    public function testOidcHttpSurfaceServesDiscoveryJwksAndUserInfo() : void
    {
        [$auth] = $this->buildAuthWithOidc();
        $surface = new ServeOidcHttpSurface($auth);

        $auth->register(new RegistrationData(
            email   : 'oidc-http@example.com',
            username: 'oidc-http',
            password: 'secret'
        ));
        $auth->login(new Credentials(
            identifier: 'oidc-http@example.com',
            password  : 'secret'
        ));
        $client = $auth->registerOAuthClient(new RegisterClientData(
            name         : 'OIDC HTTP Client',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid', 'email', 'profile']
        ));
        $code = $auth->authorizeOAuthCode(new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid', 'email', 'profile'],
            nonce      : 'nonce-http'
        ));
        $grant = $auth->exchangeOAuthCode(new ExchangeAuthorizationCodeData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            code        : $code->code,
            redirectUri : 'https://rp.example.test/callback'
        ));

        $discovery = $surface->execute(new HttpEndpointInput(
            method: 'GET',
            path  : '/.well-known/openid-configuration'
        ));
        $jwks = $surface->execute(new HttpEndpointInput(
            method: 'GET',
            path  : '/.well-known/jwks.json'
        ));
        $userInfo = $surface->execute(new HttpEndpointInput(
            method : 'GET',
            path   : '/oidc/userinfo',
            headers: ['Authorization' => 'Bearer ' . $grant->accessToken]
        ));

        $this->assertSame(200, $discovery->statusCode);
        $this->assertSame('https://auth.example.test', $discovery->body['issuer']);
        $this->assertSame(200, $jwks->statusCode);
        $this->assertCount(1, $jwks->body['keys']);
        $this->assertSame(200, $userInfo->statusCode);
        $this->assertSame('oidc-http@example.com', $userInfo->body['email']);
    }

    public function testOidcHttpSurfaceRejectsUserInfoWithoutBearerToken() : void
    {
        [$auth] = $this->buildAuthWithOidc();
        $surface = new ServeOidcHttpSurface($auth);

        $response = $surface->execute(new HttpEndpointInput(
            method: 'GET',
            path  : '/oidc/userinfo'
        ));

        $this->assertSame(401, $response->statusCode);
        $this->assertSame('invalid_token', $response->body['error']);
    }

    /**
     * @return array{Auth, OpenSslOidcProvider}
     */
    private function buildAuthWithOidc() : array
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $provider = $this->createOidcProvider();
        $auth = Auth::configuration()
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec('oidc-http-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore($refreshTokens)
            ->withOidcProvider($provider)
            ->ready();

        return [$auth, $provider];
    }

    private function createOidcProvider() : OpenSslOidcProvider
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        self::assertNotFalse($key);
        openssl_pkey_export($key, $privateKeyPem);

        return new OpenSslOidcProvider(
            issuer               : 'https://auth.example.test',
            privateKeyPem        : $privateKeyPem,
            keyId                : 'oidc-http-key',
            authorizationEndpoint: 'https://auth.example.test/oauth/authorize',
            tokenEndpoint        : 'https://auth.example.test/oauth/token',
            userInfoEndpoint     : 'https://auth.example.test/oidc/userinfo',
            jsonWebKeySetUri     : 'https://auth.example.test/.well-known/jwks.json'
        );
    }
}
