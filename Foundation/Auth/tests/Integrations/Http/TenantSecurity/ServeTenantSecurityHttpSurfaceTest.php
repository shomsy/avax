<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http\TenantSecurity;

use Avax\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Auth\Integrations\Http\TenantSecurity\ServeTenantSecurityHttpSurface;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
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

        $tenant = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants',
            body  : [
                'slug' => 'acme',
                'name' => 'Acme',
                'ownerUserId' => 1,
            ]
        ));
        $invite = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/invites',
            body  : [
                'email' => 'member@example.com',
                'role' => 'admin',
                'invitedBy' => 'owner@example.com',
            ]
        ));
        $accepted = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/invites/accept',
            body  : [
                'inviteToken' => $invite->body['plainTextToken'],
                'userId' => 2,
            ]
        ));
        $members = $surface->execute(input: new HttpEndpointInput(
            method: 'GET',
            path  : '/tenants/acme/members'
        ));
        $suspendedMember = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/members/2/suspend'
        ));
        $transferredOwner = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/transfer-owner',
            body  : ['newOwnerUserId' => 2]
        ));
        $oauthClient = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/oauth-clients',
            body  : [
                'name' => 'Acme App',
                'type' => 'confidential',
                'redirectUris' => ['https://app.acme.test/callback'],
                'allowedScopes' => ['openid', 'profile'],
                'allowedGrantTypes' => ['authorization_code', 'refresh_token'],
                'frontChannelLogoutSupported' => true,
                'backChannelLogoutSupported' => true,
            ]
        ));
        $oauthClientId = $oauthClient->body['client']['clientId'];
        $updatedOAuthClient = $surface->execute(input: new HttpEndpointInput(
            method: 'PUT',
            path  : '/tenants/acme/oauth-clients/' . rawurlencode($oauthClientId),
            body  : [
                'name' => 'Acme App Updated',
                'type' => 'confidential',
                'redirectUris' => ['https://app.acme.test/callback', 'https://app.acme.test/return'],
                'allowedScopes' => ['openid', 'profile', 'email'],
                'allowedGrantTypes' => ['authorization_code', 'refresh_token'],
                'frontChannelLogoutSupported' => true,
                'backChannelLogoutSupported' => false,
            ]
        ));
        $rotatedOAuthSecret = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/oauth-clients/' . rawurlencode($oauthClientId) . '/rotate-secret'
        ));
        $listedOAuthClients = $surface->execute(input: new HttpEndpointInput(
            method: 'GET',
            path  : '/tenants/acme/oauth-clients'
        ));
        $disabledOAuthClient = $surface->execute(input: new HttpEndpointInput(
            method: 'DELETE',
            path  : '/tenants/acme/oauth-clients/' . rawurlencode($oauthClientId)
        ));
        $pendingOAuthClient = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/oauth-clients',
            body  : [
                'name' => 'Acme Workload',
                'type' => 'confidential',
                'redirectUris' => ['urn:avax:oauth:acme-workload'],
                'allowedScopes' => ['orders.read'],
                'allowedGrantTypes' => ['client_credentials'],
                'allowedAudiences' => ['orders-api'],
                'workloadIdentity' => true,
                'requiredSenderConstraint' => 'mtls',
                'approvalRequired' => true,
            ]
        ));
        $pendingOAuthClientId = $pendingOAuthClient->body['client']['clientId'];
        $approvedOAuthClient = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/oauth-clients/' . rawurlencode($pendingOAuthClientId) . '/approve',
            body  : ['approvedBy' => 'approver']
        ));
        $removedFormerOwner = $surface->execute(input: new HttpEndpointInput(
            method: 'DELETE',
            path  : '/tenants/acme/members/1'
        ));

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
        $outage = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/scim-directories/' . rawurlencode($directoryId) . '/outage',
            body  : ['reason' => 'Maintenance']
        ));
        $recovered = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/security/scim-directories/' . rawurlencode($directoryId) . '/recover'
        ));

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

        $this->assertSame(expected: 201, actual: $tenant->statusCode);
        $this->assertSame(expected: 'acme', actual: $tenant->body['tenant']['slug']);
        $this->assertSame(expected: 201, actual: $invite->statusCode);
        $this->assertSame(expected: 200, actual: $accepted->statusCode);
        $this->assertCount(expectedCount: 2, haystack: $members->body['members']);
        $this->assertSame(expected: 'suspended', actual: $suspendedMember->body['member']['state']);
        $this->assertSame(expected: 2, actual: $transferredOwner->body['tenant']['ownerUserId']);
        $this->assertSame(expected: 201, actual: $oauthClient->statusCode);
        $this->assertNotNull(actual: $oauthClient->body['plainTextSecret']);
        $this->assertTrue(condition: $oauthClient->body['client']['frontChannelLogoutSupported']);
        $this->assertTrue(condition: $oauthClient->body['client']['backChannelLogoutSupported']);
        $this->assertSame(expected: 'Acme App Updated', actual: $updatedOAuthClient->body['client']['name']);
        $this->assertTrue(condition: $updatedOAuthClient->body['client']['frontChannelLogoutSupported']);
        $this->assertFalse(condition: $updatedOAuthClient->body['client']['backChannelLogoutSupported']);
        $this->assertNotNull(actual: $rotatedOAuthSecret->body['plainTextSecret']);
        $this->assertCount(expectedCount: 1, haystack: $listedOAuthClients->body['clients']);
        $this->assertFalse(condition: $disabledOAuthClient->body['client']['active']);
        $this->assertSame(expected: 'pending_approval', actual: $pendingOAuthClient->body['client']['approvalStatus']);
        $this->assertFalse(condition: $pendingOAuthClient->body['client']['active']);
        $this->assertSame(expected: 'approved', actual: $approvedOAuthClient->body['client']['approvalStatus']);
        $this->assertTrue(condition: $approvedOAuthClient->body['client']['active']);
        $this->assertSame(expected: 204, actual: $removedFormerOwner->statusCode);
        $this->assertSame(expected: 201, actual: $connection->statusCode);
        $this->assertSame(expected: 200, actual: $verified->statusCode);
        $this->assertNotNull(actual: $verified->body['connection']['domainVerifiedAt']);
        $this->assertSame(expected: 200, actual: $metadataSynced->statusCode);
        $this->assertSame(expected: 'healthy', actual: $health->body['health']);
        $this->assertSame(expected: 201, actual: $directory->statusCode);
        $this->assertArrayHasKey(key: 'plainTextToken', array: $directory->body);
        $this->assertSame(expected: 'unavailable', actual: $outage->body['directory']['health']);
        $this->assertSame(expected: 'healthy', actual: $recovered->body['directory']['health']);
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

    public function testTenantSecurityHttpSurfacePropagatesRequestObjectVerificationKey() : void
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        self::assertNotFalse(condition: $key);
        $details = openssl_pkey_get_details($key);
        self::assertIsArray(actual: $details);

        $rotatedKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        self::assertNotFalse(condition: $rotatedKey);
        $rotatedDetails = openssl_pkey_get_details($rotatedKey);
        self::assertIsArray(actual: $rotatedDetails);

        $surface = new ServeTenantSecurityHttpSurface(auth: $this->buildAuth());
        $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants',
            body  : [
                'slug' => 'acme',
                'name' => 'Acme',
                'ownerUserId' => 1,
            ]
        ));

        $created = $surface->execute(input: new HttpEndpointInput(
            method: 'POST',
            path  : '/tenants/acme/oauth-clients',
            body  : [
                'name' => 'Acme SPA',
                'type' => 'public',
                'redirectUris' => ['https://spa.acme.test/callback'],
                'allowedScopes' => ['openid'],
                'requestObjectSignatureRequired' => true,
                'requestObjectVerificationKeyPem' => $details['key'],
            ]
        ));

        $updated = $surface->execute(input: new HttpEndpointInput(
            method: 'PUT',
            path  : '/tenants/acme/oauth-clients/' . rawurlencode($created->body['client']['clientId']),
            body  : [
                'name' => 'Acme SPA',
                'type' => 'public',
                'redirectUris' => ['https://spa.acme.test/callback'],
                'allowedScopes' => ['openid'],
                'requestObjectSignatureRequired' => true,
                'requestObjectVerificationKeyPem' => $rotatedDetails['key'],
            ]
        ));

        $this->assertSame(expected: $details['key'], actual: $created->body['client']['requestObjectVerificationKeyPem']);
        $this->assertSame(expected: $rotatedDetails['key'], actual: $updated->body['client']['requestObjectVerificationKeyPem']);
    }

    private function buildAuth() : Auth
    {
        $userSource = new InMemoryUserSource();
        $userSource->create(user: User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'owner@example.com'),
            username    : 'owner',
            passwordHash: 'hash'
        ));
        $userSource->create(user: User::create(
            id          : new UserId(value: 2),
            email       : new UserEmail(value: 'member@example.com'),
            username    : 'member',
            passwordHash: 'hash'
        ));
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
