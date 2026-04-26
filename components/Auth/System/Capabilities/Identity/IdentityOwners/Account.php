<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\IdentityOwners;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChange;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChange;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\EmailChangeChallenge;
use Avax\Auth\System\Flows\ChangeEmail\EmailChangeFailed;
use Avax\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flows\ChangePassword\PasswordChangeFailed;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Flows\Register\Register;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Auth\System\Flows\Register\RegistrationResult;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class Account
{
    public function __construct(
        #[SensitiveParameter] private ChangePassword          $changePassword,
        #[SensitiveParameter] private BeginEmailChange        $beginEmailChange,
        #[SensitiveParameter] private ConfirmEmailChange|null $confirmEmailChange,
        private Register                                      $register
    ) {}

    /**
     * @throws PasswordChangeFailed
     * @throws RateLimitException
     * @throws Unauthenticated
     */
    public function changePassword(ChangePasswordData $data) : void
    {
        $this->changePassword->execute(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge
    {
        return $this->beginEmailChange->execute(data: $data);
    }

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool
    {
        return $this->confirmEmailChangeOrFail()->execute(data: $data);
    }

    private function confirmEmailChangeOrFail() : ConfirmEmailChange
    {
        return $this->confirmEmailChange ?? throw EmailChangeFailed::unsupported();
    }

    /**
     * @throws RegistrationFailed
     * @throws RateLimitException
     */
    public function register(RegistrationData $data) : RegistrationResult
    {
        return $this->register->execute(data: $data);
    }
}
