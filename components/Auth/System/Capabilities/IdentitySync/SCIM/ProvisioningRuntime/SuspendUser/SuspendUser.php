<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\SuspendUser;

use components\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use components\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use components\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use components\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use components\Auth\System\Capabilities\Identity\User\UserId;
use components\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use components\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use components\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use components\Auth\System\Capabilities\Tenancy\AdminRealmSupport\AdminElevationStoreInterface;
use components\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class SuspendUser
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
    ) {}

    public function execute(int $userId) : void
    {
        $this->requireAdminElevation->execute();
        $id = new UserId(value: $userId);
        $this->lifecycle?->suspend(userId: $id, source: LifecycleSource::ADMIN, reason: 'admin_suspended');
        if ($this->lifecycle === null) {
            $this->userSource->deactivate(id: $id);
        }
        $this->sessionRegistry?->revokeForUser(userId: $id, revokedAt: $this->clock->now(), reason: 'suspended');
        $this->refreshTokenStore?->revokeUser(userId: $id);
        $this->adminElevationStore?->revokeUser(userId: $userId);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.provisioning.user.suspended',
                                           occurredAt: $this->clock->now(),
                                           context   : ['subject_user_id' => $userId]
                                       ));
    }
}
