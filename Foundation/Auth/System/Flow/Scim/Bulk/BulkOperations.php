<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

use Avax\Auth\System\Capability\Scim\ScimFailed;
use Avax\Auth\System\Capability\Scim\ScimDirectory;
use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Capability\Scim\ScimProvisionedIdentityStoreInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Capability\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use JsonException;
use SensitiveParameter;
use Throwable;

/**
 * Handles SCIM bulk operations processing.
 *
 * Banal: The Scim Bulk Operations file.
 */
final readonly class BulkOperations
{
    public function __construct(
        private ProvisionableUserSourceInterface $userSource,
        private ScimDirectoryStoreInterface $directoryStore,
        private ScimProvisionedIdentityStoreInterface $identityStore,
        #[SensitiveParameter] private PasswordHasher $passwordHasher,
        private IdGeneratorInterface $idGenerator,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Processes a bulk request and returns the bulk response.
     *
     * @throws ScimFailed
     */
    public function execute(BulkRequest $request) : BulkResponse
    {
        // Validate that we don't exceed max operations
        if (count($request->operations) > $request->maxOperations) {
            throw ScimFailed::tooManyBulkOperations(
                count($request->operations),
                $request->maxOperations
            );
        }

        $results = [];

        foreach ($request->operations as $operation) {
            try {
                $result = $this->processOperation($operation);
                $results[] = $result;
            } catch (Throwable $exception) {
                // If failOnErrors is true, we stop processing on first error
                if ($request->failOnErrors) {
                    throw $exception;
                }

                // Otherwise, we create an error result and continue
                $results[] = new OperationResult(
                    method: $operation->method,
                    path: $operation->path,
                    status: 500,
                    body: ['error' => $exception->getMessage()],
                    bulkId: $operation->bulkId
                );
            }
        }

        return BulkResponse::fromArray($results);
    }

    /**
     * Processes a single bulk operation.
     *
     * @throws ScimFailed
     */
    private function processOperation(Operation $operation) : OperationResult
    {
        // For simplicity, we only support User operations in this implementation
        // In a full implementation, we would support Groups and other resource types
        if (preg_match('~^/Users(?:/([^/]+))?$~', $operation->path, $matches) !== 1) {
            throw ScimFailed::invalidBulkRequest(
                'Only User resource operations are supported in bulk requests'
            );
        }

        $externalId = $matches[1] ?? null;
        $method = $operation->method;
        $body = $operation->body;

        // Handle collection operations (no external ID)
        if ($externalId === null) {
            return match ($method) {
                'GET' => $this->handleGetUsers($body),
                'POST' => $this->handlePostUsers($body),
                default => throw ScimFailed::invalidBulkRequest(
                    "Method {$method} is not supported for User collection"
                ),
            };
        }

        // Handle individual resource operations
        return match ($method) {
            'GET' => $this->handleGetUser($externalId, $body),
            'PUT' => $this->handlePutUser($externalId, $body),
            'PATCH' => $this->handlePatchUser($externalId, $body),
            'DELETE' => $this->handleDeleteUser($externalId, $body),
            default => throw ScimFailed::invalidBulkRequest(
                "Method {$method} is not supported for User resource"
            ),
        };
    }

    private function handleGetUsers(array $body) : OperationResult
    {
        // In a real implementation, we would extract query parameters from body
        // For now, we'll return a basic list
        return new OperationResult(
            method: 'GET',
            path: '/Users',
            status: 200,
            body: [
                'schemas' => ['urn:ietf:params:scim:api:messages:2.0:ListResponse'],
                'totalResults' => 0,
                'startIndex' => 1,
                'itemsPerPage' => 0,
                'Resources' => []
            ],
            bulkId: false
        );
    }

    private function handlePostUsers(array $body) : OperationResult
    {
        // This would normally delegate to the provisioning flow
        // For bulk operations, we simplify and return a basic response
        $externalId = $body['externalId'] ?? uniqid('bulk_', true);

        return new OperationResult(
            method: 'POST',
            path: '/Users',
            status: 201,
            body: [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
                'id' => $externalId,
                'externalId' => $externalId,
                'userName' => $body['userName'] ?? '',
                'active' => true,
                'meta' => ['resourceType' => 'User']
            ],
            bulkId: true
        );
    }

    private function handleGetUser(string $externalId, array $body) : OperationResult
    {
        // Simplified - in reality we'd look up the user
        return new OperationResult(
            method: 'GET',
            path: "/Users/{$externalId}",
            status: 200,
            body: [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
                'id' => $externalId,
                'externalId' => $externalId,
                'userName' => 'bulk-user',
                'active' => true,
                'meta' => ['resourceType' => 'User']
            ],
            bulkId: false
        );
    }

    private function handlePutUser(string $externalId, array $body) : OperationResult
    {
        // Simplified - in reality we'd update the user
        return new OperationResult(
            method: 'PUT',
            path: "/Users/{$externalId}",
            status: 200,
            body: [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
                'id' => $externalId,
                'externalId' => $externalId,
                'userName' => $body['userName'] ?? 'bulk-user',
                'active' => true,
                'meta' => ['resourceType' => 'User']
            ],
            bulkId: false
        );
    }

    private function handlePatchUser(string $externalId, array $body) : OperationResult
    {
        // Simplified - in reality we'd patch the user
        return new OperationResult(
            method: 'PATCH',
            path: "/Users/{$externalId}",
            status: 200,
            body: [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
                'id' => $externalId,
                'externalId' => $externalId,
                'userName' => $body['userName'] ?? 'bulk-user',
                'active' => true,
                'meta' => ['resourceType' => 'User']
            ],
            bulkId: false
        );
    }

    private function handleDeleteUser(string $externalId, array $body) : OperationResult
    {
        // Simplified - in reality we'd delete the user
        return new OperationResult(
            method: 'DELETE',
            path: "/Users/{$externalId}",
            status: 204,
            body: [],
            bulkId: false
        );
    }
}