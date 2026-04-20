<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Federation;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capabilities\Federation\FederationProvider;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capabilities\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flows\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flows\Federation\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Auth\System\Flows\Federation\FederationFailed;
use Avax\Auth\System\Flows\Federation\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Flows\Federation\StartFederatedLogin\StartFederatedLoginData;
use Avax\Auth\System\Flows\Federation\VerifyDomain\VerifyFederationDomainData;
use Avax\Auth\System\Flows\Token\HmacTokenCodec;
use Avax\Auth\System\Flows\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flows\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\Tests\Support\FakeFederationRuntime;
use PHPUnit\Framework\TestCase;

final class FederationFlowTest extends TestCase
{
    public function testFederationConnectionDiscoveryAndLoginProvisioning() : void
    {
        $auditLog      = new InMemoryAuditLog();
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $runtime       = new FakeFederationRuntime();
        $auth          = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                                                                   userSource       : $userSource,
                                                                   codec            : new HmacTokenCodec(secret: 'federation-secret'),
                                                                   clock            : new Clock(),
                                                                   revocationStore  : new InMemoryTokenRevocationStore(),
                                                                   refreshTokenStore: $refreshTokens
                                                               )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->withAuditLog(auditLog: $auditLog)
            ->withFederationRuntime(federationRuntime: $runtime)
            ->ready();

        $connection = $auth->registerFederationConnection(data: new RegisterFederationConnectionData(
                                                                    tenantSlug       : 'acme',
                                                                    name             : 'Acme OIDC',
                                                                    provider         : FederationProvider::OIDC,
                                                                    domain           : 'acme.test',
                                                                    ssoOnly          : true,
                                                                    groupRoleMap     : ['admins' => ['admin']],
                                                                    metadataUrl      : 'https://idp.example.test/.well-known/openid-configuration',
                                                                    breakGlassAllowed: true
                                                                ));

        $this->assertNull(actual: $auth->discoverFederationConnection(email: 'user@acme.test'));

        $verified = $auth->verifyFederationDomain(data: new VerifyFederationDomainData(
                                                            connectionId     : $connection->connectionId,
                                                            verificationToken: $connection->domainVerificationToken ?? ''
                                                        ));
        $this->assertTrue(condition: $verified->isDomainVerified());
        $this->assertSame(expected: $connection->connectionId, actual: $auth->discoverFederationConnection(email: 'user@acme.test')?->connectionId);

        $synced = $auth->syncFederationMetadata(connectionId: $connection->connectionId);
        $this->assertNotNull(actual: $synced->metadataHash);
        $this->assertNotNull(actual: $synced->metadataIssuer);

        $this->assertSame(expected: FederationConnectionHealth::HEALTHY, actual: $auth->checkFederationConnectionHealth(connectionId: $connection->connectionId));
        $this->assertFalse(condition: $auth->evaluateFederationBreakGlassBypass(connectionId: $connection->connectionId));

        $runtime->health = FederationConnectionHealth::UNAVAILABLE;
        $this->assertSame(expected: FederationConnectionHealth::UNAVAILABLE, actual: $auth->checkFederationConnectionHealth(connectionId: $connection->connectionId));
        $this->assertTrue(condition: $auth->evaluateFederationBreakGlassBypass(connectionId: $connection->connectionId));
        $this->assertContains(
            needle  : 'auth.federation.break_glass.denied',
            haystack: array_map(static fn ($event) => $event->name, $auditLog->events())
        );
        $this->assertContains(
            needle  : 'auth.federation.break_glass.allowed',
            haystack: array_map(static fn ($event) => $event->name, $auditLog->events())
        );

        try {
            $auth->startFederatedLogin(data: new StartFederatedLoginData(
                                                 connectionId: $connection->connectionId,
                                                 redirectUri : 'https://app.example.test/callback',
                                                 state       : 'state-break-glass'
                                             ));
            $this->fail(message: 'Expected unavailable federation connection to block SSO start.');
        } catch (FederationFailed $exception) {
            $this->assertSame(expected: 'Federation connection is unavailable.', actual: $exception->getMessage());
        }

