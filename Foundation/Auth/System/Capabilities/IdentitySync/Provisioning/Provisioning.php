<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\Provisioning;

use Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\DeprovisionUser\DeprovisionUser;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\ReactivateUser\ReactivateUser;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\SuspendUser\SuspendUser;
use RuntimeException;

final readonly class Provisioning
{
    public function __construct(
        private SuspendUser|null     $suspendUser,
        private ReactivateUser|null  $reactivateUser,
        private DeprovisionUser|null $deprovisionUser
    ) {}

    public function suspendUser(int $userId) : void
    {
        $this->suspendUserOrFail()->execute(userId: $userId);
    }

    private function suspendUserOrFail() : SuspendUser
    {
        return $this->suspendUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }

    public function reactivateUser(int $userId) : void
    {
        $this->reactivateUserOrFail()->execute(userId: $userId);
    }

    private function reactivateUserOrFail() : ReactivateUser
    {
        return $this->reactivateUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }

    public function deprovisionUser(int $userId) : void
    {
        $this->deprovisionUserOrFail()->execute(userId: $userId);
    }

    private function deprovisionUserOrFail() : DeprovisionUser
    {
        return $this->deprovisionUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }
}
