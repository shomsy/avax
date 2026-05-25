<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Accounts;

use Avax\Components\Identity\Foundation\Values\LoginName;
use Avax\Components\Identity\Foundation\Values\UserId;

interface UserAccountDirectory
{
    public function findByLoginName(LoginName $loginName): UserAccount|null;

    public function findByUserId(UserId $userId): UserAccount|null;

    public function save(UserAccount $account): void;
}
