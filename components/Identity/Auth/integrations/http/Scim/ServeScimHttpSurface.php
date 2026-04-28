<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Http\Scim;

use Avax\Components\Identity\Auth\Integrations\Headers\ReadBearerToken;
use Avax\Components\Identity\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Components\Identity\Auth\Integrations\Http\JsonHttpResponse;
use Avax\Components\Identity\Auth\System\Auth;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkOperation;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkOperationResult;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ScimGroupProjection;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ScimUserProjection;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimAccountState;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * Publishes a framework-neutral SCIM HTTP surface over the package-owned SCIM runtime.
 */
final readonly class ServeScimHttpSurface
{
    private const string SCIM_CONTENT_TYPE     = 'application/scim+json';
    private const string LIST_RESPONSE_SCHEMA  = 'urn:ietf:params:scim:api:messages:2.0:ListResponse';
    private const string ERROR_SCHEMA          = 'urn:ietf:params:scim:api:messages:2.0:Error';
    private const string USER_SCHEMA           = 'urn:ietf:params:scim:schemas:core:2.0:User';
    private const string GROUP_SCHEMA          = 'urn:ietf:params:scim:schemas:core:2.0:Group';
    private const string USER_EXTENSION_SCHEMA = 'urn:avax:params:scim:schemas:auth:1.0:User';

    public function __construct(
        #[SensitiveParameter] private Auth            $auth,
        #[SensitiveParameter] private ReadBearerToken $readBearerToken = new ReadBearerToken()
    ) {}

    public function execute(HttpEndpointInput $input) : JsonHttpResponse
    {
        $method = strtoupper(string: trim(string: $input->method));
        $path   = $this->normalizePath(path: $input->path);

        try {
            if ($method === 'GET' && $path === '/ServiceProviderConfig') {
                return $this->serviceProviderConfig();
            }

            if ($method === 'GET' && $path === '/ResourceTypes') {
                return $this->resourceTypes();
            }

            if ($method === 'GET' && $path === '/Schemas') {
                return $this->schemas();
            }

            if ($path === '/Users' && $method === 'GET') {
                return $this->listUsers(input: $input);
            }

            if ($path === '/Users' && $method === 'POST') {
                return $this->createUser(input: $input);
            }

            if ($path === '/Groups' && $method === 'GET') {
                return $this->listGroups(input: $input);
            }

            if ($path === '/Bulk' && $method === 'POST') {
                return $this->bulk(input: $input);
            }

            if (preg_match(pattern: '~^/Users/([^/]+)$~', subject: $path, matches: $matches) === 1) {
                $externalId = urldecode(string: $matches[1]);

                return match ($method) {
                    'GET'    => $this->readUser(input: $input, externalId: $externalId),
                    'PUT'    => $this->replaceUser(input: $input, externalId: $externalId),
                    'PATCH'  => $this->patchUser(input: $input, externalId: $externalId),
                    'DELETE' => $this->deleteUser(input: $input, externalId: $externalId),
                    default  => $this->error(statusCode: 405, scimType: 'invalidMethod', detail: 'SCIM method is not supported for this route.'),
                };
            }

            if (preg_match(pattern: '~^/Groups/([^/]+)$~', subject: $path, matches: $matches) === 1) {
                $groupId = urldecode(string: $matches[1]);

                return match ($method) {
                    'GET'   => $this->readGroup(input: $input, groupId: $groupId),
                    default => $this->error(statusCode: 405, scimType: 'invalidMethod', detail: 'SCIM method is not supported for this route.'),
                };
            }
        } catch (ScimFailed $exception) {
            return $this->mapScimFailure(failure: $exception);
        } catch (InvalidArgumentException $exception) {
            return $this->error(statusCode: 400, scimType: 'invalidValue', detail: $exception->getMessage());
        }

        return $this->error(statusCode: 404, scimType: 'notFound', detail: 'SCIM route was not found.');
    }

    private function normalizePath(string $path) : string
    {
        $trimmed    = '/' . trim(string: $path, characters: '/');
        $normalized = preg_replace(pattern: '~^/scim/v2(?=/|$)~', replacement: '', subject: $trimmed);
        $normalized = is_string(value: $normalized) ? $normalized : $trimmed;

        return $normalized === '' ? '/' : $normalized;
    }

    private function serviceProviderConfig() : JsonHttpResponse
    {
        return $this->response(body: [
                                         'schemas'               => ['urn:ietf:params:scim:schemas:core:2.0:ServiceProviderConfig'],
                                         'patch'                 => ['supported' => true],
                                         'bulk'                  => ['supported' => true, 'maxOperations' => 100, 'maxPayloadSize' => 1048576],
                                         'filter'                => ['supported' => true, 'maxResults' => 100],
                                         'changePassword'        => ['supported' => false],
                                         'sort'                  => ['supported' => false],
                                         'etag'                  => ['supported' => false],
                                         'authenticationSchemes' => [[
                                                                         'type'        => 'oauthbearertoken',
                                                                         'name'        => 'OAuth Bearer Token',
                                                                         'description' => 'Directory-scoped bearer token for SCIM runtime access.',
                                                                         'specUri'     => 'https://www.rfc-editor.org/rfc/rfc6750',
                                                                         'primary'     => true,
                                                                     ]],
                                     ]);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function response(array $body) : JsonHttpResponse
    {
        return new JsonHttpResponse(
            statusCode: 200,
            body      : $body,
            headers   : ['Content-Type' => self::SCIM_CONTENT_TYPE]
        );
    }

    private function resourceTypes() : JsonHttpResponse
    {
        return $this->listResponse(resources: [[
                                                   'schemas'          => ['urn:ietf:params:scim:schemas:core:2.0:ResourceType'],
                                                   'id'               => 'User',
                                                   'name'             => 'User',
                                                   'endpoint'         => '/Users',
                                                   'schema'           => self::USER_SCHEMA,
                                                   'schemaExtensions' => [[
                                                                              'schema'   => self::USER_EXTENSION_SCHEMA,
                                                                              'required' => false,
                                                                          ]],
                                               ], [
                                                   'schemas'  => ['urn:ietf:params:scim:schemas:core:2.0:ResourceType'],
                                                   'id'       => 'Group',
                                                   'name'     => 'Group',
                                                   'endpoint' => '/Groups',
                                                   'schema'   => self::GROUP_SCHEMA,
                                               ]]);
    }

    /**
     * @param list<array<string, mixed>> $resources
     */
    private function listResponse(array $resources) : JsonHttpResponse
    {
        return $this->response(body: [
                                         'schemas'      => [self::LIST_RESPONSE_SCHEMA],
                                         'totalResults' => count(value: $resources),
                                         'startIndex'   => 1,
                                         'itemsPerPage' => count(value: $resources),
                                         'Resources'    => $resources,
                                     ]);
    }

    private function schemas() : JsonHttpResponse
    {
        return $this->listResponse(resources: [
                                                  [
                                                      'schemas'     => ['urn:ietf:params:scim:schemas:core:2.0:Schema'],
                                                      'id'          => self::USER_SCHEMA,
                                                      'name'        => 'User',
                                                      'description' => 'Core SCIM user resource owned by the Auth SCIM runtime.',
                                                      'attributes'  => [
                                                          ['name' => 'externalId', 'type' => 'string', 'required' => true, 'multiValued' => false],
                                                          ['name' => 'userName', 'type' => 'string', 'required' => true, 'multiValued' => false],
                                                          ['name' => 'active', 'type' => 'boolean', 'required' => false, 'multiValued' => false],
                                                          ['name' => 'emails', 'type' => 'complex', 'required' => true, 'multiValued' => true],
                                                          ['name' => 'groups', 'type' => 'complex', 'required' => false, 'multiValued' => true],
                                                      ],
                                                  ],
                                                  [
                                                      'schemas'     => ['urn:ietf:params:scim:schemas:core:2.0:Schema'],
                                                      'id'          => self::USER_EXTENSION_SCHEMA,
                                                      'name'        => 'AvaxAuthUser',
                                                      'description' => 'Auth-specific SCIM extension for state and effective role projection.',
                                                      'attributes'  => [
                                                          ['name' => 'state', 'type' => 'string', 'required' => false, 'multiValued' => false],
                                                          ['name' => 'roles', 'type' => 'string', 'required' => false, 'multiValued' => true],
                                                      ],
                                                  ],
                                                  [
                                                      'schemas'     => ['urn:ietf:params:scim:schemas:core:2.0:Schema'],
                                                      'id'          => self::GROUP_SCHEMA,
                                                      'name'        => 'Group',
                                                      'description' => 'Derived SCIM group projection owned by the Auth SCIM runtime.',
                                                      'attributes'  => [
                                                          ['name' => 'displayName', 'type' => 'string', 'required' => true, 'multiValued' => false],
                                                          ['name' => 'members', 'type' => 'complex', 'required' => false, 'multiValued' => true],
                                                      ],
                                                  ],
                                              ]);
    }

    private function listUsers(HttpEndpointInput $input) : JsonHttpResponse
    {
        $directoryId = $this->directoryId(input: $input);
        $this->directoryToken(input: $input);
        $users      = $this->auth->readScimUsers(directoryId: $directoryId);
        $users      = $this->applyFilter(users: $users, filter: $this->stringValue(source: $input->query, field: 'filter'));
        $startIndex = max(1, $this->intValue(source: $input->query, field: 'startIndex') ?? 1);
        $count      = $this->intValue(source: $input->query, field: 'count') ?? count(value: $users);
        $slice      = array_slice(array: $users, offset: $startIndex - 1, length: $count);

        return $this->response(body: [
                                         'schemas'      => [self::LIST_RESPONSE_SCHEMA],
                                         'totalResults' => count(value: $users),
                                         'startIndex'   => $startIndex,
                                         'itemsPerPage' => count(value: $slice),
                                         'Resources'    => array_map(callback: fn (ScimUserProjection $user) : array => $this->userResource(user: $user), array: $slice),
                                     ]);
    }

    private function directoryId(HttpEndpointInput $input) : string
    {
        return $this->requiredString(source: $input->routeParameters + $input->query, field: 'directoryId');
    }

    /**
     * @param array<string, mixed> $source
     */
    private function requiredString(array $source, string $field) : string
    {
        $value = $this->stringValue(source: $source, field: $field);

        if ($value === null || $value === '') {
            throw new InvalidArgumentException(message: "SCIM {$field} is required.");
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function stringValue(array $source, string $field) : string|null
    {
        $value = $source[$field] ?? null;

        return is_scalar(value: $value) ? trim(string: (string) $value) : null;
    }

    private function directoryToken(HttpEndpointInput $input) : string
    {
        $token = $this->readBearerToken->execute(headers: $input->headers, server: $input->server);

        if ($token === null) {
            throw new InvalidArgumentException(message: 'SCIM directory bearer token is required.');
        }

        return $token;
    }

    /**
     * @param list<ScimUserProjection> $users
     *
     * @return list<ScimUserProjection>
     */
    private function applyFilter(array $users, string|null $filter) : array
    {
        if ($filter === null || trim(string: $filter) === '') {
            return $users;
        }

        if (preg_match(pattern: '/^(externalId|userName) eq "([^"]+)"$/', subject: trim(string: $filter), matches: $matches) !== 1) {
            throw new InvalidArgumentException(message: 'Unsupported SCIM filter syntax.');
        }

        $field = $matches[1];
        $value = $matches[2];

        return array_values(array: array_filter(
                                       array   : $users,
                                       callback: static fn (ScimUserProjection $user) : bool => match ($field) {
                                           'externalId' => $user->externalId === $value,
                                           'userName'   => $user->username === $value,
                                       }
                                   ));
    }

    /**
     * @param array<string, mixed> $source
     */
    private function intValue(array $source, string $field) : int|null
    {
        $value = $source[$field] ?? null;

        if (is_int(value: $value)) {
            return $value;
        }

        if (is_string(value: $value) && $value !== '' && ctype_digit(text: $value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function userResource(ScimUserProjection $user) : array
    {
        return [
            'schemas'                   => [self::USER_SCHEMA, self::USER_EXTENSION_SCHEMA],
            'id'                        => $user->externalId,
            'externalId'                => $user->externalId,
            'userName'                  => $user->username,
            'active'                    => $user->state === ScimAccountState::ACTIVE,
            'emails'                    => [[
                                                'value'   => $user->email,
                                                'primary' => true,
                                            ]],
            'groups'                    => array_map(
                callback: static fn (string $group) : array => ['value' => $group, 'display' => $group],
                array   : $user->groups
            ),
            self::USER_EXTENSION_SCHEMA => [
                'state' => $user->state->value,
                'roles' => $user->roles,
            ],
            'meta'                      => [
                'resourceType' => 'User',
            ],
        ];
    }

    private function createUser(HttpEndpointInput $input) : JsonHttpResponse
    {
        $result = $this->auth->provisionScimUser(data: $this->provisionData(input: $input));
        $user   = $this->findUser(directoryId: $this->directoryId(input: $input), externalId: $result->externalId);

        return new JsonHttpResponse(
            statusCode: 201,
            body      : $user !== null ? $this->userResource(user: $user) : [
                            'externalId'                => $result->externalId,
                            self::USER_EXTENSION_SCHEMA => ['state' => $result->state->value, 'roles' => $result->roles],
                        ],
            headers   : [
                            'Content-Type' => self::SCIM_CONTENT_TYPE,
                            'Location'     => '/Users/' . rawurlencode(string: $result->externalId),
                        ]
        );
    }

    private function provisionData(HttpEndpointInput $input, string|null $forcedExternalId = null) : ProvisionScimUserData
    {
        $body        = $input->body;
        $directoryId = $this->directoryId(input: $input);
        $existing    = $forcedExternalId !== null ? $this->findUser(directoryId: $directoryId, externalId: $forcedExternalId) : null;
        $externalId  = $forcedExternalId ?? $this->requiredString(source: $body, field: 'externalId');

        return new ProvisionScimUserData(
            directoryId   : $directoryId,
            directoryToken: $this->directoryToken(input: $input),
            externalId    : $externalId,
            email         : $this->email(body: $body, fallback: $existing?->email),
            username      : $this->username(body: $body, fallback: $existing?->username),
            groups        : $this->groups(body: $body, fallback: $existing !== null ? $existing->groups : []),
            state         : $this->state(body: $body, fallback: $existing !== null ? $existing->state : ScimAccountState::ACTIVE)
        );
    }

    private function findUser(string $directoryId, string $externalId) : ScimUserProjection|null
    {
        foreach ($this->auth->readScimUsers(directoryId: $directoryId) as $user) {
            if ($user->externalId === $externalId) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function email(array $body, string|null $fallback = null) : string
    {
        $emails = $body['emails'] ?? null;

        if (is_array(value: $emails)) {
            foreach ($emails as $candidate) {
                if (is_array(value: $candidate) && is_scalar(value: $candidate['value'] ?? null)) {
                    return trim(string: (string) $candidate['value']);
                }
            }
        }

        if (is_scalar(value: $body['email'] ?? null)) {
            return trim(string: (string) $body['email']);
        }

        if ($fallback !== null && trim(string: $fallback) !== '') {
            return $fallback;
        }

        throw new InvalidArgumentException(message: 'SCIM email is required.');
    }

    /**
     * @param array<string, mixed> $body
     */
    private function username(array $body, string|null $fallback = null) : string
    {
        if (is_scalar(value: $body['userName'] ?? null) || is_scalar(value: $body['username'] ?? null)) {
            return trim(string: (string) ($body['userName'] ?? $body['username']));
        }

        if ($fallback !== null && trim(string: $fallback) !== '') {
            return $fallback;
        }

        throw new InvalidArgumentException(message: 'SCIM userName is required.');
    }

    /**
     * @param array<string, mixed> $body
     * @param list<string>         $fallback
     *
     * @return list<string>
     */
    private function groups(array $body, array $fallback) : array
    {
        if (! array_key_exists(key: 'groups', array: $body)) {
            return $fallback;
        }

        $groups = $body['groups'];

        if (! is_array(value: $groups)) {
            throw new InvalidArgumentException(message: 'SCIM groups must be an array.');
        }

        $resolved = [];

        foreach ($groups as $candidate) {
            if (is_scalar(value: $candidate)) {
                $resolved[] = trim(string: (string) $candidate);
                continue;
            }

            if (is_array(value: $candidate) && is_scalar(value: $candidate['value'] ?? null)) {
                $resolved[] = trim(string: (string) $candidate['value']);
            }
        }

        return array_values(array: array_filter(array: $resolved, callback: static fn (string $group) : bool => $group !== ''));
    }

    /**
     * @param array<string, mixed> $body
     */
    private function state(array $body, ScimAccountState $fallback) : ScimAccountState
    {
        if (is_scalar(value: $body['state'] ?? null)) {
            $state = ScimAccountState::tryFrom(
                value: strtolower(string: trim(string: (string) $body['state']))
            );

            if ($state !== null) {
                return $state;
            }
        }

        if (is_bool(value: $body['active'] ?? null)) {
            return $body['active'] === true ? ScimAccountState::ACTIVE : ScimAccountState::SUSPENDED;
        }

        return $fallback;
    }

    private function listGroups(HttpEndpointInput $input) : JsonHttpResponse
    {
        $directoryId = $this->directoryId(input: $input);
        $this->directoryToken(input: $input);
        $groups = $this->auth->readScimGroups(directoryId: $directoryId);

        return $this->listResponse(resources: array_map(
                                                  callback: fn (ScimGroupProjection $group) : array => $this->groupResource(group: $group),
                                                  array   : $groups
                                              ));
    }

    /**
     * @return array<string, mixed>
     */
    private function groupResource(ScimGroupProjection $group) : array
    {
        return [
            'schemas'     => [self::GROUP_SCHEMA],
            'id'          => $group->groupId,
            'displayName' => $group->displayName,
            'members'     => array_map(
                callback: static fn ($member) : array => [
                    'value'   => $member->externalId,
                    'display' => $member->email,
                    '$ref'    => '/Users/' . rawurlencode(string: $member->externalId),
                ],
                array   : $group->members
            ),
        ];
    }

    private function bulk(HttpEndpointInput $input) : JsonHttpResponse
    {
        $request  = new ScimBulkRequest(
            directoryId   : $this->directoryId(input: $input),
            directoryToken: $this->directoryToken(input: $input),
            operations    : $this->bulkOperations(body: $input->body)
        );
        $response = $this->auth->runScimBulk(data: $request);

        return $this->response(body: [
                                         'schemas'    => ['urn:ietf:params:scim:api:messages:2.0:BulkResponse'],
                                         'Operations' => array_map(
                                             callback: static fn (ScimBulkOperationResult $result) : array => [
                                                 'method'   => $result->method,
                                                 'path'     => $result->path,
                                                 'status'   => (string) $result->status,
                                                 'bulkId'   => $result->bulkId,
                                                 'response' => $result->response,
                                             ],
                                             array   : $response->operations
                                         ),
                                     ]);
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return list<ScimBulkOperation>
     */
    private function bulkOperations(array $body) : array
    {
        $operations = $body['Operations'] ?? [];

        if (! is_array(value: $operations)) {
            throw new InvalidArgumentException(message: 'SCIM bulk Operations must be an array.');
        }

        $resolved = [];

        foreach ($operations as $operation) {
            if (! is_array(value: $operation)) {
                continue;
            }

            $method = strtoupper(string: trim(string: (string) ($operation['method'] ?? '')));
            $path   = trim(string: (string) ($operation['path'] ?? ''));

            if ($method === '' || $path === '') {
                continue;
            }

            $resolved[] = new ScimBulkOperation(
                method: $method,
                path  : $path,
                body  : is_array(value: $operation['data'] ?? null) ? $operation['data'] : [],
                bulkId: is_scalar(value: $operation['bulkId'] ?? null) ? trim(string: (string) $operation['bulkId']) : null
            );
        }

        return $resolved;
    }

    private function readUser(HttpEndpointInput $input, string $externalId) : JsonHttpResponse
    {
        $this->directoryToken(input: $input);
        $user = $this->findUser(directoryId: $this->directoryId(input: $input), externalId: $externalId);

        if ($user === null) {
            return $this->error(statusCode: 404, scimType: 'notFound', detail: 'SCIM user was not found.');
        }

        return $this->response(body: $this->userResource(user: $user));
    }

    private function error(int $statusCode, string $scimType, string $detail) : JsonHttpResponse
    {
        return new JsonHttpResponse(
            statusCode: $statusCode,
            body      : [
                            'schemas'  => [self::ERROR_SCHEMA],
                            'scimType' => $scimType,
                            'detail'   => $detail,
                            'status'   => (string) $statusCode,
                        ],
            headers   : ['Content-Type' => self::SCIM_CONTENT_TYPE]
        );
    }

    private function replaceUser(HttpEndpointInput $input, string $externalId) : JsonHttpResponse
    {
        $this->auth->provisionScimUser(data: $this->provisionData(input: $input, forcedExternalId: $externalId));
        $user = $this->findUser(directoryId: $this->directoryId(input: $input), externalId: $externalId);

        return $this->response(body: $user !== null ? $this->userResource(user: $user) : []);
    }

    private function patchUser(HttpEndpointInput $input, string $externalId) : JsonHttpResponse
    {
        $current = $this->findUser(directoryId: $this->directoryId(input: $input), externalId: $externalId);

        if ($current === null) {
            return $this->error(statusCode: 404, scimType: 'notFound', detail: 'SCIM user was not found.');
        }

        $patchedBody  = $this->applyPatch(body: $input->body, current: $current);
        $patchedInput = new HttpEndpointInput(
            method         : 'PUT',
            path           : $input->path,
            headers        : $input->headers,
            query          : $input->query,
            routeParameters: $input->routeParameters,
            body           : $patchedBody,
            server         : $input->server
        );

        return $this->replaceUser(input: $patchedInput, externalId: $externalId);
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    private function applyPatch(array $body, ScimUserProjection $current) : array
    {
        $patched = [
            'externalId' => $current->externalId,
            'userName'   => $current->username,
            'emails'     => [['value' => $current->email, 'primary' => true]],
            'groups'     => array_map(callback: static fn (string $group) : array => ['value' => $group, 'display' => $group], array: $current->groups),
            'state'      => $current->state->value,
            'active'     => $current->state === ScimAccountState::ACTIVE,
        ];

        foreach (($body['Operations'] ?? []) as $operation) {
            if (! is_array(value: $operation)) {
                continue;
            }

            $op    = strtoupper(string: trim(string: (string) ($operation['op'] ?? '')));
            $path  = trim(string: (string) ($operation['path'] ?? ''));
            $value = $operation['value'] ?? null;

            if ($path === '') {
                if (($op === 'ADD' || $op === 'REPLACE') && is_array(value: $value)) {
                    $patched = array_replace($patched, $value);
                }

                continue;
            }

            if ($op === 'REMOVE' && $path === 'groups') {
                $patched['groups'] = [];
                continue;
            }

            if (! in_array(needle: $op, haystack: ['ADD', 'REPLACE'], strict: true)) {
                continue;
            }

            match ($path) {
                'userName'                                      => $patched['userName'] = is_scalar(value: $value) ? (string) $value : $patched['userName'],
                'emails'                                        => $patched['emails'] = is_array(value: $value) ? $value : $patched['emails'],
                'groups'                                        => $patched['groups'] = is_array(value: $value) ? $value : $patched['groups'],
                'active'                                        => $this->applyActiveStatePatch(patched: $patched, value: $value),
                'state', self::USER_EXTENSION_SCHEMA . ':state' => $patched['state'] = is_scalar(value: $value) ? (string) $value : $patched['state'],
                default                                         => null,
            };
        }

        return $patched;
    }

    /**
     * @param array<string, mixed> $patched
     */
    private function applyActiveStatePatch(array &$patched, mixed $value) : void
    {
        if (! is_bool(value: $value)) {
            return;
        }

        $patched['active'] = $value;
        $patched['state']  = $value ? ScimAccountState::ACTIVE->value : ScimAccountState::SUSPENDED->value;
    }

    private function deleteUser(HttpEndpointInput $input, string $externalId) : JsonHttpResponse
    {
        $this->auth->deleteScimUser(data: new DeleteScimUserData(
                                              directoryId   : $this->directoryId(input: $input),
                                              directoryToken: $this->directoryToken(input: $input),
                                              externalId    : $externalId
                                          ));

        return new JsonHttpResponse(
            statusCode: 204,
            body      : [],
            headers   : ['Content-Type' => self::SCIM_CONTENT_TYPE]
        );
    }

    private function readGroup(HttpEndpointInput $input, string $groupId) : JsonHttpResponse
    {
        $this->directoryToken(input: $input);

        foreach ($this->auth->readScimGroups(directoryId: $this->directoryId(input: $input)) as $group) {
            if ($group->groupId === $groupId) {
                return $this->response(body: $this->groupResource(group: $group));
            }
        }

        return $this->error(statusCode: 404, scimType: 'notFound', detail: 'SCIM group was not found.');
    }

    private function mapScimFailure(ScimFailed $failure) : JsonHttpResponse
    {
        return match ($failure->getMessage()) {
            'Invalid SCIM directory token.'                                                 => $this->error(statusCode: 401, scimType: 'invalidToken', detail: $failure->getMessage()),
            'SCIM directory is not registered.', 'SCIM provisioned identity was not found.' => $this->error(statusCode: 404, scimType: 'notFound', detail: $failure->getMessage()),
            'SCIM group-to-role mapping is invalid.'                                        => $this->error(statusCode: 422, scimType: 'invalidValue', detail: $failure->getMessage()),
            default                                                                         => $this->error(statusCode: 400, scimType: 'invalidValue', detail: $failure->getMessage()),
        };
    }
}
