<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Facades;

use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerification;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerificationData;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationChallenge;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmail;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmailData;

final readonly class Verification
{
    public function __construct(
        private BeginEmailVerification $beginEmailVerification,
        private VerifyEmail            $verifyEmail
    ) {}

    public function beginEmailVerification(BeginEmailVerificationData $data) : EmailVerificationChallenge
    {
        return $this->beginEmailVerification->execute(data: $data);
    }

    public function verifyEmail(VerifyEmailData $data) : bool
    {
        return $this->verifyEmail->execute(data: $data);
    }
}
