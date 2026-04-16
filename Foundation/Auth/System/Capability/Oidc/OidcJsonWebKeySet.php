<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

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
