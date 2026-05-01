<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Explainability;

use SensitiveParameter;

final readonly class AuthIssueExplainer
{
    public function explainAccessDenied(
        string $resource,
        ?string $requiredPermission = null,
        ?string $tenant = null,
        ?string $resourceTenant = null,
    ): AuthIssueExplanation {
        $resourceName = trim(string: $resource) !== '' ? trim(string: $resource) : 'resource';

        return new AuthIssueExplanation(
            code      : 'access_denied',
            message   : "Access denied to {$resourceName}.",
            meaning   : 'The identity is authenticated, but the current authorization policy did not allow this action.',
            resolution: [
                'Verify the required role or permission is assigned to the identity.',
                'Check tenant or resource ownership boundaries before retrying.',
                'Review the active access policy for this resource and action.',
            ],
            context   : [
                'resource' => $resourceName,
                'required_permission' => $requiredPermission !== null && trim(string: $requiredPermission) !== '' ? trim(string: $requiredPermission) : null,
                'tenant' => $tenant !== null && trim(string: $tenant) !== '' ? trim(string: $tenant) : null,
                'resource_tenant' => $resourceTenant !== null && trim(string: $resourceTenant) !== '' ? trim(string: $resourceTenant) : null,
            ],
        );
    }

    public function explainStepUpRequired(
        string $action,
        ?bool $phishingResistantRequired = null,
        ?int $freshAfterSeconds = null,
    ): AuthIssueExplanation {
        $phishingResistantRequired ??= false;
        $actionName = trim(string: $action) !== '' ? trim(string: $action) : 'sensitive_action';

        return new AuthIssueExplanation(
            code      : 'step_up_required',
            message   : "Step-up authentication is required for {$actionName}.",
            meaning   : $phishingResistantRequired
                            ? 'The current session is not strong enough because this action requires a phishing-resistant authenticator.'
                            : 'The current session is authenticated, but the action requires fresh MFA proof.',
            resolution: [
                $phishingResistantRequired
                    ? 'Complete the action with a passkey or another phishing-resistant factor.'
                    : 'Complete a fresh MFA challenge before retrying the action.',
                'Check the actor-tier assurance policy assigned to this identity.',
                'If this is unexpected, review the fresh-auth threshold for the action.',
            ],
            context   : [
                'action' => $actionName,
                'phishing_resistant_required' => $phishingResistantRequired ? 1 : 0,
                'fresh_after_seconds' => $freshAfterSeconds,
            ],
        );
    }

    public function explainSenderConstraintFailure(
        string $reason,
        ?string $requiredConstraint = null,
    ): AuthIssueExplanation {
        $normalizedReason = trim(string: $reason) !== '' ? trim(string: $reason) : 'unknown_reason';

        return new AuthIssueExplanation(
            code      : 'sender_constraint_failed',
            message   : "Sender-constraint validation failed: {$normalizedReason}.",
            meaning   : 'The proof presented with the token did not satisfy the DPoP or mTLS binding expected by the token posture.',
            resolution: [
                'Regenerate the DPoP proof or reconfigure the client certificate before retrying.',
                'Check reverse-proxy forwarding and trusted header configuration for this deployment profile.',
                'Compare the presented proof thumbprint against the token binding recorded at issuance time.',
            ],
            context   : [
                'reason' => $normalizedReason,
                'required_constraint' => $requiredConstraint !== null && trim(string: $requiredConstraint) !== '' ? trim(string: $requiredConstraint) : null,
            ],
        );
    }

    public function explainSessionRevocation(string $status, #[SensitiveParameter] ?string $sessionId = null): AuthIssueExplanation
    {
        $normalizedStatus = strtoupper(string: trim(string: $status));
        $meaning = match ($normalizedStatus) {
            'LOCAL_ONLY' => 'The local session state was revoked, but relying-party propagation is not part of this flow.',
            'PARTIAL' => 'The local session state was revoked, but at least one downstream relying party still needs operator attention.',
            'PROPAGATED' => 'The local session state and downstream relying parties acknowledged the revocation.',
            default => 'The session revocation state needs review.',
        };

        return new AuthIssueExplanation(
            code      : 'session_revocation_status',
            message   : "Session revocation status: {$normalizedStatus}.",
            meaning   : $meaning,
            resolution: [
                'Review the tracked session registry and refresh-token family state for the identity.',
                'If relying parties are involved, check their logout or back-channel notification health.',
                'Use the audit trail to confirm whether the revocation was local-only, partial, or fully propagated.',
            ],
            context   : [
                'status' => $normalizedStatus !== '' ? $normalizedStatus : 'UNKNOWN',
                'session_id' => $sessionId !== null && trim(string: $sessionId) !== '' ? trim(string: $sessionId) : null,
            ],
        );
    }

    public function explainTrustedDeviceDecision(?string $deviceId = null): AuthIssueExplanation
    {
        return new AuthIssueExplanation(
            code      : 'trusted_device_not_supported',
            message   : 'Trusted-device recognition is not provided by this package.',
            meaning   : 'Remembered-device posture is an explicit non-goal of the auth kernel because it requires a broader device trust chain.',
            resolution: [
                'Use MFA or phishing-resistant step-up instead of relying on remembered-device shortcuts.',
                'Implement device trust in a separate product boundary if your platform truly requires it.',
                'Refer to docs/trusted-device-policy.md before adding device-specific bypass behavior.',
            ],
            context   : [
                'device_id' => $deviceId !== null && trim(string: $deviceId) !== '' ? trim(string: $deviceId) : null,
            ],
        );
    }
}
