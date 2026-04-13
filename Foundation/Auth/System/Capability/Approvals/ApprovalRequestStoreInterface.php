<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Approvals;

/**
 * Store interface for approval requests.
 */
interface ApprovalRequestStoreInterface
{
    public function find(string $requestId) : ApprovalRequest|null;

    public function findPendingByResource(string $resourceId) : ApprovalRequest|null;

    /**
     * @return list<ApprovalRequest>
     */
    public function findPendingByType(ApprovalType $type) : array;

    public function save(ApprovalRequest $request) : void;

    public function delete(string $requestId) : void;
}