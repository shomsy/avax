<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec;

use InvalidArgumentException;
use JsonException;
use SensitiveParameter;
use Throwable;

/**
 * Encodes and decodes compact JWTs signed with shared-secret HMAC algorithms.
 */
final readonly class HmacTokenCodec implements TokenCodecInterface
{
    private const array SUPPORTED_ALGORITHMS
        = [
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ];

    public function __construct(
        #[SensitiveParameter]
        private string $secret,
        private string $algorithm = 'HS256',
        private ?string $keyId = null,
    ) {
        if ($this->secret === '') {
            throw new InvalidArgumentException(message: 'HMAC token secret cannot be empty.');
        }

        if (! isset(self::SUPPORTED_ALGORITHMS[$this->algorithm])) {
            throw new InvalidArgumentException(
                message: sprintf(
                    'Unsupported HMAC token algorithm "%s". Supported algorithms: %s.',
                    $this->algorithm,
                    implode(separator: ', ', array: array_keys(array: self::SUPPORTED_ALGORITHMS)),
                ),
            );
        }
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    public function encode(array $claims): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm,
        ];

        if ($this->keyId !== null) {
            $header['kid'] = $this->keyId;
        }

        $encodedHeader = $this->base64UrlEncode(value: $this->jsonEncode(value: $header));
        $encodedClaims = $this->base64UrlEncode(value: $this->jsonEncode(value: $claims));
        $signingInput = sprintf('%s.%s', $encodedHeader, $encodedClaims);
        $signature = hash_hmac(
            algo  : self::SUPPORTED_ALGORITHMS[$this->algorithm],
            data  : $signingInput,
            key   : $this->secret,
            binary: true,
        );

        return sprintf('%s.%s', $signingInput, $this->base64UrlEncode(value: $signature));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(string: strtr(base64_encode(string: $value), '+/', '-_'), characters: '=');
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function jsonEncode(array $value): string
    {
        try {
            return json_encode(value: $value, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new InvalidArgumentException(message: 'Token payload could not be encoded as JSON.', code: $jsonException->getCode(), previous: $jsonException);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function decode(#[SensitiveParameter] string $token): ?array
    {
        try {
            $parts = explode(separator: '.', string: $token);

            if (count(value: $parts) !== 3) {
                return null;
            }

            [$encodedHeader, $encodedClaims, $encodedSignature] = $parts;
            $header = $this->jsonDecode(json: $this->base64UrlDecode(value: $encodedHeader));
            $claims = $this->jsonDecode(json: $this->base64UrlDecode(value: $encodedClaims));

            $headerAlgorithm = $header['alg'] ?? null;
            $headerKeyId = $header['kid'] ?? null;

            if (! is_string(value: $headerAlgorithm) || $headerAlgorithm !== $this->algorithm) {
                return null;
            }

            if ($this->keyId !== null && $headerKeyId !== $this->keyId) {
                return null;
            }

            if ($headerKeyId !== null && ! is_string(value: $headerKeyId)) {
                return null;
            }

            $providedSignature = $this->base64UrlDecode(value: $encodedSignature);
            $expectedSignature = hash_hmac(
                algo  : self::SUPPORTED_ALGORITHMS[$this->algorithm],
                data  : sprintf('%s.%s', $encodedHeader, $encodedClaims),
                key   : $this->secret,
                binary: true,
            );

            if (! hash_equals(known_string: $expectedSignature, user_string: $providedSignature)) {
                return null;
            }

            return $claims;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonDecode(string $json): array
    {
        try {
            $decoded = json_decode(json: $json, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new InvalidArgumentException(message: 'Token payload is not valid JSON.', code: $jsonException->getCode(), previous: $jsonException);
        }

        if (! is_array(value: $decoded)) {
            throw new InvalidArgumentException(message: 'Token payload must decode to an object.');
        }

        return $decoded;
    }

    private function base64UrlDecode(string $value): string
    {
        $padding = (4 - (strlen(string: $value) % 4)) % 4;
        $decoded = base64_decode(
            string: strtr($value.str_repeat(string: '=', times: $padding), '-_', '+/'),
            strict: true,
        );

        if ($decoded === false) {
            throw new InvalidArgumentException(message: 'Token segment is not valid base64url.');
        }

        return $decoded;
    }
}
