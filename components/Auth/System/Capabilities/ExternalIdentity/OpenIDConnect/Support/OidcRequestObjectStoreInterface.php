<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

use DateTimeImmutable;

interface OidcRequestObjectStoreInterface
{
    /**
     * @param array<string, mixed> $claims
     */
    public function store(
        string            $requestUri,
        array             $claims,
        DateTimeImmutable $expiresAt,
        bool              $signatureVerified = false,
        string|null       $signingAlgorithm = null,
        string|null       $signingClientId = null
    ) : OidcRequestObject;

    public function find(string $requestUri) : OidcRequestObject|null;

    public function consume(string $requestUri) : OidcRequestObject|null;
}
