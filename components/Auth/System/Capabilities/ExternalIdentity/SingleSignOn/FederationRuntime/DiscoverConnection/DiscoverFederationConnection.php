<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\DiscoverConnection;

use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use SensitiveParameter;

final readonly class DiscoverFederationConnection
{
    public function __construct(private FederationConnectionStoreInterface $connectionStore) {}

    public function execute(#[SensitiveParameter] string $email) : FederationConnection|null
    {
        $separator = strrchr(haystack: $email, needle: '@');
        $domain    = strtolower(string: trim(string: substr(string: $separator !== false ? $separator : '', offset: 1)));

        if ($domain === '') {
            return null;
        }

        $connection = $this->connectionStore->findByDomain(domain: $domain);

        return $connection?->isDomainVerified() === true ? $connection : null;
    }
}
