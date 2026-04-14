<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

use DateTimeImmutable;

final readonly class OidcRequestObject
{
    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        public string $requestUri,
        public array $claims,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $expiresAt,
        public bool $signatureVerified = false,
        public string|null $signingAlgorithm = null,
        public string|null $signingClientId = null
    ) {}
}
