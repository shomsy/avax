<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ReadChangeRequest;

use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStoreInterface;

final readonly class ReadTenantSecurityChangeRequest
{
    private TenantSecurityChangeRequestStoreInterface $changeRequestStore;

    public function __construct(
        TenantSecurityChangeRequestStoreInterface $changeRequestStore
    )
    {
        $this->changeRequestStore = $changeRequestStore;
    }

    public function execute(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->changeRequestStore->find(changeId: $changeId);
    }
}
