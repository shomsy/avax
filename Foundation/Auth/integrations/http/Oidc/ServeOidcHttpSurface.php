<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http\Oidc;

use Avax\Auth\Integrations\Headers\ReadBearerToken;
use Avax\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Auth\Integrations\Http\JsonHttpResponse;
use Avax\Auth\System\AuthInterface;
use Throwable;

/**
 * Publishes the kernel-owned OIDC discovery, JWKS, and userinfo HTTP surface.
 */
final readonly class ServeOidcHttpSurface
{
    public function __construct(
        private AuthInterface $auth,
        private ReadBearerToken $readBearerToken = new ReadBearerToken()
    ) {}

    public function execute(HttpEndpointInput $input) : JsonHttpResponse
    {
        $metadata = $this->auth->readOidcProviderMetadata();
        $path = $this->normalizePath($input->path);
        $method = strtoupper(trim($input->method));
        $jwksPath = $this->normalizePath((string) parse_url($metadata->jsonWebKeySetUri, PHP_URL_PATH));
        $userInfoPath = $this->normalizePath((string) parse_url($metadata->userInfoEndpoint, PHP_URL_PATH));

        if ($method === 'GET' && $path === '/.well-known/openid-configuration') {
            return new JsonHttpResponse(
                statusCode: 200,
                body      : [
                    'issuer' => $metadata->issuer,
                    'authorization_endpoint' => $metadata->authorizationEndpoint,
                    'token_endpoint' => $metadata->tokenEndpoint,
                    'userinfo_endpoint' => $metadata->userInfoEndpoint,
                    'jwks_uri' => $metadata->jsonWebKeySetUri,
                    'scopes_supported' => $metadata->scopesSupported,
                    'response_types_supported' => $metadata->responseTypesSupported,
                    'grant_types_supported' => $metadata->grantTypesSupported,
                    'subject_types_supported' => $metadata->subjectTypesSupported,
                    'id_token_signing_alg_values_supported' => $metadata->idTokenSigningAlgValuesSupported,
                    'code_challenge_methods_supported' => $metadata->codeChallengeMethodsSupported,
                ],
                headers   : [
                    'Content-Type' => 'application/json',
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
                            'n' => $key->modulus,
                            'e' => $key->exponent,
                        ],
                        $jsonWebKeySet->keys
                    ),
                ],
                headers   : [
                    'Content-Type' => 'application/jwk-set+json',
                    'Cache-Control' => 'public, max-age=300',
                ]
            );
        }

        if (in_array($method, ['GET', 'POST'], true) && $path === $userInfoPath) {
            $accessToken = $this->readBearerToken->execute($input->headers, $input->server);

            if ($accessToken === null) {
                return $this->error(401, 'invalid_token', 'Bearer access token is required for OIDC userinfo.');
            }

            try {
                $userInfo = $this->auth->readOidcUserInfo($accessToken);
            } catch (Throwable) {
                return $this->error(401, 'invalid_token', 'Active access token is required for OIDC userinfo.');
            }

            return new JsonHttpResponse(
                statusCode: 200,
                body      : $userInfo->claims,
                headers   : [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-store',
                ]
            );
        }

        return $this->error(404, 'not_found', 'OIDC route was not found.');
    }

    private function error(int $statusCode, string $errorCode, string $message) : JsonHttpResponse
    {
        return new JsonHttpResponse(
            statusCode: $statusCode,
            body      : [
                'error' => $errorCode,
                'error_description' => $message,
            ],
            headers   : ['Content-Type' => 'application/json']
        );
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
