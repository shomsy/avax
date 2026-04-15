<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\StepUp;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Mfa\FreshMfaRequired;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Guards sensitive actions behind recent MFA proof.
 */
final readonly class RequireFreshMfa
{
    private int                   $maxAgeSeconds;
    private Clock                 $clock;
    private CurrentAuthentication $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        Clock                                       $clock,
        int                                         $maxAgeSeconds = 300
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->clock                 = $clock;
        $this->maxAgeSeconds         = $maxAgeSeconds;
    }

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
