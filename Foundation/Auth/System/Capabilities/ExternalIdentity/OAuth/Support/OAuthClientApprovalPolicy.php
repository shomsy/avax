<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\OAuth;

use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\OAuthSenderConstraintType;

/**
 * Explicit approval rule for high-risk OAuth client registrations.
 */
final readonly class OAuthClientApprovalPolicy
{
    public function requiresApproval(
        bool|null                      $workloadIdentity = null,
        bool|null                      $phishingResistantRequired = null,
        OAuthSenderConstraintType|null $requiredSenderConstraint = null
    ) : bool
    {
        $workloadIdentity          ??= false;
        $phishingResistantRequired ??= false;

        return $requiredSenderConstraint !== null;
    }
}
