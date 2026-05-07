<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\Identity\IdentityOwners;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\System\Flows\ChangeEmail\BeginEmailChange;
use Avax\Components\Identity\Auth\System\System\Flows\ChangeEmail\BeginEmailChangeData;
use Avax\Components\Identity\Auth\System\System\Flows\ChangeEmail\ConfirmEmailChange;
use Avax\Components\Identity\Auth\System\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use Avax\Components\Identity\Auth\System\System\Flows\ChangeEmail\EmailChangeChallenge;
use Avax\Components\Identity\Auth\System\System\Flows\ChangeEmail\EmailChangeFailed;
use Avax\Components\Identity\Auth\System\System\Flows\ChangePassword\ChangePassword;
use Avax\Components\Identity\Auth\System\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Components\Identity\Auth\System\System\Flows\ChangePassword\PasswordChangeFailed;
use Avax\Components\Identity\Auth\System\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Components\Identity\Auth\System\System\Flows\Register\Register;
use Avax\Components\Identity\Auth\System\System\Flows\Register\RegistrationData;
use Avax\Components\Identity\Auth\System\System\Flows\Register\RegistrationFailed;
use Avax\Components\Identity\Auth\System\System\Flows\Register\RegistrationResult;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class Account
{
    public function __construct(
        #[SensitiveParameter]
        private ChangePassword      $changePassword,
        #[SensitiveParameter]
        private BeginEmailChange    $beginEmailChange,
        #[SensitiveParameter]
        private ?ConfirmEmailChange $confirmEmailChange,
        private Register            $register,
    ) {}

    /**
     * @throws PasswordChangeFailed
     * @throws RateLimitException
     * @throws Unauthenticated
     */
    public function changePassword(ChangePasswordData $changePasswordData) : void
    {
        $this->changePassword->execute(data: $changePasswordData);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function beginEmailChange(BeginEmailChangeData $beginEmailChangeData) : EmailChangeChallenge
    {
        return $this->beginEmailChange->execute(data: $beginEmailChangeData);
    }

    public function confirmEmailChange(ConfirmEmailChangeData $confirmEmailChangeData) : bool
    {
        return $this->confirmEmailChangeOrFail()->execute(data: $confirmEmailChangeData);
    }

    private function confirmEmailChangeOrFail() : ConfirmEmailChange
    {
        return $this->confirmEmailChange ?? throw EmailChangeFailed::unsupported();
    }

    /**
     * @throws RegistrationFailed
     * @throws RateLimitException
     */
    public function register(RegistrationData $registrationData) : RegistrationResult
    {
        return $this->register->execute(data: $registrationData);
    }
}
