<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\Policy;

/**
 * Recovery posture expressed by an identity policy.
 */
enum RecoveryPath: string
{
    case PASSWORD_RESET        = 'password_reset';
    case MFA_RECOVERY          = 'mfa_recovery';
    case FEDERATED_REPROVISION = 'federated_reprovision';
    case ADMIN_APPROVAL        = 'admin_approval';
    case NONE                  = 'none';
}
