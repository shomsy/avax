<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\FederationRuntime\DiscoverConnection;

use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use SensitiveParameter;

final readonly class DiscoverFederationConnection
{
    public function __construct(private FederationConnectionStoreInterface $federationConnectionStore) {}

    public function execute(#[SensitiveParameter] string $email) : ?FederationConnection
    {
        $separator = strrchr(haystack: $email, needle: '@');
        $domain    = strtolower(string: trim(string: substr(string: $separator !== false ? $separator : '', offset: 1)));

        if ($domain === '') {
            return null;
        }

        $connection = $this->federationConnectionStore->findByDomain(domain: $domain);

        return $connection?->isDomainVerified() === true ? $connection : null;
    }
}
