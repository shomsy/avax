<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Protocol;

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
        ?string           $signingAlgorithm = null,
        ?string           $signingClientId = null,
    ) : OidcRequestObject;

    public function find(string $requestUri) : ?OidcRequestObject;

    public function consume(string $requestUri) : ?OidcRequestObject;
}
