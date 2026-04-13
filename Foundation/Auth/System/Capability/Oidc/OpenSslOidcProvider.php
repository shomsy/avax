<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

use Avax\Auth\System\Capability\User\User;
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

    public function __construct(
        private string                        $issuer,
        #[SensitiveParameter] string          $privateKeyPem,
        private string                        $keyId,
        private string                        $authorizationEndpoint,
        #[\SensitiveParameter] private string $tokenEndpoint,
        private string                        $userInfoEndpoint,
        private string                        $jsonWebKeySetUri,
        private int                           $idTokenLifetime = 600,
        private string                        $algorithm = 'RS256'
    ) {
        if (trim($issuer) === '') {
            throw new InvalidArgumentException(message: 'OIDC issuer cannot be empty.');
        }

        if ($algorithm !== 'RS256') {
            throw new InvalidArgumentException(message: 'Only RS256 is currently supported for OIDC ID tokens.');
        }

        $privateKey = openssl_pkey_get_private($privateKeyPem);

        if (! $privateKey instanceof OpenSSLAsymmetricKey) {
            throw new InvalidArgumentException(message: 'OIDC private key could not be loaded.');
        }

        $details = openssl_pkey_get_details($privateKey);

        if (! is_array($details) || ! is_string($details['key'] ?? null)) {
            throw new InvalidArgumentException(message: 'OIDC public key details could not be derived.');
        }

        $publicKey = openssl_pkey_get_public($details['key']);

        if (! $publicKey instanceof OpenSSLAsymmetricKey) {
            throw new InvalidArgumentException(message: 'OIDC public key could not be loaded.');
        }

        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
    }

    public function issueIdToken(
        User $user,
        string $clientId,
        array $scopes,
        string|null $nonce = null,
        DateTimeImmutable|null $authenticatedAt = null,
        bool $phishingResistant = false
    ) : OidcIdToken
    {
        $issuedAt = new DateTimeImmutable();
        $expiresAt = $issuedAt->modify(modifier: '+' . $this->idTokenLifetime . ' seconds');
        $claims = [
            'iss' => $this->issuer,
            'sub' => (string) $user->getId()->value,
            'aud' => $clientId,
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'auth_time' => ($authenticatedAt ?? $issuedAt)->getTimestamp(),
            'preferred_username' => $user->getUsername(),
        ];

        if (in_array('email', $scopes, true)) {
            $claims['email'] = $user->getEmail()->value;
        }

        if (in_array('profile', $scopes, true)) {
            $claims['name'] = $user->getUsername();
        }

        if ($nonce !== null && trim($nonce) !== '') {
            $claims['nonce'] = trim($nonce);
        }

        if ($phishingResistant) {
            $claims['acr'] = 'phr';
        }

        return new OidcIdToken(
            token     : $this->encode(claims: $claims),
            expiresAt : $expiresAt
        );
    }

    public function readProviderMetadata() : OidcProviderMetadata
    {
        return new OidcProviderMetadata(
            issuer                        : $this->issuer,
            authorizationEndpoint         : $this->authorizationEndpoint,
            tokenEndpoint                 : $this->tokenEndpoint,
            userInfoEndpoint              : $this->userInfoEndpoint,
            jsonWebKeySetUri              : $this->jsonWebKeySetUri,
            scopesSupported               : ['openid', 'profile', 'email'],
            responseTypesSupported        : ['code'],
            grantTypesSupported           : ['authorization_code', 'refresh_token'],
            subjectTypesSupported         : ['public'],
            idTokenSigningAlgValuesSupported: [$this->algorithm],
            codeChallengeMethodsSupported : ['S256']
        );
    }

    public function readJsonWebKeySet() : OidcJsonWebKeySet
    {
        $details = openssl_pkey_get_details($this->publicKey);

        if (! is_array($details) || ! isset($details['rsa']['n'], $details['rsa']['e'])) {
            throw new RuntimeException(message: 'OIDC RSA key details are not available.');
        }

        return new OidcJsonWebKeySet(keys: [
            new OidcJsonWebKey(
                keyType  : 'RSA',
                keyId    : $this->keyId,
                algorithm: $this->algorithm,
                use      : 'sig',
                modulus  : $this->base64UrlEncode(value: $details['rsa']['n']),
                exponent : $this->base64UrlEncode(value: $details['rsa']['e'])
            ),
        ]);
    }

    public function resolveIdToken(#[SensitiveParameter] string $idToken) : array|null
    {
        $segments = explode('.', $idToken);

        if (count($segments) !== 3) {
            return null;
        }

        [$headerSegment, $claimSegment, $signatureSegment] = $segments;
        $headerJson = $this->base64UrlDecode(value: $headerSegment);
        $claimsJson = $this->base64UrlDecode(value: $claimSegment);
        $signature = $this->base64UrlDecode(value: $signatureSegment);

        if ($headerJson === null || $claimsJson === null || $signature === null) {
            return null;
        }

        try {
            $header = json_decode($headerJson, true, 512, JSON_THROW_ON_ERROR);
            $claims = json_decode($claimsJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (
            ! is_array($header)
            || ! is_array($claims)
            || ($header['alg'] ?? null) !== $this->algorithm
            || ($header['kid'] ?? null) !== $this->keyId
        ) {
            return null;
        }

        $verified = openssl_verify(
            $headerSegment . '.' . $claimSegment,
            $signature,
            $this->publicKey,
            OPENSSL_ALGO_SHA256
        );

        if ($verified !== 1) {
            return null;
        }

        $issuer = $claims['iss'] ?? null;
        $expiresAt = $claims['exp'] ?? null;

        if (! is_string($issuer) || $issuer !== $this->issuer || ! is_int($expiresAt)) {
            return null;
        }

        if ($expiresAt <= time()) {
            return null;
        }

        return $claims;
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
        $claimSegment = $this->base64UrlEncode(value: $this->encodeJson(payload: $claims));
        $signature = '';

        if (! openssl_sign($headerSegment . '.' . $claimSegment, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException(message: 'OIDC ID token signing failed.');
        }

        return $headerSegment . '.' . $claimSegment . '.' . $this->base64UrlEncode(value: $signature);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encodeJson(array $payload) : string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(message: 'OIDC payload could not be encoded.', previous: $exception);
        }
    }

    private function base64UrlEncode(string $value) : string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
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
}
