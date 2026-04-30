<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

final readonly class OidcJsonWebKeySet
{
    /**
     * @param list<OidcJsonWebKey> $keys
     */
    public function __construct(public array $keys) {}
}
