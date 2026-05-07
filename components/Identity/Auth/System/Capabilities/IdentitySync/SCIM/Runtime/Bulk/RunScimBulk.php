<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk;

use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimAccountState;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use InvalidArgumentException;
use Random\RandomException;

final readonly class RunScimBulk
{
    public function __construct(private ProvisionScimUser $provisionScimUser, private DeleteScimUser $deleteScimUser, private int $maximumOperations = 100) {}

    /**
     * @throws RandomException
     */
    public function execute(ScimBulkRequest $scimBulkRequest) : ScimBulkResponse
    {
        if ($scimBulkRequest->operations === []) {
            throw ScimFailed::invalidBulkRequest(message: 'SCIM bulk request must contain at least one operation.');
        }

        if (count(value: $scimBulkRequest->operations) > $this->maximumOperations) {
            throw ScimFailed::tooManyBulkOperations(provided: count(value: $scimBulkRequest->operations), maximum: $this->maximumOperations);
        }

        $results = [];

        foreach ($scimBulkRequest->operations as $operation) {
            $results[] = $this->executeOperation(request: $scimBulkRequest, operation: $operation);
        }

        return new ScimBulkResponse(operations: $results);
    }

    /**
     * @throws RandomException
     */
    private function executeOperation(ScimBulkRequest $scimBulkRequest, ScimBulkOperation $scimBulkOperation) : ScimBulkOperationResult
    {
        $method = strtoupper(string: trim(string: $scimBulkOperation->method));
        $path   = '/' . trim(string: $scimBulkOperation->path, characters: '/');

        return match (true) {
            $method === 'POST' && $path === '/Users'                                                                   => $this->createUser(request: $scimBulkRequest, operation: $scimBulkOperation),
            $method === 'PUT' && preg_match(pattern: '~^/Users/([^/]+)$~', subject: $path, matches: $matches) === 1    => $this->replaceUser(
                externalId: urldecode(string: $matches[1]),
                request   : $scimBulkRequest,
                operation : $scimBulkOperation,
            ),
            $method === 'DELETE' && preg_match(pattern: '~^/Users/([^/]+)$~', subject: $path, matches: $matches) === 1 => $this->deleteUser(
                externalId: urldecode(string: $matches[1]),
                request   : $scimBulkRequest,
                operation : $scimBulkOperation,
            ),
            default                                                                                                    => throw ScimFailed::invalidBulkRequest(message: sprintf('Unsupported SCIM bulk operation [%s %s].', $method, $path)),
        };
    }

    /**
     * @throws RandomException
     */
    private function createUser(ScimBulkRequest $scimBulkRequest, ScimBulkOperation $scimBulkOperation) : ScimBulkOperationResult
    {
        $scimProvisioningResult = $this->provisionScimUser->execute(data: $this->provisionData(
            body      : $scimBulkOperation->body,
            externalId: $this->requiredString(body: $scimBulkOperation->body, field: 'externalId'),
            request   : $scimBulkRequest,
        ));

        return new ScimBulkOperationResult(
            method  : 'POST',
            path    : '/Users',
            status  : 201,
            response: [
                          'externalId' => $scimProvisioningResult->externalId,
                          'userId'     => $scimProvisioningResult->userId,
                          'state'      => $scimProvisioningResult->state->value,
                          'roles'      => $scimProvisioningResult->roles,
                          'created'    => $scimProvisioningResult->created,
                      ],
            bulkId  : $scimBulkOperation->bulkId,
        );
    }

    /**
     * @param array<string, mixed> $body
     */
    private function provisionData(ScimBulkRequest $scimBulkRequest, array $body, string $externalId) : ProvisionScimUserData
    {
        return new ProvisionScimUserData(
            directoryId   : $scimBulkRequest->directoryId,
            directoryToken: $scimBulkRequest->directoryToken,
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
            if (! is_array(value: $email)) {
                continue;
            }

            if (! is_string(value: $email['value'] ?? null)) {
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
            throw new InvalidArgumentException(message: sprintf('Field [%s] is required.', $field));
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
    private function replaceUser(ScimBulkRequest $scimBulkRequest, ScimBulkOperation $scimBulkOperation, string $externalId) : ScimBulkOperationResult
    {
        $scimProvisioningResult = $this->provisionScimUser->execute(data: $this->provisionData(
            body      : $scimBulkOperation->body,
            externalId: $externalId,
            request   : $scimBulkRequest,
        ));

        return new ScimBulkOperationResult(
            method  : 'PUT',
            path    : '/Users/' . rawurlencode(string: $externalId),
            status  : 200,
            response: [
                          'externalId' => $scimProvisioningResult->externalId,
                          'userId'     => $scimProvisioningResult->userId,
                          'state'      => $scimProvisioningResult->state->value,
                          'roles'      => $scimProvisioningResult->roles,
                          'updated'    => $scimProvisioningResult->updated,
                          'idempotent' => $scimProvisioningResult->idempotent,
                      ],
            bulkId  : $scimBulkOperation->bulkId,
        );
    }

    private function deleteUser(ScimBulkRequest $scimBulkRequest, ScimBulkOperation $scimBulkOperation, string $externalId) : ScimBulkOperationResult
    {
        $this->deleteScimUser->execute(data: new DeleteScimUserData(
                                                 directoryId   : $scimBulkRequest->directoryId,
                                                 directoryToken: $scimBulkRequest->directoryToken,
                                                 externalId    : $externalId,
                                             ));

        return new ScimBulkOperationResult(
            method  : 'DELETE',
            path    : '/Users/' . rawurlencode(string: $externalId),
            status  : 204,
            response: [],
            bulkId  : $scimBulkOperation->bulkId,
        );
    }
}
