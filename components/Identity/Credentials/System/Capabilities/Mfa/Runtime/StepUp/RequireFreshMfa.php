<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\FreshMfaRequired;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Guards sensitive actions behind recent MFA proof.
 */
final readonly class RequireFreshMfa
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private Clock                 $clock,
        private int                   $maxAgeSeconds = 300,
    ) {}

    /**
     * @throws Unauthenticated
     * @throws FreshMfaRequired
     */
    public function execute(?int $maxAgeSeconds = null) : void
    {
        $authenticationContext = $this->currentAuthentication->read();
        $user                  = $authenticationContext->user();
        $maxAgeSeconds         ??= $this->maxAgeSeconds;

        if (! $user instanceof AuthenticatedUser) {
            throw new Unauthenticated();
        }

        if (! $user->mfaEnabled) {
            return;
        }

        $verifiedAt = $authenticationContext->mfaVerifiedAt();

        if (! $verifiedAt instanceof DateTimeImmutable) {
            throw new FreshMfaRequired(maxAgeSeconds: $maxAgeSeconds);
        }

        $age = $this->clock->now()->getTimestamp() - $verifiedAt->getTimestamp();

        if ($age > $maxAgeSeconds) {
            throw new FreshMfaRequired(maxAgeSeconds: $maxAgeSeconds);
        }
    }
}
