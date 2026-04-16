<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\OAuth;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\OAuth\OAuthClientType;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Login\AuthenticationFailed;
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
use Avax\Auth\System\Flow\Register\RegistrationFailed;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class OAuthFlowTest extends TestCase
{
    /**
     * @throws \DateMalformedStringException
     * @throws RegistrationFailed
     * @throws AuthenticationFailed
     */
    public function testPublicClientRequiresPkceDuringAuthorization() : void
    {
        $auth = $this->buildAuth();

        $auth->register(data: new RegistrationData(
                                  email   : 'oauth-public@example.com',
                                  username: 'oauth-public',
                                  password: 'secret'
                              ));
        $auth->login(credentials: new Credentials(
                                      identifier: 'oauth-public@example.com',
                                      password  : 'secret'
                                  ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
                                                       name         : 'Public SPA',
                                                       type         : OAuthClientType::PUBLIC,
                                                       redirectUris : ['https://spa.example.test/callback'],
                                                       allowedScopes: ['profile']
                                                   ));

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('PKCE is required for this client.');

        $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
                                            clientId   : $client->client->clientId,
                                            redirectUri: 'https://spa.example.test/callback',
                                            scopes     : ['profile']
                                        ));
    }

    private function buildAuth(InMemoryAuditLog|null $auditLog = null) : Auth
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $jwtIdentity   = new JwtIdentity(
            userSource       : $userSource,
            codec            : new HmacTokenCodec(secret: 'oauth-flow-secret'),
            clock            : new Clock(),
            revocationStore  : new InMemoryTokenRevocationStore(),
            refreshTokenStore: $refreshTokens
        );

        return Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: $jwtIdentity))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->withAuditLog(auditLog: $auditLog ?? new InMemoryAuditLog())
            ->ready();
    }

    /**
     * @throws \DateMalformedStringException
     * @throws AuthenticationFailed
     * @throws RegistrationFailed
     */
    public function testOAuthAuthorizationCodeRefreshIntrospectionAndRevokeCycle() : void
    {
        $auditLog = new InMemoryAuditLog();
        $auth     = $this->buildAuth(auditLog: $auditLog);

        $auth->register(data: new RegistrationData(
                                  email   : 'oauth@example.com',
                                  username: 'oauth-user',
                                  password: 'secret'
                              ));
        $auth->login(credentials: new Credentials(
                                      identifier: 'oauth@example.com',
                                      password  : 'secret'
                                  ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
                                                       name         : 'Backoffice',
                                                       type         : OAuthClientType::CONFIDENTIAL,
                                                       redirectUris : ['https://app.example.test/callback'],
                                                       allowedScopes: ['email', 'profile']
                                                   ));

        $code = $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
                                                    clientId   : $client->client->clientId,
                                                    redirectUri: 'https://app.example.test/callback',
                                                    scopes     : ['profile', 'email']
                                                ));

        $grant = $auth->exchangeOAuthCode(data: new ExchangeAuthorizationCodeData(
                                                    clientId    : $client->client->clientId,
                                                    code        : $code->code,
                                                    redirectUri : 'https://app.example.test/callback',
                                                    clientSecret: $client->plainTextSecret
                                                ));

        $this->assertSame(expected: $client->client->clientId, actual: $grant->clientId);
        $this->assertSame(expected: ['email', 'profile'], actual: $grant->scopes);
        $this->assertNotNull(actual: $grant->refreshToken);

        $introspection = $auth->introspectOAuthToken(data: new IntrospectTokenData(
                                                               clientId    : $client->client->clientId,
                                                               token       : $grant->accessToken,
                                                               clientSecret: $client->plainTextSecret
                                                           ));

        $this->assertTrue(condition: $introspection->active);
        $this->assertSame(expected: $client->client->clientId, actual: $introspection->clientId);
        $this->assertSame(expected: ['email', 'profile'], actual: $introspection->scopes);

        $refreshed = $auth->exchangeOAuthRefreshToken(data: new ExchangeRefreshTokenData(
                                                                clientId    : $client->client->clientId,
                                                                refreshToken: $grant->refreshToken ?? '',
                                                                clientSecret: $client->plainTextSecret
                                                            ));

        $this->assertNotSame(expected: $grant->refreshToken, actual: $refreshed->refreshToken);

        try {
            $auth->exchangeOAuthRefreshToken(data: new ExchangeRefreshTokenData(
                                                       clientId    : $client->client->clientId,
                                                       refreshToken: $grant->refreshToken ?? '',
                                                       clientSecret: $client->plainTextSecret
                                                   ));
            $this->fail(message: 'Expected refresh token reuse to fail.');
        } catch (OAuthTokenExchangeFailed $exception) {
            $this->assertSame(expected: 'Invalid OAuth grant.', actual: $exception->getMessage());
        }

        $this->assertContains(
            needle  : 'auth.oauth.refresh.reuse_detected',
            haystack: array_map(static fn ($event) => $event->name, $auditLog->events())
        );

        $auth->revokeOAuthToken(data: new RevokeTokenData(
                                          clientId     : $client->client->clientId,
                                          token        : $refreshed->accessToken,
                                          clientSecret : $client->plainTextSecret,
                                          tokenTypeHint: 'access_token'
                                      ));

        $inactive = $auth->introspectOAuthToken(data: new IntrospectTokenData(
                                                          clientId    : $client->client->clientId,
                                                          token       : $refreshed->accessToken,
                                                          clientSecret: $client->plainTextSecret
                                                      ));

        $this->assertFalse(condition: $inactive->active);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws AuthenticationFailed
     * @throws RegistrationFailed
     */
    public function testPhishingResistantOAuthClientRejectsCompatibilityLoginAsPrimaryPath() : void
    {
        $auth = $this->buildAuth();

        $auth->register(data: new RegistrationData(
                                  email   : 'oauth-high-assurance@example.com',
                                  username: 'oauth-high-assurance',
                                  password: 'secret'
                              ));
        $auth->login(credentials: new Credentials(
                                      identifier: 'oauth-high-assurance@example.com',
                                      password  : 'secret'
                                  ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
                                                       name                     : 'High Assurance Backoffice',
                                                       type                     : OAuthClientType::CONFIDENTIAL,
                                                       redirectUris             : ['https://secure.example.test/callback'],
                                                       allowedScopes            : ['profile'],
                                                       phishingResistantRequired: true
                                                   ));

        $this->expectException(OAuthAuthorizationFailed::class);
        $this->expectExceptionMessage('Phishing-resistant authentication is required for this client.');

        $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
                                            clientId   : $client->client->clientId,
                                            redirectUri: 'https://secure.example.test/callback',
                                            scopes     : ['profile']
                                        ));
    }

    /**
     * @throws \DateMalformedStringException
     * @throws AuthenticationFailed
     * @throws RegistrationFailed
     */
    public function testOAuthRefreshRequiresMatchingSenderConstraint() : void
    {
        $auth = $this->buildAuth();

        $auth->register(data: new RegistrationData(
                                  email   : 'oauth-bound@example.com',
                                  username: 'oauth-bound',
                                  password: 'secret'
                              ));
        $auth->login(credentials: new Credentials(
                                      identifier: 'oauth-bound@example.com',
                                      password  : 'secret'
                                  ));

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
                                                       name                    : 'Bound API',
                                                       type                    : OAuthClientType::CONFIDENTIAL,
                                                       redirectUris            : ['https://bound.example.test/callback'],
                                                       allowedScopes           : ['profile'],
                                                       allowedGrantTypes       : [OAuthGrantType::AUTHORIZATION_CODE, OAuthGrantType::REFRESH_TOKEN],
                                                       requiredSenderConstraint: OAuthSenderConstraintType::DPOP
                                                   ));

        $code    = $auth->authorizeOAuthCode(data: new AuthorizeCodeData(
                                                       clientId   : $client->client->clientId,
                                                       redirectUri: 'https://bound.example.test/callback',
                                                       scopes     : ['profile']
                                                   ));
        $binding = new OAuthSenderConstraint(
            type      : OAuthSenderConstraintType::DPOP,
            thumbprint: 'thumb-1'
        );

        $grant = $auth->exchangeOAuthCode(data: new ExchangeAuthorizationCodeData(
                                                    clientId        : $client->client->clientId,
                                                    code            : $code->code,
                                                    redirectUri     : 'https://bound.example.test/callback',
                                                    clientSecret    : $client->plainTextSecret,
                                                    senderConstraint: $binding
                                                ));

        $this->assertSame(expected: 'DPoP', actual: $grant->tokenType);
        $this->assertNotNull(actual: $grant->senderConstraint);
        $this->assertSame(expected: 'thumb-1', actual: $grant->senderConstraint?->thumbprint);

        $refreshed = $auth->exchangeOAuthRefreshToken(data: new ExchangeRefreshTokenData(
                                                                clientId        : $client->client->clientId,
                                                                refreshToken    : $grant->refreshToken ?? '',
                                                                clientSecret    : $client->plainTextSecret,
                                                                senderConstraint: $binding
                                                            ));

        $this->assertSame(expected: 'DPoP', actual: $refreshed->tokenType);

        $this->expectException(OAuthTokenExchangeFailed::class);
        $this->expectExceptionMessage('Invalid sender constraint proof.');

        $auth->exchangeOAuthRefreshToken(data: new ExchangeRefreshTokenData(
                                                   clientId        : $client->client->clientId,
                                                   refreshToken    : $refreshed->refreshToken ?? '',
                                                   clientSecret    : $client->plainTextSecret,
                                                   senderConstraint: new OAuthSenderConstraint(
                                                                         type      : OAuthSenderConstraintType::DPOP,
                                                                         thumbprint: 'thumb-2'
                                                                     )
                                               ));
    }

    public function testOAuthClientCredentialsIssuesWorkloadTokenWithAudienceAndConstraint() : void
    {
        $auth = $this->buildAuth();

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
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

        $grant = $auth->exchangeOAuthClientCredentials(data: new ExchangeClientCredentialsData(
                                                                 clientId        : $client->client->clientId,
                                                                 clientSecret    : $client->plainTextSecret,
                                                                 scopes          : ['orders.read'],
                                                                 audience        : 'orders-api',
                                                                 senderConstraint: new OAuthSenderConstraint(
                                                                                       type      : OAuthSenderConstraintType::MTLS,
                                                                                       thumbprint: 'cert-thumb-1'
                                                                                   )
                                                             ));

        $this->assertTrue(condition: $grant->workloadIdentity);
        $this->assertSame(expected: 'client:' . $client->client->clientId, actual: $grant->subject);
        $this->assertSame(expected: 'orders-api', actual: $grant->audience);
        $this->assertNull(actual: $grant->refreshToken);

        $introspection = $auth->introspectOAuthToken(data: new IntrospectTokenData(
                                                               clientId        : $client->client->clientId,
                                                               token           : $grant->accessToken,
                                                               clientSecret    : $client->plainTextSecret,
                                                               expectedAudience: 'orders-api'
                                                           ));

        $this->assertTrue(condition: $introspection->active);
        $this->assertTrue(condition: $introspection->workloadIdentity);
        $this->assertSame(expected: $grant->subject, actual: $introspection->subject);
        $this->assertSame(expected: 'orders-api', actual: $introspection->audience);
        $this->assertSame(expected: OAuthSenderConstraintType::MTLS, actual: $introspection->senderConstraint?->type);

        $auth->revokeOAuthToken(data: new RevokeTokenData(
                                          clientId     : $client->client->clientId,
                                          token        : $grant->accessToken,
                                          clientSecret : $client->plainTextSecret,
                                          tokenTypeHint: 'access_token'
                                      ));

        $inactive = $auth->introspectOAuthToken(data: new IntrospectTokenData(
                                                          clientId        : $client->client->clientId,
                                                          token           : $grant->accessToken,
                                                          clientSecret    : $client->plainTextSecret,
                                                          expectedAudience: 'orders-api'
                                                      ));

        $this->assertFalse(condition: $inactive->active);
    }

    public function testOAuthClientCredentialsRejectsAudienceScopeBoundaryViolations() : void
    {
        $auth = $this->buildAuth();

        $client = $auth->registerOAuthClient(data: new RegisterClientData(
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

        $auth->exchangeOAuthClientCredentials(data: new ExchangeClientCredentialsData(
                                                        clientId        : $client->client->clientId,
                                                        clientSecret    : $client->plainTextSecret,
                                                        scopes          : ['orders.write'],
                                                        audience        : 'orders-api',
                                                        senderConstraint: new OAuthSenderConstraint(
                                                                              type      : OAuthSenderConstraintType::MTLS,
                                                                              thumbprint: 'cert-thumb-1'
                                                                          )
                                                    ));
    }

    public function testReadWorkloadIdentitiesReturnsRegisteredMachineClients() : void
    {
        $auth = $this->buildAuth();

        $machineClient = $auth->registerOAuthClient(data: new RegisterClientData(
                                                              name                     : 'Billing Worker',
                                                              type                     : OAuthClientType::CONFIDENTIAL,
                                                              redirectUris             : ['urn:avax:oauth:billing-worker'],
                                                              allowedScopes            : ['billing.read', 'billing.write'],
                                                              allowedAudiences         : ['billing-api'],
                                                              allowedGrantTypes        : [OAuthGrantType::CLIENT_CREDENTIALS],
                                                              audienceScopeBoundaries  : [
                                                                                             'billing-api' => ['billing.read'],
                                                                                         ],
                                                              requiredSenderConstraint : OAuthSenderConstraintType::MTLS,
                                                              workloadIdentity         : true,
                                                              phishingResistantRequired: true
                                                          ));
        $auth->registerOAuthClient(data: new RegisterClientData(
                                             name         : 'Human Backoffice',
                                             type         : OAuthClientType::CONFIDENTIAL,
                                             redirectUris : ['https://app.example.test/callback'],
                                             allowedScopes: ['profile']
                                         ));

        $profiles = $auth->readWorkloadIdentities();

        $this->assertCount(expectedCount: 1, haystack: $profiles);
        $this->assertSame(expected: $machineClient->client->clientId, actual: $profiles[0]->clientId);
        $this->assertSame(expected: ['billing-api'], actual: $profiles[0]->allowedAudiences);
        $this->assertSame(expected: ['billing.read'], actual: $profiles[0]->audienceScopeBoundaries['billing-api']);
        $this->assertTrue(condition: $profiles[0]->phishingResistantRequired);
    }
}
