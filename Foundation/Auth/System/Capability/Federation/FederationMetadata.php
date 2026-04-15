<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

/**
 * Stable metadata snapshot fetched from a federation provider.
 */
final readonly class FederationMetadata
{
    public array  $claims;
    public string $singleSignOnUrl;
    public string $issuer;

    /**
     * @param array<string, string> $claims
     */
    public function __construct(
        string $issuer,
        string $singleSignOnUrl,
        array  $claims = []
    )
    {
        $this->issuer          = $issuer;
        $this->singleSignOnUrl = $singleSignOnUrl;
        $this->claims          = $claims;
    }
}
