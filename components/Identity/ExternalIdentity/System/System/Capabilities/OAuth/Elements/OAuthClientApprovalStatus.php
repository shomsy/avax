<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements;

/**
 * Approval state for a registered OAuth client.
 */
enum OAuthClientApprovalStatus: string
{
    case APPROVED         = 'approved';
    case PENDING_APPROVAL = 'pending_approval';
}
