<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation;

final readonly class SignaturePayload
{
    public function __construct(
        public SignatureKeyId $keyId,
        public SignatureTimestamp $timestamp,
        public SignatureNonce $nonce,
        public string $signature,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toHeaders(): array
    {
        return [
            'X-AvaX-Signature-KeyId' => $this->keyId->value,
            'X-AvaX-Signature-Timestamp' => (string) $this->timestamp->epochSeconds,
            'X-AvaX-Signature-Nonce' => $this->nonce->value,
            'X-AvaX-Signature' => $this->signature,
        ];
    }

    /**
     * @param array<string, string|list<string>> $headers
     *
     * @throws \InvalidArgumentException When required signature headers are missing
     */
    public static function fromHeaders(array $headers): self
    {
        $getHeader = static function (string $name) use ($headers): string {
            $lower = strtolower($name);
            foreach ($headers as $key => $values) {
                if (strtolower((string) $key) === $lower) {
                    return is_array($values) ? ($values[0] ?? '') : (string) $values;
                }
            }

            return '';
        };

        $keyId = $getHeader('X-AvaX-Signature-KeyId');
        $timestamp = (int) $getHeader('X-AvaX-Signature-Timestamp');
        $nonce = $getHeader('X-AvaX-Signature-Nonce');
        $signature = $getHeader('X-AvaX-Signature');

        if ($keyId === '' || $timestamp === 0 || $nonce === '' || $signature === '') {
            throw new \InvalidArgumentException('Missing required signature headers.');
        }

        return new self(
            new SignatureKeyId($keyId),
            new SignatureTimestamp($timestamp),
            new SignatureNonce($nonce),
            $signature,
        );
    }
}
