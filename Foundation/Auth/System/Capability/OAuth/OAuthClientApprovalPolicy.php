<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;

/**
 * Explicit approval rule for high-risk OAuth client registrations.
 */
final readonly class OAuthClientApprovalPolicy
{
    public function requiresApproval(
        bool $workloadIdentity = false,
        bool $phishingResistantRequired = false,
        OAuthSenderConstraintType|null $requiredSenderConstraint = null
    ) : bool {
        return $requiredSenderConstraint !== null;
    }
}
