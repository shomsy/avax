<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp;

use components\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use components\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Guards sensitive actions behind recent MFA proof.
 */
final readonly class RequireFreshMfa
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private Clock                                       $clock,
        private int                                         $maxAgeSeconds = 300
    ) {}

    /**
     * @throws Unauthenticated
     * @throws FreshMfaRequired
     */
    public function execute(int|null $maxAgeSeconds = null) : void
    {
        $context       = $this->currentAuthentication->read();
        $user          = $context->user();
        $maxAgeSeconds ??= $this->maxAgeSeconds;

        if ($user === null) {
            throw new Unauthenticated();
        }

        if (! $user->mfaEnabled) {
            return;
        }

        $verifiedAt = $context->mfaVerifiedAt();

        if ($verifiedAt === null) {
            throw new FreshMfaRequired(maxAgeSeconds: $maxAgeSeconds);
        }

        $age = $this->clock->now()->getTimestamp() - $verifiedAt->getTimestamp();

        if ($age > $maxAgeSeconds) {
            throw new FreshMfaRequired(maxAgeSeconds: $maxAgeSeconds);
        }
    }
}
