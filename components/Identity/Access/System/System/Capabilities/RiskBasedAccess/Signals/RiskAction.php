<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\RiskBasedAccess\Signals;

enum RiskAction: string
{
    case ALLOW           = 'allow';
    case REQUIRE_MFA     = 'require_mfa';
    case REQUIRE_PASSKEY = 'require_passkey';
    case SOFT_LOCK       = 'soft_lock';
    case REVOKE_SESSIONS = 'revoke_sessions';
    case OPEN_REVIEW     = 'open_review';
}
