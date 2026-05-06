<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners;

use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerification;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerificationData;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationChallenge;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmail;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmailData;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class Verification
{
    public function __construct(
        #[SensitiveParameter]
        private BeginEmailVerification $beginEmailVerification,
        #[SensitiveParameter]
        private VerifyEmail $verifyEmail,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     */
    public function beginEmailVerification(BeginEmailVerificationData $beginEmailVerificationData): EmailVerificationChallenge
    {
        return $this->beginEmailVerification->execute(data: $beginEmailVerificationData);
    }

    public function verifyEmail(VerifyEmailData $verifyEmailData): bool
    {
        return $this->verifyEmail->execute(data: $verifyEmailData);
    }
}
