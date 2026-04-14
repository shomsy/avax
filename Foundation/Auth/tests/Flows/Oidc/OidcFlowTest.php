<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Oidc;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\Oidc\OpenSslOidcProvider;
use Avax\Auth\System\Capability\Oidc\RotatingOidcProvider;
use Avax\Auth\System\Capability\Oidc\SubjectIdentifierStrategy;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\OAuth\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Flow\OAuth\OAuthAuthorizationFailed;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Flow\Oidc\JarmResponse\BuildJarmResponseData;
use Avax\Auth\System\Flow\Oidc\Logout\LogoutData as OidcLogoutData;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class OidcFlowTest extends TestCase
{
    public function testOidcDiscoveryJwksIdTokenAndUserInfoFlow() : void
    {
        [$auth, $provider] = $this->buildAuthWithOidc();

        $auth->register(data: new RegistrationData(
            email   : 'oidc@example.com',
            username: 'oidc-user',
            password: 'secret'
        ));
        $auth->login(credentials: new Credentials(
            identifier: 'oidc@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
            name         : 'OIDC Web',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid', 'profile', 'email']
        ));

        $code = $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid', 'profile', 'email'],
            nonce      : 'nonce-1'
        ));
        $grant = $auth->exchangeOAuthCode(data: new ExchangeAuthorizationCodeData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            code        : $code->code,
            redirectUri : 'https://rp.example.test/callback'
        ));

        $metadata = $auth->readOidcProviderMetadata();
        $jsonWebKeySet = $auth->readOidcJsonWebKeySet();
        $userInfo = $auth->readOidcUserInfo(accessToken: $grant->accessToken);
        $claims = $provider->resolveIdToken(idToken: $grant->idToken ?? '');

        $this->assertSame(expected: 'https://auth.example.test', actual: $metadata->issuer);
        $this->assertSame(expected: 'https://auth.example.test/.well-known/jwks.json', actual: $metadata->jsonWebKeySetUri);
        $this->assertCount(expectedCount: 1, haystack: $jsonWebKeySet->keys);
        $this->assertNotNull(actual: $grant->idToken);
        $this->assertSame(expected: 'nonce-1', actual: $claims['nonce'] ?? null);
        $this->assertSame(expected: $client->client->clientId, actual: $claims['aud'] ?? null);
        $this->assertSame(expected: 'oidc@example.com', actual: $userInfo->claims['email'] ?? null);
        $this->assertSame(expected: 'oidc-user', actual: $userInfo->claims['preferred_username'] ?? null);
    }

    public function testOidcParAuthorizationRequestAndJarmResponseFlow() : void
    {
        [$auth, $provider] = $this->buildAuthWithOidc();

        $auth->register(data: new RegistrationData(
            email   : 'oidc-par@example.com',
            username: 'oidc-par',
            password: 'secret'
        ));
        $auth->login(credentials: new Credentials(
            identifier: 'oidc-par@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
            name         : 'OIDC PAR Client',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid', 'profile', 'email']
        ));

        $pushed = $auth->pushOidcAuthorizationRequest(data: new PushAuthorizationRequestData(
            clientId    : $client->client->clientId,
            redirectUri : 'https://rp.example.test/callback',
            scopes      : ['openid', 'profile', 'email'],
            state       : 'state-par',
            nonce       : 'nonce-par'
        ));

        $code = $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid', 'profile', 'email'],
            nonce      : 'nonce-par',
            requestUri : $pushed->requestUri
        ));
        $jarm = $auth->buildOidcJarmResponse(data: new BuildJarmResponseData(
            clientId: $client->client->clientId,
            code    : $code->code,
            state   : 'state-par'
        ));
        $claims = $provider->resolveJwt(jwt: $jarm->responseJwt);

        $this->assertNotEmpty(actual: $pushed->requestUri);
        $this->assertSame(expected: $client->client->clientId, actual: $claims['aud'] ?? null);
        $this->assertSame(expected: $code->code, actual: $claims['code'] ?? null);
        $this->assertSame(expected: 'state-par', actual: $claims['state'] ?? null);
    }

    public function testOidcParAcceptsClientSignedRequestObjects() : void
    {
        [$auth] = $this->buildAuthWithOidc();

        $auth->register(data: new RegistrationData(
            email: 'oidc-jar@example.com',
            username: 'oidc-jar',
            password: 'secret'
        ));
        $auth->login(credentials: new Credentials(
            identifier: 'oidc-jar@example.com',
            password: 'secret'
        ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
            name: 'OIDC JAR Client',
            type: OAuthClientType::CONFIDENTIAL,
            redirectUris: ['https://rp.example.test/callback'],
            allowedScopes: ['openid', 'profile'],
            requestObjectSignatureRequired: true
        ));
        $requestJwt = (new HmacTokenCodec(secret: $client->plainTextSecret ?? ''))->encode(claims: [
            'client_id' => $client->client->clientId,
            'redirect_uri' => 'https://rp.example.test/callback',
            'scope' => 'openid profile',
            'state' => 'state-jar',
            'nonce' => 'nonce-jar',
            'exp' => time() + 300,
        ]);

        $pushed = $auth->pushOidcAuthorizationRequest(data: new PushAuthorizationRequestData(
            clientId: $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            requestObjectJwt: $requestJwt,
            clientSecret: $client->plainTextSecret
        ));
        $code = $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
            clientId: $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes: ['openid', 'profile'],
            nonce: 'nonce-jar',
            requestUri: $pushed->requestUri
        ));

        $this->assertNotEmpty(actual: $pushed->requestUri);
        $this->assertNotEmpty(actual: $code->code);
    }

    public function testOidcLogoutRevokesCurrentSession() : void
    {
        [$auth] = $this->buildAuthWithOidc();

        $auth->register(data: new RegistrationData(
            email   : 'oidc-logout@example.com',
            username: 'oidc-logout',
            password: 'secret'
        ));
        $auth->login(credentials: new Credentials(
            identifier: 'oidc-logout@example.com',
            password  : 'secret'
        ));

        $sessionId = $auth->current()->sessionId();
        self::assertNotNull($sessionId);

        $result = $auth->oidcLogout(data: new OidcLogoutData(
            sessionId             : $sessionId,
            postLogoutRedirectUri : 'https://rp.example.test/post-logout',
            state                 : 'logout-state'
        ));

        $this->assertTrue(condition: $result->revoked);
        $this->assertSame(expected: $sessionId, actual: $result->sessionId);
        $this->assertFalse(condition: $auth->current()->isAuthenticated());
    }

    public function testOidcAuthorizationRejectsUnknownRequestUri() : void
    {
        [$auth] = $this->buildAuthWithOidc();

        $auth->register(data: new RegistrationData(
            email   : 'oidc-par-invalid@example.com',
            username: 'oidc-par-invalid',
            password: 'secret'
        ));
        $auth->login(credentials: new Credentials(
            identifier: 'oidc-par-invalid@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
            name         : 'OIDC PAR Invalid Client',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid']
        ));

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('OIDC request object is invalid.');

        $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid'],
            nonce      : 'nonce-invalid',
            requestUri : 'urn:ietf:params:oauth:request_uri:missing'
        ));
    }

    public function testOidcAuthorizationRequiresNonce() : void
    {
        [$auth] = $this->buildAuthWithOidc();

        $auth->register(data: new RegistrationData(
            email   : 'oidc-nonce@example.com',
            username: 'oidc-nonce',
            password: 'secret'
        ));
        $auth->login(credentials: new Credentials(
            identifier: 'oidc-nonce@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
            name         : 'OIDC Web',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid']
        ));

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('OIDC nonce is required for this authorization request.');

        $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid']
        ));
    }

    public function testOidcAuthorizationFailsWhenProviderIsMissing() : void
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $auth = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec(secret: 'oidc-auth-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->ready();

        $auth->register(data: new RegistrationData(
            email   : 'oidc-missing@example.com',
            username: 'oidc-missing',
            password: 'secret'
        ));
        $auth->login(credentials: new Credentials(
            identifier: 'oidc-missing@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
            name         : 'OIDC Web',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid']
        ));

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('OIDC provider support is not configured.');

        $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid'],
            nonce      : 'nonce-1'
        ));
    }

    public function testPairwiseOidcProviderIssuesStablePairwiseSubjects() : void
    {
        [$auth, $provider] = $this->buildAuthWithOidc(
            subjectIdentifierStrategy: SubjectIdentifierStrategy::PAIRWISE,
            pairwiseSalt: 'pairwise-salt-1'
        );

        $auth->register(data: new RegistrationData(
            email   : 'pairwise@example.com',
            username: 'pairwise-user',
            password: 'secret'
        ));
        $auth->login(credentials: new Credentials(
            identifier: 'pairwise@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
            name         : 'Pairwise Web',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid', 'profile']
        ));

        $firstCode = $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid', 'profile'],
            nonce      : 'nonce-pairwise-1'
        ));
        $firstGrant = $auth->exchangeOAuthCode(data: new ExchangeAuthorizationCodeData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            code        : $firstCode->code,
            redirectUri : 'https://rp.example.test/callback'
        ));

        $secondCode = $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid', 'profile'],
            nonce      : 'nonce-pairwise-2'
        ));
        $secondGrant = $auth->exchangeOAuthCode(data: new ExchangeAuthorizationCodeData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            code        : $secondCode->code,
            redirectUri : 'https://rp.example.test/callback'
        ));

        $firstClaims = $provider->resolveIdToken(idToken: $firstGrant->idToken ?? '');
        $secondClaims = $provider->resolveIdToken(idToken: $secondGrant->idToken ?? '');
        $userInfo = $auth->readOidcUserInfo(accessToken: $firstGrant->accessToken);

        $this->assertSame(expected: ['pairwise'], actual: $auth->readOidcProviderMetadata()->subjectTypesSupported);
        $this->assertNotSame(expected: '1', actual: $firstClaims['sub'] ?? null);
        $this->assertSame(expected: $firstClaims['sub'] ?? null, actual: $secondClaims['sub'] ?? null);
        $this->assertSame(expected: $firstClaims['sub'] ?? null, actual: $userInfo->claims['sub'] ?? null);
    }

    public function testRotatingOidcProviderPublishesOverlapKeysAndVerifiesLegacyTokens() : void
    {
        $legacyProvider = $this->createOidcProvider(keyId: 'oidc-key-legacy');
        $activeProvider = $this->createOidcProvider(keyId: 'oidc-key-active');
        $provider = new RotatingOidcProvider(
            activeProvider       : $activeProvider,
            verificationProviders: [$legacyProvider]
        );
        $user = User::create(
            id          : new UserId(value: 77),
            email       : new UserEmail(value: 'rotate@example.com'),
            username    : 'rotate',
            passwordHash: 'hash'
        );

        $legacyToken = $legacyProvider->issueIdToken(
            user     : $user,
            clientId : 'rotating-client',
            scopes   : ['openid', 'email'],
            nonce    : 'nonce-rotate'
        );

        $keys = $provider->readJsonWebKeySet()->keys;
        $claims = $provider->resolveIdToken(idToken: $legacyToken->token);

        $this->assertCount(expectedCount: 2, haystack: $keys);
        $this->assertSame(expected: 'nonce-rotate', actual: $claims['nonce'] ?? null);
        $this->assertSame(expected: 'rotate@example.com', actual: $claims['email'] ?? null);
    }

    /**
     * @return array{Auth, OpenSslOidcProvider}
     */
    private function buildAuthWithOidc(
        SubjectIdentifierStrategy $subjectIdentifierStrategy = SubjectIdentifierStrategy::PUBLIC,
        string|null $pairwiseSalt = null
    ) : array
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $sessionRegistry = new InMemorySessionRegistry();
        $jwtIdentity = new JwtIdentity(
            userSource       : $userSource,
            codec            : new HmacTokenCodec(secret: 'oidc-auth-secret'),
            clock            : new Clock(),
            revocationStore  : new InMemoryTokenRevocationStore(),
            refreshTokenStore: $refreshTokens
        );
        $provider = $this->createOidcProvider(
            subjectIdentifierStrategy: $subjectIdentifierStrategy,
            pairwiseSalt            : $pairwiseSalt
        );
        $auth = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(
                sessionIdentity: new SessionIdentity(sessionRegistry: $sessionRegistry),
                jwtIdentity    : $jwtIdentity
            ))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->withOidcProvider(oidcProvider: $provider)
            ->ready();

        return [$auth, $provider];
    }

    private function createOidcProvider(
        string $keyId = 'oidc-key-1',
        SubjectIdentifierStrategy $subjectIdentifierStrategy = SubjectIdentifierStrategy::PUBLIC,
        string|null $pairwiseSalt = null
    ) : OpenSslOidcProvider
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
            keyId                : $keyId,
            authorizationEndpoint: 'https://auth.example.test/oauth/authorize',
            tokenEndpoint        : 'https://auth.example.test/oauth/token',
            userInfoEndpoint     : 'https://auth.example.test/oidc/userinfo',
            jsonWebKeySetUri     : 'https://auth.example.test/.well-known/jwks.json',
            subjectIdentifierStrategy: $subjectIdentifierStrategy,
            pairwiseSalt: $pairwiseSalt
        );
    }
}
