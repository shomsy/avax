<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support;

enum PasskeyChallengePurpose: string
{
    case REGISTRATION   = 'registration';
    case AUTHENTICATION = 'authentication';
}
