<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Provisioning\ReactivateUser;

use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class ReactivateUser
{
    public function __construct(
        private ProvisionableUserSourceInterface $userSource,
        private RequireAdminElevation $requireAdminElevation,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    public function execute(int $userId) : void
    {
        $this->requireAdminElevation->execute();
        $this->userSource->activate(new UserId($userId));
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.provisioning.user.reactivated',
            occurredAt: $this->clock->now(),
            context   : ['subject_user_id' => $userId]
        ));
    }
}
