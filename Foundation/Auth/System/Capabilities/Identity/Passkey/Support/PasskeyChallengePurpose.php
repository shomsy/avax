<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Support;

enum PasskeyChallengePurpose: string
{
    case REGISTRATION   = 'registration';
    case AUTHENTICATION = 'authentication';
}
