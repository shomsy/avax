<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Oidc;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\Oidc\OpenSslOidcProvider;
use Avax\Auth\System\Capability\Oidc\RotatingOidcProvider;
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

        $auth->register(new RegistrationData(
            email   : 'oidc@example.com',
            username: 'oidc-user',
            password: 'secret'
        ));
        $auth->login(new Credentials(
            identifier: 'oidc@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(new RegisterClientData(
            name         : 'OIDC Web',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid', 'profile', 'email']
        ));

        $code = $auth->authorizeOAuthCode(new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid', 'profile', 'email'],
            nonce      : 'nonce-1'
        ));
        $grant = $auth->exchangeOAuthCode(new ExchangeAuthorizationCodeData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            code        : $code->code,
            redirectUri : 'https://rp.example.test/callback'
        ));

        $metadata = $auth->readOidcProviderMetadata();
        $jsonWebKeySet = $auth->readOidcJsonWebKeySet();
        $userInfo = $auth->readOidcUserInfo($grant->accessToken);
        $claims = $provider->resolveIdToken($grant->idToken ?? '');

        $this->assertSame('https://auth.example.test', $metadata->issuer);
        $this->assertSame('https://auth.example.test/.well-known/jwks.json', $metadata->jsonWebKeySetUri);
        $this->assertCount(1, $jsonWebKeySet->keys);
        $this->assertNotNull($grant->idToken);
        $this->assertSame('nonce-1', $claims['nonce'] ?? null);
        $this->assertSame($client->client->clientId, $claims['aud'] ?? null);
        $this->assertSame('oidc@example.com', $userInfo->claims['email'] ?? null);
        $this->assertSame('oidc-user', $userInfo->claims['preferred_username'] ?? null);
    }

    public function testOidcAuthorizationRequiresNonce() : void
    {
        [$auth] = $this->buildAuthWithOidc();

        $auth->register(new RegistrationData(
            email   : 'oidc-nonce@example.com',
            username: 'oidc-nonce',
            password: 'secret'
        ));
        $auth->login(new Credentials(
            identifier: 'oidc-nonce@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(new RegisterClientData(
            name         : 'OIDC Web',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid']
        ));

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('OIDC nonce is required for this authorization request.');

        $auth->authorizeOAuthCode(new AuthorizeCodeData(
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
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec('oidc-auth-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore($refreshTokens)
            ->ready();

        $auth->register(new RegistrationData(
            email   : 'oidc-missing@example.com',
            username: 'oidc-missing',
            password: 'secret'
        ));
        $auth->login(new Credentials(
            identifier: 'oidc-missing@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(new RegisterClientData(
            name         : 'OIDC Web',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://rp.example.test/callback'],
            allowedScopes: ['openid']
        ));

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('OIDC provider support is not configured.');

        $auth->authorizeOAuthCode(new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://rp.example.test/callback',
            scopes     : ['openid'],
            nonce      : 'nonce-1'
        ));
    }

    public function testRotatingOidcProviderPublishesOverlapKeysAndVerifiesLegacyTokens() : void
    {
        $legacyProvider = $this->createOidcProvider('oidc-key-legacy');
        $activeProvider = $this->createOidcProvider('oidc-key-active');
        $provider = new RotatingOidcProvider(
            activeProvider       : $activeProvider,
            verificationProviders: [$legacyProvider]
        );
        $user = User::create(
            id          : new UserId(77),
            email       : new UserEmail('rotate@example.com'),
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
        $claims = $provider->resolveIdToken($legacyToken->token);

        $this->assertCount(2, $keys);
        $this->assertSame('nonce-rotate', $claims['nonce'] ?? null);
        $this->assertSame('rotate@example.com', $claims['email'] ?? null);
    }

    /**
     * @return array{Auth, OpenSslOidcProvider}
     */
    private function buildAuthWithOidc() : array
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $jwtIdentity = new JwtIdentity(
            userSource       : $userSource,
            codec            : new HmacTokenCodec('oidc-auth-secret'),
            clock            : new Clock(),
            revocationStore  : new InMemoryTokenRevocationStore(),
            refreshTokenStore: $refreshTokens
        );
        $provider = $this->createOidcProvider();
        $auth = Auth::configuration()
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: $jwtIdentity))
            ->withRefreshTokenStore($refreshTokens)
            ->withOidcProvider($provider)
            ->ready();

        return [$auth, $provider];
    }

    private function createOidcProvider(string $keyId = 'oidc-key-1') : OpenSslOidcProvider
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
            keyId                : $keyId,
            authorizationEndpoint: 'https://auth.example.test/oauth/authorize',
            tokenEndpoint        : 'https://auth.example.test/oauth/token',
            userInfoEndpoint     : 'https://auth.example.test/oidc/userinfo',
            jsonWebKeySetUri     : 'https://auth.example.test/.well-known/jwks.json'
        );
    }
}
