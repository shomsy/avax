<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp;

use Avax\Components\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Auth\System\Foundation\Clock;
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
