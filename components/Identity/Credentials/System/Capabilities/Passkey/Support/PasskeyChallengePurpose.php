<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support;

enum PasskeyChallengePurpose: string
{
    case REGISTRATION   = 'registration';
    case AUTHENTICATION = 'authentication';
}
