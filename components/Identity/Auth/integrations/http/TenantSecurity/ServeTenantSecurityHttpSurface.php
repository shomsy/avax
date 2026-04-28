<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Http\TenantSecurity;

use Avax\Components\Identity\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Components\Identity\Auth\Integrations\Http\JsonHttpResponse;
use Avax\Components\Identity\Auth\System\Auth;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClientData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientType;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthGrantType;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthTokenEndpointAuthMethod;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnectionData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomainData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationProvider;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutageData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\Tenant;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantInvite;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantMemberRole;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant\CreateTenantData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;
use InvalidArgumentException;
use SensitiveParameter;
use Throwable;

/**
 * Publishes a framework-neutral tenant-admin API over tenant security, federation, and SCIM flows.
 */
final readonly class ServeTenantSecurityHttpSurface
{
    public function __construct(
        #[SensitiveParameter] private Auth $auth
    ) {}

    public function execute(HttpEndpointInput $input) : JsonHttpResponse
    {
        $method     = strtoupper(string: trim(string: $input->method));
        $path       = $this->normalizePath(path: $input->path);
        $tenantSlug = $this->tenantSlug(input: $input);

        try {
            if ($method === 'GET' && $path === '/tenants') {
                return $this->response(statusCode: 200, body: [
                    'tenants' => array_map(callback: $this->tenantResource(...), array: $this->auth->readTenants()),
                ]);
            }

            if ($method === 'POST' && $path === '/tenants') {
                $tenant = $this->auth->createTenant(data: new CreateTenantData(
                                                              slug       : $this->requiredString(body: $input->body, field: 'slug'),
                                                              name       : $this->requiredString(body: $input->body, field: 'name'),
                                                              ownerUserId: $this->requiredInt(body: $input->body, field: 'ownerUserId')
                                                          ));

                return $this->response(statusCode: 201, body: ['tenant' => $this->tenantResource(tenant: $tenant)]);
            }

            if ($method === 'GET' && $path === '/tenants/' . $tenantSlug . '/members') {
                return $this->response(statusCode: 200, body: [
                    'tenant'  => $tenantSlug,
                    'members' => array_map(callback: $this->memberResource(...), array: $this->auth->readTenantMembers(tenantSlug: $tenantSlug)),
                ]);
            }

            if ($method === 'POST' && $path === '/tenants/' . $tenantSlug . '/invites') {
                $invite = $this->auth->inviteTenantMember(data: new InviteTenantMemberData(
                                                                    tenantSlug: $tenantSlug,
                                                                    email     : $this->requiredString(body: $input->body, field: 'email'),
                                                                    role      : TenantMemberRole::from(value: strtolower(string: $this->requiredString(body: $input->body, field: 'role'))),
                                                                    invitedBy : $this->requiredString(body: $input->body, field: 'invitedBy')
                                                                ));

                return $this->response(statusCode: 201, body: [
                    'invite'         => $this->inviteResource(invite: $invite->invite),
                    'plainTextToken' => $invite->plainTextToken,
                ]);
            }

            if ($method === 'POST' && $path === '/tenants/' . $tenantSlug . '/invites/accept') {
                $member = $this->auth->acceptTenantInvite(data: new AcceptTenantInviteData(
                                                                    inviteToken: $this->requiredString(body: $input->body, field: 'inviteToken'),
                                                                    userId     : $this->requiredInt(body: $input->body, field: 'userId')
                                                                ));

                return $this->response(statusCode: 200, body: ['member' => $this->memberResource(member: $member)]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/members/([0-9]+)/suspend$~', subject: $path, matches: $matches) === 1) {
                $member = $this->auth->suspendTenantMember(data: new SuspendTenantMemberData(
                                                                     tenantSlug: $tenantSlug,
                                                                     userId    : (int) $matches[1]
                                                                 ));

                return $this->response(statusCode: 200, body: ['member' => $this->memberResource(member: $member)]);
            }

            if ($method === 'DELETE' && preg_match(pattern: '~^/tenants/[^/]+/members/([0-9]+)$~', subject: $path, matches: $matches) === 1) {
                $this->auth->removeTenantMember(data: new RemoveTenantMemberData(
                                                          tenantSlug: $tenantSlug,
                                                          userId    : (int) $matches[1]
                                                      ));

                return $this->response(statusCode: 204, body: []);
            }

            if ($method === 'POST' && $path === '/tenants/' . $tenantSlug . '/transfer-owner') {
                $tenant = $this->auth->transferTenantOwnership(data: new TransferTenantOwnershipData(
                                                                         tenantSlug    : $tenantSlug,
                                                                         newOwnerUserId: $this->requiredInt(body: $input->body, field: 'newOwnerUserId')
                                                                     ));

                return $this->response(statusCode: 200, body: ['tenant' => $this->tenantResource(tenant: $tenant)]);
            }

            if ($method === 'GET' && $path === '/tenants/' . $tenantSlug . '/oauth-clients') {
                return $this->response(statusCode: 200, body: [
                    'clients' => array_map(
                        callback: $this->oauthClientResource(...),
                        array   : $this->tenantOAuthClients(tenantSlug: $tenantSlug)
                    ),
                ]);
            }

            if ($method === 'POST' && $path === '/tenants/' . $tenantSlug . '/oauth-clients') {
                $client = $this->auth->registerOAuthClient(data: new RegisterClientData(
                                                                     name                           : $this->requiredString(body: $input->body, field: 'name'),
                                                                     type                           : OAuthClientType::from(value: strtolower(string: $this->requiredString(body: $input->body, field: 'type'))),
                                                                     redirectUris                   : $this->stringList(values: $input->body['redirectUris'] ?? []),
                                                                     tenantSlug                     : $tenantSlug,
                                                                     allowedScopes                  : $this->stringList(values: $input->body['allowedScopes'] ?? []),
                                                                     allowedAudiences               : $this->stringList(values: $input->body['allowedAudiences'] ?? []),
                                                                     allowedGrantTypes              : $this->grantTypes(values: $input->body['allowedGrantTypes'] ?? []),
                                                                     audienceScopeBoundaries        : $this->audienceScopeBoundaries(body: $input->body),
                                                                     tokenEndpointAuthMethod        : $this->tokenEndpointAuthMethod(body: $input->body),
                                                                     requiredSenderConstraint       : $this->senderConstraintType(body: $input->body),
                                                                     workloadIdentity               : $this->boolValue(body: $input->body, field: 'workloadIdentity'),
                                                                     phishingResistantRequired      : $this->boolValue(body: $input->body, field: 'phishingResistantRequired'),
                                                                     requestObjectSignatureRequired : $this->boolValue(body: $input->body, field: 'requestObjectSignatureRequired'),
                                                                     frontChannelLogoutSupported    : $this->boolValue(body: $input->body, field: 'frontChannelLogoutSupported'),
                                                                     backChannelLogoutSupported     : $this->boolValue(body: $input->body, field: 'backChannelLogoutSupported'),
                                                                     approvalRequired               : $this->boolValue(body: $input->body, field: 'approvalRequired'),
                                                                     requestObjectVerificationKeyPem: $this->nullableMultilineString(body: $input->body)
                                                                 ));

                return $this->response(statusCode: 201, body: [
                    'client'          => $this->oauthClientResource(client: $client->client),
                    'plainTextSecret' => $client->plainTextSecret,
                ]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/oauth-clients/([^/]+)/approve$~', subject: $path, matches: $matches) === 1) {
                $client = $this->auth->approveOAuthClientRegistration(data: new ApproveClientRegistrationData(
                                                                                clientId  : urldecode(string: $matches[1]),
                                                                                approvedBy: $this->requiredString(body: $input->body, field: 'approvedBy')
                                                                            ));

                return $this->response(statusCode: 200, body: ['client' => $this->oauthClientResource(client: $client)]);
            }

            if ($method === 'PUT' && preg_match(pattern: '~^/tenants/[^/]+/oauth-clients/([^/]+)$~', subject: $path, matches: $matches) === 1) {
                $client = $this->auth->updateOAuthClient(data: new UpdateClientData(
                                                                   clientId                       : urldecode(string: $matches[1]),
                                                                   name                           : $this->requiredString(body: $input->body, field: 'name'),
                                                                   type                           : OAuthClientType::from(value: strtolower(string: $this->requiredString(body: $input->body, field: 'type'))),
                                                                   redirectUris                   : $this->stringList(values: $input->body['redirectUris'] ?? []),
                                                                   tenantSlug                     : $tenantSlug,
                                                                   allowedScopes                  : $this->stringList(values: $input->body['allowedScopes'] ?? []),
                                                                   allowedAudiences               : $this->stringList(values: $input->body['allowedAudiences'] ?? []),
                                                                   allowedGrantTypes              : $this->grantTypes(values: $input->body['allowedGrantTypes'] ?? []),
                                                                   audienceScopeBoundaries        : $this->audienceScopeBoundaries(body: $input->body),
                                                                   tokenEndpointAuthMethod        : $this->tokenEndpointAuthMethod(body: $input->body),
                                                                   requiredSenderConstraint       : $this->senderConstraintType(body: $input->body),
                                                                   workloadIdentity               : $this->boolValue(body: $input->body, field: 'workloadIdentity'),
                                                                   phishingResistantRequired      : $this->boolValue(body: $input->body, field: 'phishingResistantRequired'),
                                                                   requestObjectSignatureRequired : $this->boolValue(body: $input->body, field: 'requestObjectSignatureRequired'),
                                                                   frontChannelLogoutSupported    : $this->boolValue(body: $input->body, field: 'frontChannelLogoutSupported'),
                                                                   backChannelLogoutSupported     : $this->boolValue(body: $input->body, field: 'backChannelLogoutSupported'),
                                                                   approvalRequired               : $this->boolValue(body: $input->body, field: 'approvalRequired'),
                                                                   requestObjectVerificationKeyPem: $this->nullableMultilineString(body: $input->body)
                                                               ));

                return $this->response(statusCode: 200, body: ['client' => $this->oauthClientResource(client: $client)]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/oauth-clients/([^/]+)/rotate-secret$~', subject: $path, matches: $matches) === 1) {
                $client = $this->auth->rotateOAuthClientSecret(clientId: urldecode(string: $matches[1]));

                return $this->response(statusCode: 200, body: [
                    'client'          => $this->oauthClientResource(client: $client->client),
                    'plainTextSecret' => $client->plainTextSecret,
                ]);
            }

            if ($method === 'DELETE' && preg_match(pattern: '~^/tenants/[^/]+/oauth-clients/([^/]+)$~', subject: $path, matches: $matches) === 1) {
                $client = $this->auth->disableOAuthClient(clientId: urldecode(string: $matches[1]));

                return $this->response(statusCode: 200, body: ['client' => $this->oauthClientResource(client: $client)]);
            }

            if ($method === 'GET' && $path === '/tenants/' . $tenantSlug . '/security') {
                return $this->summary(tenantSlug: $tenantSlug);
            }

            if ($method === 'GET' && $path === '/tenants/' . $tenantSlug . '/security/changes') {
                return $this->response(statusCode: 200, body: [
                    'tenant'  => $tenantSlug,
                    'changes' => array_map(callback: $this->changeResource(...), array: $this->auth->readTenantSecurityChangeRequests(tenantSlug: $tenantSlug)),
                ]);
            }

            if ($method === 'GET' && preg_match(pattern: '~^/tenants/[^/]+/security/changes/([^/]+)$~', subject: $path, matches: $matches) === 1) {
                $change = $this->auth->readTenantSecurityChangeRequest(changeId: urldecode(string: $matches[1]));

                return $change === null
                    ? $this->error(statusCode: 404, errorCode: 'not_found', message: 'Tenant security change request was not found.')
                    : $this->response(statusCode: 200, body: ['change' => $this->changeResource(change: $change)]);
            }

            if ($method === 'POST' && $path === '/tenants/' . $tenantSlug . '/security/changes') {
                $change = $this->auth->beginTenantSecurityChange(data: new BeginTenantSecurityChangeData(
                                                                           tenantSlug : $tenantSlug,
                                                                           requestedBy: $this->requiredString(body: $input->body, field: 'requestedBy'),
                                                                           reason     : $this->requiredString(body: $input->body, field: 'reason'),
                                                                           after      : $this->configurationFromBody(tenantSlug: $tenantSlug, body: $input->body)
                                                                       ));

                return $this->response(statusCode: 201, body: ['change' => $this->changeResource(change: $change)]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/security/changes/([^/]+)/approve$~', subject: $path, matches: $matches) === 1) {
                $change = $this->auth->approveTenantSecurityChange(
                    changeId  : urldecode(string: $matches[1]),
                    approvedBy: $this->requiredString(body: $input->body, field: 'approvedBy')
                );

                return $this->response(statusCode: 200, body: ['change' => $this->changeResource(change: $change)]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/security/changes/([^/]+)/apply$~', subject: $path, matches: $matches) === 1) {
                $configuration = $this->auth->applyTenantSecurityChange(changeId: urldecode(string: $matches[1]));

                return $this->response(statusCode: 200, body: ['configuration' => $this->configurationResource(configuration: $configuration)]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/security/changes/([^/]+)/rollback$~', subject: $path, matches: $matches) === 1) {
                $configuration = $this->auth->rollbackTenantSecurityChange(changeId: urldecode(string: $matches[1]));

                return $this->response(statusCode: 200, body: ['configuration' => $this->configurationResource(configuration: $configuration)]);
            }

            if ($method === 'GET' && $path === '/tenants/' . $tenantSlug . '/security/federation-connections') {
                return $this->response(statusCode: 200, body: [
                    'connections' => array_map(
                        callback: $this->connectionResource(...),
                        array   : $this->tenantConnections(tenantSlug: $tenantSlug)
                    ),
                ]);
            }

            if ($method === 'POST' && $path === '/tenants/' . $tenantSlug . '/security/federation-connections') {
                $connection = $this->auth->registerFederationConnection(data: new RegisterFederationConnectionData(
                                                                                  tenantSlug       : $tenantSlug,
                                                                                  name             : $this->requiredString(body: $input->body, field: 'name'),
                                                                                  provider         : FederationProvider::from(value: strtolower(string: $this->requiredString(body: $input->body, field: 'provider'))),
                                                                                  domain           : $this->requiredString(body: $input->body, field: 'domain'),
                                                                                  ssoOnly          : $this->boolValue(body: $input->body, field: 'ssoOnly'),
                                                                                  groupRoleMap     : $this->groupRoleMap(body: $input->body),
                                                                                  metadataUrl      : $this->nullableString(body: $input->body, field: 'metadataUrl'),
                                                                                  breakGlassAllowed: $this->boolValue(body: $input->body, field: 'breakGlassAllowed')
                                                                              ));

                return $this->response(statusCode: 201, body: ['connection' => $this->connectionResource(connection: $connection)]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/security/federation-connections/([^/]+)/verify-domain$~', subject: $path, matches: $matches) === 1) {
                $connection = $this->auth->verifyFederationDomain(data: new VerifyFederationDomainData(
                                                                            connectionId     : urldecode(string: $matches[1]),
                                                                            verificationToken: $this->requiredString(body: $input->body, field: 'verificationToken')
                                                                        ));

                return $this->response(statusCode: 200, body: ['connection' => $this->connectionResource(connection: $connection)]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/security/federation-connections/([^/]+)/sync-metadata$~', subject: $path, matches: $matches) === 1) {
                $connection = $this->auth->syncFederationMetadata(connectionId: urldecode(string: $matches[1]));

                return $this->response(statusCode: 200, body: ['connection' => $this->connectionResource(connection: $connection)]);
            }

            if ($method === 'GET' && preg_match(pattern: '~^/tenants/[^/]+/security/federation-connections/([^/]+)/health$~', subject: $path, matches: $matches) === 1) {
                $health = $this->auth->checkFederationConnectionHealth(connectionId: urldecode(string: $matches[1]));

                return $this->response(statusCode: 200, body: ['health' => $health->value]);
            }

            if ($method === 'GET' && $path === '/tenants/' . $tenantSlug . '/security/scim-directories') {
                return $this->response(statusCode: 200, body: [
                    'directories' => array_map(
                        callback: $this->directoryResource(...),
                        array   : $this->auth->readScimDirectories(tenantSlug: $tenantSlug)
                    ),
                ]);
            }

            if ($method === 'POST' && $path === '/tenants/' . $tenantSlug . '/security/scim-directories') {
                $directory = $this->auth->registerScimDirectory(data: new RegisterScimDirectoryData(
                                                                          tenantSlug  : $tenantSlug,
                                                                          name        : $this->requiredString(body: $input->body, field: 'name'),
                                                                          groupRoleMap: $this->groupRoleMap(body: $input->body)
                                                                      ));

                return $this->response(statusCode: 201, body: [
                    'directory'      => $this->directoryResource(directory: $directory->directory),
                    'plainTextToken' => $directory->plainTextToken,
                ]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/security/scim-directories/([^/]+)/rotate-token$~', subject: $path, matches: $matches) === 1) {
                $rotated = $this->auth->rotateScimToken(directoryId: urldecode(string: $matches[1]));

                return $this->response(statusCode: 200, body: [
                    'directory'      => $this->directoryResource(directory: $rotated->directory),
                    'plainTextToken' => $rotated->plainTextToken,
                ]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/security/scim-directories/([^/]+)/outage$~', subject: $path, matches: $matches) === 1) {
                $directory = $this->tenantScimDirectory(tenantSlug: $tenantSlug, directoryId: urldecode(string: $matches[1]));
                $updated   = $this->auth->markScimDirectoryOutage(data: new MarkScimDirectoryOutageData(
                                                                            directoryId: $directory->directoryId,
                                                                            reason     : $this->nullableString(body: $input->body, field: 'reason')
                                                                        ));

                return $this->response(statusCode: 200, body: ['directory' => $this->directoryResource(directory: $updated)]);
            }

            if ($method === 'POST' && preg_match(pattern: '~^/tenants/[^/]+/security/scim-directories/([^/]+)/recover$~', subject: $path, matches: $matches) === 1) {
                $directory = $this->tenantScimDirectory(tenantSlug: $tenantSlug, directoryId: urldecode(string: $matches[1]));
                $updated   = $this->auth->recoverScimDirectoryOutage(data: new RecoverScimDirectoryOutageData(
                                                                               directoryId: $directory->directoryId
                                                                           ));

                return $this->response(statusCode: 200, body: ['directory' => $this->directoryResource(directory: $updated)]);
            }
        } catch (Throwable $failure) {
            $message = strtolower(string: $failure->getMessage());
            $status  = str_contains(haystack: $message, needle: 'not found') || str_contains(haystack: $message, needle: 'unknown')
                ? 404
                : 422;

            return $this->error(statusCode: $status, errorCode: 'tenant_security_failed', message: $failure->getMessage());
        }

        return $this->error(statusCode: 404, errorCode: 'not_found', message: 'Tenant security route was not found.');
    }

    private function normalizePath(string $path) : string
    {
        $trimmed = trim(string: $path);

        if ($trimmed === '' || $trimmed === '/') {
            return '/';
        }

        return '/' . trim(string: $trimmed, characters: '/');
    }

    private function tenantSlug(HttpEndpointInput $input) : string
    {
        $routeTenantSlug = $input->routeParameters['tenantSlug'] ?? null;

        if (is_scalar(value: $routeTenantSlug) && trim(string: (string) $routeTenantSlug) !== '') {
            return trim(string: (string) $routeTenantSlug);
        }

        if (preg_match(pattern: '~^/tenants/([^/]+)(?:/.*)?$~', subject: $this->normalizePath(path: $input->path), matches: $matches) === 1) {
            return urldecode(string: $matches[1]);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $body
     */
    private function response(int $statusCode, array $body) : JsonHttpResponse
    {
        return new JsonHttpResponse(
            statusCode: $statusCode,
            body      : $body,
            headers   : ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param array<string, mixed> $body
     */
    private function requiredString(array $body, string $field) : string
    {
        $value = $this->nullableString(body: $body, field: $field);

        if ($value === null || $value === '') {
            throw new InvalidArgumentException(message: "{$field} is required.");
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function nullableString(array $body, string $field) : string|null
    {
        $value = $body[$field] ?? null;

        return is_scalar(value: $value) ? trim(string: (string) $value) : null;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function requiredInt(array $body, string $field) : int
    {
        $value = $this->intValue(body: $body, field: $field);

        if ($value === null) {
            throw new InvalidArgumentException(message: "{$field} must be an integer.");
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function intValue(array $body, string $field) : int|null
    {
        $value = $body[$field] ?? null;

        if (is_int(value: $value)) {
            return $value;
        }

        if (is_string(value: $value) && ctype_digit(text: $value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function tenantResource(Tenant $tenant) : array
    {
        return [
            'tenantId'    => $tenant->tenantId,
            'slug'        => $tenant->slug,
            'name'        => $tenant->name,
            'ownerUserId' => $tenant->ownerUserId,
            'createdAt'   => $tenant->createdAt->format(format: DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inviteResource(TenantInvite $invite) : array
    {
        return [
            'inviteId'   => $invite->inviteId,
            'tenantId'   => $invite->tenantId,
            'email'      => $invite->email,
            'role'       => $invite->role->value,
            'invitedBy'  => $invite->invitedBy,
            'createdAt'  => $invite->createdAt->format(format: DATE_ATOM),
            'acceptedAt' => $invite->acceptedAt?->format(format: DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function memberResource(TenantMember $member) : array
    {
        return [
            'tenantId' => $member->tenantId,
            'userId'   => $member->userId,
            'role'     => $member->role->value,
            'state'    => $member->state->value,
            'joinedAt' => $member->joinedAt->format(format: DATE_ATOM),
        ];
    }

    /**
     * @return list<OAuthClient>
     */
    private function tenantOAuthClients(string $tenantSlug) : array
    {
        return array_values(array: array_filter(
                                       array   : $this->auth->readOAuthClients(),
                                       callback: static fn (OAuthClient $client) : bool => $client->tenantSlug === $tenantSlug
                                   ));
    }

    /**
     * @param array<int|string, mixed> $values
     *
     * @return list<string>
     */
    private function stringList(array $values) : array
    {
        $resolved = [];

        foreach ($values as $value) {
            if (! is_scalar(value: $value)) {
                continue;
            }

            $trimmed = trim(string: (string) $value);

            if ($trimmed !== '') {
                $resolved[] = $trimmed;
            }
        }

        return $resolved;
    }

    /**
     * @param array<int|string, mixed> $values
     *
     * @return list<OAuthGrantType>
     */
    private function grantTypes(array $values) : array
    {
        $resolved = [];

        foreach ($this->stringList(values: $values) as $value) {
            $grantType = OAuthGrantType::tryFrom(value: $value);

            if ($grantType !== null && ! in_array(needle: $grantType, haystack: $resolved, strict: true)) {
                $resolved[] = $grantType;
            }
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, list<string>>
     */
    private function audienceScopeBoundaries(array $body) : array
    {
        $boundaries = $body['audienceScopeBoundaries'] ?? [];

        if (! is_array(value: $boundaries)) {
            return [];
        }

        $resolved = [];

        foreach ($boundaries as $audience => $scopes) {
            if (! is_string(value: $audience) || trim(string: $audience) === '' || ! is_array(value: $scopes)) {
                continue;
            }

            $resolved[trim(string: $audience)] = $this->stringList(values: $scopes);
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function tokenEndpointAuthMethod(array $body) : OAuthTokenEndpointAuthMethod|null
    {
        $value = $this->nullableString(body: $body, field: 'tokenEndpointAuthMethod');

        return $value !== null && $value !== ''
            ? OAuthTokenEndpointAuthMethod::from(value: strtolower(string: $value))
            : null;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function senderConstraintType(array $body) : OAuthSenderConstraintType|null
    {
        $value = $this->nullableString(body: $body, field: 'requiredSenderConstraint');

        return $value !== null && $value !== ''
            ? OAuthSenderConstraintType::from(value: strtolower(string: $value))
            : null;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function boolValue(array $body, string $field) : bool
    {
        $value = $body[$field] ?? null;

        if (is_bool(value: $value)) {
            return $value;
        }

        if (is_string(value: $value)) {
            return in_array(needle: strtolower(string: trim(string: $value)), haystack: ['1', 'true', 'yes', 'on'], strict: true);
        }

        return false;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function nullableMultilineString(array $body) : string|null
    {
        $value = $body['requestObjectVerificationKeyPem'] ?? null;

        return is_scalar(value: $value) ? (string) $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function oauthClientResource(OAuthClient $client) : array
    {
        return [
            'clientId'                        => $client->clientId,
            'tenantSlug'                      => $client->tenantSlug,
            'name'                            => $client->name,
            'type'                            => $client->type->value,
            'redirectUris'                    => $client->redirectUris,
            'allowedScopes'                   => $client->allowedScopes,
            'allowedAudiences'                => $client->allowedAudiences,
            'allowedGrantTypes'               => array_map(callback: static fn (OAuthGrantType $grantType) : string => $grantType->value, array: $client->allowedGrantTypes),
            'audienceScopeBoundaries'         => $client->audienceScopeBoundaries,
            'tokenEndpointAuthMethod'         => $client->tokenEndpointAuthMethod->value,
            'requiredSenderConstraint'        => $client->requiredSenderConstraint?->value,
            'workloadIdentity'                => $client->workloadIdentity,
            'phishingResistantRequired'       => $client->phishingResistantRequired,
            'requestObjectSignatureRequired'  => $client->requestObjectSignatureRequired,
            'requestObjectVerificationKeyPem' => $client->requestObjectVerificationKeyPem,
            'frontChannelLogoutSupported'     => $client->frontChannelLogoutSupported,
            'backChannelLogoutSupported'      => $client->backChannelLogoutSupported,
            'approvalStatus'                  => $client->approvalStatus->value,
            'approvedAt'                      => $client->approvedAt?->format(format: DATE_ATOM),
            'approvedBy'                      => $client->approvedBy,
            'active'                          => $client->active,
        ];
    }

    private function summary(string $tenantSlug) : JsonHttpResponse
    {
        return $this->response(statusCode: 200, body: [
            'tenant'                => $tenantSlug,
            'configuration'         => $this->configurationResource(configuration: $this->auth->readTenantSecurityConfiguration(tenantSlug: $tenantSlug)),
            'federationConnections' => array_map(
                callback: $this->connectionResource(...),
                array   : $this->tenantConnections(tenantSlug: $tenantSlug)
            ),
            'scimDirectories'       => array_map(
                callback: $this->directoryResource(...),
                array   : $this->auth->readScimDirectories(tenantSlug: $tenantSlug)
            ),
            'changes'               => array_map(
                callback: $this->changeResource(...),
                array   : $this->auth->readTenantSecurityChangeRequests(tenantSlug: $tenantSlug)
            ),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function configurationResource(TenantSecurityConfiguration|null $configuration) : array|null
    {
        if ($configuration === null) {
            return null;
        }

        return [
            'tenantSlug'             => $configuration->tenantSlug,
            'federationConnectionId' => $configuration->federationConnectionId,
            'scimDirectoryId'        => $configuration->scimDirectoryId,
            'verifiedDomains'        => $configuration->verifiedDomains,
            'groupRoleMap'           => $configuration->groupRoleMap,
            'policyProfile'          => $configuration->policyProfile,
            'rolloutVersion'         => $configuration->rolloutVersion,
        ];
    }

    /**
     * @return list<FederationConnection>
     */
    private function tenantConnections(string $tenantSlug) : array
    {
        return array_values(array: array_filter(
                                       array   : $this->auth->readFederationConnections(),
                                       callback: static fn (FederationConnection $connection) : bool => $connection->tenantSlug === $tenantSlug
                                   ));
    }

    private function error(int $statusCode, #[SensitiveParameter] string $errorCode, string $message) : JsonHttpResponse
    {
        return $this->response(statusCode: $statusCode, body: [
            'error'   => $errorCode,
            'message' => $message,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function changeResource(TenantSecurityChangeRequest $change) : array
    {
        return [
            'changeId'     => $change->changeId,
            'tenantSlug'   => $change->tenantSlug,
            'requestedBy'  => $change->requestedBy,
            'reason'       => $change->reason,
            'status'       => $change->status->value,
            'diff'         => $change->diff,
            'requestedAt'  => $change->requestedAt->format(format: DATE_ATOM),
            'approvedBy'   => $change->approvedBy,
            'approvedAt'   => $change->approvedAt?->format(format: DATE_ATOM),
            'appliedAt'    => $change->appliedAt?->format(format: DATE_ATOM),
            'rolledBackAt' => $change->rolledBackAt?->format(format: DATE_ATOM),
            'before'       => $this->configurationResource(configuration: $change->before),
            'after'        => $this->configurationResource(configuration: $change->after),
        ];
    }

    /**
     * @param array<string, mixed> $body
     */
    private function configurationFromBody(string $tenantSlug, array $body) : TenantSecurityConfiguration
    {
        return new TenantSecurityConfiguration(
            tenantSlug            : $tenantSlug,
            federationConnectionId: $this->nullableString(body: $body, field: 'federationConnectionId'),
            scimDirectoryId       : $this->nullableString(body: $body, field: 'scimDirectoryId'),
            verifiedDomains       : $this->stringList(values: $body['verifiedDomains'] ?? []),
            groupRoleMap          : $this->groupRoleMap(body: $body),
            policyProfile         : $this->nullableString(body: $body, field: 'policyProfile') ?? 'user',
            rolloutVersion        : $this->intValue(body: $body, field: 'rolloutVersion') ?? 1
        );
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, list<string>>
     */
    private function groupRoleMap(array $body) : array
    {
        $groupRoleMap = $body['groupRoleMap'] ?? [];

        if (! is_array(value: $groupRoleMap)) {
            return [];
        }

        $resolved = [];

        foreach ($groupRoleMap as $group => $roles) {
            if (! is_string(value: $group) || trim(string: $group) === '' || ! is_array(value: $roles)) {
                continue;
            }

            $resolved[trim(string: $group)] = $this->stringList(values: $roles);
        }

        return $resolved;
    }

    /**
     * @return array<string, mixed>
     */
    private function connectionResource(FederationConnection $connection) : array
    {
        return [
            'connectionId'            => $connection->connectionId,
            'tenantSlug'              => $connection->tenantSlug,
            'name'                    => $connection->name,
            'provider'                => $connection->provider->value,
            'domain'                  => $connection->domain,
            'ssoOnly'                 => $connection->ssoOnly,
            'groupRoleMap'            => $connection->groupRoleMap,
            'metadataUrl'             => $connection->metadataUrl,
            'metadataIssuer'          => $connection->metadataIssuer,
            'metadataSyncedAt'        => $connection->metadataSyncedAt?->format(format: DATE_ATOM),
            'domainVerificationToken' => $connection->domainVerificationToken,
            'domainVerifiedAt'        => $connection->domainVerifiedAt?->format(format: DATE_ATOM),
            'health'                  => $connection->health->value,
            'healthCheckedAt'         => $connection->healthCheckedAt?->format(format: DATE_ATOM),
            'breakGlassAllowed'       => $connection->breakGlassAllowed,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function directoryResource(ScimDirectory $directory) : array
    {
        return [
            'directoryId'       => $directory->directoryId,
            'tenantSlug'        => $directory->tenantSlug,
            'name'              => $directory->name,
            'groupRoleMap'      => $directory->groupRoleMap,
            'createdAt'         => $directory->createdAt->format(format: DATE_ATOM),
            'rotatedAt'         => $directory->rotatedAt?->format(format: DATE_ATOM),
            'health'            => $directory->health->value,
            'healthCheckedAt'   => $directory->healthCheckedAt?->format(format: DATE_ATOM),
            'outageReason'      => $directory->outageReason,
            'outageStartedAt'   => $directory->outageStartedAt?->format(format: DATE_ATOM),
            'outageRecoveredAt' => $directory->outageRecoveredAt?->format(format: DATE_ATOM),
        ];
    }

    private function tenantScimDirectory(string $tenantSlug, string $directoryId) : ScimDirectory
    {
        foreach ($this->auth->readScimDirectories(tenantSlug: $tenantSlug) as $directory) {
            if ($directory->directoryId === $directoryId) {
                return $directory;
            }
        }

        throw new InvalidArgumentException(message: 'SCIM directory was not found for the tenant.');
    }
}
