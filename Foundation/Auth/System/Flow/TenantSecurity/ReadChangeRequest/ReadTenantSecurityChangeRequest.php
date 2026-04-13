<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\TenantSecurity\ReadChangeRequest;

use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStoreInterface;

final readonly class ReadTenantSecurityChangeRequest
{
    public function __construct(
        private TenantSecurityChangeRequestStoreInterface $changeRequestStore
    ) {}

    public function execute(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->changeRequestStore->find($changeId);
    }
}
