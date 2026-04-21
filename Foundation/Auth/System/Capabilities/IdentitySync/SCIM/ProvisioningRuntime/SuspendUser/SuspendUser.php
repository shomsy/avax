<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\SuspendUser;

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

final readonly class SuspendUser
{
    private LifecycleOrchestrator|null        $lifecycle;
    private AdminElevationStoreInterface|null $adminElevationStore;
    private RefreshTokenStoreInterface|null   $refreshTokenStore;
    private SessionRegistryInterface|null     $sessionRegistry;
    private Clock                             $clock;
    private AuditLogInterface                 $auditLog;
    private RequireAdminElevation             $requireAdminElevation;
    private ProvisionableUserSourceInterface  $userSource;

    public function __construct(
        ProvisionableUserSourceInterface                      $userSource,
        RequireAdminElevation                                 $requireAdminElevation,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        #[SensitiveParameter] SessionRegistryInterface|null   $sessionRegistry = null,
        #[SensitiveParameter] RefreshTokenStoreInterface|null $refreshTokenStore = null,
        AdminElevationStoreInterface|null                     $adminElevationStore = null,
        LifecycleOrchestrator|null                            $lifecycle = null
    )
    {
        $this->userSource            = $userSource;
        $this->requireAdminElevation = $requireAdminElevation;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->sessionRegistry       = $sessionRegistry;
        $this->refreshTokenStore     = $refreshTokenStore;
        $this->adminElevationStore   = $adminElevationStore;
        $this->lifecycle             = $lifecycle;
    }

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
