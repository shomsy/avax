<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

use Avax\Auth\System\Capability\Scim\ScimAccountState;
use Avax\Auth\System\Flow\Scim\DeleteUser\DeleteScimUser;
use Avax\Auth\System\Flow\Scim\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUser;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Flow\Scim\ScimFailed;
use InvalidArgumentException;

final readonly class RunScimBulk
{
    private int               $maximumOperations;
    private DeleteScimUser    $deleteScimUser;
    private ProvisionScimUser $provisionScimUser;

    public function __construct(
        ProvisionScimUser $provisionScimUser,
        DeleteScimUser    $deleteScimUser,
        int               $maximumOperations = 100
    )
    {
        $this->provisionScimUser = $provisionScimUser;
        $this->deleteScimUser    = $deleteScimUser;
        $this->maximumOperations = $maximumOperations;
    }

    public function execute(ScimBulkRequest $request) : ScimBulkResponse
    {
        if ($request->operations === []) {
            throw ScimFailed::invalidBulkRequest(message: 'SCIM bulk request must contain at least one operation.');
        }

        if (count($request->operations) > $this->maximumOperations) {
            throw ScimFailed::tooManyBulkOperations(provided: count($request->operations), maximum: $this->maximumOperations);
        }

        $results = [];

        foreach ($request->operations as $operation) {
            $results[] = $this->executeOperation(request: $request, operation: $operation);
        }

        return new ScimBulkResponse(operations: $results);
    }

    private function executeOperation(ScimBulkRequest $request, ScimBulkOperation $operation) : ScimBulkOperationResult
    {
        $method = strtoupper(trim($operation->method));
        $path   = '/' . trim($operation->path, '/');

        return match (true) {
            $method === 'POST' && $path === '/Users'                                        => $this->createUser(request: $request, operation: $operation),
            $method === 'PUT' && preg_match('~^/Users/([^/]+)$~', $path, $matches) === 1    => $this->replaceUser(
                request   : $request,
                operation : $operation,
                externalId: urldecode($matches[1])
            ),
            $method === 'DELETE' && preg_match('~^/Users/([^/]+)$~', $path, $matches) === 1 => $this->deleteUser(
                request   : $request,
                operation : $operation,
                externalId: urldecode($matches[1])
            ),
            default                                                                         => throw ScimFailed::invalidBulkRequest(message: "Unsupported SCIM bulk operation [{$method} {$path}]."),
        };
    }

    private function createUser(ScimBulkRequest $request, ScimBulkOperation $operation) : ScimBulkOperationResult
    {
        $result = $this->provisionScimUser->execute(data: $this->provisionData(
            request   : $request,
            body      : $operation->body,
            externalId: $this->requiredString(body: $operation->body, field: 'externalId')
        ));

        return new ScimBulkOperationResult(
            method  : 'POST',
            path    : '/Users',
            status  : 201,
            response: [
                          'externalId' => $result->externalId,
                          'userId'     => $result->userId,
                          'state'      => $result->state->value,
                          'roles'      => $result->roles,
                          'created'    => $result->created,
                      ],
            bulkId  : $operation->bulkId
        );
    }

    /**
     * @param array<string, mixed> $body
     */
    private function provisionData(ScimBulkRequest $request, array $body, string $externalId) : ProvisionScimUserData
    {
        return new ProvisionScimUserData(
            directoryId   : $request->directoryId,
            directoryToken: $request->directoryToken,
            externalId    : $externalId,
            email         : $this->email(body: $body),
            username      : $this->requiredString(body: $body, field: 'userName'),
            groups        : $this->groups(body: $body),
            state         : $this->state(body: $body)
        );
    }

    /**
     * @param array<string, mixed> $body
     */
    private function email(array $body) : string
    {
        $emails = $body['emails'] ?? [];

        if (! is_array($emails)) {
            throw new InvalidArgumentException(message: 'SCIM emails must be an array.');
        }

        foreach ($emails as $email) {
            if (! is_array($email) || ! is_string($email['value'] ?? null)) {
                continue;
            }

            return trim($email['value']);
        }

        throw new InvalidArgumentException(message: 'SCIM email value is required.');
    }

    /**
     * @param array<string, mixed> $body
     */
    private function requiredString(array $body, string $field) : string
    {
        $value = $body[$field] ?? null;

        if (! is_scalar($value) || trim((string) $value) === '') {
            throw new InvalidArgumentException(message: "Field [{$field}] is required.");
        }

        return trim((string) $value);
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return list<string>
     */
    private function groups(array $body) : array
    {
        $groups = $body['groups'] ?? [];

        if (! is_array($groups)) {
            return [];
        }

        $resolved = [];

        foreach ($groups as $group) {
            if (is_array($group) && is_string($group['value'] ?? null) && trim($group['value']) !== '') {
                $resolved[] = trim($group['value']);
            }
        }

        return array_values(array_unique($resolved));
    }

    /**
     * @param array<string, mixed> $body
     */
    private function state(array $body) : ScimAccountState
    {
        $active = $body['active'] ?? null;
        $state  = is_string($body['state'] ?? null) ? strtolower(trim($body['state'])) : null;

        return match (true) {
            $state === 'disabled'  => ScimAccountState::DISABLED,
            $state === 'suspended' => ScimAccountState::SUSPENDED,
            $active === false      => ScimAccountState::SUSPENDED,
            default                => ScimAccountState::ACTIVE,
        };
    }

    private function replaceUser(ScimBulkRequest $request, ScimBulkOperation $operation, string $externalId) : ScimBulkOperationResult
    {
        $result = $this->provisionScimUser->execute(data: $this->provisionData(
            request   : $request,
            body      : $operation->body,
            externalId: $externalId
        ));

        return new ScimBulkOperationResult(
            method  : 'PUT',
            path    : '/Users/' . rawurlencode($externalId),
            status  : 200,
            response: [
                          'externalId' => $result->externalId,
                          'userId'     => $result->userId,
                          'state'      => $result->state->value,
                          'roles'      => $result->roles,
                          'updated'    => $result->updated,
                          'idempotent' => $result->idempotent,
                      ],
            bulkId  : $operation->bulkId
        );
    }

    private function deleteUser(ScimBulkRequest $request, ScimBulkOperation $operation, string $externalId) : ScimBulkOperationResult
    {
        $this->deleteScimUser->execute(data: new DeleteScimUserData(
                                                 directoryId   : $request->directoryId,
                                                 directoryToken: $request->directoryToken,
                                                 externalId    : $externalId
                                             ));

        return new ScimBulkOperationResult(
            method  : 'DELETE',
            path    : '/Users/' . rawurlencode($externalId),
            status  : 204,
            response: [],
            bulkId  : $operation->bulkId
        );
    }
}
