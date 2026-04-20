<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\ReactivateUser;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Foundation\Clock;

final readonly class ReactivateUser
{
    private LifecycleOrchestrator|null       $lifecycle;
    private Clock                            $clock;
    private AuditLogInterface                $auditLog;
    private RequireAdminElevation            $requireAdminElevation;
    private ProvisionableUserSourceInterface $userSource;

    public function __construct(
        ProvisionableUserSourceInterface $userSource,
        RequireAdminElevation            $requireAdminElevation,
        AuditLogInterface                $auditLog,
        Clock                            $clock,
        LifecycleOrchestrator|null       $lifecycle = null
    )
    {
        $this->userSource            = $userSource;
        $this->requireAdminElevation = $requireAdminElevation;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->lifecycle             = $lifecycle;
    }

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
