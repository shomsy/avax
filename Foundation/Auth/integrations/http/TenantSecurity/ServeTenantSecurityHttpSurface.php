<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http\TenantSecurity;

use Avax\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Auth\Integrations\Http\JsonHttpResponse;
use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationProvider;
use Avax\Auth\System\Capability\Scim\ScimDirectory;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Flow\Federation\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Flow\Federation\VerifyDomain\VerifyFederationDomainData;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Throwable;

/**
 * Publishes a framework-neutral tenant-admin API over tenant security, federation, and SCIM flows.
 */
final readonly class ServeTenantSecurityHttpSurface
{
    public function __construct(
        private AuthInterface $auth
    ) {}

    public function execute(HttpEndpointInput $input) : JsonHttpResponse
    {
        $method = strtoupper(trim($input->method));
        $path = $this->normalizePath($input->path);
        $tenantSlug = $this->tenantSlug($input);

        try {
            if ($method === 'GET' && $path === '/tenants/' . $tenantSlug . '/security') {
                return $this->summary($tenantSlug);
            }

            if ($method === 'GET' && $path === '/tenants/' . $tenantSlug . '/security/changes') {
                return $this->response(200, [
                    'tenant' => $tenantSlug,
                    'changes' => array_map($this->changeResource(...), $this->auth->readTenantSecurityChangeRequests($tenantSlug)),
                ]);
            }

            if ($method === 'GET' && preg_match('~^/tenants/[^/]+/security/changes/([^/]+)$~', $path, $matches) === 1) {
                $change = $this->auth->readTenantSecurityChangeRequest(urldecode($matches[1]));

                return $change === null
                    ? $this->error(404, 'not_found', 'Tenant security change request was not found.')
                    : $this->response(200, ['change' => $this->changeResource($change)]);
            }

            if ($method === 'POST' && $path === '/tenants/' . $tenantSlug . '/security/changes') {
                $change = $this->auth->beginTenantSecurityChange(new BeginTenantSecurityChangeData(
                    tenantSlug  : $tenantSlug,
                    requestedBy : $this->requiredString($input->body, 'requestedBy'),
                    reason      : $this->requiredString($input->body, 'reason'),
                    after       : $this->configurationFromBody($tenantSlug, $input->body)
                ));

                return $this->response(201, ['change' => $this->changeResource($change)]);
            }

            if ($method === 'POST' && preg_match('~^/tenants/[^/]+/security/changes/([^/]+)/approve$~', $path, $matches) === 1) {
                $change = $this->auth->approveTenantSecurityChange(
                    urldecode($matches[1]),
                    $this->requiredString($input->body, 'approvedBy')
                );

                return $this->response(200, ['change' => $this->changeResource($change)]);
            }

            if ($method === 'POST' && preg_match('~^/tenants/[^/]+/security/changes/([^/]+)/apply$~', $path, $matches) === 1) {
                $configuration = $this->auth->applyTenantSecurityChange(urldecode($matches[1]));

                return $this->response(200, ['configuration' => $this->configurationResource($configuration)]);
            }

            if ($method === 'POST' && preg_match('~^/tenants/[^/]+/security/changes/([^/]+)/rollback$~', $path, $matches) === 1) {
                $configuration = $this->auth->rollbackTenantSecurityChange(urldecode($matches[1]));

                return $this->response(200, ['configuration' => $this->configurationResource($configuration)]);
            }

            if ($method === 'GET' && $path === '/tenants/' . $tenantSlug . '/security/federation-connections') {
                return $this->response(200, [
                    'connections' => array_map(
                        $this->connectionResource(...),
                        $this->tenantConnections($tenantSlug)
                    ),
                ]);
            }

            if ($method === 'POST' && $path === '/tenants/' . $tenantSlug . '/security/federation-connections') {
                $connection = $this->auth->registerFederationConnection(new RegisterFederationConnectionData(
                    tenantSlug        : $tenantSlug,
                    name              : $this->requiredString($input->body, 'name'),
                    provider          : FederationProvider::from(strtolower($this->requiredString($input->body, 'provider'))),
                    domain            : $this->requiredString($input->body, 'domain'),
                    ssoOnly           : $this->boolValue($input->body, 'ssoOnly'),
                    groupRoleMap      : $this->groupRoleMap($input->body),
                    metadataUrl       : $this->nullableString($input->body, 'metadataUrl'),
                    breakGlassAllowed : $this->boolValue($input->body, 'breakGlassAllowed')
                ));

                return $this->response(201, ['connection' => $this->connectionResource($connection)]);
            }

            if ($method === 'POST' && preg_match('~^/tenants/[^/]+/security/federation-connections/([^/]+)/verify-domain$~', $path, $matches) === 1) {
                $connection = $this->auth->verifyFederationDomain(new VerifyFederationDomainData(
                    connectionId      : urldecode($matches[1]),
                    verificationToken : $this->requiredString($input->body, 'verificationToken')
                ));

                return $this->response(200, ['connection' => $this->connectionResource($connection)]);
            }

            if ($method === 'POST' && preg_match('~^/tenants/[^/]+/security/federation-connections/([^/]+)/sync-metadata$~', $path, $matches) === 1) {
                $connection = $this->auth->syncFederationMetadata(urldecode($matches[1]));

                return $this->response(200, ['connection' => $this->connectionResource($connection)]);
            }

            if ($method === 'GET' && preg_match('~^/tenants/[^/]+/security/federation-connections/([^/]+)/health$~', $path, $matches) === 1) {
                $health = $this->auth->checkFederationConnectionHealth(urldecode($matches[1]));

                return $this->response(200, ['health' => $health->value]);
            }

            if ($method === 'GET' && $path === '/tenants/' . $tenantSlug . '/security/scim-directories') {
                return $this->response(200, [
                    'directories' => array_map(
                        $this->directoryResource(...),
                        $this->auth->readScimDirectories($tenantSlug)
                    ),
                ]);
            }

            if ($method === 'POST' && $path === '/tenants/' . $tenantSlug . '/security/scim-directories') {
                $directory = $this->auth->registerScimDirectory(new RegisterScimDirectoryData(
                    tenantSlug  : $tenantSlug,
                    name        : $this->requiredString($input->body, 'name'),
                    groupRoleMap: $this->groupRoleMap($input->body)
                ));

                return $this->response(201, [
                    'directory' => $this->directoryResource($directory->directory),
                    'plainTextToken' => $directory->plainTextToken,
                ]);
            }

            if ($method === 'POST' && preg_match('~^/tenants/[^/]+/security/scim-directories/([^/]+)/rotate-token$~', $path, $matches) === 1) {
                $rotated = $this->auth->rotateScimToken(urldecode($matches[1]));

                return $this->response(200, [
                    'directory' => $this->directoryResource($rotated->directory),
                    'plainTextToken' => $rotated->plainTextToken,
                ]);
            }
        } catch (Throwable $failure) {
            $status = str_contains(strtolower($failure->getMessage()), 'not found') || str_contains(strtolower($failure->getMessage()), 'unknown')
                ? 404
                : 422;

            return $this->error($status, 'tenant_security_failed', $failure->getMessage());
        }

        return $this->error(404, 'not_found', 'Tenant security route was not found.');
    }

    private function summary(string $tenantSlug) : JsonHttpResponse
    {
        return $this->response(200, [
            'tenant' => $tenantSlug,
            'configuration' => $this->configurationResource($this->auth->readTenantSecurityConfiguration($tenantSlug)),
            'federationConnections' => array_map(
                $this->connectionResource(...),
                $this->tenantConnections($tenantSlug)
            ),
            'scimDirectories' => array_map(
                $this->directoryResource(...),
                $this->auth->readScimDirectories($tenantSlug)
            ),
            'changes' => array_map(
                $this->changeResource(...),
                $this->auth->readTenantSecurityChangeRequests($tenantSlug)
            ),
        ]);
    }

    /**
     * @return list<FederationConnection>
     */
    private function tenantConnections(string $tenantSlug) : array
    {
        return array_values(array_filter(
            $this->auth->readFederationConnections(),
            static fn (FederationConnection $connection) : bool => $connection->tenantSlug === $tenantSlug
        ));
    }

    /**
     * @param array<string, mixed> $body
     */
    private function configurationFromBody(string $tenantSlug, array $body) : TenantSecurityConfiguration
    {
        return new TenantSecurityConfiguration(
            tenantSlug             : $tenantSlug,
            federationConnectionId : $this->nullableString($body, 'federationConnectionId'),
            scimDirectoryId        : $this->nullableString($body, 'scimDirectoryId'),
            verifiedDomains        : $this->stringList($body['verifiedDomains'] ?? []),
            groupRoleMap           : $this->groupRoleMap($body),
            policyProfile          : $this->nullableString($body, 'policyProfile') ?? 'user',
            rolloutVersion         : $this->intValue($body, 'rolloutVersion') ?? 1
        );
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
            'tenantSlug' => $configuration->tenantSlug,
            'federationConnectionId' => $configuration->federationConnectionId,
            'scimDirectoryId' => $configuration->scimDirectoryId,
            'verifiedDomains' => $configuration->verifiedDomains,
            'groupRoleMap' => $configuration->groupRoleMap,
            'policyProfile' => $configuration->policyProfile,
            'rolloutVersion' => $configuration->rolloutVersion,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function changeResource(TenantSecurityChangeRequest $change) : array
    {
        return [
            'changeId' => $change->changeId,
            'tenantSlug' => $change->tenantSlug,
            'requestedBy' => $change->requestedBy,
            'reason' => $change->reason,
            'status' => $change->status->value,
            'diff' => $change->diff,
            'requestedAt' => $change->requestedAt->format(DATE_ATOM),
            'approvedBy' => $change->approvedBy,
            'approvedAt' => $change->approvedAt?->format(DATE_ATOM),
            'appliedAt' => $change->appliedAt?->format(DATE_ATOM),
            'rolledBackAt' => $change->rolledBackAt?->format(DATE_ATOM),
            'before' => $this->configurationResource($change->before),
            'after' => $this->configurationResource($change->after),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function connectionResource(FederationConnection $connection) : array
    {
        return [
            'connectionId' => $connection->connectionId,
            'tenantSlug' => $connection->tenantSlug,
            'name' => $connection->name,
            'provider' => $connection->provider->value,
            'domain' => $connection->domain,
            'ssoOnly' => $connection->ssoOnly,
            'groupRoleMap' => $connection->groupRoleMap,
            'metadataUrl' => $connection->metadataUrl,
            'metadataIssuer' => $connection->metadataIssuer,
            'metadataSyncedAt' => $connection->metadataSyncedAt?->format(DATE_ATOM),
            'domainVerificationToken' => $connection->domainVerificationToken,
            'domainVerifiedAt' => $connection->domainVerifiedAt?->format(DATE_ATOM),
            'health' => $connection->health->value,
            'healthCheckedAt' => $connection->healthCheckedAt?->format(DATE_ATOM),
            'breakGlassAllowed' => $connection->breakGlassAllowed,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function directoryResource(ScimDirectory $directory) : array
    {
        return [
            'directoryId' => $directory->directoryId,
            'tenantSlug' => $directory->tenantSlug,
            'name' => $directory->name,
            'groupRoleMap' => $directory->groupRoleMap,
            'createdAt' => $directory->createdAt->format(DATE_ATOM),
            'rotatedAt' => $directory->rotatedAt?->format(DATE_ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, list<string>>
     */
    private function groupRoleMap(array $body) : array
    {
        $groupRoleMap = $body['groupRoleMap'] ?? [];

        if (! is_array($groupRoleMap)) {
            return [];
        }

        $resolved = [];

        foreach ($groupRoleMap as $group => $roles) {
            if (! is_string($group) || trim($group) === '' || ! is_array($roles)) {
                continue;
            }

            $resolved[trim($group)] = $this->stringList($roles);
        }

        return $resolved;
    }

    /**
     * @param array<int|string, mixed> $values
     * @return list<string>
     */
    private function stringList(array $values) : array
    {
        $resolved = [];

        foreach ($values as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $trimmed = trim((string) $value);

            if ($trimmed !== '') {
                $resolved[] = $trimmed;
            }
        }

        return array_values($resolved);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function requiredString(array $body, string $field) : string
    {
        $value = $this->nullableString($body, $field);

        if ($value === null || $value === '') {
            throw new \InvalidArgumentException("{$field} is required.");
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function nullableString(array $body, string $field) : string|null
    {
        $value = $body[$field] ?? null;

        return is_scalar($value) ? trim((string) $value) : null;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function boolValue(array $body, string $field) : bool
    {
        $value = $body[$field] ?? null;

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function intValue(array $body, string $field) : int|null
    {
        $value = $body[$field] ?? null;

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }

    private function tenantSlug(HttpEndpointInput $input) : string
    {
        $routeTenantSlug = $input->routeParameters['tenantSlug'] ?? null;

        if (is_scalar($routeTenantSlug) && trim((string) $routeTenantSlug) !== '') {
            return trim((string) $routeTenantSlug);
        }

        if (preg_match('~^/tenants/([^/]+)/security(?:/.*)?$~', $this->normalizePath($input->path), $matches) === 1) {
            return urldecode($matches[1]);
        }

        throw new \InvalidArgumentException('tenantSlug is required.');
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

    private function error(int $statusCode, string $errorCode, string $message) : JsonHttpResponse
    {
        return $this->response($statusCode, [
            'error' => $errorCode,
            'message' => $message,
        ]);
    }

    private function normalizePath(string $path) : string
    {
        $trimmed = trim($path);

        if ($trimmed === '' || $trimmed === '/') {
            return '/';
        }

        return '/' . trim($trimmed, '/');
    }
}
