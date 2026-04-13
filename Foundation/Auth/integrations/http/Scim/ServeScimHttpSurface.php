<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http\Scim;

use Avax\Auth\Integrations\Headers\ReadBearerToken;
use Avax\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Auth\Integrations\Http\JsonHttpResponse;
use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Capability\Scim\ScimAccountState;
use Avax\Auth\System\Flow\Scim\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Flow\Scim\ReadUsers\ScimUserProjection;
use Avax\Auth\System\Flow\Scim\ScimFailed;
use InvalidArgumentException;

/**
 * Publishes a framework-neutral SCIM HTTP surface over the package-owned SCIM runtime.
 */
final readonly class ServeScimHttpSurface
{
    private const SCIM_CONTENT_TYPE = 'application/scim+json';
    private const LIST_RESPONSE_SCHEMA = 'urn:ietf:params:scim:api:messages:2.0:ListResponse';
    private const ERROR_SCHEMA = 'urn:ietf:params:scim:api:messages:2.0:Error';
    private const USER_SCHEMA = 'urn:ietf:params:scim:schemas:core:2.0:User';
    private const USER_EXTENSION_SCHEMA = 'urn:avax:params:scim:schemas:auth:1.0:User';

    public function __construct(
        private AuthInterface $auth,
        private ReadBearerToken $readBearerToken = new ReadBearerToken()
    ) {}

    public function execute(HttpEndpointInput $input) : JsonHttpResponse
    {
        $method = strtoupper(trim($input->method));
        $path = $this->normalizePath($input->path);

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
                return $this->listUsers($input);
            }

            if ($path === '/Users' && $method === 'POST') {
                return $this->createUser($input);
            }

            if (preg_match('~^/Users/([^/]+)$~', $path, $matches) === 1) {
                $externalId = urldecode($matches[1]);

                return match ($method) {
                    'GET' => $this->readUser($input, $externalId),
                    'PUT' => $this->replaceUser($input, $externalId),
                    'PATCH' => $this->patchUser($input, $externalId),
                    'DELETE' => $this->deleteUser($input, $externalId),
                    default => $this->error(405, 'invalidMethod', 'SCIM method is not supported for this route.'),
                };
            }
        } catch (ScimFailed $exception) {
            return $this->mapScimFailure($exception);
        } catch (InvalidArgumentException $exception) {
            return $this->error(400, 'invalidValue', $exception->getMessage());
        }

        return $this->error(404, 'notFound', 'SCIM route was not found.');
    }

    private function serviceProviderConfig() : JsonHttpResponse
    {
        return $this->response(200, [
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:ServiceProviderConfig'],
            'patch' => ['supported' => true],
            'bulk' => ['supported' => false, 'maxOperations' => 0, 'maxPayloadSize' => 0],
            'filter' => ['supported' => true, 'maxResults' => 100],
            'changePassword' => ['supported' => false],
            'sort' => ['supported' => false],
            'etag' => ['supported' => false],
            'authenticationSchemes' => [[
                'type' => 'oauthbearertoken',
                'name' => 'OAuth Bearer Token',
                'description' => 'Directory-scoped bearer token for SCIM runtime access.',
                'specUri' => 'https://www.rfc-editor.org/rfc/rfc6750',
                'primary' => true,
            ]],
        ]);
    }

    private function resourceTypes() : JsonHttpResponse
    {
        return $this->listResponse([[
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:ResourceType'],
            'id' => 'User',
            'name' => 'User',
            'endpoint' => '/Users',
            'schema' => self::USER_SCHEMA,
            'schemaExtensions' => [[
                'schema' => self::USER_EXTENSION_SCHEMA,
                'required' => false,
            ]],
        ]]);
    }

    private function schemas() : JsonHttpResponse
    {
        return $this->listResponse([
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:Schema'],
                'id' => self::USER_SCHEMA,
                'name' => 'User',
                'description' => 'Core SCIM user resource owned by the Auth SCIM runtime.',
                'attributes' => [
                    ['name' => 'externalId', 'type' => 'string', 'required' => true, 'multiValued' => false],
                    ['name' => 'userName', 'type' => 'string', 'required' => true, 'multiValued' => false],
                    ['name' => 'active', 'type' => 'boolean', 'required' => false, 'multiValued' => false],
                    ['name' => 'emails', 'type' => 'complex', 'required' => true, 'multiValued' => true],
                    ['name' => 'groups', 'type' => 'complex', 'required' => false, 'multiValued' => true],
                ],
            ],
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:Schema'],
                'id' => self::USER_EXTENSION_SCHEMA,
                'name' => 'AvaxAuthUser',
                'description' => 'Auth-specific SCIM extension for state and effective role projection.',
                'attributes' => [
                    ['name' => 'state', 'type' => 'string', 'required' => false, 'multiValued' => false],
                    ['name' => 'roles', 'type' => 'string', 'required' => false, 'multiValued' => true],
                ],
            ],
        ]);
    }

    private function listUsers(HttpEndpointInput $input) : JsonHttpResponse
    {
        $directoryId = $this->directoryId($input);
        $this->directoryToken($input);
        $users = $this->auth->readScimUsers($directoryId);
        $users = $this->applyFilter($users, $this->stringValue($input->query, 'filter'));
        $startIndex = max(1, $this->intValue($input->query, 'startIndex') ?? 1);
        $count = $this->intValue($input->query, 'count') ?? count($users);
        $slice = array_slice($users, $startIndex - 1, $count);

        return $this->response(200, [
            'schemas' => [self::LIST_RESPONSE_SCHEMA],
            'totalResults' => count($users),
            'startIndex' => $startIndex,
            'itemsPerPage' => count($slice),
            'Resources' => array_map(fn (ScimUserProjection $user) : array => $this->userResource($user), $slice),
        ]);
    }

    private function readUser(HttpEndpointInput $input, string $externalId) : JsonHttpResponse
    {
        $this->directoryToken($input);
        $user = $this->findUser($this->directoryId($input), $externalId);

        if ($user === null) {
            return $this->error(404, 'notFound', 'SCIM user was not found.');
        }

        return $this->response(200, $this->userResource($user));
    }

    private function createUser(HttpEndpointInput $input) : JsonHttpResponse
    {
        $result = $this->auth->provisionScimUser($this->provisionData($input));
        $user = $this->findUser($this->directoryId($input), $result->externalId);

        return new JsonHttpResponse(
            statusCode: 201,
            body      : $user !== null ? $this->userResource($user) : [
                'externalId' => $result->externalId,
                self::USER_EXTENSION_SCHEMA => ['state' => $result->state->value, 'roles' => $result->roles],
            ],
            headers   : [
                'Content-Type' => self::SCIM_CONTENT_TYPE,
                'Location' => '/Users/' . rawurlencode($result->externalId),
            ]
        );
    }

    private function replaceUser(HttpEndpointInput $input, string $externalId) : JsonHttpResponse
    {
        $this->auth->provisionScimUser($this->provisionData($input, $externalId));
        $user = $this->findUser($this->directoryId($input), $externalId);

        return $this->response(200, $user !== null ? $this->userResource($user) : []);
    }

    private function patchUser(HttpEndpointInput $input, string $externalId) : JsonHttpResponse
    {
        $current = $this->findUser($this->directoryId($input), $externalId);

        if ($current === null) {
            return $this->error(404, 'notFound', 'SCIM user was not found.');
        }

        $patchedBody = $this->applyPatch($input->body, $current);
        $patchedInput = new HttpEndpointInput(
            method         : 'PUT',
            path           : $input->path,
            headers        : $input->headers,
            query          : $input->query,
            routeParameters: $input->routeParameters,
            body           : $patchedBody,
            server         : $input->server
        );

        return $this->replaceUser($patchedInput, $externalId);
    }

    private function deleteUser(HttpEndpointInput $input, string $externalId) : JsonHttpResponse
    {
        $this->auth->deleteScimUser(new DeleteScimUserData(
            directoryId    : $this->directoryId($input),
            directoryToken : $this->directoryToken($input),
            externalId     : $externalId
        ));

        return new JsonHttpResponse(
            statusCode: 204,
            body      : [],
            headers   : ['Content-Type' => self::SCIM_CONTENT_TYPE]
        );
    }

    /**
     * @param list<ScimUserProjection> $users
     * @return list<ScimUserProjection>
     */
    private function applyFilter(array $users, string|null $filter) : array
    {
        if ($filter === null || trim($filter) === '') {
            return $users;
        }

        if (preg_match('/^(externalId|userName) eq "([^"]+)"$/', trim($filter), $matches) !== 1) {
            throw new InvalidArgumentException('Unsupported SCIM filter syntax.');
        }

        $field = $matches[1];
        $value = $matches[2];

        return array_values(array_filter(
            $users,
            static fn (ScimUserProjection $user) : bool => match ($field) {
                'externalId' => $user->externalId === $value,
                'userName' => $user->username === $value,
            }
        ));
    }

    private function findUser(string $directoryId, string $externalId) : ScimUserProjection|null
    {
        foreach ($this->auth->readScimUsers($directoryId) as $user) {
            if ($user->externalId === $externalId) {
                return $user;
            }
        }

        return null;
    }

    private function provisionData(HttpEndpointInput $input, string|null $forcedExternalId = null) : ProvisionScimUserData
    {
        $body = $input->body;
        $directoryId = $this->directoryId($input);
        $existing = $forcedExternalId !== null ? $this->findUser($directoryId, $forcedExternalId) : null;
        $externalId = $forcedExternalId ?? $this->requiredString($body, 'externalId');

        return new ProvisionScimUserData(
            directoryId    : $directoryId,
            directoryToken : $this->directoryToken($input),
            externalId     : $externalId,
            email          : $this->email($body, $existing?->email),
            username       : $this->username($body, $existing?->username),
            groups         : $this->groups($body, $existing?->groups ?? []),
            state          : $this->state($body, $existing?->state ?? ScimAccountState::ACTIVE)
        );
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    private function applyPatch(array $body, ScimUserProjection $current) : array
    {
        $patched = [
            'externalId' => $current->externalId,
            'userName' => $current->username,
            'emails' => [['value' => $current->email, 'primary' => true]],
            'groups' => array_map(static fn (string $group) : array => ['value' => $group, 'display' => $group], $current->groups),
            'state' => $current->state->value,
            'active' => $current->state === ScimAccountState::ACTIVE,
        ];

        foreach (($body['Operations'] ?? []) as $operation) {
            if (! is_array($operation)) {
                continue;
            }

            $op = strtoupper(trim((string) ($operation['op'] ?? '')));
            $path = trim((string) ($operation['path'] ?? ''));
            $value = $operation['value'] ?? null;

            if ($path === '') {
                if (($op === 'ADD' || $op === 'REPLACE') && is_array($value)) {
                    $patched = array_replace($patched, $value);
                }

                continue;
            }

            if ($op === 'REMOVE' && $path === 'groups') {
                $patched['groups'] = [];
                continue;
            }

            if (! in_array($op, ['ADD', 'REPLACE'], true)) {
                continue;
            }

            match ($path) {
                'userName' => $patched['userName'] = is_scalar($value) ? (string) $value : $patched['userName'],
                'emails' => $patched['emails'] = is_array($value) ? $value : $patched['emails'],
                'groups' => $patched['groups'] = is_array($value) ? $value : $patched['groups'],
                'active' => $this->applyActiveStatePatch($patched, $value),
                'state', self::USER_EXTENSION_SCHEMA . ':state' => $patched['state'] = is_scalar($value) ? (string) $value : $patched['state'],
                default => null,
            };
        }

        return $patched;
    }

    /**
     * @param array<string, mixed> $patched
     */
    private function applyActiveStatePatch(array &$patched, mixed $value) : void
    {
        if (! is_bool($value)) {
            return;
        }

        $patched['active'] = $value;
        $patched['state'] = $value ? ScimAccountState::ACTIVE->value : ScimAccountState::SUSPENDED->value;
    }

    /**
     * @return array<string, mixed>
     */
    private function userResource(ScimUserProjection $user) : array
    {
        return [
            'schemas' => [self::USER_SCHEMA, self::USER_EXTENSION_SCHEMA],
            'id' => $user->externalId,
            'externalId' => $user->externalId,
            'userName' => $user->username,
            'active' => $user->state === ScimAccountState::ACTIVE,
            'emails' => [[
                'value' => $user->email,
                'primary' => true,
            ]],
            'groups' => array_map(
                static fn (string $group) : array => ['value' => $group, 'display' => $group],
                $user->groups
            ),
            self::USER_EXTENSION_SCHEMA => [
                'state' => $user->state->value,
                'roles' => $user->roles,
            ],
            'meta' => [
                'resourceType' => 'User',
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $resources
     */
    private function listResponse(array $resources) : JsonHttpResponse
    {
        return $this->response(200, [
            'schemas' => [self::LIST_RESPONSE_SCHEMA],
            'totalResults' => count($resources),
            'startIndex' => 1,
            'itemsPerPage' => count($resources),
            'Resources' => $resources,
        ]);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function response(int $statusCode, array $body) : JsonHttpResponse
    {
        return new JsonHttpResponse(
            statusCode: $statusCode,
            body      : $body,
            headers   : ['Content-Type' => self::SCIM_CONTENT_TYPE]
        );
    }

    private function mapScimFailure(ScimFailed $failure) : JsonHttpResponse
    {
        return match ($failure->getMessage()) {
            'Invalid SCIM directory token.' => $this->error(401, 'invalidToken', $failure->getMessage()),
            'SCIM directory is not registered.', 'SCIM provisioned identity was not found.' => $this->error(404, 'notFound', $failure->getMessage()),
            'SCIM group-to-role mapping is invalid.' => $this->error(422, 'invalidValue', $failure->getMessage()),
            default => $this->error(400, 'invalidValue', $failure->getMessage()),
        };
    }

    private function error(int $statusCode, string $scimType, string $detail) : JsonHttpResponse
    {
        return new JsonHttpResponse(
            statusCode: $statusCode,
            body      : [
                'schemas' => [self::ERROR_SCHEMA],
                'scimType' => $scimType,
                'detail' => $detail,
                'status' => (string) $statusCode,
            ],
            headers   : ['Content-Type' => self::SCIM_CONTENT_TYPE]
        );
    }

    private function directoryId(HttpEndpointInput $input) : string
    {
        return $this->requiredString($input->routeParameters + $input->query, 'directoryId');
    }

    private function directoryToken(HttpEndpointInput $input) : string
    {
        $token = $this->readBearerToken->execute($input->headers, $input->server);

        if ($token === null) {
            throw new InvalidArgumentException('SCIM directory bearer token is required.');
        }

        return $token;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function email(array $body, string|null $fallback = null) : string
    {
        $emails = $body['emails'] ?? null;

        if (is_array($emails)) {
            foreach ($emails as $candidate) {
                if (is_array($candidate) && is_scalar($candidate['value'] ?? null)) {
                    return trim((string) $candidate['value']);
                }
            }
        }

        if (is_scalar($body['email'] ?? null)) {
            return trim((string) $body['email']);
        }

        if ($fallback !== null && trim($fallback) !== '') {
            return $fallback;
        }

        throw new InvalidArgumentException('SCIM email is required.');
    }

    /**
     * @param array<string, mixed> $body
     */
    private function username(array $body, string|null $fallback = null) : string
    {
        if (is_scalar($body['userName'] ?? null)) {
            return trim((string) $body['userName']);
        }

        if (is_scalar($body['username'] ?? null)) {
            return trim((string) $body['username']);
        }

        if ($fallback !== null && trim($fallback) !== '') {
            return $fallback;
        }

        throw new InvalidArgumentException('SCIM userName is required.');
    }

    /**
     * @param array<string, mixed> $body
     * @param list<string> $fallback
     * @return list<string>
     */
    private function groups(array $body, array $fallback) : array
    {
        if (! array_key_exists('groups', $body)) {
            return $fallback;
        }

        $groups = $body['groups'];

        if (! is_array($groups)) {
            throw new InvalidArgumentException('SCIM groups must be an array.');
        }

        $resolved = [];

        foreach ($groups as $candidate) {
            if (is_scalar($candidate)) {
                $resolved[] = trim((string) $candidate);
                continue;
            }

            if (is_array($candidate) && is_scalar($candidate['value'] ?? null)) {
                $resolved[] = trim((string) $candidate['value']);
            }
        }

        return array_values(array_filter($resolved, static fn (string $group) : bool => $group !== ''));
    }

    /**
     * @param array<string, mixed> $body
     */
    private function state(array $body, ScimAccountState $fallback) : ScimAccountState
    {
        if (is_scalar($body['state'] ?? null)) {
            $state = ScimAccountState::tryFrom(strtolower(trim((string) $body['state'])));

            if ($state !== null) {
                return $state;
            }
        }

        if (is_bool($body['active'] ?? null)) {
            return $body['active'] === true ? ScimAccountState::ACTIVE : ScimAccountState::SUSPENDED;
        }

        return $fallback;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function requiredString(array $source, string $field) : string
    {
        $value = $this->stringValue($source, $field);

        if ($value === null || $value === '') {
            throw new InvalidArgumentException("SCIM {$field} is required.");
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function stringValue(array $source, string $field) : string|null
    {
        $value = $source[$field] ?? null;

        return is_scalar($value) ? trim((string) $value) : null;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function intValue(array $source, string $field) : int|null
    {
        $value = $source[$field] ?? null;

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '' && ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }

    private function normalizePath(string $path) : string
    {
        $trimmed = '/' . trim($path, '/');
        $normalized = preg_replace('~^/scim/v2(?=/|$)~', '', $trimmed);
        $normalized = is_string($normalized) ? $normalized : $trimmed;

        return $normalized === '' ? '/' : $normalized;
    }
}
