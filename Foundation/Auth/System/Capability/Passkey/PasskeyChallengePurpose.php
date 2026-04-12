<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Passkey;

enum PasskeyChallengePurpose : string
{
    case REGISTRATION = 'registration';
    case AUTHENTICATION = 'authentication';
}
