<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use InvalidArgumentException;
use JsonException;
use SensitiveParameter;
use Throwable;

/**
 * Loads the active HMAC key ring from a JSON file on every operation.
 */
final readonly class FileBackedHmacKeyRingCodec implements TokenCodecInterface
{
    private string $keyRingPath;

    public function __construct(
        string $keyRingPath
    )
    {
        $this->keyRingPath = $keyRingPath;
    }

    /**
     * @param array<string, mixed> $claims
     */
    public function encode(array $claims) : string
    {
        return $this->buildCodec()->encode(claims: $claims);
    }

    private function buildCodec() : TokenCodecInterface
    {
        $configuration = $this->readConfiguration();
        $primary       = $this->createCodec(configuration: $configuration['primary']);
        $verification  = [];

        foreach ($configuration['verification'] as $key) {
            $verification[] = $this->createCodec(configuration: $key);
        }

        return new MultiKeyHmacTokenCodec(
            primaryCodec      : $primary,
            verificationCodecs: $verification
        );
    }

    /**
     * @return array{
     *     primary: array{secret:string, algorithm:string, kid:string|null},
     *     verification: list<array{secret:string, algorithm:string, kid:string|null}>
     * }
     */
    private function readConfiguration() : array
    {
        if (! is_file($this->keyRingPath) || ! is_readable($this->keyRingPath)) {
            throw new InvalidArgumentException(message: "Key ring file is not readable: {$this->keyRingPath}");
        }

        $json = file_get_contents($this->keyRingPath);

        if ($json === false) {
            throw new InvalidArgumentException(message: "Key ring file could not be read: {$this->keyRingPath}");
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(message: 'Key ring file is not valid JSON.', previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new InvalidArgumentException(message: 'Key ring file must decode to an object.');
        }

        $primary      = $this->normalizeKey(candidate: $decoded['primary'] ?? null, label: 'primary');
        $verification = [];

        foreach ($decoded['verification'] ?? [] as $index => $key) {
            $verification[] = $this->normalizeKey(candidate: $key, label: "verification[$index]");
        }

        return [
            'primary'      => $primary,
            'verification' => $verification,
        ];
    }

    /**
     * @param mixed $candidate
     *
     * @return array{secret:string, algorithm:string, kid:string|null}
     */
    private function normalizeKey(mixed $candidate, string $label) : array
    {
        if (! is_array($candidate)) {
            throw new InvalidArgumentException(message: "Key ring {$label} entry must be an object.");
        }

        $secret    = trim((string) ($candidate['secret'] ?? ''));
        $algorithm = trim((string) ($candidate['algorithm'] ?? 'HS256'));
        $kid       = trim((string) ($candidate['kid'] ?? ''));

        if ($secret === '') {
            throw new InvalidArgumentException(message: "Key ring {$label} secret cannot be empty.");
        }

        return [
            'secret'    => $secret,
            'algorithm' => $algorithm,
            'kid'       => $kid !== '' ? $kid : null,
        ];
    }

    /**
     * @param array{secret:string, algorithm:string, kid:string|null} $configuration
     */
    private function createCodec(array $configuration) : TokenCodecInterface
    {
        return new HmacTokenCodec(
            secret   : $configuration['secret'],
            algorithm: $configuration['algorithm'],
            keyId    : $configuration['kid']
        );
    }

    public function decode(#[SensitiveParameter] string $token) : array|null
    {
        try {
            return $this->buildCodec()->decode(token: $token);
        } catch (Throwable) {
            return null;
        }
    }
}
