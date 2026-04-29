<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Flows\ManageSecurityChange;

/**
 * BeginSecurityChange - Initiates a sensitive security configuration change (e.g. changing password policy).
 */
final readonly class BeginSecurityChange
{
    public function execute(string $tenantId, array $data) : object
    {
        // Logic to create a change request. Sourced from avax.txt TenantSecurity implementation.
        return (object)['request_id' => 'req_123', 'status' => 'pending_approval'];
    }
}
