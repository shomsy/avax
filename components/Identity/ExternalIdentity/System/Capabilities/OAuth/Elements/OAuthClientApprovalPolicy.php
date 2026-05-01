<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\SenderConstraint\OAuthSenderConstraintType;

/**
 * Explicit approval rule for high-risk OAuth client registrations.
 */
final readonly class OAuthClientApprovalPolicy
{
    public function requiresApproval(
        bool                           $workloadIdentity = null,
        bool                           $phishingResistantRequired = null,
        OAuthSenderConstraintType|null $requiredSenderConstraint = null,
    ) : bool
    {
        $workloadIdentity          ??= false;
        $phishingResistantRequired ??= false;

        return $requiredSenderConstraint !== null;
    }
}
