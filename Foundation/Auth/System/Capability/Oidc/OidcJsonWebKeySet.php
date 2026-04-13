<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

final readonly class OidcJsonWebKeySet
{
    /**
     * @param list<OidcJsonWebKey> $keys
     */
    public function __construct(
        public array $keys
    ) {}
}
