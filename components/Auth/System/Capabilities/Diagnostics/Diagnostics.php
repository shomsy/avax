<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Diagnostics;

use components\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplainer;
use components\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;
use SensitiveParameter;

final readonly class Diagnostics
{
    public function __construct(#[SensitiveParameter] private AuthIssueExplainer $authIssueExplainer) {}

    public function explainAccessDenied(
        string      $resource,
        string|null $requiredPermission = null,
        string|null $tenant = null,
        string|null $resourceTenant = null
    ) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainAccessDenied(
            resource          : $resource,
            requiredPermission: $requiredPermission,
            tenant            : $tenant,
            resourceTenant    : $resourceTenant
        );
    }

    public function explainStepUpRequired(
        string    $action,
        bool|null $phishingResistantRequired = null,
        int|null  $freshAfterSeconds = null
    ) : AuthIssueExplanation
    {
        $phishingResistantRequired ??= false;

        return $this->authIssueExplainer->explainStepUpRequired(
            action                   : $action,
            phishingResistantRequired: $phishingResistantRequired,
            freshAfterSeconds        : $freshAfterSeconds
        );
    }

    public function explainSenderConstraintFailure(string $reason, string|null $requiredConstraint = null) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainSenderConstraintFailure(
            reason            : $reason,
            requiredConstraint: $requiredConstraint
        );
    }

    public function explainSessionRevocation(string $status, #[SensitiveParameter] string|null $sessionId = null) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainSessionRevocation(status: $status, sessionId: $sessionId);
    }

    public function explainTrustedDeviceDecision(string|null $deviceId = null) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainTrustedDeviceDecision(deviceId: $deviceId);
    }
}
