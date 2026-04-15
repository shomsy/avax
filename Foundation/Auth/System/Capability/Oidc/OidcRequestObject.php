<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

use DateTimeImmutable;

final readonly class OidcRequestObject
{
    public string|null       $signingClientId;
    public string|null       $signingAlgorithm;
    public bool              $signatureVerified;
    public DateTimeImmutable $expiresAt;
    public DateTimeImmutable $createdAt;
    public array             $claims;
    public string            $requestUri;

    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        string            $requestUri,
        array             $claims,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $expiresAt,
        bool|null         $signatureVerified = null,
        string|null       $signingAlgorithm = null,
        string|null       $signingClientId = null
    )
    {
        $signatureVerified       ??= false;
        $this->requestUri        = $requestUri;
        $this->claims            = $claims;
        $this->createdAt         = $createdAt;
        $this->expiresAt         = $expiresAt;
        $this->signatureVerified = $signatureVerified;
        $this->signingAlgorithm  = $signingAlgorithm;
        $this->signingClientId   = $signingClientId;
    }
}
