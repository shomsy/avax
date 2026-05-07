<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners;

use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordReset;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPassword;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class Recovery
{
    public function __construct(
        #[SensitiveParameter]
        private BeginPasswordReset $beginPasswordReset,
        #[SensitiveParameter]
        private ResetPassword      $resetPassword,
    ) {}

    /**
     * @throws DateMalformedStringException
     */
    public function beginPasswordReset(BeginPasswordResetData $beginPasswordResetData) : PasswordResetChallenge
    {
        return $this->beginPasswordReset->execute(data: $beginPasswordResetData);
    }

    public function resetPassword(ResetPasswordData $resetPasswordData) : bool
    {
        return $this->resetPassword->execute(data: $resetPasswordData);
    }
}
