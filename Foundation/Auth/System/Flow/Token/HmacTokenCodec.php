<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use InvalidArgumentException;
use JsonException;
use SensitiveParameter;

/**
 * Minimal HMAC JWT codec owned by the package.
 */
final readonly class HmacTokenCodec implements TokenCodecInterface
{
    public function __construct(
        #[SensitiveParameter] private string $secret,
        private string                       $algorithm = 'HS256',
        private string|null                  $keyId = null
    )
    {
        if ($this->secret === '') {
            throw new InvalidArgumentException('JWT secret cannot be empty.');
        }

        if (! isset(self::algorithms()[$this->algorithm])) {
            throw new InvalidArgumentException("Unsupported JWT algorithm: {$this->algorithm}");
        }
    }

    /**
     * @return array<string, string>
     */
    private static function algorithms() : array
    {
        return [
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ];
    }

    /**
     * @param array<string, mixed> $claims
     */
    public function encode(array $claims) : string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm,
        ];

        if ($this->keyId !== null && trim($this->keyId) !== '') {
            $header['kid'] = trim($this->keyId);
        }

        $headerSegment = $this->base64UrlEncode($this->encodeJson($header));
        $claimSegment  = $this->base64UrlEncode($this->encodeJson($claims));
        $signature     = hash_hmac(
            algo  : self::algorithms()[$this->algorithm],
            data  : "{$headerSegment}.{$claimSegment}",
            key   : $this->secret,
            binary: true
        );

        return "{$headerSegment}.{$claimSegment}.{$this->base64UrlEncode($signature)}";
    }

    private function base64UrlEncode(string $value) : string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encodeJson(array $payload) : string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Token payload could not be encoded.', previous: $exception);
        }
    }

    public function decode(#[SensitiveParameter] string $token) : array|null
    {
        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            return null;
        }

        [$headerSegment, $claimSegment, $signatureSegment] = $segments;

        $header = $this->decodeJson($this->base64UrlDecode($headerSegment));

        if (! is_array($header) || ($header['alg'] ?? null) !== $this->algorithm) {
            return null;
        }

        if ($this->keyId !== null && ($header['kid'] ?? null) !== $this->keyId) {
            return null;
        }

        $expectedSignature = hash_hmac(
            algo  : self::algorithms()[$this->algorithm],
            data  : "{$headerSegment}.{$claimSegment}",
            key   : $this->secret,
            binary: true
        );

        $actualSignature = $this->base64UrlDecode($signatureSegment);

        if ($actualSignature === null || ! hash_equals($expectedSignature, $actualSignature)) {
            return null;
        }

        $claims = $this->decodeJson($this->base64UrlDecode($claimSegment));

        return is_array($claims) ? $claims : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string|null $json) : array|null
    {
        if ($json === null) {
            return null;
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
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
