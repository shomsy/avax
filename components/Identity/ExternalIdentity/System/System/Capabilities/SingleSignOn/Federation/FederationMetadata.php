<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\Federation;

/**
 * Stable metadata snapshot fetched from a federation provider.
 */
final readonly class FederationMetadata
{
    /**
     * @param array<string, string> $claims
     */
    public function __construct(public string $issuer, public string $singleSignOnUrl, public array $claims = []) {}
}
