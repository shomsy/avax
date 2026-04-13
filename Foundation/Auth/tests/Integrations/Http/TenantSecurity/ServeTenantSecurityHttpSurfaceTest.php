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
        $surface = new ServeTenantSecurityHttpSurface(auth: $this->buildAuth());

        $connection = $surface->execute(input: new HttpEndpointInput(
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

        $verified = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/federation-connections/' . rawurlencode($connectionId) . '/verify-domain',
            body  : ['verificationToken' => $verificationToken]
        ));
        $metadataSynced = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/federation-connections/' . rawurlencode($connectionId) . '/sync-metadata'
        ));
        $health = $surface->execute(input: new HttpEndpointInput(
            method: 'GET',
            path  : '/tenants/acme/security/federation-connections/' . rawurlencode($connectionId) . '/health'
        ));
        $directory = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/scim-directories',
            body  : [
                'name' => 'Acme Workforce',
                'groupRoleMap' => ['admins' => ['admin']],
            ]
        ));
        $directoryId = $directory->body['directory']['directoryId'];

        $change = $surface->execute(input: new HttpEndpointInput(
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
        $changes = $surface->execute(input: new HttpEndpointInput(
            method: 'GET',
            path  : '/tenants/acme/security/changes'
        ));
        $approved = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes/' . rawurlencode($changeId) . '/approve',
            body  : ['approvedBy' => 'approver']
        ));
        $applied = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes/' . rawurlencode($changeId) . '/apply'
        ));
        $summary = $surface->execute(input: new HttpEndpointInput(
            method: 'GET',
            path  : '/tenants/acme/security'
        ));
        $rolledBack = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes/' . rawurlencode($changeId) . '/rollback'
        ));

        $this->assertSame(expected: 201, actual: $connection->statusCode);
        $this->assertSame(expected: 200, actual: $verified->statusCode);
        $this->assertNotNull(actual: $verified->body['connection']['domainVerifiedAt']);
        $this->assertSame(expected: 200, actual: $metadataSynced->statusCode);
        $this->assertSame(expected: 'healthy', actual: $health->body['health']);
        $this->assertSame(expected: 201, actual: $directory->statusCode);
        $this->assertArrayHasKey(key: 'plainTextToken', array: $directory->body);
        $this->assertSame(expected: 201, actual: $change->statusCode);
        $this->assertCount(expectedCount: 1, haystack: $changes->body['changes']);
        $this->assertSame(expected: 'approved', actual: $approved->body['change']['status']);
        $this->assertSame(expected: $connectionId, actual: $applied->body['configuration']['federationConnectionId']);
        $this->assertSame(expected: $directoryId, actual: $summary->body['configuration']['scimDirectoryId']);
        $this->assertSame(expected: 1, actual: count($summary->body['federationConnections']));
        $this->assertNull(actual: $rolledBack->body['configuration']['federationConnectionId']);
    }

    public function testTenantSecurityHttpSurfaceRejectsApplyWithoutApproval() : void
    {
        $surface = new ServeTenantSecurityHttpSurface(auth: $this->buildAuth());
        $change = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes',
            body  : [
                'requestedBy' => 'security-admin',
                'reason' => 'Unapproved apply',
                'policyProfile' => 'tenant_admin',
            ]
        ));

        $response = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/changes/' . rawurlencode($change->body['change']['changeId']) . '/apply'
        ));

        $this->assertSame(expected: 422, actual: $response->statusCode);
        $this->assertSame(expected: 'tenant_security_failed', actual: $response->body['error']);
    }

    private function buildAuth() : Auth
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();

        return Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec(secret: 'tenant-http-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->withFederationRuntime(federationRuntime: new FakeFederationRuntime())
            ->ready();
    }
}
