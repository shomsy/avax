<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol;

final readonly class OidcJsonWebKeySet
{
    /**
     * @param list<OidcJsonWebKey> $keys
     */
    public function __construct(public array $keys) {}
}
