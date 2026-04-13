<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http\TenantSecurity;

use Avax\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Auth\Integrations\Http\TenantSecurity\ServeTenantSecurityHttpSurface;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\Tests\Support\FakeFederationRuntime;
use PHPUnit\Framework\TestCase;

final class ServeTenantSecurityHttpSurfaceTest extends TestCase
{
    public function testTenantSecurityHttpSurfacePublishesAdminApi() : void
    {
        $surface = new ServeTenantSecurityHttpSurface($this->buildAuth());

        $connection = $surface->execute(new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/federation-connections',
            body  : [
                'name' => 'Acme OIDC',
                'provider' => 'oidc',
                'domain' => 'acme.test',
                'groupRoleMap' => ['admins' => ['admin']],
                'ssoOnly' => true,
                'breakGlassAllowed' => true,
                'metadataUrl' => 'https://idp.acme.test/metadata',
            ]
        ));
        $connectionId = $connection->body['connection']['connectionId'];
        $verificationToken = $connection->body['connection']['domainVerificationToken'];

        $verified = $surface->execute(new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/federation-connections/' . rawurlencode($connectionId) . '/verify-domain',
            body  : ['verificationToken' => $verificationToken]
        ));
        $metadataSynced = $surface->execute(new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/federation-connections/' . rawurlencode($connectionId) . '/sync-metadata'
        ));
        $health = $surface->execute(new HttpEndpointInput(
            method: 'GET',
            path  : '/tenants/acme/security/federation-connections/' . rawurlencode($connectionId) . '/health'
        ));
        $directory = $surface->execute(new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/scim-directories',
            body  : [
                'name' => 'Acme Workforce',
                'groupRoleMap' => ['admins' => ['admin']],
            ]
        ));
        $directoryId = $directory->body['directory']['directoryId'];

        $change = $surface->execute(new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes',
            body  : [
                'requestedBy' => 'security-admin',
                'reason' => 'Tenant rollout',
                'federationConnectionId' => $connectionId,
                'scimDirectoryId' => $directoryId,
                'verifiedDomains' => ['acme.test'],
                'groupRoleMap' => ['admins' => ['admin']],
                'policyProfile' => 'tenant_admin',
            ]
        ));
        $changeId = $change->body['change']['changeId'];
        $changes = $surface->execute(new HttpEndpointInput(
            method: 'GET',
            path  : '/tenants/acme/security/changes'
        ));
        $approved = $surface->execute(new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes/' . rawurlencode($changeId) . '/approve',
            body  : ['approvedBy' => 'approver']
        ));
        $applied = $surface->execute(new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes/' . rawurlencode($changeId) . '/apply'
        ));
        $summary = $surface->execute(new HttpEndpointInput(
            method: 'GET',
            path  : '/tenants/acme/security'
        ));
        $rolledBack = $surface->execute(new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes/' . rawurlencode($changeId) . '/rollback'
        ));

        $this->assertSame(201, $connection->statusCode);
        $this->assertSame(200, $verified->statusCode);
        $this->assertNotNull($verified->body['connection']['domainVerifiedAt']);
        $this->assertSame(200, $metadataSynced->statusCode);
        $this->assertSame('healthy', $health->body['health']);
        $this->assertSame(201, $directory->statusCode);
        $this->assertArrayHasKey('plainTextToken', $directory->body);
        $this->assertSame(201, $change->statusCode);
        $this->assertCount(1, $changes->body['changes']);
        $this->assertSame('approved', $approved->body['change']['status']);
        $this->assertSame($connectionId, $applied->body['configuration']['federationConnectionId']);
        $this->assertSame($directoryId, $summary->body['configuration']['scimDirectoryId']);
        $this->assertSame(1, count($summary->body['federationConnections']));
        $this->assertNull($rolledBack->body['configuration']['federationConnectionId']);
    }

    public function testTenantSecurityHttpSurfaceRejectsApplyWithoutApproval() : void
    {
        $surface = new ServeTenantSecurityHttpSurface($this->buildAuth());
        $change = $surface->execute(new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes',
            body  : [
                'requestedBy' => 'security-admin',
                'reason' => 'Unapproved apply',
                'policyProfile' => 'tenant_admin',
            ]
        ));

        $response = $surface->execute(new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes/' . rawurlencode($change->body['change']['changeId']) . '/apply'
        ));

        $this->assertSame(422, $response->statusCode);
        $this->assertSame('tenant_security_failed', $response->body['error']);
    }

    private function buildAuth() : Auth
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();

        return Auth::configuration()
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec('tenant-http-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore($refreshTokens)
            ->withFederationRuntime(new FakeFederationRuntime())
            ->ready();
    }
}
