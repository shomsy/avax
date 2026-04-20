<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\TenantSecurity\ReadChangeRequest;

use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityChangeRequestStoreInterface;

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
