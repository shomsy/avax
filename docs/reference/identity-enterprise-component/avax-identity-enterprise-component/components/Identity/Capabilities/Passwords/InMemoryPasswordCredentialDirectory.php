<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Passwords;

use Avax\Components\Identity\Foundation\State\ResettableIdentityState;
use Avax\Components\Identity\Foundation\Values\UserId;

final class InMemoryPasswordCredentialDirectory implements PasswordCredentialDirectory, ResettableIdentityState
{
    /** @var array<string, PasswordCredential> */
    private array $credentials = [];

    public function findForUser(UserId $userId): PasswordCredential|null
    {
        return $this->credentials[$userId->toString()] ?? null;
    }

    public function save(PasswordCredential $credential): void
    {
        $this->credentials[$credential->userId()->toString()] = $credential;
    }

    public function reset(): void
    {
        $this->credentials = [];
    }
}
