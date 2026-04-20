<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http\Oidc;

use Avax\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Auth\Integrations\Http\Oidc\ServeOidcHttpSurface;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientType;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OpenSslOidcProvider;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\InMemorySessionRegistry;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\HmacTokenCodec;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\InMemoryRefreshTokenStore;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\InMemoryTokenRevocationStore;
use Avax\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Foundation\Clock;
use JsonException;
use PHPUnit\Framework\TestCase;
use SensitiveParameter;

final class ServeOidcHttpSurfaceTest extends TestCase
{
    /**
     */
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
        $code   = $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
                                                      clientId   : $client->client->clientId,
                                                      redirectUri: 'https://rp.example.test/callback',
                                                      scopes     : ['openid', 'email', 'profile'],
                                                      nonce      : 'nonce-http'
                                                  ));
        $grant  = $auth->exchangeOAuthCode(data: new ExchangeAuthorizationCodeData(
                                                     clientId    : $client->client->clientId,
                                                     code        : $code->code,
                                                     redirectUri : 'https://rp.example.test/callback',
                                                     clientSecret: $client->plainTextSecret
                                                 ));

        $discovery = $surface->execute(input: new HttpEndpointInput(
                                                  method: 'GET',
                                                  path  : '/.well-known/openid-configuration'
                                              ));
        $par       = $surface->execute(input: new HttpEndpointInput(
                                                  method: 'POST',
                                                  path  : '/oauth/par',
                                                  body  : [
                                                              'client_id'    => $client->client->clientId,
                                                              'redirect_uri' => 'https://rp.example.test/callback',
                                                              'scope'        => 'openid email profile',
                                                              'nonce'        => 'nonce-par-http',
                                                          ]
                                              ));
        $logout    = $surface->execute(input: new HttpEndpointInput(
                                                  method: 'GET',
                                                  path  : '/oidc/logout',
                                                  query : [
                                                              'session_id' => $auth->current()->sessionId(),
                                                              'state'      => 'logout-state-http',
                                                          ]
                                              ));
        $jwks      = $surface->execute(input: new HttpEndpointInput(
                                                  method: 'GET',
                                                  path  : '/.well-known/jwks.json'
                                              ));
        $userInfo  = $surface->execute(input: new HttpEndpointInput(
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

    /**
     * @return array{Auth, OpenSslOidcProvider}
     */
    private function buildAuthWithOidc() : array
    {
        $userSource      = new InMemoryUserSource();
        $refreshTokens   = new InMemoryRefreshTokenStore();
        $sessionRegistry = new InMemorySessionRegistry();
        $provider        = $this->createOidcProvider();
        $auth            = Auth::configuration()
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
        [$privateKeyPem] = $this->rsaKeyPair();

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

    /**
     * @return array{0: string, 1: string}
     */
    private function rsaKeyPair() : array
    {
        $key = openssl_pkey_new([
                                    'private_key_bits' => 2048,
                                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                                ]);
        self::assertNotFalse(condition: $key);
        openssl_pkey_export($key, $privateKeyPem);
        $details = openssl_pkey_get_details($key);
        self::assertIsArray(actual: $details);

        return [$privateKeyPem, $details['key']];
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

    public function testOidcDiscoveryPublishesRegistrationEndpoint() : void
    {
        [$auth] = $this->buildAuthWithOidc();
        $surface = new ServeOidcHttpSurface(auth: $auth);

        $response = $surface->execute(input: new HttpEndpointInput(
                                                 method: 'GET',
                                                 path  : '/.well-known/openid-configuration'
                                             ));

        $this->assertSame(expected: 200, actual: $response->statusCode);
        $this->assertSame(expected: 'https://auth.example.test/oidc/register', actual: $response->body['registration_endpoint']);
    }

    /**
     */
    public function testOidcHttpSurfaceAcceptsSignedJarParRequest() : void
    {
        [$auth] = $this->buildAuthWithOidc();
        $surface = new ServeOidcHttpSurface(auth: $auth);

        $auth->register(data: new RegistrationData(
                                  email   : 'oidc-http-jar@example.com',
                                  username: 'oidc-http-jar',
                                  password: 'secret'
                              ));
        $auth->login(credentials: new Credentials(
                                      identifier: 'oidc-http-jar@example.com',
                                      password  : 'secret'
                                  ));
        $client     = $auth->registerOAuthClient(data: new RegisterClientData(
                                                           name                          : 'OIDC HTTP JAR Client',
                                                           type                          : OAuthClientType::CONFIDENTIAL,
                                                           redirectUris                  : ['https://rp.example.test/callback'],
                                                           allowedScopes                 : ['openid'],
                                                           requestObjectSignatureRequired: true
                                                       ));
        $requestJwt = (new HmacTokenCodec(secret: $client->plainTextSecret ?? ''))->encode(claims: [
                                                                                                       'client_id'    => $client->client->clientId,
                                                                                                       'redirect_uri' => 'https://rp.example.test/callback',
                                                                                                       'scope'        => 'openid',
                                                                                                       'nonce'        => 'nonce-http-jar',
                                                                                                       'exp'          => time() + 300,
                                                                                                   ]);

        $response = $surface->execute(input: new HttpEndpointInput(
                                                 method: 'POST',
                                                 path  : '/oauth/par',
                                                 body  : [
                                                             'client_id'     => $client->client->clientId,
                                                             'redirect_uri'  => 'https://rp.example.test/callback',
                                                             'request'       => $requestJwt,
                                                             'client_secret' => $client->plainTextSecret,
                                                         ]
                                             ));

        $this->assertSame(expected: 201, actual: $response->statusCode);
        $this->assertArrayHasKey(key: 'request_uri', array: $response->body);
    }

    public function testOidcHttpSurfaceRegistersPublicClient() : void
    {
        [$auth] = $this->buildAuthWithOidc();
        $surface = new ServeOidcHttpSurface(auth: $auth);

        $response = $surface->execute(input: new HttpEndpointInput(
                                                 method: 'POST',
                                                 path  : '/oidc/register',
                                                 body  : [
                                                             'client_name'                => 'OIDC Dynamic SPA',
                                                             'application_type'           => 'web',
                                                             'token_endpoint_auth_method' => 'none',
                                                             'redirect_uris'              => ['https://spa.example.test/callback'],
                                                             'grant_types'                => ['authorization_code'],
                                                             'scope'                      => 'openid profile',
                                                         ]
                                             ));

        $this->assertSame(expected: 201, actual: $response->statusCode);
        $this->assertSame(expected: 'OIDC Dynamic SPA', actual: $response->body['client_name']);
        $this->assertSame(expected: 'none', actual: $response->body['token_endpoint_auth_method']);
        $this->assertSame(expected: ['https://spa.example.test/callback'], actual: $response->body['redirect_uris']);
    }

    public function testOidcHttpSurfaceUpdatesRegisteredClient() : void
    {
        [$auth] = $this->buildAuthWithOidc();
        $surface = new ServeOidcHttpSurface(auth: $auth);

        $created = $surface->execute(input: new HttpEndpointInput(
                                                method: 'POST',
                                                path  : '/oidc/register',
                                                body  : [
                                                            'client_name'                => 'OIDC Dynamic SPA',
                                                            'application_type'           => 'web',
                                                            'token_endpoint_auth_method' => 'none',
                                                            'redirect_uris'              => ['https://spa.example.test/callback'],
                                                            'grant_types'                => ['authorization_code'],
                                                            'scope'                      => 'openid profile',
                                                        ]
                                            ));

        $updated = $surface->execute(input: new HttpEndpointInput(
                                                method: 'PUT',
                                                path  : '/oidc/register/' . rawurlencode($created->body['client_id']),
                                                body  : [
                                                            'client_name'                => 'OIDC Dynamic SPA 2',
                                                            'token_endpoint_auth_method' => 'none',
                                                            'redirect_uris'              => ['https://spa.example.test/callback', 'https://spa.example.test/return'],
                                                            'grant_types'                => ['authorization_code'],
                                                            'scope'                      => 'openid profile email',
                                                        ]
                                            ));

        $this->assertSame(expected: 200, actual: $updated->statusCode);
        $this->assertSame(expected: 'OIDC Dynamic SPA 2', actual: $updated->body['client_name']);
        $this->assertSame(expected: ['https://spa.example.test/callback', 'https://spa.example.test/return'], actual: $updated->body['redirect_uris']);
        $this->assertSame(expected: 'openid profile email', actual: $updated->body['scope']);
    }

    public function testOidcHttpSurfaceUpdatePreservesExistingTokenAuthMethodWhenOmitted() : void
    {
        [$auth] = $this->buildAuthWithOidc();
        $surface = new ServeOidcHttpSurface(auth: $auth);

        $created = $surface->execute(input: new HttpEndpointInput(
                                                method: 'POST',
                                                path  : '/oidc/register',
                                                body  : [
                                                            'client_name'                => 'OIDC Dynamic SPA',
                                                            'token_endpoint_auth_method' => 'none',
                                                            'redirect_uris'              => ['https://spa.example.test/callback'],
                                                            'grant_types'                => ['authorization_code'],
                                                            'scope'                      => 'openid',
                                                        ]
                                            ));

        $updated = $surface->execute(input: new HttpEndpointInput(
                                                method: 'PUT',
                                                path  : '/oidc/register/' . rawurlencode($created->body['client_id']),
                                                body  : [
                                                            'client_name'   => 'OIDC Dynamic SPA',
                                                            'redirect_uris' => ['https://spa.example.test/callback'],
                                                            'grant_types'   => ['authorization_code'],
                                                            'scope'         => 'openid profile',
                                                        ]
                                            ));

        $this->assertSame(expected: 200, actual: $updated->statusCode);
        $this->assertSame(expected: 'none', actual: $updated->body['token_endpoint_auth_method']);
    }

    public function testOidcHttpSurfaceDisablesRegisteredClient() : void
    {
        [$auth] = $this->buildAuthWithOidc();
        $surface = new ServeOidcHttpSurface(auth: $auth);

        $created = $surface->execute(input: new HttpEndpointInput(
                                                method: 'POST',
                                                path  : '/oidc/register',
                                                body  : [
                                                            'client_name'                => 'OIDC Dynamic SPA',
                                                            'application_type'           => 'web',
                                                            'token_endpoint_auth_method' => 'none',
                                                            'redirect_uris'              => ['https://spa.example.test/callback'],
                                                            'grant_types'                => ['authorization_code'],
                                                            'scope'                      => 'openid',
                                                        ]
                                            ));

        $deleted = $surface->execute(input: new HttpEndpointInput(
                                                method: 'DELETE',
                                                path  : '/oidc/register/' . rawurlencode($created->body['client_id'])
                                            ));

        $this->assertSame(expected: 200, actual: $deleted->statusCode);
        $this->assertSame(expected: $created->body['client_id'], actual: $deleted->body['client_id']);
        $this->assertFalse(condition: $deleted->body['active']);
    }

    /**
     * @throws JsonException
     */
    public function testOidcHttpSurfaceRegistersRequestObjectVerificationKeyForSignedPar() : void
    {
        [$auth] = $this->buildAuthWithOidc();
        $surface = new ServeOidcHttpSurface(auth: $auth);
        [$privateKeyPem, $publicKeyPem] = $this->rsaKeyPair();

        $created = $surface->execute(input: new HttpEndpointInput(
                                                method: 'POST',
                                                path  : '/oidc/register',
                                                body  : [
                                                            'client_name'                         => 'OIDC JAR SPA',
                                                            'token_endpoint_auth_method'          => 'none',
                                                            'redirect_uris'                       => ['https://spa.example.test/callback'],
                                                            'grant_types'                         => ['authorization_code'],
                                                            'scope'                               => 'openid profile',
                                                            'request_object_signature_required'   => true,
                                                            'request_object_verification_key_pem' => $publicKeyPem,
                                                        ]
                                            ));

        $par = $surface->execute(input: new HttpEndpointInput(
                                            method: 'POST',
                                            path  : '/oauth/par',
                                            body  : [
                                                        'client_id'    => $created->body['client_id'],
                                                        'redirect_uri' => 'https://spa.example.test/callback',
                                                        'request'      => $this->signRs256Jwt(
                                                            claims       : [
                                                                               'iss'          => $created->body['client_id'],
                                                                               'aud'          => 'https://auth.example.test',
                                                                               'client_id'    => $created->body['client_id'],
                                                                               'redirect_uri' => 'https://spa.example.test/callback',
                                                                               'scope'        => 'openid profile',
                                                                               'nonce'        => 'nonce-http-rsa',
                                                                               'exp'          => time() + 300,
                                                                           ],
                                                            privateKeyPem: $privateKeyPem
                                                        ),
                                                    ]
                                        ));

        $this->assertSame(expected: 201, actual: $created->statusCode);
        $this->assertSame(expected: 201, actual: $par->statusCode);
        $this->assertArrayHasKey(key: 'request_uri', array: $par->body);
    }

    /**
     * @param array<string, mixed> $claims
     * @param string               $privateKeyPem
     *
     * @return string
     * @throws JsonException
     */
    private function signRs256Jwt(array $claims, #[SensitiveParameter] string $privateKeyPem) : string
    {
        $header    = $this->base64UrlEncode(value: json_encode(['typ' => 'JWT', 'alg' => 'RS256'], JSON_THROW_ON_ERROR));
        $payload   = $this->base64UrlEncode(value: json_encode($claims, JSON_THROW_ON_ERROR));
        $input     = $header . '.' . $payload;
        $signature = '';

        $signed = openssl_sign($input, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
        self::assertTrue(condition: $signed);

        return $input . '.' . $this->base64UrlEncode(value: $signature);
    }

    private function base64UrlEncode(string $value) : string
    {
        return $value
                |> base64_encode(...)
                |> (static fn ($x) => strtr($x, '+/', '-_'))
                |> (static fn ($x) => rtrim($x, '='));
    }
}
