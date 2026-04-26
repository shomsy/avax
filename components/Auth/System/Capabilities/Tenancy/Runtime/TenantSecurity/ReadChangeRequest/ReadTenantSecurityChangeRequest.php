<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ReadChangeRequest;

use components\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use components\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStoreInterface;

final readonly class ReadTenantSecurityChangeRequest
{
    public function __construct(private TenantSecurityChangeRequestStoreInterface $changeRequestStore) {}

    public function execute(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->changeRequestStore->find(changeId: $changeId);
    }
}
