<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk;

use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimAccountState;
use InvalidArgumentException;
use Random\RandomException;

final readonly class RunScimBulk
{
    public function __construct(private ProvisionScimUser $provisionScimUser, private DeleteScimUser $deleteScimUser, private int $maximumOperations = 100) {}

    /**
     * @throws RandomException
     */
    public function execute(ScimBulkRequest $request) : ScimBulkResponse
    {
        if ($request->operations === []) {
            throw ScimFailed::invalidBulkRequest(message: 'SCIM bulk request must contain at least one operation.');
        }

        if (count(value: $request->operations) > $this->maximumOperations) {
            throw ScimFailed::tooManyBulkOperations(provided: count(value: $request->operations), maximum: $this->maximumOperations);
        }

        $results = [];

        foreach ($request->operations as $operation) {
            $results[] = $this->executeOperation(request: $request, operation: $operation);
        }

        return new ScimBulkResponse(operations: $results);
    }

    /**
     * @throws RandomException
     */
    private function executeOperation(ScimBulkRequest $request, ScimBulkOperation $operation) : ScimBulkOperationResult
    {
        $method = strtoupper(string: trim(string: $operation->method));
        $path   = '/' . trim(string: $operation->path, characters: '/');

        return match (true) {
            $method === 'POST' && $path === '/Users'                                                                   => $this->createUser(request: $request, operation: $operation),
            $method === 'PUT' && preg_match(pattern: '~^/Users/([^/]+)$~', subject: $path, matches: $matches) === 1    => $this->replaceUser(
                request   : $request,
                operation : $operation,
                externalId: urldecode(string: $matches[1]),
            ),
            $method === 'DELETE' && preg_match(pattern: '~^/Users/([^/]+)$~', subject: $path, matches: $matches) === 1 => $this->deleteUser(
                request   : $request,
                operation : $operation,
                externalId: urldecode(string: $matches[1]),
            ),
            default                                                                                                    => throw ScimFailed::invalidBulkRequest(message: "Unsupported SCIM bulk operation [{$method} {$path}]."),
        };
    }

    /**
     * @throws RandomException
     */
    private function createUser(ScimBulkRequest $request, ScimBulkOperation $operation) : ScimBulkOperationResult
    {
        $result = $this->provisionScimUser->execute(data: $this->provisionData(
            request   : $request,
            body      : $operation->body,
            externalId: $this->requiredString(body: $operation->body, field: 'externalId'),
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
            bulkId  : $operation->bulkId,
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
            state         : $this->state(body: $body),
        );
    }

    /**
     * @param array<string, mixed> $body
     */
    private function email(array $body) : string
    {
        $emails = $body['emails'] ?? [];

        if (! is_array(value: $emails)) {
            throw new InvalidArgumentException(message: 'SCIM emails must be an array.');
        }

        foreach ($emails as $email) {
            if (! is_array(value: $email) || ! is_string(value: $email['value'] ?? null)) {
                continue;
            }

            return trim(string: $email['value']);
        }

        throw new InvalidArgumentException(message: 'SCIM email value is required.');
    }

    /**
     * @param array<string, mixed> $body
     */
    private function requiredString(array $body, string $field) : string
    {
        $value = $body[$field] ?? null;

        if (! is_scalar(value: $value) || trim(string: (string) $value) === '') {
            throw new InvalidArgumentException(message: "Field [{$field}] is required.");
        }

        return trim(string: (string) $value);
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return list<string>
     */
    private function groups(array $body) : array
    {
        $groups = $body['groups'] ?? [];

        if (! is_array(value: $groups)) {
            return [];
        }

        $resolved = [];

        foreach ($groups as $group) {
            if (is_array(value: $group) && is_string(value: $group['value'] ?? null) && trim(string: $group['value']) !== '') {
                $resolved[] = trim(string: $group['value']);
            }
        }

        return array_values(array: array_unique(array: $resolved));
    }

    /**
     * @param array<string, mixed> $body
     */
    private function state(array $body) : ScimAccountState
    {
        $active = $body['active'] ?? null;
        $state  = is_string(value: $body['state'] ?? null) ? strtolower(string: trim(string: $body['state'])) : null;

        return match (true) {
            $state === 'disabled'                     => ScimAccountState::DISABLED,
            $state === 'suspended', $active === false => ScimAccountState::SUSPENDED,
            default                                   => ScimAccountState::ACTIVE,
        };
    }

    /**
     * @throws RandomException
     */
    private function replaceUser(ScimBulkRequest $request, ScimBulkOperation $operation, string $externalId) : ScimBulkOperationResult
    {
        $result = $this->provisionScimUser->execute(data: $this->provisionData(
            request   : $request,
            body      : $operation->body,
            externalId: $externalId,
        ));

        return new ScimBulkOperationResult(
            method  : 'PUT',
            path    : '/Users/' . rawurlencode(string: $externalId),
            status  : 200,
            response: [
                          'externalId' => $result->externalId,
                          'userId'     => $result->userId,
                          'state'      => $result->state->value,
                          'roles'      => $result->roles,
                          'updated'    => $result->updated,
                          'idempotent' => $result->idempotent,
                      ],
            bulkId  : $operation->bulkId,
        );
    }

    private function deleteUser(ScimBulkRequest $request, ScimBulkOperation $operation, string $externalId) : ScimBulkOperationResult
    {
        $this->deleteScimUser->execute(data: new DeleteScimUserData(
                                                 directoryId   : $request->directoryId,
                                                 directoryToken: $request->directoryToken,
                                                 externalId    : $externalId,
                                             ));

        return new ScimBulkOperationResult(
            method  : 'DELETE',
            path    : '/Users/' . rawurlencode(string: $externalId),
            status  : 204,
            response: [],
            bulkId  : $operation->bulkId,
        );
    }
}
