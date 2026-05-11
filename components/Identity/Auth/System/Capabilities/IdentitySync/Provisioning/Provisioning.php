<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Provisioning;

use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\IdentitySyncCapabilityUnavailable;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\DeprovisionUser\DeprovisionUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\ReactivateUser\ReactivateUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\SuspendUser\SuspendUser;

final readonly class Provisioning
{
    public function __construct(
        private SuspendUser|null     $suspendUser,
        private ReactivateUser|null  $reactivateUser,
        private DeprovisionUser|null $deprovisionUser,
    ) {}

    public function isConfigured() : bool
    {
        return $this->suspendUser instanceof SuspendUser
            && $this->reactivateUser instanceof ReactivateUser
            && $this->deprovisionUser instanceof DeprovisionUser;
    }

    public function suspendUser(int $userId) : void
    {
        $this->suspendUserOrFail()->execute(userId: $userId);
    }

    private function suspendUserOrFail() : SuspendUser
    {
        return $this->suspendUser ?? throw IdentitySyncCapabilityUnavailable::provisioning(operation: 'suspend_user');
    }

    public function reactivateUser(int $userId) : void
    {
        $this->reactivateUserOrFail()->execute(userId: $userId);
    }

    private function reactivateUserOrFail() : ReactivateUser
    {
        return $this->reactivateUser ?? throw IdentitySyncCapabilityUnavailable::provisioning(operation: 'reactivate_user');
    }

    public function deprovisionUser(int $userId) : void
    {
        $this->deprovisionUserOrFail()->execute(userId: $userId);
    }

    private function deprovisionUserOrFail() : DeprovisionUser
    {
        return $this->deprovisionUser ?? throw IdentitySyncCapabilityUnavailable::provisioning(operation: 'deprovision_user');
    }
}
