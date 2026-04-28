<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums;

/**
 * MFA proof types supported by the package.
 */
enum MfaMethod: string
{
    case TOTP        = 'totp';
    case BACKUP_CODE = 'backup_code';
}
