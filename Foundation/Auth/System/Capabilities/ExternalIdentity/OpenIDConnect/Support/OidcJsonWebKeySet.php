<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

final readonly class OidcJsonWebKeySet
{
    /** @var list<OidcJsonWebKey> */
    public array $keys;

    /**
     * @param list<OidcJsonWebKey> $keys
     */
    public function __construct(
        array $keys
    )
    {
        $this->keys = $keys;
    }
}
