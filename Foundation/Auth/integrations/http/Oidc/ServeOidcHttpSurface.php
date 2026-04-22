<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http\Oidc;

use Avax\Auth\Integrations\Headers\ReadBearerToken;
use Avax\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Auth\Integrations\Http\JsonHttpResponse;
use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClientData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientType;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthGrantType;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthTokenEndpointAuthMethod;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\PkceMethod;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutData as OidcLogoutData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData;
use SensitiveParameter;
use Throwable;

/**
 * Publishes the kernel-owned OIDC discovery, JWKS, and userinfo HTTP surface.
 */
final readonly class ServeOidcHttpSurface
{
    public function __construct(
        #[SensitiveParameter] private AuthInterface   $auth,
        #[SensitiveParameter] private ReadBearerToken $readBearerToken = new ReadBearerToken()
    )
    {
    }

    public function execute(HttpEndpointInput $input) : JsonHttpResponse
    {
        $metadata     = $this->auth->readOidcProviderMetadata();
        $path         = $this->normalizePath(path: $input->path);
        $method       = strtoupper(trim($input->method));
        $jwksPath     = $this->normalizePath(path: (string) parse_url($metadata->jsonWebKeySetUri, PHP_URL_PATH));
        $userInfoPath = $this->normalizePath(path: (string) parse_url($metadata->userInfoEndpoint, PHP_URL_PATH));

        if ($method === 'GET' && $path === '/.well-known/openid-configuration') {
            return new JsonHttpResponse(
                statusCode: 200,
                body      : [
                                'issuer'                                              => $metadata->issuer,
                                'authorization_endpoint'                              => $metadata->authorizationEndpoint,
                                'token_endpoint'                                      => $metadata->tokenEndpoint,
                                'registration_endpoint'                               => $metadata->registrationEndpoint,
                                'userinfo_endpoint'                                   => $metadata->userInfoEndpoint,
                                'jwks_uri'                                            => $metadata->jsonWebKeySetUri,
                                'scopes_supported'                                    => $metadata->scopesSupported,
                                'response_types_supported'                            => $metadata->responseTypesSupported,
                                'grant_types_supported'                               => $metadata->grantTypesSupported,
                                'subject_types_supported'                             => $metadata->subjectTypesSupported,
                                'id_token_signing_alg_values_supported'               => $metadata->idTokenSigningAlgValuesSupported,
                                'code_challenge_methods_supported'                    => $metadata->codeChallengeMethodsSupported,
                                'end_session_endpoint'                                => $metadata->endSessionEndpoint,
                                'pushed_authorization_request_endpoint'               => $metadata->pushedAuthorizationRequestEndpoint,
                                'frontchannel_logout_supported'                       => $metadata->frontChannelLogoutSupported,
                                'backchannel_logout_supported'                        => $metadata->backChannelLogoutSupported,
                                'backchannel_logout_session_supported'                => $metadata->backChannelLogoutSessionSupported,
                                'request_object_signing_alg_values_supported'         => $metadata->requestObjectSigningAlgValuesSupported,
                                'authorization_response_signing_alg_values_supported' => $metadata->authorizationResponseSigningAlgValuesSupported,
                            ],
                headers   : [
                                'Content-Type'  => 'application/json',
                                'Cache-Control' => 'public, max-age=300',
                            ]
            );
        }

        if ($method === 'GET' && $path === $jwksPath) {
            $jsonWebKeySet = $this->auth->readOidcJsonWebKeySet();

            return new JsonHttpResponse(
                statusCode: 200,
                body      : [
                                'keys' => array_map(
                                    static fn ($key) : array => [
                                        'kty' => $key->keyType,
                                        'kid' => $key->keyId,
                                        'alg' => $key->algorithm,
                                        'use' => $key->use,
                                        'n'   => $key->modulus,
                                        'e'   => $key->exponent,
                                    ],
                                    $jsonWebKeySet->keys
                                ),
                            ],
                headers   : [
                                'Content-Type'  => 'application/jwk-set+json',
                                'Cache-Control' => 'public, max-age=300',
                            ]
            );
        }

        if (in_array($method, ['GET', 'POST'], true) && $path === $userInfoPath) {
            $accessToken = $this->readBearerToken->execute(headers: $input->headers, server: $input->server);

            if ($accessToken === null) {
                return $this->error(statusCode: 401, errorCode: 'invalid_token', message: 'Bearer access token is required for OIDC userinfo.');
            }

            try {
                $userInfo = $this->auth->readOidcUserInfo(accessToken: $accessToken);
            } catch (Throwable) {
                return $this->error(statusCode: 401, errorCode: 'invalid_token', message: 'Active access token is required for OIDC userinfo.');
            }

            return new JsonHttpResponse(
                statusCode: 200,
                body      : $userInfo->claims,
                headers   : [
                                'Content-Type'  => 'application/json',
                                'Cache-Control' => 'no-store',
                            ]
            );
        }

        if ($method === 'POST' && $path === '/oauth/par') {
            $request = $this->auth->pushOidcAuthorizationRequest(data: new PushAuthorizationRequestData(
                                                                           clientId           : $this->readString(input: $input, keys: ['client_id', 'clientId']) ?? '',
                                                                           redirectUri        : $this->readString(input: $input, keys: ['redirect_uri', 'redirectUri']) ?? '',
                                                                           scopes             : $this->readScopes(input: $input),
                                                                           state              : $this->readString(input: $input, keys: ['state']),
                                                                           nonce              : $this->readString(input: $input, keys: ['nonce']),
                                                                           requestObjectJwt   : $this->readString(input: $input, keys: ['request']),
                                                                           clientSecret       : $this->readString(input: $input, keys: ['client_secret', 'clientSecret']),
                                                                           codeChallenge      : $this->readString(input: $input, keys: ['code_challenge', 'codeChallenge']),
                                                                           codeChallengeMethod: $this->readPkceMethod(input: $input),
                                                                           ipAddress          : $this->readString(input: $input, keys: ['ip_address', 'ipAddress']) ?? $this->readServerValue(server: $input->server, name: 'REMOTE_ADDR'),
                                                                           userAgent          : $this->readString(input: $input, keys: ['user_agent', 'userAgent']) ?? $this->readServerValue(server: $input->server, name: 'HTTP_USER_AGENT')
                                                                       ));

            return new JsonHttpResponse(
                statusCode: 201,
                body      : [
                                'request_uri' => $request->requestUri,
                                'expires_in'  => max(0, $request->expiresAt->getTimestamp() - time()),
                            ],
                headers   : [
                                'Content-Type'  => 'application/json',
                                'Cache-Control' => 'no-store',
                            ]
            );
        }

        if ($method === 'POST' && $path === '/oidc/register') {
            $registered = $this->auth->registerOAuthClient(data: new RegisterClientData(
                                                                     name                           : $this->readString(input: $input, keys: ['client_name', 'clientName']) ?? '',
                                                                     type                           : $this->oidcClientType(input: $input),
                                                                     redirectUris                   : $this->readStringList(input: $input, keys: ['redirect_uris', 'redirectUris']),
                                                                     allowedScopes                  : $this->readScopes(input: $input),
                                                                     allowedGrantTypes              : $this->readGrantTypes(input: $input),
                                                                     tokenEndpointAuthMethod        : $this->readTokenEndpointAuthMethod(input: $input),
                                                                     requestObjectSignatureRequired : $this->readBool(input: $input),
                                                                     requestObjectVerificationKeyPem: $this->readMultilineString(input: $input)
                                                                 ));

            return new JsonHttpResponse(
                statusCode: 201,
                body      : $this->oidcClientResource(client: $registered->client),
                headers   : [
                                'Content-Type'  => 'application/json',
                                'Cache-Control' => 'no-store',
                            ]
            );
        }

        if ($method === 'PUT' && preg_match('~^/oidc/register/([^/]+)$~', $path, $matches) === 1) {
            $clientId = urldecode($matches[1]);
            $existing = $this->findOAuthClient(clientId: $clientId);

            if ($existing === null) {
                return $this->error(statusCode: 404, errorCode: 'not_found', message: 'OIDC client was not found.');
            }

            $requestedTokenEndpointAuthMethod = $this->readTokenEndpointAuthMethod(input: $input);
            $tokenEndpointAuthMethod          = $requestedTokenEndpointAuthMethod ?? $existing->tokenEndpointAuthMethod;
            $type                             = $requestedTokenEndpointAuthMethod === null
                ? $existing->type
                : ($tokenEndpointAuthMethod === OAuthTokenEndpointAuthMethod::NONE ? OAuthClientType::PUBLIC : OAuthClientType::CONFIDENTIAL);

            $updated = $this->auth->updateOAuthClient(data: new UpdateClientData(
                                                                clientId                       : $clientId,
                                                                name                           : $this->readString(input: $input, keys: ['client_name', 'clientName']) ?? $existing->name,
                                                                type                           : $type,
                                                                redirectUris                   : $this->readStringList(input: $input, keys: ['redirect_uris', 'redirectUris']),
                                                                allowedScopes                  : $this->readScopes(input: $input),
                                                                allowedGrantTypes              : $this->readGrantTypes(input: $input),
                                                                tokenEndpointAuthMethod        : $tokenEndpointAuthMethod,
                                                                requestObjectSignatureRequired : $this->readBool(input: $input),
                                                                requestObjectVerificationKeyPem: $this->readMultilineString(input: $input)
                                                            ));

            return new JsonHttpResponse(
                statusCode: 200,
                body      : $this->oidcClientResource(client: $updated),
                headers   : [
                                'Content-Type'  => 'application/json',
                                'Cache-Control' => 'no-store',
                            ]
            );
        }

        if ($method === 'DELETE' && preg_match('~^/oidc/register/([^/]+)$~', $path, $matches) === 1) {
            $client = $this->auth->disableOAuthClient(clientId: urldecode($matches[1]));

            return new JsonHttpResponse(
                statusCode: 200,
                body      : $this->oidcClientResource(client: $client) + ['active' => $client->active],
                headers   : [
                                'Content-Type'  => 'application/json',
                                'Cache-Control' => 'no-store',
                            ]
            );
        }

        if (in_array($method, ['GET', 'POST'], true) && $path === '/oidc/logout') {
            $result = $this->auth->oidcLogout(data: new OidcLogoutData(
                                                        sessionId            : $this->readString(input: $input, keys: ['session_id', 'sid']),
                                                        idTokenHint          : $this->readString(input: $input, keys: ['id_token_hint', 'idTokenHint']),
                                                        logoutToken          : $this->readString(input: $input, keys: ['logout_token', 'logoutToken']),
                                                        postLogoutRedirectUri: $this->readString(input: $input, keys: ['post_logout_redirect_uri', 'postLogoutRedirectUri']),
                                                        state                : $this->readString(input: $input, keys: ['state'])
                                                    ));

            return new JsonHttpResponse(
                statusCode: 200,
                body      : [
                                'revoked'                  => $result->revoked,
                                'session_id'               => $result->sessionId,
                                'post_logout_redirect_uri' => $result->postLogoutRedirectUri,
                                'state'                    => $result->state,
                            ],
                headers   : [
                                'Content-Type'  => 'application/json',
                                'Cache-Control' => 'no-store',
                            ]
            );
        }

        if ($method === 'POST' && $path === '/oidc/backchannel-logout') {
            $result = $this->auth->oidcLogout(data: new OidcLogoutData(
                                                        logoutToken: $this->readString(input: $input, keys: ['logout_token', 'logoutToken']) ?? ''
                                                    ));

            return new JsonHttpResponse(
                statusCode: $result->revoked ? 200 : 400,
                body      : [
                                'revoked'    => $result->revoked,
                                'session_id' => $result->sessionId,
                            ],
                headers   : [
                                'Content-Type'  => 'application/json',
                                'Cache-Control' => 'no-store',
                            ]
            );
        }

        return $this->error(statusCode: 404, errorCode: 'not_found', message: 'OIDC route was not found.');
    }

    private function normalizePath(string $path) : string
    {
        $trimmed = trim($path);

        if ($trimmed === '' || $trimmed === '/') {
            return '/';
        }

        return '/' . trim($trimmed, '/');
    }

    private function error(int $statusCode, #[SensitiveParameter] string $errorCode, string $message) : JsonHttpResponse
    {
        return new JsonHttpResponse(
            statusCode: $statusCode,
            body      : [
                            'error'             => $errorCode,
                            'error_description' => $message,
                        ],
            headers   : ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param list<string> $keys
     */
    private function readString(HttpEndpointInput $input, array $keys) : string|null
    {
        foreach ($keys as $key) {
            $value = $this->readArrayValue(values: $input->query, key: $key) ?? $this->readArrayValue(values: $input->body, key: $key) ?? $this->readArrayValue(values: $input->routeParameters, key: $key);

            if ($value === null || trim($value) === '') {
                continue;
            }

            return trim($value);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $values
     */
    private function readArrayValue(array $values, string $key) : string|null
    {
        foreach ($values as $candidateKey => $value) {
            if (strcasecmp($candidateKey, $key) !== 0) {
                continue;
            }

            if (is_array($value)) {
                $value = reset($value);
            }

            if (is_string($value)) {
                return $value;
            }

            if (is_int($value) || is_float($value) || is_bool($value)) {
                return (string) $value;
            }

        }

        return null;
    }

    /**
     *
     * @return list<string>
     */
    private function readScopes(HttpEndpointInput $input) : array
    {
        $keys = ['scope', 'scopes'];
        foreach ($keys as $key) {
            $value = $this->readString(input: $input, keys: [$key]);

            if ($value === null) {
                continue;
            }

            $parts = preg_split(pattern: '/\s+/', subject: trim($value), flags: PREG_SPLIT_NO_EMPTY);

            return $parts === false ? [] : array_values(array_unique($parts));
        }

        return [];
    }

    private function readPkceMethod(HttpEndpointInput $input) : PkceMethod|null
    {
        $value = $this->readString(input: $input, keys: ['code_challenge_method', 'codeChallengeMethod']);

        if ($value === null) {
            return null;
        }

        return PkceMethod::tryFrom(value: $value);
    }

    /**
     * @param array<string, mixed> $server
     */
    private function readServerValue(array $server, string $name) : string|null
    {
        $value = $server[$name] ?? null;

        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return (string) $value;
        }

        return null;
    }

    private function oidcClientType(HttpEndpointInput $input) : OAuthClientType
    {
        return $this->readTokenEndpointAuthMethod(input: $input) === OAuthTokenEndpointAuthMethod::NONE
            ? OAuthClientType::PUBLIC
            : OAuthClientType::CONFIDENTIAL;
    }

    private function readTokenEndpointAuthMethod(HttpEndpointInput $input) : OAuthTokenEndpointAuthMethod|null
    {
        $value = $this->readString(input: $input, keys: ['token_endpoint_auth_method', 'tokenEndpointAuthMethod']);

        return $value !== null ? OAuthTokenEndpointAuthMethod::tryFrom(value: $value) : null;
    }

    /**
     * @param list<string> $keys
     *
     * @return list<string>
     */
    private function readStringList(HttpEndpointInput $input, array $keys) : array
    {
        foreach ($keys as $key) {
            $value = $input->body[$key] ?? $input->query[$key] ?? null;

            if (! is_array($value)) {
                continue;
            }

            $resolved = [];

            foreach ($value as $candidate) {
                if (! is_scalar($candidate)) {
                    continue;
                }

                $normalized = trim((string) $candidate);

                if ($normalized === '' || in_array($normalized, $resolved, true)) {
                    continue;
                }

                $resolved[] = $normalized;
            }

            return $resolved;
        }

        return [];
    }

    /**
     *
     * @return list<OAuthGrantType>
     */
    private function readGrantTypes(HttpEndpointInput $input) : array
    {
        $keys     = ['grant_types', 'grantTypes'];
        $resolved = [];

        foreach ($this->readStringList(input: $input, keys: $keys) as $value) {
            $grantType = OAuthGrantType::tryFrom(value: $value);

            if ($grantType === null || in_array($grantType, $resolved, true)) {
                continue;
            }

            $resolved[] = $grantType;
        }

        return $resolved;
    }

    /**
     */
    private function readBool(HttpEndpointInput $input) : bool
    {
        $keys = ['request_object_signature_required', 'requestObjectSignatureRequired'];
        foreach ($keys as $key) {
            $value = $input->body[$key] ?? $input->query[$key] ?? null;

            if (is_bool($value)) {
                return $value;
            }

            if (is_string($value)) {
                return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
            }
        }

        return false;
    }

    /**
     */
    private function readMultilineString(HttpEndpointInput $input) : string|null
    {
        $keys = ['request_object_verification_key_pem', 'requestObjectVerificationKeyPem'];
        foreach ($keys as $key) {
            $value = $input->body[$key] ?? $input->query[$key] ?? $input->routeParameters[$key] ?? null;

            if (is_string($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function oidcClientResource(OAuthClient $client) : array
    {
        return [
            'client_id'                           => $client->clientId,
            'client_name'                         => $client->name,
            'redirect_uris'                       => $client->redirectUris,
            'grant_types'                         => array_map(static fn (OAuthGrantType $grantType) : string => $grantType->value, $client->allowedGrantTypes),
            'scope'                               => implode(' ', $client->allowedScopes),
            'token_endpoint_auth_method'          => $client->tokenEndpointAuthMethod->value,
            'request_object_signature_required'   => $client->requestObjectSignatureRequired,
            'request_object_verification_key_pem' => $client->requestObjectVerificationKeyPem,
        ];
    }

    private function findOAuthClient(string $clientId) : OAuthClient|null
    {
        foreach ($this->auth->readOAuthClients() as $client) {
            if ($client->clientId === $clientId) {
                return $client;
            }
        }

        return null;
    }
}
