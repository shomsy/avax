<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Facades;

use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordReset;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPassword;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData;
use SensitiveParameter;

final readonly class Recovery
{
    public function __construct(
        #[SensitiveParameter] private BeginPasswordReset $beginPasswordReset,
        #[SensitiveParameter] private ResetPassword      $resetPassword
    ) {}

    public function beginPasswordReset(#[SensitiveParameter] BeginPasswordResetData $data) : PasswordResetChallenge
    {
        return $this->beginPasswordReset->execute(data: $data);
    }

    public function resetPassword(ResetPasswordData $data) : bool
    {
        return $this->resetPassword->execute(data: $data);
    }
}
