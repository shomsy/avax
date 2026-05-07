<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy;

/**
 * Factors referenced by identity assurance policy tiers.
 */
enum AuthenticationFactor: string
{
    case PASSWORD      = 'password';
    case TOTP          = 'totp';
    case BACKUP_CODE   = 'backup_code';
    case PASSKEY       = 'passkey';
    case FEDERATED_SSO = 'federated_sso';
    case DPOP          = 'dpop';
    case MTLS          = 'mtls';
}
