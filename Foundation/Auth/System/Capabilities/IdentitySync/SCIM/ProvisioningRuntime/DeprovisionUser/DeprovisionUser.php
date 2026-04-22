<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\DeprovisionUser;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmSupport\AdminElevationStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class DeprovisionUser
{
    public function __construct(
        private ProvisionableUserSourceInterface                      $userSource,
        private RequireAdminElevation                                 $requireAdminElevation,
        private AuditLogInterface                                     $auditLog,
        private Clock                                                 $clock,
        #[SensitiveParameter] private SessionRegistryInterface|null   $sessionRegistry = null,
        #[SensitiveParameter] private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        private AdminElevationStoreInterface|null                     $adminElevationStore = null,
        private LifecycleOrchestrator|null                            $lifecycle = null
    )
    {
    }

    public function execute(int $userId) : void
    {
        $this->requireAdminElevation->execute();
        $id = new UserId(value: $userId);
        $this->lifecycle?->deprovision(userId: $id, source: LifecycleSource::ADMIN, reason: 'admin_deprovisioned');
        if ($this->lifecycle === null) {
            $this->userSource->replaceRoles(id: $id, roles: []);
            $this->userSource->replacePermissions(id: $id, permissions: []);
            $this->userSource->deactivate(id: $id);
        }
        $this->sessionRegistry?->revokeForUser(userId: $id, revokedAt: $this->clock->now(), reason: 'deprovisioned');
        $this->refreshTokenStore?->revokeUser(userId: $id);
        $this->adminElevationStore?->revokeUser(userId: $userId);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.provisioning.user.deprovisioned',
                                           occurredAt: $this->clock->now(),
                                           context   : ['subject_user_id' => $userId]
                                       ));
    }
}
