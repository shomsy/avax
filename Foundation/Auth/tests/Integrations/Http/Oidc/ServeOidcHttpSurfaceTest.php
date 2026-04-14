<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http\Oidc;

use Avax\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Auth\Integrations\Http\Oidc\ServeOidcHttpSurface;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\Oidc\OpenSslOidcProvider;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\OAuth\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Auth\System\Flow\Oidc\Logout\LogoutData as OidcLogoutData;
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
        $surface = new ServeOidcHttpSurface(auth: $auth);

        $auth->register(data: new RegistrationData(
            email   : 'oidc-http@example.com',
            username: 'oidc-http',
            password: 'secret'
        ));
        $auth->login(credentials: new Credentials(
            identifier: 'oidc-http@example.com',
            password  : 'secret'
        ));
        $client = $auth->registerOAuthClient(data: new RegisterClientData(
            name         : 'OIDC HTTP Client',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid', 'email', 'profile']
        ));
        $code = $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid', 'email', 'profile'],
            nonce      : 'nonce-http'
        ));
        $grant = $auth->exchangeOAuthCode(data: new ExchangeAuthorizationCodeData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            code        : $code->code,
            redirectUri : 'https://rp.example.test/callback'
        ));

        $discovery = $surface->execute(input: new HttpEndpointInput(
            method: 'GET',
            path  : '/.well-known/openid-configuration'
        ));
        $par = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/oauth/par',
            body  : [
                'client_id' => $client->client->clientId,
                'redirect_uri' => 'https://rp.example.test/callback',
                'scope' => 'openid email profile',
                'nonce' => 'nonce-par-http',
            ]
        ));
        $logout = $surface->execute(input: new HttpEndpointInput(
            method: 'GET',
            path  : '/oidc/logout',
            query : [
                'session_id' => $auth->current()->sessionId(),
                'state' => 'logout-state-http',
            ]
        ));
        $jwks = $surface->execute(input: new HttpEndpointInput(
            method: 'GET',
            path  : '/.well-known/jwks.json'
        ));
        $userInfo = $surface->execute(input: new HttpEndpointInput(
            method : 'GET',
            path   : '/oidc/userinfo',
            headers: ['Authorization' => 'Bearer ' . $grant->accessToken]
        ));

        $this->assertSame(expected: 200, actual: $discovery->statusCode);
        $this->assertSame(expected: 'https://auth.example.test', actual: $discovery->body['issuer']);
        $this->assertSame(expected: 'https://auth.example.test/oidc/logout', actual: $discovery->body['end_session_endpoint']);
        $this->assertSame(expected: 'https://auth.example.test/oauth/par', actual: $discovery->body['pushed_authorization_request_endpoint']);
        $this->assertTrue(condition: $discovery->body['frontchannel_logout_supported']);
        $this->assertSame(expected: 200, actual: $jwks->statusCode);
        $this->assertSame(expected: 201, actual: $par->statusCode);
        $this->assertSame(expected: 200, actual: $logout->statusCode);
        $this->assertTrue(condition: $logout->body['revoked']);
        $this->assertCount(expectedCount: 1, haystack: $jwks->body['keys']);
        $this->assertSame(expected: 200, actual: $userInfo->statusCode);
        $this->assertSame(expected: 'oidc-http@example.com', actual: $userInfo->body['email']);
    }

    public function testOidcHttpSurfaceRejectsUserInfoWithoutBearerToken() : void
    {
        [$auth] = $this->buildAuthWithOidc();
        $surface = new ServeOidcHttpSurface(auth: $auth);

        $response = $surface->execute(input: new HttpEndpointInput(
            method: 'GET',
            path  : '/oidc/userinfo'
        ));

        $this->assertSame(expected: 401, actual: $response->statusCode);
        $this->assertSame(expected: 'invalid_token', actual: $response->body['error']);
    }

    /**
     * @return array{Auth, OpenSslOidcProvider}
     */
    private function buildAuthWithOidc() : array
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $sessionRegistry = new InMemorySessionRegistry();
        $provider = $this->createOidcProvider();
        $auth = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(
                sessionIdentity: new SessionIdentity(sessionRegistry: $sessionRegistry),
                jwtIdentity    : new JwtIdentity(
                    userSource       : $userSource,
                    codec            : new HmacTokenCodec(secret: 'oidc-http-secret'),
                    clock            : new Clock(),
                    revocationStore  : new InMemoryTokenRevocationStore(),
                    refreshTokenStore: $refreshTokens
                )
            ))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->withOidcProvider(oidcProvider: $provider)
            ->ready();

        return [$auth, $provider];
    }

    private function createOidcProvider() : OpenSslOidcProvider
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        self::assertNotFalse(condition: $key);
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
