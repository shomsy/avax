Foundation\Auth/System/Capability/Approvals/Approver.php

<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Approvals;

use DateTimeImmutable;

/**
 * Approver service that processes approval requests.
 */
final readonly class Approver
{
    public function __construct(
        private ApprovalRequestStoreInterface $store,
        private ApprovalPolicy $policy
    ) {}

    /**
     * Creates an approval request if required by policy.
     */
    public function request(
        ApprovalType $type,
        string $requesterId,
        string $resourceId,
        string $description
    ) : ApprovalRequest|null {
        if (! $this->policy->requiresApproval($type)) {
            return null;
        }

        $now = new DateTimeImmutable();
        $expiresMinutes = $this->policy->expiryMinutesFor($type);
        $expiresAt = $now->modify("+{$expiresMinutes} minutes");

        $request = new ApprovalRequest(
            requestId          : bin2hex(random_bytes(16)),
            type             : $type,
            requesterId      : $requesterId,
            resourceId       : $resourceId,
            description     : $description,
            status         : ApprovalStatus::PENDING,
            createdAt       : $now,
            expiresAt       : $expiresAt,
            resolvedAt      : null,
            resolverId     : null,
            resolutionReason: null
        );

        $this->store->save($request);

        return $request;
    }

    /**
     * Approves a pending request.
     */
    public function approve(
        string $requestId,
        string $resolverId,
        string $reason = null
    ) : ApprovalRequest|null {
        $request = $this->store->find($requestId);

        if ($request === null || ! $request->canBeApproved()) {
            return null;
        }

        $resolved = new ApprovalRequest(
            requestId          : $request->requestId,
            type             : $request->type,
            requesterId      : $request->requesterId,
            resourceId       : $request->resourceId,
            description     : $request->description,
            status         : ApprovalStatus::APPROVED,
            createdAt       : $request->createdAt,
            expiresAt       : $request->expiresAt,
            resolvedAt      : new DateTimeImmutable(),
            resolverId     : $resolverId,
            resolutionReason: $reason
        );

        $this->store->save($resolved);

        return $resolved;
    }

    /**
     * Rejects a pending request.
     */
    public function reject(
        string $requestId,
        string $resolverId,
        string $reason
    ) : ApprovalRequest|null {
        $request = $this->store->find($requestId);

        if ($request === null || ! $request->canBeApproved()) {
            return null;
        }

        $resolved = new ApprovalRequest(
            requestId          : $request->requestId,
            type             : $request->type,
            requesterId      : $request->requesterId,
            resourceId       : $request->resourceId,
            description     : $request->description,
            status         : ApprovalStatus::REJECTED,
            createdAt       : $request->createdAt,
            expiresAt       : $request->expiresAt,
            resolvedAt      : new DateTimeImmutable(),
            resolverId     : $resolverId,
            resolutionReason: $reason
        );

        $this->store->save($resolved);

        return $resolved;
    }

    /**
     * Checks if resource has pending approval.
     */
    public function hasPendingApproval(string $resourceId) : bool
    {
        return $this->store->findPendingByResource($resourceId) !== null;
    }
}