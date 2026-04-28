<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\IdentityOwners;

use Avax\Components\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordReset;
use Avax\Components\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData;
use Avax\Components\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge;
use Avax\Components\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPassword;
use Avax\Components\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class Recovery
{
    public function __construct(
        #[SensitiveParameter] private BeginPasswordReset $beginPasswordReset,
        #[SensitiveParameter] private ResetPassword      $resetPassword
    ) {}

    /**
     * @throws DateMalformedStringException
     */
    public function beginPasswordReset(BeginPasswordResetData $data) : PasswordResetChallenge
    {
        return $this->beginPasswordReset->execute(data: $data);
    }

    public function resetPassword(ResetPasswordData $data) : bool
    {
        return $this->resetPassword->execute(data: $data);
    }
}
