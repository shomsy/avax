<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\Diagnostics;

use Avax\Components\Identity\Auth\System\System\Capabilities\Diagnostics\Explainability\AuthIssueExplainer;
use Avax\Components\Identity\Auth\System\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;
use SensitiveParameter;

final readonly class Diagnostics
{
    public function __construct(#[SensitiveParameter] private AuthIssueExplainer $authIssueExplainer) {}

    public function explainAccessDenied(
        string  $resource,
        ?string $requiredPermission = null,
        ?string $tenant = null,
        ?string $resourceTenant = null,
    ) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainAccessDenied(
            resource          : $resource,
            requiredPermission: $requiredPermission,
            tenant            : $tenant,
            resourceTenant    : $resourceTenant,
        );
    }

    public function explainStepUpRequired(
        string $action,
        ?bool  $phishingResistantRequired = null,
        ?int   $freshAfterSeconds = null,
    ) : AuthIssueExplanation
    {
        $phishingResistantRequired ??= false;

        return $this->authIssueExplainer->explainStepUpRequired(
            action                   : $action,
            phishingResistantRequired: $phishingResistantRequired,
            freshAfterSeconds        : $freshAfterSeconds,
        );
    }

    public function explainSenderConstraintFailure(string $reason, ?string $requiredConstraint = null) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainSenderConstraintFailure(
            reason            : $reason,
            requiredConstraint: $requiredConstraint,
        );
    }

    public function explainSessionRevocation(string $status, #[SensitiveParameter] ?string $sessionId = null) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainSessionRevocation(status: $status, sessionId: $sessionId);
    }

    public function explainTrustedDeviceDecision(?string $deviceId = null) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainTrustedDeviceDecision(deviceId: $deviceId);
    }
}
