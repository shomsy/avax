<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Accounts;

use Avax\Components\Identity\Foundation\State\ResettableIdentityState;
use Avax\Components\Identity\Foundation\Values\LoginName;
use Avax\Components\Identity\Foundation\Values\UserId;

final class InMemoryUserAccountDirectory implements UserAccountDirectory, ResettableIdentityState
{
    /** @var array<string, UserAccount> */
    private array $byUserId = [];

    /** @var array<string, string> */
    private array $userIdByLoginName = [];

    public function findByLoginName(LoginName $loginName): UserAccount|null
    {
        $userId = $this->userIdByLoginName[$loginName->toString()] ?? null;
        if ($userId === null) {
            return null;
        }

        return $this->byUserId[$userId] ?? null;
    }

    public function findByUserId(UserId $userId): UserAccount|null
    {
        return $this->byUserId[$userId->toString()] ?? null;
    }

    public function save(UserAccount $account): void
    {
        $this->byUserId[$account->userId()->toString()] = $account;
        $this->userIdByLoginName[$account->loginName()->toString()] = $account->userId()->toString();
    }

    public function reset(): void
    {
        $this->byUserId = [];
        $this->userIdByLoginName = [];
    }
}
