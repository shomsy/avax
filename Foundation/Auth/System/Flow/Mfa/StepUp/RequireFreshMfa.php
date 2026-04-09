<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\StepUp;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Mfa\FreshMfaRequired;
use Avax\Auth\System\Foundation\Clock;

/**
 * Guards sensitive actions behind recent MFA proof.
 */
final readonly class RequireFreshMfa
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication,
        private Clock                 $clock,
        private int                   $maxAgeSeconds = 300
    ) {}

    /**
     * @throws Unauthenticated
     * @throws FreshMfaRequired
     */
    public function execute() : void
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if (! $user->mfaEnabled) {
            return;
        }

        $verifiedAt = $context->mfaVerifiedAt();

        if ($verifiedAt === null) {
            throw new FreshMfaRequired($this->maxAgeSeconds);
        }

        $age = $this->clock->now()->getTimestamp() - $verifiedAt->getTimestamp();

        if ($age > $this->maxAgeSeconds) {
            throw new FreshMfaRequired($this->maxAgeSeconds);
        }
    }
}
