<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Provisioning\DeprovisionUser;

use Avax\Auth\System\Capability\AdminRealm\AdminElevationStoreInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class DeprovisionUser
{
    public function __construct(
        private ProvisionableUserSourceInterface $userSource,
        private RequireAdminElevation $requireAdminElevation,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private SessionRegistryInterface|null $sessionRegistry = null,
        private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        private AdminElevationStoreInterface|null $adminElevationStore = null
    ) {}

    public function execute(int $userId) : void
    {
        $this->requireAdminElevation->execute();
        $id = new UserId($userId);
        $this->userSource->replaceRoles($id, []);
        $this->userSource->replacePermissions($id, []);
        $this->userSource->deactivate($id);
        $this->sessionRegistry?->revokeForUser($id, $this->clock->now(), 'deprovisioned');
        $this->refreshTokenStore?->revokeUser($id);
        $this->adminElevationStore?->revokeUser($userId);
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.provisioning.user.deprovisioned',
            occurredAt: $this->clock->now(),
            context   : ['subject_user_id' => $userId]
        ));
    }
}
