<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\ReactivateUser;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;

final readonly class ReactivateUser
{
    public function __construct(private ProvisionableUserSourceInterface $provisionableUserSource, private RequireAdminElevation $requireAdminElevation, private AuditLogInterface $auditLog, private Clock $clock, private ?LifecycleOrchestrator $lifecycleOrchestrator = null) {}

    public function execute(int $userId): void
    {
        $this->requireAdminElevation->execute();
        $id = new UserId(value: $userId);
        $this->lifecycleOrchestrator?->activate(userId: $id, source: LifecycleSource::ADMIN, reason: 'admin_reactivated');
        if (! $this->lifecycleOrchestrator instanceof LifecycleOrchestrator) {
            $this->provisionableUserSource->activate(id: $id);
        }

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.provisioning.user.reactivated',
            occurredAt: $this->clock->now(),
            context   : ['subject_user_id' => $userId],
        ));
    }
}
