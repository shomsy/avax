<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol;

use DateTimeImmutable;

final readonly class OidcRequestObject
{
    public bool $signatureVerified;

    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        public string $requestUri,
        public array $claims,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $expiresAt,
        bool $signatureVerified = null,
        public ?string $signingAlgorithm = null,
        public ?string $signingClientId = null,
    ) {
        $signatureVerified ??= false;
        $this->signatureVerified = $signatureVerified;
    }
}
