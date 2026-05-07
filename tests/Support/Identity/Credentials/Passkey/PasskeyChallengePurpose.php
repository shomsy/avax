<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Identity\Credentials\Passkey;

enum PasskeyChallengePurpose: string
{
    case REGISTRATION = 'registration';
    case AUTHENTICATION = 'authentication';
}
