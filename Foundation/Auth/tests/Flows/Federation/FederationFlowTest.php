<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Federation;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capability\Federation\FederationProvider;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Federation\FederationFailed;
use Avax\Auth\System\Flow\Federation\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Auth\System\Flow\Federation\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Flow\Federation\StartFederatedLogin\StartFederatedLoginData;
use Avax\Auth\System\Flow\Federation\VerifyDomain\VerifyFederationDomainData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
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
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec('federation-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore($refreshTokens)
            ->withAuditLog($auditLog)
            ->withFederationRuntime($runtime)
            ->ready();

        $connection = $auth->registerFederationConnection(new RegisterFederationConnectionData(
            tenantSlug  : 'acme',
            name        : 'Acme OIDC',
            provider    : FederationProvider::OIDC,
            domain      : 'acme.test',
            groupRoleMap: ['admins' => ['admin']],
            metadataUrl : 'https://idp.example.test/.well-known/openid-configuration',
            ssoOnly     : true,
            breakGlassAllowed: true
        ));

        $this->assertNull($auth->discoverFederationConnection('user@acme.test'));

        $verified = $auth->verifyFederationDomain(new VerifyFederationDomainData(
            connectionId      : $connection->connectionId,
            verificationToken : $connection->domainVerificationToken ?? ''
        ));
        $this->assertTrue($verified->isDomainVerified());
        $this->assertSame($connection->connectionId, $auth->discoverFederationConnection('user@acme.test')?->connectionId);

        $synced = $auth->syncFederationMetadata($connection->connectionId);
        $this->assertNotNull($synced->metadataHash);
        $this->assertNotNull($synced->metadataIssuer);

        $this->assertSame(FederationConnectionHealth::HEALTHY, $auth->checkFederationConnectionHealth($connection->connectionId));
        $this->assertFalse($auth->evaluateFederationBreakGlassBypass($connection->connectionId));

        $runtime->health = FederationConnectionHealth::UNAVAILABLE;
        $this->assertSame(FederationConnectionHealth::UNAVAILABLE, $auth->checkFederationConnectionHealth($connection->connectionId));
        $this->assertTrue($auth->evaluateFederationBreakGlassBypass($connection->connectionId));
        $this->assertContains(
            'auth.federation.break_glass.denied',
            array_map(static fn ($event) => $event->name, $auditLog->events())
        );
        $this->assertContains(
            'auth.federation.break_glass.allowed',
            array_map(static fn ($event) => $event->name, $auditLog->events())
        );

        try {
            $auth->startFederatedLogin(new StartFederatedLoginData(
                connectionId: $connection->connectionId,
                redirectUri : 'https://app.example.test/callback',
                state       : 'state-break-glass'
            ));
            $this->fail('Expected unavailable federation connection to block SSO start.');
        } catch (FederationFailed $exception) {
            $this->assertSame('Federation connection is unavailable.', $exception->getMessage());
        }

        $runtime->health = FederationConnectionHealth::HEALTHY;
        $auth->checkFederationConnectionHealth($connection->connectionId);

        $started = $auth->startFederatedLogin(new StartFederatedLoginData(
            connectionId: $connection->connectionId,
            redirectUri : 'https://app.example.test/callback',
            state       : 'state-1'
        ));
        $this->assertStringContainsString($connection->connectionId, $started->redirectUrl);

        $result = $auth->completeFederatedLogin(new CompleteFederatedLoginData(
            connectionId: $connection->connectionId,
            payload     : [
                'subject' => 'external-123',
                'email' => 'admin@acme.test',
                'display_name' => 'Acme Admin',
                'groups' => ['admins'],
            ],
            ipAddress   : '127.0.0.1',
            userAgent   : 'PHPUnit'
        ));

        $this->assertTrue($result->isAuthenticated());
        $this->assertSame('admin@acme.test', $result->user()?->email);
        $this->assertNotEmpty($auth->readRiskSignals($result->user()?->id));
    }

    public function testFederationConnectionPolicyRejectsTenantCrossingAndInvalidMappings() : void
    {
        $auth = $this->buildAuth();
        $connection = $auth->registerFederationConnection(new RegisterFederationConnectionData(
            tenantSlug  : 'acme',
            name        : 'Acme OIDC',
            provider    : FederationProvider::OIDC,
            domain      : 'acme.test',
            groupRoleMap: ['admins' => ['admin']],
            metadataUrl : 'https://idp.example.test/.well-known/openid-configuration'
        ));

        $this->assertNotNull($connection->domainVerificationToken);

        try {
            $auth->registerFederationConnection(new RegisterFederationConnectionData(
                tenantSlug  : 'other-tenant',
                name        : 'Cross Tenant',
                provider    : FederationProvider::OIDC,
                domain      : 'acme.test',
                groupRoleMap: ['admins' => ['admin']]
            ));
            $this->fail('Expected duplicate domain registration to fail.');
        } catch (FederationFailed $exception) {
            $this->assertSame('Federation domain is already registered for another tenant.', $exception->getMessage());
        }

        try {
            $auth->registerFederationConnection(new RegisterFederationConnectionData(
                tenantSlug  : 'invalid-map',
                name        : 'Invalid Map',
                provider    : FederationProvider::OIDC,
                domain      : 'invalid.test',
                groupRoleMap: ['admins' => ['super-admin']]
            ));
            $this->fail('Expected invalid role mapping to fail.');
        } catch (FederationFailed $exception) {
            $this->assertSame('Federation group-to-role mapping is invalid.', $exception->getMessage());
        }
    }

    private function buildAuth() : Auth
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();

        return Auth::configuration()
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec('federation-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore($refreshTokens)
            ->withAuditLog(new InMemoryAuditLog())
            ->withFederationRuntime(new FakeFederationRuntime())
            ->ready();
    }
}
