<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\AuthenticatePassword;

use Avax\Components\Identity\Capabilities\Accounts\UserAccountDirectory;
use Avax\Components\Identity\Capabilities\Audit\IdentityAuditTrail;
use Avax\Components\Identity\Capabilities\Audit\IdentityEvent;
use Avax\Components\Identity\Capabilities\Passwords\PasswordCredentialDirectory;
use Avax\Components\Identity\Capabilities\Passwords\VerifyPasswordHash;
use Avax\Components\Identity\Foundation\Time\Clock;

final readonly class AuthenticatePassword
{
    public function __construct(
        private UserAccountDirectory $accounts,
        private PasswordCredentialDirectory $credentials,
        private VerifyPasswordHash $passwords,
        private IdentityAuditTrail $audit,
        private Clock $clock,
    ) {}

    public function authenticate(PasswordLogin $login): AuthenticationResult
    {
        $account = $this->accounts->findByLoginName($login->loginName());
        if ($account === null || ! $account->isEnabled()) {
            $this->audit->record(new IdentityEvent('identity.authentication.rejected', $this->clock->now()));

            return AuthenticationResult::rejected();
        }

        $credential = $this->credentials->findForUser($account->userId());
        if ($credential === null || ! $this->passwords->verify($login->password(), $credential->passwordHash())) {
            $this->audit->record(new IdentityEvent('identity.authentication.rejected', $this->clock->now(), ['user_id' => $account->userId()->toString()]));

            return AuthenticationResult::rejected();
        }

        $this->audit->record(new IdentityEvent('identity.authentication.accepted', $this->clock->now(), ['user_id' => $account->userId()->toString()]));

        return AuthenticationResult::accepted($account->userId(), $login->tenantId() ?? $account->primaryTenantId());
    }
}
