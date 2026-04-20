<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

/**
 * Approval state for a registered OAuth client.
 */
enum OAuthClientApprovalStatus: string
{
    case APPROVED         = 'approved';
    case PENDING_APPROVAL = 'pending_approval';
}
