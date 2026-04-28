<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\ReactivateUser;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class ReactivateUser
{
    public function __construct(private ProvisionableUserSourceInterface $userSource, private RequireAdminElevation $requireAdminElevation, private AuditLogInterface $auditLog, private Clock $clock, private LifecycleOrchestrator|null $lifecycle = null) {}

    public function execute(int $userId) : void
    {
        $this->requireAdminElevation->execute();
        $id = new UserId(value: $userId);
        $this->lifecycle?->activate(userId: $id, source: LifecycleSource::ADMIN, reason: 'admin_reactivated');
        if ($this->lifecycle === null) {
            $this->userSource->activate(id: $id);
        }
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.provisioning.user.reactivated',
                                           occurredAt: $this->clock->now(),
                                           context   : ['subject_user_id' => $userId]
                                       ));
    }
}
