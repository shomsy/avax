<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\SuspendUser;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use SensitiveParameter;

final readonly class SuspendUser
{
    public function __construct(
        private ProvisionableUserSourceInterface $provisionableUserSource,
        private RequireAdminElevation            $requireAdminElevation,
        private AuditLogInterface                $auditLog,
        private Clock                            $clock,
        #[SensitiveParameter]
        private SessionRegistryInterface|null     $sessionRegistry = null,
        #[SensitiveParameter]
        private RefreshTokenStoreInterface|null   $refreshTokenStore = null,
        private AdminElevationStoreInterface|null $adminElevationStore = null,
        private LifecycleOrchestrator|null        $lifecycleOrchestrator = null,
    ) {}

    public function execute(int $userId) : void
    {
        $this->requireAdminElevation->execute();
        $id = new UserId(value: $userId);
        $this->lifecycleOrchestrator?->suspend(userId: $id, source: LifecycleSource::ADMIN, reason: 'admin_suspended');
        if (! $this->lifecycleOrchestrator instanceof LifecycleOrchestrator) {
            $this->provisionableUserSource->deactivate(id: $id);
        }

        $this->sessionRegistry?->revokeForUser(userId: $id, revokedAt: $this->clock->now(), reason: 'suspended');
        $this->refreshTokenStore?->revokeUser(userId: $id);
        $this->adminElevationStore?->revokeUser(userId: $userId);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.provisioning.user.suspended',
                                           occurredAt: $this->clock->now(),
                                           context   : ['subject_user_id' => $userId],
                                       ));
    }
}
