<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Security;

enum TenantSecurityChangeRequestStatus: string
{
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED         = 'approved';
    case APPLIED          = 'applied';
    case ROLLED_BACK      = 'rolled_back';
}
