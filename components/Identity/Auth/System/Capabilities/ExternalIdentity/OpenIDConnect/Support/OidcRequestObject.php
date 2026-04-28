<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

use DateTimeImmutable;

final readonly class OidcRequestObject
{
    public bool $signatureVerified;

    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        public string            $requestUri,
        public array             $claims,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $expiresAt,
        bool|null                $signatureVerified = null,
        public string|null       $signingAlgorithm = null,
        public string|null       $signingClientId = null
    )
    {
        $signatureVerified       ??= false;
        $this->signatureVerified = $signatureVerified;
    }
}
