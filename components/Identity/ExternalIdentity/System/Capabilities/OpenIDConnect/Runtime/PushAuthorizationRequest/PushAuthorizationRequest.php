<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthAuthorizationFailed;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\PkceMethod;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Support\OidcProviderInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Support\OidcRequestObjectStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;
use Random\RandomException;
use SensitiveParameter;

final readonly class PushAuthorizationRequest
{
    public function __construct(private OidcRequestObjectStoreInterface $requestObjectStore, private AuditLogInterface $auditLog, private Clock $clock, private OAuthClientRegistryInterface|null $clientRegistry = null, private OidcProviderInterface|null $oidcProvider = null) {}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function execute(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        $now         = $this->clock->now();
        $signed      = $this->resolveSignedRequestObject(data: $data, now: $now);
        $claims      = $signed['claims'] ?? $this->claimsFromPlainRequest(data: $data);
        $clientId    = trim(string: (string) ($claims['client_id'] ?? ''));
        $redirectUri = trim(string: (string) ($claims['redirect_uri'] ?? ''));

        if ($clientId === '' || $redirectUri === '') {
            throw new InvalidArgumentException(message: 'PAR requests require client and redirect URIs.');
        }

        $client = $this->clientRegistry?->find(clientId: $clientId);

        if ($client?->requestObjectSignatureRequired === true && ($signed['signature_verified'] ?? false) !== true) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $requestUri = 'urn:ietf:params:oauth:request_uri:' . bin2hex(string: random_bytes(length: 16));
        $expiresAt  = $now->modify(modifier: '+5 minutes');

        $this->requestObjectStore->store(
            requestUri       : $requestUri,
            claims           : $claims,
            expiresAt        : $expiresAt,
            signatureVerified: ($signed['signature_verified'] ?? false) === true,
            signingAlgorithm : is_string(value: $signed['signing_algorithm'] ?? null) ? $signed['signing_algorithm'] : null,
            signingClientId  : ($signed['signature_verified'] ?? false) === true ? $clientId : null,
        );

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oidc.par.pushed',
                                           occurredAt: $now,
                                           context   : [
                                                           'client_id'                         => $clientId,
                                                           'request_uri'                       => $requestUri,
                                                           'redirect_uri'                      => $redirectUri,
                                                           'request_object_signature_verified' => ($signed['signature_verified'] ?? false) === true ? 1 : 0,
                                                           'request_object_signing_alg'        => $signed['signing_algorithm'] ?? null,
                                                       ],
                                       ));

        return new PushedAuthorizationRequest(
            requestUri         : $requestUri,
            expiresAt          : $expiresAt,
            clientId           : $clientId,
            redirectUri        : $redirectUri,
            scopes             : $this->normalizeScopes(scopes: $this->normalizeScopeValue(value: $claims['scope'] ?? null)),
            state              : $this->readStringValue(value: $claims['state'] ?? null),
            nonce              : $this->readStringValue(value: $claims['nonce'] ?? null),
            codeChallenge      : $this->readStringValue(value: $claims['code_challenge'] ?? null),
            codeChallengeMethod: $this->normalizeCodeChallengeMethod(value: $claims['code_challenge_method'] ?? null),
        );
    }

    /**
     * @return array{claims?: array<string, mixed>, signature_verified?: bool, signing_algorithm?: string}
     */
    private function resolveSignedRequestObject(PushAuthorizationRequestData $data, DateTimeImmutable $now) : array
    {
        $jwt = $data->requestObjectJwt !== null ? trim(string: $data->requestObjectJwt) : '';

        if ($jwt === '') {
            return [];
        }

        if ($this->clientRegistry === null) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        [$header, $claims] = $this->decodeJwtWithoutVerification(jwt: $jwt);
        $algorithm    = $this->readStringValue(value: $header['alg'] ?? null);
        $clientId     = $this->readStringValue(value: $claims['client_id'] ?? null);
        $clientSecret = $data->clientSecret !== null ? trim(string: $data->clientSecret) : '';
        $client       = $clientId !== null ? $this->clientRegistry->find(clientId: $clientId) : null;

        if ($algorithm === null || $clientId === null || $client === null) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        if ($data->clientId !== '' && trim(string: $data->clientId) !== $clientId) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $verifiedClaims = match ($algorithm) {
            'HS256', 'HS384', 'HS512' => $this->verifyHmacRequestObject(
                jwt         : $jwt,
                clientId    : $clientId,
                clientSecret: $clientSecret,
                algorithm   : $algorithm,
            ),
            'RS256'                   => $this->verifyRsaRequestObject(
                jwt         : $jwt,
                publicKeyPem: $client->requestObjectVerificationKeyPem,
            ),
            default                   => null,
        };

        if (! is_array(value: $verifiedClaims)) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $expiresAt = $verifiedClaims['exp'] ?? null;
        $notBefore = $verifiedClaims['nbf'] ?? null;

        if (is_int(value: $expiresAt) && $expiresAt <= $now->getTimestamp()) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        if (is_int(value: $notBefore) && $notBefore > $now->getTimestamp()) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        if (! $this->claimsMatchSignedRequestObjectPolicy(claims: $verifiedClaims, clientId: $clientId)) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        return [
            'claims'             => $verifiedClaims,
            'signature_verified' => true,
            'signing_algorithm'  => $algorithm,
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function decodeJwtWithoutVerification(#[SensitiveParameter] string $jwt) : array
    {
        $segments = explode(separator: '.', string: $jwt);

        if (count(value: $segments) !== 3) {
            throw OAuthAuthorizationFailed::invalidRequestObject();
        }

        $header = $this->decodeJson(base64Url: $segments[0]);
        $claims = $this->decodeJson(base64Url: $segments[1]);

        if (! is_array(value: $header) || ! is_array(value: $claims)) {
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
            $payload = json_decode(json: $decoded, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array(value: $payload) ? $payload : null;
    }

    private function base64UrlDecode(string $value) : string|null
    {
        $padding = strlen(string: $value) % 4;

        if ($padding > 0) {
            $value .= str_repeat(string: '=', times: 4 - $padding);
        }

        $decoded = base64_decode(string: strtr($value, '-_', '+/'), strict: true);

        return $decoded === false ? null : $decoded;
    }

    private function readStringValue(mixed $value) : string|null
    {
        if (! is_string(value: $value)) {
            return null;
        }

        $normalized = trim(string: $value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function verifyHmacRequestObject(
        #[SensitiveParameter]
        string $jwt,
        string $clientId,
        #[SensitiveParameter]
        string $clientSecret,
        string $algorithm,
    ) : array|null
    {
        if ($clientSecret === '' || ! $this->clientRegistry?->verifySecret(clientId: $clientId, plainTextSecret: $clientSecret)) {
            return null;
        }

        $claims = new HmacTokenCodec(secret: $clientSecret, algorithm: $algorithm)->decode(token: $jwt);

        return is_array(value: $claims) ? $claims : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function verifyRsaRequestObject(#[SensitiveParameter] string $jwt, #[SensitiveParameter] string|null $publicKeyPem) : array|null
    {
        if ($publicKeyPem === null || trim(string: $publicKeyPem) === '') {
            return null;
        }

        $segments = explode(separator: '.', string: $jwt);

        if (count(value: $segments) !== 3) {
            return null;
        }

        $signingInput = $segments[0] . '.' . $segments[1];
        $signature    = $this->base64UrlDecode(value: $segments[2]);

        if ($signature === null) {
            return null;
        }

        $verified = openssl_verify(data: $signingInput, signature: $signature, public_key: $publicKeyPem, algorithm: OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            return null;
        }

        $claims = $this->decodeJson(base64Url: $segments[1]);

        return is_array(value: $claims) ? $claims : null;
    }

    /**
     * @param array<string, mixed> $claims
     */
    private function claimsMatchSignedRequestObjectPolicy(array $claims, string $clientId) : bool
    {
        $issuer = $this->readStringValue(value: $claims['iss'] ?? null);

        if ($issuer !== null && $issuer !== $clientId) {
            return false;
        }

        if ($this->oidcProvider === null || ! array_key_exists(key: 'aud', array: $claims)) {
            return true;
        }

        $expectedAudience = $this->oidcProvider->readProviderMetadata()->issuer;
        $audience         = $claims['aud'];

        if (is_string(value: $audience)) {
            return trim(string: $audience) === $expectedAudience;
        }

        if (! is_array(value: $audience)) {
            return false;
        }

        return array_any(array: $audience, callback: static fn ($candidate) => is_string(value: $candidate) && trim(string: $candidate) === $expectedAudience);
    }

    /**
     * @return array<string, mixed>
     */
    private function claimsFromPlainRequest(PushAuthorizationRequestData $data) : array
    {
        return [
            'client_id'             => trim(string: $data->clientId),
            'redirect_uri'          => trim(string: $data->redirectUri),
            'scope'                 => implode(separator: ' ', array: $this->normalizeScopes(scopes: $data->scopes)),
            'state' => $data->state !== null ? trim(string: $data->state) : null,
            'nonce' => $data->nonce !== null ? trim(string: $data->nonce) : null,
            'code_challenge'        => $data->codeChallenge !== null ? trim(string: $data->codeChallenge) : null,
            'code_challenge_method' => $data->codeChallengeMethod?->value,
        ];
    }

    /**
     * @param list<string> $scopes
     *
     * @return list<string>
     */
    private function normalizeScopes(array $scopes) : array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $parts = preg_split(pattern: '/\s+/', subject: trim(string: $scope), flags: PREG_SPLIT_NO_EMPTY);

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
     * @param array<int, string>|string|null $value
     *
     * @return list<string>
     */
    private function normalizeScopeValue(string|array|null $value) : array
    {
        if (is_array(value: $value)) {
            return array_values(array: $value);
        }

        if (! is_string(value: $value) || trim(string: $value) === '') {
            return [];
        }

        $parts = preg_split(pattern: '/\s+/', subject: trim(string: $value), flags: PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : $parts;
    }

    private function normalizeCodeChallengeMethod(string|null $value) : PkceMethod|null
    {
        if ($value === null || trim(string: $value) === '') {
            return null;
        }

        return PkceMethod::tryFrom(value: trim(string: $value));
    }
}
