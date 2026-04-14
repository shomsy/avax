<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest;

use Avax\Auth\System\Capability\Oidc\OidcRequestObjectStoreInterface;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\OAuth\PkceMethod;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\OAuth\OAuthAuthorizationFailed;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;

final readonly class PushAuthorizationRequest
{
    public function __construct(
        private OidcRequestObjectStoreInterface $requestObjectStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private OAuthClientRegistryInterface|null $clientRegistry = null
    ) {}

    public function execute(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        $now = $this->clock->now();
        $signed = $this->resolveSignedRequestObject(data: $data, now: $now);
        $claims = $signed['claims'] ?? $this->claimsFromPlainRequest(data: $data);
        $clientId = trim((string) ($claims['client_id'] ?? ''));
        $redirectUri = trim((string) ($claims['redirect_uri'] ?? ''));

        if ($clientId === '' || $redirectUri === '') {
            throw new InvalidArgumentException(message: 'PAR requests require client and redirect URIs.');
        }

        $client = $this->clientRegistry?->find(clientId: $clientId);

        if ($client?->requestObjectSignatureRequired === true && ($signed['signature_verified'] ?? false) !== true) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $requestUri = 'urn:ietf:params:oauth:request_uri:' . bin2hex(random_bytes(16));
        $expiresAt = $now->modify(modifier: '+5 minutes');

        $this->requestObjectStore->store(
            requestUri: $requestUri,
            claims: $claims,
            expiresAt: $expiresAt,
            signatureVerified: ($signed['signature_verified'] ?? false) === true,
            signingAlgorithm: is_string($signed['signing_algorithm'] ?? null) ? $signed['signing_algorithm'] : null,
            signingClientId: ($signed['signature_verified'] ?? false) === true ? $clientId : null
        );

        $this->auditLog->record(event: new AuditEvent(
            name: 'auth.oidc.par.pushed',
            occurredAt: $now,
            context: [
                'client_id' => $clientId,
                'request_uri' => $requestUri,
                'redirect_uri' => $redirectUri,
                'request_object_signature_verified' => ($signed['signature_verified'] ?? false) === true ? 1 : 0,
                'request_object_signing_alg' => $signed['signing_algorithm'] ?? null,
            ]
        ));

        return new PushedAuthorizationRequest(
            requestUri: $requestUri,
            expiresAt: $expiresAt,
            clientId: $clientId,
            redirectUri: $redirectUri,
            scopes: $this->normalizeScopes(scopes: $this->normalizeScopeValue(value: $claims['scope'] ?? null)),
            state: $this->readStringValue(value: $claims['state'] ?? null),
            nonce: $this->readStringValue(value: $claims['nonce'] ?? null),
            codeChallenge: $this->readStringValue(value: $claims['code_challenge'] ?? null),
            codeChallengeMethod: $this->normalizeCodeChallengeMethod(value: $claims['code_challenge_method'] ?? null)
        );
    }

    /**
     * @return array{claims?: array<string, mixed>, signature_verified?: bool, signing_algorithm?: string}
     */
    private function resolveSignedRequestObject(PushAuthorizationRequestData $data, DateTimeImmutable $now) : array
    {
        $jwt = $data->requestObjectJwt !== null ? trim($data->requestObjectJwt) : '';

        if ($jwt === '') {
            return [];
        }

        if ($this->clientRegistry === null) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        [$header, $claims] = $this->decodeJwtWithoutVerification(jwt: $jwt);
        $algorithm = $this->readStringValue(value: $header['alg'] ?? null);
        $clientId = $this->readStringValue(value: $claims['client_id'] ?? null);
        $clientSecret = $data->clientSecret !== null ? trim($data->clientSecret) : '';

        if ($algorithm === null || $clientId === null || $clientSecret === '') {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        if (! in_array($algorithm, ['HS256', 'HS384', 'HS512'], true)) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        if ($data->clientId !== '' && trim($data->clientId) !== $clientId) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        if (! $this->clientRegistry->verifySecret(clientId: $clientId, plainTextSecret: $clientSecret)) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $verifiedClaims = (new HmacTokenCodec(secret: $clientSecret, algorithm: $algorithm))->decode(token: $jwt);

        if (! is_array($verifiedClaims)) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $expiresAt = $verifiedClaims['exp'] ?? null;
        $notBefore = $verifiedClaims['nbf'] ?? null;

        if (is_int($expiresAt) && $expiresAt <= $now->getTimestamp()) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        if (is_int($notBefore) && $notBefore > $now->getTimestamp()) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        return [
            'claims' => $verifiedClaims,
            'signature_verified' => true,
            'signing_algorithm' => $algorithm,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function claimsFromPlainRequest(PushAuthorizationRequestData $data) : array
    {
        return [
            'client_id' => trim($data->clientId),
            'redirect_uri' => trim($data->redirectUri),
            'scope' => implode(' ', $this->normalizeScopes(scopes: $data->scopes)),
            'state' => $data->state !== null ? trim($data->state) : null,
            'nonce' => $data->nonce !== null ? trim($data->nonce) : null,
            'code_challenge' => $data->codeChallenge !== null ? trim($data->codeChallenge) : null,
            'code_challenge_method' => $data->codeChallengeMethod?->value,
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function decodeJwtWithoutVerification(string $jwt) : array
    {
        $segments = explode('.', $jwt);

        if (count($segments) !== 3) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $header = $this->decodeJson(base64Url: $segments[0]);
        $claims = $this->decodeJson(base64Url: $segments[1]);

        if (! is_array($header) || ! is_array($claims)) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        return [$header, $claims];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string $base64Url) : array|null
    {
        $decoded = $this->base64UrlDecode(value: $base64Url);

        if ($decoded === null) {
            return null;
        }

        try {
            $payload = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($payload) ? $payload : null;
    }

    private function base64UrlDecode(string $value) : string|null
    {
        $padding = strlen($value) % 4;

        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }

    /**
     * @param list<string> $scopes
     * @return list<string>
     */
    private function normalizeScopes(array $scopes) : array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $parts = preg_split(pattern: '/\s+/', subject: trim($scope), flags: PREG_SPLIT_NO_EMPTY);

            if ($parts === false) {
                continue;
            }

            foreach ($parts as $value) {
                if (in_array(needle: $value, haystack: $normalized, strict: true)) {
                    continue;
                }

                $normalized[] = $value;
            }
        }

        sort(array: $normalized);

        return $normalized;
    }

    /**
     * @return list<string>
     * @param array<int, string>|string|null $value
     */
    private function normalizeScopeValue(string|array|null $value) : array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $parts = preg_split(pattern: '/\s+/', subject: trim($value), flags: PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : $parts;
    }

    private function normalizeCodeChallengeMethod(string|null $value) : PkceMethod|null
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return PkceMethod::tryFrom(trim($value));
    }

    private function readStringValue(mixed $value) : string|null
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }
}
