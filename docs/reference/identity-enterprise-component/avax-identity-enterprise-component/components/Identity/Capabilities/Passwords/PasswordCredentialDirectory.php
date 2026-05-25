<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Passwords;

use Avax\Components\Identity\Foundation\Values\UserId;

interface PasswordCredentialDirectory
{
    public function findForUser(UserId $userId): PasswordCredential|null;

    public function save(PasswordCredential $credential): void;
}
