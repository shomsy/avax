<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use DateMalformedStringException;
use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;
use OpenSSLAsymmetricKey;
use RuntimeException;
use SensitiveParameter;

final readonly class OpenSslOidcProvider implements OidcProviderInterface
{
    private OpenSSLAsymmetricKey $privateKey;

    private OpenSSLAsymmetricKey $publicKey;

    private int $idTokenLifetime;

    private SubjectIdentifierStrategy $subjectIdentifierStrategy;

    public function __construct(
        private string             $issuer,
        #[SensitiveParameter]
        string                     $privateKeyPem,
        private string             $keyId,
        private string             $authorizationEndpoint,
        #[SensitiveParameter]
        private string             $tokenEndpoint,
        private string             $userInfoEndpoint,
        private string      $jsonWebKeySetUri, SubjectIdentifierStrategy|null $subjectIdentifierStrategy = null,
        #[SensitiveParameter]
        private string|null $pairwiseSalt = null, int|null $idTokenLifetime = null,
        private string             $algorithm = 'RS256',
    )
    {
        $subjectIdentifierStrategy       ??= SubjectIdentifierStrategy::PUBLIC;
        $idTokenLifetime                 ??= 600;
        $this->subjectIdentifierStrategy = $subjectIdentifierStrategy;
        $this->idTokenLifetime           = $idTokenLifetime;
        if (trim(string: $this->issuer) === '') {
            throw new InvalidArgumentException(message: 'OIDC issuer cannot be empty.');
        }

        if ($this->algorithm !== 'RS256') {
            throw new InvalidArgumentException(message: 'Only RS256 is currently supported for OIDC ID tokens.');
        }

        if ($this->subjectIdentifierStrategy === SubjectIdentifierStrategy::PAIRWISE && trim(string: (string) $this->pairwiseSalt) === '') {
            throw new InvalidArgumentException(message: 'Pairwise OIDC subjects require a configured salt.');
        }

        $privateKey = openssl_pkey_get_private(private_key: $privateKeyPem);

        if (! $privateKey instanceof OpenSSLAsymmetricKey) {
            throw new InvalidArgumentException(message: 'OIDC private key could not be loaded.');
        }

        $details = openssl_pkey_get_details(key: $privateKey);

        if (! is_array(value: $details) || ! is_string(value: $details['key'] ?? null)) {
            throw new InvalidArgumentException(message: 'OIDC public key details could not be derived.');
        }

        $publicKey = openssl_pkey_get_public(public_key: $details['key']);

        if (! $publicKey instanceof OpenSSLAsymmetricKey) {
            throw new InvalidArgumentException(message: 'OIDC public key could not be loaded.');
        }

        $this->privateKey = $privateKey;
        $this->publicKey  = $publicKey;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function issueIdToken(
        User               $user,
        string             $clientId,
        array $scopes, string|null $nonce = null, DateTimeImmutable|null $authenticatedAt = null,
        #[SensitiveParameter]
        ?string            $sessionId = null,
        bool               $phishingResistant = false,
    ) : OidcIdToken
    {
        $issuedAt  = new DateTimeImmutable();
        $expiresAt = $issuedAt->modify(modifier: '+' . $this->idTokenLifetime . ' seconds');
        $claims    = [
            'iss'                => $this->issuer,
            'sub'                => $this->subjectIdentifier(user: $user, clientId: $clientId),
            'aud'                => $clientId,
            'iat'                => $issuedAt->getTimestamp(),
            'exp'                => $expiresAt->getTimestamp(),
            'auth_time'          => ($authenticatedAt ?? $issuedAt)->getTimestamp(),
            'preferred_username' => $user->getUsername(),
        ];

        if (in_array(needle: 'email', haystack: $scopes, strict: true)) {
            $claims['email'] = $user->getEmail()->value;
        }

        if (in_array(needle: 'profile', haystack: $scopes, strict: true)) {
            $claims['name'] = $user->getUsername();
        }

        if ($nonce !== null && trim(string: $nonce) !== '') {
            $claims['nonce'] = trim(string: $nonce);
        }

        if ($sessionId !== null && trim(string: $sessionId) !== '') {
            $claims['sid'] = trim(string: $sessionId);
        }

        if ($phishingResistant) {
            $claims['acr'] = 'phr';
        }

        return new OidcIdToken(
            token    : $this->issueJwt(claims: $claims),
            expiresAt: $expiresAt,
        );
    }

    public function subjectIdentifier(User $user, string $clientId) : string
    {
        return match ($this->subjectIdentifierStrategy) {
            SubjectIdentifierStrategy::PUBLIC   => (string) $user->getId()->value,
            SubjectIdentifierStrategy::PAIRWISE => new SubjectIdentifier(strategy: SubjectIdentifierStrategy::PAIRWISE)->generate(
                localSubject    : (string) $user->getId()->value,
                sectorIdentifier: $clientId,
                pairwiseSalt    : (string) $this->pairwiseSalt,
            ),
        };
    }

    public function issueJwt(array $claims) : string
    {
        return $this->encode(claims: $claims);
    }

    /**
     * @param array<string, mixed> $claims
     */
    private function encode(array $claims) : string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm,
            'kid' => $this->keyId,
        ];

        $headerSegment = $this->base64UrlEncode(value: $this->encodeJson(payload: $header));
        $claimSegment  = $this->base64UrlEncode(value: $this->encodeJson(payload: $claims));
        $signature     = '';

        if (! openssl_sign(data: $headerSegment . '.' . $claimSegment, signature: $signature, private_key: $this->privateKey, algorithm: OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException(message: 'OIDC ID token signing failed.');
        }

        return $headerSegment . '.' . $claimSegment . '.' . $this->base64UrlEncode(value: $signature);
    }

    private function base64UrlEncode(string $value) : string
    {
        return rtrim(string: strtr(base64_encode(string: $value), '+/', '-_'), characters: '=');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encodeJson(array $payload) : string
    {
        try {
            return json_encode(value: $payload, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new InvalidArgumentException(message: 'OIDC payload could not be encoded.', code: $jsonException->getCode(), previous: $jsonException);
        }
    }

    public function readProviderMetadata() : OidcProviderMetadata
    {
        return new OidcProviderMetadata(
            issuer                                        : $this->issuer,
            authorizationEndpoint                         : $this->authorizationEndpoint,
            tokenEndpoint                                 : $this->tokenEndpoint,
            registrationEndpoint                          : $this->issuer . '/oidc/register',
            userInfoEndpoint                              : $this->userInfoEndpoint,
            endSessionEndpoint                            : $this->issuer . '/oidc/logout',
            pushedAuthorizationRequestEndpoint            : $this->issuer . '/oauth/par',
            jsonWebKeySetUri                              : $this->jsonWebKeySetUri,
            scopesSupported                               : ['openid', 'profile', 'email'],
            responseTypesSupported                        : ['code'],
            grantTypesSupported                           : ['authorization_code', 'refresh_token'],
            subjectTypesSupported                         : [$this->subjectIdentifierStrategy->value],
            idTokenSigningAlgValuesSupported              : [$this->algorithm],
            codeChallengeMethodsSupported                 : ['S256'],
            frontChannelLogoutSupported                   : true,
            backChannelLogoutSupported                    : true,
            backChannelLogoutSessionSupported             : true,
            requestObjectSigningAlgValuesSupported        : ['HS256', 'HS384', 'HS512', $this->algorithm],
            authorizationResponseSigningAlgValuesSupported: [$this->algorithm],
        );
    }

    public function readJsonWebKeySet() : OidcJsonWebKeySet
    {
        $details = openssl_pkey_get_details(key: $this->publicKey);

        if (! is_array(value: $details) || ! isset($details['rsa']['n'], $details['rsa']['e'])) {
            throw new RuntimeException(message: 'OIDC RSA key details are not available.');
        }

        return new OidcJsonWebKeySet(keys: [
                                               new OidcJsonWebKey(
                                                   keyType  : 'RSA',
                                                   keyId    : $this->keyId,
                                                   algorithm: $this->algorithm,
                                                   use      : 'sig',
                                                   modulus  : $this->base64UrlEncode(value: $details['rsa']['n']),
                                                   exponent : $this->base64UrlEncode(value: $details['rsa']['e']),
                                               ),
                                           ]);
    }

    public function resolveIdToken(#[SensitiveParameter] string $idToken) : ?array
    {
        $claims = $this->resolveJwt(jwt: $idToken);

        if ($claims === null || ! is_string(value: $claims['aud'] ?? null) || trim(string: $claims['aud']) === '') {
            return null;
        }

        return $claims;
    }

    public function resolveJwt(#[SensitiveParameter] string $jwt) : ?array
    {
        $segments = explode(separator: '.', string: $jwt);

        if (count(value: $segments) !== 3) {
            return null;
        }

        [$headerSegment, $claimSegment, $signatureSegment] = $segments;
        $headerJson = $this->base64UrlDecode(value: $headerSegment);
        $claimsJson = $this->base64UrlDecode(value: $claimSegment);
        $signature  = $this->base64UrlDecode(value: $signatureSegment);

        if ($headerJson === null || $claimsJson === null || $signature === null) {
            return null;
        }

        try {
            $header = json_decode(json: $headerJson, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
            $claims = json_decode(json: $claimsJson, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (
            ! is_array(value: $header)
            || ! is_array(value: $claims)
            || ($header['alg'] ?? null) !== $this->algorithm
            || ($header['kid'] ?? null) !== $this->keyId
        ) {
            return null;
        }

        $verified = openssl_verify(
            data      : $headerSegment . '.' . $claimSegment,
            signature : $signature,
            public_key: $this->publicKey,
            algorithm : OPENSSL_ALGO_SHA256,
        );

        if ($verified !== 1) {
            return null;
        }

        $issuer    = $claims['iss'] ?? null;
        $expiresAt = $claims['exp'] ?? null;

        if (! is_string(value: $issuer) || $issuer !== $this->issuer || ! is_int(value: $expiresAt) || $expiresAt <= time()) {
            return null;
        }

        return $claims;
    }

    private function base64UrlDecode(string $value) : ?string
    {
        $padding = strlen(string: $value) % 4;

        if ($padding > 0) {
            $value .= str_repeat(string: '=', times: 4 - $padding);
        }

        $decoded = base64_decode(string: strtr($value, '-_', '+/'), strict: true);

        return $decoded === false ? null : $decoded;
    }
}
