<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Passkey;

enum PasskeyChallengePurpose: string
{
    case REGISTRATION   = 'registration';
    case AUTHENTICATION = 'authentication';
}
