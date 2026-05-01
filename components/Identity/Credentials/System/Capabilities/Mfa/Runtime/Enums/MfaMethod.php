<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums;

/**
 * MFA proof types supported by the package.
 */
enum MfaMethod: string
{
    case TOTP = 'totp';
    case BACKUP_CODE = 'backup_code';
}
