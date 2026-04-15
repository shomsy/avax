<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\TenantSecurity;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Federation\FederationProvider;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStatus;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Federation\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Auth\System\Flow\TenantSecurity\TenantSecurityFailed;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\Tests\Support\FakeFederationRuntime;
use PHPUnit\Framework\TestCase;

final class TenantSecurityFlowTest extends TestCase
{
    public function testTenantSecurityChangeApprovalApplyAndRollback() : void
    {
        $auth       = $this->buildAuth();
        $connection = $auth->registerFederationConnection(data: new RegisterFederationConnectionData(
                                                                    tenantSlug  : 'acme',
                                                                    name        : 'Acme OIDC',
                                                                    provider    : FederationProvider::OIDC,
                                                                    domain      : 'acme.test',
                                                                    groupRoleMap: ['admins' => ['admin']]
                                                                ));
        $directory  = $auth->registerScimDirectory(data: new RegisterScimDirectoryData(
                                                             tenantSlug  : 'acme',
                                                             name        : 'Acme Workforce',
                                                             groupRoleMap: ['admins' => ['admin']]
                                                         ));

        $change = $auth->beginTenantSecurityChange(data: new BeginTenantSecurityChangeData(
                                                             tenantSlug : 'acme',
                                                             requestedBy: 'security-admin',
                                                             reason     : 'Turn on tenant SSO posture',
                                                             after      : new TenantSecurityConfiguration(
                                                                              tenantSlug            : 'acme',
                                                                              federationConnectionId: $connection->connectionId,
                                                                              scimDirectoryId       : $directory->directory->directoryId,
                                                                              verifiedDomains       : ['acme.test'],
                                                                              groupRoleMap          : ['admins' => ['admin']],
                                                                              policyProfile         : 'tenant_admin'
                                                                          )
                                                         ));

        $this->assertSame(expected: TenantSecurityChangeRequestStatus::PENDING_APPROVAL, actual: $change->status);
        $this->assertArrayHasKey(key: 'federation_connection_id', array: $change->diff);

        $approved = $auth->approveTenantSecurityChange(changeId: $change->changeId, approvedBy: 'approver');
        $this->assertSame(expected: TenantSecurityChangeRequestStatus::APPROVED, actual: $approved->status);

        $applied    = $auth->applyTenantSecurityChange(changeId: $change->changeId);
        $stored     = $auth->readTenantSecurityConfiguration(tenantSlug: 'acme');
        $rolledBack = $auth->rollbackTenantSecurityChange(changeId: $change->changeId);

        $this->assertSame(expected: $connection->connectionId, actual: $applied->federationConnectionId);
        $this->assertSame(expected: $applied->federationConnectionId, actual: $stored?->federationConnectionId);
        $this->assertNull(actual: $rolledBack->federationConnectionId);
    }

    private function buildAuth() : Auth
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();

        return Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                                                                   userSource       : $userSource,
                                                                   codec            : new HmacTokenCodec(secret: 'tenant-auth-secret'),
                                                                   clock            : new Clock(),
                                                                   revocationStore  : new InMemoryTokenRevocationStore(),
                                                                   refreshTokenStore: $refreshTokens
                                                               )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->withFederationRuntime(federationRuntime: new FakeFederationRuntime())
            ->ready();
    }

    public function testTenantSecurityRejectsUnknownReferencedResources() : void
    {
        $auth = $this->buildAuth();

        $this->expectException(TenantSecurityFailed::class);
        $this->expectExceptionMessage('Tenant security references an unknown federation connection.');

        $auth->beginTenantSecurityChange(data: new BeginTenantSecurityChangeData(
                                                   tenantSlug : 'acme',
                                                   requestedBy: 'security-admin',
                                                   reason     : 'Invalid reference',
                                                   after      : new TenantSecurityConfiguration(
                                                                    tenantSlug            : 'acme',
                                                                    federationConnectionId: 'missing-fed'
                                                                )
                                               ));
    }

    public function testTenantSecurityApplyRequiresApproval() : void
    {
        $auth       = $this->buildAuth();
        $connection = $auth->registerFederationConnection(data: new RegisterFederationConnectionData(
                                                                    tenantSlug  : 'acme',
                                                                    name        : 'Acme OIDC',
                                                                    provider    : FederationProvider::OIDC,
                                                                    domain      : 'acme.test',
                                                                    groupRoleMap: ['admins' => ['admin']]
                                                                ));

        $change = $auth->beginTenantSecurityChange(data: new BeginTenantSecurityChangeData(
                                                             tenantSlug : 'acme',
                                                             requestedBy: 'security-admin',
                                                             reason     : 'Attempt unapproved apply',
                                                             after      : new TenantSecurityConfiguration(
                                                                              tenantSlug            : 'acme',
                                                                              federationConnectionId: $connection->connectionId
                                                                          )
                                                         ));

        $this->expectException(TenantSecurityFailed::class);
        $this->expectExceptionMessage('Tenant security approval is required before apply.');

        $auth->applyTenantSecurityChange(changeId: $change->changeId);
    }
}