        $runtime->health = FederationConnectionHealth::HEALTHY;
        $auth->checkFederationConnectionHealth(connectionId: $connection->connectionId);

        $started = $auth->startFederatedLogin(data: new StartFederatedLoginData(
                                                        connectionId: $connection->connectionId,
                                                        redirectUri : 'https://app.example.test/callback',
                                                        state       : 'state-1'
                                                    ));
        $this->assertStringContainsString(needle: $connection->connectionId, haystack: $started->redirectUrl);

        $result = $auth->completeFederatedLogin(data: new CompleteFederatedLoginData(
                                                          connectionId: $connection->connectionId,
                                                          payload     : [
                                                                            'subject'      => 'external-123',
                                                                            'email'        => 'admin@acme.test',
                                                                            'display_name' => 'Acme Admin',
                                                                            'groups'       => ['admins'],
                                                                        ],
                                                          ipAddress   : '127.0.0.1',
                                                          userAgent   : 'PHPUnit'
                                                      ));

        $this->assertTrue(condition: $result->isAuthenticated());
        $this->assertSame(expected: 'admin@acme.test', actual: $result->user()?->email);
        $this->assertNotEmpty(actual: $auth->readRiskSignals(userId: $result->user()?->id));
    }

    public function testFederationConnectionPolicyRejectsTenantCrossingAndInvalidMappings() : void
    {
        $auth       = $this->buildAuth();
        $connection = $auth->registerFederationConnection(data: new RegisterFederationConnectionData(
                                                                    tenantSlug  : 'acme',
                                                                    name        : 'Acme OIDC',
                                                                    provider    : FederationProvider::OIDC,
                                                                    domain      : 'acme.test',
                                                                    groupRoleMap: ['admins' => ['admin']],
                                                                    metadataUrl : 'https://idp.example.test/.well-known/openid-configuration'
                                                                ));

        $this->assertNotNull(actual: $connection->domainVerificationToken);

        try {
            $auth->registerFederationConnection(data: new RegisterFederationConnectionData(
                                                          tenantSlug  : 'other-tenant',
                                                          name        : 'Cross Tenant',
                                                          provider    : FederationProvider::OIDC,
                                                          domain      : 'acme.test',
                                                          groupRoleMap: ['admins' => ['admin']]
                                                      ));
            $this->fail(message: 'Expected duplicate domain registration to fail.');
        } catch (FederationFailed $exception) {
            $this->assertSame(expected: 'Federation domain is already registered for another tenant.', actual: $exception->getMessage());
        }

        try {
            $auth->registerFederationConnection(data: new RegisterFederationConnectionData(
                                                          tenantSlug  : 'invalid-map',
                                                          name        : 'Invalid Map',
                                                          provider    : FederationProvider::OIDC,
                                                          domain      : 'invalid.test',
                                                          groupRoleMap: ['admins' => ['super-admin']]
                                                      ));
            $this->fail(message: 'Expected invalid role mapping to fail.');
        } catch (FederationFailed $exception) {
            $this->assertSame(expected: 'Federation group-to-role mapping is invalid.', actual: $exception->getMessage());
        }
    }

    private function buildAuth() : Auth
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();

        return Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                                                                   userSource       : $userSource,
                                                                   codec            : new HmacTokenCodec(secret: 'federation-secret'),
                                                                   clock            : new Clock(),
                                                                   revocationStore  : new InMemoryTokenRevocationStore(),
                                                                   refreshTokenStore: $refreshTokens
                                                               )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->withAuditLog(auditLog: new InMemoryAuditLog())
            ->withFederationRuntime(federationRuntime: new FakeFederationRuntime())
            ->ready();
    }
}
