<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

final readonly class OidcJsonWebKeySet
{
    /**
     * @param list<OidcJsonWebKey> $keys
     */
    public function __construct(public array $keys) {}
}
