<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\OAuth;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\OAuth\PkceMethod;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\OAuth\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Flow\OAuth\ExchangeClientCredentials\ExchangeClientCredentialsData;
use Avax\Auth\System\Flow\OAuth\ExchangeRefreshToken\ExchangeRefreshTokenData;
use Avax\Auth\System\Flow\OAuth\IntrospectToken\IntrospectTokenData;
use Avax\Auth\System\Flow\OAuth\OAuthAuthorizationFailed;
use Avax\Auth\System\Flow\OAuth\OAuthTokenExchangeFailed;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Flow\OAuth\RevokeToken\RevokeTokenData;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class OAuthFlowTest extends TestCase
{
    public function testPublicClientRequiresPkceDuringAuthorization() : void
    {
        $auth = $this->buildAuth();

        $auth->register(new RegistrationData(
            email   : 'oauth-public@example.com',
            username: 'oauth-public',
            password: 'secret'
        ));
        $auth->login(new Credentials(
            identifier: 'oauth-public@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(new RegisterClientData(
            name         : 'Public SPA',
            type         : OAuthClientType::PUBLIC,
            redirectUris : ['https://spa.example.test/callback'],
            allowedScopes: ['profile']
        ));

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('PKCE is required for this client.');

        $auth->authorizeOAuthCode(new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://spa.example.test/callback',
            scopes     : ['profile']
        ));
    }

    public function testOAuthAuthorizationCodeRefreshIntrospectionAndRevokeCycle() : void
    {
        $auditLog = new InMemoryAuditLog();
        $auth     = $this->buildAuth($auditLog);

        $auth->register(new RegistrationData(
            email   : 'oauth@example.com',
            username: 'oauth-user',
            password: 'secret'
        ));
        $auth->login(new Credentials(
            identifier: 'oauth@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(new RegisterClientData(
            name         : 'Backoffice',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://app.example.test/callback'],
            allowedScopes: ['email', 'profile']
        ));

        $code = $auth->authorizeOAuthCode(new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://app.example.test/callback',
            scopes     : ['profile', 'email']
        ));

        $grant = $auth->exchangeOAuthCode(new ExchangeAuthorizationCodeData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            code        : $code->code,
            redirectUri : 'https://app.example.test/callback'
        ));

        $this->assertSame($client->client->clientId, $grant->clientId);
        $this->assertSame(['email', 'profile'], $grant->scopes);
        $this->assertNotNull($grant->refreshToken);

        $introspection = $auth->introspectOAuthToken(new IntrospectTokenData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            token       : $grant->accessToken
        ));

        $this->assertTrue($introspection->active);
        $this->assertSame($client->client->clientId, $introspection->clientId);
        $this->assertSame(['email', 'profile'], $introspection->scopes);

        $refreshed = $auth->exchangeOAuthRefreshToken(new ExchangeRefreshTokenData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            refreshToken: $grant->refreshToken ?? ''
        ));

        $this->assertNotSame($grant->refreshToken, $refreshed->refreshToken);

        try {
            $auth->exchangeOAuthRefreshToken(new ExchangeRefreshTokenData(
                clientId    : $client->client->clientId,
                clientSecret: $client->plainTextSecret,
                refreshToken: $grant->refreshToken ?? ''
            ));
            $this->fail('Expected refresh token reuse to fail.');
        } catch (OAuthTokenExchangeFailed $exception) {
            $this->assertSame('Invalid OAuth grant.', $exception->getMessage());
        }

        $this->assertContains(
            'auth.oauth.refresh.reuse_detected',
            array_map(static fn ($event) => $event->name, $auditLog->events())
        );

        $auth->revokeOAuthToken(new RevokeTokenData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            token       : $refreshed->accessToken,
            tokenTypeHint: 'access_token'
        ));

        $inactive = $auth->introspectOAuthToken(new IntrospectTokenData(
            clientId    : $client->client->clientId,
            clientSecret: $client->plainTextSecret,
            token       : $refreshed->accessToken
        ));

        $this->assertFalse($inactive->active);
    }

    public function testPhishingResistantOAuthClientRejectsCompatibilityLoginAsPrimaryPath() : void
    {
        $auth = $this->buildAuth();

        $auth->register(new RegistrationData(
            email   : 'oauth-high-assurance@example.com',
            username: 'oauth-high-assurance',
            password: 'secret'
        ));
        $auth->login(new Credentials(
            identifier: 'oauth-high-assurance@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(new RegisterClientData(
            name                     : 'High Assurance Backoffice',
            type                     : OAuthClientType::CONFIDENTIAL,
            redirectUris             : ['https://secure.example.test/callback'],
            allowedScopes            : ['profile'],
            phishingResistantRequired: true
        ));

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('Phishing-resistant authentication is required for this client.');

        $auth->authorizeOAuthCode(new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://secure.example.test/callback',
            scopes     : ['profile']
        ));
    }

    public function testOAuthRefreshRequiresMatchingSenderConstraint() : void
    {
        $auth = $this->buildAuth();

        $auth->register(new RegistrationData(
            email   : 'oauth-bound@example.com',
            username: 'oauth-bound',
            password: 'secret'
        ));
        $auth->login(new Credentials(
            identifier: 'oauth-bound@example.com',
            password  : 'secret'
        ));

        $client = $auth->registerOAuthClient(new RegisterClientData(
            name                     : 'Bound API',
            type                     : OAuthClientType::CONFIDENTIAL,
            redirectUris             : ['https://bound.example.test/callback'],
            allowedScopes            : ['profile'],
            allowedGrantTypes        : [OAuthGrantType::AUTHORIZATION_CODE, OAuthGrantType::REFRESH_TOKEN],
            requiredSenderConstraint : OAuthSenderConstraintType::DPOP
        ));

        $code = $auth->authorizeOAuthCode(new AuthorizeCodeData(
            clientId   : $client->client->clientId,
            redirectUri: 'https://bound.example.test/callback',
            scopes     : ['profile']
        ));
        $binding = new OAuthSenderConstraint(
            type      : OAuthSenderConstraintType::DPOP,
            thumbprint: 'thumb-1'
        );

        $grant = $auth->exchangeOAuthCode(new ExchangeAuthorizationCodeData(
            clientId         : $client->client->clientId,
            clientSecret     : $client->plainTextSecret,
            code             : $code->code,
            redirectUri      : 'https://bound.example.test/callback',
            senderConstraint : $binding
        ));

        $this->assertSame('DPoP', $grant->tokenType);
        $this->assertNotNull($grant->senderConstraint);
        $this->assertSame('thumb-1', $grant->senderConstraint?->thumbprint);

        $refreshed = $auth->exchangeOAuthRefreshToken(new ExchangeRefreshTokenData(
            clientId         : $client->client->clientId,
            clientSecret     : $client->plainTextSecret,
            refreshToken     : $grant->refreshToken ?? '',
            senderConstraint : $binding
        ));

        $this->assertSame('DPoP', $refreshed->tokenType);

        $this->expectException(OAuthTokenExchangeFailed::class);
        $this->expectExceptionMessage('Invalid sender constraint proof.');

        $auth->exchangeOAuthRefreshToken(new ExchangeRefreshTokenData(
            clientId         : $client->client->clientId,
            clientSecret     : $client->plainTextSecret,
            refreshToken     : $refreshed->refreshToken ?? '',
            senderConstraint : new OAuthSenderConstraint(
                type      : OAuthSenderConstraintType::DPOP,
                thumbprint: 'thumb-2'
            )
        ));
    }

    public function testOAuthClientCredentialsIssuesWorkloadTokenWithAudienceAndConstraint() : void
    {
        $auth = $this->buildAuth();

        $client = $auth->registerOAuthClient(new RegisterClientData(
            name                    : 'Orders Worker',
            type                    : OAuthClientType::CONFIDENTIAL,
            redirectUris            : ['urn:avax:oauth:orders-worker'],
            allowedScopes           : ['orders.read', 'orders.write'],
            allowedAudiences        : ['orders-api'],
            allowedGrantTypes       : [OAuthGrantType::CLIENT_CREDENTIALS],
            audienceScopeBoundaries : [
                'orders-api' => ['orders.read'],
            ],
            requiredSenderConstraint: OAuthSenderConstraintType::MTLS,
            workloadIdentity        : true
        ));

        $grant = $auth->exchangeOAuthClientCredentials(new ExchangeClientCredentialsData(
            clientId         : $client->client->clientId,
            clientSecret     : $client->plainTextSecret,
            scopes           : ['orders.read'],
            audience         : 'orders-api',
            senderConstraint : new OAuthSenderConstraint(
                type      : OAuthSenderConstraintType::MTLS,
                thumbprint: 'cert-thumb-1'
            )
        ));

        $this->assertTrue($grant->workloadIdentity);
        $this->assertSame('client:' . $client->client->clientId, $grant->subject);
        $this->assertSame('orders-api', $grant->audience);
        $this->assertNull($grant->refreshToken);

        $introspection = $auth->introspectOAuthToken(new IntrospectTokenData(
            clientId         : $client->client->clientId,
            clientSecret     : $client->plainTextSecret,
            token            : $grant->accessToken,
            expectedAudience : 'orders-api'
        ));

        $this->assertTrue($introspection->active);
        $this->assertTrue($introspection->workloadIdentity);
        $this->assertSame($grant->subject, $introspection->subject);
        $this->assertSame('orders-api', $introspection->audience);
        $this->assertSame(OAuthSenderConstraintType::MTLS, $introspection->senderConstraint?->type);

        $auth->revokeOAuthToken(new RevokeTokenData(
            clientId      : $client->client->clientId,
            clientSecret  : $client->plainTextSecret,
            token         : $grant->accessToken,
            tokenTypeHint : 'access_token'
        ));

        $inactive = $auth->introspectOAuthToken(new IntrospectTokenData(
            clientId         : $client->client->clientId,
            clientSecret     : $client->plainTextSecret,
            token            : $grant->accessToken,
            expectedAudience : 'orders-api'
        ));

        $this->assertFalse($inactive->active);
    }

    public function testOAuthClientCredentialsRejectsAudienceScopeBoundaryViolations() : void
    {
        $auth = $this->buildAuth();

        $client = $auth->registerOAuthClient(new RegisterClientData(
            name                    : 'Orders Worker',
            type                    : OAuthClientType::CONFIDENTIAL,
            redirectUris            : ['urn:avax:oauth:orders-worker'],
            allowedScopes           : ['orders.read', 'orders.write'],
            allowedAudiences        : ['orders-api'],
            allowedGrantTypes       : [OAuthGrantType::CLIENT_CREDENTIALS],
            audienceScopeBoundaries : [
                'orders-api' => ['orders.read'],
            ],
            requiredSenderConstraint: OAuthSenderConstraintType::MTLS,
            workloadIdentity        : true
        ));

        $this->expectException(OAuthTokenExchangeFailed::class);
        $this->expectExceptionMessage('Invalid OAuth grant.');

        $auth->exchangeOAuthClientCredentials(new ExchangeClientCredentialsData(
            clientId         : $client->client->clientId,
            clientSecret     : $client->plainTextSecret,
            scopes           : ['orders.write'],
            audience         : 'orders-api',
            senderConstraint : new OAuthSenderConstraint(
                type      : OAuthSenderConstraintType::MTLS,
                thumbprint: 'cert-thumb-1'
            )
        ));
    }

    public function testReadWorkloadIdentitiesReturnsRegisteredMachineClients() : void
    {
        $auth = $this->buildAuth();

        $machineClient = $auth->registerOAuthClient(new RegisterClientData(
            name                    : 'Billing Worker',
            type                    : OAuthClientType::CONFIDENTIAL,
            redirectUris            : ['urn:avax:oauth:billing-worker'],
            allowedScopes           : ['billing.read', 'billing.write'],
            allowedAudiences        : ['billing-api'],
            allowedGrantTypes       : [OAuthGrantType::CLIENT_CREDENTIALS],
            audienceScopeBoundaries : [
                'billing-api' => ['billing.read'],
            ],
            requiredSenderConstraint: OAuthSenderConstraintType::MTLS,
            workloadIdentity        : true,
            phishingResistantRequired: true
        ));
        $auth->registerOAuthClient(new RegisterClientData(
            name         : 'Human Backoffice',
            type         : OAuthClientType::CONFIDENTIAL,
            redirectUris : ['https://app.example.test/callback'],
            allowedScopes: ['profile']
        ));

        $profiles = $auth->readWorkloadIdentities();

        $this->assertCount(1, $profiles);
        $this->assertSame($machineClient->client->clientId, $profiles[0]->clientId);
        $this->assertSame(['billing-api'], $profiles[0]->allowedAudiences);
        $this->assertSame(['billing.read'], $profiles[0]->audienceScopeBoundaries['billing-api']);
        $this->assertTrue($profiles[0]->phishingResistantRequired);
    }

    private function buildAuth(InMemoryAuditLog|null $auditLog = null) : Auth
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $jwtIdentity   = new JwtIdentity(
            userSource       : $userSource,
            codec            : new HmacTokenCodec('oauth-flow-secret'),
            clock            : new Clock(),
            revocationStore  : new InMemoryTokenRevocationStore(),
            refreshTokenStore: $refreshTokens
        );

        return Auth::configuration()
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: $jwtIdentity))
            ->withRefreshTokenStore($refreshTokens)
            ->withAuditLog($auditLog ?? new InMemoryAuditLog())
            ->ready();
    }
}
