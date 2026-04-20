<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Federation\DiscoverConnection;

use Avax\Auth\System\Capabilities\Federation\FederationConnection;
use Avax\Auth\System\Capabilities\Federation\FederationConnectionStoreInterface;
use SensitiveParameter;

final readonly class DiscoverFederationConnection
{
    private FederationConnectionStoreInterface $connectionStore;

    public function __construct(
        FederationConnectionStoreInterface $connectionStore
    )
    {
        $this->connectionStore = $connectionStore;
    }

    public function execute(#[SensitiveParameter] string $email) : FederationConnection|null
    {
        $separator = strrchr($email, '@');
        $domain    = strtolower(trim(substr($separator !== false ? $separator : '', 1)));

        if ($domain === '') {
            return null;
        }

        $connection = $this->connectionStore->findByDomain(domain: $domain);

        return $connection?->isDomainVerified() === true ? $connection : null;
    }
}
