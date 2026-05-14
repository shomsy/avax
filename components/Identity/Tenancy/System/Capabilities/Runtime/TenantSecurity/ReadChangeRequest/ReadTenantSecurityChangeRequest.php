<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadChangeRequest;

use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStoreInterface;

final readonly class ReadTenantSecurityChangeRequest
{
    public function __construct(private TenantSecurityChangeRequestStoreInterface $tenantSecurityChangeRequestStore) {}

    public function execute(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->tenantSecurityChangeRequestStore->find(changeId: $changeId);
    }
}
