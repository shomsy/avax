<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Provisioning\SuspendUser;

use Avax\Auth\System\Capability\AdminRealm\AdminElevationStoreInterface;
use Avax\Auth\System\Capability\Lifecycle\LifecycleOrchestrator;
use Avax\Auth\System\Capability\Lifecycle\LifecycleSource;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
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
