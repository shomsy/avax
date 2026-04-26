<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser;

use components\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimAccountState;

final readonly class ScimProvisioningResult
{
    /**
     * @param list<string> $roles
     */
    public function __construct(public int $userId, public string $externalId, public ScimAccountState $state, public array $roles, public bool $created, public bool $updated, public bool $idempotent, public bool $driftDetected) {}
}
