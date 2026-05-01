<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;

/**
 * Explicit approval rule for high-risk OAuth client registrations.
 */
final readonly class OAuthClientApprovalPolicy
{
    public function requiresApproval(
        ?bool                      $workloadIdentity = null,
        ?bool                      $phishingResistantRequired = null,
        ?OAuthSenderConstraintType $oAuthSenderConstraintType = null,
    ): bool {
        $workloadIdentity ??= false;
        $phishingResistantRequired ??= false;

        return $oAuthSenderConstraintType instanceof OAuthSenderConstraintType;
    }
}
